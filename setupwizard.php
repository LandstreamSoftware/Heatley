<?php
// Include the main.php file
include 'main.php';
// Check if the user is logged in, if not then redirect to login page
check_loggedin($con);

$accountid = $_SESSION['account_id'];

$sqlAccess = "SELECT * FROM accesscontrol WHERE accountID = $accountid";
$resultAccess = $con->query($sqlAccess);

$accessto = -1;

if ($resultAccess->num_rows > 0) {
    while($rowAccess = $resultAccess->fetch_assoc()) {
       $accessto .= "," . $rowAccess["companyID"]; 
    }
}

// Upcoming actions in the next 30 days
$thirtyday_tasks = $con->query('SELECT * FROM dashboard_upcoming_view WHERE recordOwnerID IN ('.$accessto.')')->fetch_all(MYSQLI_ASSOC);
//$thirtyday_tasks = $con->query('SELECT * FROM buildings')->fetch_all(MYSQLI_ASSOC);//
// OPEX Budget tasks
$opex_tasks = $con->query('SELECT * FROM dashboard_opexbudget_view WHERE recordOwnerID IN ('.$accessto.')')->fetch_all(MYSQLI_ASSOC);
// Expiring Leases
$expiring_tasks = $con->query('SELECT * FROM dashboard_expiries_view WHERE recordOwnerID IN ('.$accessto.')')->fetch_all(MYSQLI_ASSOC);


?>
<?=template_header('Setup Wizard')?>

<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Setup Wizard</h2>
		<p><?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?></p>
	</div>
</div>


<div class="block">

	<p>Follow the steps below to setup your Lease Manager data.</p>
    <p>Your login is authorised to only see records that are created by you, or another user in your business, which keeps it private from other users.</p>
    <p>You'll start by creating a new Owner Company, or assigning your own company as the owner of a building. Once the building has an owner, everything else that you create
        (Premises, Leases, OPEX Budgets and Tenant Invoices) will automatically have the same owner, so will only be visible to you and your collegues.
    </p>

</div>


<div class="dashboard">
    <div class="content-block stat green">
        <div class="data">
            <h3>Step 1</h3>
            <h3>Create a Property Owner company if your own business is not the owner of the buildings.</h3>
        </div>
        <div class="setup-footer">
            <p class="pad-1"></p>Set the Company Type to Property Owner.</br>
            If your company is the owner of the buildings then make sure your own Company has the type Propery Owner.
        </div>
        <div class="setup-footer" style="justify-content:flex-end;">
            <a href="addcompany.php" class="btn btn-primary" style="margin-right:10px;">Add Company</a>
        </div>
    </div>
</div>
<div class="dashboard">
    <div class="content-block stat green">
        <div class="data">
            <h3>Step 2</h3>
            <h3>Create a Contact for the Property Owner</h3>
        </div>
        <div class="setup-footer">
            <p class="pad-1"></p>Set the Company to the one created in Step 1.
        </div>
        <div class="setup-footer" style="justify-content:flex-end;">
            <a href="addcontact.php" class="btn btn-primary" style="margin-right:10px;">Add Contact</a>
        </div>
    </div>
</div>
<div class="dashboard">
    <div class="content-block stat green">
        <div class="data">
            <h3>Step 3</h3>
            <h3>Go back and set the Primary Contact for the Company</h3>
        </div>
        <div class="setup-footer">
           <p class="pad-1"></p>Select the Contact created in Step 2 as the primary contact for the owner company.
        </div>
        <div class="setup-footer" style="justify-content:flex-end;">
            <a href="listcompanies.php" class="btn btn-primary" style="margin-right:10px;">Edit Company</a>
        </div>
    </div>
</div>
<div class="dashboard">
    <div class="content-block stat green">
        <div class="data">
            <h3>Step 4</h3>
            <h3>Create a Building</h3>
        </div>
        <div class="setup-footer">
            <p class="pad-1"></p>Create a Building that will contain 1 or more Premises to be leased. You will see that the Property Owner company is available to choose as the owner of the building.
        </div>
        <div class="setup-footer" style="justify-content:flex-end;">
            <a href="addbuilding.php" class="btn btn-primary" style="margin-right:10px;">Add Building</a>
        </div>
    </div>
