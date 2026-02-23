<?php
/**
 * authentication.php
 *
 * Xero OAuth2 flow + token persistence to MySQL table `xero_oauth_tokens`.
 *
 * Requires:
 *   composer require league/oauth2-client guzzlehttp/guzzle xeroapi/xero-php-oauth2
 *
 * Notes:
 * - access_token / refresh_token columns are VARBINARY(2048). We store token strings directly.
 * - token_key_version is left at default 1 unless you override.
 */


declare(strict_types=1);

ini_set('display_errors', 'On');
error_reporting(E_ALL);

require '../vendor/autoload.php';

use GuzzleHttp\Client as GuzzleClient;
use League\OAuth2\Client\Provider\GenericProvider;

//session_start();
include '../main.php';

$Q = explode("/", $_SERVER['QUERY_STRING']);
parse_str($Q[0],$QueryParameters);
$companyid = $QueryParameters['cid'];


/* ---------------------------- CONFIG ---------------------------- */
$sql = "SELECT * FROM xero_oauth_tokens WHERE companyID = $companyid";

$result = $con->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      $clientid = $row["client_id"];
      $clientsecret = $row["client_secret"];
      $redirecturi  = $row["redirect_uri"];
      $scopes = $row["scopes"];
      
    } 
}

/* -------------------------- OAUTH CLIENT ------------------------- */


$provider = new GenericProvider([
    'clientId'                => $clientid,
    'clientSecret'            => $clientsecret,
    'redirectUri'             => $redirecturi,
    'urlAuthorize'            => 'https://login.xero.com/identity/connect/authorize',
    'urlAccessToken'          => 'https://identity.xero.com/connect/token',
    'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation',
]);


/* ---------------------------- HELPERS ---------------------------- */


function pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;


    $dsn  = $GLOBALS['db _host'];
    $user = $GLOBALS['db_user'];
    $pass = $GLOBALS['db_pass'];


    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // If you are on MySQL < 8 and hit auth plugin issues, adjust as needed.
    ]);


    return $pdo;
}
 

/**
 * Fetch Xero connections to get tenantId(s).
 * Returns array of connections from GET https://api.xero.com/connections
 */
function fetchXeroConnections(string $accessToken): array
{
    $http = new GuzzleClient(['timeout' => 20]);


    $resp = $http->request('GET', 'https://api.xero.com/connections', [
        'headers' => [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept'        => 'application/json',
        ],
    ]);


    $json = json_decode((string)$resp->getBody(), true);
    return is_array($json) ? $json : [];
}


/**
 * Upsert token record by company_id.
 * If a row exists for company_id, update it and increment token_version.
 * Otherwise insert a new row with token_version = 0.
 */
function upsertTokenByTenant(array $row): void
{
    $pdo = pdo();


    // Find existing record
    $stmt = $pdo->prepare('SELECT id, token_version FROM xero_oauth_tokens WHERE companyID = :company_id LIMIT 1');
    $stmt->execute([':company_id' => $companyid]);
    $existing = $stmt->fetch();


    if ($existing) {
        $newVersion = ((int)$existing['token_version']) + 1;


        $sql = '
            UPDATE xero_oauth_tokens
               SET access_token = :access_token,
                   refresh_token = :refresh_token,
                   access_token_expires_at = :access_token_expires_at,
                   scopes = :scopes,
                   client_id = :client_id,
                   client_secret = :client_secret,
                   redirect_uri = :redirect_uri,
                   recordOwnerID = :recordOwnerID,
                   token_version = :token_version
             WHERE id = :id
        ';
        $upd = $pdo->prepare($sql);
        $upd->execute([
            ':id'                     => (int)$existing['id'],
            ':access_token'           => $row['access_token'],
            ':refresh_token'          => $row['refresh_token'],
            ':access_token_expires_at'=> $row['access_token_expires_at'],
            ':scopes'                 => $row['scopes'],
            ':client_id'              => $row['client_id'],
            ':client_secret'          => $row['client_secret'],
            ':token_version'          => $newVersion,
        ]);
    } else {
        $sql = '
            INSERT INTO xero_oauth_tokens
                (companyID, tenant_id, access_token, refresh_token, token_key_version,
                 access_token_expires_at, scopes, token_version, client_id, client_secret,
                 recordOwnerID, redirect_uri)
            VALUES
                (:companyID, :tenant_id, :access_token, :refresh_token, :token_key_version,
                 :access_token_expires_at, :scopes, :token_version, :client_id, :client_secret,
                 :recordOwnerID, :redirect_uri)
        ';
        $ins = $pdo->prepare($sql);
        $ins->execute([
            ':companyID'               => $row['companyID'],
            ':tenant_id'               => $row['tenant_id'],
            ':access_token'            => $row['access_token'],
            ':refresh_token'           => $row['refresh_token'],
            ':token_key_version'       => $row['token_key_version'] ?? 1,
            ':access_token_expires_at' => $row['access_token_expires_at'],
            ':scopes'                  => $row['scopes'],
            ':token_version'           => 0,
            ':client_id'               => $row['client_id'],
            ':client_secret'           => $row['client_secret'],
            ':recordOwnerID'           => $row['recordOwnerID'],
            ':redirect_uri'            => $row['redirect_uri'],
        ]);
    }
}


