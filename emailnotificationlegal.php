<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
	header('Location: index.html');
	exit;
} else {
    $uid = $_SESSION["id"];
    
    include 'dbconnect/db.php';

    $sqlAccess = "SELECT * FROM accesscontrol WHERE accountID = $accountid";
    $resultAccess = $con->query($sqlAccess);

    $accessto = -1;

    if ($resultAccess->num_rows > 0) {
        while($rowAccess = $resultAccess->fetch_assoc()) {
            $accessto .= "," . $rowAccess["companyID"]; 
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
    </head>
<body>

<div class="container">
    <div class="menu">
        <?php include 'menu.php';?>
    </div>

<?php

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPcontenttype = $QueryParameters['contenttype'];
if(!empty($QueryParameters['renewalid'])) {
    $QPrenewalid = $QueryParameters['renewalid'];
}
if(!empty($QueryParameters['renewalstatusid'])) {
    $QPrenewalstatusid = $QueryParameters['renewalstatusid'];
}
//if(!empty($QueryParameters['companyid'])) {
//    $QPcompanyid = $QueryParameters['companyid'];
//}
if(!empty($QueryParameters['opexid'])) {
    $QPopexid = $QueryParameters['opexid'];
}

//Use to list the OPEX item
$sql = "SELECT * FROM notification_opex_view WHERE `idrenewals` = $QPrenewalid and `idopex` = $QPopexid and recordOwnerID IN ($accessto)";
$result = $conn->query($sql);

//Get one record for the email content
$sql2 = "SELECT * FROM notification_opex_view WHERE `idrenewals` = $QPrenewalid and `idopex` = $QPopexid and recordOwnerID IN ($accessto) LIMIT 1";
$result2 = $conn->query($sql2);


$GST = 0.15;

//Build the content
while($row2 = $result2->fetch_assoc()) {

    $opexbudgetdatefrom = date_format(date_create($row2["opexdate"]),"Y");
    $opexbudgetdateto = date_format(date_add(date_create($row2["opexdate"]),date_interval_create_from_date_string("1 year")),"Y");

    $rentpremises = $row2["rentpremises"];
    $rentcarparks = $row2["rentcarparks"];

    $managementfeepercent = $row2["managementfeepercent"];

    $firstname = $row2["firstname"];
    $lastname = $row2["lastname"];
    $emailaddress = $row2["emailaddress"];

    echo 
    "<div class=\"row well\">
        <div class=\"col-sm-12\">
            <p><strong>To: </strong>" . $firstname . " " . $lastname . " (" . $emailaddress . ")</p>
        </div>
    </div>";
    //The message
    echo "<div class=\"row well\">
        <div class=\"col-sm-12\">
            <p>Hi " . $row2["firstname"] . ",</p>";
    switch($QPcontenttype) {
        case "renewal":
            echo "<p>Below are the details of the ".$row2["renewaltype"]." for your lease at ".$row2["premisesaddress1"].", which is due to take effect on ".date_format(date_create($row2["startdate"]),"j F Y").".</p>
            ";
            $renewaltypeid = $row2["renewaltypeid"];
        switch($renewaltypeid) {
            case "1": //Rent at Commencement
                echo "<p></p>";
                break;
            case "3"://1st Right of Renewal
                echo "<p>In exercising your right of renewal, the term of the lease will be extended until ".date_format(date_create($row2["enddate"]),"j F Y").".</p>";
                break;
            case "4": //2nd Right of Renewal
                echo "<p>In exercising your right of renewal, the term of the lease will be extended until ".date_format(date_create($row2["enddate"]),"j F Y").".</p>";
                break;
            case "5": //3rd Right of Renewal
                echo "<p>In exercising your right of renewal, the term of the lease will be extended until ".date_format(date_create($row2["enddate"]),"j F Y").".</p></p>";
                break;
            case "6": //Deed of Variation
                echo "<p></p>";
                break;
            case "7": //CPI Rent Review
                echo "<p>Your rent will increase by the Consumer price index as published on the <a href=\"https://www.stats.govt.nz/indicators/consumers-price-index-cpi\">StatsNZ website</a>.</p>";
                break;
            case "8": //Market Rent Review
                echo "<p>The market rent for your premises has been assesed at $".number_format($row2["rentpremises"],2)." plus GST per annum.</p>";
                break;
            case "9": //Fixed % Rent Review
                echo "<p>Your fixed rent review will increase your rent payments by ".$row2["fixedpercent"]."% as per your lease agreemet.</p>";
            default: 
                echo "";
        }
        echo "<p>If you are happy with the proposed changes please confirm by clicking on the button below or replying to this email.</p>";

        switch($renewaltypeid) {
            case "1": //Rent at Commencement
                    
                break;
            case "3"://1st Right of Renewal
                echo"<p>We will prepare any necessary documentation and forward it to you for signing.</p>";
                break;
            case "4": //2nd Right of Renewal
                echo"<p>We will prepare any necessary documentation and forward it to you for signing.</p>";
                break;
            case "5": //3rd Right of Renewal
                echo"<p>We will prepare any necessary documentation and forward it to you for signing.</p>";
                break;
            case "6": //Deed of Variation
                echo"<p>We will prepare any necessary documentation and forward it to you for signing.</p>";
                break;
            case "7": //CPI Rent Review
                    
                break;
            case "8": //Market Rent Review
                    
                break;
            case "9": //Fixed % Rent Review
                    
            default: 
                    
        }       
            
        echo "</div>
            <div class=\"col-sm-12\">
                <h4 style=\"font-weight:400;\">" . $row2["companyname"] . "</h4>
                <h5>" . $row2["renewaltype"] . "</h5>
                <p>Premises: " . $row2["unitname"] . ", ". $row2["premisesaddress1"] . "</p>
            </div>";
            break;
        case "opexbudget":
        echo "Below are the details of the new OPEX Budget which is due to take effect on April 1 ".$opexbudgetdatefrom.".</p>
        <p>Please make arrangements to modify your payments to include the new amount shown below.</p>
        </div>
            <div class=\"col-sm-12\">
                <h4 style=\"font-weight:400;\">" . $row2["companyname"] . "</h4>
                <h5>OPEX Update</h5>
                <p>Premises: " . $row2["unitname"] . ", ". $row2["premisesaddress1"] . "</p>
            </div>";
        default:
        echo "";
    }

//Renewal details
    //Get the OPEX total
        $sql3 = "SELECT * FROM notification_opextotal_view WHERE `opexid` = $QPopexid and recordOwnerID IN ($accessto)";
        $result3 = $conn->query($sql3);
        while($row3 = $result3->fetch_assoc()) {
            $opextotal = $row3["opextotal"];
        }

        
        $opexpercentage = $row2["opexpercentage"];
        $opexportion = $opextotal * $opexpercentage / 100;
        $managementfee = ($rentpremises + $rentcarparks + $opexportion) * $managementfeepercent / 100;
        $permonthtotal = ($rentpremises + $rentcarparks + $opexportion + $managementfee) * (1 + $GST) / 12;

echo "<table style=\"width:60%; margin:15px; border: 1px solid #555;\">
        <th style=\"background-color:#dadada; padding: 20px 10px; width:55%\">Item</th>
        <th style=\"background-color:#dadada; width:5%;\">&nbsp;</th>
        <th style=\"background-color:#dadada; width:5%; text-align:center;\">Per Annum<br>(excl GST)</th>
        <th style=\"background-color:#dadada; width:5%;\">&nbsp;</th>
        <th style=\"background-color:#dadada; width:5%; text-align:center;\">Monthly<br>(excl GST)</th>
        <th style=\"background-color:#dadada; width:5%;\">&nbsp;</th>
        <th style=\"background-color:#dadada; width:5%; text-align:center;\">Monthly<br>(incl GST)</th>
        <tr>
            <td style=\"padding: 10px 10px;\">Rent</td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format($rentpremises + $rentcarparks,2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format(($rentpremises + $rentcarparks) / 12,2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format(($rentpremises + $rentcarparks) * (1 + $GST) / 12,2) . "</h5></td>
        </tr>
        <tr>
            <td style=\"padding: 10px 10px;\">OPEX ".$opexbudgetdatefrom."-".$opexbudgetdateto." Budget</td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format($opextotal * $opexpercentage / 100,2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format($opextotal * $opexpercentage / 100 / 12,2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format($opextotal * $opexpercentage / 100 * (1 + $GST) / 12,2) . "</h5></td>
        </tr>
        <tr>
            <td style=\"padding: 10px 10px;\"><strong>Sub Total</strong></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right; border-top:1px solid;\"><h5 style=\"font-weight:500;\">$" . number_format(($rentpremises + $rentcarparks + $opexportion),2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right; border-top:1px solid;\"><h5 style=\"font-weight:500;\">$" . number_format(($rentpremises + $rentcarparks + $opexportion) / 12,2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right; border-top:1px solid;\"><h5 style=\"font-weight:500;\">$" . number_format(($rentpremises + $rentcarparks + $opexportion) * (1 + $GST) / 12,2) . "</h5></td>
        </tr>
        <tr>
            <td style=\"padding: 10px 10px;\">Management Fee</td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format(($rentpremises + $rentcarparks + $opexportion) * $managementfeepercent / 100,2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format((($rentpremises + $rentcarparks + $opexportion) * $managementfeepercent / 100)/12,2) . "</h5></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5 style=\"font-weight:500;\">$" . number_format((($rentpremises + $rentcarparks + $opexportion) * $managementfeepercent / 100) * (1 + $GST) / 12,2) . "</h5></td>
        </tr>
        <tr>
            <td style=\"padding: 10px 10px;\"><strong>Total</strong></td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right; border-top:1px solid;\"><h5 style=\"font-weight:500;\">$" . number_format($rentpremises + $rentcarparks + $opexportion + $managementfee,2) . "</h5></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td style=\"padding: 10px 10px;\">GST</td>
            <td></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5>$" . number_format(($rentpremises + $rentcarparks + $opexportion + $managementfee) * $GST,2) . "</h5></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr style=\"background-color:#dff0d8; border-top: 1px solid;\">
            <td colspan=\"6\" style=\"padding: 10px 10px;\"><strong>Total to pay each month (incl. GST)</strong></td>
            <td style=\"padding: 0px 10px; text-align:right;\"><h5 style=\"font-weight:500;\">$" . number_format($permonthtotal,2) . "</h5></td>
        </tr>
    </table>";
}



echo "<div class=\"row\" style=\"padding: 10px 20px;\">
        <div class=\"col-sm-12\" style=\"margin-top:20px;\">
            <p>Regards,</p>
            <p>Heidi Hall</p>
            <p>heidi.hall@bayleyspmw.co.nz</p>
        </div>
    </div>
</div>";