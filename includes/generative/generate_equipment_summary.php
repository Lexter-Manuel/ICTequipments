<?php
/**
 * generate_equipment_summary.php
 * TCPDF report: Equipment Summary (mirrors the dashboard equipment-summary page)
 */
require_once __DIR__ . '/../../vendor/TCPDF/tcpdf.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';

$db = getDB();

// =====================================================================
//  FILTERS  (passed via query string from the summary page)
// =====================================================================
$filterDivision = isset($_GET['division']) ? trim($_GET['division']) : '';
$filterEqType   = isset($_GET['eq_type'])  ? trim($_GET['eq_type'])  : '';
$filterYear     = isset($_GET['year'])     ? trim($_GET['year'])     : '';

// Build subtitle from active filters
$filterLabels = [];
if ($filterDivision !== '') {
    $stmt = $db->prepare("SELECT location_name FROM location WHERE location_id = ?");
    $stmt->execute([$filterDivision]);
    $divName = $stmt->fetchColumn();
    if ($divName) $filterLabels[] = 'Division: ' . $divName;
}
if ($filterEqType !== '') {
    $stmt = $db->prepare("SELECT typeName FROM tbl_equipment_type_registry WHERE typeId = ?");
    $stmt->execute([$filterEqType]);
    $etName = $stmt->fetchColumn();
    if ($etName) $filterLabels[] = 'Type: ' . $etName;
}
if ($filterYear !== '') $filterLabels[] = 'Year: ' . $filterYear;
$filterSubtitle = !empty($filterLabels) ? implode('  |  ', $filterLabels) : '';

// =====================================================================
//  DATA QUERIES  (shared with modules/reports/equipment-summary.php)
// =====================================================================
require_once __DIR__ . '/../queries/equipment_summary_queries.php';

// =====================================================================
//  HELPERS
// =====================================================================
$reportDate = date('F d, Y');

$assetDir    = __DIR__ . '/../../public/assets/letterhead/';
$bgWavePath  = $assetDir . 'bg_wave.png';
$sealPath    = $assetDir . 'logo_president.png';
$niaLogoPath = $assetDir . 'logo_nia.png';
$bagongPath  = $assetDir . 'logo_bagong.png';
$isoCertPath = $assetDir . 'iso_cert-with-qr.png';

// =====================================================================
//  PDF CLASS  (reuse NIA letterhead)
// =====================================================================
class NIAEquipPDF extends TCPDF {

    public $bgWavePath  = '';
    public $sealPath    = '';
    public $niaLogoPath = '';
    public $bagongPath  = '';
    public $isoCertPath = '';

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
            $this->Image($this->sealPath, $logoX, 11, $logoSize, $logoSize, 'PNG');
        }

        if ($this->niaLogoPath && file_exists($this->niaLogoPath)) {
            $this->Image($this->niaLogoPath, $logoX + $logoSize + 2, 11, $logoSize, $logoSize, 'PNG');
        }

        if ($this->bagongPath && file_exists($this->bagongPath)) {   
            $logoSize = 19;
            $logoY    = 5;
            $this->Image($this->bagongPath, $pageW - 10 - $logoSize, 10, $logoSize, $logoSize, 'PNG');
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
            $this->Image($this->bgWavePath, 0, $footerY - 30, $pageW, 0, 'PNG');
        }
        if ($this->isoCertPath && file_exists($this->isoCertPath)) {
            $this->Image($this->isoCertPath, $isoX, $isoY, $isoW, $isoH, 'PNG');
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
//  REUSABLE DRAWING HELPERS
// =====================================================================

/**
 * Clean government-style section heading — bold uppercase label with a
 * full-width hairline rule underneath.  No coloured accents.
 */
function sectionHeading($pdf, $lm, $contentW, $title) {
    $y = $pdf->GetY();
    $pdf->SetXY($lm, $y);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($contentW, 6, strtoupper($title), 0, 1, 'L');

    // thin horizontal rule
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.3);
    $pdf->Line($lm, $pdf->GetY(), $lm + $contentW, $pdf->GetY());
    $pdf->Ln(3);
}
/**
 * Data table — header and rows styled to match generate_maintenance_history.php:
 * white fill, black text, black borders on header; alternating white/light-gray rows.
 */
