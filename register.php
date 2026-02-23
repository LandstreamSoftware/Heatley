<?php
// Include the public menu file
include 'menupublic.php';

// No need for the user to see the login form if they're logged-in, so redirect them to the home page
if (isset($_SESSION['account_loggedin'])) {
	// If the user is not logged in, redirect to the home page.
	header('Location: home.php');
	exit;
}
// Also check if they are "remembered"
if (isset($_COOKIE['remember_me']) && !empty($_COOKIE['remember_me'])) {
	// If the remember me cookie matches one in the database then we can update the session variables.
	$stmt = $con->prepare('SELECT id, username, role FROM accounts WHERE remember_me_code = ?');
	$stmt->bind_param('s', $_COOKIE['remember_me']);
	$stmt->execute();
	$stmt->store_result();
	if ($stmt->num_rows > 0) {
		// Found a match
		$stmt->bind_result($id, $username, $role);
		$stmt->fetch();
		$stmt->close();
		// Authenticate the user
		session_regenerate_id();
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
		// Redirect to the home page
		header('Location: home.php');
		exit;
	}
}
?>
<?=template_header('Register')?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Account Registration</title>
		<link href="style.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<div class="register">

			<div class="icon">
				<!-- You could add your own site logo or icon here -->
				<svg width="26" height="26" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM504 312V248H440c-13.3 0-24-10.7-24-24s10.7-24 24-24h64V136c0-13.3 10.7-24 24-24s24 10.7 24 24v64h64c13.3 0 24 10.7 24 24s-10.7 24-24 24H552v64c0 13.3-10.7 24-24 24s-24-10.7-24-24z"/></svg>
			</div>

			<h1>Account Registration</h1>

			<form action="register-process.php" method="post" class="form register-form">

				<label class="form-label" for="firstname">First Name</label>
				<div class="form-group">
					<svg class="form-icon-left" width="14" height="14" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"/></svg>
					<input class="form-input" type="text" name="firstname" placeholder="First name" id="firstname" required>
				</div>

				<label class="form-label" for="lastname">Last Name</label>
				<div class="form-group">
					<svg class="form-icon-left" width="14" height="14" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"/></svg>
					<input class="form-input" type="text" name="lastname" placeholder="Last name" id="lastname" required>
				</div>

				<label class="form-label" for="companyname">Company Name</label>
				<div class="form-group">
					<svg class="form-icon-left" width="16" height="16" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M192 64C156.7 64 128 92.7 128 128L128 512C128 547.3 156.7 576 192 576L448 576C483.3 576 512 547.3 512 512L512 128C512 92.7 483.3 64 448 64L192 64zM304 416L336 416C353.7 416 368 430.3 368 448L368 528L272 528L272 448C272 430.3 286.3 416 304 416zM224 176C224 167.2 231.2 160 240 160L272 160C280.8 160 288 167.2 288 176L288 208C288 216.8 280.8 224 272 224L240 224C231.2 224 224 216.8 224 208L224 176zM368 160L400 160C408.8 160 416 167.2 416 176L416 208C416 216.8 408.8 224 400 224L368 224C359.2 224 352 216.8 352 208L352 176C352 167.2 359.2 160 368 160zM224 304C224 295.2 231.2 288 240 288L272 288C280.8 288 288 295.2 288 304L288 336C288 344.8 280.8 352 272 352L240 352C231.2 352 224 344.8 224 336L224 304zM368 288L400 288C408.8 288 416 295.2 416 304L416 336C416 344.8 408.8 352 400 352L368 352C359.2 352 352 344.8 352 336L352 304C352 295.2 359.2 288 368 288z"/></svg>
					<input class="form-input" type="text" name="companyname" placeholder="Company name" id="companyname" required>
				</div>

				<label class="form-label" for="username">Username</label>
				<div class="form-group">
					<svg class="form-icon-left" width="14" height="14" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"/></svg>
					<input class="form-input" type="text" name="username" placeholder="Username" id="username" required>
				</div>

				<label class="form-label" for="password">Password</label>
				<div class="form-group">
					<svg class="form-icon-left" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/></svg>
					<input class="form-input" type="password" name="password" placeholder="Password" id="password" required>
				</div>

				<label class="form-label" for="cpassword">Confirm Password</label>
				<div class="form-group">
					<svg class="form-icon-left" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/></svg>
					<input class="form-input" type="password" name="cpassword" placeholder="Confirm Password" id="cpassword" required>
				</div>

				<label class="form-label" for="email">Email</label>
				<div class="form-group mar-bot-5">
					<svg class="form-icon-left" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/></svg>
					<input class="form-input" type="email" name="email" placeholder="Email" id="email" required>
				</div>
				
				<div class="msg"></div>

				<button class="btn blue" type="submit">Register</button>

				<p class="register-link">Already have an account? <a href="login.php" class="form-link">Login</a></p>

			</form>

		</div>

		<?=template_footer()?>

		<script>
		// AJAX code
		const registrationForm = document.querySelector('.register-form');
		registrationForm.onsubmit = event => {
			event.preventDefault();
			fetch(registrationForm.action, { method: 'POST', body: new FormData(registrationForm), cache: 'no-store' }).then(response => response.text()).then(result => {
				registrationForm.querySelector('.msg').classList.remove('error','success');
				if (result.toLowerCase().includes('success:')) {
					registrationForm.querySelector('.msg').classList.add('success');
					registrationForm.querySelector('.msg').innerHTML = result.replace('Success: ', '');
				} else if (result.toLowerCase().includes('redirect')) {
					window.location.href = 'home.php';
				} else {
					registrationForm.querySelector('.msg').classList.add('error');
					registrationForm.querySelector('.msg').innerHTML = result.replace('Error: ', '');
				}
			});
		};
		</script>	
	</body>
</html>