<?php
session_start();
require_once '../../includes/config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'technician') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../controllers/TechnicianController.php';

$conn = getDBConnection();
$controller = new TechnicianController($conn);
$details = $controller->getDetailedStats();

$lines = [];
$lines[] = 'Technician Statistics Summary';
$lines[] = 'Generated: ' . date('M j, Y g:i A');
$lines[] = '';
$lines[] = 'Jobs:';
$lines[] = '  Total Jobs: ' . $details['services']['total'];
$lines[] = '  Pending: ' . $details['services']['pending'];
$lines[] = '  Active: ' . $details['services']['active'];
$lines[] = '  Completed: ' . $details['services']['completed'];
$lines[] = '  Cancelled/Rejected: ' . $details['services']['cancelled'];
$lines[] = '  Success Rate: ' . $details['services']['success_rate'] . '%';
$lines[] = '';
$lines[] = 'Payments:';
$lines[] = '  Total Payments: ' . $details['payments']['total_payments'];
$lines[] = '  Pending Payments: ' . $details['payments']['pending_payments'];
$lines[] = '  Verified Payments: ' . $details['payments']['verified_payments'];
$lines[] = '  Total Amount Earned: ETB ' . number_format($details['payments']['total_amount_earned'], 2);
$lines[] = '';
$lines[] = 'Messages:';
$lines[] = '  Total Messages: ' . $details['messages']['total'];
$lines[] = '  Unread Messages: ' . $details['messages']['unread'];

function pdf_escape($text) {
    $text = str_replace('\\', '\\\\', $text);
    $text = str_replace('(', '\\(', $text);
    $text = str_replace(')', '\\)', $text);
    return $text;
}

$contentStream = '';
$y = 760;
foreach($lines as $line) {
    $contentStream .= sprintf("BT /F1 12 Tf 72 %.2f Td (%s) Tj ET\n", $y, pdf_escape($line));
    $y -= 16;
}
$length = strlen($contentStream);

$objects = [];
$objects[] = "1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj\n";
$objects[] = "2 0 obj<< /Type /Pages /Count 1 /Kids [3 0 R] >>endobj\n";
$objects[] = "3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>endobj\n";
$objects[] = "4 0 obj<< /Length {$length} >>stream\n" . $contentStream . "endstream\nendobj\n";
$objects[] = "5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj\n";

$pdf = "%PDF-1.4\n";
$offsets = [];
foreach($objects as $obj) {
    $offsets[] = strlen($pdf);
    $pdf .= $obj;
}
$xrefStart = strlen($pdf);
$pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
$pdf .= "0000000000 65535 f \n";
foreach($offsets as $offset) {
    $pdf .= sprintf("%010d 00000 n \n", $offset);
}
$pdf .= "trailer<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefStart}\n%%EOF";

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="technician_statistics.pdf"');
header('Content-Length: ' . strlen($pdf));
echo $pdf;
exit;
