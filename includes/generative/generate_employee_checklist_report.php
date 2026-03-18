<?php
/**
 * generate_employee_checklist_report.php
 * Generates a PDF checklist report for ALL equipment assigned to an employee.
 * Each piece of equipment gets its own page with the latest maintenance record.
 *
 * Usage: ?employeeId=123
 */
require_once __DIR__ . '/../../config/session-guard.php';
require_once __DIR__ . '/../../vendor/TCPDF/tcpdf.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';

// ─── Input ────────────────────────────────────────────────────────────────────
$db         = getDB();
$employeeId = (int) ($_GET['employeeId'] ?? 0);

if ($employeeId <= 0) {
    http_response_code(400);
    exit('Missing or invalid employeeId.');
}

// ─── Fetch employee info ──────────────────────────────────────────────────────
$empStmt = $db->prepare("
    SELECT e.employeeId, e.firstName, e.middleName, e.lastName, e.suffixName,
           e.position,
           l.location_name,
           parent_loc.location_name AS parent_location_name
    FROM tbl_employee e
    LEFT JOIN location l ON e.location_id = l.location_id
    LEFT JOIN location parent_loc ON l.parent_location_id = parent_loc.location_id
    WHERE e.employeeId = :eid
");
$empStmt->bindValue(':eid', $employeeId, PDO::PARAM_INT);
$empStmt->execute();
$employee = $empStmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    http_response_code(404);
    exit('Employee not found.');
}

$empFullName = trim(implode(' ', array_filter([
    $employee['firstName'],
    $employee['middleName'],
    $employee['lastName'],
    $employee['suffixName']
])));

$empLocation = implode(' › ', array_filter([
    $employee['location_name']
]));

// ─── Fetch all equipment assigned to this employee ────────────────────────────
$equipStmt = $db->prepare("
    SELECT v.id, v.type_id, v.type_name, v.brand, v.serial, v.location_name
    FROM view_maintenance_master v
    WHERE v.owner_name = :ownerName
    ORDER BY v.type_name ASC, v.brand ASC
");
$ownerName = trim($employee['firstName'] . ' ' . $employee['lastName']);
$equipStmt->bindValue(':ownerName', $ownerName);
$equipStmt->execute();
$equipment = $equipStmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($equipment)) {
    http_response_code(404);
    exit('No equipment found for this employee.');
}

// ─── For each equipment, fetch latest maintenance record ──────────────────────
$equipmentRecords = [];
foreach ($equipment as $equip) {
    $recStmt = $db->prepare("
        SELECT
            mr.recordId,
            mr.templateId,
            mr.maintenanceDate,
            mr.overallStatus,
            mr.conditionRating,
            mr.remarks,
            mr.checklistJson,
            mr.preparedBy,
            mr.checkedBy,
            mr.notedBy,
            COALESCE(mt_direct.templateName, mt_fallback.templateName) AS templateName,
            COALESCE(mt_direct.signatories_json, mt_fallback.signatories_json) AS signatories_json
        FROM tbl_maintenance_record mr
        LEFT JOIN tbl_maintenance_schedule ms ON mr.scheduleId = ms.scheduleId
        LEFT JOIN tbl_maintenance_template mt_direct   ON mr.templateId = mt_direct.templateId
        LEFT JOIN tbl_maintenance_template mt_fallback ON mr.equipmentTypeId = mt_fallback.targetTypeId
                                                      AND mt_fallback.isActive = 1
                                                      AND mr.templateId IS NULL
        WHERE ms.equipmentId   = :equipId
          AND ms.equipmentType = :equipType
        ORDER BY mr.maintenanceDate DESC
        LIMIT 1
    ");
    $recStmt->bindValue(':equipId',   $equip['id'],      PDO::PARAM_INT);
    $recStmt->bindValue(':equipType', $equip['type_id'], PDO::PARAM_INT);
    $recStmt->execute();
    $rec = $recStmt->fetch(PDO::FETCH_ASSOC);

    $equipmentRecords[] = [
        'equipment' => $equip,
        'record'    => $rec ?: null,
    ];
}

// ─── checklistJson normalizer (same as single-record report) ──────────────────
function normaliseStatus(string $raw): string {
    $v = strtolower(trim($raw));
    if (in_array($v, ['yes', 'ok', 'done', 'pass', 'passed', 'x', '1', 'true'])) return 'Yes';
    if (in_array($v, ['no', 'fail', 'failed', 'ng', '0', 'false']))               return 'No';
    if (in_array($v, ['n/a', 'na', 'not applicable', '-']))                        return 'N/A';
    return '';
}

function normaliseChecklistJson(?string $raw): array {
    if (empty($raw)) return [];
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) return [];

    // Shape C: canonical
    if (isset($decoded[0]['categoryName'])) {
        foreach ($decoded as &$section) {
            foreach ($section['items'] as &$item) {
                $item['taskDescription'] = $item['taskDescription'] ?? $item['task'] ?? $item['desc'] ?? '';
                $item['value']           = normaliseStatus($item['value'] ?? $item['status'] ?? '');
            }
        }
        return $decoded;
    }

    // Shape B
    if (isset($decoded['categories']) && is_array($decoded['categories'])) {
        $out = [];
        foreach ($decoded['categories'] as $cat) {
            $catName = $cat['name'] ?? $cat['categoryName'] ?? 'General';
            $items   = [];
            foreach ($cat['items'] ?? [] as $item) {
                $items[] = [
                    'taskDescription' => $item['task'] ?? $item['taskDescription'] ?? $item['desc'] ?? '',
                    'value'           => normaliseStatus($item['status'] ?? $item['value'] ?? ''),
                ];
            }
            $out[] = ['categoryName' => $catName, 'items' => $items];
        }
        return $out;
    }

    // Shape A: flat array
    if (isset($decoded[0]) && is_array($decoded[0])) {
        $items = [];
        foreach ($decoded as $item) {
            $task = $item['desc'] ?? $item['task'] ?? $item['taskDescription'] ?? '';
            $val  = $item['status'] ?? $item['value'] ?? '';
            if ($task !== '') {
                $items[] = ['taskDescription' => $task, 'value' => normaliseStatus($val)];
            }
        }
        return [['categoryName' => 'Maintenance Checklist', 'items' => $items]];
    }

    return [];
}

