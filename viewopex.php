<?php
// Include the main.php file
include 'main.php';
// Check if the user is logged in, if not then redirect to login page
check_loggedin($con);
// Template code below

$accountid = $_SESSION['account_id'];

$sql9 = "SELECT * FROM accesscontrol WHERE accountID = $accountid";
$result9 = $con->query($sql9);

$accessto = 0;
$totaloverunder = 0;

if ($result9->num_rows > 0) {
    while($row9 = $result9->fetch_assoc()) {
       $accessto .= "," . $row9["companyID"]; 
    }
}
?>

<?=template_header('View OPEX')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>OPEX Details</h2>
<!--		<p>Welcome back, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>!</p>  -->
	</div>
</div>

<div class="block">

<?php
$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$QPopexid = $QueryParameters['opexid'];
if (!empty($QueryParameters['order'])) {
  $QPorder = $QueryParameters['order'];
} else {
  $QPorder = "transactiondate DESC";
}
?>

<table class="table">

<?php
$sql = "SELECT * FROM opex_view WHERE opexid = $QPopexid and recordOwnerID IN ($accessto)";
$result = $con->query($sql);

$sql2 = "SELECT * FROM opexitems_view WHERE opexid = $QPopexid and recordownerid IN ($accessto) ORDER BY opexitemname ASC";
$result2 = $con->query($sql2);

$sql8 = "SELECT * FROM opexitemsallocated_view WHERE opexid = $QPopexid and recordownerid IN ($accessto) ORDER BY opexitemname ASC";
$result8 = $con->query($sql8);

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$results_per_page = 20; // Number of results per page
$offset = ($page - 1) * $results_per_page;
// Get total number of records
$total_query = "SELECT COUNT(*) AS total FROM transactions_view WHERE opexid = $QPopexid and recordownerid IN ($accessto)";
$resultCount = $con->query($total_query);
$rowCount = $resultCount->fetch_assoc();
$total_results = $rowCount['total'];
// Calculate total number of pages
$total_pages = ceil($total_results / $results_per_page);

$sql3 = "SELECT * FROM transactions_view WHERE opexid = $QPopexid and recordownerid IN ($accessto) ORDER BY $QPorder LIMIT $results_per_page OFFSET $offset";
$result3 = $con->query($sql3);

//$sql4 = "SELECT * FROM opexinvoice_total_view WHERE opexid = $QPopexid and recordOwnerID IN ($accessto)";


$sql4 = "SELECT SUM(`transactionamount`) AS total FROM opexinvoice_total_view WHERE opexid = $QPopexid";
$result4 = $con->query($sql4);
$opexinvoicecostsum = 0;
if ($result4->num_rows > 0) {
  while($row4 = $result4->fetch_assoc()) {
    if (isset($row4["total"])) {
      $opexinvoicecostsum = number_format($row4["total"],2);
    }
  }
}

$sql5 = "SELECT * FROM opexinvoice_totalrates_view WHERE opexid = $QPopexid and recordOwnerID IN ($accessto)";
$result5 = $con->query($sql5);
if ($result5->num_rows > 0) {
  while($row5 = $result5->fetch_assoc()) {
    $opexinvoicetotalrates = number_format($row5["opexinvoicecostsum"],2);
  }
} else {
  $opexinvoicetotalrates = 0;
}

$sql7 = "SELECT * FROM opexinvoice_totalmanagementfees_view WHERE opexid = $QPopexid and recordOwnerID IN ($accessto)";
$result7 = $con->query($sql7);
if ($result7->num_rows > 0) {
  while($row7 = $result7->fetch_assoc()) {
    $opexinvoicetotalmanfees = number_format($row7["opexinvoicecostsum"],2);
  }
} else {
  $opexinvoicetotalmanfees = 0;
}


