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

?>

<?=template_header('Add more Lease Terms')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Add Lease Clause</h2>
	</div>
</div>

<div class="block">     

<?php
    // define variables and set to empty values
$leasetermid = $clausenumber = "";
$leasetermidErr = $clausenumberErr = "";
$currentclauses = 0;

$Q = explode("/", $_SERVER['QUERY_STRING']);
    parse_str($Q[0], $QueryParameters);
    if (empty($QueryParameters['leaseid'])) {
        $QPleaseid = "";
        $leaseid = "";
    } else {
        $QPleaseid = $QueryParameters['leaseid'];
        $leaseid = $QueryParameters['leaseid'];
    }


$sql = "SELECT * FROM leaseterms_view WHERE leaseid = $QPleaseid and recordOwnerID IN ($accessto) ORDER BY leaseTermName";
    $result = $con->query($sql);

    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            $currentclauses .= "," . $row["leasetermsid"]; 
        }
    }

//Get the list of terms for the dropdown list
$sql3 = "SELECT * FROM leaseterms WHERE idleaseterms NOT IN ($currentclauses) and recordOwnerID IN ($accessto) ORDER BY LeaseTermName";
    $result3 = $con->query($sql3);

$sql4 = "SELECT premisesaddress1, tenantname FROM leases_view WHERE idlease = $QPleaseid";
    $result4 = $con->query($sql4);
    while($row4 = $result4->fetch_assoc()) {
        $premisesaddress1 = $row4["premisesaddress1"];
        $tenantname = $row4["tenantname"];
    }

//Get the list of existing terms to exclude them from the selection list

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["leaseid"])) {
        $leaseidErr = "Lease ID is required";
    } else {
        $leaseid = test_input($_POST["leaseid"]);
        //check if the field only contains numbers
        if (!preg_match("/^[-0-9' ]*$/", $leaseid)) {
            $leaseidErr = "Only numbers allowed";
        }
    }

    if (empty($_POST["leasetermid"])) {
        $leasetermidErr = "Lease Term is required";
    } else {
        $leasetermid = test_input($_POST["leasetermid"]);
        //check if the field only contains numbers
        if (!preg_match("/^[-0-9' ]*$/", $leasetermid)) {
            $leasetermidErr = "Only numbers allowed";
        }
    }

    $clausenumber = test_input($_POST["clausenumber"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/&|^[a-zA-Z-0-9āēīōūĀĒĪŌŪ()'. ]*$/", $clausenumber)) {
        $clausenumberErr = "Disallowed characters in Clause Number";
    }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}



if ($_SERVER["REQUEST_METHOD"] == "POST" and $leasetermidErr == NULL and $clausenumberErr == NULL) {

    //prepare and bind
    $stmt = $con->prepare("INSERT INTO leasetermsmapping (leaseID, leaseTermID, clauseNumber) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $leaseid, $leasetermid, $clausenumber);

    if ($stmt->execute()) {
        echo '<div class=\"row\">
        <table class="table table-hover">
            <tbody>
                <tr class="success">
                    <td>Success!</td>
                </tr>
            </tbody>
        </table>';
     
         echo "<div class=\"row\">
            <div class=\"col-sm-2\"><a href=\"viewleaseterms.php?leaseid=" . $leaseid . "\" class=\"btn btn-primary\">Back to Lease Clauses</a></div>
            <div class=\"col-sm-2\"><a href=\"addmoreleaseterms.php?leaseid=" . $QPleaseid . "\" class=\"btn btn-primary\">Add Clause</a></div>
        </div>";
    } else {
        echo 'Error creating record: ' . $con->error;
    }
    

} else {

    ?>
    <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?leaseid=' . $leaseid);?>">
    <div  class="form-group">
        <div class="col-sm-12 h4" style="padding-bottom:30px;"><?php echo $tenantname;?><br><p><?php echo $premisesaddress1;?></p></div>
        <div></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="leasetermid" style="padding-top:5px">Lease Clause: <span class="text-danger">*</span></label>
        <div class="col-sm-6">
            <select class="form-control" id="leasetermid" name="leasetermid">
            <?php
                echo "<option value=\"\"> - Select a Lease Clause - </option>";
            while($row3 = $result3->fetch_assoc()) {
                echo "<option value=\"" . $row3["idleaseterms"] . "\">" . $row3["leaseTermName"] . "</option>";
            }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $leasetermidErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="clausenumber">Clause Number: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="clausenumber" type="text" name="clausenumber" value="<?php echo $clausenumber;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $clausenumberErr;?></span></div>
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