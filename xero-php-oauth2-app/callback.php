<?php
ini_set('display_errors', 'On');
require '../vendor/autoload.php';
include '../main.php';
require_once('../xero-php-oauth2-app/storage.php');


// Storage Classe uses sessions for storing token > extend to your DB of choice
$storage = new StorageClass();

$accountid = $_SESSION['account_id'];

$sqlAccess = "SELECT * FROM accounts WHERE id = $accountid";
$resultAccess = $con->query($sqlAccess);

if ($resultAccess->num_rows > 0) {
    while($rowAccess = $resultAccess->fetch_assoc()) {
       $companyid = $rowAccess["companyID"]; 
    }
}

$sql = "SELECT * FROM xero_oauth_tokens WHERE companyID = $companyid";
$result = $con->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      $clientid = $row["client_id"];
      $clientsecret = $row["client_secret"];
      $redirecturi  = $row["redirect_uri"];
      $scopes = $row["scopes"];
      $tokenversion = $row["token_version"];
      $refreshtoken = (string)$row['refresh_token'];
    } 
}

$provider = new \League\OAuth2\Client\Provider\GenericProvider([
  'clientId'                => $clientid,
  'clientSecret'            => $clientsecret,
  'redirectUri'             => $redirecturi,
  'urlAuthorize'            => 'https://login.xero.com/identity/connect/authorize',
  'urlAccessToken'          => 'https://identity.xero.com/connect/token',
  'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation'
]);


use League\OAuth2\Client\Provider\GenericProvider;

/**
 * Refresh-token rotation logic:
 * Given a stored refresh token, request a new access token. Xero rotates refresh tokens,
 * so you MUST replace the stored refresh_token with the newly returned refresh_token.
 */

function rotate_refresh_token(GenericProvider $provider, string $refreshToken): \League\OAuth2\Client\Token\AccessTokenInterface {
  return $provider->getAccessToken('refresh_token', [
    'refresh_token' => $refreshToken,
  ]);
}

// Server-side refresh endpoint
if (!isset($_GET['code']) && isset($_GET['refresh_tenant_id'])) {

  $logFile = XERO_LOG_FILE_PATH;
  $logMessage = "callback.php \n  no code, refresh_tenant_id is set";
  file_put_contents($logFile, $logMessage, FILE_APPEND); 

  try {
    refresh_and_persist_for_tenant($provider, (string)$_GET['refresh_tenant_id'], $refreshtoken);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Refreshed and persisted tokens for tenant_id=" . (string)$_GET['refresh_tenant_id'];
    $newaccesstoken = (string)$_GET['token'];
    return $newaccesstoken;
  } catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Refresh failed: " . $e->getMessage();
    exit;
  }
}

// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {
  //echo "Something went wrong, no authorization code found";
  //exit("Something went wrong, no authorization code found");
  header('Location: ../xero-edit_authentication_credentials.php?cid='.$companyid);
  exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
  echo "Invalid State";
  unset($_SESSION['oauth2state']);
  exit('Invalid state');
} else {

$logFile = XERO_LOG_FILE_PATH;
$logMessage = "code exists \n";
file_put_contents($logFile, $logMessage, FILE_APPEND); 

try {
  // Try to get an access token using the authorization code grant.
  $accessToken = $provider->getAccessToken('authorization_code', [
    'code' => $_GET['code']
  ]);


$theToken = $accessToken->getToken();
$theRefreshToken = $accessToken->getRefreshToken();
$logMessage = "token aquired: $theToken \n refresh token: $theRefreshToken \n\n";
file_put_contents($logFile, $logMessage, FILE_APPEND); 

// Save my tokens, expiration tenant_id
      
$storage->setToken(
    $accessToken->getToken(),
    $accessToken->getExpires(),
//    $result[0]->getTenantId(),
    $accessToken->getRefreshToken(),
    $accessToken->getValues()["id_token"]
);


//      $config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken((string)$accessToken->getToken());

//      $identityInstance = new XeroAPI\XeroPHP\Api\IdentityApi(
 //       new GuzzleHttp\Client(),
//        $config
//      );

//      $result = $identityInstance->getConnections();




        $storage = new StorageClass();
        $config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken( (string)$storage->getSession()['token'] );
        $identityApi = new XeroAPI\XeroPHP\Api\IdentityApi(
            new GuzzleHttp\Client(),
            $config // Your standard configuration with the access token
        );

        // Get all connections
        $result = $identityApi->getConnections();

        foreach ($result as $connection) {
            $tenantid =  $connection->getTenantId();
        }

$logMessage = "tenantID: $tenantid \n";
file_put_contents($logFile, $logMessage, FILE_APPEND);


      // Save my tokens, expiration tenant_id
      
      $storage->setToken(
          $accessToken->getToken(),
          $accessToken->getExpires(),
      //    $result[0]->getTenantId(),
          $accessToken->getRefreshToken(),
          $accessToken->getValues()["id_token"]
      );
    
      $accesstoken = $accessToken->getToken();
      $refreshtoken = $accessToken->getRefreshToken();
      $accesstokenexpiresat = date('Y-m-d H:i:s', $accessToken->getExpires());
      $tokenversion += 1;
      

      // Update the xero-ouath-tokens record
      $stmt = $con->prepare("UPDATE xero_oauth_tokens SET access_token = ?, refresh_token = ?, access_token_expires_at = ?, token_version = ?, tenant_id = ? WHERE companyID = ?");
      $stmt->bind_param("sssiis",$accesstoken, $refreshtoken, $accesstokenexpiresat, $tokenversion, $tenantid, $companyid);
      $stmt->execute();
      $stmt->close();

      header('Location: ' . '../profile.php?at='.$accessToken);
      exit();

    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
      echo "Callback failed";
      exit();
    }
  }
?>