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

$searchstring = "";
$searchstringErr = "";
$type="";
$opex = 0;

$sqlopex = "SELECT * FROM opex_view WHERE  recordOwnerID IN ($accessto) ORDER BY buildingname, opexdate DESC";
$resultopex = $con->query($sqlopex);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["searchstring"])) {
    $searchstring = "";
  } else {
    $searchstring = test_input($_POST["searchstring"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' .-\/]*$/", $searchstring)) {
        $searchstringErr = "Prohibited characters used in search sting";
        $searchstring = "";
    }
  }
  if (!empty($_POST["opex"])) {
    $opex = $_POST["opex"];
  }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
if (!empty($QueryParameters['order'])) {
  $QPorder = $QueryParameters['order'];
} else {
  $QPorder = "transactiondate DESC, companyname";
}
if (!empty($QueryParameters['searchstring'])) {
  $searchstring = $QueryParameters['searchstring'];
}
if (!empty($QueryParameters['type'])) {
  $type = $QueryParameters['type'];
}
if (!empty($QueryParameters['opex'])) {
  $opex = $QueryParameters['opex'];
}

switch ($type) {
  case 1:
    $headingtext = "Invoices";
    break;
  case 2:
    $headingtext = "OPEX Bills";
    break;
  default:
    $headingtext = "Invoices";
    $type = 1;
}
template_header($headingtext);

?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2><?php echo $headingtext;?></h2>
	</div>
</div>

<div class="block">

<div class="row">
  <div class="col-sm-5">
    <?php if ($type == 2) { ?>
    <form class="form form-medium" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?order='.$QPorder.'&searchstring='.$searchstring.'&type='.$type);?>" style="display:flex;">
      <div class="form-group">
        <label class="form-label" for="month">OPEX:</label>
        <div class="col-sm-7">
          <select class="form-control" name="opex" id="opex">
          <?php 
          if ($opex == 0) {
            echo "<option value =\"0\" selected>Select an Opex option</option>";
          }
          while ($rowopex = $resultopex->fetch_assoc()) {
            if ($rowopex["opexid"] == $opex) {
              echo "<option value=\"" . $rowopex["opexid"] . "\" selected>". $rowopex["buildingname"] . " (" . date_format(date_create($rowopex["opexdate"]),"j F Y") . ")</option>";
            } else {
              echo "<option value=\"" . $rowopex["opexid"] . "\">". $rowopex["buildingname"] . " (" . date_format(date_create($rowopex["opexdate"]),"j F Y") .")</option>";
            }
          }
          ?> 
          </select>
        </div>
        <div class="col-sm-3">
          <input type="submit" value="Refresh" class="btn btn-primary">
        </div>
      </div>
    </form>
    <?php } ?>
  </div>

  <div class="col-sm-2" style="text-align:right; padding-top:5px;"><span class="error"><span class="text-danger"><?php echo $searchstringErr;?></span></div>
    <div class="col-sm-5" style="text-align:right;">
      <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"].'?order='.$QPorder.'&type='.$type.'&opex='.$opex);?>" style="display:flex;">
        <input class="form-control" id="searchstring" type="text" name="searchstring" value="<?php echo $searchstring;?>" placeholder="Search in Invoice Number, Date, Company, Ref, Unit, Amount or Total" style="border-radius:3px 0px 0px 3px;">
        <button class="btn btn-primary" style="border-radius:0px;" type="submit">GO</button>
      </form>
    </div>
  </div>


<script>
$(document).ready(function(){
  $("#myInput").on("keyup", function() {
	var value = $(this).val().toLowerCase();
	$("#myTable tr").filter(function() {
  	$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
	});
  });
});

  function previewInvoice(url) {
    var myWindow = window.open(url,"","width=900,height=1000,top=30,left=300");
  }
</script>


<table class="table table-striped">
  <thead>
    <tr>
      <th><?php if ($QPorder == 'invoicenumber') {
        echo "<a href=\"?order=invoicenumber DESC&searchstring=".$searchstring."&type=".$type."&opex=".$opex."\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=invoicenumber&searchstring=".$searchstring."&type=".$type."&opex=".$opex."\" style=\"text-decoration:none; color:inherit;\">";
      }?>Invoice Number</a></th>
      <th><?php if ($QPorder == 'transactiondate') {
        echo "<a href=\"?order=transactiondate DESC&searchstring=".$searchstring."&type=".$type."&opex=".$opex."\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=transactiondate&searchstring=".$searchstring."&type=".$type."&opex=".$opex."\" style=\"text-decoration:none; color:inherit;\">";
      }?>Date</a></th>
      <th><?php if ($QPorder == 'companyname') {
        echo "<a href=\"?order=companyname DESC&searchstring=".$searchstring."&type=".$type."&opex=".$opex."\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=companyname&searchstring=".$searchstring."&type=".$type."&opex=".$opex."\" style=\"text-decoration:none; color:inherit;\">";
      }?>Company</a></th>
      <th><?php if ($QPorder == 'transactioncategoryid') {
        echo "<a href=\"?order=transactioncategoryid DESC&searchstring=".$searchstring."&type=".$type."&opex=".$opex."\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=transactioncategoryid&searchstring=".$searchstring."&type=".$type."&opex=".$opex."\" style=\"text-decoration:none; color:inherit;\">";
      }?>Category</a></th>
      <th><?php if ($QPorder == 'unitname') {
        echo "<a href=\"?order=unitname DESC&searchstring=".$searchstring."&type=".$type."&opex=".$opex."\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=unitname&searchstring=".$searchstring."&type=".$type."&opex=".$opex."\" style=\"text-decoration:none; color:inherit;\">";
      }?>Unit</a></th>
      <th style="text-align:center;"><?php if ($QPorder == 'buildingid') {
        echo "<a href=\"?order=buildingid DESC&searchstring=".$searchstring."&type=".$type."&opex=".$opex."\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=buildingid&searchstring=".$searchstring."&type=".$type."&opex=".$opex."\" style=\"text-decoration:none; color:inherit;\">";
      }?>Building</a></th>
      <th><?php if ($QPorder == 'transactionamount') {
        echo "<a href=\"?order=transactionamount DESC&searchstring=".$searchstring."&type=".$type."&opex=".$opex."\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=transactionamount&searchstring=".$searchstring."&type=".$type."&opex=".$opex."\" style=\"text-decoration:none; color:inherit;\">";
      }?>Amount</a></th>
      <th>Total</th>
      <th>Due Date</th>
      <th style="text-align:center;"><?php if ($QPorder == 'invoicestatusid, transactiondate DESC') {
        echo "<a href=\"?order=invoicestatusid DESC, transactiondate DESC&searchstring=".$searchstring."&type=".$type."&opex=".$opex."\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=invoicestatusid, transactiondate DESC&searchstring=".$searchstring."&type=".$type."&opex=".$opex."\" style=\"text-decoration:none; color:inherit;\">";
      }?>Status</a></th>
      <th style="text-align:center;"><?php if ($QPorder == 'invoicepaiddate') {
        echo "<a href=\"?order=invoicepaiddate DESC&searchstring=".$searchstring."&type=".$type."&opex=".$opex."\" style=\"text-decoration:none; color:inherit;\">";
      } else {
        echo "<a href=\"?order=invoicepaiddate&searchstring=".$searchstring."&type=".$type."&opex=".$opex."\" style=\"text-decoration:none; color:inherit;\">";
      }?>Date Paid</a></th>
      
      <th></th>
    </tr>
  </thead>
  <d id="myTable">

  <?php


// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$results_per_page = 20; // Number of results per page
$offset = ($page - 1) * $results_per_page;
// Get total number of records
$total_query = "SELECT COUNT(*) AS total FROM transactions_view WHERE recordownerid IN ($accessto) AND 
transactiontypeid = $type AND ";
if ($type == 2) {
  $total_query = $total_query."opexid = $opex AND ";
}
$total_query = $total_query."(invoicenumber LIKE '%$searchstring%' OR
transactiondate LIKE '%$searchstring%' OR
invoicepaiddate LIKE '%$searchstring%' OR
companyname LIKE '%$searchstring%' OR
transactioncategoryname LIKE '%$searchstring%' OR
unitname LIKE '%$searchstring%' OR
transactionamount LIKE '%$searchstring%' OR
transactiontotal LIKE '%$searchstring%')";
$resultCount = $con->query($total_query);
$rowCount = $resultCount->fetch_assoc();
$total_results = $rowCount['total'];
// Calculate total number of pages
$total_pages = ceil($total_results / $results_per_page);

