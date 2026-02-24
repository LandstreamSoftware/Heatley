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

$searchperiod = 0;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (!empty($_POST["searchperiod"])) {
    $searchperiod = $_POST["searchperiod"];
  }
}

$nowD = date('d');
$nowM = date('m');
$nowY = date('Y');

if ($searchperiod == 12) { // This financial year
    if ($nowM < 4) {
        $y = $nowY - 1;
    } else {
        $y = $nowY;
    }
    $m = 4;
    $endDate = $nowY."-".$nowM."-".$nowD;
    $end = date_create($endDate);
    date_add($end,date_interval_create_from_date_string("1 month"));
} elseif ($searchperiod == 1) { // Last month
    if ($nowM  == 1) {
        $m = 12;
        $y = $nowY - 1;
    } else {
        $m = $nowM - 1;
        $y = $nowY;
    }
    $endDate = $nowY."-".$nowM."-1";
    $end = date_create($endDate);
    date_sub($end,date_interval_create_from_date_string("1 day"));
} elseif ($searchperiod == 2) { // Two months ago
    if ($nowM  == 2) {
        $m = 12;
        $y = $nowY - 1;
    } elseif ($nowM == 1) {
        $m = 11;
        $y = $nowY - 1;
    } else {
        $m = $nowM - 2;
        $y = $nowY;
    }
    $endDate = $nowY."-".$nowM."-1";
    $end = date_create($endDate);
    date_sub($end,date_interval_create_from_date_string("1 month"));
} else { //$searchperiod = 0 (this month)
    $m = $nowM;
    $y = $nowY;
    $endDate = $nowY."-".$nowM."-".$nowD;
    $end = date_create($endDate);
    date_add($end,date_interval_create_from_date_string("1 month"));
}



$sql1 = "SELECT * FROM accounts WHERE id = $accountid";
$result1 = $con->query($sql1);
if ($result1->num_rows > 0) {
    while($row1 = $result1->fetch_assoc()) {
       $companyid = $row1["companyID"]; 
    }
}


$myNewToken = check_xero_token_expiry($con, $companyid);

if (isset($myNewToken)) {
    $accesstoken = $myNewToken;
}

$config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken($accesstoken);

// Initialize Identity API
$identityApi = new XeroAPI\XeroPHP\Api\IdentityApi(
    new GuzzleHttp\Client(),
    $config // Your standard configuration with the access token
);

// Get all connections
$connections = $identityApi->getConnections();

foreach ($connections as $connection) {
    $xeroTenantId = $connection->getTenantId();
    $xeroTenantName = $connection->getTenantName();
}





$apiInstance = new XeroAPI\XeroPHP\Api\AccountingApi(
    new GuzzleHttp\Client(),
    $config
);

use XeroAPI\XeroPHP\Models\Accounting\Invoice;

$d = 1;
$fromDate = sprintf('%04d,%02d,%02d', $y, $m, $d);
$toDate = date_format($end, "Y,m,d");


echo "From: ".$fromDate." to: ".$toDate;



use XeroAPI\XeroPHP\Api\AccountingApi;

function xeroDateTimeLiteral(DateTimeInterface $d): string {
    // Xero where-clause DateTime(Y,M,D)
    return sprintf('DateTime(%d,%d,%d)', (int)$d->format('Y'), (int)$d->format('n'), (int)$d->format('j'));
}

/**
 * Fetch bank transactions for ALL bank accounts within a date range.
 *
 * @return array keyed by AccountID => ['account' => Account, 'transactions' => BankTransaction[]]
 */
function getBankTransactionsByBankAccountAndDateRange(
    AccountingApi $api,
    string $xeroTenantId,
    $fromDate,
    $toDate,
    ?string $status = null,          // e.g. "AUTHORISED" or null
    int $pageSize = 100,
    int $unitdp = 2
): array {
    // 1) Get BANK accounts
    $accountsWhere = 'Type=="BANK"'; // optionally add: && Status=="ACTIVE"
    $accounts = $api->getAccounts($xeroTenantId, null, $accountsWhere, 'Name ASC')->getAccounts();

    $results = [];

    foreach ($accounts as $account) {
        $accountId = $account->getAccountID();   // GUID string
        $accountName = $account->getName();

echo $accountName."<br>";

    //    $fromLit = xeroDateTimeLiteral($fromDate);
    //    $toLit   = xeroDateTimeLiteral($toDate);
    $fromLit = $fromDate;
    $toLit = $toDate;

        // 2) Build where for this bank account + date range
        // Note: guid("...") usage is the common Xero where-clause pattern for IDs. :contentReference[oaicite:2]{index=2}
        $whereParts = [
            "Date >= $fromLit",
            "Date <= $toLit",
            "BankAccount.AccountID == guid(\"$accountId\")",
        ];
        if ($status) {
            $whereParts[] = "Status == \"$status\"";
        }
        $where = implode(' AND ', $whereParts);

        // 3) Page through bank transactions for this account
        $page = 1;
        $allTx = [];

        do {
            $resp = $api->getBankTransactions(
                $xeroTenantId,
                null,               // ifModifiedSince
                $where,
                'Date ASC',         // order
                null,               // iDs
                $status,            // status (string or null)
                $page,
                $unitdp,
                $pageSize
            );

            $tx = $resp->getBankTransactions() ?? [];
            $count = count($tx);

            if ($count > 0) {
                $allTx = array_merge($allTx, $tx);
            }

            $page++;
        } while ($count === $pageSize);

        $results[$accountId] = [
            'account' => $account,          // includes Name/Code/BankAccountNumber etc.
            'accountName' => $accountName,
            'transactions' => $allTx,
        ];
    }

    return $results;
}