</div>
<div class="dashboard">
    <div class="content-block stat green">
        <div class="data">
            <h3>Step 5</h3>
            <h3>Create Premises</h3>
        </div>
        <div class="setup-footer">
            <p class="pad-1"></p>Create Premises for each lease you want to manage. Later on you will create an OPEX budget that can be apportioned to each of the premises in the building.
        </div>
        <div class="setup-footer" style="justify-content:flex-end;">
            <a href="addpremises.php" class="btn btn-primary" style="margin-right:10px;">Add Premises</a>
        </div>
    </div>
</div>
<div class="dashboard">
    <div class="content-block stat green">
        <div class="data">
            <h3>Step 6</h3>
            <h3>Create an OPEX Budget</h3>
        </div>
        <div class="setup-footer">
            <p class="pad-1"></p>The OPEX Budget will contain OPEX Items and be associated with a Building.
        </div>
        <div class="setup-footer" style="justify-content:flex-end;">
            <a href="addopex.php" class="btn btn-primary" style="margin-right:10px;">Add OPEX</a>
        </div>
    </div>
</div>
<div class="dashboard">
    <div class="content-block stat green">
        <div class="data">
            <h3>Step 7</h3>
            <h3>Add OPEX Items</h3>
        </div>
        <div class="setup-footer">
            <p class="pad-1"></p>The OPEX Items make up the OPEX Budget for the year. It's a good idea to name your OPEX budget with the Year it relates to since it is likely you will have a new one each year.
        </div>
        <div class="setup-footer" style="justify-content:flex-end;">
            <a href="listopex.php" class="btn btn-primary" style="margin-right:10px;">Add OPEX Item</a>
        </div>
    </div>
</div>
<div class="dashboard">
    <div class="content-block stat green">
        <div class="data">
            <h3>Step 8</h3>
            <h3>Add a Tenant company and contact</h3>
        </div>
        <div class="setup-footer">
            <p class="pad-1"></p>Create the Company first and set the Company Type to Tenant. Then create a contact for the tenant and add them as the primary contact on the Tenant record.
        </div>
        <div class="setup-footer" style="justify-content:flex-end;">
            <a href="addcompany.php" class="btn btn-primary" style="margin-right:10px;">Add Tenant Company</a>
        </div>
    </div>
</div>
<div class="dashboard">
    <div class="content-block stat green">
        <div class="data">
            <h3>Step 9</h3>
            <h3>Add a Lease</h3>
        </div>
        <div class="setup-footer">
            <p class="pad-1"></p>You will need the Tenant company and Premises you created in Step 5 and Step 8 in order to create a Lease.
        </div>
        <div class="setup-footer" style="justify-content:flex-end;">
            <a href="addlease.php" class="btn btn-primary" style="margin-right:10px;">Add Lease</a>
        </div>
    </div>
</div>
<div class="dashboard">
    <div class="content-block stat green">
        <div class="data">
            <h3>Step 10</h3>
            <h3>Add Renewal records</h3>
        </div>
        <div class="setup-footer">
            <p class="pad-1"></p>Starting with Rent at Commencement, add any Rent Reviews and Right of Renewals contained in the lease. Only set the current Renewal record to active as this determines the rent you expect to receive.
        </div>
        <div class="setup-footer" style="justify-content:flex-end;">
            <a href="addrenewal.php" class="btn btn-primary" style="margin-right:10px;">Add Renewal</a>
        </div>
    </div>
</div>
<div class="dashboard">
    <div class="content-block stat green">
        <div class="data">
            <h3>Congratulations you are all set up!</h3>
        </div>
        <div class="setup-footer">
            <p class="pad-1"></p>Feel free to&nbsp;<a href="contact-us.php">contact us</a>&nbsp;if you need any further assitance.
        </div>
        <div class="setup-footer" style="justify-content:flex-end;">
            <a href="addrenewal.php" class="btn btn-primary" style="margin-right:10px;">Add Renewal</a>
        </div>
    </div>
</div>



<?=template_footer()?>