if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    $annualopexcost = number_format($row["annualopexcost"],2);
    $annualopexcostplusgst = number_format($row["annualopexcost"] * 1.15,2);
    echo "<tr>
            <td style=\"width:25%\">Building Name:</td>
            <td style=\"width:25%\"><a class=\"h4link\" href=\"/viewbuilding.php?buildingid=" . $row["buildingid"] . "\">" . $row["buildingname"]. "</a></td>
            <td style=\"width:25%\"></td>
            <td style=\"width:25%\"></td>
        </tr>
        <tr>
            <td>Date:</td>
            <td colspan=\"3\">" . date_format(date_create($row["opexdate"]),"j F Y") . "</td>
        </tr>
        <tr>
            <td>Status</td>
            <td colspan=\"3\">" . $row["opexstatus"]. "</td>
        </tr>
        </table>";
    $buildingid = $row["buildingid"];
  }
} else {
    $resultcount = 0;
    echo "0 results";
    echo "</tbody></table>";
}

//$sql3 = "SELECT * FROM tenants_of_building_view WHERE idbuildings = $buildingid and recordOwnerID IN ($accessto)";
//$result3 = $con->query($sql3);

echo
"<div class=\"row\">
        <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px\"><a href=\"editopex.php?opexid=" . $QPopexid . "\" class=\"btn btn-primary\">Edit OPEX</a></div>
</div>

<div>
    <h3 style=\"padding:15px 0 15px 0;\">OPEX Budget</h3>
</div>

<div class=\"container-fluid\" style=\"margin:3px 0 0 0;\">
  <div class=\"row\" style=\"margin:3px 0 10px 0;\">
    <div class=\"col-sm-3\"><strong>Opex Item</strong></div>
    <div class=\"col-sm-3\"><strong>Category</strong></div>
    <div class=\"col-sm-2\" style=\" text-align:right;\"><strong>Annual Budget</strong></div>
    <div class=\"col-sm-2\" style=\" text-align:right;\"><strong>Annual Budget incl GST</strong></div>
    <div class=\"col-sm-2\" style=\" text-align:right;\"><strong>Actual Cost to Date</strong></div>";

    while($row2 = $result2->fetch_assoc()) {
      $opexitemcost = $row2["opexitemcost"];
      $thiscategoryid = $row2["opexcategoryid"];

      echo 
      "<div class=\"row\" style=\"margin:3px 0 0 0; border-top:solid 1px #ccc;\">
      <div class=\"col-sm-3\" style=\"margin:6px 0 6px 0;\"><a href=\"/editopexitem.php?opexitemid=" . $row2["idopexitems"] . "\">" . $row2["opexitemname"]. "</a></div>
      <div class=\"col-sm-3\" style=\"margin:6px 0 6px 0;\">" . $row2["opexcategoryname"] . "</div>
      <div class=\"col-sm-2\" style=\"margin:6px 0 6px 0; text-align:right;\">$" . number_format($opexitemcost,2) . "</div>
      <div class=\"col-sm-2\" style=\"margin:6px 0 6px 0; text-align:right;\">$" . number_format($opexitemcost * 1.15,2) . "</div>"; // Annual Budget incl GST

      // Display the actual cost of each OPEX item
      $sql6 = "SELECT * FROM opexitems_category_totals_view WHERE opexid = $QPopexid and transactioncategoryid = $thiscategoryid";
      $result6 = $con->query($sql6);
      if ($result6->num_rows > 0) {
        while($row6 = $result6->fetch_assoc()) {
          $opexinvoicecategorycost = $row6["opexinvoicecategorycost"];
          $opexinvoicecategorytotal = $row6["opexinvoicecategorytotal"];
          echo "<div class=\"col-sm-2\" style=\"margin:6px 0 6px 0; text-align:right;\">$" . number_format($opexinvoicecategorycost,2) . "</div>";
        }
      } else {
        $opexinvoicecategorycost = 0;
        echo "<div class=\"col-sm-2\" style=\"margin:6px 0 6px 0; text-align:right;\">-</div>";
      }
      /*
      $underover = $opexitemcost - $opexinvoicecategorycost;
      $totaloverunder = $totaloverunder + $underover;

      echo "<div class=\"col-sm-2\" style=\"margin:6px 0 6px 0; text-align:right;\">$" . number_format($underover,2) . "</div>
      */
      echo "</div>";

    }