/**
 * For a given record, build the normalised checklist sections.
 */
function buildChecklistSections(PDO $db, ?array $rec): array {
    if (!$rec) return [];

    $recordId = $rec['recordId'];

    // Priority 1: tbl_maintenance_response
    $sqlResp = "
        SELECT categoryName, taskDescription, response, sequenceOrder
        FROM tbl_maintenance_response
        WHERE recordId = :rid
        ORDER BY sequenceOrder ASC
    ";
    $stmtR = $db->prepare($sqlResp);
    $stmtR->bindValue(':rid', $recordId, PDO::PARAM_INT);
    $stmtR->execute();
    $respRows = $stmtR->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($respRows)) {
        $grouped = [];
        foreach ($respRows as $row) {
            $cat = $row['categoryName'];
            if (!isset($grouped[$cat])) $grouped[$cat] = [];
            $grouped[$cat][] = [
                'taskDescription' => $row['taskDescription'],
                'value'           => $row['response']
            ];
        }
        $sections = [];
        foreach ($grouped as $cat => $items) {
            $sections[] = ['categoryName' => $cat, 'items' => $items];
        }
        return $sections;
    }

    // Priority 2: checklistJson
    $sections = normaliseChecklistJson($rec['checklistJson']);
    if (!empty($sections)) return $sections;

    // Priority 3: blank template from DB
    $sqlItems = "
        SELECT cc.categoryName, ci.taskDescription
        FROM tbl_maintenance_record   mr
        JOIN tbl_maintenance_schedule ms ON mr.scheduleId      = ms.scheduleId
        JOIN tbl_maintenance_template mt ON ms.equipmentType   = mt.targetTypeId AND mt.isActive = 1
        JOIN tbl_checklist_category   cc ON cc.templateId      = mt.templateId
        JOIN tbl_checklist_item       ci ON ci.categoryId      = cc.categoryId
        WHERE mr.recordId = :rid
        ORDER BY cc.sequenceOrder, ci.sequenceOrder
    ";
    $stmtI = $db->prepare($sqlItems);
    $stmtI->bindValue(':rid', $recordId, PDO::PARAM_INT);
    $stmtI->execute();
    $rows    = $stmtI->fetchAll(PDO::FETCH_ASSOC);
    $grouped = [];
    foreach ($rows as $row) {
        $cat = $row['categoryName'];
        if (!isset($grouped[$cat])) $grouped[$cat] = [];
        $grouped[$cat][] = ['taskDescription' => $row['taskDescription'], 'value' => ''];
    }
    $sections = [];
    foreach ($grouped as $cat => $items) {
        $sections[] = ['categoryName' => $cat, 'items' => $items];
    }
    return $sections;
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
$fmtShort = fn($d) => $d ? date('M d, Y', strtotime($d)) : '—';

