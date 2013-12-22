<?php

//  Setup the include path before trying to include any other source files.
ini_set("include_path", ".:../:../epiphany/src/");

include 'Epi.php';
include 'Database.php';

//  Constants used in the entire module.
define('APP_NAME_PARAM', 'appName');

define('UNIQUE_ID_PREFIX', 'friday-lab-20131220');
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
    $appData = Database::queryAppsPerName($appName);
    if($appData) {
        reportSuccess($appData);
        return;
    }

    //  Generate unique app ID and its secret.
    $appId = uniqid(UNIQUE_ID_PREFIX, true);
    $secret = uniqid(UNIQUE_ID_PREFIX, true);

    //  Add the new app to the database and return its data.
    $data = Database::addApp($appName, $appId, $secret);

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

    $stats = Database::queryStatsPerAppName($appName);

    reportSuccess($stats);
}

function logout() {
    reportSuccess();
}

function unregisterApp() {
    reportSuccess();
}

?>
