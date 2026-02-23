<?php
include 'main.php';
// Now we check if the data was submitted, isset() function will check if the data exists.
if (!isset($_POST['firstname'], $_POST['lastname'], $_POST['mobilenumber'], $_POST['emailaddress'], $_POST['companyname'], $_POST['message'])) {
	// Could not get the data that should have been sent.
	exit('Error: Please complete the registration form!');
}
// Make sure the submitted registration values are not empty.
if (empty($_POST['firstname']) || empty($_POST['lastname']) || empty($_POST['mobilenumber']) || empty($_POST['emailaddress']) || empty($_POST['companyname']) || empty($_POST['message'])) {
	// One or more values are empty.
	exit('Error: Please complete the registration form!');
}
// Check to see if the email is valid.
if (!filter_var($_POST['emailaddress'], FILTER_VALIDATE_EMAIL)) {
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
if (!preg_match('/^[0-9 .]+$/', $_POST['mobilenumber'])) {
    exit('Error: Mobile number must contain only numbers and space!');
}
// Company name must contain only characters and hyphen.
if (!preg_match('/^[a-zA-Z .-]+$/', $_POST['companyname'])) {
    exit('Error: Company name must contain only letters, hyphen, space and dot!');
}
// message must contain only characters, space, hyphen, dot.
if (!preg_match('/^[a-zA-Z .-]+$/', $_POST['companyname'])) {
    exit('Error: Message must contain only letters, hyphen, space or dot!');
}


// Send notification email
if (notifications_enabled) {
    send_contact_us_email($_POST['firstname'], $_POST['lastname'], $_POST['mobilenumber'], $_POST['emailaddress'], $_POST['companyname'], $_POST['message']);

    echo 'Success: Your message has been sent!';

}

?>