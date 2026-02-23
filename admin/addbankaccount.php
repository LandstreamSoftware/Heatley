<?php
include 'main.php';



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

while($rowuser = $resultuser->fetch_assoc()) {
    $mycompanyid = $rowuser["companyID"]; 
}

?>
<?=template_admin_header('Bank Accounts', 'bank_accounts')?>

<script>
    function akahuAuthenticate(url) {
    var myWindow = window.open(url,"","width=500,height=1000,top=30,left=300");
  }
</script>


<div class="page-title">
	<div class="icon">
		<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
	</div>	
	<div class="wrap">
		<h2>Add Bank Account</h2>
	</div>
</div>

<div class="block">

    <div class="row">
        <p>To begin the process click on the link below to take you to akahu.nz where you will log in to your bank and authorise Lease Manager to access your account.</p>
    </div>

    <div class="row">
        <div class="col-sm-2">
            <a href="#" target="_blank" onclick="akahuAuthenticate('https://oauth.akahu.nz/?client_id=app_token_cm47p8pnb000108jlg57m73wc&response_type=code&redirect_uri=http%3A%2F%2Fleasemanager.co.nz%2Fakahu_redirect.php&scope=ENDURING_CONSENT'); return false; " class="text-decoration-none" >https://oauth.akahu.nz</a>
        </div>
    </div>
       

 

    <div class="row">

    <?php
    $con->close();
    ?>

</div>

<?=template_footer()?>