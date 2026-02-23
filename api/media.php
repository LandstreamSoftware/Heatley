<?php
// Access this page using an authorization token.

include_once '../config.php';

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

  // /api/media.php?item_id=7
  $item_id = $_GET['item_id'] ?? null;

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && $item_id) {
    $media_type = $_POST['media_type'] ?? 'photo';

    if (!isset($_FILES['file'])) {
      echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
      exit;
    }

    $uploadDir = __DIR__ . '/../uploads/';
    $fileName = uniqid() . '_' . basename($_FILES['file']['name']);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
      // Save URL in DB
      $stmt = $pdo->prepare("
        INSERT INTO inspection_media (item_id, file_url, media_type)
        VALUES (:item_id, :file_url, :media_type)
      ");
      $stmt->execute([
        ':item_id' => $item_id,
        ':file_url' => '/uploads/' . $fileName,
        ':media_type' => $media_type
      ]);

      echo json_encode(['status' => 'success', 'file_url' => '/uploads/' . $fileName]);
    } else {
      echo json_encode(['status' => 'error', 'message' => 'Upload failed']);
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
}