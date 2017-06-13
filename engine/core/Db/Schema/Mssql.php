<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Db\Schema;

use Exception;
use Piwik\Common;
use Piwik\Date;
use Piwik\Db\SchemaInterface;
use Piwik\Db;
use Piwik\DbHelper;

/**
 * MySQL schema
 */
class Mssql implements SchemaInterface
{
    private $tablesInstalled = null;

    /**
     * Is this schema available?
     *
     * @return bool  True if schema is available; false otherwise
     */
    public static function isAvailable()
    {
        return true;
    }

    /**
     * Get the SQL to create Piwik tables
     *
     * @return array  array of strings containing SQL
     */
    public function getTablesCreateSql()
    {
        $engine       = $this->getTableEngine();
        $prefixTables = $this->getTablePrefix();

        $tables = array(
            'user'    => "CREATE TABLE {$prefixTables}user (
                          login NVARCHAR(100) NOT NULL,
                          password NCHAR(32) NOT NULL,
                          alias NVARCHAR(45) NOT NULL,
                          email NVARCHAR(100) NOT NULL,
                          token_auth NCHAR(32) NOT NULL UNIQUE,
                          superuser_access TINYINT NOT NULL DEFAULT '0',
                          date_registered datetime NULL,
                            PRIMARY KEY(login)
                          )             ",

            'access'  => "CREATE TABLE {$prefixTables}access (
                          login NVARCHAR(100) NOT NULL,
                          idsite BIGINT NOT NULL,
                          access NVARCHAR(10) NULL,
                            PRIMARY KEY(login, idsite)
                          )
            ",

