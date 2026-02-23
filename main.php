<?php
// The main file contains the database connection, session initializing, and functions, other PHP files will depend on this file.
// Include the configuration file
include_once 'config.php';
require_once 'vendor/autoload.php';
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
// Update the charset
mysqli_set_charset($con, db_charset);
// Get the company name
$stmt = $con->prepare('SELECT companyname FROM accounts_view WHERE id = ?');
$stmt->bind_param('i', $_SESSION['account_id']);
$stmt->execute();
$stmt->bind_result($mycompanyname);
$stmt->fetch();
$stmt->close();
// Template header function
function template_header($title)
{
	// User with Banking panel link - will only be visible if the user role is a User with Banking
	$banking_panel_link = isset($_SESSION['account_role']) && $_SESSION['account_role'] == 'User with Banking' ? '
	<li class="nav-item ps-3 dropdown">
          <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Invoices
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="listtransactions.php?type=1&order=transactiondate DESC">Invoices</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="listtransactions.php?type=2&opex=0">Opex Bills</a></li>
          </ul>
        </li>
	<li class="nav-item ps-3">
          <a class="nav-link" href="listbankaccounts.php">Banking</a>
    </li>' : '';

	// Admin panel link - will only be visible if the user is an admin
	$admin_panel_link = isset($_SESSION['account_role']) && $_SESSION['account_role'] == 'Admin' ? '
	<li class="nav-item ps-3 dropdown">
          <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Invoices
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="listtransactions.php?type=1&order=transactiondate DESC">Invoices</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="listtransactions.php?type=2&opex=0">Opex Bills</a></li>
          </ul>
        </li>
	<li class="nav-item ps-3">
          <a class="nav-link" href="listbankaccounts.php">Banking</a>
    </li>
	<li class="nav-item ps-3 dropdown">
		<a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
		Xero
		</a>
		<ul class="dropdown-menu">
		<li><a class="dropdown-item" href="xero-invoices.php">Xero Invoices</a></li>
		<li><hr class="dropdown-divider"></li>
		<li><a class="dropdown-item" href="xero-bills.php">Xero Bills</a></li>
		<li><hr class="dropdown-divider"></li>
		<li><a class="dropdown-item" href="https://login.xero.com" target="_blank">Log in to Xero</a></li>
		</ul>
	</li>
	<li class="nav-item ps-3">
		<a class="nav-link" href="admin/index.php" target="_blank">Admin</a>
	</li>' : '';


	// Get the current file name (eg. home.php, profile.php)
	$current_file_name = basename($_SERVER['PHP_SELF']);
	// Indenting the below code may cause HTML validation errors
	echo '<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>' . $title . '</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<!--	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script> -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
		<style>
		.pagination a {
			color: black;
			float: left;
			padding: 8px 16px;
			text-decoration: none;
			transition: background-color .3s;
		}

		.pagination a.active {
			background-color: dodgerblue;
			color: white;
		}

		.pagination a:hover:not(.active) {background-color: #ddd;}

		.navbar-expand-lg .navbar-nav .nav-item.active .nav-link {
    		color: white;
		}

		img, svg {
    		fill: var(--bs-nav-link-color);
		}

		.header .wrapper {
			display: grid;
		}

		</style>
	
	</head>
	<body>

		<header class="header">

			<div class="wrapper">

<nav class="navbar navbar-expand-xl bg-body-tertiary" data-bs-theme="dark">
  <div class="container-fluid">
    <a href="home.php" style="padding-right:8px;"><img width="180px" src="img/heatley_portal_white_180.png"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <li class="nav-item ps-3">
          <a class="nav-link" href="home.php?timespan=90&expiretimespan=90&compliancetimespan=90">Home</a>
        </li>
		
		<li class="nav-item ps-3 dropdown">
          <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Contacts
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="listcompanies.php">Companies</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="listcontacts.php">Contacts</a></li>
          </ul>
        </li>


		' . $banking_panel_link . '

		' . $admin_panel_link . '

		<li class="nav-item ps-3">
          <a class="nav-link" href="listreports.php">Reports</a>
        </li>


		<li class="nav-item ps-3">
          <a class="nav-link" href="help/" target="_blank">Help</a>
        </li>

		<li class="nav-item ps-3">
        	<a href="logout.php" class="nav-link">
				<svg width="12" height="12" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"/></svg>
				Logout
			</a>
        </li>
	  </ul>
    </div>
	<p class="collapse navbar-collapse" id="navbarSupportedContent" style="color:#999; font-size:12px; padding:15px 0 0 40px;">
	<a href="profile.php" style="color:inherit; text-decoration:none; font-size:12px;">' ?>
	<?= htmlspecialchars($_SESSION['account_name'], ENT_QUOTES) ?>
	<?php echo '</a><br></p>
  </div>
</nav>
		</header>
		<div class="content">';
}
//Automatically highlight the current page on the menu
?>
<script>
	document.addEventListener("DOMContentLoaded", function () {
		const currentPath = window.location.pathname;  // Get the current page path
		document.querySelectorAll('.nav-item').forEach(item => {
			const link = item.querySelector('.nav-link');  // Get the link inside the nav-item
			if (link && link.pathname === currentPath) {
				item.classList.add('active');  // Add 'active' class to the <li> element
			}
		});
	});
</script>

<?php

// Template footer function
function template_footer()
{
	// Output the footer HTML
	echo '</div>
	<footer>
		&copy; Landstream Software ' . date("Y");
	'
	</footer>
	</body>
</html>';
}
// The below function will check if the user is logged-in and also check the remember me cookie
function check_loggedin($con, $redirect_file = 'login.php')
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
// Send activation email function
function send_activation_email($email, $code)
{
	if (!mail_enabled)
		return;
	// Include PHPMailer library
	include_once 'lib/phpmailer/Exception.php';
	include_once 'lib/phpmailer/PHPMailer.php';
	include_once 'lib/phpmailer/SMTP.php';
	// Create an instance; passing `true` enables exceptions
	$mail = new PHPMailer(true);
	try {
		// Server settings
		if (SMTP) {
			$mail->isSMTP();
			$mail->Host = smtp_host;
			$mail->SMTPAuth = true;
			$mail->Username = smtp_user;
			$mail->Password = smtp_pass;
			$mail->SMTPSecure = smtp_secure; //PHPMailer::ENCRYPTION_SMTPS;
			$mail->Port = smtp_port;
		}
		// Recipients
		$mail->setFrom(mail_from, mail_name);
		$mail->addAddress($email);
		$mail->addReplyTo(mail_from, mail_name);
		// Content
		$mail->isHTML(true);
		$mail->Subject = 'Account Activation Required';
		// Activation link
		$activate_link = root_url . 'activate.php?code=' . $code;
		// Read the template contents and replace the "%link" placeholder with the above variable
		$email_template = str_replace('%link%', $activate_link, file_get_contents('activation-email-template.html'));
		// Set email body
		$mail->Body = $email_template;
		$mail->AltBody = strip_tags($email_template);
		// Send mail
		$mail->send();
	} catch (Exception $e) {
		// Output error message
		exit('Error: Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
	}
}
// Send notification email to admin@leasemanager
function send_notification_email($account_id, $account_username, $account_email, $account_date, $firstname, $lastname, $companyname)
{
	if (!mail_enabled)
		return;
	// Include PHPMailer library
	include_once 'lib/phpmailer/Exception.php';
	include_once 'lib/phpmailer/PHPMailer.php';
	include_once 'lib/phpmailer/SMTP.php';
	// Create an instance; passing `true` enables exceptions
	$mail = new PHPMailer(true);
	try {
		// Server settings
		if (SMTP) {
			$mail->isSMTP();
			$mail->Host = smtp_host;
			$mail->SMTPAuth = true;
			$mail->Username = smtp_user;
			$mail->Password = smtp_pass;
			$mail->SMTPSecure = smtp_secure; //PHPMailer::ENCRYPTION_SMTPS;
			$mail->Port = smtp_port;
		}
		// Recipients
		$mail->setFrom(mail_from, mail_name);
		$mail->addAddress(notification_email);
		$mail->addReplyTo(mail_from, mail_name);
		// Content
		$mail->isHTML(true);
		$mail->Subject = 'A new user has registered!';
		// Read the template contents and replace the "%link" placeholder with the above variable
		$email_template = str_replace(['%id%', '%username%', '%date%', '%email%', '%firstname%', '%lastname%', '%companyname%'], [$account_id, htmlspecialchars($account_username, ENT_QUOTES), $account_date, $account_email, $firstname, $lastname, $companyname], file_get_contents('notification-email-template.html'));
		// Set email body
		$mail->Body = $email_template;
		$mail->AltBody = strip_tags($email_template);
		// Send mail
		$mail->send();
	} catch (Exception $e) {
		// Output error message
		exit('Error: Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
	}
}
// Send password reset email function
function send_password_reset_email($email, $username, $code)
{
	if (!mail_enabled)
		return;
	// Include PHPMailer library
	include_once 'lib/phpmailer/Exception.php';
	include_once 'lib/phpmailer/PHPMailer.php';
	include_once 'lib/phpmailer/SMTP.php';
	// Create an instance; passing `true` enables exceptions
	$mail = new PHPMailer(true);
	try {
		// Server settings
		if (SMTP) {
			$mail->isSMTP();
			$mail->Host = smtp_host;
			$mail->SMTPAuth = true;
			$mail->Username = smtp_user;
			$mail->Password = smtp_pass;
			$mail->SMTPSecure = smtp_secure; //PHPMailer::ENCRYPTION_SMTPS;
			$mail->Port = smtp_port;
		}
		// Recipients
		$mail->setFrom(mail_from, mail_name);
		$mail->addAddress($email);
		$mail->addReplyTo(mail_from, mail_name);
		// Content
		$mail->isHTML(true);
		$mail->Subject = 'Password Reset';
		// Password reset link
		$reset_link = base_url . 'reset-password.php?code=' . $code;
		// Read the template contents and replace the "%link%" placeholder with the above variable
		$email_template = str_replace(['%link%', '%username%'], [$reset_link, htmlspecialchars($username, ENT_QUOTES)], file_get_contents('resetpass-email-template.html'));
		// Set email body
		$mail->Body = $email_template;
		$mail->AltBody = strip_tags($email_template);
		// Send mail
		$mail->send();
	} catch (Exception $e) {
		// Output error message
		exit('Error: Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
	}
}

// Send contact us email function
function send_contact_us_email($firstname, $lastname, $emailaddress, $mobilenumber, $companyname, $message)
{
	if (!mail_enabled)
		return;
	// Include PHPMailer library
	include_once 'lib/phpmailer/Exception.php';
	include_once 'lib/phpmailer/PHPMailer.php';
	include_once 'lib/phpmailer/SMTP.php';
	// Create an instance; passing `true` enables exceptions
	$mail = new PHPMailer(true);

	try {
		// Server settings
		if (SMTP) {
			$mail->isSMTP();
			$mail->Host = smtp_host;
			$mail->SMTPAuth = true;
			$mail->Username = smtp_user;
			$mail->Password = smtp_pass;
			$mail->SMTPSecure = smtp_secure; //PHPMailer::ENCRYPTION_SMTPS;
			$mail->Port = smtp_port;
		}
		// Recipients
		$mail->setFrom(mail_from, mail_name);
		$mail->addAddress(notification_email);
		$mail->addReplyTo(mail_from, mail_name);
		// Content
		$mail->isHTML(true);
		$mail->Subject = 'A Contact Us enquiry has been submitted';
		// Read the template contents and replace the "%link" placeholder with the above variable
		$email_template = str_replace(['%firstname%', '%lastname%', '%mobilenumber%', '%emailaddress%', '%companyname%', '%message%'], [$firstname, $lastname, $mobilenumber, $emailaddress, $companyname, $message], file_get_contents('contact-us-email-template.html'));
		// Set email body
		$mail->Body = $email_template;
		$mail->AltBody = strip_tags($email_template);
		// Send mail
		$mail->send();
	} catch (Exception $e) {
		// Output error message
		exit('Error: The message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
	}
}


// Send renewal notification email function
function send_renewal_notification_email($recipientemail, $renewalid, $tenantname, $renewaltype, $renewaldate)
{
	if (!mail_enabled)
		return;
	// Include PHPMailer library
	include_once 'lib/phpmailer/Exception.php';
	include_once 'lib/phpmailer/PHPMailer.php';
	include_once 'lib/phpmailer/SMTP.php';
	// Create an instance; passing `true` enables exceptions
	$mail = new PHPMailer(true);

	try {
		// Server settings
		if (SMTP) {
			$mail->isSMTP();
			//$mail->Host = smtp_host;
			//$mail->SMTPAuth = true;
			//$mail->Username = smtp_user;
			//$mail->Password = smtp_pass;
			//$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
			//$mail->Port = smtp_port;

			$mail->Host = 'mail.smtp2go.com';
			$mail->SMTPAuth = true;
			$mail->Username = 'landstream.co.nz'; // SMTP username
			$mail->Password = 'kSnuAJjRwFykZXgT'; // SMTP password
			$mail->SMTPSecure = smtp_secure; //PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
			$mail->Port = smtp_port; // TCP port to connect to (587 or 80, 25, 8025, 587, 2525) - 587 = TLS Port, working on dev.
		}
		// Recipients
		$mail->setFrom(mail_from, mail_name);
		$mail->addAddress($recipientemail);
		$mail->addReplyTo(mail_from, mail_name);
		// Content
		$mail->isHTML(true);
		$mail->Subject = $renewaltype . ' - ' . $tenantname;
		// Read the template contents and replace the "%link" placeholder with the above variable
		$renewal_link = root_url . 'viewrenewal.php?renewalid=' . $renewalid;
		// Read the template contents and replace the "%link" placeholder with the above variable
		$email_template = str_replace(['%link%', '%tenantname%', '%renewaltype%', '%renewaldate%'], [$renewal_link, $tenantname, $renewaltype, $renewaldate], file_get_contents('email-template-renewal-notification.html'));
		// Set email body
		$mail->Body = $email_template;
		$mail->AltBody = strip_tags($email_template);

		//$mail->SMTPDebug = 3;
		//$mail->Debugoutput = 'html';

		// Send mail
		$mail->send();
	} catch (Exception $e) {
		// Output error message
		exit('Error: The message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
	}
}

// Check if Xero token has expired and refresh it if it has expired
function check_xero_token_expiry($con, $companyid)
{
	$sql2 = "SELECT * FROM xero_oauth_tokens WHERE companyID = $companyid";
	$result2 = $con->query($sql2);
	if ($result2->num_rows > 0) {
		while($row2 = $result2->fetch_assoc()) {
		$accesstoken = $row2["access_token"];
		$expiredAt =  $row2["access_token_expires_at"];
		$tenantid = $row2["tenant_id"];
		$refreshtoken = $row2["refresh_token"];
		$clientid = $row2["client_id"];
		$clientsecret = $row2["client_secret"];
		$redirecturi = $row2["redirect_uri"];
		$newtokenversion = $row2["token_version"] + 1;
		}
	} else {
		// No Xero authentication found
		header("Location: xero-add_authentication_credentials.php");
		exit;
	}


	// Set up the dates for comparison
	$formatted_now = date_format(new DateTimeImmutable('now'), 'Y-m-d H:i:s');
	$expiredAt_date = date_create($expiredAt);
	date_sub($expiredAt_date, date_interval_create_from_date_string("2 minutes"));
	$atOrNearexpiredAt = date_format($expiredAt_date, 'Y-m-d H:i:s');

	//If the token has expired or is very close to expiring
	if ($atOrNearexpiredAt <= $formatted_now) {

		$provider = new \League\OAuth2\Client\Provider\GenericProvider([
		'clientId'                => $clientid,
		'clientSecret'            => $clientsecret,
		'redirectUri'             => $redirecturi,
		'urlAuthorize'            => 'https://login.xero.com/identity/connect/authorize',
		'urlAccessToken'          => 'https://identity.xero.com/connect/token',
		'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation'
		]);

		// Get a new token and refresh token
		$newToken = $provider->getAccessToken('refresh_token', [
			'refresh_token' => $refreshtoken,
		]);
		$accesstoken = $newToken->getToken();

		$refreshtoken = $newToken->getRefreshToken();
		$expiresAt = (new DateTimeImmutable())->setTimestamp($newToken->getExpires())->format('Y-m-d H:i:s');

		//Update the database with the new token, refresh token and expiry
		$sqlupd = "UPDATE xero_oauth_tokens SET access_token_expires_at = '$expiresAt', access_token = '$accesstoken', refresh_token = '$refreshtoken', token_version = '$newtokenversion' WHERE companyID = $companyid";

		if ($con->query($sqlupd) === TRUE) {
		// New token data successfully stored in database.
		return $accesstoken;
		}
	} else {
		return $accesstoken;
	}
}

// Send two-factor authentication email function
function send_twofactor_email($email, $code) {
	if (!mail_enabled) return;
	// Include PHPMailer library
	include_once 'lib/phpmailer/Exception.php';
	include_once 'lib/phpmailer/PHPMailer.php';
	include_once 'lib/phpmailer/SMTP.php';
	// Create an instance; passing `true` enables exceptions
	$mail = new PHPMailer(true);
	try {
		// Server settings
		if (SMTP) {
			$mail->isSMTP();
			//$mail->Host = smtp_host;
			//$mail->SMTPAuth = true;
			//$mail->Username = smtp_user;
			//$mail->Password = smtp_pass;
			//$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
			//$mail->Port = smtp_port;

			$mail->Host = 'mail.smtp2go.com';
			$mail->SMTPAuth = true;
			$mail->Username = 'landstream.co.nz'; // SMTP username
			$mail->Password = 'kSnuAJjRwFykZXgT'; // SMTP password
			$mail->SMTPSecure = smtp_secure; //PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
			$mail->Port = smtp_port; // TCP port to connect to (587 or 80, 25, 8025, 587, 2525) - 587 = TLS Port, working on dev.
		}
		// Recipients
		$mail->setFrom(mail_from, mail_name);
		$mail->addAddress($email);
		$mail->addReplyTo(mail_from, mail_name);
		// Content
		$mail->isHTML(true);
		$mail->Subject = 'Your Access Code';
		// Read the template contents and replace the "%code%" placeholder with the above variable
		$email_template = str_replace('%code%', $code, file_get_contents('twofactor-email-template.html'));
		// Set email body
		$mail->Body = $email_template;
		$mail->AltBody = strip_tags($email_template);
		// Send mail
		$mail->send();
	} catch (Exception $e) {
		// Output error message
		exit('Error: Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
	}
}
?>