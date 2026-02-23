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

<?=template_header('Add Contact')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Add Contact</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block"> 

<?php
    // define variables and set to empty values
$firstname = $middlename = $lastname = $companyid = $emailaddress = $mobilenumber = $phonenumber = $companyid = $title = "";
$firstnameErr = $middlenameErr = $lastnameErr = $companyidErr = $emailaddressErr = $mobilenumberErr = $phonenumberErr = $companyidErr = $titleErr = "";

$sql1 = "SELECT * FROM companies WHERE recordOwnerID IN ($accessto) ORDER BY companyName";
    $result1 = $con->query($sql1);


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST["firstname"])) {
        $firstnameErr = "First Name is required";
    } else {
        $firstname = test_input($_POST["firstname"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $firstname)) {
            $firstnameErr = "Only letters, dash and spaces allowed";
        }
    }

    $middlename = test_input($_POST["middlename"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $middlename)) {
        $middlenameErr = "Only letters, dash and spaces allowed";
    }
    
    if (empty($_POST["lastname"])) {
        $lastnameErr = "last Name is required";
    } else {
        $lastname = test_input($_POST["lastname"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $lastname)) {
            $lastnameErr = "Only letters, dash and spaces allowed";
        }
    }
    
    if (empty($_POST["emailaddress"])) {
        //empty email is allowed
    } else {
        $emailaddress = test_input($_POST["emailaddress"]);
        //check if the field only contains letters dash or white space
        if (!filter_var($emailaddress, FILTER_VALIDATE_EMAIL)) {
            $emailaddressErr = "Invalid email format";
        }
    }
    
    $mobilenumber = test_input($_POST["mobilenumber"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $mobilenumber)) {
        $mobilenumberErr = "Only numbers allowed";
    }
      
    $phonenumber = test_input($_POST["phonenumber"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[0-9' ]*$/", $phonenumber)) {
        $phonenumberErr = "Only numbers allowed";
    }

    if (empty($_POST["companyid"])) {
        $companyidErr = "Company is required";
    } else {
        $companyid = test_input($_POST["companyid"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9' ]*$/", $companyid)) {
            $companyidErr = "Only numbers allowed";
        }
        $sql3 = "SELECT idcompany, recordOwnerID FROM companies WHERE idcompany = $companyid";
        $resultrecordownler = $con->query($sql3);
        while($rowrecordowner = $resultrecordownler->fetch_assoc()) {
            $recordownerid = $rowrecordowner["recordOwnerID"]; 
        }
    }

    $title = test_input($_POST["title"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $title)) {
        $titleErr = "Only letters, dash and spaces allowed";
    }

    if (isset($_POST["isprimarycontact"])) {
        $isprimarycontact = 1;
    } else {
        $isprimarycontact = 0;
    }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}



if ($_SERVER["REQUEST_METHOD"] == "POST" and $firstnameErr == NULL and $middlenameErr == NULL and $lastnameErr == NULL and $emailaddressErr == NULL and $mobilenumberErr == NULL and $phonenumberErr == NULL and $companyidErr == NULL and $titleErr == NULL) {

    //prepare and bind
    $stmt = $con->prepare("INSERT INTO contacts (firstName, middlename, lastName, emailAddress, mobileNumber, phoneNumber, companyID, title, recordOwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssisi", $firstname, $middlename, $lastname, $emailaddress, $mobilenumber, $phonenumber, $companyid, $title, $recordownerid);

    if ($stmt->execute()) {
        if ($isprimarycontact == 1) {
            //Update the company record with the primary contact
            $last_id = $con->insert_id;
            $stmt1 = $con->prepare('UPDATE companies SET primaryContactID = ? WHERE idcompany = ?');
            $stmt1->bind_param('si', $last_id, $companyid);
            $stmt1->execute();
            $stmt1->close();
        }
        
        echo '<div class=\"row\">
        <table class="table table-hover">
            <tbody>
                <tr class="success">
                    <td>Success!</td>
                </tr>
            </tbody>
        </table>';
     
         echo "<div class=\"row\">
            <div class=\"col-sm-2\"><a href=\"listcontacts.php\" class=\"btn btn-primary\">Back to Contacts</a></div>
        </div>";
    } else {
        echo 'Error creating record: ' . $con->error;
    }
    

} else {

    ?>
    <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <div  class="form-group">
        <label class="form-label col-sm-4" for="firstname" style="padding-top:5px">First Name: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="firstname" type="text" name="firstname" value="<?php echo $firstname;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $firstnameErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="middlename" style="padding-top:5px">Middle Name:</label>
        <div class="col-sm-6"><input class="form-control" id="middlename" type="text" name="middlename" value="<?php echo $middlename;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $middlenameErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="lastname" style="padding-top:5px">Last Name: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="lastname" type="text" name="lastname" value="<?php echo $lastname;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $lastnameErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="emailaddress" style="padding-top:5px">Email: </label>
        <div class="col-sm-6"><input class="form-control" id="emailaddress" type="text" name="emailaddress" value="<?php echo $emailaddress;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $emailaddressErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="mobilenumber" style="padding-top:5px">Mobile:</label>
        <div class="col-sm-6"><input class="form-control" id="mobilenumber" type="text" name="mobilenumber" value="<?php echo $mobilenumber;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $mobilenumberErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="phonenumber" style="padding-top:5px">Phone:</label>
        <div class="col-sm-6"><input class="form-control" id="phonenumber" type="text" name="phonenumber" value="<?php echo $phonenumber;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $phonenumberErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="title" style="padding-top:5px">Title:</label>
        <div class="col-sm-6"><input class="form-control" id="title" type="text" name="title" value="<?php echo $title;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $titleErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="companyid" style="padding-top:5px">Company: <span class="text-danger">*</label>
        <div class="col-sm-6">
            <select class="form-control" id="companyid" name="companyid">
            <?php
                echo "<option value=\"0\"> - Select a Company - </option>";
            while($row = $result1->fetch_assoc()) {
                echo "<option value=\"" . $row["idcompany"] . "\">". $row["companyName"] . "</option>";
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $companyidErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-3" for="isprimarycontact" style="padding-top:5px">Is this the primary contact for this company?</label>
        <div class="col-sm-1"><span class="error">&nbsp;</div>
        <div class="col-sm-8">
            <label class="switch">
                <input type="checkbox" id="isprimarycontact" name="isprimarycontact" style="width:20px; height:20px;" checked>
            </label>
        </div>
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