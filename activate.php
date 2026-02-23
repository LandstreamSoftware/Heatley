<?php
include 'main.php';
// Success message variable
$success_msg = '';
// First we check if the email and code exists, these variables will appear as parameters in the URL
if (isset($_GET['code']) && !empty($_GET['code'])) {
	// Check if the account exists with the specified activation code
	$stmt = $con->prepare('SELECT * FROM accounts WHERE activation_code = ? AND activation_code != "activated" AND activation_code != "deactivated"');
	$stmt->bind_param('s', $_GET['code']);
	$stmt->execute();
	// Store the result so we can check if the account exists in the database.
	$stmt->store_result();
	if ($stmt->num_rows > 0) {
		// Account exists with the requested code.
		$stmt->close();
		// Update the activation code column to "activated" - this is how we can check if the user has activated their account
		$stmt = $con->prepare('UPDATE accounts SET activation_code = "activated" WHERE activation_code = ?');
		$stmt->bind_param('s', $_GET['code']);
		$stmt->execute();
		$stmt->close();
		// Output success message
		$success_msg = 'Your account is now activated!<br>Once your account has been approved by the Heatley Portal administrator, you can <a href="index.php" class="form-link">Login</a>.';
	} else {
		// Account with the code specified does not exist
		$success_msg = 'The account is already activated or doesn\'t exist!';
	}
} else {
	$success_msg = 'No code was specified!';
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Activate Account</title>
		<link href="style.css" rel="stylesheet" type="text/css">
	</head>
	<body style="background-color:#f3f4f7;font-family:system-ui,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';box-sizing:border-box;font-size:16px;padding:5px;">

		<div style="padding:50px;background-color:#fff;margin:60px auto;box-sizing:border-box;font-size:16px;max-width:600px;">
			<h1 style="box-sizing:border-box;font-size:20px;color:#474a50;padding-bottom:10px;font-weight:600;">Activate Account</h1>
			<p style="box-sizing:border-box;font-size:16px;margin:0;padding:10px 0 35px 0;"><?=$success_msg?></p>
			<!--
			<a href="%link%"
				style="display:inline-block;background-color:#3d82d1;text-decoration:none;color:#fff;box-sizing:border-box;font-size:14px;font-weight:500;padding:10px 15px;border-radius:4px;">Activate
				Account</a>
			-->
		</div>
	</body>
</html>