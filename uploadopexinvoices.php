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

//Get the list of OPEX budgets
$sql3 = "SELECT * FROM opex_view  where recordOwnerID IN ($accessto) ORDER BY opexdate";
$result3 = $con->query($sql3);

?>

<?=template_header('Bulk Add Invoices')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Upload OPEX Invoices</h2>
	</div>
</div>

<div class="block">
    <p> OPEX invoices can be uploaded in bulk</p>
    <p>The upload file must be a tab delimited .txt file with the data in the correct columns.</p>
    <p>You can download the template file below to get started.</p>
    <div class="row">
        <div class="col-sm-2" style="padding-top:20px;"><a href="/downloads/opex_invoice_import_template.txt" class="btn btn-primary" download>Download Template</a></div>
    </div>
</div>


<div class="block">
    <form action="uploadaction.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label class="form-label col-sm-4" for="opexid" style="padding-top:5px">OPEX Budget: <span class="text-danger">*</span></label>
            <div class="col-sm-6">
                <select class="form-control" id="opexid" name="opexid">
                    <?php
                    echo "<option value=\"\"> - Select an OPEX Budget - </option>";
                    while ($row3 = $result3->fetch_assoc()) {
                        echo "<option value=\"" . $row3["opexid"] . "\">" . $row3["buildingname"] . " (" . $row3["opexdate"] . ")</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <input type="file" name="fileToUpload" id="fileToUpload"> 
        <div class="row">
            <input type="hidden" name="step" id="step" value="1">
        </div>
        <div class="row" style="padding:20px 0;">
            <input type="submit" value="Upload File" name="submit" class="btn btn-primary" style="width:100px">
        </div>
    </form>
</div>