$assetDir    = __DIR__ . '/../../public/assets/letterhead/';
$bgWavePath  = $assetDir . 'bg_wave.jpeg';
$sealPath    = $assetDir . 'logo_president.jpeg';
$niaLogoPath = $assetDir . 'logo_nia.jpeg';
$bagongPath  = $assetDir . 'logo_bagong.jpeg';
$isoCertPath = $assetDir . 'iso_cert-with-qr.jpeg';

// ─── PDF class ────────────────────────────────────────────────────────────────
class NIAEmployeeChecklistPDF extends TCPDF {

    public string $bgWavePath  = '';
    public string $sealPath    = '';
    public string $niaLogoPath = '';
    public string $bagongPath  = '';
    public string $isoCertPath = '';

    // ── HEADER ────────────────────────────────────────────────────
    public function Header() {
        $pageW    = $this->getPageWidth();
        $pageH    = $this->getPageHeight();
        $margins  = $this->getMargins();
        $lm       = $margins['left'];
        $rm       = $margins['right'];
        $contentW = $pageW - $lm - $rm;

        $logoSize = 15;
        $logoY    = 7;
        $logoX    = 10;

        if ($this->sealPath && file_exists($this->sealPath)) {
            $this->Image($this->sealPath, $logoX, 11, $logoSize, $logoSize, 'JPEG');
        }

        if ($this->niaLogoPath && file_exists($this->niaLogoPath)) {
            $this->Image($this->niaLogoPath, $logoX + $logoSize + 2, 11, $logoSize, $logoSize, 'JPEG');
        }

        if ($this->bagongPath && file_exists($this->bagongPath)) {   
            $logoSize = 19;
            $logoY    = 5;
            $this->Image($this->bagongPath, $pageW - 10 - $logoSize, 10, $logoSize, $logoSize, 'JPEG');
        }

        $textX = (2 * $logoSize) + 7;
        $textW = $contentW - (2 * $lm) - (2 * $logoSize);

        $this->SetXY($textX, 10);
        $this->SetFont('trajanprob', 'B', 9);
        $this->SetTextColor(0, 0, 0);
        $this->Cell($textW, 4.5, 'Republic of the Philippines', 0, 1, 'L');

        $this->SetX($textX);
        $this->SetFont('trajanpro', '', 8);
        $this->SetTextColor(0, 0, 0);
        $this->Cell($textW, 3.5, 'OFFICE OF THE PRESIDENT', 0, 1, 'L');

        $this->SetX($textX);
        $this->SetFont('trajanprob', 'B', 9);
        $this->Cell($textW, 4.5, 'NATIONAL IRRIGATION ADMINISTRATION', 0, 1, 'L');

        $this->SetX($textX);
        $this->SetFont('trajanpro', '', 8);
        $this->Cell($textW, 3.5, 'UPPER PAMPANGA RIVER INTEGRATED IRRIGATION SYSTEMS', 0, 1, 'L');
    }

    public function Footer() {
        $pageW    = $this->getPageWidth();
        $pageH    = $this->getPageHeight();
        $margins  = $this->getMargins();
        $lm       = $margins['left'];
        $rm       = $margins['right'];
        $contentW = $pageW - $lm - $rm;

        $footerY  = $pageH - 22;

        $isoW = 40;
        $isoH = 0; // auto
        $isoX = $pageW - 10 - $isoW;
        $isoY = $footerY + 1;

        if ($this->bgWavePath && file_exists($this->bgWavePath)) {
            $this->Image($this->bgWavePath, 0, $footerY - 30, $pageW, 0, 'JPEG');
        }
        if ($this->isoCertPath && file_exists($this->isoCertPath)) {
            $this->Image($this->isoCertPath, $isoX, $isoY, $isoW, $isoH, 'JPEG');
        }

        $textW = $contentW - $isoW - 4;

        $this->SetFont('helvetica', 'B', 7);
        $this->SetTextColor(0, 0, 0);
        $this->SetXY(10, $footerY + 1);
        $this->Cell($textW, 4, 'Maharlika Highway, Cabanatuan City, Nueva Ecija, Philippines', 0, 1, 'L');

        $this->SetFont('helvetica', '', 6.5);
        $this->SetX(10);
        $this->Cell($textW, 3.5, 'Direct line No.: (044) 958 9709  •  Telefax No.: (044) 958 9709', 0, 1, 'L');

        $this->SetX(10);
        $this->Cell($textW, 3.5, 'Email: upriis@nia.gov.ph  •  Website: www.upriis.nia.gov.ph  •  TIN: 000916415024', 0, 1, 'L');

        // $this->SetFont('helvetica', '', 5);
        // $this->SetTextColor(0, 0, 0);
        // $this->SetXY(10, $footerY + 14);
        // $this->Cell($textW, 3, 'NIA-UPRIIS-HEAD OFFICE-ICT-MAINTENANCE-REPORT', 0, 0, 'L');

        $this->SetFont('helvetica', '', 6.5);
        $this->SetTextColor(0, 0, 0);
        $this->SetXY($isoX + 10, $footerY + 15);
        $this->Cell($isoW, 3.5, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 0, 'R');
    }
}

