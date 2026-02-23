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

$sqluser = "SELECT * FROM accounts WHERE id = $accountid";
$resultuser = $con->query($sqluser);

//while($rowuser = $resultuser->fetch_assoc()) {
//    $recordownerid = $rowuser["companyID"]; 
//}
?>

<?=template_header('Add Lease')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Add Lease</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">     

<?php
    // define variables and set to empty values
$commencement = $tenantid = $premisesid = $term = $rightsofrenewal = $leaseexpirydate = $annualrentpremises = $annualrentcarparks = $signedon = $signedbyid = $guarantorid = $bondamount = $leasestatusid = "";
$commencementErr = $tenantidErr = $premisesidErr = $termErr = $rightsofrenewalErr = $leaseexpirydateErr = $annualrentpremisesErr = $annualrentcarparksErr = $signedonErr = $signedbyidErr = $guarantoridErr = $bondamountErr = $leasestatusidErr = "";

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
if(empty($QueryParameters['tenantid'])){
    $QPtenantid = "";
}else{
    $QPtenantid = $QueryParameters['tenantid'];
    $tenantid = $QueryParameters['tenantid'];
}
if(empty($QPrenewalid)){
    $QPpropertyid ="";
}else{
    $QPpropertyid = $QueryParameters['propertyid'];
    $propertyid = $QueryParameters['propertyid'];
}

//Query for dropdowns
$sql1 = "SELECT idcompany, companyName FROM companies WHERE companyTypeID = 1 and recordOwnerID IN ($accessto) ORDER BY companyName";
    $result1 = $con->query($sql1);

//$sql2 = "SELECT * FROM premises_view WHERE recordownerid IN ($accessto) ORDER BY buildingname, premisesAddress1";
//    $result2 = $con->query($sql2);
$sql2 = "SELECT * FROM premises WHERE recordOwnerID IN ($accessto) ORDER BY buildingID, unitName";
    $result2 = $con->query($sql2);

$sql3 = "SELECT * FROM leasestatus ORDER BY idleasestatus";
    $result3 = $con->query($sql3);

