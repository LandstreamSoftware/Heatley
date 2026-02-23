<?php
// Access this page using an authorization token.
$cron_token = '21062287935eeb370e86358956428880e301050da049eeb370e86358956424a1433a9d1812bc5e5dd';

// Check if the request contains the correct token 
if (php_sapi_name() !== 'cli' && (!isset($_GET['token']) || $_GET['token'] !== $cron_token)) { 
    http_response_code(403); // Forbidden 
    die('Access denied.'); 
} else {

  include_once '../config.php';

  header('Content-Type: application/json');
  
  // Connect to the MySQL database using MySQLi
  $con = mysqli_connect(db_host, db_user, db_pass, db_name);
  // If there is an error with the MySQL connection, stop the script and output the error
  if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
  }




}