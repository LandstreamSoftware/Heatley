<?php
include 'main.php';
// Check logged-in
check_loggedin($con);
// Error message variable
$error_msg = '';
// Success message variable
$success_msg = '';
// Retrieve additional account info from the database because we don't have them stored in sessions
$stmt = $con->prepare('SELECT firstname, lastname, username, password, email, activation_code, role, registered, companyID FROM accounts WHERE id = ?');
// In this case, we can use the account ID to retrieve the account info.
$stmt->bind_param('i', $_SESSION['account_id']);
$stmt->execute();
$stmt->bind_result($firstname, $lastname, $username, $password, $email, $activation_code, $role, $registered_date, $companyid);
$stmt->fetch();
$stmt->close();

// Get the company name
$stmt = $con->prepare('SELECT companyname, companyid FROM accounts_view WHERE id = ?');
$stmt->bind_param('i', $_SESSION['account_id']);
$stmt->execute();
$stmt->bind_result($companyname, $companyid);
$stmt->fetch();
$stmt->close();

// Get the access account list
$accesslist = array();
$accountid = $_SESSION['account_id'];
$sql2 ="SELECT companyid, companyname FROM accesscontrol_view WHERE accountid = $accountid ORDER BY companyname";
//$stmt->bind_param('i', $_SESSION['account_id']);
//$stmt->execute();
//$stmt->bind_result($accesslist);
$result2 = $con->query($sql2);
while($row2 = $result2->fetch_assoc()) {
    $accesslist[] = $row2["companyname"]; 
}
//$stmt->fetch();
//$stmt->close();

// Handle edit profile post data
if (isset($_POST['firstname'], $_POST['lastname'], $_POST['username'], $_POST['npassword'], $_POST['cpassword'], $_POST['email'])) {
	// Make sure the submitted registration values are not empty.
	if (empty($_POST['username']) || empty($_POST['email'])) {
		$error_msg = 'The input fields must not be empty!';
	} else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		$error_msg = 'Please provide a valid email address!';
	} else if (!preg_match('/^[a-zA-Z0-9@.]+$/', $_POST['username'])) {
	    $error_msg = 'Username must contain only letters, numbers, @ and dot!';
	} else if (!empty($_POST['npassword']) && (strlen($_POST['npassword']) > 20 || strlen($_POST['npassword']) < 5)) {
		$error_msg = 'Password must be between 5 and 20 characters long!';
	} else if ($_POST['cpassword'] != $_POST['npassword']) {
		$error_msg = 'Passwords do not match!';
	}
	// No validation errors... Process update
	if (empty($error_msg)) {
		// Check if new username or email already exists in the database
		$stmt = $con->prepare('SELECT * FROM accounts WHERE (username = ? OR email = ?) AND username != ? AND email != ?');
		$stmt->bind_param('ssss', $_POST['username'], $_POST['email'], $_SESSION['account_name'], $email);
		$stmt->execute();
		$stmt->store_result();
		// Account exists? Output error...
		if ($stmt->num_rows > 0) {
			$error_msg = 'Account already exists with that username and/or email!';
		} else {
			// No errors occured, update the account...
			$stmt->close();
			// Hash the new password if it was posted and is not blank
			$password = !empty($_POST['npassword']) ? password_hash($_POST['npassword'], PASSWORD_DEFAULT) : $password;
			// If email has changed, generate a new activation code
			$activation_code = account_activation && $email != $_POST['email'] ? hash('sha256', uniqid() . $_POST['email'] . secret_key) : $activation_code;
			// Update the account
			$stmt = $con->prepare('UPDATE accounts SET firstname = ?, lastname = ?, username = ?, password = ?, email = ?, activation_code = ? WHERE id = ?');
			$stmt->bind_param('ssssssi', $_POST['firstname'], $_POST['lastname'], $_POST['username'], $password, $_POST['email'], $activation_code, $_SESSION['account_id']);
			$stmt->execute();
			$stmt->close();
			// Update the session variables
			$_SESSION['account_name'] = $_POST['username'];
			// If email has changed, logout the user and send a new activation email
			if (account_activation && $email != $_POST['email']) {
				// Account activation required, send the user the activation email with the "send_activation_email" function from the "main.php" file
				send_activation_email($_POST['email'], $activation_code);
				// Logout the user
				unset($_SESSION['account_loggedin']);
				// Output success message
				$success_msg = 'You have changed your email address! You need to re-activate your account!';
			} else {
				// Profile updated successfully, redirect the user back to the profile page
				header('Location: profile.php');
				exit;
			}
		}
	}
}
?>
<?=template_header('Profile')?>

<?php if (!isset($_GET['action'])): ?>

<!-- View Profile Page -->

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Profile</h2>
		<p>View and edit your profile details below.</p>
	</div>
</div>