            'site'    => "CREATE TABLE {$prefixTables}site (
                          idsite BIGINT NOT NULL IDENTITY(1,1),
                          name NVARCHAR(90) NOT NULL,
                          main_url NVARCHAR(255) NOT NULL,
                            ts_created datetime NULL,
                            ecommerce TINYINT DEFAULT 0,
                            sitesearch TINYINT DEFAULT 1,
                            sitesearch_keyword_parameters TEXT NOT NULL,
                            sitesearch_category_parameters TEXT NOT NULL,
                            timezone NVARCHAR( 50 ) NOT NULL,
                            currency NCHAR( 3 ) NOT NULL,
                            exclude_unknown_urls TINYINT DEFAULT 0,
                            excluded_ips TEXT NOT NULL,
                            excluded_parameters TEXT NOT NULL,
                            excluded_user_agents TEXT NOT NULL,
                            [group] NVARCHAR(250) NOT NULL,
                            [type] NVARCHAR(255) NOT NULL,
                            keep_url_fragment TINYINT NOT NULL DEFAULT 0,
                              PRIMARY KEY(idsite)
                            )
            ",

            'site_setting'    => "CREATE TABLE {$prefixTables}site_setting (
                          idsite BIGINT NOT NULL IDENTITY (1,1),
                          setting_name NVARCHAR(255) NOT NULL,
                          setting_value TEXT NOT NULL,
                              PRIMARY KEY(idsite, setting_name)
                            )
            ",

            'site_url'    => "CREATE TABLE {$prefixTables}site_url (
                              idsite BIGINT NOT NULL,
                              url NVARCHAR(255) NOT NULL,
                                PRIMARY KEY(idsite, url)
                              )
            ",

            'goal'       => "CREATE TABLE {$prefixTables}goal (
                              idsite int NOT NULL,
                              idgoal int NOT NULL,
                              name NVARCHAR(50) NOT NULL,
                              match_attribute NVARCHAR(20) NOT NULL,
                              pattern NVARCHAR(255) NOT NULL,
                              pattern_type NVARCHAR(10) NOT NULL,
                              case_sensitive int NOT NULL,
                              allow_multiple int NOT NULL,
                              revenue float NOT NULL,
                              deleted int NOT NULL default '0',
                                PRIMARY KEY  (idsite,idgoal)
                              )
            ",

            'logger_message'      => "CREATE TABLE {$prefixTables}logger_message (
                                      idlogger_message BIGINT NOT NULL IDENTITY (1,1),
                                      tag NVARCHAR(50) NULL,
                                      timestamp datetime NULL,
                                      level NVARCHAR(16) NULL,
                                      message TEXT NULL,
                                        PRIMARY KEY(idlogger_message)
                                      )
            ",

            'log_action'          => "CREATE TABLE {$prefixTables}log_action (
                                      idaction BIGINT NOT NULL IDENTITY (1,1),
                                      name nvarchar(MAX),
                                      hash BIGINT NOT NULL,
                                      type INT  NULL,
                                      url_prefix TINYINT NULL,
                                        PRIMARY KEY(idaction),
                                        INDEX index_type_hash (type, hash)
                                      )
            ",

            'log_visit'   => "CREATE TABLE {$prefixTables}log_visit (
                              idvisit BIGINT NOT NULL IDENTITY (1,1),
                              idsite BIGINT NOT NULL,
                              idvisitor NVARCHAR(200) NOT NULL,
                              user_id Nvarchar(200),
                              visitor_localtime TIME NOT NULL,
                              visitor_returning  SMALLINT NOT NULL,
                              visitor_count_visits INT NOT NULL,
                              visitor_days_since_last INT NOT NULL,
                              visitor_days_since_order INT NOT NULL,
                              visitor_days_since_first INT NOT NULL,
                              visit_first_action_time DATETIME NOT NULL,
                              visit_last_action_time DATETIME NOT NULL,
                              visit_exit_idaction_url BIGINT DEFAULT 0,
                              visit_exit_idaction_name BIGINT NOT NULL,
                              visit_entry_idaction_url BIGINT NOT NULL,
                              visit_entry_idaction_name BIGINT NOT NULL,
                              visit_total_actions INT NOT NULL,
                              visit_total_searches INT NOT NULL,
                              visit_total_events INT NOT NULL,
                              visit_total_time INT NOT NULL,
                              visit_goal_converted SMALLINT NOT NULL,
                              visit_goal_buyer SMALLINT NOT NULL, 
                              referer_type SMALLINT NULL,
                              referer_name NVARCHAR(70) NULL,
                              referer_url NVARCHAR(MAX)) NOT NULL,
                              referer_keyword NVARCHAR(255) NULL,
                              config_id NVARCHAR(200) NOT NULL,
                              config_os NCHAR(3) NOT NULL,
                              config_os_version Nvarchar(100),
                              config_browser_engine NVARCHAR(10) NOT NULL,
                              config_browser_name NVARCHAR(10) NOT NULL,
                              config_browser_version NVARCHAR(20) NOT NULL,
                              config_resolution NVARCHAR(9) NOT NULL,
                                config_device_brand Nvarchar(100),
                                config_device_model Nvarchar(100),
                                config_device_type tinyint,
                              config_pdf SMALLINT NOT NULL,
                              config_flash SMALLINT NOT NULL,
                              config_java SMALLINT NOT NULL,
                              config_director SMALLINT NOT NULL,
                              config_quicktime SMALLINT NOT NULL,
                              config_realplayer SMALLINT NOT NULL,
                              config_windowsmedia SMALLINT NOT NULL,
                              config_gears SMALLINT NOT NULL,
                              config_silverlight SMALLINT NOT NULL,
                              config_cookie SMALLINT NOT NULL,
                              location_ip NVARCHAR(200) NOT NULL,
                              location_browser_lang NVARCHAR(20) NOT NULL,
                              location_country NVARCHAR(3) NOT NULL,
                              location_region NVARCHAR(2) DEFAULT NULL,
                              location_city NVARCHAR(255) DEFAULT NULL,
                              location_latitude DOUBLE PRECISION DEFAULT NULL,
                              location_longitude DOUBLE PRECISION DEFAULT NULL,
                              custom_var_k1 NVARCHAR(200) DEFAULT NULL,
                              custom_var_v1 NVARCHAR(200) DEFAULT NULL,
                              custom_var_k2 NVARCHAR(200) DEFAULT NULL,
                              custom_var_v2 NVARCHAR(200) DEFAULT NULL,
                              custom_var_k3 NVARCHAR(200) DEFAULT NULL,
                              custom_var_v3 NVARCHAR(200) DEFAULT NULL,
                              custom_var_k4 NVARCHAR(200) DEFAULT NULL,
                              custom_var_v4 NVARCHAR(200) DEFAULT NULL,
                              custom_var_k5 NVARCHAR(200) DEFAULT NULL,
                              custom_var_v5 NVARCHAR(200) DEFAULT NULL,
                                PRIMARY KEY(idvisit),
                                INDEX index_idsite_config_datetime (idsite, config_id, visit_last_action_time),
                                INDEX index_idsite_datetime (idsite, visit_last_action_time),
                                INDEX index_idsite_idvisitor (idsite, idvisitor)
                              )
            ",

            'log_conversion_item'   => "CREATE TABLE {$prefixTables}log_conversion_item (
                                        idsite BIGINT NOT NULL,
                                        idvisitor BINARY(8) NOT NULL,
                                        server_time DATETIME NOT NULL,
                                        idvisit BIGINT NOT NULL,
                                        idorder NVARCHAR(100) NOT NULL,
                                        idaction_sku BIGINT NOT NULL,
                                        idaction_name BIGINT NOT NULL,
                                        idaction_category BIGINT NOT NULL,
                                        idaction_category2 BIGINT NOT NULL,
                                        idaction_category3 BIGINT NOT NULL,
                                        idaction_category4 BIGINT NOT NULL,
                                        idaction_category5 BIGINT NOT NULL,
                                        price FLOAT NOT NULL,
                                        quantity BIGINT NOT NULL,
                                        deleted TINYINT NOT NULL,
                                          PRIMARY KEY(idvisit, idorder, idaction_sku),
                                          INDEX index_idsite_servertime ( idsite, server_time )
                                        )
            ",

            'log_conversion'      => "CREATE TABLE {$prefixTables}log_conversion (
                                      idvisit BIGINT NOT NULL,
                                      idsite BIGINT NOT NULL,
                                      idvisitor BINARY(8) NOT NULL,
                                      server_time datetime NOT NULL,
                                      idaction_url int default NULL,
                                      idlink_va INT DEFAULT NULL,
                                      referer_visit_server_date DATE DEFAULT NULL,
                                      referer_type BIGINT DEFAULT NULL,
                                      referer_name NVARCHAR(70) DEFAULT NULL,
                                      referer_keyword NVARCHAR(255) DEFAULT NULL,
                                      visitor_returning SMALLINT NOT NULL,
                                      visitor_count_visits INT NOT NULL,
                                      visitor_days_since_first INT NOT NULL,
                                      visitor_days_since_order INT NOT NULL,
                                      location_country NVARCHAR(3) NOT NULL,
                                      location_region NVARCHAR(2) DEFAULT NULL,
                                      location_city NVARCHAR(255) DEFAULT NULL,
                                      location_latitude DOUBLE PRECISION DEFAULT NULL,
                                      location_longitude DOUBLE PRECISION DEFAULT NULL,
                                      url TEXT NOT NULL,
                                      idgoal int NOT NULL,
                                      buster bigint NOT NULL,
                                      idorder NVARCHAR(100) default NULL,
                                      items INT DEFAULT NULL,
                                      revenue FLOAT DEFAULT NULL,
                                      revenue_subtotal FLOAT DEFAULT NULL,
                                      revenue_tax FLOAT DEFAULT NULL,
                                      revenue_shipping FLOAT DEFAULT NULL,
                                      revenue_discount FLOAT DEFAULT NULL,
                                      custom_var_k1 NVARCHAR(200) DEFAULT NULL,
                                      custom_var_v1 NVARCHAR(200) DEFAULT NULL,
                                      custom_var_k2 NVARCHAR(200) DEFAULT NULL,
                                      custom_var_v2 NVARCHAR(200) DEFAULT NULL,
                                      custom_var_k3 NVARCHAR(200) DEFAULT NULL,
                                      custom_var_v3 NVARCHAR(200) DEFAULT NULL,
                                      custom_var_k4 NVARCHAR(200) DEFAULT NULL,
                                      custom_var_v4 NVARCHAR(200) DEFAULT NULL,
                                      custom_var_k5 NVARCHAR(200) DEFAULT NULL,
                                      custom_var_v5 NVARCHAR(200) DEFAULT NULL,
                                        PRIMARY KEY (idvisit, idgoal, buster),
                                        INDEX index_idsite_datetime ( idsite, server_time )
                                      )
            ",
            // UNIQUE KEY unique_idsite_idorder (idsite, idorder),




            'log_link_visit_action' => "CREATE TABLE {$prefixTables}log_link_visit_action (
                                        idlink_va BIGINT NOT NULL IDENTITY (1,1),
                                        idsite BIGINT NOT NULL,
                                        idvisitor NVARCHAR(200) NOT NULL,
                                              server_time DATETIME NOT NULL,
                                        idvisit BIGINT NOT NULL,
                                              idaction_url BIGINT DEFAULT NULL,
                                        idaction_url_ref BIGINT NULL DEFAULT 0,
                                              idaction_name BIGINT,
                                        idaction_name_ref BIGINT NOT NULL,
                                              idaction_event_category BIGINT DEFAULT NULL,
                                              idaction_event_action BIGINT DEFAULT NULL,
                                        idaction_content_interaction BIGINT DEFAULT NULL,
                                        idaction_content_name BIGINT DEFAULT NULL,
                                        idaction_content_piece BIGINT DEFAULT NULL,
                                        idaction_content_target BIGINT DEFAULT NULL,
                                              time_spent_ref_action BIGINT NOT NULL,
                                              custom_var_k1 NVARCHAR(200) DEFAULT NULL,
                                              custom_var_v1 NVARCHAR(200) DEFAULT NULL,
                                              custom_var_k2 NVARCHAR(200) DEFAULT NULL,
                                              custom_var_v2 NVARCHAR(200) DEFAULT NULL,
                                              custom_var_k3 NVARCHAR(200) DEFAULT NULL,
                                              custom_var_v3 NVARCHAR(200) DEFAULT NULL,
                                              custom_var_k4 NVARCHAR(200) DEFAULT NULL,
                                              custom_var_v4 NVARCHAR(200) DEFAULT NULL,
                                              custom_var_k5 NVARCHAR(200) DEFAULT NULL,
                                              custom_var_v5 NVARCHAR(200) DEFAULT NULL,
                                        custom_float FLOAT NULL DEFAULT NULL,
                                          PRIMARY KEY(idlink_va),
                                          INDEX index_idvisit(idvisit)
                                        )
            ",

            'log_profiling'   => "CREATE TABLE {$prefixTables}log_profiling (
                                  query nvarchar(MAX) NOT NULL,
                                  [count] BIGINT NULL,
                                  sum_time_ms FLOAT NULL
                                  )
            ",

            'option'        => "CREATE TABLE {$prefixTables}option (
                                option_name NVARCHAR( 255 ) NOT NULL,
                                option_value TEXT NOT NULL,
                                autoload TINYINT NOT NULL DEFAULT '1',
                                  PRIMARY KEY ( option_name ),
                                  INDEX autoload( autoload )
                                )
            ",

            'session'       => "CREATE TABLE {$prefixTables}session (
                                id NVARCHAR( 255 ) NOT NULL,
                                modified INT,
                                lifetime INT,
                                data TEXT,
                                  PRIMARY KEY ( id )
                                )
            ",

            'archive_numeric'     => "CREATE TABLE {$prefixTables}archive_numeric (
                                      idarchive BIGINT NOT NULL,
                                      name NVARCHAR(255) NOT NULL,
                                      idsite BIGINT NULL,
                                      date1 DATE NULL,
                                      date2 DATE NULL,
                                      period INT  NULL,
                                      ts_archived DATETIME NULL,
                                      value float NULL,
                                        PRIMARY KEY(idarchive, name),
                                        INDEX index_idsite_dates_period(idsite, date1, date2, period, ts_archived),
                                        INDEX index_period_archived(period, ts_archived)
                                      )
            ",
