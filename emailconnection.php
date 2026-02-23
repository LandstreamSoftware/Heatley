<?php
// The main file contains the database connection, session initializing, and functions, other PHP files will depend on this file.
// Include the configuration file
include_once 'config.php';
// Namespaces
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Connect to the MySQL database using MySQLi
$con = mysqli_connect(db_host, db_user, db_pass, db_name);
// If there is an error with the MySQL connection, stop the script and output the error
if (mysqli_connect_errno()) {
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
// Update the charset
mysqli_set_charset($con, db_charset);

// Get the company name
$stmt = $con->prepare('SELECT companyname FROM accounts_view WHERE id = ?');
$stmt->bind_param('i', $_SESSION['account_id']);
$stmt->execute();
$stmt->bind_result($mycompanyname);
$stmt->fetch();
$stmt->close();