// ─── Init PDF ─────────────────────────────────────────────────────────────────
$pdf = new NIAEmployeeChecklistPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->bgWavePath  = $bgWavePath;
$pdf->sealPath    = $sealPath;
$pdf->niaLogoPath = $niaLogoPath;
$pdf->bagongPath  = $bagongPath;
$pdf->isoCertPath = $isoCertPath;

$pdf->SetCreator('NIA UPRIIS ICT System');
$pdf->SetAuthor('NIA UPRIIS');
$pdf->SetTitle('ICT Equipment Checklist Report – ' . $empFullName);
$pdf->SetMargins(15, 32, 15);
$pdf->SetAutoPageBreak(true, 55);
$pdf->SetFont('helvetica', '', 9);

$pageW    = 215.9; // Legal page width in mm
$lm       = 15;
$rm       = 15;
$contentW = $pageW - $lm - $rm;

// ── Column widths for checklist table ─────────────────────────────────────────
$wProc = 42;
$wYes  = 15;
$wNo   = 11;
$wNA   = 11;
$wDesc = $contentW - $wProc - $wYes - $wNo - $wNA;
$thH   = 6.5;
$rowH  = 6.0;

// ─── Draw checklist table header ──────────────────────────────────────────────
$drawHeader = function () use ($pdf, $lm, $wProc, $wDesc, $wYes, $wNo, $wNA, $thH): void {
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.3);
    $pdf->SetX($lm);
    $pdf->Cell($wProc, $thH, 'Maintenance Procedure', 1, 0, 'C', true);
    $pdf->Cell($wDesc, $thH, 'Description',           1, 0, 'C', true);
    $pdf->Cell($wYes,  $thH, 'Yes',                   1, 0, 'C', true);
    $pdf->Cell($wNo,   $thH, 'No',                    1, 0, 'C', true);
    $pdf->Cell($wNA,   $thH, 'N/A',                   1, 1, 'C', true);
};

$checkPageBreak = function (float $neededH) use ($pdf, $drawHeader): void {
    if ($pdf->GetY() + $neededH > $pdf->getPageHeight() - 58) {
        $pdf->AddPage();
        $pdf->SetY(32);
        $drawHeader();
    }
};

