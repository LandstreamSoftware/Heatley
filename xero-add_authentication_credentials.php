<?php
// Include the main.php file
include 'main.php';
// Check if the user is logged in, if not then redirect to login page
check_loggedin($con);
// Template code below

$accountid = $_SESSION['account_id'];

$sqlAccess = "SELECT * FROM accesscontrol WHERE accountID = $accountid";
$resultAccess = $con->query($sqlAccess);

$accessto = -1;

if ($resultAccess->num_rows > 0) {
    while ($rowAccess = $resultAccess->fetch_assoc()) {
        $accessto .= "," . $rowAccess["companyID"];
    }
}

$sql9 = "SELECT * FROM accounts WHERE id = $accountid";
$result9 = $con->query($sql9);

while ($row9 = $result9->fetch_assoc()) {
    $recordownerid = $row9["companyID"];
}
?>

<?= template_header('Add Xero Authentication Credentials') ?>

<div class="page-title">
    <div class="icon">
        <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
            <path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z" />
        </svg>
    </div>
    <div class="wrap">
        <h2>Add Xero Authentication Credentials</h2>
    </div>
<!-- Help button
    <div class="ms-auto">
		<a href="/help/#/connecting-to-xero" target="_blank" class="btn btn-outline-success btn-sm d-inline-flex align-items-center" role="button">Help</a>
	</div>
-->
</div>