// Display the allocated opex items
if ($result8->num_rows > 0) {
  while($row8 = $result8->fetch_assoc()) {
    $opexitemcost = $row8["opexitemcost"];
    $thiscategoryid = $row8["opexcategoryid"];
    $thisopexitemid = $row8["idopexitems"];

    echo 
    "<div class=\"row\" style=\"margin:3px 0 0 0; border-top:solid 1px #ccc;\">
      <div class=\"col-sm-3\" style=\"margin:6px 0 6px 0;\"><a href=\"/editopexitem.php?opexitemid=" . $row8["idopexitems"] . "\">" . $row8["opexitemname"]. "</a></div>
      <div class=\"col-sm-3\" style=\"margin:6px 0 6px 0;\">" . $row8["opexcategoryname"] . "</div>
      <div class=\"col-sm-2\" style=\"margin:6px 0 6px 0; text-align:right;\">$" . number_format($opexitemcost,2) . "</div>
      <div class=\"col-sm-2\" style=\"margin:6px 0 6px 0; text-align:right;\">$" . number_format($opexitemcost * 1.15,2) . "</div>";

    // Display the actual cost of each OPEX item
    $sql6 = "SELECT * FROM opexitems_category_totals_view WHERE opexid = $QPopexid and transactioncategoryid = $thiscategoryid";
    $result6 = $con->query($sql6);
    if ($result6->num_rows > 0) {
      while($row6 = $result6->fetch_assoc()) {
        $opexinvoicecategorycost = $row6["opexinvoicecategorycost"];
        $opexinvoicecategorytotal = $row6["opexinvoicecategorytotal"];
        echo "<div class=\"col-sm-2\" style=\"margin:6px 0 6px 0; text-align:right;\">$" . number_format($opexinvoicecategorycost,2) . "</div>";
      }
    } else {
      $opexinvoicecategorycost = 0;
      echo "<div class=\"col-sm-2\" style=\"margin:6px 0 6px 0; text-align:right;\">-</div>";
    }

    $sql9 = "SELECT * FROM opexitemallocation_view WHERE opexitemid = $thisopexitemid";
    $result9 = $con->query($sql9);
    if ($result9->num_rows > 0) {
      while($row9 = $result9->fetch_assoc()) {
        $allocatedcost = $row9["allocatedcost"];
        echo
        "<div class=\"row\" style=\"margin:3px 0 0 0; border-top:solid 0px #ccc;\">
          <div class=\"col-sm-3\" style=\"margin:6px 0 6px 0; margin-left:20px;\">&nbsp;</div>
          <div class=\"col-sm-2\" style=\"margin:6px 0 6px 0;\">" . $row9["unitname"] . " - " . $row9["allocationpercentage"] . "%</div>
          <div class=\"col-sm-3\" style=\"margin:6px 0 6px 0; text-align:right;\">$" . number_format($allocatedcost,2) . "</div>
          <div class=\"col-sm-2\" style=\"margin:6px 0 6px 0; text-align:right;\">$" . number_format($allocatedcost * 1.15,2) . "</div>
        </div>";
      }
    }
    echo "</div>";
  }
}




echo "</div>";

//Display the totals
echo "<div class=\"row\" style=\"margin:3px 0 10px 0; border-top:solid 1px #ccc;\">
  <div class=\"col-sm-3\" style=\"margin:3px 0 6px 0;\"><strong>TOTAL</strong></div>
  <div class=\"col-sm-3\" style=\"margin:6px 0 6px 0;\">&nbsp;</div>
  <div class=\"col-sm-2\" style=\"margin:6px 0 6px 0; text-align:right; font-weight:bold;\">$".$annualopexcost."</div>
  <div class=\"col-sm-2\" style=\"margin:6px 0 6px 0; text-align:right; font-weight:bold;\">$".$annualopexcostplusgst."</div>
  <div class=\"col-sm-2\" style=\"margin:6px 0 6px 0; text-align:right; font-weight:bold;\">$".$opexinvoicecostsum."</div>";
//  <div class=\"col-sm-2\" style=\"margin:6px 0 6px 0; text-align:right; font-weight:bold;\">$".number_format($totaloverunder,2)."</div>

