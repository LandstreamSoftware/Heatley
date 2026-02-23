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

  // Assume URL like: /api/rooms.php?inspection_id=12
  $inspection_id = $_GET['inspection_id'] ?? null;

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && $inspection_id) {
    $input = json_decode(file_get_contents('php://input'), true);

    $stmt = $pdo->prepare("
      INSERT INTO inspectionareas (areaName)
      VALUES (:name)
    ");

    $stmt->execute([
      ':inspection_id' => $inspection_id,
      ':name' => $input['name']
    ]);

    echo json_encode([
      'status' => 'success',
      'room_id' => $pdo->lastInsertId()
    ]);
  }
  
  if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT * FROM inspectionareas";
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