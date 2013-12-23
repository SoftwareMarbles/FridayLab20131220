<?php

//  Constants used in the entire module.
define('APP_NAME_PARAM', 'appName');
define('APP_ID_PARAM', 'appId');
define('APP_SECRET_PARAM', 'appSecret');
define('LOGIN_TOKEN_PARAM', 'token');
define('MESSAGE_ID_PARAM', 'messageId');
define('STATUS_RETURN_PARAM', 'status');
define('TIMESTAMP_RETURN_PARAM', 'timestamp');

class Api
{
    public static function setupApi()
    {
        //  Heartbeat API doesn't receive any parameters.
        getApi()->get('/', array('Api', 'heartbeat'), EpiApi::external);
        //  Register API receives the name of the app to be registered.
        getApi()->post('/registerApp', array('Api', 'registerApp'), EpiApi::external);
        getApi()->post('/login', array('Api', 'login'), EpiApi::external);
        getApi()->post('/send', array('Api', 'send'), EpiApi::external);
        getApi()->get('/getStatus', array('Api', 'getStatus'), EpiApi::external);
        getApi()->get('/getStatistics', array('Api', 'getStatistics'), EpiApi::external);
        getApi()->post('/logout', array('Api', 'logout'), EpiApi::external);
        getApi()->post('/unregisterApp', array('Api', 'unres'), EpiApi::external);
        getRoute()->run();
    }

    //  As seen here: http://stackoverflow.com/questions/169428/php-datetime-microseconds-always-returns-0
    static function nowWithUseconds()
    {
        list($usec, $sec) = explode(' ', microtime());
        $usec = substr($usec, 2, 6);
        $datetime_now = date('Y-m-d H:i:s\.', $sec).$usec;
        return new DateTime($datetime_now, new DateTimeZone(date_default_timezone_get()));
    }

    static function getTimestamp()
    {
        $now = nowWithUseconds();
        return $now->format('Y-m-d H:i:s.u');
    }

    static function reportFailure($error)
    {
        echo json_encode(array(
            STATUS_RETURN_PARAM => 'fail',
            TIMESTAMP_RETURN_PARAM => Api::getTimestamp(),
            'error' => $error));
    }

    static function reportSuccess(array $result = NULL)
    {
        //  Make the result array if it wasn't passed.
        if(!$result)
        {
            $result = array();
        }

        //  Always add the status success to the result array.
        $result[STATUS_RETURN_PARAM] = 'success';
        $result[TIMESTAMP_RETURN_PARAM] = Api::getTimestamp();

        echo json_encode($result);
    }

    //  Returns the given parameter's value or reports failure if the parameter is not available and not optional.
    static function getParam($param, $optional = FALSE)
    {
        $value = $_GET[$param];
        if($value)
        {
            return $value;
        }

        if(!$optional)
        {
            Api::reportFailure(sprintf('%s parameter is not optional.', $param));
        }

        return NULL;
    }

    static function getAppNameParam()
    {
        return Api::getParam(APP_NAME_PARAM);
    }

    static function getAppIdParam()
    {
        return Api::getParam(APP_ID_PARAM);
    }

    static function getAppSecretParam()
    {
        return Api::getParam(APP_SECRET_PARAM);
    }

    static function getTokenParam()
    {
        return Api::getParam(LOGIN_TOKEN_PARAM);
    }

    static function getMessageIdParam()
    {
        return Api::getParam(MESSAGE_ID_PARAM);
    }

    static function tokenIsValid($token)
    {
        $loginData = Database::queryLoginsPerToken($token);

        //  A token is valid if it exist in the database, it hasn't expired just yet (its expiresAt is less than the current time)
        //  and its state is loggedIn.
        return $loginData
            && $loginData['expiresAt'] < new DateTime()
            && $loginData['state'] == DatabaseLoginState::LoggedIn;
    }

    //  The functions implementing API.
    public static function heartbeat()
    {
        //  Return the format and the current server time.
        $data = array(
            'format' => 'json',
            'version' => '0.02');

        Api::reportSuccess($data);
    }

    public static function registerApp()
    {
        try
        {
            $appName = Api::getAppNameParam();
            if(!$appName)
            {
                return;
            }

            //  If the app is already register just return its data.
            $appData = Database::queryAppsPerName($appName);
            if($appData)
            {
                Api::reportSuccess($appData);
                return;
            }

            //  Generate unique app ID and its secret.
            $appId = uniqid('id.', true);
            $secret = uniqid('secret.', true);

            //  Add the new app to the database and return its data.
            $data = Database::addApp($appName, $appId, $secret);
            if(!data)
            {
                Api::reportFailure('Couldn\'t add app data.');
                return;
            }

            Api::reportSuccess($data);
        }
        catch(Exception $e)
        {
            Api::reportFailure($e->getMessage());
        }
    }

