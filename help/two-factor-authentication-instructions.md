# Instructions

## Add-on Details

The Two-factor Authentication add-on will require the user to provide a code if the IP address has changed, this code is sent to the user's email address.

## How To Add

In phpMyAdmin select the "phplogin" database and import the "twofactor.sql" SQL file.

Copy both the "twofactor-email-template.html" and "twofactor.php" files to your "phplogin" directory.

Edit the "main.php" file and add:

```
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
			$mail-&gt;isSMTP();
			$mail-&gt;Host = smtp_host;
			$mail-&gt;SMTPAuth = true;
			$mail-&gt;Username = smtp_user;
			$mail-&gt;Password = smtp_pass;
			$mail-&gt;SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
			$mail-&gt;Port = smtp_port;
		}
		// Recipients
		$mail-&gt;setFrom(mail_from, mail_name);
		$mail-&gt;addAddress($email);
		$mail-&gt;addReplyTo(mail_from, mail_name);
		// Content
		$mail-&gt;isHTML(true);
		$mail-&gt;Subject = 'Your Access Code';
		// Read the template contents and replace the &quot;%code%&quot; placeholder with the above variable
		$email_template = str_replace('%code%', $code, file_get_contents('twofactor-email-template.html'));
		// Set email body
		$mail-&gt;Body = $email_template;
		$mail-&gt;AltBody = strip_tags($email_template);
		// Send mail
		$mail-&gt;send();
	} catch (Exception $e) {
		// Output error message
		exit('Error: Message could not be sent. Mailer Error: ' . $mail-&gt;ErrorInfo);
	}
}
```

Edit the "authenticate.php" file and find this line:

```
$stmt = $con->prepare('SELECT id, password, remember_me_code, activation_code, role, username, approved FROM accounts WHERE username = ?');
```

Replace with:

```
$stmt = $con->prepare('SELECT id, password, remember_me_code, activation_code, role, username, approved, ip FROM accounts WHERE username = ?');
```

Find:

```
$stmt->bind_result($id, $password, $remember_me_code, $activation_code, $role, $username, $approved);
```

Replace with:

```
$stmt->bind_result($id, $password, $remember_me_code, $activation_code, $role, $username, $approved, $ip);
```

Find:

```
echo 'Error: Your account has not been approved yet!';
```

Add below:

```
} else if ($_SERVER['REMOTE_ADDR'] != $ip) {
	// Two-factor authentication required
	$_SESSION['tfa_id'] = $id;
	echo 'tfa: twofactor.php';
```

Edit the "index.php" <span style="color:green;">("login.php")</span> file and find this line:

```
window.location.href = 'home.php';
```

Add after:

```
} else if (result.includes('tfa:')) {
    window.location.href = result.replace('tfa: ', '');
```

Edit the "register-process.php" file and find this line:

```
$stmt = $con->prepare('INSERT INTO accounts (username, password, email, activation_code, role, registered, last_seen, approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
```

Replace with:

```
$stmt = $con->prepare('INSERT INTO accounts (username, password, email, activation_code, role, registered, last_seen, approved, ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
```

Find this line:

```
$stmt->bind_param('sssssssi', $_POST['username'], $password, $_POST['email'], $activation_code, $role, $date, $date, $approved);
```

Replace with:

```
$ip = $_SERVER['REMOTE_ADDR'];
$stmt->bind_param('sssssssis', $_POST['username'], $password, $_POST['email'], $activation_code, $role, $date, $date, $approved, $ip);
```
