<?php
// The main file contains the database connection, session initializing, and functions, other PHP files will depend on this file.
// Include the configuration file
include_once '../config.php';
// We need to use sessions, so you should always start sessions using the below function
session_start();



// Connect to the MySQL database using MySQLi
$con = mysqli_connect(db_host, db_user, db_pass, db_name);
// If there is an error with the MySQL connection, stop the script and output the error
if (mysqli_connect_errno()) {
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
// Update the charset
mysqli_set_charset($con, db_charset);
// Get the company name
$stmt = $con->prepare('SELECT companyid, companyname, firstname, lastname FROM accounts_view WHERE id = ?');
$stmt->bind_param('i', $_SESSION['account_id']);
$stmt->execute();
$stmt->bind_result($mycompanyid, $mycompanyname, $firstname, $lastname);
$stmt->fetch();
$stmt->close();

function template_header($title)
{
	global $firstname, $lastname;
	echo '<!DOCTYPE html>
	<html lang="en">

	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Account Login</title>
		<link rel="manifest" href="/pwa/manifest.json" />
		<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;700&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="../pwa/css/styles.css" />
		<link rel="icon" type="image/png" href="/pwa/assets/icons/favicon-96x96.png" sizes="96x96" />
		<link rel="icon" type="image/svg+xml" href="/pwa/assets/icons/favicon.svg" />
		<link rel="shortcut icon" href="/pwa/assets/icons/favicon.ico" />
		<link rel="apple-touch-icon" sizes="180x180" href="/pwa/assets/icons/apple-touch-icon.png" />
		<meta name="apple-mobile-web-app-title" content="Inspector" />
		<link rel="manifest" href="/pwa/assets/icons/site.webmanifest" />

<script>
	function openNav() {
	document.getElementById("myNav").style.width = "60%";
	}

	function closeNav() {
	document.getElementById("myNav").style.width = "0%";
	}
</script>

	</head>

	<body>
    <header class="header">
        <div class="heading">
		<span style="font-size:30px;cursor:pointer;margin-left:15px" onclick="openNav()">&#9776;</span>
            <div id="myNav" class="overlay">
				<a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
				<div class="overlay-content">
					<a href="index.php">Home</a>
					<a href="addinspection.php">Add Inspection</a>
					<a href="savedinspections.php">Saved Inspections</a>
					<a href="logout.php">Log Out</a>
				</div>
			</div>
			<div>' . $title . '</div>
            <div class="inspector">' . $firstname . ' ' . $lastname . '</div>
        </div>
	</header>
	<div class="content">
';

}


// The below function will check if the user is logged-in and also check the remember me cookie
function check_loggedin($con, $redirect_file = '/pwa/login.php')
{
	// If you want to update the "last seen" column on every page load, you can uncomment the below code, but it may slow down your site.
	/*
				if (isset($_SESSION['account_loggedin'])) {
					$date = date('Y-m-d\TH:i:s');
					$stmt = $con->prepare('UPDATE accounts SET last_seen = ? WHERE id = ?');
					$stmt->bind_param('si', $date, $_SESSION['account_id']);
					$stmt->execute();
					$stmt->close();
				}
				*/
	// Check for remember me cookie variable and loggedin session variable
	if (isset($_COOKIE['remember_me']) && !empty($_COOKIE['remember_me']) && !isset($_SESSION['account_loggedin'])) {
		// If the remember me cookie matches one in the database then we can update the session variables.
		$stmt = $con->prepare('SELECT id, username, role FROM accounts WHERE remember_me_code = ?');
		$stmt->bind_param('s', $_COOKIE['remember_me']);
		$stmt->execute();
		$stmt->store_result();
		// If there are results
		if ($stmt->num_rows > 0) {
			// Found a match, update the session variables and keep the user logged-in
			$stmt->bind_result($id, $username, $role);
			$stmt->fetch();
			$stmt->close();
			// Regenerate session ID
			session_regenerate_id();
			// Declare session variables; authenticate the user
			$_SESSION['account_loggedin'] = TRUE;
			$_SESSION['account_name'] = $username;
			$_SESSION['account_id'] = $id;
			$_SESSION['account_role'] = $role;
			// Update last seen date
			$date = date('Y-m-d\TH:i:s');
			$stmt = $con->prepare('UPDATE accounts SET last_seen = ? WHERE id = ?');
			$stmt->bind_param('si', $date, $id);
			$stmt->execute();
			$stmt->close();
		} else {
			// If the user is not remembered, redirect to the login page.
			header('Location: ' . $redirect_file);
			exit;
		}
	} else if (!isset($_SESSION['account_loggedin'])) {
		// If the user is not logged-in, redirect to the login page.
		header('Location: ' . $redirect_file);
		exit;
	}
}