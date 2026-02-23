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
    while ($rowAccess = $resultAccess->fetch_assoc()) {
        $accessto .= "," . $rowAccess["companyID"];
    }
}

?>

<?= template_header('List OPEX') ?>

<div class="page-title">
    <div class="icon">
        <svg width="30" height="30" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
            <path d="M14 2.2C22.5-1.7 32.5-.3 39.6 5.8L80 40.4 120.4 5.8c9-7.7 22.3-7.7 31.2 0L192 40.4 232.4 5.8c9-7.7 22.3-7.7 31.2 0L304 40.4 344.4 5.8c7.1-6.1 17.1-7.5 25.6-3.6s14 12.4 14 21.8l0 464c0 9.4-5.5 17.9-14 21.8s-18.5 2.5-25.6-3.6L304 471.6l-40.4 34.6c-9 7.7-22.3 7.7-31.2 0L192 471.6l-40.4 34.6c-9 7.7-22.3 7.7-31.2 0L80 471.6 39.6 506.2c-7.1 6.1-17.1 7.5-25.6 3.6S0 497.4 0 488L0 24C0 14.6 5.5 6.1 14 2.2zM96 144c-8.8 0-16 7.2-16 16s7.2 16 16 16l192 0c8.8 0 16-7.2 16-16s-7.2-16-16-16L96 144zM80 352c0 8.8 7.2 16 16 16l192 0c8.8 0 16-7.2 16-16s-7.2-16-16-16L96 336c-8.8 0-16 7.2-16 16zM96 240c-8.8 0-16 7.2-16 16s7.2 16 16 16l192 0c8.8 0 16-7.2 16-16s-7.2-16-16-16L96 240z" />
        </svg>
    </div>
    <div class="wrap">
        <h2>OPEX</h2>
        <!--		<p>Welcome back, <?= htmlspecialchars($_SESSION['account_name'], ENT_QUOTES) ?>!</p>  -->
    </div>
</div>

<div class="block">


    <div class="row">
        <div class="col-sm-8">
        </div>
        <div class="col-sm-4" style="text-align:right;">
            <input class="form-control" id="myInput" type="text" placeholder="Filter OPEX">
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $("#myInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#myTable tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>


    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Building Name</th>
                <th>Annual OPEX Cost</th>
                <th>Annual OPEX incl. GST</th>
                <th>Status</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody id="myTable">
            <?php

            $sql = "SELECT * FROM opex_view WHERE opexstatusid <> 3 and recordOwnerID IN ($accessto) ORDER BY opexstatusid DESC, opexdate, buildingname";
            $result = $con->query($sql);

            $sql2 = "SELECT * FROM opexstatus";
            $result2 = $con->query($sql2);

            if ($result->num_rows > 0) {


                // output data of each row
                while ($row = $result->fetch_assoc()) {
                    if ($row["opexstatusid"] == 2) {
                        echo "<tr class=\"alert-success\" style=\"border-top:solid 1px #ccc;\">";
                    } else {
                        echo "<tr style=\"border-top:solid 1px #ccc;\">";
                    }

                    if ($row["annualopexcost"] == NULL) {
                        $annualopexcost = 0;
                    } else {
                        $annualopexcost = $row["annualopexcost"];
                    }

                    if ($row["annualopexcostgst"] == NULL) {
                        $annualopexcostgst = 0;
                    } else {
                        $annualopexcostgst = $row["annualopexcostgst"];
                    }

                    echo "<td style=\"margin:10px 0 10px 0; \">" . date_format(date_create($row["opexdate"]), "j F Y") . "</td>
            <td style=\"margin:10px 0 10px 0; \"><a href=\"/viewopex.php?opexid=" . $row["opexid"] . "\">" . $row["buildingname"] . "</a></td>
            <td style=\"margin:10px 0 10px 0; \">$" . number_format($annualopexcost, 2) . "</td>
            <td style=\"margin:10px 0 10px 0; \">$" . number_format($annualopexcost + $annualopexcostgst, 2) . "</td>
            <td style=\"margin:10px 0 10px 0; \">" . $row["opexstatus"] . "</td>
        </tr>";
                }
            } else {
                echo "0 results";
            }

            echo "</table>
    <div class=\"row\">
        <div class=\"col-sm-6\" style=\"padding-top:20px;\"><a href=\"addopex.php\" class=\"btn btn-primary\">Add OPEX</a></div>
        <div class=\"col-sm-6\" style=\"padding-top:20px; text-align:right;\"><a href=\"listopexexpired.php\">Show expired OPEX records</a></div>
    </div>";

            $con->close();
            ?>


            <?= template_footer() ?>