function drawTable($pdf, $lm, $contentW, $cols, $rows, $showFooter = null) {
    $thH     = 7;
    $lineH   = 3.8;
    $minRowH = 6;
    $cellPad = 1.0;

    $drawHeader = function() use ($pdf, $cols, $lm, $thH) {
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

    $drawHeader();

    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetDrawColor(0, 0, 0);

    foreach ($rows as $i => $rowCells) {
        $maxH = $minRowH;
        foreach ($rowCells as $ci => $text) {
            $innerW   = $cols[$ci]['w'] - ($cellPad * 2);
            $numLines = $pdf->getNumLines((string)$text, $innerW);
            $h        = ($numLines * $lineH) + ($cellPad * 2);
            if ($h > $maxH) $maxH = $h;
        }

        $breakMargin = $pdf->getBreakMargin();
        if ($pdf->GetY() + $maxH > ($pdf->getPageHeight() - $breakMargin)) {
            $pdf->AddPage();
            $pdf->SetY(32);
            $drawHeader();
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

        foreach ($rowCells as $ci => $text) {
            $colW  = $cols[$ci]['w'];
            $align = $cols[$ci]['align'] ?? 'L';

            $pdf->SetFillColor($fillR, $fillG, $fillB);
            $pdf->Rect($x, $rowY, $colW, $maxH, 'DF');

            $pdf->SetXY($x + $cellPad, $rowY + $cellPad);
            $pdf->SetTextColor(30, 30, 30);
            $pdf->SetFont('helvetica', '', 7);
            $pdf->MultiCell($colW - ($cellPad * 2), $lineH, (string)$text, 0, $align, false, 0);
            $x += $colW;
        }
        $pdf->SetY($rowY + $maxH);
    }

    if ($showFooter) {
        $footY = $pdf->GetY();
        $x = $lm;
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetDrawColor(0, 0, 0);
        foreach ($showFooter as $ci => $text) {
            $colW = $cols[$ci]['w'];
            $pdf->SetXY($x, $footY);
            $pdf->Cell($colW, $minRowH, (string)$text, 1, 0, $cols[$ci]['align'] ?? 'L', true);
            $x += $colW;
        }
        $pdf->Ln($minRowH);
    }
}

// =====================================================================
//  INITIALIZE PDF
// =====================================================================
$pdf = new NIAEquipPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->bgWavePath  = $bgWavePath;
$pdf->sealPath    = $sealPath;
$pdf->niaLogoPath = $niaLogoPath;
$pdf->bagongPath  = $bagongPath;
$pdf->isoCertPath = $isoCertPath;

$pdf->SetCreator('NIA UPRIIS ICT System');
$pdf->SetAuthor('NIA UPRIIS');
$pdf->SetTitle('EQUIPMENT SUMMARY REPORT');
$pdf->SetMargins(20, 32, 20);
$pdf->SetAutoPageBreak(true, 40);
$pdf->SetFont('helvetica', '', 10);
$pdf->AddPage();

$pageW    = $pdf->getPageWidth();
$lm       = 20;
$contentW = $pageW - 40;

// =====================================================================
//  TITLE
// =====================================================================
$pdf->SetXY($lm, 32);
$pdf->SetFont('helvetica', 'B', 13);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell($contentW, 8, 'EQUIPMENT INVENTORY SUMMARY REPORT', 0, 1, 'C');

if ($filterSubtitle !== '') {
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor(80, 80, 80);
    $pdf->Cell($contentW, 5, $filterSubtitle, 0, 1, 'C');
}

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell($contentW, 5, $reportDate, 0, 1, 'R');
$pdf->Ln(3);

// =====================================================================
//  EQUIPMENT BY DIVISION
// =====================================================================
sectionHeading($pdf, $lm, $contentW, 'Equipment by Division');

$divCols = [
    ['w' => 50, 'label' => 'Division',    'align' => 'L'],
    ['w' => 22, 'label' => 'System Units','align' => 'C'],
    ['w' => 22, 'label' => 'Monitors',    'align' => 'C'],
    ['w' => 22, 'label' => 'Printers',    'align' => 'C'],
    ['w' => 22, 'label' => 'AIO',         'align' => 'C'],
];
$usedW = 0;
foreach ($divCols as $c) $usedW += $c['w'];
$divCols[] = ['w' => $contentW - $usedW, 'label' => 'Total', 'align' => 'C'];

$divRows = [];
$gTotal  = 0;
foreach ($byDivision as $div) {
    $dTotal = $div['system_units'] + $div['monitors'] + $div['printers'] + $div['allinones'];
    $gTotal += $dTotal;
    $divRows[] = [$div['division'], $div['system_units'], $div['monitors'], $div['printers'], $div['allinones'], $dTotal];
}

$footerRow = ['Grand Total', '', '', '', '', $gTotal];
drawTable($pdf, $lm, $contentW, $divCols, $divRows, $footerRow);
$pdf->Ln(6);

// =====================================================================
//  EQUIPMENT HEALTH — Condition Rating
// =====================================================================
sectionHeading($pdf, $lm, $contentW, 'Equipment Health — Condition Rating');

if (!empty($conditionSummary)) {
    $condCols = [
        ['w' => 40, 'label' => 'Condition',  'align' => 'L'],
        ['w' => 30, 'label' => 'Count',      'align' => 'C'],
        ['w' => 30, 'label' => '% of Total', 'align' => 'C'],
    ];
    $condTot  = array_sum(array_column($conditionSummary, 'cnt'));
    $condRows = [];
    foreach ($conditionSummary as $c) {
        $pct = $condTot > 0 ? round(($c['cnt'] / $condTot) * 100, 1) : 0;
        $condRows[] = [$c['conditionRating'], $c['cnt'], $pct . '%'];
    }
    drawTable($pdf, $lm, $contentW, $condCols, $condRows);
} else {
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell($contentW, 6, 'No maintenance condition data available.', 0, 1, 'C');
}
$pdf->Ln(4);

// ── Overall Status ──
sectionHeading($pdf, $lm, $contentW, 'Equipment Health — Overall Status');

if (!empty($statusSummary)) {
    $statCols = [
        ['w' => 50, 'label' => 'Status', 'align' => 'L'],
        ['w' => 30, 'label' => 'Count',  'align' => 'C'],
    ];
    $statRows = [];
    foreach ($statusSummary as $s) {
        $statRows[] = [$s['overallStatus'], $s['cnt']];
    }
    drawTable($pdf, $lm, $contentW, $statCols, $statRows);
} else {
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell($contentW, 6, 'No maintenance status data available.', 0, 1, 'C');
}
$pdf->Ln(6);

// =====================================================================
//  ACQUISITION TIMELINE
// =====================================================================
if ($pdf->GetY() + 40 > $pdf->getPageHeight() - 50) {
    $pdf->AddPage();
    $pdf->SetY(32);
}

sectionHeading($pdf, $lm, $contentW, 'Acquisition Timeline');

if (!empty($byYear)) {
    $yrCols = [
        ['w' => 40, 'label' => 'Year Acquired', 'align' => 'C'],
        ['w' => 30, 'label' => 'Count',          'align' => 'C'],
        ['w' => 30, 'label' => '% of Total',     'align' => 'C'],
    ];
    $yrRows = [];
    foreach ($byYear as $yr) {
        $pct = $total > 0 ? round(($yr['total'] / $total) * 100, 1) : 0;
        $yrRows[] = [$yr['year_acquired'], $yr['total'], $pct . '%'];
    }
    drawTable($pdf, $lm, $contentW, $yrCols, $yrRows);
} else {
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell($contentW, 6, 'No acquisition year data available.', 0, 1, 'C');
}
$pdf->Ln(6);

// =====================================================================
//  SOFTWARE LICENSE SUMMARY
// =====================================================================
if ($pdf->GetY() + 30 > $pdf->getPageHeight() - 50) {
    $pdf->AddPage();
    $pdf->SetY(32);
}

sectionHeading($pdf, $lm, $contentW, 'Software License Summary');

$swCols = [
    ['w' => 50, 'label' => 'Category', 'align' => 'L'],
    ['w' => 30, 'label' => 'Count',    'align' => 'C'],
];
$swRows = [
    ['Total Licenses',     $softTotal],
    ['Perpetual',           $softPerpetual],
    ['Expiring (next 90d)', $softExpiring],
    ['Expired',             $softExpired],
];
drawTable($pdf, $lm, $contentW, $swCols, $swRows);

// =====================================================================
//  SIGNATURE BLOCK
// =====================================================================
$sigY = $pdf->GetY() + 8;
if ($sigY + 35 > $pdf->getPageHeight() - 55) {
    $pdf->AddPage();
    $sigY = 42;
}

$halfW   = $contentW / 2;
$lineStr = '______________________________';

$pdf->SetFont('helvetica', 'B', 9);
$lineW = $pdf->GetStringWidth($lineStr);

$pdf->SetXY($lm, $sigY);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell($halfW, 4, 'Prepared by:', 0, 0, 'L');
$pdf->SetX($lm + 49);
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
$pdf->SetX($lm + 38);
$pdf->Cell($contentW, 4, 'Noted by:', 0, 1, 'C');

$pdf->Ln(8);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetX($lm);
$pdf->Cell($contentW, 4, $lineStr, 0, 1, 'R');

$pdf->SetFont('helvetica', 'I', 7);
$pdf->SetX($lm + $contentW - $lineW);
$pdf->Cell($lineW, 4, 'Division Manager, AdFin', 0, 1, 'C');

// =====================================================================
//  OUTPUT PDF
// =====================================================================
$filename = 'Equipment_Summary_Report_' . date('Y-m-d') . '.pdf';
logActivity(ACTION_EXPORT, MODULE_REPORTS, "Exported equipment summary report PDF");
$pdf->Output($filename, 'I');