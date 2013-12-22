<?php

include 'Epi.php';

define('TABLE_APPS_ID_COLUMN', 'id');
define('TABLE_APPS_NAME_COLUMN', 'name');
define('TABLE_APPS_SECRET_COLUMN', 'secret');

class Database
{
    public static function setupDatabase() {
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
DROP TABLE IF EXISTS fridayLab20131220.apps;

CREATE TABLE IF NOT EXISTS fridayLab20131220.apps (
    id varchar(100),
    name varchar(100),
    secret varchar(100)
);
',
'
CREATE TABLE IF NOT EXISTS fridayLab20131220.messages (
    id varchar(100),
    appId varchar(100),
    status varchar(10)
);
',
//  Our last statement is USE so that we switch the connection context to our db.
'USE fridayLab20131220;'
    );

        //  Execute all the create statements.
        for ($i=0; $i < count($createStatements); $i++) {
            getDatabase()->execute($createStatements[$i]);
        }
    }

    public static function queryAppsPerName($appName) {
        return getDatabase()->one('SELECT * FROM apps WHERE name = :appName', array(':appName' => $appName));
    }

    public static function addApp($appName, $appId, $appSecret) {
        //  Store the app data.
        getDatabase()->execute('INSERT INTO apps(id, name, secret) VALUES(:id, :name, :secret)', array(
            ':id' => $appId,
            ':name' => $appName,
            ':secret' => $appSecret));

        //  As the result we return the row we just added from the database itself.
        return queryAppsPerName($appName);
    }

    public static function queryStatsPerAppName($appName) {
        return getDatabase()->one(
            'SELECT COUNT(*) AS messageCount FROM messages WHERE appId = (SELECT id FROM apps WHERE appName = :appName)',
            array(':appName' => $appName));
    }
}

?>
