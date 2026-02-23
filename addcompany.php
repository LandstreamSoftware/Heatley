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

while($rowuser = $resultuser->fetch_assoc()) {
    $mycompanyid = $rowuser["companyID"]; 
}
?>

<?=template_header('Add Company')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Add Company</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block"> 
        
<?php
    // define variables and set to empty values
$companyname = $address1 = $address2 = $suburb = $city = $postcode = $companytypeid = $primarycontactid = $lawyercompanyid = $recordownerid = "";
$companynameErr = $address1Err = $address2Err = $suburbErr = $cityErr = $postcodeErr = $companytypeidErr = $primarycontactidErr = $lawyercompanyidErr = $recordowneridErr = "";

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
if(empty($QueryParameters['companyid'])){
    $QPcompanyid = "";
}else{
    $QPcompanyid = $QueryParameters['companyid'];
    $companyid = $QueryParameters['companyid'];
}

if (isset($_SESSION['account_role']) && $_SESSION['account_role'] == 'Admin') {
    $sql1 = "SELECT * FROM companytype ORDER BY idcompanytype";
} else {
    $sql1 = "SELECT * FROM companytype Where idcompanytype <> 2  ORDER BY idcompanytype";
}

    $result1 = $con->query($sql1);

$sql3 = "SELECT * FROM contacts_view WHERE recordOwnerID IN ($accessto) ORDER BY firstName";
    $result3 = $con->query($sql3);

$sql4 = "SELECT idcompany, companyName from companies WHERE companyTypeID = 3 and recordOwnerID IN ($accessto)"; //Lawyers
    $result4 = $con->query($sql4);

