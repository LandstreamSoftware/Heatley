<?php
// Include the main.php file
//include 'main.php';
// Check if the user is logged in, if not then redirect to login page

// Include the configuration file
include_once 'config.php';
// We need to use sessions, so you should always start sessions using the below function
session_start();
// Namespaces
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Connect to the MySQL database using MySQLi
$con = mysqli_connect(db_host, db_user, db_pass, db_name);
// If there is an error with the MySQL connection, stop the script and output the error
if (mysqli_connect_errno()) {
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

function check_loggedin($con, $redirect_file = 'index.php')
{
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



check_loggedin($con);
// Template code below

$accountid = $_SESSION['account_id'];

$sqlAccess = "SELECT * FROM accesscontrol WHERE accountID = $accountid";
$resultAccess = $con->query($sqlAccess);

$accessto = -1;

if ($resultAccess->num_rows > 0) {
    while ($rowAccess = $resultAccess->fetch_assoc()) {
        $accessto .= "," . $rowAccess["companyID"];
    }
}

if (isset($_GET['companyid'])) {
    $companyid = intval($_GET['companyid']);
    $query = "SELECT * FROM leasepremises_view WHERE tenantid = ? ORDER BY unitname";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $companyid);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    header('Content-Type: application/json');
    echo json_encode($items);
}

// Get the list of premises
if (isset($_GET['buildingid'])) {
    $buildingid = intval($_GET['buildingid']);
    $query = "SELECT DISTINCT idpremises, unitname, buildingname FROM premises_view WHERE idbuildings = ? ORDER BY unitName";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $buildingid);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    header('Content-Type: application/json');
    echo json_encode($items);
}

// Get the list of areas
if (isset($_GET['premisesid'])) {
    $premisesid = intval($_GET['premisesid']);
    $query = "SELECT DISTINCT premisesid, idinspectionarea, areaname FROM inspections_view WHERE premisesid = ? ORDER BY areaname";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $premisesid);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    header('Content-Type: application/json');
    echo json_encode($items);
}

$con->close();