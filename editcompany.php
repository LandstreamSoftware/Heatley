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

<?=template_header('Edit Company')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Edit Company</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">

<?php
// define variables and set to empty values

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPcompanyid = $QueryParameters['companyid'];

$sql = "SELECT * from companies WHERE idcompany = $QPcompanyid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);

$sql1 = "SELECT * from companytype ORDER BY companyType";
$result1 = $con->query($sql1);

$sql3 = "SELECT * from contacts WHERE companyID = $QPcompanyid and recordOwnerID IN ($accessto)";
$result3 = $con->query($sql3);
//Get the list of lawyers
$sql4 = "SELECT * from companies WHERE companyTypeID = 3 and recordOwnerID IN ($accessto)";
$result4 = $con->query($sql4);


if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $thiscompanyid = $row["idcompany"];
        $companyname = $row["companyName"];
        $address1 = $row["address1"];
        $address2 = $row["address2"];
        $suburb = $row["addressSuburb"];
        $city = $row["addressCity"];
        $postcode = $row["addressPostCode"];
        $companytypeid = $row["companyTypeID"];
        $primarycontactid = $row["primaryContactID"];
        $logoimagefilename = $row["logoImageFileName"];
        $nzbn = $row["NZBN"];
        $gstnumber = $row["gstNumber"];
        $bankaccountnumber = $row["bankAccountNumber"];
    } 
} else {
    $companyname = $address1 = $address2 = $suburb = $city = $postcode = $companytypeid = $primarycontactid = $logoimagefilename = $nzbn = $gstnumber = $bankaccountnumber = "";
}
    $companynameErr = $address1Err = $address2Err = $suburbErr = $cityErr = $postcodeErr = $companytypeidErr = $primarycontactidErr = $logoimagefilenameErr = $nzbnErr = $gstnumberErr = $bankaccountnumberErr = "";