// ─── Render each equipment's checklist ────────────────────────────────────────
$equipIndex = 0;
foreach ($equipmentRecords as $entry) {
    $equip = $entry['equipment'];
    $rec   = $entry['record'];
    $equipIndex++;

    // Add new page for each equipment
    $pdf->AddPage();

    // ── Title ─────────────────────────────────────────────────────────────────
    $pdf->SetXY($lm, 32);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell($contentW, 6, 'ICT PREVENTIVE MAINTENANCE', 0, 1, 'C');
    $pdf->SetX($lm);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell($contentW, 5, 'Procedure Checklist', 0, 1, 'C');
    $pdf->Ln(1);

    // ── Equipment counter badge ───────────────────────────────────────────────
    $pdf->SetX($lm);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell($contentW, 4, 'Equipment ' . $equipIndex . ' of ' . count($equipmentRecords), 0, 1, 'C');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(2);

    // ── Info block ────────────────────────────────────────────────────────────
    $colHalf = $contentW / 2;
    $infoRowH = 6;

    $labelVal = function (string $label, string $value, float $x, float $y, float $totalW) use ($pdf, $infoRowH): void {
        $labelW = 36;
        $pdf->SetXY($x, $y);
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->Cell($labelW, $infoRowH, $label, 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 8.5);
        $pdf->Cell($totalW - $labelW, $infoRowH, $value, 'B', 0, 'L');
    };

    $infoY = $pdf->GetY();
    $labelVal('Division/Section/Unit:', $empLocation,                                 $lm,            $infoY,                 $colHalf - 4);
    $labelVal('Employee Name:',         $empFullName,                                 $lm,            $infoY + $infoRowH,     $colHalf - 4);
    $labelVal('Designation:',           $employee['position'] ?? '',                  $lm,            $infoY + $infoRowH * 2, $colHalf - 4);
    $labelVal('Date:',                  $rec ? $fmtShort($rec['maintenanceDate']) : '—', $lm + $colHalf, $infoY,                 $colHalf);
    $labelVal('ICT Equipment Type:',    $equip['type_name'] ?? '',                    $lm + $colHalf, $infoY + $infoRowH,     $colHalf);
    $labelVal('Property No.:',          $equip['serial'] ?? '',                       $lm + $colHalf, $infoY + $infoRowH * 2, $colHalf);
    $pdf->SetY($infoY + $infoRowH * 3 + 5);

    // ── Build checklist sections ──────────────────────────────────────────────
    $checklistSections = buildChecklistSections($db, $rec);

    if (empty($checklistSections)) {
        // No maintenance record — show a notice
        $pdf->SetFont('helvetica', 'I', 9);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetX($lm);
        $pdf->Cell($contentW, 10, 'No maintenance record found for this equipment.', 0, 1, 'C');
        $pdf->SetTextColor(0, 0, 0);
        continue;
    }

    // ── Checklist table ───────────────────────────────────────────────────────
    $drawHeader();

    foreach ($checklistSections as $section) {
        $catName = $section['categoryName'] ?? '';
        $items   = $section['items'] ?? [];
        if (empty($items)) continue;

        $numItems = count($items);
        $blockH   = $numItems * $rowH;

        $checkPageBreak($blockH + 2);

        $blockStartY = $pdf->GetY();

        // Category label cell
        $pdf->SetXY($lm, $blockStartY);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.25);
        $pdf->MultiCell($wProc, $blockH, '  ' . $catName, 1, 'L', false, 0);

        // Item rows
        $pdf->SetFont('helvetica', '', 7.5);
        $currentY = $blockStartY;

        foreach ($items as $item) {
            $desc    = $item['taskDescription'] ?? '';
            $val     = $item['value'] ?? '';

            $markYes = ($val === 'Yes') ? chr(52) : '';
            $markNo  = ($val === 'No')  ? chr(52) : '';
            $markNA  = ($val === 'N/A') ? chr(52) : '';

            $pdf->SetXY($lm + $wProc, $currentY);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('helvetica', '', 7.5);
            $pdf->Cell($wDesc, $rowH, '  ' . $desc, 1, 0, 'L', false);

            $pdf->SetFont('ZapfDingbats', '', 9);
            $pdf->Cell($wYes, $rowH, $markYes, 1, 0, 'C', false);
            $pdf->Cell($wNo,  $rowH, $markNo,  1, 0, 'C', false);
            $pdf->Cell($wNA,  $rowH, $markNA,  1, 0, 'C', false);

            $currentY += $rowH;
            $pdf->SetY($currentY);
        }
    }

    // ── Remarks / Recommendations ─────────────────────────────────────────────
    $remarksH = 16;
    $checkPageBreak($remarksH + 2);
    $pdf->SetX($lm);
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->Cell($wProc, $remarksH, "Remarks/\nRecommendations", 'LRB', 0, 'C', false);
    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->MultiCell($contentW - $wProc, $remarksH, '  ' . ($rec['remarks'] ?? ''), 'LRB', 'L', false, 1);

    // ── Signatories ───────────────────────────────────────────────────────────
    $signatories = [];
    if (!empty($rec['signatories_json'])) {
        $sig = json_decode($rec['signatories_json'], true);
        if (is_array($sig)) $signatories = $sig;
    }

    if ($pdf->GetY() + 48 > $pdf->getPageHeight() - 55) {
        $pdf->AddPage();
        $pdf->SetY(36);
    }

    $preparedName  = trim($_SESSION['full_name'] ?? $_SESSION['user_name'] ?? $rec['preparedBy'] ?? '');
    $preparedTitle = 'ICT Staff';

    $endUserName   = trim($empFullName);
    $endUserTitle  = trim($employee['position'] ?? '');

    $checkedName   = trim($rec['checkedBy'] ?? ($signatories['verifiedByName'] ?? ''));
    $checkedTitle  = trim($signatories['verifiedByTitle'] ?? 'Sr. Supply Officer');

    $notedName     = trim($rec['notedBy'] ?? ($signatories['notedByName'] ?? ''));
    $notedTitle    = trim($signatories['notedByTitle'] ?? 'Division Manager, AdFin');

    drawChecklistSignatories(
        $pdf,
        $lm,
        $contentW,
        $preparedName,
        $preparedTitle,
        $endUserName,
        $endUserTitle,
        $checkedName,
        $checkedTitle,
        $notedName,
        $notedTitle
    );
}

