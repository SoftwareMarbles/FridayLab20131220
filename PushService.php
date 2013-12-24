<?php

require_once 'ApnsPHP/Autoload.php';

class PushService
{
    static $PEM_PATH;
    static $CERT_PASSPHRASE;

    public static function SetupService($CONFIG)
    {
        $PEM_PATH = $CONFIG['PUSH_SERVICE_PEM_PATH'];
        $CERT_PASSPHRASE = $CONFIG['PUSH_SERVICE_CERT_PASSPHRASE'];

        if(!isset($PEM_PATH)
            || !isset($CERT_PASSPHRASE))
        {
            throw new Exception('Not all push service configuration parameters have been defined.');
        }
    }

    public static function push($messageData)
    {
        // Instanciate a new ApnsPHP_Push object
        $push = new ApnsPHP_Push(
                ApnsPHP_Abstract::ENVIRONMENT_SANDBOX,
                $PEM_PATH
        );

        // Set the Provider Certificate passphrase
        $push->setProviderCertificatePassphrase();

        // Set the Root Certificate Autority to verify the Apple remote peer
        $push->setRootCertificationAuthority('../certs/entrust_root_certification_authority.pem');

        // Connect to the Apple Push Notification Service
        $push->connect();

        // Instantiate a new Message with a single recipient
        $message = new ApnsPHP_Message($messageData['recepient']);

        // Set a simple welcome text
        $message->setText($messageData['messageText']);

        // Play the default sound
        $message->setSound();

        // Add the message to the message queue
        $push->add($message);

        // Send all messages in the message queue
        $push->send();

        // Disconnect from the Apple Push Notification Service
        $push->disconnect();

        // Examine the error message container
        $aErrorQueue = $push->getErrors();
        if (!empty($aErrorQueue)) {
            return $aErrorQueue;
        }

        return NULL;
    }
}

?>