echo "</div>

</div>";

echo
"<div class=\"row\">
        <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px;\"><a href=\"addopexitem.php?opexid=" . $QPopexid . "\" class=\"btn btn-primary\">Add OPEX Item</a></div>
        <div class=\"col-sm-2\" style=\"padding-top:20px; padding-bottom:20px;\"><a href=\"listtransactions.php?type=2&opex=" . $QPopexid . "\" class=\"btn btn-primary\">View Opex Bills</a></div>
</div>";

echo 
"<div class=\"row\">
    <div class=\"col-sm-3\"><h3 style=\"padding:15px 0 15px 0;\">OPEX Bills</h3></div>
    <div class=\"col-sm-5\" style=\"padding:20px 0 0 0; text-align:right;\">
        <input class=\"form-control\" id=\"myInput\" type=\"text\" placeholder=\"Search Invoice Number, Supplier name or Category\">
    </div>
    <div class=\"col-sm-2\" style=\"padding:20px; text-align:right;\"><a href=\"addopexinvoice.php?opexid=" . $QPopexid . "\" class=\"btn btn-primary\">Add OPEX Bill</a></div>
    <div class=\"col-sm-2\" style=\"padding:20px; text-align:right;\"><a href=\"importopexinvoices.php?opexid=" . $QPopexid . "\" class=\"btn btn-primary\">Import Invoices</a></div>
</div>

<div class=\"container-fluid\" style=\"margin:3px 0 0 0;\">";

?>
<table class="table">
  <thead>
    <tr>
    <th><?php if ($QPorder == 'transactiondate') {
        echo "<a href=\"?opexid=$QPopexid&order=transactiondate DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?opexid=$QPopexid&order=transactiondate\" style=\"text-decoration:none; color:inherit;\">";
      }?>Date</a></th>
      <th><?php if ($QPorder == 'invoicenumber') {
        echo "<a href=\"?opexid=$QPopexid&order=invoicenumber DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?opexid=$QPopexid&order=invoicenumber\" style=\"text-decoration:none; color:inherit;\">";
      }?>Invoice Number</a></th>
      <th><?php if ($QPorder == 'companyname, transactiondate DESC') {
        echo "<a href=\"?opexid=$QPopexid&order=companyname DESC, transactiondate DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?opexid=$QPopexid&order=companyname, transactiondate DESC\" style=\"text-decoration:none; color:inherit;\">";
      }?>Supplier</a></th>
      <th style="text-align:left;"><?php if ($QPorder == 'opexcategoryname, transactiondate DESC') {
        echo "<a href=\"?opexid=$QPopexid&order=opexcategoryname DESC, transactiondate DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?opexid=$QPopexid&order=opexcategoryname, transactiondate DESC\" style=\"text-decoration:none; color:inherit;\">";
      }?>Category</a></th>
      <th style="text-align:right; padding-right:60px;"><?php if ($QPorder == 'invoicecost') {
        echo "<a href=\"?opexid=$QPopexid&order=invoicecost DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?opexid=$QPopexid&order=invoicecost\" style=\"text-decoration:none; color:inherit;\">";
      }?>
      Cost</a></th>
      <th style="text-align:right; padding-right:60px;"><?php if ($QPorder == 'invoicetotal') {
        echo "<a href=\"?opexid=$QPopexid&order=invoicetotal DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?opexid=$QPopexid&order=invoicetotal\" style=\"text-decoration:none; color:inherit;\">";
      }?>Total (incl. GST)</a></th>
      <th style="text-align:center;"><?php if ($QPorder == 'invoicestatusid') {
        echo "<a href=\"?opexid=$QPopexid&order=invoicestatusid DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?opexid=$QPopexid&order=invoicestatusid\" style=\"text-decoration:none; color:inherit;\">";
      }?>Status</a></th>
      <th style="text-align:center;"><?php if ($QPorder == 'transactiondatepaid DESC, transactiondate DESC') {
        echo "<a href=\"?opexid=$QPopexid&order=transactiondatepaid, transactiondate DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?opexid=$QPopexid&order=transactiondatepaid DESC, transactiondate DESC\" style=\"text-decoration:none; color:inherit;\">";
      }?>Date Paid</a></th>
      <th style="text-align:center;"><?php if ($QPorder == 'unitname, transactiondate DESC') {
        echo "<a href=\"?opexid=$QPopexid&order=unitname DESC, transactiondate DESC\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?opexid=$QPopexid&order=unitname, transactiondate DESC\" style=\"text-decoration:none; color:inherit;\">";
      }?>Premises</a></th>
    </tr>
  </thead>
  <tbody id="myTable">