function drawChecklistSignatories(
    TCPDF $pdf,
    float $lm,
    float $contentW,
    string $preparedName,
    string $preparedTitle,
    string $endUserName,
    string $endUserTitle,
    string $checkedName,
    string $checkedTitle,
    string $notedName,
    string $notedTitle
): void {
    $pageH = $pdf->getPageHeight();

    if ($pdf->GetY() + 52 > $pageH - 45) {
        $pdf->AddPage();
        $pdf->SetY(36);
    }

    $startY = $pdf->GetY() + 10;

    $leftW  = $contentW * 0.48;
    $rightW = $contentW * 0.48;
    $gap    = $contentW * 0.04;

    $leftX  = $lm;
    $rightX = $lm + $leftW + $gap;

    $lineW  = 62;
    $labelH = 4;
    $nameH  = 4;
    $titleH = 4;
    $blockGap = 12;

    $leftLineX  = $leftX + 2;
    $rightLineX = $rightX + ($rightW - $lineW - 2);

    // LEFT COLUMN
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetXY($leftX, $startY);
    $pdf->Cell($leftW, $labelH, 'Prepared/Conducted By:', 0, 1, 'L');

    $lineY = $startY + 9;
    $pdf->Line($leftLineX, $lineY, $leftLineX + $lineW, $lineY);

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetXY($leftLineX, $lineY - 3.5);
    $pdf->Cell($lineW, $nameH, $preparedName, 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->SetXY($leftLineX, $lineY + 1.5);
    $pdf->Cell($lineW, $titleH, $preparedTitle, 0, 1, 'C');

    $endUserLabelY = $lineY + $blockGap;
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetXY($leftX, $endUserLabelY);
    $pdf->Cell($leftW, $labelH, 'End User:', 0, 1, 'L');

    $endUserLineY = $endUserLabelY + 9;
    $pdf->Line($leftLineX, $endUserLineY, $leftLineX + $lineW, $endUserLineY);

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetXY($leftLineX, $endUserLineY - 3.5);
    $pdf->Cell($lineW, $nameH, $endUserName, 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->SetXY($leftLineX, $endUserLineY + 1.5);
    $pdf->Cell($lineW, $titleH, $endUserTitle, 0, 1, 'C');

    // RIGHT COLUMN
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetXY($rightX, $startY);
    $pdf->Cell($rightW, $labelH, 'Checked By:', 0, 1, 'L');

    $rightLineY = $startY + 9;
    $pdf->Line($rightLineX, $rightLineY, $rightLineX + $lineW, $rightLineY);

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetXY($rightLineX, $rightLineY - 3.5);
    $pdf->Cell($lineW, $nameH, $checkedName, 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->SetXY($rightLineX, $rightLineY + 1.5);
    $pdf->Cell($lineW, $titleH, $checkedTitle, 0, 1, 'C');

    $notedLabelY = $rightLineY + $blockGap;
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetXY($rightX, $notedLabelY);
    $pdf->Cell($rightW, $labelH, 'Noted By:', 0, 1, 'L');

    $notedLineY = $notedLabelY + 9;
    $pdf->Line($rightLineX, $notedLineY, $rightLineX + $lineW, $notedLineY);

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetXY($rightLineX, $notedLineY - 3.5);
    $pdf->Cell($lineW, $nameH, $notedName, 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->SetXY($rightLineX, $notedLineY + 1.5);
    $pdf->Cell($lineW, $titleH, $notedTitle, 0, 1, 'C');

    $pdf->SetY(max($endUserLineY, $notedLineY) + 10);
}

// ─── Output ───────────────────────────────────────────────────────────────────
$safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $empFullName);
$filename = 'Employee_Checklist_Report_' . $safeName . '_' . date('Y-m-d') . '.pdf';
logActivity(ACTION_EXPORT, MODULE_REPORTS, "Exported employee checklist report PDF for {$empFullName} (ID: {$employeeId})");
$pdf->Output($filename, 'I');