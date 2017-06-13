<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 *
 */
namespace Piwik\Plugins\LanguagesManager;

use Piwik\Common;
use Piwik\Config\ClientConfig;
use Piwik\Custom\CacheHelper;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Db\QueryHelper;
use Piwik\Cache;
use Piwik\Custom;

class Model
{
    private static $rawPrefix = 'user_language';
    private $table;

    public function __construct()
    {
        $this->table = Common::prefixTable(self::$rawPrefix);
    }

    public function deleteUserLanguage($userLogin)
    {
        Db::query('DELETE FROM ' . $this->table . ' WHERE login = ?', $userLogin);
        CacheHelper::deleteValue('lang_' . $userLogin);
    }

    /**
     * Returns the language for the user
     *
     * @param string $userLogin
     * @return string
     */
    public function getLanguageForUser($userLogin)
    {
        if (CacheHelper::hasKey('lang_'.$userLogin)) {
            return CacheHelper::getValue('lang_'.$userLogin);
        }
        $data = Db::fetchOne('SELECT language FROM ' . $this->table .
                            ' WHERE login = ? ', array($userLogin));
        CacheHelper::setValue('lang_'.$userLogin, $data);
        return $data;
    }

    /**
     * Sets the language for the user
     *
     * @param string $login
     * @param string $languageCode
     * @return bool
     */
    public function setLanguageForUser($login, $languageCode)
    {
        if (QueryHelper::DEFAULT_SCHEMA == 'Mssql') {
            $query = 'BEGIN
			BEGIN
				UPDATE ' . $this->table . ' SET language = ? WHERE login = ?
			END
			IF NOT EXISTS (SELECT * FROM ' . $this->table . ' WHERE login = ?)
				BEGIN
					INSERT INTO ' . $this->table . ' (login, language) VALUES (?,?)
				END
		    END';
            $bind = array($languageCode, $login, $login, $login, $languageCode);
        } else {

            $query = 'INSERT INTO ' . $this->table .
                ' (login, language) VALUES (?,?) ON DUPLICATE KEY UPDATE language=?';
            $bind = array($login, $languageCode, $languageCode);
        }
        Db::query($query, $bind);
        CacheHelper::setValue('lang_' . $login, $languageCode);
        return true;
    }

    /**
     * Returns whether the given user has choosen to use 12 hour clock
     *
     * @param $userLogin
     * @return bool
     * @throws \Exception
     */
    public function uses12HourClock($userLogin)
    {
        if (CacheHelper::hasKey('use12hour_'.$userLogin)) {
            return CacheHelper::getValue('use12hour_' . $userLogin);
        }
        $data = (bool) Db::fetchOne('SELECT use_12_hour_clock FROM ' . $this->table .
            ' WHERE login = ? ', array($userLogin));
        CacheHelper::setValue('use12hour_'.$userLogin, $data);
        return $data;
    }

    /**
     * Sets whether the given user wants to use 12 hout clock
     *
     * @param string $login
     * @param string $use12HourClock
     * @return bool
     */
    public function set12HourClock($login, $use12HourClock)
    {
        if (QueryHelper::DEFAULT_SCHEMA == 'Mssql') {
            $query = 'BEGIN
			BEGIN
				UPDATE ' . $this->table . ' SET use_12_hour_clock = ? WHERE login = ?
			END
			IF NOT EXISTS (SELECT * FROM ' . $this->table . ' WHERE login = ?)
				BEGIN
					INSERT INTO ' . $this->table . ' (login, use_12_hour_clock) VALUES (?,?)
				END
		    END';
            $bind = array($use12HourClock, $login, $login, $login, $use12HourClock);
        } else {
            $query = 'INSERT INTO ' . $this->table .
                ' (login, use_12_hour_clock) VALUES (?,?) ON DUPLICATE KEY UPDATE use_12_hour_clock=?';
            $bind = array($login, $use12HourClock, $use12HourClock);
        }
        Db::query($query, $bind);
        CacheHelper::setValue('use12hour_' . $login, $use12HourClock);
        return true;
    }

    public static function install()
    {
        $userLanguage = "login NVARCHAR( 100 ) NOT NULL ,
					     language NVARCHAR( 10 ) NOT NULL ,
					     use_12_hour_clock INTEGER NOT NULL ,
					     PRIMARY KEY ( login )";
        // DbHelper::createTable(self::$rawPrefix, $userLanguage);
    }

    public static function uninstall()
    {
        Db::dropTables(Common::prefixTable(self::$rawPrefix));
    }
}
