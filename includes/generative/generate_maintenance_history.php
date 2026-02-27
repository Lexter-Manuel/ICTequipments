<?php

require_once __DIR__ . '/../../vendor/TCPDF/tcpdf.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';

// ─── Inputs ──────────────────────────────────────────────────────
$db         = getDB();
$dateFrom   = trim($_GET['dateFrom']   ?? '');
$dateTo     = trim($_GET['dateTo']     ?? '');
$rangeLabel = trim($_GET['rangeLabel'] ?? '');   // human-readable label sent by JS
$search     = $_GET['search'] ?? '';
$sectionUnit = $_GET['sectionUnit'] ?? '';

// Fallback: if rangeLabel was not supplied, build a basic one
if ($rangeLabel === '') {
    if ($dateFrom === '' && $dateTo === '') {
        $rangeLabel = 'All Time';
    } elseif ($dateFrom === $dateTo) {
        $rangeLabel = $dateFrom;
    } else {
        $rangeLabel = ($dateFrom ?: '(start)') . ' – ' . ($dateTo ?: '(end)');
    }
}
/**
 * Build a SQL date condition and populate $bindings with named params.
 *
 * @param string  $dateFrom   YYYY-MM-DD or empty
 * @param string  $dateTo     YYYY-MM-DD or empty
 * @param array   &$bindings  Receives named params when needed
 * @param string  $alias      Column alias prefix (default 'mr')
 * @return string             SQL fragment (no leading AND)
 */
function buildDateCond(string $dateFrom, string $dateTo, array &$bindings, string $alias = 'mr'): string {
    if ($dateFrom === '' && $dateTo === '') {
        return '1=1'; // All Time
    }
    if ($dateFrom !== '' && $dateTo !== '') {
        $bindings[':dateFrom'] = $dateFrom;
        $bindings[':dateTo']   = $dateTo;
        return "DATE($alias.maintenanceDate) BETWEEN :dateFrom AND :dateTo";
    }
    if ($dateFrom !== '') {
        $bindings[':dateFrom'] = $dateFrom;
        return "DATE($alias.maintenanceDate) >= :dateFrom";
    }
    $bindings[':dateTo'] = $dateTo;
    return "DATE($alias.maintenanceDate) <= :dateTo";
}

// ─── Build WHERE clause ──────────────────────────────────────────
$dateBindings = [];
$dateCond     = buildDateCond($dateFrom, $dateTo, $dateBindings);
$whereParts   = [$dateCond];
$params       = $dateBindings;

if ($search) {
    $whereParts[] = "(v.serial LIKE :s OR v.brand LIKE :s OR mr.preparedBy LIKE :s OR mr.remarks LIKE :s OR v.owner_name LIKE :s)";
    $params[':s'] = "%$search%";
}
if ($sectionUnit) {
    $whereParts[]             = "v.location_name = :sectionUnit";
    $params[':sectionUnit']   = $sectionUnit;
}
$where = implode(' AND ', $whereParts);

// ─── Records query ───────────────────────────────────────────────
$sql = "
    SELECT 
        mr.recordId,
        mr.maintenanceDate,
        mr.overallStatus,
        mr.conditionRating,
        mr.remarks,
        mr.preparedBy AS technician,
        mr.checkedBy,
        mr.notedBy,
        v.brand,
        v.serial,
        v.type_name,
        v.location_name,
        v.owner_name
    FROM tbl_maintenance_record mr
    LEFT JOIN tbl_maintenance_schedule ms ON mr.scheduleId = ms.scheduleId
    LEFT JOIN view_maintenance_master v ON ms.equipmentId = v.id AND ms.equipmentType = v.type_id
    WHERE $where
    ORDER BY mr.maintenanceDate DESC, v.location_name ASC, v.type_name ASC
";
$stmt = $db->prepare($sql);
foreach ($params as $k => $val) $stmt->bindValue($k, $val);
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ─── Helpers ─────────────────────────────────────────────────────
$reportDate  = date('F d, Y');
$reportMonth = date('F, Y');
$e = fn($v) => htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
$fmtDate = fn($d) => $d ? date('M d, Y', strtotime($d)) : '';

$assetDir     = __DIR__ . '/../../public/assets/letterhead/';
$bgWavePath   = $assetDir . 'bg_wave.jpeg';
$sealPath     = $assetDir . 'logo_president.jpeg';
$niaLogoPath  = $assetDir . 'logo_nia.jpeg';
$bagongPath   = $assetDir . 'logo_bagong.jpeg';
$isoCertPath  = $assetDir . 'iso_cert-with-qr.jpeg';