<?php
while($row3 = $result3->fetch_assoc()) {
    //if(empty($row3["invoicedatepaid"])){
    //    $invoicestatus = "Due";
    //  } else {
    //    $invoicestatus = "Paid";
    //  }
    $invoicestatus = $row3['invoicestatus'];
    echo "<tr>
      <td>" . date_format(date_create($row3["transactiondate"]),"j F Y") . "</td>
      <td><a href=\"viewtransaction.php?id=" . $row3["idtransaction"] . "&opex=" . $QPopexid . "\">" . $row3["invoicenumber"] . "</a></td>
      <td>" . $row3["companyname"] . "</td>
      <td style=\"text-align:left;\">" . $row3["transactioncategoryname"] . "</td>
      <td style=\"text-align:right; padding-right:60px;\">$" . number_format($row3["transactionamount"],2) . "</td>
      <td style=\"text-align:right; padding-right:60px;\">$" . number_format($row3["transactiontotal"],2) . "</td>";
      switch ($row3["invoicestatusid"]) {
        case '7': //ERROR
        case '8':
        case '9':
        case '10':
          echo "<td style=\"text-align:center;\"><a href=payopexinvoice.php?invoiceid=". $row3["idtransaction"] . " style=\"text-decoration:none; color:red;\">".$row3["invoicestatus"]."</a></td>";
          break;
        case '4': //Paid
        case '6': //Processing
          echo "<td style=\"text-align:center;\">".$row3["invoicestatus"]."</td>";
          break;
        case '2': //Active
          echo "<td style=\"text-align:center;\"><a href=payopexinvoice.php?invoiceid=". $row3["idtransaction"] . ">Unpaid</a></td>";
          break;
      }
      echo "<td style=\"text-align:center;\">" . $row3["invoicepaiddate"] . "</td>";
      if (is_null($row3["premisesid"])) {
        echo "<td style=\"text-align:center;\"><td>";
      } elseif ($row3["premisesid"] == 0) {
        echo "<td style=\"text-align:center;\">common</td>";
      } else {
        echo "<td style=\"text-align:center;\">" .$row3["unitname"] . "</td>";
      }
    "
    <td>".$row3["invoicestatus"]."</td>
    
    </tr>";
}

echo "</tbody></table>";

// Display pagination links
$first_displayed_record_number = $offset + 1;
if ($total_results < $offset + $results_per_page) {
    $last_displayed_record_number = $total_results;
} else {
    $last_displayed_record_number = $offset + $results_per_page;
}
echo
"<div class=\"row\">
  <div class=\"col-sm-2\" style=\"padding-top:5px;\">";
  if ($last_displayed_record_number > 0) {
    echo $first_displayed_record_number." to ".$last_displayed_record_number." of ";
  }
  echo $total_results." items</div>
  <div class=\"col-sm-3 pagination\">";
  for ($x = 1; $x <= $total_pages; $x++) {
    if ($page == $x) {
      echo '<a class="active">' . $x . '</a>';
    } else {
      echo '<a href="?page=' . ($x) . '&opexid=' . $QPopexid . '&order=' . $QPorder .'">' . ($x) . '</a>';
    }
  }
  echo "</div>
</div>";

        $con->close();
?>

<script>
$(document).ready(function(){
  $("#myInput").on("keyup", function() {
	var value = $(this).val().toLowerCase();
	$("#myTable tr").filter(function() {
  	$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
	});
  });
});
</script>

</div>

<?=template_footer()?>