$sql5 = "SELECT idcompany, companyName from companies WHERE idcompany IN ($accessto)";
    $result5 = $con->query($sql5);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["companyname"])) {
        $companynameErr = "Company Name is required";
    } else {
        $companyname = test_input($_POST["companyname"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/&|^[a-zA-Z-0-9āēīōūĀĒĪŌŪ()' ]*$/", $companyname)) {
            $companynameErr = "Only letters, dash and spaces allowed";
        }
    }

    if (empty($_POST["address1"])) {
        $address1Err = "Address is required";
    } else {
        $address1 = test_input($_POST["address1"]);
        //any characters are allowed in the address field
    }

    $address2 = test_input($_POST["address2"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $address2)) {
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
        $companytypeidErr = "Company Type is required";
    } else {
        $companytypeid = test_input($_POST["companytypeid"]);
        //check if the field only contains numbers
        if (!preg_match("/^[0-9' ]*$/", $companytypeid)) {
            $companytypeidErr = "Only numbers allowed";
        }
    }

//    $primarycontactid = test_input($_POST["primarycontactid"]);
    //check if the field only contains numbers
//    if (!preg_match("/^[0-9' ]*$/", $primarycontactid)) {
//        $primarycontactidErr = "Only numbers allowed";
//    }
    

    if (empty($_POST["recordownerid"])) {
        $recordownerid = $mycompanyid;
    } else {
        $recordownerid = test_input($_POST["recordownerid"]);
        //check if the field only contains numbers
        if (!preg_match("/^[0-9' ]*$/", $recordownerid)) {
            $recordowneridErr = "Only numbers allowed";
        }
    }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}



if ($_SERVER["REQUEST_METHOD"] == "POST" and $companynameErr == NULL and $address1Err == NULL and $address2Err == NULL and $suburbErr == NULL and $cityErr == NULL and $postcodeErr == NULL and $companytypeidErr == NULL and $lawyercompanyidErr == NULL) {

    //prepare and bind
    $stmt = $con->prepare("INSERT INTO companies (companyName, address1, address2, addressSuburb, addressCity, addressPostCode, companyTypeID, lawyercompanyid, propertyManagerCompanyID, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssiiii", $companyname, $address1, $address2, $suburb, $city, $postcode, $companytypeid, $lawyercompanyid, $mycompanyid, $recordownerid);

    if ($stmt->execute()) {
        if ($companytypeid == "5") {
            //If the company is type Property Owner, set the recordOwnerID to the idcompany.
            $newcompanyid = $con->insert_id;
            $sql5 = "UPDATE companies SET companyName = '$companyname', recordOwnerID = '$newcompanyid' WHERE idcompany=$newcompanyid";
            if ($con->query($sql5) === TRUE) {
                //Add the new Property Owner company to the Admin access list.
                $adminaccountid = 1;
                $stmt1 = $con->prepare("INSERT INTO accesscontrol (accountID, companyID) VALUES (?, ?)");
                $stmt1->bind_param("ii", $adminaccountid, $newcompanyid);
                if ($stmt1->execute()) {
                    //Add the new Property Owner company to the current user's access list.
                    $stmt2 = $con->prepare("INSERT INTO accesscontrol (accountID, companyID) VALUES (?, ?)");
                    $stmt2->bind_param("ii", $accountid, $newcompanyid);
                    if ($stmt2->execute()) {
                        echo "<div class=\"row\">
                        <table class=\"table table-hover\">
                            <tbody>
                            <tr class=\"success\">
                                <td>Owner company created.</td>
                            </tr>
                            </tbody>
                        </table>";
                    } else {
                        echo 'Error creating access record for the your account: ' . $con->error;
                    }
                } else {
                    echo 'Error creating access record for the admin account: ' . $con->error;
                }
            } else {
                echo 'Error updating record: ' . $con->error;
            }

        } else {

            echo "<div class=\"row\">
            <table class=\"table table-hover\">
                <tbody>
                <tr class=\"success\">
                    <td>Success!</td>
                </tr>
                </tbody>
            </table>";
        }
     
         echo "<div class=\"row\">
            <div class=\"col-sm-2\"><a href=\"listcompanies.php\" class=\"btn btn-primary\">Back to Companies</a></div>
        </div>";
    } else {
        echo "Error creating record: " . $con->error;
    }
    
} else {

    ?>

<script type="text/javascript">
//Hide various fields depending on the user selection
function MyFunction() {
    var x = document.getElementById("companytypeid").selectedIndex;

    const collection1 = document.getElementsByClassName("div1");
    const collection2 = document.getElementsByClassName("div2");

    for (let i = 0; i < collection1.length; i++) {
        collection1[i].style.visibility = "collapse";
    }
    for (let j = 0; j < collection2.length; j++) {
        collection2[j].style.visibility = "collapse";
    }

    switch (x) {
        case 0: //No selection
            for (let i = 0; i < collection1.length; i++) {
                collection1[i].style.visibility = "collapse";
            }
            for (let j = 0; j < collection2.length; j++) {
                collection2[j].style.visibility = "collapse";
            }
        break;

        case 1:
        case 2:
        case 3:
        case 4:
        case 6:
            for (let i = 0; i < collection1.length; i++) {
                collection1[i].style.visibility = "visible";
            }
            for (let j = 0; j < collection2.length; j++) {
                collection2[j].style.visibility = "visible";
            }
        break;

        case 5: //Property Owner
            for (let i = 0; i < collection1.length; i++) {
                collection1[i].style.visibility = "collapse";
            }
            for (let j = 0; j < collection2.length; j++) {
                collection2[j].style.visibility = "visible";
            }
        break;

        default:
            for (let i = 0; i < collection1.length; i++) {
                collection1[i].style.visibility = "collapse";
            }
            for (let j = 0; j < collection2.length; j++) {
                collection2[j].style.visibility = "collapse";
            }
    }
}


</script>


    <form id="NewCompanyForm" class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?companyid='.$QPcompanyid);?>">
    <div class="form-group" id="div1">
        <label class="form-label col-sm-4" for="companytypeid" style="padding-top:5px">Company Type: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="companytypeid" name="companytypeid" onchange="MyFunction();">
            <?php
                echo "<option value=\"0\"> - Select a type - </option>";
            while($row = $result1->fetch_assoc()) {
                if($row["idcompanytype"] == "$companytypeid") {
                    echo "<option value=\"" . $row["idcompanytype"] . "\" selected>". $row["companyType"] . "</option>";
                }
                echo "<option value=\"" . $row["idcompanytype"] . "\">". $row["companyType"] . "</option>";
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $companytypeidErr;?></span></span></div>
    </div>
    <div  class="form-group div2">
        <label class="form-label col-sm-4" for="companyname" style="padding-top:5px">Company Name: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="companyname" type="text" name="companyname" value="<?php echo $companyname;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $companynameErr;?></span></span></div>
    </div>
    <div  class="form-group div2">
        <label class="form-label col-sm-4" for="address1" style="padding-top:5px">Address 1: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="address1" type="text" name="address1" value="<?php echo $address1;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $address1Err;?></span></span></div>
    </div>
    <div  class="form-group div2">
        <label class="form-label col-sm-4" for="address2" style="padding-top:5px">Address 2: </label>
        <div class="col-sm-6"><input class="form-control" id="address2" type="text" name="address2" value="<?php echo $address2;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $address2Err;?></span></span></div>
    </div>
    <div  class="form-group div2">
        <label class="form-label col-sm-4" for="suburb" style="padding-top:5px">Suburb:</label>
        <div class="col-sm-6"><input class="form-control" id="suburb" type="text" name="suburb" value="<?php echo $suburb;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $suburbErr;?></span></span></div>
    </div>
    <div  class="form-group div2">
        <label class="form-label col-sm-4" for="city" style="padding-top:5px">City: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="city" type="text" name="city" value="<?php echo $city;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $cityErr;?></span></span></div>
    </div>
    <div  class="form-group div2">
        <label class="form-label col-sm-4" for="postcode" style="padding-top:5px">Post Code: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="postcode" type="text" name="postcode" value="<?php echo $postcode;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $postcodeErr;?></span></span></div>
    </div>
    <!--
    <div class="form-group div2">
        <label class="form-label col-sm-4" for="primarycontactid" style="padding-top:5px">Primary Contact:</label>
        <div class="col-sm-6">
            <select class="form-control" id="primarycontactid" name="primarycontactid">
            <?php
                echo "<option value=\"0\"> - Select a contact - </option>";
            while($row3 = $result3->fetch_assoc()) {
                if($row3["idcontacts"] === $primarycontactid){
                    echo "<option value=\"" . $row3["idcontacts"] . "\" selected>". $row3["firstname"] . " " . $row3["lastname"] . " - " . $row3["companyname"] . "</option>";
                } else {
                    echo "<option value=\"" . $row3["idcontacts"] . "\">". $row3["firstname"] . " " . $row3["lastname"] . " - " . $row3["companyname"] . "</option>";
                }
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $primarycontactidErr;?></span></span></div>
    </div>
    -->

<!--
    <div class="form-group div2">
        <label class="form-label col-sm-4" for="lawyercompanyid" style="padding-top:5px">Lawyer:</label>
        <div class="col-sm-6">
            <select class="form-control" id="lawyercompanyid" name="lawyercompanyid">
            <?php
                echo "<option value=\"0\"> - Select this company's lawyer - </option>";
            while($row4 = $result4->fetch_assoc()) {
                if($row4["idcompany"] === $lawyercompanyid){
                    echo "<option value=\"" . $row4["idcompany"] . "\" selected>". $row4["companyName"] . "</option>";
                } else {
                    echo "<option value=\"" . $row4["idcompany"] . "\">". $row4["companyName"] . "</option>";
                }
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $lawyercompanyidErr;?></span></span></div>
    </div>
-->

    <div class="form-group div1">
        <label class="form-label col-sm-4" for="recordownerid" style="padding-top:5px">Owning Account:</label>
        <div class="col-sm-6">
            <select class="form-control" id="recordownerid" name="recordownerid">
            <?php
            while($row5 = $result5->fetch_assoc()) {
                if($row5["idcompany"] === $mycompanyid){
                    echo "<option value=\"" . $row5["idcompany"] . "\" selected>". $row5["companyName"] . "</option>";
                } else {
                    echo "<option value=\"" . $row5["idcompany"] . "\">". $row5["companyName"] . "</option>";
                }
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $recordowneridErr;?></span></span></div>
    </div>

    <div class="row">
        <div class="col-sm-1" style="padding-top:40px;"><input type="submit" value="Submit" class="btn btn-primary" style="width:100px"></div>
    </div>
    </form>

<div class="row">
<?php
}

$con->close();
?>

</div>

<?=template_footer()?>