$sql = "SELECT * FROM transactions_view WHERE recordownerid IN ($accessto) AND 
  transactiontypeid = $type AND ";
if ($type == 2) {
  $sql = $sql."opexid = $opex AND ";
}
$sql = $sql."(invoicenumber LIKE '%$searchstring%' OR
transactiondate LIKE '%$searchstring%' OR
invoicepaiddate LIKE '%$searchstring%' OR
companyname LIKE '%$searchstring%' OR
transactioncategoryname LIKE '%$searchstring%' OR
unitname LIKE '%$searchstring%' OR
transactionamount LIKE '%$searchstring%' OR
transactiontotal LIKE '%$searchstring%')
ORDER BY $QPorder LIMIT $results_per_page OFFSET $offset";
$result = $con->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    $invoiceid = $row["idtransaction"];
    $newdate = new DateTime('now', new DateTimeZone('Pacific/Auckland'));
    $dateNow = date_format($newdate,"Y-m-d");
    $invoiceduedate = date_format(date_create($row["invoiceduedate"]),"Y-m-d");

    echo "<tr>
      <td><a href=\"viewtransaction.php?id=" . $row["idtransaction"] . "&opex=" . $opex . "&searchstring=" . $searchstring . "\">" . $row["invoicenumber"] . "</a></td>
      <td>" . date_format(date_create($row["transactiondate"]),"j F Y"). "</td>
      <td><a href=\"viewcompany.php?companyid=" . $row["transactioncompanyid"] . "\">" . $row["companyname"] . "</a></td>
      <td>" . $row["transactioncategoryname"] . "</td>
      <td>" . $row["unitname"] . "</td>
      <td style=\"text-align:center;\">" . $row["buildingname"] . "</td>
      <td>$" . $row["transactionamount"] . "</td>
      <td>$" . $row["transactiontotal"] . "</td>";
      if ($invoiceduedate < $dateNow and $row["invoicestatusid"] <> 4) {
        echo "<td style=\"color:red;\">" . date_format(date_create($row["invoiceduedate"]),"j F Y") . "</td>";
      } else {
        echo "<td>" . date_format(date_create($row["invoiceduedate"]),"j F Y") . "</td>";
      }
      

      switch ($row["invoicestatusid"]) {
        case '1': //Draft
          echo "<td style=\"text-align:center;\">
          <a href=\"#\" onclick=\"previewInvoice('previewinvoice2.php?invoiceid=".$row["idtransaction"]."'); return false; \" class=\"text-decoration-none\">".$row["invoicestatus"]."</a></td>
          <td></td>";
          break;
        case '2': //Active
          if ($row["transactiontypeid"] == '2') {
            echo "<td style=\"text-align:center;\"><a href=payopexinvoice.php?invoiceid=". $row["idtransaction"] . ">Pay Now</a></td>
            <td></td>";
          } else {
            echo "<td style=\"text-align:center;\">
              <a href=\"#\" onclick=\"previewInvoice('previewinvoice2.php?invoiceid=".$row["idtransaction"]."'); return false; \" class=\"text-decoration-none\">".$row["invoicestatus"]."</a></td>";
            if ($invoiceduedate < $dateNow) {
              echo "<td style=\"color:red; text-align:center;\">Overdue</td>";
            } else {
              echo "<td></td>";
            }
          }
          break;
        case '3': //Sent
          echo "<td style=\"text-align:center;\">";
          if ($row["transactiontypeid"] == 1) {
            echo "<a href=\"#\" onclick=\"previewInvoice('previewinvoice2.php?invoiceid=".$row["idtransaction"]."'); return false; \" class=\"text-decoration-none\">".$row["invoicestatus"]."</a></td>";
          }
          echo "</td>";
          if ($invoiceduedate < $dateNow) {
              echo "<td style=\"color:red; text-align:center;\">Overdue</td>
              <td></td>";
            } else {
              echo "<td></td>
              <td></td>";
            }
          break;
        case '4': //Paid
          echo "<td style=\"text-align:center;\">
          <a href=\"#\" onclick=\"previewInvoice('previewinvoice2.php?invoiceid=".$row["idtransaction"]."'); return false; \" class=\"text-decoration-none\">".$row["invoicestatus"]."</a></td>
          <td style=\"text-align:center;\">".$row["invoicepaiddate"]."</td>
          <td></td>";
          break;
        case '5': //Void
          echo "<td style=\"text-align:center;\">".$row["invoicestatus"]."</td>
          <td></td>
          <td></td>";
          break;
        case '6': //Processing
          echo "<td style=\"text-align:center;\">".$row["invoicestatus"]."</td>
          <td></td>";
          break;
        case '7': //ERROR
        case '8':
        case '9':
        case '10':
          echo "<td style=\"text-align:center;\"><a href=payopexinvoice.php?invoiceid=". $row["idtransaction"] . " style=\"text-decoration:none; color:red;\">".$row["invoicestatus"]."</a></td>
          <td></td>";
          break;
        case '11': //Reconciled
          echo "<td style=\"text-align:center;\">
          <a href=\"#\" onclick=\"previewInvoice('previewinvoice2.php?invoiceid=".$row["idtransaction"]."'); return false; \" class=\"text-decoration-none\">".$row["invoicestatus"]."</a></td>
          <td></td>";
          break;
        
        
        
      }

    if ($row["invoicestatusid"] < 3 and $row["transactiontypeid"] == 1) {
      $tenantid = $row["transactioncompanyid"];
      $duedate = date_format(date_create($row["invoiceduedate"]),"d/m/Y");

      $sqlpm = "SELECT * FROM leases_view where leasestatusid = 2 and tenantid = $tenantid ORDER BY commencement desc LIMIT 1";
      $resultpm = $con->query($sqlpm);
      while($rowpm = $resultpm->fetch_assoc()) {
        $propertymanagerlogo = $rowpm["logoimagefilename"];
        $propertymanager = $rowpm["propertymanagercompany"];
      }

      $sqlcompany = "SELECT * FROM companies where idcompany = $tenantid";
      $resultcompany = $con->query($sqlcompany);
      while($rowcompany = $resultcompany->fetch_assoc()) {
        $recipientid = $rowcompany["primaryContactID"];
      }

      $sqltoken = "SELECT * FROM public_invoice_links where transactionID = $invoiceid";
      $resulttoken = $con->query($sqltoken);
      while($rowtoken = $resulttoken->fetch_assoc()) {
        $token = $rowtoken["token"];
      }

      echo "<td>";
      ?>
      <form method="post" action="sendinvoiceemail.php">
        <input hidden id="subject" type="text" name="subject" value="Invoice <?php echo $row["invoicenumber"]?>">
        <input hidden id="recipientid" type="text" name="recipientid" value="<?php echo $recipientid?>">
        <input hidden id="fromheading" type="text" name="fromheading" value="Invoice <?php echo $row["invoicenumber"]?>">
        <input hidden id="invoicenumber" type="text" name="invoicenumber" value="<?php echo $row["invoicenumber"]?>">
        <input hidden id="propertymanagerlogo" type="text" name="propertymanagerlogo" value="<?php echo $propertymanagerlogo?>">
        <input hidden id="invoicedescription" type="text" name="invoicedescription" value="<?php echo $row["transactioncategoryname"]?>">
        <input hidden id="invoicetotal" type="text" name="invoicetotal" value="<?php echo $row["transactiontotal"]?>">
        <input hidden id="token" type="text" name="token" value="<?php echo $token?>">
        <input hidden id="duedate" type="text" name="duedate" value="<?php echo $duedate?>">
        <input hidden id="propertymanager" type="text" name="propertymanager" value="<?php echo $propertymanager?>">
        <input hidden id="invoiceid" type="text" name="invoiceid" value="<?php echo $invoiceid?>">
        <input hidden id="order" type="text" name="order" value="<?php echo $QPorder?>">
        <input hidden id="transactiontype" type="text" name="transactiontype" value="<?php echo $type?>">
        <input hidden id="searchstring" type="text" name="searchstring" value="<?php echo $searchstring?>">

        <!--<button type="submit" value="Send" class="btn btn-primary">Email</button>-->
        <input type="submit" value="Send" style="all: unset; cursor: pointer; color:rgb(13, 110, 253)">
      </form>
      <?php
    } else {

    }
      
  echo "</tr>";
  }
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
//version 2

for ($x = 1; $x <= $total_pages; $x++) {
  if ($page == $x) {
    echo '<a class="active">' . $x . '</a>';
  } else {
    echo '<a href="?page=' . ($x) . '&order=' . $QPorder . '&searchstring=' . $searchstring . '&type=' . $type . '&opex=' . $opex . '">' . ($x) . '</a>';
  }

}

echo "</div>

</div>
<div class=\"row\">";
if ($type == 2) {
  echo "<div class=\"col-sm-9\" style=\"padding-top:20px;\"><a href=\"addopexinvoice.php?type=2&searchstring=" . $searchstring . "\" class=\"btn btn-primary\">Add Opex Bill</a></div>";
} else {
  echo "<div class=\"col-sm-9\" style=\"padding-top:20px;\"><a href=\"addtransaction.php?type=1\" class=\"btn btn-primary\">Add Invoice</a></div>";
}

echo "</div>";

  $con->close();
?>


<?=template_footer()?>