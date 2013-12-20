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

function reportError($error) {
    echo json_encode(array(
        'status' => 'fail',
        'error' => $error));
}

//  The functions implementing API.
function heartbeat() {
    $now = new DateTime();

    //  Return the live status with the current server time.
    $data = array(
        'format' => 'json',
        'status' => 'live',
        'time' => $now->format('Y-m-d H:i:s.mmm'));

    echo json_encode($data);
}

function registerApp() {
    $appName = $_GET['appName'],
    if(!$appName) {
        reportError('appName parameter is not optional.');
        return;
    }

    $data = array(
        'appName' => $appName,
        'appId' => 'appId',
        'secret' => 'secret');
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
    $data = array(
        'status' => 'OK');
    echo json_encode($data);
}

function unregisterApp() {
    $data = array(
        'status' => 'OK');
    echo json_encode($data);
}

?>