    public static function login()
    {
        try
        {
            //  We need both app's ID and secret to correctly login.
            $appId = Api::getAppIdParam();
            if(!$appId)
            {
                return;
            }
            $appSecret = Api::getAppSecretParam();
            if(!$appSecret)
            {
                return;
            }

            //  We need to check that the app has been registered and that its
            //  secret and the given secret match.
            $appData = Database::queryAppsPerId($appId);
            if(!$appData)
            {
                Api::reportFailure('Incorrect app ID.');
                return;
            }
            if($appData['secret'] != $appSecret)
            {
                //  We report the bad secret error openly. There are other API calls where a possible attacker
                //  could check the validity of the app ID in his or her posession so it makes no sense to obfuscate
                //  the message here.
                Api::reportFailure('Incorrect app secret.');
                return;
            }

            //  Generate unique token.
            $token = uniqid('token.', true);
            //  Tokens for now all expire in a day.
            $expiresAt = new DateTime();
            $expiresAt->add(DateInterval::createFromDateString('1 day'));

            //  Add the token to the database and return its data.
            $loginData = Database::addLogin($token, $appId, $expiresAt, DatabaseLoginState::LoggedIn);
            if(!loginData)
            {
                Api::reportFailure('Couldn\'t add login data.');
                return;
            }

            Api::reportSuccess($loginData);
        }
        catch(Exception $e)
        {
            Api::reportFailure($e->getMessage());
        }
    }

    public static function send()
    {
        try
        {
            $token = Api::getTokenParam();
            if(!$token)
            {
                return;
            }

            if(!tokenIsValid($token))
            {
                Api::reportFailure('Token has expired.');
                return;
            }

            //  Get the JSON payload from the request wrapper (see http://php.net/manual/en/wrappers.php.php)
            $rawPayload = file_get_contents('php://input');
            if(!$rawPayload)
            {
                Api::reportFailure('Payload is missing.');
                return;
            }
            //  Decode the JSON and convert the payload into an array.
            $payload = json_decode($rawPayload, TRUE);
            if(!$payload)
            {
                Api::reportFailure('Payload is not correctly formed JSON.');
                return;
            }

            $TYPE = 'type';
            $RECEPIENT = 'recepient';
            $MESSAGE_TEXT = 'messageText';

            if(!isset($payload[$TYPE])
                || !isset($payload[$RECEPIENT])
                || !isset($payload[$MESSAGE_TEXT]))
            {
                Api::reportFailure('Payload lacks non-optional properties.');
                return;
            }

            //  Generate unique message ID.
            $messageId = uniqid('message.', true);

            $messageData = Database::addMessage(
                $messageId,
                $token,
                DatabaseMessageState::Waiting,
                $payload[$TYPE],
                $payload[$RECEPIENT],
                $payload[$MESSAGE_TEXT]);
            if(!$messageData)
            {
               Api::reportFailure('Couldn\'t add messageStatus data.');
               return;
            }

            //  Now send the message.
            PushService::push($messageData);

            Api::reportSuccess($messageData);
        }
        catch(Exception $e)
        {
            Api::reportFailure($e->getMessage());
        }
    }

    public static function getStatus()
    {
        try
        {
            $messageId = Api::getMessageIdParam();
            if(!$messageId)
            {
                return;
            }

            $messageData = Database::queryMessagesPerId($messageId);
            if(!$messageData)
            {
                Api::reportFailure('Couldn\'t find the message.');
                return;
            }

            Api::reportSuccess($messageData);
        }
        catch(Exception $e)
        {
            Api::reportFailure($e->getMessage());
        }
    }

    public static function getStatistics()
    {
        try
        {
            $appName = Api::getAppNameParam();
            if(!$appName)
            {
                return;
            }

            $stats = Database::queryStatsPerAppName($appName);

            Api::reportSuccess($stats);
        }
        catch(Exception $e)
        {
            Api::reportFailure($e->getMessage());
        }
    }

    public static function logout()
    {
        try
        {
            $token = Api::getTokenParam();
            if(!$token)
            {
                return;
            }

            //  This cannot logically fail as we don't care if the client tries to log out an inexisting login.
            Database::updateLoginState($token, DatabaseLoginState::LoggedOut);

            Api::reportSuccess();
        }
        catch(Exception $e)
        {
            Api::reportFailure($e->getMessage());
        }
    }

    public static function unregisterApp()
    {
        try
        {
            Api::reportSuccess();
        }
        catch(Exception $e)
        {
            Api::reportFailure($e->getMessage());
        }
    }
}

?>