//CHANGED BY ZAKIR, 02202016, WILL STORE TEXT IN PLACE OF BLOB.
            'archive_blob'        => "CREATE TABLE {$prefixTables}archive_blob (
                                      idarchive BIGINT NOT NULL,
                                      name NVARCHAR(255) NOT NULL,
                                      idsite BIGINT NULL,
                                      date1 DATE NULL,
                                      date2 DATE NULL,
                                      period INT NULL,
                                      ts_archived DATETIME NULL,
                                      value TEXT NULL,
                                        PRIMARY KEY(idarchive, name),
                                        INDEX index_period_archived(period, ts_archived)
                                      )
            ",
//VARBINARY(MAX)
            'sequence'        => "CREATE TABLE {$prefixTables}sequence (
                                      name NVARCHAR(120) NOT NULL,
                                      value BIGINT NOT NULL ,
                                      PRIMARY KEY(name)
                                  )
            ",
        );

        return $tables;
    }

    /**
     * Get the SQL to create a specific Piwik table
     *
     * @param string $tableName
     * @throws Exception
     * @return string  SQL
     */
    public function getTableCreateSql($tableName)
    {
        $tables = DbHelper::getTablesCreateSql();

        if (!isset($tables[$tableName])) {
            throw new Exception("The table '$tableName' SQL creation code couldn't be found.");
        }

        return $tables[$tableName];
    }

    /**
     * Names of all the prefixed tables in piwik
     * Doesn't use the DB
     *
     * @return array  Table names
     */
    public function getTablesNames()
    {
        $aTables      = array_keys($this->getTablesCreateSql());
        $prefixTables = $this->getTablePrefix();

        $return = array();
        foreach ($aTables as $table) {
            $return[] = $prefixTables . $table;
        }

        return $return;
    }

    /**
     * Get list of installed columns in a table
     *
     * @param  string $tableName The name of a table.
     *
     * @return array  Installed columns indexed by the column name.
     */
    public function getTableColumns($tableName)
    {
        $db = $this->getDb();

        $allColumns = $db->fetchAll("SELECT column_name as Field, data_type as [Type], is_nullable as [Null], '' as [Key],  column_default  as [Default], '' as Extra FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = '". $tableName . "'");

        $fields = array();
        foreach ($allColumns as $column) {
            $fields[trim($column['Field'])] = $column;
        }

        return $fields;
    }

    /**
     * Get list of tables installed
     *
     * @param bool $forceReload Invalidate cache
     * @return array  installed Tables
     */
    public function getTablesInstalled($forceReload = true)
    {
        if (is_null($this->tablesInstalled)
            || $forceReload === true
        ) {
            $db = $this->getDb();
            $prefixTables = $this->getTablePrefixEscaped();

            $allTables = $this->getAllExistingTables($prefixTables);

            // all the tables to be installed
            $allMyTables = $this->getTablesNames();

            // we get the intersection between all the tables in the DB and the tables to be installed
            $tablesInstalled = array_intersect($allMyTables, $allTables);

            // at this point we have the static list of core tables, but let's add the monthly archive tables
            // $allArchiveNumeric = $db->fetchCol("SHOW TABLES LIKE '" . $prefixTables . "archive_numeric%'");
            // $allArchiveBlob    = $db->fetchCol("SHOW TABLES LIKE '" . $prefixTables . "archive_blob%'");
            $allArchiveNumeric = $db->fetchCol($this->getShowTableSql ($prefixTables . "archive_numeric%"));
            $allArchiveBlob    = $db->fetchCol($this->getShowTableSql ($prefixTables . "archive_blob%"));
            $allTablesReallyInstalled = array_merge($tablesInstalled, $allArchiveNumeric, $allArchiveBlob);

            $this->tablesInstalled = $allTablesReallyInstalled;
        }

        return $this->tablesInstalled;
    }

    public function getShowTableSql ($tblname) {
        $sql = "SELECT table_name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME like '".$tblname."'";
        return $sql;
    }

    /**
     * Checks whether any table exists
     *
     * @return bool  True if tables exist; false otherwise
     */
    public function hasTables()
    {
        return count($this->getTablesInstalled()) != 0;
    }

    /**
     * Create database
     *
     * @param string $dbName Name of the database to create
     */
    public function createDatabase($dbName = null)
    {
        if (is_null($dbName)) {
            $dbName = $this->getDbName();
        }

        Db::exec("CREATE DATABASE IF NOT EXISTS " . $dbName . " DEFAULT NCHARACTER SET utf8");
    }

    /**
     * Creates a new table in the database.
     *
     * @param string $nameWithoutPrefix The name of the table without any piwik prefix.
     * @param string $createDefinition  The table create definition, see the "MySQL CREATE TABLE" specification for
     *                                  more information.
     * @throws \Exception
     */
    public function createTable($nameWithoutPrefix, $createDefinition)
    {
        $statement = sprintf("CREATE TABLE [%s] ( %s ) ;",
                             Common::prefixTable($nameWithoutPrefix),
                             $createDefinition,
                             $this->getTableEngine());

        try {
            Db::exec($statement);
        } catch (Exception $e) {
            // mysql code error 1050:table already exists
            // see bug #153 https://github.com/piwik/piwik/issues/153
            if (!$this->getDb()->isErrNo($e, '1050')) {
                throw $e;
            }
        }
    }

    /**
     * Drop database
     */
    public function dropDatabase($dbName = null)
    {
        $dbName = $dbName ?: $this->getDbName();
        Db::exec("DROP DATABASE IF EXISTS " . $dbName);
    }

    /**
     * Create all tables
     */
    public function createTables()
    {
        $db = $this->getDb();
        $prefixTables = $this->getTablePrefix();

        $tablesAlreadyInstalled = $this->getTablesInstalled();
        $tablesToCreate = $this->getTablesCreateSql();
        unset($tablesToCreate['archive_blob']);
        unset($tablesToCreate['archive_numeric']);

        foreach ($tablesToCreate as $tableName => $tableSql) {
            $tableName = $prefixTables . $tableName;
            if (!in_array($tableName, $tablesAlreadyInstalled)) {
                $db->query($tableSql);
            }
        }
    }

    /**
     * Creates an entry in the User table for the "anonymous" user.
     */
    public function createAnonymousUser()
    {
        // The anonymous user is the user that is assigned by default
        // note that the token_auth value is anonymous, which is assigned by default as well in the Login plugin
        $db = $this->getDb();
        $sql = "INSERT INTO " . Common::prefixTable("user") . "
                    VALUES ( 'anonymous', '', 'anonymous', 'anonymous@example.org', 'anonymous', 0, '" . Date::factory('now')->getDatetime() . "' );";
        // echo $sql;
        $db->query($sql);
    }

    /**
     * Truncate all tables
     */
    public function truncateAllTables()
    {
        $tables = $this->getAllExistingTables();
        foreach ($tables as $table) {
            Db::query("TRUNCATE [$table]");
        }
    }

    private function getTablePrefix()
    {
        return $this->getDbSettings()->getTablePrefix();
    }

    private function getTableEngine()
    {
        return "";
        // return $this->getDbSettings()->getEngine();
    }

    private function getDb()
    {
        return Db::get();
    }

    private function getDbSettings()
    {
        return new Db\Settings();
    }

    private function getDbName()
    {
        return $this->getDbSettings()->getDbName();
    }

    private function getAllExistingTables($prefixTables = false)
    {
        if (empty($prefixTables)) {
            $prefixTables = $this->getTablePrefixEscaped();
        }

        // $sql = "SHOW TABLES LIKE '" . $prefixTables . "%'";
        $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME LIKE '" . $prefixTables . "%'";

        return Db::get()->fetchCol($sql);
    }

    private function getTablePrefixEscaped()
    {
        $prefixTables = $this->getTablePrefix();
        // '_' matches any NCHARacter; force it to be literal
        $prefixTables = str_replace('_', '_', $prefixTables);
        return $prefixTables;
    }
}
