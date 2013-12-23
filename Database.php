<?php

define('TABLE_APPS_ID_COLUMN', 'id');
define('TABLE_APPS_NAME_COLUMN', 'name');
define('TABLE_APPS_SECRET_COLUMN', 'secret');

class DatabaseMessageState
{
    const __default = self::Waiting;

    const Waiting = 0;
    const Sent = 1;
}

class DatabaseLoginState
{
    const __default = self::LoggedIn;

    const LoggedIn = 0;
    const LoggedOut = 1;
}

class Database
{
    //  Functions related to apps table.
    public static function queryAppsPerName($appName)
    {
        return getDatabase()->one('SELECT * FROM apps WHERE name = :appName', array(':appName' => $appName));
    }

    public static function queryAppsPerId($appId)
    {
        return getDatabase()->one('SELECT * FROM apps WHERE id = :appId', array(':appId' => $appId));
    }

    public static function addApp($appName, $appId, $appSecret)
    {
        //  Store the app data.
        getDatabase()->execute('INSERT INTO apps(id, name, secret) VALUES(:id, :name, :secret)', array(
            ':id' => $appId,
            ':name' => $appName,
            ':secret' => $appSecret));

        //  As the result we return the row we just added from the database itself.
        return Database::queryAppsPerName($appName);
    }

    //  Functions related to logins table.
    public static function addLogin($token, $appId, $expiresAt, $state)
    {
        //  Store the login data.
        getDatabase()->execute('INSERT INTO logins(token, appId, expiresAt, state) VALUES(:token, :appId, :expiresAt, :state)', array(
            ':token' => $token,
            ':appId' => $appId,
            ':expiresAt' => $expiresAt->format('Y-m-d H:i:s.u'),
            ':state' => $state));

        //  As the result we return the row we just added from the database itself.
        return Database::queryLoginsPerToken($token);
    }

    public static function queryLoginsPerToken($token)
    {
        return getDatabase()->one(
            'SELECT * FROM logins WHERE token = :token',
            array(':token' => $token));
    }

    public static function updateLoginState($token, $state)
    {
        getDatabase()->execute('UPDATE logins SET state = :state WHERE token = :token',
            array(':token' => $token, ':state' => $state));
    }

    //  Functions related to messages table.
    public static function queryMessagesPerId($messageId)
    {
        return getDatabase()->one(
            'SELECT * FROM messages WHERE id = :messageId',
            array(':messageId' => $messageId));
    }

    public static function addMessage($messageId, $token, $state, $type, $recepient, $messageText)
    {
        //  Store the data.
        getDatabase()->execute('INSERT INTO messages(id, token, state, type, recepient, messageText) VALUES(:id, :token, :state, :type, :recepient, :messageText)', array(
            ':id' => $messageId,
            ':token' => $token,
            ':state' => $state,
            ':type' => $type,
            ':recepient' => $recepient,
            ':messageText' => $messageText));

        //  As the result we return the row we just added from the database itself.
        return Database::queryMessagesPerId($messageId);
    }

    public static function updateMessageState($messageId, $state)
    {
        getDatabase()->execute('UPDATE messages SET state = :state WHERE id = :messageId',
            array(':messageId' => $messageId, ':state' => $state));
    }

    public static function queryStatsPerAppId($appId)
    {
        return getDatabase()->one(
            '
        SELECT
            (SELECT COUNT(*) AS messageCount FROM messages WHERE state = 0 AND token IN (SELECT token FROM logins WHERE appId = :appId)) AS messagesWaiting,
            (SELECT COUNT(*) AS messageCount FROM messages WHERE state = 1 AND token IN (SELECT token FROM logins WHERE appId = :appId)) AS messagesSent
            ',
            array(':appId' => $appId));
    }

    //  Sets up the database (creates it or updates it)
    public static function setupDatabase()
    {
        EpiDatabase::employ(
            'mysql',
            'mysql',
            'localhost',
            'root',
            'moot');

        $createStatements = array(
'CREATE DATABASE IF NOT EXISTS fridayLab20131220;'
,
'
CREATE TABLE IF NOT EXISTS fridayLab20131220.apps
(
    id varchar(100),
    name varchar(100),
    secret varchar(100)
);
',
'
CREATE TABLE IF NOT EXISTS fridayLab20131220.messages
(
    id varchar(100),
    token varchar(100),
    state int,
    type int,
    recepient varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
    messageText varchar(2000) CHARACTER SET utf8 COLLATE utf8_unicode_ci
);
',
'
CREATE TABLE IF NOT EXISTS fridayLab20131220.logins
(
    token varchar(100),
    appId varchar(100),
    expiresAt datetime,
    state int
)
',
//  Our last statement is USE so that we switch the connection context to our db.
'USE fridayLab20131220;'
    );

        //  Execute all the create statements.
        for ($i=0; $i < count($createStatements); $i++)
        {
            getDatabase()->execute($createStatements[$i]);
        }
    }

}

?>
