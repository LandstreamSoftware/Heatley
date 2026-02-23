<?php
include 'main.php';
// Current date
//$date = date('Y-m-d\TH:i:s');
$dateNow = new DateTime('now', new DateTimeZone('Pacific/Auckland'));
$date = $dateNow->format('Y-m-d\TH:i:s');

$accountid = $_SESSION['account_id'];

$sqlAccess = "SELECT * FROM accesscontrol WHERE accountID = $accountid";
$resultAccess = $con->query($sqlAccess);

$accessto = -1;

if ($resultAccess->num_rows > 0) {
    while($rowAccess = $resultAccess->fetch_assoc()) {
       $accessto .= "," . $rowAccess["companyID"]; 
    }
}


$sql = "SELECT * FROM bankaccounts WHERE recordOwnerID IN ($accessto) ORDER BY name";
$result = $con->query($sql);

$sql1 = "SELECT * FROM bankaccounts WHERE recordOwnerID = 0 ORDER BY name";
$result1 = $con->query($sql1);

$sql2 = "SELECT * FROM companies WHERE companyTypeID in (2,5) and recordOwnerID in ($accessto) ORDER BY companyName";
$result2 = $con->query($sql2);


?>
<?=template_admin_header('Bank Accounts', 'bank_accounts')?>

<div class="content-title">
    <div class="title">
        <div class="icon">
            <svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M224 0a128 128 0 1 1 0 256A128 128 0 1 1 224 0zM178.3 304h91.4c11.8 0 23.4 1.2 34.5 3.3c-2.1 18.5 7.4 35.6 21.8 44.8c-16.6 10.6-26.7 31.6-20 53.3c4 12.9 9.4 25.5 16.4 37.6s15.2 23.1 24.4 33c15.7 16.9 39.6 18.4 57.2 8.7v.9c0 9.2 2.7 18.5 7.9 26.3H29.7C13.3 512 0 498.7 0 482.3C0 383.8 79.8 304 178.3 304zM436 218.2c0-7 4.5-13.3 11.3-14.8c10.5-2.4 21.5-3.7 32.7-3.7s22.2 1.3 32.7 3.7c6.8 1.5 11.3 7.8 11.3 14.8v17.7c0 7.8 4.8 14.8 11.6 18.7c6.8 3.9 15.1 4.5 21.8 .6l13.8-7.9c6.1-3.5 13.7-2.7 18.5 2.4c7.6 8.1 14.3 17.2 20.1 27.2s10.3 20.4 13.5 31c2.1 6.7-1.1 13.7-7.2 17.2l-14.4 8.3c-6.5 3.7-10 10.9-10 18.4s3.5 14.7 10 18.4l14.4 8.3c6.1 3.5 9.2 10.5 7.2 17.2c-3.3 10.6-7.8 21-13.5 31s-12.5 19.1-20.1 27.2c-4.8 5.1-12.5 5.9-18.5 2.4l-13.8-7.9c-6.7-3.9-15.1-3.3-21.8 .6c-6.8 3.9-11.6 10.9-11.6 18.7v17.7c0 7-4.5 13.3-11.3 14.8c-10.5 2.4-21.5 3.7-32.7 3.7s-22.2-1.3-32.7-3.7c-6.8-1.5-11.3-7.8-11.3-14.8V467.8c0-7.9-4.9-14.9-11.7-18.9c-6.8-3.9-15.2-4.5-22-.6l-13.5 7.8c-6.1 3.5-13.7 2.7-18.5-2.4c-7.6-8.1-14.3-17.2-20.1-27.2s-10.3-20.4-13.5-31c-2.1-6.7 1.1-13.7 7.2-17.2l14-8.1c6.5-3.8 10.1-11.1 10.1-18.6s-3.5-14.8-10.1-18.6l-14-8.1c-6.1-3.5-9.2-10.5-7.2-17.2c3.3-10.6 7.7-21 13.5-31s12.5-19.1 20.1-27.2c4.8-5.1 12.4-5.9 18.5-2.4l13.6 7.8c6.8 3.9 15.2 3.3 22-.6c6.9-3.9 11.7-11 11.7-18.9V218.2zm92.1 133.5a48.1 48.1 0 1 0 -96.1 0 48.1 48.1 0 1 0 96.1 0z"/></svg>
        </div>
        <div class="txt">
            <h2>Bank Accounts</h2>
            <p>View bank accounts.</p>
        </div>
    </div>
</div>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" ) {
    $_id = $_POST["_id"];
    $recordownerid = $_POST["recordownerid"];

//prepare and bind
$sql3 = "UPDATE bankaccounts SET recordOwnerID = '$recordownerid' WHERE _id = '$_id'";

