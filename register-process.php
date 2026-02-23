<?php
include 'main.php';
// Now we check if the data was submitted, isset() function will check if the data exists.
if (!isset($_POST['firstname'], $_POST['lastname'], $_POST['username'], $_POST['password'], $_POST['cpassword'], $_POST['email'])) {
	// Could not get the data that should have been sent.
	exit('Error: Please complete the registration form!');
}
// Make sure the submitted registration values are not empty.
if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email'])) {
	// One or more values are empty.
	exit('Error: Please complete the registration form!');
}
// Check to see if the email is valid.
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
	exit('Error: Please provide a valid email address!');
}
// First name must contain only characters and hyphen.
if (!preg_match('/^[a-zA-Z-]+$/', $_POST['firstname'])) {
    exit('Error: First name must contain only letters and hyphen!');
}
// Last name must contain only characters and hyphen.
if (!preg_match('/^[a-zA-Z-]+$/', $_POST['lastname'])) {
    exit('Error: Last name must contain only letters and hyphen!');
}
// Username must contain only characters and numbers.
if (!preg_match('/^[a-zA-Z0-9@.+]+$/', $_POST['username'])) {
    exit('Error: Username must contain only letters and numbers and @ and dot!');
}
// Password must be between 5 and 20 characters long.
if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
	exit('Error: Password must be between 5 and 20 characters long!');
}
// Check if both the password and confirm password fields match
if ($_POST['cpassword'] != $_POST['password']) {
	exit('Error: Passwords do not match!');
}
// We need to check if the account with that username exists.
$stmt = $con->prepare('SELECT id, password FROM accounts WHERE username = ? OR email = ?');
// Bind parameters (s = string, i = int, b = blob, etc), hash the password using the PHP password_hash function.
$stmt->bind_param('ss', $_POST['username'], $_POST['email']);
$stmt->execute();
$stmt->store_result();
// Store the result so we can check if the account exists in the database.
if ($stmt->num_rows > 0) {
	// Username already exists
	echo 'Error: Username and/or email exists!';
} else {
	$stmt->close();
	// Username doesnt exists, insert new account
	// We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
	$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
	// Generate unique activation code
	$activation_code = account_activation ? hash('sha256', uniqid() . $_POST['email'] . secret_key) : 'activated';
	// Approval required?
	$approved = account_approval ? 0 : 1;
	// Default Company - to be assigned by an admin
	$companyid = "";
	// Default role
	$role = 'User';
	// Current date
	$date = date('Y-m-d\TH:i:s');
	// Prepare query; prevents SQL injection
	$stmt = $con->prepare('INSERT INTO accounts (firstname, lastname, username, password, email, activation_code, role, registered, last_seen, approved, ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
	// Bind our variables to the query
	$ip = $_SERVER['REMOTE_ADDR'];
	$stmt->bind_param('sssssssssis', $_POST['firstname'], $_POST['lastname'], $_POST['username'], $password, $_POST['email'], $activation_code, $role, $date, $date, $approved, $ip);
	$stmt->execute();
	$stmt->close();
	// Last insert ID
	$id = $con->insert_id;
	// Send notification email
	if (notifications_enabled) {
		send_notification_email($id, $_POST['username'], $_POST['email'], $date, $_POST['firstname'], $_POST['lastname'], $_POST['companyname']);
	}
	// If account activation is required, send activation email
	if (account_activation) {
		// Account activation required, send the user the activation email with the "send_activation_email" function from the "main.php" file
		send_activation_email($_POST['email'], $activation_code);
		echo 'Success: Please check your email to activate your account!';
	} else {
		// Automatically authenticate the user if the option is enabled
		if (auto_login_after_register) {
			// Regenerate session ID
			session_regenerate_id();
			// Declare session variables
			$_SESSION['account_loggedin'] = TRUE;
			$_SESSION['account_name'] = $_POST['username'];
			$_SESSION['account_id'] = $id;
			$_SESSION['account_role'] = $role;
			// Do not change the output message as the AJAX code will use this to detect if the registration was successful and redirect to the home page
			echo 'redirect';
		} else {
			echo 'Success: You have successfully registered! You can now login!';
		}
	}
}
?>