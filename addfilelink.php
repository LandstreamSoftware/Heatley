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

<?=template_header('Add File Link')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Add FileLink</h2>
	</div>
</div>

<div class="block"> 

<?php
    // define variables and set to empty values
$filelink = $filename = $recordid = $redirect = "";
$filelinkErr = $filenameErr = $recordidErr = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST["filelink"])) {
        $filelinkErr = "File URL is required";
    } else {
        $filelink = test_input($_POST["filelink"]);
        //check if the field only contains letters dash or white space
        if (filter_var($filelink, FILTER_VALIDATE_URL)) {
            // Valid url
        } else {
            $filelinkErr = "Not a valid URL";
        }
    }

    if (empty($_POST["filename"])) {
        $filenameErr = "File name is required";
    } else {
        $filename = test_input($_POST["filename"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $filename)) {
            $filenameErr = "Only letters, dash and spaces allowed";
        }
    }

    if (empty($_POST["recordid"])) {
        $recordidErr = "No Lease ID";
    } else {
        $recordid = $_POST["recordid"];
    }

    if (empty($_POST["redirect"])) {
        $redirectErr = "No Lease ID";
    } else {
        $redirect = $_POST["redirect"];
    }
    
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}



if ($_SERVER["REQUEST_METHOD"] == "POST" and $filelinkErr == NULL and $filenameErr == NULL and $recordidErr == NULL) {

    //prepare and bind
    $stmt = $con->prepare("INSERT INTO files (recordID, fileName, originalName, filePath) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $recordid, $filename, $filename, $filelink);

    if ($stmt->execute()) { 
        echo '<div class=\"row\">
        <table class="table table-hover">
            <tbody>
                <tr class="success">
                    <td>Document URL Saved!</td>
                </tr>
            </tbody>
        </table>';
     
         echo "<div class=\"row\">
            <div class=\"col-sm-2\"><a href=\"" . $redirect . "\" class=\"btn btn-primary\">Back to Lease</a></div>
        </div>";
    } else {
        echo 'Error creating record: ' . $con->error;
    }
    

} else {

    ?>
    

<div class="row">
<?php
}

$con->close();
?>

</div>

<?=template_footer()?>