$sqlg = "SELECT * FROM contacts WHERE recordOwnerID IN ($accessto) ORDER BY firstName";
    $resultg = $con->query($sqlg);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["commencement"])) {
    $commencementErr = "Commencement date is required";
  } else {
    $commencement = test_input($_POST["commencement"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9' ]*$/", $commencement)) {
        $commencementErr = "Only letters, dash and spaces allowed";
    }
  }

  if (empty($_POST["tenantid"])) {
    $tenantidErr = "Tenant is required";
  } else {
    $tenantid = test_input($_POST["tenantid"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9.' ]*$/", $tenantid)) {
        $tenantidErr = "Only numbers and dot allowed";
    }
  }

    if (empty($_POST["premisesid"])) {
        $premisesidErr = "Premises is required";
    } else {
        $premisesid = test_input($_POST["premisesid"]);
        //check if the field only contains numbers
        if (!preg_match("/^[0-9' ]*$/", $premisesid)) {
            $premisesidErr = "Only numbers allowed";
        }
        $sql4 = "SELECT idpremises, recordOwnerID FROM premises WHERE idpremises = $premisesid";
        $resultrecordownler = $con->query($sql4);
        while($rowrecordowner = $resultrecordownler->fetch_assoc()) {
            $recordownerid = $rowrecordowner["recordOwnerID"]; 
        }
    }

  if (empty($_POST["term"])) {
    $termErr = "term is required";
  } else {
    $term = test_input($_POST["term"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9.' ]*$/", $term)) {
        $termErr = "Only numbers and dot allowed";
    }
  }

  if (empty($_POST["rightsofrenewal"])) {
    $rightsofrenewalErr = "Rights of Renewal is required";
  } else {
    $rightsofrenewal = test_input($_POST["rightsofrenewal"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[()a-zA-Z-0-9āēīōūĀĒĪŌŪ.' ]*$/", $rightsofrenewal)) {
        $rightsofrenewalErr = "Only letters, numbers, dash, brackets and dot allowed";
    }
  }

  if (empty($_POST["leaseexpirydate"])) {
    $leaseexpirydateErr = "Expiry date is required";
  } else {
    $leaseexpirydate = test_input($_POST["leaseexpirydate"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[-0-9' ]*$/", $leaseexpirydate)) {
        $leaseexpirydateErr = "numbers and dash allowed";
    }
  }

  if (empty($_POST["annualrentpremises"])) {
    $annualrentpremisesErr = "Annual premises rent is required";
  } else {
    $annualrentpremises = test_input($_POST["annualrentpremises"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9.' ]*$/", $annualrentpremises)) {
        $annualrentpremisesErr = "Only numbers and dot allowed";
    }
  }

  if (empty($_POST["annualrentcarparks"])) {
    $annualrentcarparksErr = "Annual carparks rent is required";
  } else {
    $annualrentcarparks = test_input($_POST["annualrentcarparks"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9.' ]*$/", $annualrentcarparks)) {
        $annualrentcarparksErr = "Only numbers and dot allowed";
    }
  }

  if (empty($_POST["signedon"])) {
    $signedon = NULL;
  } else {
    $signedon = test_input($_POST["signedon"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9' ]*$/", $signedon)) {
          $signedonErr = "Only letters, dash and spaces allowed";
    }
  }

    $signedbyid = test_input($_POST["signedbyid"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $signedbyid)) {
          $signedbyidErr = "Only numbers allowed";
    }

    $guarantorid = test_input($_POST["guarantorid"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $guarantorid)) {
        $guarantoridErr = "Only numbers allowed";
    }

    $bondamount = test_input($_POST["bondamount"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9.' ]*$/", $bondamount)) {
        $bondamountErr = "Only numbers and dot allowed";
    }


  if (empty($_POST["leasestatusid"])) {
    $leasestatusidErr = "Status is required";
} else {
    $leasestatusid = test_input($_POST["leasestatusid"]);
    //check if the field only contains v
    if (!preg_match("/^[0-9.' ]*$/", $leasestatusid)) {
        $leasestatusidErr = "Only numbers allowed";
    }
}
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" and $tenantidErr == NULL and $premisesidErr == NULL and $commencementErr == NULL and $termErr == NULL and $rightsofrenewalErr == NULL and $leaseexpirydateErr == NULL and $annualrentpremisesErr == NULL and $annualrentcarparksErr == NULL and $signedonErr == NULL and $signedbyidErr == NULL and $guarantoridErr == NULL and $bondamountErr == NULL and $leasestatusidErr == NULL) {

    //prepare and bind
    $stmt = $con->prepare("INSERT INTO leases (tenantID, premisesID, commencement, term, rightsOfRenewal, leaseExpirydate, annualRentPremises, annualRentCarparks, signedOn, signedByID, guarantorID, bondAmount, leaseStatusID, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisdssddsiidii", $tenantid, $premisesid, $commencement, $term, $rightsofrenewal, $leaseexpirydate, $annualrentpremises, $annualrentcarparks, $signedon, $signedbyid, $guarantorid, $bondamount, $leasestatusid, $recordownerid);

    if ($stmt->execute()) {
        $last_id = $con->insert_id;
        // Create a new, active Renewal record - Rent at Commenceent
        $enddate = date('Y-m-d', strtotime('+364 days', strtotime($commencement)));
        $renewaltypeid = 1;
        $renewalstatusid = 3;
        $stmt2 = $con->prepare("INSERT INTO renewals (leaseID, startDate, endDate, renewalTypeID, renewalStatusID, rentPremises, rentCarparks, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("issiiddi",  $last_id, $commencement, $enddate, $renewaltypeid, $renewalstatusid, $annualrentpremises, $annualrentcarparks, $recordownerid);

        if ($stmt2->execute()) {
            echo '<table class="table table-hover">
            <tbody>
                <tr class="success">
                    <td>Success!</td>
                </tr>
            </tbody>
            </table>';
    
            echo "<div class=\"row\">
            <div class=\"col-sm-2\"><a href=\"listleases.php\" class=\"btn btn-primary\">Back to Leases</a></div>
            </div>";
        } else {
            echo 'Error creating record: ' . $con->error;
        }
    } else {
        echo 'Error creating record: ' . $con->error;
    }
} else {


?>

<form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <div class="form-group">
        <label class="form-label col-sm-4" for="tenantid" style="padding-top:5px">Tenant: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="tenantid" name="tenantid">
            <?php
                echo "<option value=\"\"> - Select a tenant - </option>";
            while($row = $result1->fetch_assoc()) {
                echo "<option value=\"" . $row["idcompany"] . "\">". $row["companyName"] . "</option>";
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $tenantidErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="premisesid" style="padding-top:5px">Premises: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="premisesid" name="premisesid">
            <?php
                echo "<option value=\"\"> - Select premises - </option>";
            while($row = $result2->fetch_assoc()) {
                //echo "<option value=\"" . $row["idpremises"] . "\">". $row["unitname"] . ", " . $row["premisesaddress1"] . " (" . $row["buildingname"] . ")</option>";
                echo "<option value=\"" . $row["idpremises"] . "\">". $row["unitName"] . ", " . $row["premisesAddress1"] . "</option>";
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $premisesidErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="commencement" style="padding-top:5px">Commencement Date:<br>(yyyy-mm-dd) <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="commencement" type="date" name="commencement" value="<?php echo $commencement;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $commencementErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="term" style="padding-top:5px">Term (years): <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="term" type="text" name="term" value="<?php echo $term;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $termErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="rightsofrenewal">Rights of Renewal: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="rightsofrenewal" type="text" name="rightsofrenewal" value="<?php echo $rightsofrenewal;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $rightsofrenewalErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="leaseexpirydate" style="padding-top:5px">Expiry Date: (yyyy-mm-dd) <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="leaseexpirydate" type="date" name="leaseexpirydate" value="<?php echo $leaseexpirydate;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $leaseexpirydateErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="annualrentpremises" style="padding-top:5px">Annual Rent - Premises: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="annualrentpremises" type="text" name="annualrentpremises" value="<?php echo $annualrentpremises;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $annualrentpremisesErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="annualrentcarparks" style="padding-top:5px">Annual Rent - Carparks: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="annualrentcarparks"  type="text" name="annualrentcarparks" value="<?php echo $annualrentcarparks;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $annualrentcarparksErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="signedon" style="padding-top:5px">Signed On: (yyyy-mm-dd)</label>
        <div class="col-sm-6"><input class="form-control" id="signedon" type="text" name="signedon" value="<?php echo $signedon;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $signedonErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="signedbyid" style="padding-top:5px">Signed By:</label>
        <div class="col-sm-6">
            <select class="form-control" id="signedbyid" type="text" name="signedbyid">
                <?php
                echo "<option value=\"0\"> - Select a signatory - </option>";
                while ($rowg = $resultg->fetch_assoc()) {
                    if($rowg["idcontacts"] === $signedbyid) {
                        echo "<option value=" . $rowg["idcontacts"] . " selected>" . $rowg["firstName"] . " " . $rowg["middleName"] . " " . $rowg["lastName"] . "</option>";
                    } else {
                        echo "<option value=" . $rowg["idcontacts"] . ">" . $rowg["firstName"] . " " . $rowg["middleName"] . " " . $rowg["lastName"] . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $signedbyidErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="guarantorid" style="padding-top:5px">Guarantor:</label>
        <div class="col-sm-6">
            <select class="form-control" id="guarantorid" type="text" name="guarantorid">
                <?php
                echo "<option value=\"0\"> - Select a Guarantor if lease has been guaranteed - </option>";
                while ($rowg = $resultg->fetch_assoc()) {
                    if($rowg["idcontacts"] === $guarantorid) {
                        echo "<option value=" . $rowg["idcontacts"] . " selected>" . $rowg["firstName"] . " " . $rowg["middleName"] . " " . $rowg["lastName"] . "</option>";
                    } else {
                        echo "<option value=" . $rowg["idcontacts"] . ">" . $rowg["firstName"] . " " . $rowg["middleName"] . " " . $rowg["lastName"] . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $guarantoridErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="bondamount" style="padding-top:5px">Bond: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="bondamount"  type="text" name="bondamount" value="<?php echo $bondamount;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $bondamountErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="leasestatusid" style="padding-top:5px">Status: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="leasestatusid" name="leasestatusid">
            <?php
            while($row3 = $result3->fetch_assoc()) {
                    echo "<option value=\"" . $row3["idleasestatus"] . "\">". $row3["leaseStatus"] . "</option>";
                }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $leasestatusidErr;?></span></div>
    </div>

    <div class="form-group">
        <div class="col-sm-1" style="padding-top:40px;"><input type="submit" value="Submit" class="btn btn-primary" style="width:100px"></div>
    </div>
</form>

<div class="row">
<?php
}

$con->close();
?>

<?=template_footer()?>