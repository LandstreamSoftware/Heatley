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

<?=template_header('Delete File')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Delete File</h2>
	</div>
</div>

<div class="block">

<?php

//$Q = explode("/", $_SERVER['QUERY_STRING']);
//parse_str($Q[0],$QueryParameters);
//$idfile = $QueryParameters['idfile'];
//$filepath = $QueryParameters['filepath'];
//$complianceid = $QueryParameters['complianceid'];

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST['idfile'])) {
    $idfile = intval($_POST['idfile']);
    //$filepath = intval($_POST['filepath']);
    //$complianceid = intval($_POST['id']);

    //$logFile = LOG_FILE_PATH;
    //$dateNow = new DateTime('now', new DateTimeZone('Pacific/Auckland'));
    //$currentDateTime = $dateNow->format('Y-m-d H:i:s');

    //$logMessage = "File path: $filepath - \n";
    //file_put_contents($logFile, $logMessage, FILE_APPEND);

    $sql = "SELECT * FROM files WHERE idfile = $idfile";
    $result = $con->query($sql);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $filepath = $row["filePath"];
        }
    }

    if (file_exists($filepath)) {
        if (is_writable($filepath)) {
            if (unlink($filepath)) {
                echo "File has been deleted successfully.";
                //prepare the delete statement to prevent SQL injection
                $stmt = $con->prepare("DELETE FROM files WHERE idfile = ?");
                $stmt->bind_param('i', $idfile);

                if ($stmt->execute()) {
                    echo '<div class=\"row\">
                    <table class="table table-hover">
                        <tbody>
                            <tr class="success">
                                <td>File deleted successfully</td>
                            </tr>
                        </tbody>
                    </table>';
                    echo "<div class=\"row\">
                        <div class=\"col-sm-2\"><a href=\"editcompliance.php?id=".$complianceid."\" class=\"btn btn-primary\">Back to Compliance record</a></div>
                    </div>";
                } else {
                    echo 'Error deleting record: ' . $con->error;
                }
            } else {
                echo "Error: Unable to delete file.";
            }
        } else {
            echo "PHP does not have permission to dlete this file.";
        }
        
    } else {
        echo "File does not exist.";
    }

}
?>

<?=template_footer()?>