<div class="block">

	<!-- Tip: it's good practice to escape user variables using htmlspecialchars() to prevent XSS attacks. -->

	<div class="profile-detail">
		<strong>First Name</strong>
		<?=htmlspecialchars($firstname, ENT_QUOTES)?>
	</div>
	<div class="profile-detail">
		<strong>Last Name</strong>
		<?=htmlspecialchars($lastname, ENT_QUOTES)?>
	</div>
	<div class="profile-detail">
		<strong>Username</strong>
		<?=htmlspecialchars($username, ENT_QUOTES)?>
	</div>

	<div class="profile-detail">
		<strong>Email</strong>
		<?=htmlspecialchars($email, ENT_QUOTES)?>
	</div>

	<div class="profile-detail">
		<strong>Role</strong>
		<?=$role?>
	</div>

	
	<?php
	$sql3 ="SELECT * FROM xero_oauth_tokens WHERE companyID = $companyid";
	$result3 = $con->query($sql3);

	if ($result3->num_rows > 0) {
		//Button to edit/save the Xero Authentication Credentials - will only be visible if the user role is a User with Xero
		if (isset($_SESSION['account_role']) && ($_SESSION['account_role'] == 'User with Xero' || $_SESSION['account_role'] == 'Admin')) {
			echo '<div class="profile-detail">
				<a class="btn btn-primary" href="xero-edit_authentication_credentials.php?cid=' . $companyid . '" style="width:300px; margin:20px;">Edit Xero Authentication Credentials</a>
			</div>';
		}
	} else {
		echo '<div class="profile-detail">
				<a class="btn btn-primary" href="xero-add_authentication_credentials.php" style="width:300px; margin:20px;">Add Xero Authentication Credentials</a>
			</div>';
	}
	?>

	<div class="profile-detail">
		<strong>Registered</strong>
		<?=date('Y-m-d H:ia', strtotime($registered_date))?>
	</div>

	<div class="profile-detail">
		<strong>Company</strong>
		<?=htmlspecialchars($companyname, ENT_QUOTES)?>
	</div>

	<div class="profile-detail">
		<strong>
			Accounts you can access:
		</strong>
		<?php
		foreach($accesslist as $x => $y) {
    		echo "$y <br>"; 
		}
		?>
	</div>
	<div class="row">
		<div class="col-sm-2" style="padding-top:20px;"><a class="btn btn-primary" href="?action=edit" style="width:200px; margin:20px;">Edit Details</a></div>
		<div class="col-sm-2" style="padding-top:20px;"><a href="setupwizard.php" class="btn btn-primary" style="width:200px; margin:20px;">Setup Wizard</a></div>
	</div>

<?php elseif ($_GET['action'] == 'edit'): ?>


<div class="block">

	<form action="" method="post" class="form form-small">

		<label class="form-label" for="firstname" style="padding-top:5px">First Name</label>
		<div class="form-group">
			<svg class="form-icon-left" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"/></svg>
			<input class="form-input" type="text" name="firstname" placeholder="First Name" id="firstname" value="<?=htmlspecialchars($firstname, ENT_QUOTES)?>">
		</div>

		<label class="form-label" for="lastname">Last Name</label>
		<div class="form-group">
			<svg class="form-icon-left" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"/></svg>
			<input class="form-input" type="text" name="lastname" placeholder="Last Name" id="lastname" value="<?=htmlspecialchars($lastname, ENT_QUOTES)?>">
		</div>

		<label class="form-label" for="username">Username</label>
		<div class="form-group">
			<svg class="form-icon-left" width="14" height="14" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"/></svg>
			<input class="form-input" type="text" name="username" placeholder="Username" id="username" value="<?=htmlspecialchars($username, ENT_QUOTES)?>" required>
		</div>

		<label class="form-label" for="npassword">New Password</label>
		<div class="form-group">
			<svg class="form-icon-left" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/></svg>
			<input class="form-input" type="password" name="npassword" placeholder="New Password" id="npassword" autocomplete="new-password">
		</div>

		<label class="form-label" for="cpassword">Confirm Password</label>
		<div class="form-group">
			<svg class="form-icon-left" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/></svg>
			<input class="form-input" type="password" name="cpassword" placeholder="Confirm Password" id="cpassword" autocomplete="new-password">
		</div>

		<label class="form-label" for="email">Email</label>
		<div class="form-group mar-bot-5">
			<svg class="form-icon-left" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/></svg>
			<input class="form-input" type="email" name="email" placeholder="Email" id="email" value="<?=htmlspecialchars($email, ENT_QUOTES)?>" required>
		</div>
		
		<?php if ($error_msg): ?>
		<div class="msg error">
			<?=$error_msg?>
		</div>
		<?php elseif ($success_msg): ?>
		<div class="msg success">
			<?=$success_msg?>
		</div>
		<?php endif; ?>

		<div class="mar-bot-2">
			<button class="btn btn-primary mar-top-1 mar-right-1" type="submit">Save Details</button>
			<a href="profile.php" class="btn btn-primary mar-top-1">Back To Profile</a>
		</div>

	</form>

</div>

<?php endif; ?>

<?=template_footer()?>