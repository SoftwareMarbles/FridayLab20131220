<?php

define('TABLE_APPS_ID_COLUMN', 'id');
define('TABLE_APPS_NAME_COLUMN', 'name');
define('TABLE_APPS_SECRET_COLUMN', 'secret');

class DatabaseMessageState extends SplEnum
{
    const __default = self::Waiting;

    const Waiting = 0;
    const Sent = 1;
}

class DatabaseLoginState extends SplEnum
{
    const __default = self::LoggedIn;

    const LoggedIn = 0;
    const LoggedOut = 1;
}

class Database
{
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
CREATE TABLE IF NOT EXISTS fridayLab20131220.apps (
    id varchar(100),
    name varchar(100),
    secret varchar(100)
);
',
'
CREATE TABLE IF EXISTS fridayLab20131220.messages;

CREATE TABLE IF NOT EXISTS fridayLab20131220.messages (
    id varchar(100),
    token varchar(100),
    state int
);
',
'
DROP TABLE IF EXISTS fridayLab20131220.logins;

CREATE TABLE IF NOT EXISTS fridayLab20131220.logins (
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

    public static function queryStatsPerAppName($appName)
    {
        return getDatabase()->one(
            'SELECT COUNT(*) AS messageCount FROM messages WHERE appId = (SELECT id FROM apps WHERE appName = :appName)',
            array(':appName' => $appName));
    }

    //  Functions related to logins table.
    public static function addLogin($token, $appId, $expiresAt, $state)
    {
        //  Store the login data.
        getDatabase()->execute('INSERT INTO logins(token, appId, expiresAt, state) VALUES(:token, :appId, :expiresAt)', array(
            ':token' => $token,
            ':appId' => $appId,
            ':expiresAt' => $expiresAt->format('Y-m-d H:i:s.u'),
            ':state' => $state);

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
        getDatabase()->execute('UPDATE login SET state = :state WHERE token = :token',
            array(':token' => $token, ':state' = $state));
    }
}

?>
