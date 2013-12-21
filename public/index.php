<?php

//  Constants used in the entire module.
define('APP_NAME_PARAM', 'appName');

define('UNIQUE_ID_PREFIX', 'friday-lab-20131220');
define('STATUS_RETURN_PARAM', 'status');
define('TABLE_APPS_ID_COLUMN', 'id');
define('TABLE_APPS_NAME_COLUMN', 'name');
define('TABLE_APPS_SECRET_COLUMN', 'secret');

//  Now that all the data and constants have been defined/declared, setup API routes using Epiphany.
ini_set("include_path", ".:../:../epiphany/src/");

include 'Epi.php';

Epi::setSetting('exceptions', true);
Epi::init('route', 'database');

EpiDatabase::employ(
    'mysql',
    'mysql',
    'localhost',
    'root',
    'moot');

setupDatabase();

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
        'time' => $now->format('Y-m-d H:i:s.mmm'));

    reportSuccess($data);
}

function registerApp() {
    $appName = $_GET[APP_NAME_PARAM];
    if(!$appName) {
        reportFailure(sprintf('%s parameter is not optional.', APP_NAME_PARAM));
        return;
    }

    //  If the app is already register just return its data.
    $appData = $tableApps[$appName];
    if($appData) {
        $appData['requestId'] = uniqid(UNIQUE_ID_PREFIX, true);
        reportSuccess($appData);
        return;
    }

    //  Generate unique app ID and its secret.
    $appId = uniqid(UNIQUE_ID_PREFIX, true);
    $secret = uniqid(UNIQUE_ID_PREFIX, true);

    //  Store the app data.
    $tableApps[$appName] = array(
        TABLE_APPS_ID_COLUMN => $appId,
        TABLE_APPS_NAME_COLUMN => $appName,
        TABLE_APPS_SECRET_COLUMN => $secret);

    //  Return the app data to the caller.
    $data = array(
        TABLE_APPS_ID_COLUMN => $appId,
        TABLE_APPS_NAME_COLUMN => $appName,
        TABLE_APPS_SECRET_COLUMN => $secret);
    reportSuccess($data);
}

function login() {
    $data = array(
        'token' => 'token',
        'expiresAt' => 'expiresAt');
    reportSuccess($data);
}

function send() {
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
    $appName = $_GET[APP_NAME_PARAM];
    if(!$appName) {
        reportFailure(sprintf('%s parameter is not optional.', APP_NAME_PARAM));
        return;
    }

    $stats = getDatabase()->one(
        'SELECT COUNT(*) AS messageCount FROM messages WHERE appId = (SELECT id FROM apps WHERE appName = :appName)',
        array(':appName', $appName));

    reportSuccess($stats);
}

function logout() {
    reportSuccess();
}

function unregisterApp() {
    reportSuccess();
}

function setupDatabase() {
    $createStatements = array(
'CREATE DATABASE fridayLab20131220'
,
'
CREATE TABLE apps(
    id varchar(100),
    appName varchar(100),
    secret varchar(100)
)
',
'
CREATE TABLE messages(
    id varchar(100),
    appId varchar(100),
    status varchar(10)
)
'
);

    //  Execute all the create statements.
    for ($i=0; $i < count($createStatements); $i++) {
        getDatabase()->execute($createStatements[$i]);
    }
}

?>
