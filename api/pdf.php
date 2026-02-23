<?php
include_once '../config.php';
require_once '../vendor/autoload.php';
// Namespaces
use Dompdf\Dompdf;

// Access this page using an authorization token.

$valid_api_key = '21062287935eeb370e86358956428880e301050da049eeb370e86358956424a1433a9d1812bc5e5dd';
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authHeader !== 'Bearer ' . $valid_api_key) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
} else {

  header('Content-Type: application/json');

  // Connect to the MySQL database using MySQLi
  $con = mysqli_connect(db_host, db_user, db_pass, db_name);
  // If there is an error with the MySQL connection, stop the script and output the error
  if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $inspection_id = $_GET['inspection_id'] ?? null;

    if ($inspection_id) {
      // Load inspection + rooms/items/media
      $sql1 = "SELECT * FROM inspections WHERE idinspection = $inspection_id";
      $result1 = $con->query($sql1);

      if ($result1->num_rows > 0) {
        while($row = $result1->fetch_assoc()) {
          // Do the same for rooms/items/media (simplified here)
          $html = "<h1>Inspection Report</h1>";
          $html .= "<p>Property ID: " . $row["premisesID"] . "</p>";
          $html .= "<p>Lease ID: " . $row["leaseID"] . "</p>";

          // Generate PDF
          $dompdf = new Dompdf();
          $dompdf->loadHtml($html);
          $dompdf->setPaper('A4', 'portrait');
          $dompdf->render();

          $pdfOutput = $dompdf->output();

          $fileName = "inspection_{$inspection_id}.pdf";
          $filePath = __DIR__ . "/../uploads/{$fileName}";
          file_put_contents($filePath, $pdfOutput);

          echo json_encode([
            'status' => 'success',
            'pdf_url' => "/uploads/{$fileName}"
          ]);
        }
      }
    }
  }

  if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT * FROM inspectionmedia";
    $result = $con->query($sql);
    $rows = [];
    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        $rows[] = $row;
      }
    }
    echo json_encode($rows); 
  }

  echo "pdf:";
}