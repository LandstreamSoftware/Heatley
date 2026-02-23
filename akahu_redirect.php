<?php
// Include the main.php file
include 'main.php';

$accountid = $_SESSION['account_id'];

//Set $code to empty string
$code = "";

$queryString = $_SERVER['QUERY_STRING'];
parse_str($queryString, $parsedQuery);
if (isset($parsedQuery['code'])) {
    $code = $parsedQuery['code'];
}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Akahu</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
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

		</style>
	
	</head>
<body>

<header class="header">
	<div class="wrapper">
		<nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
  			<div class="container-fluid">
    			<img height="40px" src="img/building_greyscale.png" style="padding-right:10px;"><img height="30px" src="img/LeaseManager.png">
  			</div>
		</nav>
	</wrapper>
</header>

<div class="content">

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg\" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Akahu Account Connection</h2>
	</div>
</div>

<div class="block">

<?php
//echo "Query string: " . $queryString . "<br>";
if (isset($parsedQuery['error'])) {
	if($parsedQuery['error'] == "access_denied") {
		//	header("Location: end_akahu_session.php");
		echo "Operation cancelled by user.";
		exit;
	}
}
if (isset($parsedQuery['error'])) {
	echo "Error: " . $parsedQuery['error'] . "<br>";
}

//<div class="row">
//	<div class="col-sm-4" style="padding-top:20px;">
//		<button class="btn btn-primary" onclick="closeWindow()">Close this window</button>
//	</div>
//</div>




if (isset($parsedQuery['error_description'])) {
	echo "Error Description: " . $parsedQuery['error_description'] . "<br>";
}

if (isset($parsedQuery['event'])) {
	$event = $parsedQuery['event'];
	switch ($event) {
	case "UPDATE":
	case "ACCEPT":
//echo "Here is the returned code: " . $code. "<br>";

		//Get the client_id and secret
		$sqlauth = "SELECT * FROM akahu_authorization";
		$resultauth = $con->query($sqlauth);
		while($rowauth = $resultauth->fetch_assoc()) {
		    $client_id = $rowauth["appToken"];
			$client_secret = $rowauth["appSecret"]; 
		}
		//Exchange an Authorization Code for a Token
		$url = "https://api.akahu.io/v1/token";
		// The data to send as JSON
		$data = [
		    'grant_type' => 'authorization_code',
			'code' => $code,
			'redirect_uri' => 'http://leasemanager.co.nz/akahu_redirect.php',
			'client_id' => $client_id,
			'client_secret' => $client_secret
		];
		// Encode data to JSON
		$jsonData = json_encode($data);
		// Initialize a cURL session
		$ch = curl_init($url);
		// Set cURL options
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
		    'Content-Type: application/json',
		    'Content-Length: ' . strlen($jsonData)
		]);
//echo "json data: " . $jsonData . "<br>";
		// Execute the request
		$response = curl_exec($ch);
		// Check for errors
		if (curl_errno($ch)) {
		    echo 'cURL Error: ' . curl_error($ch);
		} //else {
//echo "Response: " . $response . "<br>";
			$responseArray = json_decode($response, true);
			// Check if decoding was successful
			if (json_last_error() === JSON_ERROR_NONE) {
		   		// Extract the value of the 'success' key
		    	if (isset($responseArray['success'])) {
		        	$success = $responseArray['success'];
					if (isset($responseArray['error'])) {
						echo "Error: " . $responseArray['error'] . "<br>";
						if (isset($responseArray['error_description'])) {
							echo "Error Description: " . $responseArray['error_description'] . "<br>";
						}
					} else {
						$access_token = $responseArray['access_token'];
						//Save the access_token against the account
						//Check to see if the token is already saved
						$sqlexistingtoken = "SELECT akahu_access_token FROM accounts WHERE username <> 'admin' and akahu_access_token = '$access_token'";
    					$resultexistingtoken = $con->query($sqlexistingtoken);
						if ($resultexistingtoken->num_rows == 0) {
							//Update the user's account record with the access token
							$stmt = $con->prepare('UPDATE accounts SET akahu_access_token = ? WHERE id = ?');
							$stmt->bind_param('si', $access_token, $accountid);
							$stmt->execute();
							$stmt->close();

							//Run the update_bank_accounts.php
							//header("Location:update_bank_accounts.php");

							echo "Success!<br><br>Akahu Account Connection completed successfully.<br>
							You can close this window.<br>";
						}
						if ($resultexistingtoken->num_rows == 1) {
							//This user account already has this token saved

							//Run the update_bank_accounts.php
							//header("Location:update_bank_accounts.php");

							echo "Akahu Account Connection completed successfully.<br>";
						}
					}
		    	} else {
		        	echo "'success' key not found in the response.";
		    	}
			} else {
		    	echo "Failed to decode JSON: " . json_last_error_msg();
			}
		//}

		// Close the cURL session
		curl_close($ch);
		break;
	
	case "REVOKE":
		echo "Access to all bank accounts has been revoked.<br>Refresh the bank account list to show this change.";
		//Remove all bank accounts
		break;
	default:
		break;
	}
}
?>

<div class="row">
	<div class="col-sm-4" style="padding-top:20px;">
		<button class="btn btn-primary" onclick="closeWindow()">Close this window</button>
	</div>
</div>

<script>
	function closeWindow() {
			window.close();
	}
</script>
</div>