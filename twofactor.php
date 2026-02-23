<?php
include 'main.php';
// Output message
$msg = '';
// Verify the ID and email provided
if (isset($_SESSION['tfa_id'])) {
    // Prepare our SQL, preparing the SQL statement will prevent SQL injection.
    $stmt = $con->prepare('SELECT email, tfa_code, username, id, firstname, role FROM accounts WHERE id = ?');
    $stmt->bind_param('i', $_SESSION['tfa_id']);
    $stmt->execute();
    // Store the result so we can check if the account exists in the database.
    $stmt->store_result();
    // If the account exists with the email & ID provided...
    if ($stmt->num_rows > 0) {
    	$stmt->bind_result($email, $tfa_code, $username, $id, $firstname, $role);
    	$stmt->fetch();
    	$stmt->close();
        // Account exist
        if (isset($_POST['code'])) {
            // Code submitted via the form
            if ($_POST['code'] == $tfa_code) {
                // Code accepted, update the IP address
                $ip = $_SERVER['REMOTE_ADDR'];
                $stmt = $con->prepare('UPDATE accounts SET ip = ?, tfa_code = "" WHERE id = ?');
                $stmt->bind_param('si', $ip, $id);
                $stmt->execute();
                $stmt->close();
                // Destroy tfa session variables
                unset($_SESSION['tfa_id']);
                // Authenticate the user
                session_regenerate_id();
                $_SESSION['account_loggedin'] = TRUE;
                $_SESSION['account_name'] = $username;
                $_SESSION['account_id'] = $id;
                $_SESSION['account_role'] = $role;
			    $_SESSION['user_name'] = $firstname;

                // Redirect to home page
                header('Location: home.php');
                exit;
            } else {
                $msg = 'Incorrect code provided!';
            }
        } else {
            // Generate a unique code
            $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
            // Update the account with the new code
            $stmt = $con->prepare('UPDATE accounts SET tfa_code = ? WHERE id = ?');
            $stmt->bind_param('si', $code, $id);
            $stmt->execute();
            $stmt->close();
            // Send the code to the user's email
            send_twofactor_email($email, $code);
        }
    } else {
        exit('Invalid request!');
    }
} else {
    exit('Invalid request!');
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Two-factor Authentication</title>
		<link href="style.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<div class="login">

			<h1>Two-factor Authentication</h1>

            <p style="padding:15px 0 0 0;margin:0;">Please enter the code that was sent to your email address below.</p>

			<form action="" method="post" class="form">

				<label class="form-label" for="code">Code</label>
				<div class="form-group mar-bot-5">
                    <svg class="form-icon-left" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/></svg>
					<input class="form-input" type="text" name="code" placeholder="Enter Code" id="code" required>
				</div>
				
				<?php if ($msg): ?>
				<div class="msg error">
					<?=$msg?>
				</div>
				<?php endif; ?>

				<button class="btn blue" type="submit">Submit</button>

			</form>

		</div>
	</body>
</html>