if ($con->query($sql3) === TRUE) {
    echo '<div class="row">
        <div class=\"col-sm-6\">Bank account assigned successfully!</div>
    </div>';

    echo "<div class=\"row\">
        <div class=\"col-sm-2\"><a href=\"bank_accounts.php\" class=\"btn btn-primary\">Back to Bank Accounts</a></div>
    <div class=\"row\">";
} else {
    echo 'Error updating record: ' . $con->error;
}
} else {
?>

<div class="content-block">
    <div class="table">
        <table>
            <thead>
                <tr>
                    <td>Bank</td>
                    <td>Name</td>
                    <td>Account Number</td>
                    <td>Type</td>
                    <td>Holder</td>
                    <td>Status</td>
                    <td>Owner</td>
                </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0) {
                // output data of each row
                while($row = $result->fetch_assoc()) { 
                echo "<tr>
                    <td><img src=\"" . $row["connection_logo"] . "\" style=\"width:70px;\"></a></td>
                    <td style=\"padding-top:25px;\">" . $row["name"] . "</td>
                    <td style=\"padding-top:25px;\">" . $row["formatted_account"] . "</td>
                    <td style=\"padding-top:25px;\">" . $row["type"]. "</td>
                    <td style=\"padding-top:25px;\">" . $row["holder"]. "</td>
                    <td style=\"padding-top:25px;\">" . $row["status"]. "</td>
                    <td style=\"padding-top:25px;\"><a href=/viewcompany.php?companyid=" . $row["recordOwnerID"] . ">" . $row["recordOwnerID"] . "</td>
                </tr>";
                }
            } else { ?>
                <tr>
                    <td colspan="20" class="no-results">There are no accounts.</td>
                </tr>
                
            <?php } ?>
            </tbody>
        </table>
    </div>
    <div class="row">
        <div class="col-sm-2" style="padding-top:20px;"><a href="update_bank_accounts.php?token=2106228793588370e86358956424a14339d1812bc50e301050da049eebe5dd" class="btn btn-primary">Update Bank Accounts</a></div>
    </div>
</div>







<div class="content-title">
    <div class="title">
        <div class="txt" style="padding-top:50px;">
            <h2>Unassigned Bank Accounts</h2>
            <p>These bank accounts have not yet been assigned an owner.</p>
        </div>
    </div>
</div>

<div class="content-block">
    <div class="table">
        <table>
            <thead>
                <tr>
                    <td>Bank</td>
                    <td>Name</td>
                    <td>Account Number</td>
                    <td>Holder</td>
                    <td>Status</td>
                    <td>Owner</td>
                    <td>Submit</td>
                </tr>
            </thead>
            <tbody>
            <?php if ($result1->num_rows > 0) {
                // output data of each row
                while($row1 = $result1->fetch_assoc()) { 
                echo "<tr>
                    <td><img src=\"" . $row1["connection_logo"] . "\" style=\"width:70px;\"></a></td>
                    <td style=\"padding-top:25px;\">" . $row1["name"] . "</td>
                    <td style=\"padding-top:25px;\">" . $row1["formatted_account"] . "</td>
                    <td style=\"padding-top:25px;\">" . $row1["holder"]. "</td>
                    <td style=\"padding-top:25px;\">" . $row1["status"]. "</td>
                    
                        <form id=\"AssignBankAccount\" method=\"post\" action=\"" . htmlspecialchars($_SERVER["PHP_SELF"]) . "?update=1\">
                            <td style=\"padding:20px 10px 0px 0px; width:10%;\">
                                <input hidden class=\"form-control\" id=\"_id\" type=\"text\" name=\"_id\" value=\"" . $row1["_id"] . "\">
                                <select class=\"form-control\" id=\"recordownerid\" name=\"recordownerid\">
                                    <option value=\"0\"> - Select an Owner - </option>";
                                    while($row2 = $result2->fetch_assoc()) {
                                        echo "<option value=\"" . $row2["idcompany"] . "\">". $row2["companyName"] . "</option>";
                                    }
                                echo "</select>
                            </td>
                            <td style=\"padding-top:25px; text-align:left;\">
                                <input type=\"submit\" value=\"Submit\" class=\"btn btn-primary\" style=\"width:100px\">
                            </td>
                        </form>
                        </div>
                    </div>
                    
                    </td>
                </tr>";
                }
            } else { ?>
                <tr>
                    <td colspan="20" class="no-results">0 unassigned accounts.</td>
                </tr>
                
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row">
  <div class="col-sm-2" style="padding-top:20px;"><a href="addbankaccount.php" class="btn btn-primary">Add New Bank Account</a></div>
</div>
<?php } ?>

<?=template_admin_footer()?>




<!--
//Get the list of bank accounts from Akahu

$sql2 = "SELECT * FROM connections WHERE userToken IS NOT NULL and recordOwnerID IN ($accessto)";
$result2 = $con->query($sql2);

if ($result2->num_rows > 0) {
    // output data of each row
    while($row2 = $result2->fetch_assoc()) {
        $authorization = $row2["appToken"];
        $x_akahu_id = $row2["userToken"];
    }
}

    $curl = curl_init();

    curl_setopt_array($curl, [
      CURLOPT_URL => "https://api.akahu.io/v1/accounts",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => [
        "accept: application/json",
        "Authorization: $authorization",
        "X-Akahu_id: $x_akahu_id"
      ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
      echo "cURL Error #:" . $err;
    } else {
      echo $response;
    }
-->