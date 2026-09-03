<?php
declare(strict_types=1);

// Include the main.php file
include 'main.php';
// Check if the user is logged in, if not then redirect to login page
check_loggedin($con);

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Reuse the same search filter as listartworks.php so the export matches what the user is looking at
$searchstring = "";
if (!empty($_GET['searchstring'])) {
    $searchstring = $_GET['searchstring'];
    if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' .-\/]*$/", $searchstring)) {
        $searchstring = "";
    }
}

// Returns just the file name from a URL, decoded (e.g. "Amsel%2CGalia_Mistral.jpg" -> "Amsel,Galia_Mistral.jpg")
// Deliberately avoids parse_url()/PHP_URL_PATH: a handful of stored URLs contain a literal,
// un-encoded '#' (e.g. ".../Thornley,%20Geoff_Construction%20#13%20 1981.jpg"), which parse_url
// treats as a URL fragment delimiter and truncates the path there.
function url_file_name(?string $url): string {
    if (!$url) {
        return '';
    }
    $withoutQuery = explode('?', $url, 2)[0];
    $slashPos = strrpos($withoutQuery, '/');
    if ($slashPos === false) {
        return '';
    }
    return rawurldecode(substr($withoutQuery, $slashPos + 1));
}

$stmt = $con->prepare("SELECT * FROM artwork_attributes_view WHERE (
    artist LIKE CONCAT('%', ?, '%') OR
    title LIKE CONCAT('%', ?, '%') OR
    location LIKE CONCAT('%', ?, '%'))
    ORDER BY title");
$stmt->bind_param("sss", $searchstring, $searchstring, $searchstring);
$stmt->execute();
$result = $stmt->get_result();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Artwork');

$headers = ['Title', 'Artist', 'Location', 'Medium', 'Gallery', 'Price', 'Currency', 'Summary', 'Image File Name', 'Provenance File Name'];
$sheet->fromArray($headers, null, 'A1');

$row = 2;
while ($artwork = $result->fetch_assoc()) {
    $sheet->fromArray([
        $artwork['title'],
        $artwork['artist'],
        $artwork['location'],
        $artwork['medium'],
        $artwork['gallery'],
        $artwork['price'],
        $artwork['currency'],
        $artwork['summary'],
        url_file_name($artwork['image']),
        url_file_name($artwork['provenance']),
    ], null, "A{$row}");
    $row++;
}

$con->close();

$lastRow = max(2, $row - 1);

$sheet->getStyle('A1:J1')->getFont()->setBold(true);
$sheet->getStyle("F2:F{$lastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
$sheet->getStyle("A2:J{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getStyle("H2:H{$lastRow}")->getAlignment()->setWrapText(true);

foreach (range('A', 'J') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}
$sheet->freezePane('A2');

$filename = 'artwork-export-' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