class NIAReportPDF extends TCPDF {

    public $bgWavePath   = '';
    public $sealPath     = '';
    public $niaLogoPath  = '';
    public $bagongPath   = '';
    public $isoCertPath  = '';

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

        $this->SetFont('helvetica', '', 6.5);
        $this->SetTextColor(0, 0, 0);
        $this->SetXY($isoX + 10, $footerY + 15);
        $this->Cell($isoW, 3.5, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 0, 'R');
    }
}

// =====================================================================
//  INITIALIZE PDF
// =====================================================================
$pdf = new NIAReportPDF('P', 'mm', 'A4', true, 'UTF-8', false);

$pdf->bgWavePath  = $bgWavePath;
$pdf->sealPath    = $sealPath;
$pdf->niaLogoPath = $niaLogoPath;
$pdf->bagongPath  = $bagongPath;
$pdf->isoCertPath = $isoCertPath;

$pdf->SetCreator('NIA UPRIIS ICT System');
$pdf->SetAuthor('NIA UPRIIS');
$pdf->SetTitle('PREVENTIVE MAINTENANCE REPORT');
$pdf->SetMargins(20, 32, 20);
$pdf->SetAutoPageBreak(true, 43);
$pdf->SetFont('helvetica', '', 10);
$pdf->AddPage();

$pageW    = $pdf->getPageWidth();
$lm       = 20;
$rm       = 20;
$contentW = $pageW - $lm - $rm;
$y        = 32;

$pdf->SetXY($lm, $y);
$pdf->SetFont('helvetica', 'B', 13);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell($contentW, 8, 'PREVENTIVE MAINTENANCE RECORD REPORT', 0, 1, 'C');

// Date line (right-aligned, underlined)
$pdf->SetXY($lm, $pdf->GetY() + 1);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell($contentW, 5, $reportDate, 0, 0, 'R');