/* --------------------------- FLOW START --------------------------- */


try {
    // Step 1: Redirect user to Xero if no auth code
    if (!isset($_GET['code'])) {

        $authorizationUrl = $provider->getAuthorizationUrl([
            'scope' => $scopes,
        ]);

$logFile = XERO_LOG_FILE_PATH;
$logMessage = "authorizationUrl:  \n $authorizationUrl \n \n";
file_put_contents($logFile, $logMessage, FILE_APPEND);        

        $_SESSION['oauth2state'] = $provider->getState();

        header('Location: ' . $authorizationUrl);
        exit;
    }


    // Step 2: Validate state to protect against CSRF
    if (
        empty($_GET['state']) ||
        empty($_SESSION['oauth2state']) ||
        !hash_equals($_SESSION['oauth2state'], (string)$_GET['state'])
    ) {
        unset($_SESSION['oauth2state']);
        throw new RuntimeException('Invalid OAuth state. Please try again.');
    }

    // Step 3: Exchange code for token set
    $token = $provider->getAccessToken('authorization_code', [
        'code' => (string)$_GET['code'],
    ]);

    $accessToken  = $token->getToken();
    $refreshToken = $token->getRefreshToken();


    if (!$refreshToken) {
        // This typically means offline_access was not granted
        throw new RuntimeException('No refresh token returned. Ensure "offline_access" is included in scopes and consent was granted.');
    }


    // Step 4: Discover tenant_id via connections endpoint
    $connections = fetchXeroConnections($accessToken);
    if (!$connections) {
        throw new RuntimeException('No Xero connections returned. User may not have completed org connection.');
    }

    // Step 5: Persist token(s) per tenant
    $expiresAt = (new DateTimeImmutable())->setTimestamp($token->getExpires());
    $expiresAtSql = $expiresAt->format('Y-m-d H:i:s');


    $scopesString = implode(' ', $scopes);


    foreach ($connections as $conn) {
        if (empty($conn['tenantId'])) continue;


        upsertTokenByTenant([
            'companyID'               => $companyID,
            'tenant_id'               => (string)$conn['tenantId'],
            'access_token'            => $accessToken,
            'refresh_token'           => $refreshToken,
            'token_key_version'       => 1,
            'access_token_expires_at' => $expiresAtSql,
            'scopes'                  => $scopesString,
            'client_id'               => $clientId,
            'client_secret'           => $clientSecret,
            'recordOwnerID'           => $recordOwnerID,
            'redirect_uri'            => $redirectUri,
        ]);
    }

    // You can redirect to your app page here
    header('Content-Type: text/plain; charset=utf-8');
    echo "Success.\n";
    echo "Saved token set for " . count($connections) . " tenant connection(s).\n";
    exit;


} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "OAuth error: " . $e->getMessage() . "\n";
    exit;
}