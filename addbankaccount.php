<?php
include 'main.php';

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
<?=template_header('Add Bank Account')?>

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
        <div>
            <h3 style="padding:15px 0 15px 0;">How it works</h3>
        </div>
        <p>Lease Manager uses <a href="https://akahu.nz">Akahu</a> to provide data integrations with NZ banks.</p>
        <p>Akahu's OAuth2 authorization code flow allows Lease Manager to request authorization from users (you) to access your data. 
            You can grant access to transactions, balances and account data.</p> 
        <p>There are several authentication steps you will need to go through starting with providing an email address to recieve a 6 digit code.</br>
        This should be either the email address you use to log into Lease Manager, or one that you have access to.</p>
        <p>To begin the process click on the button to take you to akahu.nz where you will log in to your bank and authorize Lease Manager to access your account. Use this process to add or remove individual bank accounts.</p>
        <div class="col-sm-2" style="padding-top:20px;">   
            <a  class="btn btn-primary" href="#" target="_blank" onclick="akahuAuthenticate('https://oauth.akahu.nz/?client_id=app_token_cm47p8pnb000108jlg57m73wc&response_type=code&redirect_uri=http%3A%2F%2Fleasemanager.co.nz%2Fakahu_redirect.php&scope=ENDURING_CONSENT'); return false; " class="text-decoration-none" >https://oauth.akahu.nz</a>
        </div>
    </div>
</div>

<div class="block">
    <div class="row">
        <div>
            <h3 style="padding:15px 0 15px 0;">Next step</h3>
        </div>
    <p>Once you have connected (or removed) a bank account, return to the updated bank account list by clicking on this button.</p>
        <div class="col-sm-2" style="padding-top:20px;"><a href="update_bank_accounts.php" class="btn btn-primary">Refresh the Bank Account list</a></div>
    </div>
</div>

<div class="block">
    <div class="row">
        <div>
            <h3 style="padding:15px 0 15px 0;">Revoking access</h3>
        </div>
        <p>If you no longer want Lease Manager to access your financial data you can revoke access to all accounts. To revoke access to all your accounts go to <a href="#" target="_blank" onclick="akahuAuthenticate('https://my.akahu.nz'); return false; " class="text-decoration-none" >https://my.akahu.nz</a></p>
    </div>
</div>
<?php
    $con->close();
?>
<div class="row">

<?=template_footer()?>