$pdf->setX($lm);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(12, 5, 'For:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, $sectionUnit ?: 'All Sections/Units', 0, 1, 'L');

if ($dateFrom && $dateTo && $dateFrom !== $dateTo) {
    $pdf->SetX($lm);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(12, 5, 'From:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(28, 5, date('F d, Y', strtotime($dateFrom)), 0, 0, 'L');
    $pdf->ln();
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(12, 5, 'To:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(0, 5, date('F d, Y', strtotime($dateTo)), 0, 1, 'L');
}

if ($search) {
    $pdf->SetX($lm);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(20, 5, 'Search:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(0, 5, $search, 0, 1, 'L');
}

$pdf->Ln(4);

// =====================================================================
//  DATA TABLE
// =====================================================================
if (empty($records)) {
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell($contentW, 8, 'No maintenance records found for the selected filters.', 0, 1, 'C');
} else {

    $cols = [
        ['w' => 10,  'label' => '#',           'align' => 'C'],
        ['w' => 18,  'label' => 'Date',         'align' => 'C'],
        ['w' => 22,  'label' => 'Equipment',    'align' => 'L'],
    ];
    if (!$sectionUnit) {
        $cols[] = ['w' => 18,  'label' => 'Location',     'align' => 'L'];
    }
    $cols = array_merge($cols, [
        ['w' => 18,  'label' => 'Assigned To',  'align' => 'L'],
        ['w' => 16,  'label' => 'Technician',   'align' => 'L'],
        ['w' => 15,  'label' => 'Condition',    'align' => 'C'],
        ['w' => 19,  'label' => 'Status',       'align' => 'C'],
    ]);
    $usedW = 0;
    foreach ($cols as $c) $usedW += $c['w'];
    $cols[] = ['w' => $contentW - $usedW, 'label' => 'Remarks', 'align' => 'L'];

    $thH      = 7;
    $lineH    = 3.8;
    $minRowH  = 6;
    $cellPad  = 1.0;

    $drawTableHeader = function() use ($pdf, $cols, $lm, $thH) {
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetDrawColor(0, 0, 0);

        $x = $lm;
        $y = $pdf->GetY();
        foreach ($cols as $col) {
            $pdf->SetXY($x, $y);
            $pdf->Cell($col['w'], $thH, $col['label'], 1, 0, 'C', true);
            $x += $col['w'];
        }
        $pdf->Ln($thH);
    };

    $drawTableHeader();

    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetDrawColor(0, 0, 0);

    foreach ($records as $i => $r) {
        $equipText   = ($r['brand'] ?? '') . ' (' . ($r['type_name'] ?? '') . ')';
        $remarksText = $r['remarks'] ?? '';

        $cellTexts = [
            (string)($i + 1),
            $fmtDate($r['maintenanceDate']),
            $equipText,
        ];
        if (!$sectionUnit) {
            $cellTexts[] = $r['location_name'] ?? '';
        }
        $cellTexts = array_merge($cellTexts, [
            $r['owner_name'] ?? '',
            $r['technician'] ?? '',
            $r['conditionRating'] ?? '',
            $r['overallStatus'] ?? '',
            $remarksText,
        ]);

        $pdf->SetFont('helvetica', '', 7);
        $maxH = $minRowH;
        foreach ($cellTexts as $ci => $text) {
            $innerW   = $cols[$ci]['w'] - ($cellPad * 2);
            $numLines = $pdf->getNumLines($text, $innerW);
            $h        = ($numLines * $lineH) + ($cellPad * 2);
            if ($h > $maxH) $maxH = $h;
        }
        $breakMargin = $pdf->getBreakMargin(); // gets value from SetAutoPageBreak

        if ($pdf->GetY() + $maxH > ($pdf->getPageHeight() - $breakMargin)) {
            $pdf->AddPage();
            $pdf->SetY(32);
            $drawTableHeader();
            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetDrawColor(0, 0, 0);
        }

        if ($i % 2 === 0) {
            $fillR = 255; $fillG = 255; $fillB = 255;
        } else {
            $fillR = 245; $fillG = 245; $fillB = 245;
        }

        $rowY = $pdf->GetY();
        $x    = $lm;

        foreach ($cellTexts as $ci => $text) {
            $colW  = $cols[$ci]['w'];
            $align = $cols[$ci]['align'];

            $pdf->SetFillColor($fillR, $fillG, $fillB);
            $pdf->Rect($x, $rowY, $colW, $maxH, 'DF');

            $pdf->SetXY($x + $cellPad, $rowY + $cellPad);
            $pdf->SetTextColor(30, 30, 30);
            $pdf->SetFont('helvetica', '', 7);
            $pdf->MultiCell(
                $colW - ($cellPad * 2),
                $lineH,
                $text,
                0,
                $align,
                false,
                0
            );

            $x += $colW;
        }

        $pdf->SetY($rowY + $maxH);
    }
}

// =====================================================================
//  SIGNATURE BLOCK
// =====================================================================
$sigY = $pdf->GetY() + 5;

if ($sigY + 35 > $pdf->getPageHeight() - 55) {
    $pdf->AddPage();
    $sigY = 42;
}

$halfW = $contentW / 2;

$pdf->SetFont('helvetica', 'B', 9);
$lineStr = '______________________________';
$lineW   = $pdf->GetStringWidth($lineStr);

$pdf->SetXY($lm, $sigY);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell($halfW, 4, 'Prepared by:', 0, 0, 'L');
$pdf->SetX($rm + 49);
$pdf->Cell($halfW, 5, 'Checked by:', 0, 1, 'R');

$pdf->Ln(8);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetX($lm);
$pdf->Cell($halfW, 4, $lineStr, 0, 0, 'L');
$pdf->Cell($halfW, 5, $lineStr, 0, 1, 'R');

$pdf->SetFont('helvetica', 'I', 7);
$pdf->SetX($lm);
$pdf->Cell($lineW, 4, 'ICT Personnel', 0, 0, 'C');
$pdf->SetX($lm + $contentW - $lineW);
$pdf->Cell($lineW, 4, 'Sr. Supply Officer', 0, 1, 'C');

$pdf->Ln(8);

$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetX($rm + 38);
$pdf->Cell($contentW, 4, 'Noted by:', 0, 1, 'C');

$pdf->Ln(8);

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetX($rm);
$pdf->Cell($contentW, 4, $lineStr, 0, 1, 'R');

$pdf->SetFont('helvetica', 'I', 7);
$pdf->SetX($lm + $contentW - $lineW);
$pdf->Cell($lineW, 4, 'Division Manager, AdFin', 0, 1, 'C');

// =====================================================================
//  OUTPUT PDF
// =====================================================================
$filename = 'Maintenance_History_Report_' . date('Y-m-d') . '.pdf';
logActivity(ACTION_EXPORT, MODULE_REPORTS, "Exported maintenance history report PDF ({$rangeLabel})");
$pdf->Output($filename, 'I');