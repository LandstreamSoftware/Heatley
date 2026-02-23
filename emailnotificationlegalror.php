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


<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Renewal of Lease - contract</title>
    <meta name="author" content="Barry Pyle" />
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            text-indent: 0;
        }

        h1 {
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 15pt;
        }

        .p,
        p {
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 10pt;
            margin: 0pt;
        }

        h3 {
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 10pt;
        }

        h2 {
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 11pt;
        }

        .s1 {
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: italic;
            font-weight: normal;
            text-decoration: none;
            font-size: 10pt;
        }

        li {
            display: block;
        }

        #l1 {
            padding-left: 0pt;
            counter-reset: c1 1;
        }

        #l1>li>*:first-child:before {
            counter-increment: c1;
            content: counter(c1, upper-latin)". ";
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 10pt;
            padding-right: 18px;
        }

        #l1>li:first-child>*:first-child:before {
            counter-increment: c1 0;
        }

        #l2 {
            padding-left: 0pt;
            counter-reset: c2 1;
        }

        #l2>li>*:first-child:before {
            counter-increment: c2;
            content: counter(c2, decimal)" ";
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 11pt;
            padding-right: 20px;
        }

        #l2>li:first-child>*:first-child:before {
            counter-increment: c2 0;
        }

        #l3 {
            padding-left: 0pt;
            counter-reset: c3 1;
        }

        #l3>li>*:first-child:before {
            counter-increment: c3;
            content: counter(c2, decimal)"." counter(c3, decimal)" ";
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: bold;
            text-decoration: none;
            font-size: 10pt;
            padding-right: 10px;
        }

        #l3>li:first-child>*:first-child:before {
            counter-increment: c3 0;
        }

        #l4 {
            padding-left: 0pt;
            counter-reset: d1 1;
        }

        #l4>li>*:first-child:before {
            counter-increment: d1;
            content: "(" counter(d1, lower-latin)") ";
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 10pt;
            padding-right: 15px;
        }

        #l4>li:first-child>*:first-child:before {
            counter-increment: d1 0;
        }

        #l5 {
            padding-left: 0pt;

            counter-reset: e1 1;
        }

        #l5>li>*:first-child:before {
            counter-increment: e1;
            content: "(" counter(e1, lower-latin)") ";
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-style: normal;
            font-weight: normal;
            text-decoration: none;
            font-size: 10pt;
            padding-right: 15px;
        }

        #l5>li:first-child>*:first-child:before {
            counter-increment: e1 0;
        }

        td {
            vertical-align: middle;
        }

        #highlight {
            color:orange;
        }

        @media screen {
            p.page-number {
                display:none;
            }
        }

    


    </style>
</head>

<?php

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPrenewalid = $QueryParameters['renewalid'];

