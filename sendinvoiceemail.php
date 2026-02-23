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
/*
$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
if (!empty($QueryParameters['searchstring'])) {
  $searchstring = $QueryParameters['searchstring'];
} else {
  $searchstring = ""; 
}
if (!empty($QueryParameters['order'])) {
  $order = $QueryParameters['order'];
} else {
  $order = ""; 
}
if (!empty($QueryParameters['type'])) {
  $type = $QueryParameters['type'];
} else {
  $type = ""; 
}
*/
?>

<?=template_header('Send Email Message')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Send Email Message</h2>
	</div>
</div>

<div class="block">

<?php

$recipientid = $firstname = $lastname = $emailaddress = $subject = $bodytext = $fromheading = "";
$recipientidErr = $firstnameErr = $lastnameErr = $emailaddressErr = $subjectErr = $bodytextErr = $fromheadingErr = "";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST["recipientid"])) {
        $recipientidErr = "Recipient is required";
    } else {
        $recipientid = test_input($_POST["recipientid"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $recipientid)) {
            $recipientidErr = "Only letters, dash and spaces allowed";
        }
    }

    if (empty($_POST["subject"])) {
        $subjectErr = "Subject is required";
    } else {
        $subject = test_input($_POST["subject"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' <>\/]*$/", $subject)) {
            $subjectErr = "Only letters, dash and spaces allowed";
        }
    }

    
        $propertymanagerlogo = test_input($_POST["propertymanagerlogo"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' <>.\/]*$/", $propertymanagerlogo)) {
            $propertymanagerlogoErr = "Only letters, dash, dot and spaces allowed";
        }

        $invoicedescription = test_input($_POST["invoicedescription"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' <>.\/]*$/", $invoicedescription)) {
            $invoicedescriptionErr = "Only letters, dash, dot and spaces allowed";
        }

    if (empty($_POST["invoicetotal"])) {
        $invoicetotal = 0;
    } else {
        $invoicetotal = test_input($_POST["invoicetotal"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[0-9.' ]*$/", $invoicetotal)) {
            $invoicetotalErr = "Only letters, dash and spaces allowed";
        }
    }

    if (empty($_POST["token"])) {
        $token = "";
    } else {
        $token = test_input($_POST["token"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z0-9-]{24,}$/", $token)) {
            $tokenErr = "Only letters, dash and spaces allowed";
        }
    }
    

    if (empty($_POST["fromheading"])) {
        $fromheadingErr = "From Heading is required";
    } else {
        $fromheading = test_input($_POST["fromheading"]);
        //check if the field only contains letters dash or white space
        if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' ]*$/", $fromheading)) {
            $fromheadingErr = "Only letters, dash and spaces allowed";
        }
    }

    if (empty($_POST["duedate"])) {
        $duedate = "";
    } else {
        $duedate = $_POST["duedate"];
    }

    if (empty($_POST["invoicenumber"])) {
        $invoicenumber = "";
    } else {
        $invoicenumber = $_POST["invoicenumber"];
    }

    if (empty($_POST["propertymanager"])) {
        $propertymanager = "";
    } else {
        $propertymanager = $_POST["propertymanager"];
    }

    if (empty($_POST["invoiceid"])) {
        $invoiceid = "";
    } else {
        $invoiceid = $_POST["invoiceid"];
    }

    if (empty($_POST["order"])) {
        $order = "";
    } else {
        $order = $_POST["order"];
    }

    if (empty($_POST["transactiontype"])) {
        $type = "";
    } else {
        $type = $_POST["transactiontype"];
    }

    if (empty($_POST["searchstring"])) {
        $searchstring = "";
    } else {
        $searchstring = $_POST["searchstring"];
    }


/*
echo $recipientid . "<br>" .
    $subject . "<br>" .
    $emailbody . "<br>" .
    $fromheading . "<br>" .
    $emailtemplateurl . "<br>" .
    $parameter . "<br>" .
    $parametervalue . "<br>";
*/
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


if ($_SERVER["REQUEST_METHOD"] == "POST" and $firstnameErr == NULL and $lastnameErr == NULL and $emailaddressErr == NULL and $subjectErr == NULL and $fromheadingErr == NULL) {

    $sql1 = "SELECT * FROM contacts_view WHERE idcontact = $recipientid and recordOwnerID IN ($accessto)";
    $result1 = $con->query($sql1);

    if ($result1->num_rows > 0) {
        while($row1 = $result1->fetch_assoc()) {
            $firstname = $row1["firstname"];
            $lastname = $row1["lastname"];
            //$emailaddress = $row1["emailaddress"];
            $emailaddress = "admin@leasemanager.co.nz";
        }
    }

try {
    $mail->isSMTP();
    $mail->Host = smtp_host;
    $mail->SMTPAuth = true;
    $mail->Username   = smtp_user; // SMTP username
	$mail->Password   = smtp_pass; // SMTP password
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
	$mail->Port   	= smtp_port; // TCP port to connect to (or 80, 25, 8025, 587, 2525)

	//Recipients
	$mail->setFrom('info@leasemanager.co.nz', $fromheading); // Hard coded recipient while testing...
	$mail->addAddress($emailaddress, $firstname.' '.$lastname); // Add a recipient

    // Read the template contents and replace the "%link" placeholder with the above variable
	$email_template = str_replace('%propertymanagerlogo%', $propertymanagerlogo, file_get_contents('invoice-email-template.html'));
    $email_template = str_replace('%invoicenumber%', $invoicenumber, $email_template);
    $email_template = str_replace('%invoicedescription%', $invoicedescription, $email_template);
    $email_template = str_replace('%invoicetotal%', $invoicetotal, $email_template);
    $email_template = str_replace('%token%', $token, $email_template);
    $email_template = str_replace('%duedate%', $duedate, $email_template);
    $email_template = str_replace('%propertymanager%', $propertymanager, $email_template);

    //$email_template = file_get_contents('https://leasemanager.co.nz/'.$emailtemplateurl.'?'.$parameter.'='.$parametervalue);
	// Set email body
	$mail->Body = $email_template;

	$mail->isHTML(true); // Set email format to HTML
	$mail->Subject = $subject;

	//$mail->AltBody = strip_tags($emailbody);

	$mail->send();

    // Mark invoice as sent
    $sql2 = "UPDATE transactions SET invoiceStatusID = '3' WHERE idtransaction = $invoiceid";
    if ($con->query($sql2) === TRUE) {
        echo '<div class="row">
            <div class="col-sm-2">Invoice has been sent</div>
            <div class="col-sm-2"><a href="listtransactions.php?type=' . $type . '&order=' . $order . '&searchstring=' . $searchstring . '" class="btn btn-primary">Back to Invoices</a></div>
        </div>';
    }

} catch (Exception $e) {
	echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

} else {

    $sqlg = "SELECT * FROM contacts_view WHERE emailaddress <> '' and recordOwnerID IN ($accessto) ORDER BY firstName";
    $resultg = $con->query($sqlg);

?>
<form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <div  class="form-group">
        <label class="form-label col-sm-4" for="title">Subject:</label>
        <div class="col-sm-6"><input class="form-control" id="subject" type="text" name="subject" value="Hello World"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $subjectErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="recipientid">To:</label>
        <div class="col-sm-6">
            <select class="form-control" id="recipientid" type="text" name="recipientid">
                <?php
                echo "<option value=\"0\"> - Select a Contact - </option>";
                while ($rowg = $resultg->fetch_assoc()) {
                    echo "<option value=" . $rowg["idcontact"] . ">" . $rowg["firstname"] . " " . $rowg["lastname"] . " - " . $rowg["companyname"] . " (" . $rowg["emailaddress"] . ")</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $recipientidErr;?></span></div>
    </div>
    <div class="form-group">
        <label class="form-label col-sm-4" for="fromheading">From:</label>
        <div class="col-sm-6"><input class="form-control" id="fromheading" type="text" name="fromheading" value="Lease Manager App"></div>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $fromheadingErr;?></span></div>
    </div>
    
    <div class="form-group">
        <label class="form-label col-sm-4" for="emailbody">Body Text:</label>
        <textarea class="form-control" id="emailbody" name="emailbody" rows="3" cols="50" >body text</textarea>
        <div class="col-sm-2"><span class="error"><span class="text-danger"><?php echo $fromheadingErr;?></span></div>
    </div>
     
    <div class="form-group">
        <div class="col-sm-12" style="padding-top:40px;"><input type="submit" value="Send" class="btn btn-primary" style="width:100px"></div>
    </div>
</form>

</div>
<?php
}
?>

</div>