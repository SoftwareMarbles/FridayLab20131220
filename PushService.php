<?php

require_once './ApnsPHP/Autoload.php';

class PushService
{
    public static function SetupService()
    {
        //  Nothing to do here.
    }

    public static function push($messageData)
    {
        // Instanciate a new ApnsPHP_Push object
        $push = new ApnsPHP_Push(
                ApnsPHP_Abstract::ENVIRONMENT_SANDBOX,
                'server_certificates_bundle_sandbox.pem'
        );

        // Set the Provider Certificate passphrase
        $push->setProviderCertificatePassphrase('test');

        // Set the Root Certificate Autority to verify the Apple remote peer
        $push->setRootCertificationAuthority('entrust_root_certification_authority.pem');

        // Connect to the Apple Push Notification Service
        $push->connect();

        // Instantiate a new Message with a single recipient
        $message = new ApnsPHP_Message($messageData['recepient']);

        // Set a custom identifier. To get back this identifier use the getCustomIdentifier() method
        // over a ApnsPHP_Message object retrieved with the getErrors() message.
        $message->setCustomIdentifier("Message-Badge-3");

        // Set badge icon to "1"
        $message->setBadge(isset($messageData['badge']) ? 0 : $messageData['badge']);

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