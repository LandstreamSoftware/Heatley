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
    while($rowAccess = $resultAccess->fetch_assoc()) {
       $accessto .= "," . $rowAccess["companyID"]; 
    }
}

?>

<?=template_header('Edit XeroAuthentication Credentials')?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function showalert($alertmessage) {
        Swal.fire($alertmessage);
    }
</script>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Edit Xero Authentication Credentials</h2>
	</div>
</div>

<div class="block">

<?php
// define variables and set to empty values

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$companyid = $QueryParameters['cid'];


$sql = "SELECT * from xero_oauth_tokens WHERE companyID = $companyid";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $tenantid = $row["tenant_id"];
        $tokenexpiresat = $row["access_token_expires_at"];
        $accesstoken = $row["access_token"];
        $refreshtoken = $row["refresh_token"];
        $scopes = $row["scopes"];
        $tokenversion = $row["token_version"];
        $clientid = $row["client_id"];
        $clientsecret = $row["client_secret"];
        $redirecturi = $row["redirect_uri"];
    } 
} else {
    $tenantid = $tokenexpiresat = $accesstoken = $refreshtoken = $scopes = $tokenversion = $clientid = $clientsecret = "";
}

$showAlert = null;

$tenantidErr = $tokenexpiresatErr = $accesstokenErr = $refreshtokenErr = $scopesErr = $tokenversionErr = $clientidErr = $clientsecretErr = $redirecturiErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['manual_refresh_token'])) {

    $tenantid = test_input($_POST["tenantid"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $tenantid)) {
        $tenantidErr = "Only letters, dash and spaces allowed";
    }
    
    if (empty($_POST["clientid"])) {
        $clientidErr = "ClientID is required";
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

    if (empty($_POST["scopes"])) {
        $scopesErr = "Scopes is required";
    } else {
        $scopes = test_input($_POST["scopes"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z._0-9āēīōūĀĒĪŌŪ' ]*$/", $scopes)) {
            $scopesErr = "Only letters, underscore, dot and spaces allowed";
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

    $tokenversion = test_input($_POST["tokenversion"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $tokenversion)) {
        $tokenversionErr = "Only numbers allowed";
    }

    if (empty($_POST["tokenexpiresat"])) {
        $tokenexpiresat = date('Y-m-d H:i:s');
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['manual_refresh_token']) && $_POST['manual_refresh_token'] == '1') {

    $oldToken = (string)$accesstoken;
    $newToken = (string)check_xero_token_expiry($con, $companyid);
    $accesstoken = $newToken;
    $message = ($newToken !== $oldToken)
    ? 'Token has been refreshed'
    : 'Token is still current';
    echo '
    <script>
        Swal.fire({
        icon: "success",
            iconColor: "#0cc43aff",
            text: "'.$message.'",
            width: "350px",
            showCancelButton: false,
            confirmButtonColor: "#0cc43a",
            confirmButtonText: "OK",
        })
    </script>';
    
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}


if ($_SERVER["REQUEST_METHOD"] == "POST" and !isset($_POST['manual_refresh_token']) and $tenantidErr == NULL and $tokenexpiresatErr == NULL and $accesstokenErr == NULL and $refreshtokenErr == NULL and $scopesErr == NULL and $tokenversionErr == NULL and $clientidErr == NULL and $clientsecretErr == NULL and $redirecturiErr == NULL) {

    //prepare and bind
$sql2 = "UPDATE xero_oauth_tokens SET tenant_id = '$tenantid', access_token_expires_at = '$tokenexpiresat', access_token = '$accesstoken', refresh_token = '$refreshtoken', scopes = '$scopes', token_version = '$tokenversion', client_id = '$clientid', client_secret = '$clientsecret', redirect_uri = '$redirecturi' WHERE companyID = $companyid";

    if ($con->query($sql2) === TRUE) {

        echo '<table class="table table-hover">
        <tbody>
            <tr class="success">
                <td>Success!</td>
            </tr>
        </tbody>
        </table>';

        echo "<div class=\"row\">
            <div class=\"col-sm-2\"><a href=\"profile.php\" class=\"btn btn-primary\">Back to Profile</a></div>
        </div>
        <div class=\"row\">";
    } else {
        echo 'Error updating record: ' . $con->error;
    }
} else {

    if ($result->num_rows == 0) {

        ?>


            <div class="row">
                <p>Click on the button below to log into your Xero account and create the necessary Authentication Credetials.</p>
                
            </div>
            <div class="col-sm-1" style="padding:40px;">
                <a href="../xero-php-oauth2-app/authorization.php?cid=<?php echo $companyid; ?>"><img src="../img/connect-white.svg"></a>
            </div>


        <div class="row">
        <?php
    } else {
        $expiredAt_date = date_create($tokenexpiresat);
        $start = new DateTimeImmutable('now');
		$interval = $start->diff($expiredAt_date);
		$time_till_expire = "Expires in ".($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i . "minutes";
        if ($start > $expiredAt_date) {
            $time_till_expire = "Expired";
        }
        ?>

        <div id="token-refresh-result"></div>

        <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?cid='.$companyid);?>">
            <div class="form-group">
            <label class="form-label col-sm-4" for="clientid">Client ID: <span class="text-danger">*</span></label>
            <div class="col-sm-10"><input class="form-control" id="clientid" type="text" name="clientid" value="<?php echo $clientid;?>"></div>
            <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $clientidErr;?></span></div>
        </div>
        <div class="form-group">
            <label class="form-label col-sm-4" for="clientsecret">Client Secret: <span class="text-danger">*</span></label>
            <div class="col-sm-10"><input class="form-control" id="clientsecret" type="text" name="clientsecret" value="<?php echo $clientsecret;?>"></div>
            <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $clientsecretErr;?></span></div>
        </div>
        <div class="form-group">
            <label class="form-label col-sm-4" for="tenantid">Tenant ID: <br><span class="text-body-tertiary">(automatically generated)</span></label>
            <div class="col-sm-10"><input class="form-control" id="tenantid" type="text" name="tenantid" value="<?php echo $tenantid;?>"></div>
            <?php
            if ($tenantid == 0 || $tenantid == null) {
                ?><div class="col-sm-2"><span class="error"><span class="text-danger">NO TENANT ID: Open a new incognito window and repeat the authentication again</span></div><?php
            } else {
                ?><div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $tenantidErr;?></span></div><?php
            }
            ?>
        </div>
        <div class="form-group">
            <label class="form-label col-sm-4" for="scopes">Scopes: <span class="text-danger">*</span></label>
            <div class="col-sm-10"><input class="form-control" id="scopes" type="text" name="scopes" value="<?php echo $scopes;?>"></div>
            <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $scopesErr;?></span></div>
        </div>
        <div class="form-group">
            <label class="form-label col-sm-4" for="tokenversion">Token Version: <br><span class="text-body-tertiary">(automatically generated)</span></label>
            <div class="col-sm-3"><input class="form-control" id="tokenversion" type="text" name="tokenversion" value="<?php echo $tokenversion;?>"></div>
            <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $tokenversionErr;?></span></div>
        </div>
        
        <div class="form-group">
            <label class="form-label col-sm-4" for="accesstoken">Access Token:<br><span class="text-body-tertiary">(automatically generated)</span></label>
            <div class="col-sm-10"><textarea class="form-control" id="accesstoken" name="accesstoken" rows="15"><?php echo $accesstoken;?></textarea></div>
            <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $accesstokenErr;?></span></div>
        </div>
        <div class="form-group">
            <label class="form-label col-sm-4" for="refreshtoken">Refresh Token:<br><span class="text-body-tertiary">(automatically generated)</span></label>
            <div class="col-sm-10"><input class="form-control" id="refreshtoken" type="text" name="refreshtoken" value="<?php echo $refreshtoken;?>"></div>
            <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $refreshtokenErr;?></span></div>
        </div>
        <div class="form-group">
            <label class="form-label col-sm-4" for="tokenexpiresat">Token expires at:<br><span class="text-body-tertiary">(<?php echo $time_till_expire;?>)</span></label>
            <div class="col-sm-6"><input class="form-control" id="tokenexpiresat" type="text" name="tokenexpiresat" value="<?php echo $tokenexpiresat;?>"></div>
            <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $tokenexpiresatErr;?></span></div>
        </div>
        <div class="form-group">
            <label class="form-label col-sm-4" for="redirecturi">Redirect URI: <span class="text-danger">*</span></label>
            <div class="col-sm-10"><input class="form-control" id="redirecturi" type="text" name="redirecturi" value="<?php echo $redirecturi;?>"></div>
            <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $redirecturiErr;?></span></div>
        </div>
        
        <div class="form-group">
            <div class="col-sm-3" style="padding-top:40px;"><input type="submit" value="Save" class="btn btn-primary" style="width:100px"></div>
            <div class="col-sm-2" style="padding-top:40px;"><a href="profile.php" class="btn btn-primary">Cancel</a></div>
        </div>
        </form>

        <div class="row">
            <div class="col-sm-10" style="text-align:right;">
                <form id="manualRefreshForm" method="post" style="display:inline">
                    <input type="hidden" name="manual_refresh_token" value="1">
                    <a href="#"  class="btn btn-primary" onclick="document.getElementById('manualRefreshForm').submit(); return false">Manually refresh token</a>
                </form>
                
            </div>
            <div class="col-sm-2" style="text-align:right;">
                <a href="../xero-php-oauth2-app/authorization.php?cid=<?php echo $companyid; ?>"><img src="../img/connect-white.svg"></a>
            </div>
        </div>
    
        <div class="row">
        <?php

        if ($showAlert !== null): ?>
        <script>
            alert(<?=  json_encode($showAlert) ?>);
        </script>
        <?php endif;
    }

}

$con->close();
?>

</div>

<?=template_footer()?>