$sql = "SELECT * FROM notification_legal_view WHERE idrenewals = $QPrenewalid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $tenantcompanyname = $row["tenantcompanyname"];
        $commencement = date_format(date_create($row["commencement"]),"j F Y");
        $startdatelong = date_format(date_create($row["startdate"]),"j F Y");
        $enddatelong = date_format(date_create($row["enddate"]),"j F Y");
        $startdate = date_create($row["startdate"]);
        $renewalstartdate = $row["startdate"];
        $enddate = date_add(date_create($row["enddate"]),date_interval_create_from_date_string("1 day"));
        $renewalterm = $startdate->diff($enddate);
        $rentamount = number_format($row["rentpremises"] + $row["rentcarparks"],2);
        $monthlyrent = number_format(($row["rentpremises"] + $row["rentcarparks"])/12,2);
        $premisesaddress = "";
        if(!empty($row["premisesunitname"])) {
            $premisesaddress = $row["premisesunitname"].", ";
        }
        if(!empty($row["premisesaddress1"])) {
            $premisesaddress = $premisesaddress.$row["premisesaddress1"].", ";
        }
        if(!empty($row["premisesaddress2"])) {
            $premisesaddress = $premisesaddress.$row["premisesaddress2"].", ";
        }
        if(!empty($row["premisessuburb"])) {
            $premisesaddress = $premisesaddress.$row["premisessuburb"].", ";
        }
        if(!empty($row["premisescity"])) {
            $premisesaddress = $premisesaddress.$row["premisescity"];
        }
        $buildingid = $row["idbuildings"];
        $leaseid = $row["idlease"];
        if(!empty($row["guarantorid"])) {
            $guarantorid = $row["guarantorid"];
        } else {
            $guarantorid = "";
        }
        
    }
    
    //Get the building owner
    $sql2 = "SELECT * FROM buildings_view WHERE idbuildings = $buildingid and recordOwnerID IN ($accessto)";
    $result2 = $con->query($sql2);

    while($row2 = $result2->fetch_assoc()) {
        $ownercompanyid = $row2["idcompany"];
        $ownercompanyname = $row2["buildingowner"];
    }

    //Get the owners'primary contact full name
    $sql3 = "SELECT * FROM contact_primary_view WHERE idcompany = $ownercompanyid and recordOwnerID IN ($accessto)";
    $result3 = $con->query($sql3);

    while($row3 = $result3->fetch_assoc()) {
        $ownerfullname = $row3["firstname"] . " " . $row3["middlename"] . " " . $row3["lastname"];
    }

    //Get any previous Deeds of Variation
    $sql4 = "SELECT * FROM renewals WHERE leaseID = $leaseid and renewalTypeID = 6 and renewalStatusID = 3 and recordOwnerID IN ($accessto) ORDER BY startDate DESC LIMIT 1";
    $result4 = $con->query($sql4);

    while($row4 = $result4->fetch_assoc()) {
        $previousvariationdatelong = date_format(date_create($row4["startDate"]),"j F Y");
    }

    //Count future rights of renewal
    $sql5 = "SELECT count(idrenewals) AS futurerenewals FROM renewals WHERE leaseID = $leaseid and startDate > '$renewalstartdate' and renewalTypeID between 3 and 5 and recordOwnerID IN ($accessto)";
    $result5 = $con->query($sql5);

    while($row5 = $result5->fetch_assoc()) {
        $futurerenewalscount = $row5["futurerenewals"];
        switch ($futurerenewalscount) {
            case 0:
                $countword = "no";
                $pluralise = "rights";
                break;
            case 1:
                $countword = "one";
                $pluralise = "right";
                break;
            case 2:
                $countword = "two";
                $pluralise = "rights";
                break;
            case 3:
                $countword = "three";
                $pluralise = "rights";
                break;
            case 4:
                $countword = "four";
                $pluralise = "rights";
                break;
            case 5:
                $countword = "five";
                $pluralise = "rights";
                break;
            case 6:
                $countword = "six";
                $pluralise = "rights";
                break;
            case 7:
                $countword = "seven";
                $pluralise = "rights";
                break;
            case 8:
                $countword = "eight";
                $pluralise = "rights";
                break;
            case 9:
                $countword = "nine";
                $pluralise = "rights";
                break;
            default:
            $countword = $futurerenewalscount;
            $pluralise = "rights";
        }
    }

    //Get the guarantor's full name
    if(empty($guarantorid)) {
        $guarantorfullname = "";
    } else {
        $sql6 = "SELECT firstName, middleName, lastName FROM contacts WHERE idcontacts = $guarantorid and recordOwnerID IN ($accessto)";
        $result6 = $con->query($sql6);

        while($row6 = $result6->fetch_assoc()) {
            $guarantorfullname = $row6["firstName"] . " " . $row6["middleName"] . " " . $row6["lastName"];
        }
    }
}
?>

<body>

