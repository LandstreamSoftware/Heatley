<?php

// Template header function
function template_header($title) {
    echo '<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, minimum-scale=1">
		<title>' . $title . '</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link href="css/bootstrap.css" rel="stylesheet" type="text/css">
	</head>
	<body>';

	echo '<header class="header">

			<div class="wrapper">

		<!--		<h1>Lease Management App</h1>  -->

				<!-- If you prefer to use a logo instead of text uncomment the below code and remove the above h1 tag and replace the src attribute with the path to your logo image
				<img src="https://via.placeholder.com/200x45" width="200" height="45" alt="Logo" class="logo">
				-->

				<!-- Responsive menu toggle icon -->
				<input type="checkbox" id="menu">
				<label for="menu"></label>
				
				<nav class="menu">
                    <a href="index.php">Home</a>
					<a href="contact-us.php">Contact Us</a>
                    <a href="login.php">Log In</a>
				</nav>
			</div>

		</header>

		<div class="content">';
		
}
// Template footer function
function template_footer() {
	// Output the footer HTML
	echo '</div>
	<div class="container-fluid" style="min-height:60px; background-color:#282f3b">
    	<div class="container" style="padding:15px 0;">
        	<div style="color:#87919e; font-size:14px; font-weight:200;">&copy;Landstream Software</div>
        	<div style="color:#87919e; font-size:14px; font-weight:200;"><a href="privacypolicy.php" style="text-decoration:none">Privacy Policy</a></div>
    	</div>
	</div>
	</body>
</html>';
}
