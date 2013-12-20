<?php

ini_set("include_path", ".:../:../epiphany/src/");

include 'Epi.php';

Epi::init('route');
getRoute()->get('/', 'heartbeat');
getRoute()->get('/registerApp', 'registerApp');
getRoute()->get('/login', 'login');
getRoute()->get('/send', 'send');
getRoute()->get('/getStatus', 'getStatus');
getRoute()->get('/getStatistics', 'getStatistics');
getRoute()->get('/logout', 'logout');
getRoute()->get('/unregisterApp', 'unregisterApp');
getRoute()->run();

function heartbeat() {
    $data = array(
        'format' => 'json',
        'status' => 'live'
        );
    echo json_encode($data);
}

function registerApp() {
    $data = array(
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
