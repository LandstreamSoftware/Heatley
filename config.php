<?php
// the Heatley Portal
// Your MySQL database hostname.
define('db_host','localhost');
// Your MySQL database username.
define('db_user','root');
// Your MySQL database password.
define('db_pass','Kwu8slb3!234');
// Your MySQL database name.
define('db_name','xeroreports');
// Your MySQL database charset.
define('db_charset','utf8');
// The secret key used for hashing purposes. Change this to a random unique string.
define('secret_key','yoursecretkey');
// The base URL of the PHP login system (e.g. https://example.com/phplogin/). Must include a trailing slash.
define('base_url','localhost/');
/* Registration */
// If enabled, the user will be redirected to the homepage automatically upon registration.
define('auto_login_after_register',false);
// If enabled, the account will require email activation before the user can login.
define('account_activation',true);
// If enabled, the user will require admin approval before the user can login.
define('account_approval',true);
/* Mail */
// If enabled, mail will be sent upon registration with the activation link, etc.
define('mail_enabled',true);
// Send mail from which address?
define('mail_from','barry@landstream.co.nz');
// The name of your website/business.
define('mail_name','Heatley Portal');
// If enabled, you will receive email notifications when a new user registers.
define('notifications_enabled',true);
// The email address to send notification emails to.
define('notification_email','barry@landstream.co.nz');
// Is SMTP server?
define('SMTP',true);
// SMTP Hostname
define('smtp_host','mail.smtp2go.com');
// SMTP Port number
define('smtp_port',80);
// SMTP Username
define('smtp_user','leasemanager.co.nz');
// SMTP Password
define('smtp_pass','4HwQTOUUfcAwgvlk');
// SMTP Secure
define('smtp_secure','tls');
//Root URL
define('root_url','localhost');
/* Dev */
define('dev_base_url','192.168.1.127/');

define('LOG_FILE_PATH', '__DIR__ . /cronlogfile.txt');
define('XERO_LOG_FILE_PATH', __DIR__ . '/xerologfile.txt');

define('public_key_path', '__DIR__ . /public_key.pem');

define('public_key_compatible_path', '__DIR__ . /public_key.pem');

define('xero_redirect_uri', 'http://localhost/xero-php-oauth2-app/callback.php');
/* Endcryption key for Xero encrypting the client secret */
define('APP_SECRET_KEY', '5q4q6mTw7D+PDsEq8FU/l+x5TMePkVWsGdbnzxobYiE=');
?>