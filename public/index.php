<?php

//  Registering shutdown function
register_shutdown_function('fatalErrorHandler');

//  Setup the include path before trying to include any other source files.
ini_set("include_path", ".:../:../epiphany/src/:../ApnsPHP/");

include 'Api.php';

Api::process();

//  Handling fatal error
function fatalErrorHandler()
{
    //  Getting last error
    $error = error_get_last();

    //  Checking if last error is a fatal error
    if(($error['type'] === E_ERROR) || ($error['type'] === E_USER_ERROR))
    {
        //  Outputting JSON per our error specification.
        echo json_encode(array(
            'status' => 'fail',
            'error' => 'A fatal error has occurred in ' . $error['file'] . ', line ' . $error['line'] . ': ' . $error['message']));
    }
}

?>