//Get the list of property managers, including this company (for self management)
$sql5 = "SELECT * from companies WHERE companyTypeID = 2 and recordOwnerID IN ($accessto)";
$result5 = $con->query($sql5);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (empty($_POST["companyname"])) {
    $companynameErr = "Company Name is required";
  } else {
    $companyname = test_input($_POST["companyname"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/&|^[a-zA-Z-0-9āēīōūĀĒĪŌŪ()' \/]*$/", $companyname)) {
        $companynameErr = "Disallowed characters used in company name";
    }
  }

    $address1 = test_input($_POST["address1"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/&|^[a-zA-Z-0-9āēīōūĀĒĪŌŪ()' \/]*$/", $address1)) {
        $address1Err = "Only letters, dash and spaces allowed";
    }
  

    $address2 = test_input($_POST["address2"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' \/]*$/", $address2)) {
        $address2Err = "Only letters, dash and spaces allowed";
    }

    $suburb = test_input($_POST["suburb"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $suburb)) {
        $suburbErr = "Only letters, dash and spaces allowed";
    }
  

  if (empty($_POST["city"])) {
    $cityErr = "Town/City is required";
  } else {
    $city = test_input($_POST["city"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $city)) {
        $cityErr = "Only letters, dash and spaces allowed";
    }
  }
  
  if (empty($_POST["postcode"])) {
    $postcodeErr = "Post Code is required";
  } else {
    $postcode = test_input($_POST["postcode"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $postcode)) {
        $postcodeErr = "Only numbers allowed";
    }
  }

  if (empty($_POST["companytypeid"])) {
    $companytypeidErr = "Type is required";
  } else {
    $companytypeid = test_input($_POST["companytypeid"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $companytypeid)) {
        $companytypeidErr = "Only numbers allowed";
    }
  }

//  if (empty($_POST["primarycontactid"])) {
//    $primarycontactidErr = "Primary Contact is required";
//  } else {
    $primarycontactid = test_input($_POST["primarycontactid"]);
    //check if the field only contains numbers
    if (!preg_match("/^[0-9' ]*$/", $primarycontactid)) {
        $primarycontactidErr = "Only numbers allowed";
    }
//  }


    $logoimagefilename = test_input($_POST["logoimagefilename"]);
    //check if the field only contains numbers
    if (!preg_match("/^[a-zA-Z-0-9 _.-]*$/", $logoimagefilename)) {
        $logoimagefilenameErr = "Only numbers, dash, underscore and dot allowed";
    }

    $gstnumber = test_input($_POST["gstnumber"]);
    //check if the field only contains numbers
    if (!preg_match("/^[0-9'\- ]*$/", $gstnumber)) {
        $gstnumberErr = "Only numbers and dash allowed";
    }

    $nzbn = test_input($_POST["nzbn"]);
    //check if the field only contains numbers
    if (!preg_match("/^[0-9' ]*$/", $nzbn)) {
        $nzbnErr = "Only numbers allowed";
    }

    $bankaccountnumber = test_input($_POST["bankaccountnumber"]);
    //check if the field only contains numbers
    if (!preg_match("/^[0-9'\-]*$/", $bankaccountnumber)) {
        $bankaccountnumberErr = "Only numbers and dash allowed";
    }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" and $companynameErr == NULL and $address1Err == NULL and $suburbErr == NULL and $cityErr == NULL and $postcodeErr == NULL and $companytypeidErr == NULL and $primarycontactidErr == NULL and $logoimagefilenameErr == NULL and $nzbnErr == NULL and $gstnumberErr == NULL and $bankaccountnumberErr == NULL) {

    //prepare and bind
$sql2 = "UPDATE companies SET companyName = '$companyname', address1 = '$address1', address2 = '$address2', addressSuburb = '$suburb', addressCity = '$city', addressPostCode = '$postcode', companyTypeID = '$companytypeid', primaryContactID = '$primarycontactid', logoImageFileName = '$logoimagefilename', NZBN = '$nzbn', gstNumber = '$gstnumber', bankAccountNumber = '$bankaccountnumber' WHERE idcompany=$QPcompanyid";

    if ($con->query($sql2) === TRUE) {
        echo '<table class="table table-hover">
        <tbody>
            <tr class="success">
                <td>Success!</td>
            </tr>
        </tbody>
        </table>';

        echo "<div class=\"row\">
           <div class=\"col-sm-2\"><a href=\"listcompanies.php\" class=\"btn btn-primary\">Back to Companies</a></div>
        </div>
        <div class=\"row\">";
    } else {
        echo 'Error updating record: ' . $con->error;
    }
} else {
    ?>




    <form id="EditCompanyForm" class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?companyid='.$QPcompanyid);?>">
    <div class="form-group">
        <label class="form-label col-sm-4" for="companyname">Company Name: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="companyname" type="text" name="companyname" value="<?php echo $companyname;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $companynameErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="address1">Address 1:</label>
        <div class="col-sm-6"><input class="form-control" id="address1" type="text" name="address1" value="<?php echo $address1;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $address1Err;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="address2">Address 2: </label>
        <div class="col-sm-6"><input class="form-control" id="address2" type="text" name="address2" value="<?php echo $address2;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $address2Err;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="suburb">Suburb:</label>
        <div class="col-sm-6"><input class="form-control" id="suburb" type="text" name="suburb" value="<?php echo $suburb;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $suburbErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="city">City: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="city" type="text" name="city" value="<?php echo $city;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $cityErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="postcode">Post Code: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="postcode" type="text" name="postcode" value="<?php echo $postcode;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $postcodeErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="companytypeid">Company Type: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="companytypeid" name="companytypeid">
            <?php
            if($companytypeid == 2) {
                echo "<option value=\"2\">My Company</option>";
            } else {
                echo "<option value=\"\"> - Select a type - </option>";
                while($row = $result1->fetch_assoc()) {
                    if($row["idcompanytype"] === $companytypeid){
                        echo "<option value=\"" . $row["idcompanytype"] . "\" selected>". $row["companyType"] . "</option>";
                    } else {
                        echo "<option value=\"" . $row["idcompanytype"] . "\">". $row["companyType"] . "</option>";
                    }
                    
                }
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $companytypeidErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="primarycontactid">Primary Contact: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="primarycontactid" name="primarycontactid">
            <?php
                echo "<option value=\"0\"> - Select a contact - </option>";
            while($row3 = $result3->fetch_assoc()) {
                if($row3["idcontacts"] === $primarycontactid){
                    echo "<option value=\"" . $row3["idcontacts"] . "\" selected>". $row3["firstName"] . " " . $row3["lastName"] . "</option>";
                } else {
                    echo "<option value=\"" . $row3["idcontacts"] . "\">". $row3["firstName"] . " " . $row3["lastName"] . "</option>";
                }
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $primarycontactidErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="logoimagefilename">Logo Image File Name:</span></label>
        <div class="col-sm-6"><input class="form-control" id="logoimagefilename" type="text" name="logoimagefilename" value="<?php echo $logoimagefilename;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $logoimagefilenameErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="gstnumber">GST Number:</label>
        <div class="col-sm-6"><input class="form-control" id="gstnumber" type="text" name="gstnumber" value="<?php echo $gstnumber;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $gstnumberErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="nzbn">NZBN:</label>
        <div class="col-sm-6"><input class="form-control" id="nzbn" type="text" name="nzbn" value="<?php echo $nzbn;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $nzbnErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="bankaccountnumber">Bank Account Number:</label>
        <div class="col-sm-6"><input class="form-control" id="bankaccountnumber" type="text" name="bankaccountnumber" value="<?php echo $bankaccountnumber;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $bankaccountnumberErr;?></span></div>
    </div>
    
    <div class="form-group">
        <div class="col-sm-1" style="padding-top:40px;"><input type="submit" value="Submit" class="btn btn-primary" style="width:100px"></div>
    </div>
    </form>

<script>
//If the Company Type = Tenant, then there must be a Primary Contact
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("EditCompanyForm").onsubmit = function(event) {
        const companyTypeId = document.getElementById('companytypeid').value;
        const primaryContactId = document.getElementById('primarycontactid').value;
        console.log("Company Type ID:", companyTypeId);
        console.log("Primary Contact ID:", primaryContactId);
        if (companyTypeId === "1" && primaryContactId === "0")
            { alert("A Primary Contact is required for Tenant companies.\n\nIt is needed for certain functionality like generating a Right of Renewal document.");
            event.preventDefault(); // Prevent form submission
            return false;
        }
        return true;
    };
});
</script>

<div class="row">
<?php
}

$con->close();
?>

</div>

<?=template_footer()?>