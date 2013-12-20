<?php

//  Setup API routes using Epiphany.
ini_set("include_path", ".:../:../epiphany/src/");

include 'Epi.php';

Epi::setSetting('exceptions', true);

Epi::init('route');
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

//  The in-memory storage of our data until the database backend is implemented.
$tableApps = array();
$tableLogins = array();
$tableMessages = array();
$tableStatistics = array();

//  Constants used in the entire module.
$APP_NAME_PARAM = 'appName';
$UNIQUE_ID_PREFIX = 'friday-lab-20131220';
$STATUS_RETURN_PARAM = 'status';
$TABLE_APPS_ID_COLUMN = 'id';
$TABLE_APPS_NAME_COLUMN = 'name';
$TABLE_APPS_SECRET_COLUMN = 'secret';

//  Copied from http://www.php.net/parse_url
function convertUrlQuery($query) {
    $queryParts = explode('&', $query);

    $params = array();
    foreach ($queryParts as $param) {
        $item = explode('=', $param);
        $params[$item[0]] = $item[1];
    }

    return $params;
}

function reportFailure($error) {
    echo json_encode(array(
        $STATUS_RETURN_PARAM => 'fail',
        'error' => $error));
}

function reportSuccess(array $result = NULL) {
    //  Make the result array if it wasn't passed.
    if(!$result) {
        $result = array();
    }

    //  Always add the status success to the result array.
    $result[$STATUS_RETURN_PARAM] = 'success';

    echo json_encode($result);
}

//  The functions implementing API.
function heartbeat() {
    $now = new DateTime();

    //  Return the format and the current server time.
    $data = array(
        'format' => 'json',
        'time' => $now->format('Y-m-d H:i:s.mmm'));

    reportSuccess($data);
}

function registerApp() {
    $appName = $_GET[$APP_NAME_PARAM];
    if(!$appName) {
        reportError('{$APP_NAME_PARAM} parameter is not optional.');
        return;
    }

    //  If the app is already register just return its data.
    $appData = $tableApps[$appName];
    if($appData) {
        reportSuccess($appData);
        return;
    }

    //  Generate unique app ID and its secret.
    $appId = uniqid($UNIQUE_ID_PREFIX, true);
    $secret = uniqid($UNIQUE_ID_PREFIX, true);

    //  Store the app data.
    $tableApps[$appName] = array(
        $TABLE_APPS_ID_COLUMN => $appId,
        $TABLE_APPS_NAME_COLUMN => $appName,
        $TABLE_APPS_SECRET_COLUMN => $secret);

    //  Return the app data to the caller.
    $data = array(
        $TABLE_APPS_ID_COLUMN => $appId,
        $TABLE_APPS_NAME_COLUMN => $appName,
        $TABLE_APPS_SECRET_COLUMN => $secret);
    echo json_encode($data);
}

function login() {
    $data = array(
        'token' => 'token',
        'expiresAt' => 'expiresAt');
    echo json_encode($data);
}

function send() {
    $data = array(
        'messageId' => 'messageId',
        'status' => 'OK');
    echo json_encode($data);
}

function getStatus() {
    $data = array(
        'messageId' => 'messageId',
        'status' => 'status');
    echo json_encode($data);
}

function getStatistics() {
    $data = array(
        'loginCount' => 'loginCount',
        'sendCount' => 'sendCount',
        'getStatusCount' => 'getStatus',
        'getStatisticsCount' => 'getStatisticsCount',
        'totalCount' => 'totalCount');
    echo json_encode($data);
}

function logout() {
    reportSuccess;
}

function unregisterApp() {
    reportSuccess;
}

?>
