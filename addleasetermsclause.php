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

<?=template_header('Add Additional Terms Clause')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Add Additional Terms Clause</h2>
	</div>
</div>

<div class="block"> 

<?php
    // define variables and set to empty values
    $leasetermname = $leasetermtext = $leasetermgrouping = $recordownerid = "";
    $leasetermnameErr = $leasetermtextErr = $leasetermgroupingErr = $recordowneridErr = "";

    //Get the list of existing headings (grouping)
    $sql2 = "SELECT leaseTermGrouping FROM leaseterms WHERE recordOwnerID IN ($accessto) GROUP BY leaseTermGrouping ORDER BY leaseTermGrouping";
    $result2 = $con->query($sql2);

    $sql5 = "SELECT idcompany, companyName from companies WHERE idcompany IN ($accessto)";
    $result5 = $con->query($sql5);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["leasetermgrouping"])) {
        //$leasetermgroupingErr = "Grouping is required";
        //Empy value is allowed
    } else {
        $leasetermgrouping = test_input($_POST["leasetermgrouping"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[\/a-zA-Z-0-9' ,.]*$/", $leasetermgrouping)) {
            $leasetermgroupingErr = "Only letters, dash and spaces allowed";
        }
    }

    if (empty($_POST["leasetermname"])) {
        $leasetermnameErr = "Clause Name is required";
    } else {
        $leasetermname = test_input($_POST["leasetermname"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[\/a-zA-Z-0-9' ,.()]*$/", $leasetermname)) {
            $leasetermnameErr = "Only letters, dash and spaces allowed";
        }
    }

    if (empty($_POST["leasetermtext"])) {
        $leasetermtextErr = "Clause content is required";
    } else {
        $leasetermtext = $_POST["leasetermtext"];
    }

    $recordownerid = test_input($_POST["recordownerid"]);
      //check if the field only contains numbers
    if (!preg_match("/^[0-9' ]*$/", $recordownerid)) {
        $recordowneridErr = "Only numbers allowed";
    }

}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}



if ($_SERVER["REQUEST_METHOD"] == "POST" and $leasetermnameErr == NULL and $leasetermtextErr == NULL and $leasetermgroupingErr == NULL and $recordowneridErr == NULL) {

    //prepare and bind
    $stmt = $con->prepare("INSERT INTO leaseterms (leaseTermName, leaseTermText, leaseTermGrouping, recordOwnerID) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $leasetermname, $leasetermtext, $leasetermgrouping, $recordownerid);

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
            <div class=\"col-sm-2\"><a href=\"listleaseterms.php\" class=\"btn btn-primary\">View all Clauses</a></div>
        </div>";
    } else {
        echo 'Error creating record: ' . $con->error;
    }
} else {

    ?>
    <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <div class="form-group">
        <label class="form-label col-sm-4" for="leasetermgrouping" style="padding-top:5px">Group By:</label>
        <div class="col-sm-6">
            <select class="form-control" id="leasetermgrouping" name="leasetermgrouping">
            <?php
                echo "<option value=\"0\"> - Leave empty for now - </option>";
            while($row2 = $result2->fetch_assoc()) {
                    echo "<option value=\"" . $row2["leaseTermGrouping"] . "\">". $row2["leaseTermGrouping"] . "</option>";
                }
            ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $leasetermgroupingErr;?></span></div>
    </div>
    <div  class="form-group">
        <label class="form-label col-sm-4" for="leasetermname">Clause Name: <span class="text-danger">*</span></label>
        <div class="col-sm-6"><input class="form-control" id="leasetermname" type="text" name="leasetermname" value="<?php echo $leasetermname;?>"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $leasetermnameErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="leasetermtext">Text: <span class="text-danger">*</span></label>
        <div class="col-sm-10"><textarea class="form-control" id="leasetermtext" name="leasetermtext" rows="15"><?php echo $leasetermtext;?></textarea></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $leasetermtextErr;?></span></div>
    </div>
    <div class="form-group">
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

<script type="text/javascript">
    function myFunction() {
        const options = {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }
        var costInclGST1 = Math.round(document.getElementById("opexitemcost").value * 115) / 100;
        var costInclGST = costInclGST1.toLocaleString('en-US', options);
        document.getElementById("inclGST").innerHTML = costInclGST;
    }
</script>

</div>

<?=template_footer()?>