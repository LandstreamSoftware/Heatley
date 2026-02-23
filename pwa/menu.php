<?php

echo '<!DOCTYPE html>
<html>
	<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Property Inspection</title>
    <link rel="manifest" href="/manifest.json" />
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/pwa/css/styles.css" />
</head>
	<body>


    <header class="header">

			<div class="wrapper">

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
		

// Template footer function
function template_footer() {
	// Output the footer HTML
	echo '</div>
	<div class="container-fluid" style="min-height:60px; background-color:#282f3b">
    	<div class="container" style="padding:15px 0;">
        	<div style="color:#87919e; font-size:14px; font-weight:200;">&copy;Landstream Commercial Ltd</div>
			<div style="color:#87919e; font-size:14px; font-weight:200;"><a href="privacypolicy.php" style="text-decoration:none;">- Privacy Policy</a></div>
    	</div>
	</div>
	</body>
</html>';
}