<table style="width:700px; margin-left:auto; margin-right:auto;">
    <tr>
        <td colspan="3" style="padding: 0 0 10px 5px;">
            <h1 style="padding-top: 3pt;padding-left: 3pt;text-indent: 0pt;text-align: left;">Renewal of lease</h1>
        </td>
    </tr>
    <tr>
        <td style="width:45%;"><p style="padding: 0 0 20px 10px;">This deed is made on</p></td>
        <td style="width:10%;"><p>day of</p></td>
        <td style="width:45%; text-align:center;"><P><?php echo date("Y") ?></p></td>
    </tr>
    <tr>
        <td colspan="3" style="padding: 0 0 10px 5px;">
            <h3 style="padding-left: 7pt;text-indent: 0pt;text-align: left;">PARTIES</h3>
    <p style="text-indent: 0pt;text-align: left;"><br /></p>
    <p style="padding-left: 7pt;text-indent: 0pt;text-align: left;"><?php echo $ownercompanyname ?> (<b>Lessor</b>)</p>
    <p style="text-indent: 0pt;text-align: left;"><br /></p>
    <p style="padding-left: 7pt;text-indent: 0pt;text-align: left;"><?php echo $tenantcompanyname ?> (<b>Lessee</b>)</p>
    <p style="text-indent: 0pt;text-align: left;"><br /></p>

    <?php 
    if(!empty($guarantorid)) { ?>
        <p style="padding-left: 7pt;text-indent: 0pt;text-align: left;"><?php if(!empty($guarantorfullname)) {echo $guarantorfullname . " (<b>Guarantor</b>)"; } ?> </p>
        <p style="text-indent: 0pt;text-align: left;"><br /></p>
        <?php 
    } ?>

    <p style="padding-top: 3pt;text-indent: 0pt;text-align: left;"><br /></p>

    <h3 style="padding-left: 7pt;text-indent: 0pt;text-align: left;">BACKGROUND</h3>
    <p style="text-indent: 0pt;text-align: left;"><br /></p>
    <ol id="l1">
        <li data-list-text="A.">
            <p style="padding-left: 34pt;text-indent: -26pt;text-align: left;">By the Lease referred to in clause 1.1
                the Premises referred to in clause 1.1 were leased at the rental and on the terms and provisions
                contained in the Lease.</p>
        </li>
        <li data-list-text="B.">
            <p style="padding-top: 11pt;padding-left: 34pt;text-indent: -26pt;text-align: left;">The Lessor and the
                Lessee are the current Lessor and Lessee respectively under the Lease.</p>
        </li>
        <li data-list-text="C.">
            <p style="padding-top: 11pt;padding-left: 34pt;text-indent: -26pt;text-align: left;">The Lessor and the
                Lessee have agreed upon the initial rental to be paid during the Renewed Term and the parties are
                completing this deed to record the Renewed Term.</p>
                <p style="padding-top: 3pt;text-indent: 0pt;text-align: left;"><br /></p>
            <h3 style="padding-top: 11pt;padding-left: 7pt;text-indent: 0pt;text-align: left;">OPERATIVE PROVISIONS</h3>
            <p style="padding-top: 3pt;text-indent: 0pt;text-align: left;"><br /></p>
            <ol id="l2">
                <li data-list-text="1">
                    <h2 style="padding-left: 34pt;text-indent: -26pt;text-align: left;">Definitions and Interpretation
                    </h2>
                    <p style="padding-left: 6pt;text-indent: 0pt;line-height: 1pt;text-align: left;"><span><img
                                width="97%" height="1" alt="image" src="img/underline.png" /></span></p>
                    <p style="padding-top: 2pt;text-indent: 0pt;text-align: left;"><br /></p>
                    <ol id="l3">
                        <li data-list-text="1.1">
                            <h3 style="padding-left: 34pt;text-indent: -26pt;text-align: left;">Definitions</h3>
                            <p style="padding-top: 6pt;padding-left: 34pt;text-indent: 0pt;text-align: left;">In this
                                deed:</p>
                            <p style="text-indent: 0pt;text-align: left;"><br /></p>
                            
                            <?php
                            if ($result4->num_rows > 0) {
                                ?>
                                <h3 style="padding-left: 34pt;text-indent: 0pt;text-align: left;">Lease <span class="p">means the deed of lease between <?php echo $ownercompanyname ?> and <?php echo $tenantcompanyname ?> dated <?php echo $commencement?> and includes the Deed of Variation of Lease dated <?php echo $previousvariationdatelong?>.</span></h3>
                            <?php
                            } else {
                                ?>
                                <h3 style="padding-left: 34pt;text-indent: 0pt;text-align: left;">Lease <span class="p">means the deed of lease between <?php echo $ownercompanyname ?> and <?php echo $tenantcompanyname ?> dated <?php echo $commencement?>.</span></h3>
                            <?php
                            }
                            ?>

                            <h3 style="padding-top: 11pt;padding-left: 34pt;text-indent: 0pt;text-align: left;">Lessor
                                <span class="p">and </span>Lessee <span class="p">includes their respective successors,
                                    executors, administrators and permitted assigns.</span></h3>
                            <h3 style="padding-top: 11pt;padding-left: 34pt;text-indent: 0pt;text-align: left;">Premises
                                <span class="p">means the premises leased pursuant to the Lease, being <?php 
                                echo $premisesaddress ?>.</span></h3>
                            <h3 style="padding-top: 11pt;padding-left: 34pt;text-indent: 0pt;text-align: left;">Renewed
                                Term <span class="p">means the renewed term of the Lease evidenced by this
                                    renewal.</span></h3>
                            <p style="padding-top: 3pt;text-indent: 0pt;text-align: left;"><br /></p>
                        </li>
                        <li data-list-text="1.2">
                            <h3 style="padding-left: 34pt;text-indent: -26pt;text-align: left;">Interpretation</h3>
                            <p style="padding-top: 6pt;padding-left: 34pt;text-indent: 0pt;text-align: left;">This deed
                                is supplemental to the Lease and expressions and definitions used in this deed bear the
                                same meaning given to them in the Lease.</p>
                            <p style="padding-top: 3pt;text-indent: 0pt;text-align: left;"></p>
                            <p style="padding-top: 6pt;padding-left: 34pt;text-indent: 0pt;text-align: left;">Where obligations bind more than
                                than one person those obligations will bind those persons jointly and severally.</p>
                            <p style="padding-top: 3pt;text-indent: 0pt;text-align: left;"><br /></p>
                        </li>
                    </ol>
                </li>
                <li data-list-text="2">
                    <h2 style="padding-bottom: 1pt;padding-left: 34pt;text-indent: -26pt;text-align: left;">Renewal of term</h2>
                    <p style="padding-left: 6pt;text-indent: 0pt;line-height: 1pt;text-align: left;"><span><img
                                width="97%" height="1" alt="image" src="img/underline.png" /></span></p>
                    <ol id="l4">
                        <li data-list-text="(a)">
                            <p style="padding-top: 5pt;padding-left: 61pt;text-indent: -26pt;text-align: left;">In
                                exercise of the right to a Renewed Term contained in the Lease, the term of the Lease is
                                renewed for a period of <?php echo $renewalterm->format('%y years') ?> from <?php echo $startdatelong ?>.</p>
                        </li>
                        <li data-list-text="(b)">
                            <p style="padding-top: 11pt;padding-left: 61pt;text-indent: -26pt;text-align: left;">For the
                                purposes of clarity, the parties acknowledge that the Lessee has <?php echo $countword?> further <?php echo $pluralise?> to a
                                renewed term.</p>
                            <p style="padding-top: 3pt;text-indent: 0pt;text-align: left;"><br /></p>
                        </li>
                    </ol>
                </li>
            <p class="page-number" style="text-align: right;">1</p>
            <p style="page-break-after: always;">&nbsp;</p>
                <li data-list-text="3">
                    <h2 style="padding-top: 4pt;padding-left: 34pt;text-indent: -26pt;text-align: left;">Rental, rates
                        and other outgoings</h2>
                    <p style="padding-left: 6pt;text-indent: 0pt;line-height: 1pt;text-align: left;"><span><img
                                width="97%" height="1" alt="image" src="img/underline.png" /></span></p>
                    <ol id="l5">
                        <li data-list-text="(a)">
                            <p style="padding-top: 5pt;padding-left: 61pt;text-indent: -26pt;text-align: left;">From the
                                date of commencement of the Renewed Term the Lessee must pay rental to the Lessor at the
                                rate of $<?php echo $rentamount ?> per annum plus GST payable in advance by equal
                                monthly payments of $<?php echo $monthlyrent ?> plus GST commencing with a first payment on <?php echo $startdatelong ?>.</p>
                        </li>
                        <li data-list-text="(b)">
                            <p style="padding-top: 11pt;padding-left: 60pt;text-indent: -26pt;text-align: left;">The
                                Lessor may review the rental on each renewal date in accordance with clause 11 in the
                                First Schedule of the Lease.</p>
                        </li>
                        <li data-list-text="(c)">
                            <p style="padding-top: 11pt;padding-left: 61pt;text-indent: -26pt;text-align: left;">In
                                addition to the rental provided in clauses 3(a) and 3(b), the Lessee must continue to
                                pay the operating expenses and other amounts as provided in the Lease.</p>
                        </li>
                    </ol>
                    <p style="padding-top: 3pt;text-indent: 0pt;text-align: left;"><br /></p>
                </li>
                <li data-list-text="4">
                    <h2 style="padding-left: 34pt;text-indent: -26pt;text-align: left;">Confirmation of other Lease
                        covenants</h2>
                    <p style="padding-left: 6pt;text-indent: 0pt;line-height: 1pt;text-align: left;"><span><img
                                width="97%" height="1" alt="image" src="img/underline.png" /></span></p>
                    <p style="padding-top: 5pt;padding-left: 34pt;text-indent: 0pt;text-align: left;">The Lessee
                        acknowledges to the Lessor that during the Renewed Term the Lessee will continue to hold the
                        Premises on the same terms and provisions expressed or implied in the Lease subject to the
                        variations set out in this deed, and the Lessee covenants with the Lessor that the Lessee must
                        duly and punctually perform and observe the covenants and provisions of the Lease but as varied
                        by this deed.</p>
                    <p style="padding-top: 3pt;text-indent: 0pt;text-align: left;"><br /></p>
                </li>
                <li data-list-text="5">
                    <h2 style="padding-bottom: 1pt;padding-left: 34pt;text-indent: -26pt;text-align: left;">Costs</h2>
                </li>
            </ol>
        </li>
    </ol>
    <p style="padding-left: 6pt;text-indent: 0pt;line-height: 1pt;text-align: left;"><span><img width="97%" height="1"
                alt="image" src="img/underline.png" /></span></p>
    <p style="padding-top: 5pt;padding-left: 34pt;text-indent: 0pt;text-align: left;">The Lessee will pay the Lessor’s
        costs and disbursements of and incidental to the negotiation and execution of this deed.</p>
        </td>
    </tr>
    <tr><td colspan="3" style="text-align: right;"><p class="page-number">2</p></td></tr>
    <tr><td><p style="page-break-after: always;">&nbsp;</p></td></tr>
    <tr>
        <td style="width:45%;"><p style="padding: 20px 0 20px 10px;">This deed was executed the</p></td>
        <td style="width:10%;"><p>day of</p></td>
        <td style="width:45%; text-align:center;"><P><?php echo date("Y") ?></p></td>
    </tr>

    <?php //Landlord's signature ?>
    <tr>
        <td>
            <p style="padding: 20px 0 0 10px;">Signed by <?php echo $ownercompanyname ?> as <b>Lessor:</b></p>
        </td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    <tr>
    <tr>
        <td colspan="2">&nbsp;</td>
        <td style="border-top:solid 1px black; vertical-align: top;">
            <p style="padding: 0 0 50px 10px;">Witness name</p>
        </td>
    </tr>
    <tr>
        <td style="border-top:solid 1px black;">
            <p style="padding: 0 0 50px 10px;">Signature <?php echo $ownerfullname ?> (director)
        </td>
        <td>&nbsp;</td>
        <td style="border-top:solid 1px black;">
            <p style="padding: 0 0 50px 10px;">Signature</p>
        </td>
    <tr>
        <td style="border-top:solid 1px black;">
            <p style="padding: 0 0 50px 10px;">Date</p>
        </td>
        <td>&nbsp;</td>
        <td style="border-top:solid 1px black;">
            <p style="padding: 0 0 50px 10px;">Date</p>
        </td>
    </tr>

    <?php //Tenant's signature ?>
    <tr>
        <td>
            <p style="padding: 0 0 0 10px;">Signed by <?php echo $tenantcompanyname ?> as <b>Lessee:</b></p>
        </td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    <tr>
    <tr>
        <td colspan="2">&nbsp;</td>
        <td style="border-top:solid 1px black; vertical-align: top;">
            <p style="padding: 0 0 50px 10px;">Witness name</p>
        </td>
    </tr>
    <tr>
        <td style="border-top:solid 1px black;">
            <p style="padding: 0 0 50px 10px;">Signature of Director</p>
        </td>
        <td>&nbsp;</td>
        <td style="border-top:solid 1px black;">
            <p style="padding: 0 0 50px 10px;">Signature</p>
        </td>
    <tr>
        <td style="border-top:solid 1px black;">
            <p style="padding: 0 0 50px 10px;">Name</p>
        </td>
        <td>&nbsp;</td>
        <td style="border-top:solid 1px black;">
            <p style="padding: 0 0 50px 10px;">Date</p>
        </td>
    </tr>
    <tr>
        <td style="border-top:solid 1px black;">
            <p style="padding: 0 0 50px 10px;">Date</p>
        </td>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td style="border-top:solid 1px black;">
            <p style="padding: 0 0 50px 10px;">Signature of Director</p>
        </td>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td style="border-top:solid 1px black;">
            <p style="padding: 0 0 50px 10px;">Name</p>
        </td>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td style="border-top:solid 1px black;">
            <p style="padding: 0 0 50px 10px;">Date</p>
        </td>
        <td colspan="2">&nbsp;</td>
    </tr>

    <?php //Guarantor's signature 
    if(!empty($guarantorid)) { ?>
    <tr>
        <td>
            <p style="padding: 0 0 0 10px;">Signed by <?php echo $guarantorfullname ?> as <b>Guarantor:</b></p>
        </td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    <tr>
    <tr>
        <td colspan="2">&nbsp;</td>
        <td style="border-top:solid 1px black; vertical-align: top;">
            <p style="padding: 0 0 50px 10px;">Witness name</p>
        </td>
    </tr>
    <tr>
        <td style="border-top:solid 1px black;">
            <p style="padding: 0 0 50px 10px;">Signature <?php echo $guarantorfullname ?> (guarantor)</p>
        </td>
        <td>&nbsp;</td>
        <td style="border-top:solid 1px black;">
            <p style="padding: 0 0 50px 10px;">Signature</p>
        </td>
    <tr>
        <td style="border-top:solid 1px black;">
            <p style="padding: 0 0 50px 10px;">Date</p>
        </td>
        <td>&nbsp;</td>
        <td style="border-top:solid 1px black;">
            <p style="padding: 0 0 50px 10px;">Date</p>
        </td>
    </tr>
    <?php
    }
    ?>

    <tr>
        <td>

 

    <p class="s1" style="padding: 0 0 0 10px;">(If signed by two
        directors no witness is required)</p>
        </td>
    </tr>
    <tr><td colspan="3" style="text-align: right;"><p class="page-number">3</p></td></tr>
</table>


</body>

</html>