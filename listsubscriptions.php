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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["searchstring"])) {
    $searchstring = "";
  } else {
    $searchstring = test_input($_POST["searchstring"]);
    //check if the field only contains letters dash or white space
    if (!preg_match("/^[a-zA-Z-0-9āēīōūĀĒĪŌŪ' .-\/]*$/", $searchstring)) {
        $searchstringErr = "Prohibited characters used in search sting";
        $searchstring = "";
    } //else {
    //  $searchstring = "%".$searchstring."%";
    //}
  }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

?>

<?=template_header('List Subscription Plans')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Subscription Plans</h2>
	</div>
</div>

<div class="block">


<div class="row">

    <!-- Centered row with gap between panels -->
    <div class="row justify-content-center g-4">
      <!-- Starter -->
      <div class="col-12 col-md-6 col-lg-3">
        <div class="plan-card border border-2 border-primary rounded bg-white d-flex flex-column">

          <div class="current-plan-label text-grey text-center py-2 rounded-top">
            CURRENT PLAN
          </div>

          <div class="plan-header text-white" style="background:#6e43d1;">
            <div class="p-1 text-center">
              <div class="fw-medium fs-2">Starter</div>
            </div>

            <div class="billing-strip text-center">billed annually</div>

            <div class="price-wrap">
              <div class="price">$10</div>
              <div class="per-year">/ month</div>
            </div>
          </div>

          <div class="p-4 flex-grow-1 d-flex flex-column">
            <ul class="benefits list-unstyled mb-4">
              <li class="d-flex gap-2 align-items-start">
                <span class="tick">✓</span><span>Single tenancy</span>
              </li>
              <li class="d-flex gap-2 align-items-start">
                <span class="tick">✓</span><span>OPEX Buget</span>
              </li>
              <li class="d-flex gap-2 align-items-start">
                <span class="tick">✓</span><span>Auto generated lease renewals</span>
              </li>
              <li class="d-flex gap-2 align-items-start">
                <span class="tick">✓</span><span>Compliance tasks</span>
              </li>
            </ul>

            <form action="subscribe.php" method="post" class="mt-auto">
              <input type="hidden" name="plan" value="starter">
              <button type="submit" class="btn btn-primary w-100">Choose Starter</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Growing -->
      <div class="col-12 col-md-6 col-lg-3">
        <div class="plan-card border rounded bg-white d-flex flex-column">

          <div class="upgrade-plan-label text-success text-center py-2 rounded-top">
            UPGRADE TO
          </div>

          <div class="plan-header text-white" style="background:#0d6efd;">
            <div class="p-1 text-center">
              <div class="fw-medium fs-2">Growing</div>
            </div>

            <div class="billing-strip text-center">billed annually</div>

            <div class="price-wrap">
              <div class="price">$30</div>
              <div class="per-year">/ month</div>
            </div>
          </div>

          <div class="p-4 flex-grow-1 d-flex flex-column">
            <ul class="benefits list-unstyled mb-4">
              <li class="d-flex gap-2 align-items-start">
                <span class="tick">✓</span><span>Everything in Starter</span>
              </li>
              <li class="d-flex gap-2 align-items-start">
                <span class="tick">✓</span><span>Up to 9 tenancies</span>
              </li>
              <li class="d-flex gap-2 align-items-start">
                <span class="tick">✓</span><span>Option to integrate with Xero</span>
              </li>
            </ul>

            <form action="subscribe.php" method="post" class="mt-auto">
              <input type="hidden" name="plan" value="growing">
              <button type="submit" class="btn btn-primary w-100">Choose Growing</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Pro -->
      <div class="col-12 col-md-6 col-lg-3">
        <div class="plan-card border rounded bg-white d-flex flex-column">

          <div class="upgrade-plan-label text-success text-center py-2 rounded-top">
            UPGRADE TO
          </div>

          <div class="plan-header text-white" style="background:#198754;">
            <div class="p-1 text-center">
              <div class="fw-medium fs-2">Pro</div>
            </div>

            <div class="billing-strip text-center">billed annually</div>

            <div class="price-wrap">
              <div class="price">$80</div>
              <div class="per-year">/ month</div>
            </div>
          </div>

          <div class="p-4 flex-grow-1 d-flex flex-column">
            <ul class="benefits list-unstyled mb-4">
              <li class="d-flex gap-2 align-items-start">
                <span class="tick">✓</span><span>Everything in Growing</span>
              </li>
              <li class="d-flex gap-2 align-items-start">
                <span class="tick">✓</span><span>Unlimited tenancies</span>
              </li>
              <li class="d-flex gap-2 align-items-start">
                <span class="tick">✓</span><span>Personalised custom reports (extra fees apply to build reports)</span>
              </li>
            </ul>

            <form action="subscribe.php" method="post" class="mt-auto">
              <input type="hidden" name="plan" value="pro">
              <button type="submit" class="btn btn-primary w-100">Choose Pro</button>
            </form>
          </div>
        </div>
      </div>
    </div>


</div>


<?php
        $con->close();
?>


<?=template_footer()?>