<div class="block">

    <?php
    // define variables and set to empty values
    $scopes = "openid profile email offline_access accounting.settings accounting.transactions accounting.contacts accounting.transactions.read accounting.settings.read";
    $clientid = $clientsecret = "";
    $redirecturi = xero_redirect_uri;
    $scopesErr = $clientidErr = $clientsecretErr = $redirecturiErr = "";
    $tokenexpiresat = date('Y-m-d H:i:s');

    $sql1 = "SELECT * FROM xero_oauth_tokens  where companyID = 5 and recordOwnerID = $recordownerid";
    $result1 = $con->query($sql1);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (empty($_POST["clientid"])) {
            $clientidErr = "Client ID is required";
        } else {
            $clientid = test_input($_POST["clientid"]);
            //check if the field only contains letters dash or white space
            if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $clientid)) {
                $clientidErr = "Only letters, dash and spaces allowed";
            }
        }

        if (empty($_POST["clientsecret"])) {
            $clientsecretErr = "Client Secret is required";
        } else {
            $clientsecret = test_input($_POST["clientsecret"]);
            //check if the field only contains letters dash or white space
            if (!preg_match("/^[a-zA-Z-_0-9āēīōūĀĒĪŌŪ' ]*$/", $clientsecret)) {
                $clientsecretErr = "Only letters, underscore, dash and spaces allowed";
            }
        }

        if (empty($_POST["redirecturi"])) {
            $redirecturiErr = "Redirect URI is required";
        } else {
            $redirecturi = test_input($_POST["redirecturi"]);
            //check if the field only contains letters dash or white space
            if (filter_var($redirecturi, FILTER_VALIDATE_URL)) {
                // Valid url
            } else {
                $redirecturiErr = "Not a valid URI";
            }
        }
    }

    function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }



    if ($_SERVER["REQUEST_METHOD"] == "POST" and $scopesErr == NULL and $clientidErr == NULL and $clientsecretErr == NULL and $redirecturiErr == NULL) {

        //prepare and bind
        $stmt = $con->prepare("INSERT INTO xero_oauth_tokens (companyID, scopes, client_id, client_secret, redirect_uri, access_token_expires_at, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssi", $recordownerid, $scopes, $clientid, $clientsecret, $redirecturi, $tokenexpiresat, $recordownerid);

        if ($stmt->execute()) {
            echo '<div class=\"row\">
        <table class="table table-hover">
            <tbody>
                <tr class="success">
                    <td>Success!</td>
                </tr>
            </tbody>
        </table>';
        ?>
        <div class="row">
            <div class="col-sm-9">
                <a href="../xero-php-oauth2-app/authorization.php?cid=<?php echo $recordownerid; ?>"><img src="../img/connect-white.svg"></a>
			</div>
            <div class="col-sm-3">
                <a href="profile.php" class="btn btn-primary">Back to Profile</a>
            </div>
        </div>
        <?php
        } else {
        ?>   
        <div class="row">
            <div class="col-sm-3">
				<?php echo 'Error creating record: ' . $con->error; ?>
			</div>
            <div class="col-sm-9">
                <a href="profile.php" class="btn btn-primary">Back to Profile</a>
        </div>
        <?php
        }

    } else {

    ?>
        <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <!--<label class="form-label col-sm-4" for="redirecturi">Redirect URI:</label>-->
                <div class="col-sm-10"><input class="form-control" id="redirecturi" type="text" name="redirecturi" value="<?php echo $redirecturi; ?>" hidden></div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $redirecturiErr; ?></span></div>
            </div>
            <div class="form-group">
                <label class="form-label col-sm-4" for="clientid">Client id:<span class="text-body-tertiary"><br>(copy from your Xero 'App')</span></label>
                <div class="col-sm-10"><input class="form-control" id="clientid" type="text" name="clientid" value="<?php echo $clientid; ?>"></div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $clientidErr; ?></span></div>
            </div>
            <div class="form-group">
                <label class="form-label col-sm-4" for="clientsecret">Client secret:<span class="text-body-tertiary"><br>(copy from your Xero 'App')</span></label>
                <div class="col-sm-10"><input class="form-control" id="clientsecret" type="text" name="clientsecret" value="<?php echo $clientsecret; ?>"></div>
                <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $clientsecretErr; ?></span></div>
            </div>
            
            <input class="form-control" id="scopes" type="text" name="scopes" value="<?php echo $scopes; ?>"hidden>

            <input class="form-control" id="tokenexpiresat" type="text" name="tokenexpiresat" value="<?php echo $tokenexpiresat; ?>" hidden>

            <div class="form-group">
                <div class="col-sm-1" style="padding-top:40px;"><input type="submit" value="Save" class="btn btn-primary" style="width:100px"></div>
            </div>
        </form>

        <div class="row">
            <div class="col-sm-3" style="padding:40px;"><a href="/help/#/connecting-to-xero" target="_blank" class="btn btn-outline-success btn-sm d-inline-flex align-items-center" role="button">How to find the values to enter in the above fields</a></div>
        </div>
<!--
        <div class="row">
            <p>To obtain the values for the fields above, follow these steps to create a Xero app that you will connect LeaseManager to.</p>

            <p>
                * Login to the Xero developer center with your Xero login <a href="https://developer.xero.com/myapps">(https://developer.xero.com/myapps)</a><br>
                * Click "New App"<br>
                * Enter "LeaseManager" as the App name and choose the Web app option.<br>
                * Enter "https://leasemanager.co.nz" in the Company or application URL field.<br>
                * Enter "https://leasemanager.co.nz/xero-php-oauth2-app/callback.php" in the Redirect URI field.<br>
                * Agree to terms and conditions and click "Create App".<br><br>
                * Go to the Configuration page and click "Generate a secret" button, but don't hit save otherwise the secret will disappear.<br>
                * Copy your client id and client secret so you can enter the values in LeaseManager.<br>
                * Click the "Save" button. You secret is now hidden.<br>
                * Leave the "Login URL for launcher" field empty.<br>
                * Click on the "Save" button.<br><br>
                * In LeaseManager enter the Client id and Client secret into the above fields.<br>
                * Click "Save". You are now ready to 

            </p>
        </div>
        <div class="row">
            <div class="col-sm-3" style="padding:40px;"><img src="img/xero_app_creation_1.png" height="600px"></div>
            <div class="col-sm-3" style="padding:40px;"><img src="img/xero_app_creation_2.png" height="500px"></div>
        </div>
-->
        <div class="row">

        <?php
    }

    $con->close();
        ?>

        </div>

        <?= template_footer() ?>