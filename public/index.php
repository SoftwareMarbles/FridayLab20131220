<?php

/**
 * Handling fatal error
 *
 * @return void
 */
function fatalErrorHandler()
{
    # Getting last error
    $error = error_get_last();

    # Checking if last error is a fatal error
    if(($error['type'] === E_ERROR) || ($error['type'] === E_USER_ERROR))
    {
        echo json_encode(array(
            'status' => 'fail',
            'error' => 'A fatal error has occurred in ' . $error['file'] . ', line ' . $error['line'] . ': ' . $error['message']));
    }
}

# Registering shutdown function
register_shutdown_function('fatalErrorHandler');

//  Setup the include path before trying to include any other source files.
ini_set("include_path", ".:../:../epiphany/src/:../ApnsPHP/");

include 'Epi.php';
include 'Database.php';
include 'PushService.php';
include 'Api.php';

Epi::setSetting('exceptions', true);
Epi::init('route', 'database');

Database::setupDatabase();
PushService::setupService();
Api::setupApi();

?>
