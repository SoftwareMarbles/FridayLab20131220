<?php

//  Setup the include path before trying to include any other source files.
ini_set("include_path", ".:../:../epiphany/src/");

include 'Epi.php';
include 'Database.php';

//  Constants used in the entire module.
define('APP_NAME_PARAM', 'appName');
define('APP_ID_PARAM', 'appId');
define('APP_SECRET_PARAM', 'appSecret');
define('LOGIN_TOKEN_PARAM', 'token');

define('STATUS_RETURN_PARAM', 'status');

Epi::setSetting('exceptions', true);
Epi::init('route', 'database');

Database::setupDatabase();

//  Heartbeat API doesn't receive any parameters.
getRoute()->get('/', 'heartbeat');
//  Register API receives the name of the app to be registered.
getRoute()->post('/registerApp', 'registerApp');
getRoute()->post('/login', 'login');
getRoute()->post('/send', 'send');
getRoute()->get('/getStatus', 'getStatus');
getRoute()->get('/getStatistics', 'getStatistics');
getRoute()->post('/logout', 'logout');
getRoute()->post('/unregisterApp', 'unregisterApp');
getRoute()->run();

function reportFailure($error) {
    echo json_encode(array(
        STATUS_RETURN_PARAM => 'fail',
        'error' => $error));
}

function reportSuccess(array $result = NULL) {
    //  Make the result array if it wasn't passed.
    if(!$result) {
        $result = array();
    }

    //  Always add the status success to the result array.
    $result[STATUS_RETURN_PARAM] = 'success';

    echo json_encode($result);
}

//  The functions implementing API.
function heartbeat() {
    $now = new DateTime();

    //  Return the format and the current server time.
    $data = array(
        'format' => 'json',
        'time' => $now->format('Y-m-d H:i:s.u'));

    reportSuccess($data);
}

//  Returns the given parameter's value or reports failure if the parameter is not available and not optional.
function getParam($param, $optional = FALSE) {
    $value = $_GET[$param];
    if($value) {
        return $value;
    }

    if(!$optional) {
        reportFailure(sprintf('%s parameter is not optional.', $param));
    }

    return NULL;
}

function getAppNameParam() {
    return getParam(APP_NAME_PARAM);
}

function getAppIdParam() {
    return getParam(APP_ID_PARAM);
}

function getAppSecretParam() {
    return getParam(APP_SECRET_PARAM);
}

function getTokenParam() {
    return getParam(LOGIN_TOKEN_PARAM);
}

function tokenIsValid($token) {
    $loginData = Database::queryLoginsPerToken($token);

    //  A token is valid if it exist in the database, it hasn't expired just yet (its expiresAt is less than the current time)
    //  and its state is loggedIn.
    return $loginData
        && $loginData['expiresAt'] < new DateTime()
        && $loginData['state'] == DatabaseLoginState::LoggedIn;
}

function registerApp() {
    $appName =getAppNameParam();
    if(!$appName) {
        return;
    }

    //  If the app is already register just return its data.
    $appData = Database::queryAppsPerName($appName);
    if($appData) {
        reportSuccess($appData);
        return;
    }

    //  Generate unique app ID and its secret.
    $appId = uniqid('id.', true);
    $secret = uniqid('secret.', true);

    //  Add the new app to the database and return its data.
    $data = Database::addApp($appName, $appId, $secret);
    if(!data) {
        reportFailure('Couldn\'t add app data.');
        return;
    }

    reportSuccess($data);
}

function login() {
    //  We need both app's ID and secret to correctly login.
    $appId = getAppIdParam();
    if(!$appId) {
        return;
    }
    $appSecret = getAppSecretParam();
    if(!$appSecret) {
        return;
    }

    //  We need to check that the app has been registered and that its
    //  secret and the given secret match.
    $appData = Database::queryAppsPerId($appId);
    if(!$appData) {
        reportFailure('Incorrect app ID.');
        return;
    }
    if($appData['secret'] != $appSecret) {
        //  We report the bad secret error openly. There are other API calls where a possible attacker
        //  could check the validity of the app ID in his or her posession so it makes no sense to obfuscate
        //  the message here.
        reportFailure('Incorrect app secret.');
        return;
    }

    //  Generate unique token.
    $token = uniqid('token.', true);
    //  Tokens for now all expire in a day.
    $expiresAt = new DateTime();
    $expiresAt->add(DateInterval::createFromDateString('1 day'));

    //  Add the token to the database and return its data.
    $loginData = Database::addLogin($token, $appId, $expiresAt, DatabaseLoginState::LoggedIn);
    if(!loginData) {
        reportFailure('Couldn\'t add login data.');
        return;
    }

    reportSuccess($loginData);
}

function send() {
    $token = getTokenParam();
    if(!$token) {
        return;
    }

    if(!tokenIsValid($token)) {
        reportFailure('Token has expired.');
        return;
    }

    //  Get the JSON payload from the request wrapper (see http://php.net/manual/en/wrappers.php.php)
    $rawPayload = file_get_contents('php://input');
    if(!$rawPayload) {
        reportFailure('Payload is missing.');
        return;
    }
    $payload = json_decode($rawPayload);
    if(!$payload) {
        reportFailure('Payload is not correctly formed JSON.');
        return;
    }
    if(!isset($payload['type'])
        || !isset($payload['recepient'])
        || !isset($payload['message'])) {
        reportFailure('Payload lacks non-optional properties.');
        return;
    }

    $data = array(
        'messageId' => 'messageId');
    reportSuccess($data);
}

function getStatus() {
    $data = array(
        'messageId' => 'messageId',
        'messageStatus' => 'status');
    reportSuccess($data);
}

function getStatistics() {
    $appName = getAppNameParam();
    if(!$appName) {
        return;
    }

    $stats = Database::queryStatsPerAppName($appName);

    reportSuccess($stats);
}

function logout() {
    $token = getTokenParam();
    if(!$token) {
        return;
    }

    //  This cannot logically fail as we don't care if the client tries to log out an inexisting login.
    Database::updateLoginState($token, DatabaseLoginState::LoggedOut);

    reportSuccess();
}

function unregisterApp() {
    reportSuccess();
}

?>
