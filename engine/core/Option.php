<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Config\ClientConfig;
use Piwik\Custom\CacheHelper;
use Piwik\Db\QueryHelper;
use Piwik\Cache;
use Piwik\Custom;

/**
 * Convenient key-value storage for user specified options and temporary
 * data that needs to be persisted beyond one request.
 *
 * ### Examples
 *
 * **Setting and getting options**
 *
 *     $optionValue = Option::get('MyPlugin.MyOptionName');
 *     if ($optionValue === false) {
 *         // if not set, set it
 *         Option::set('MyPlugin.MyOptionName', 'my option value');
 *     }
 *
 * **Storing user specific options**
 *
 *     $userName = // ...
 *     Option::set('MyPlugin.MyOptionName.' . $userName, 'my option value');
 *
 * **Clearing user specific options**
 *
 *     Option::deleteLike('MyPlugin.MyOptionName.%');
 *
 * @api
 */
class Option
{

    /**
     * Returns the option value for the requested option `$name`.
     *
     * @param string $name The option name.
     * @return string|false The value or `false`, if not found.
     */
    public static function get($name)
    {
        return self::getInstance()->getValue($name);
    }

    /**
     * Returns option values for options whose names are like a given pattern.
     *
     * @param string $namePattern The pattern used in the SQL `LIKE` expression
     *                            used to SELECT options.
     * @return array Array mapping option names with option values.
     */
    public static function getLike($namePattern)
    {
        return self::getInstance()->getNameLike($namePattern);
    }

    /**
     * Sets an option value by name.
     *
     * @param string $name The option name.
     * @param string $value The value to set the option to.
     * @param int $autoLoad If set to 1, this option value will be automatically loaded when Piwik is initialzed;
     *                      should be set to 1 for options that will be used in every Piwik request.
     */
    public static function set($name, $value, $autoload = 0)
    {
        self::getInstance()->setValue($name, $value, $autoload);
    }

    /**
     * Deletes an option.
     *
     * @param string $name Option name to match exactly.
     * @param string $value If supplied the option will be deleted only if its value matches this value.
     */
    public static function delete($name, $value = null)
    {
        self::getInstance()->deleteValue($name, $value);
    }

    /**
     * Deletes all options that match the supplied pattern.
     *
     * @param string $namePattern Pattern of key to match. `'%'` characters should be used as wildcards, and literal
     *                            `'_'` characters should be escaped.
     * @param string $value If supplied, options will be deleted only if their value matches this value.
     */
    public static function deleteLike($namePattern, $value = null)
    {
        self::getInstance()->deleteNameLike($namePattern, $value);
    }

    public static function clearCachedOption($name)
    {
        self::getInstance()->clearCachedOptionByName($name);
    }

    /**
     * Clears the option value cache and forces a reload from the Database.
     * Used in unit tests to reset the state of the object between tests.
     *
     * @return void
     * @ignore
     */
    public static function clearCache()
    {
        $option = self::getInstance();
        $option->loaded = false;
        $option->all = array();
    }

    /**
     * @var array
     */
    private $all = array();

    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * Singleton instance
     * @var \Piwik\Option
     */
    private static $instance = null;

    /**
     * Returns Singleton instance
     *
     * @return \Piwik\Option
     */
    private static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Sets the singleton instance. For testing purposes.
     *
     * @param mixed
     * @ignore
     */
    public static function setSingletonInstance($instance)
    {
        self::$instance = $instance;
    }

    /**
     * Private Constructor
     */
    private function __construct()
    {
    }

    protected function clearCachedOptionByName($name)
    {
        if (isset($this->all[$name])) {
            unset($this->all[$name]);
        }
        CacheHelper::deleteValue('option_'.$name);
    }

    protected function getValue($name)
    {
        if (CacheHelper::hasKey('option_'.$name))
            return CacheHelper::getValue('option_'.$name);
        $this->autoload();
        if (isset($this->all[$name])) {
            CacheHelper::setValue('option_'.$name, $this->all[$name]);
            return $this->all[$name];
        }

        $value = Db::fetchOne('SELECT option_value FROM `' . Common::prefixTable('option') . '` ' .
                              'WHERE option_name = ?', $name);

        if ($value === false) {
            CacheHelper::setValue('option_' . $name, false);
            return false;
        }

        $this->all[$name] = $value;
        CacheHelper::setValue('option_' . $name, $value);
        return $value;
    }

    protected function setValue($name, $value, $autoLoad = 0)
    {
        $autoLoad = (int)$autoLoad;

        if (QueryHelper::DEFAULT_SCHEMA == 'Mssql') {
            $sql = '
            BEGIN
                BEGIN
                    UPDATE ' . Common::prefixTable('option') . ' SET option_value = ? WHERE option_name = ? and autoload = ?
                END
                IF NOT EXISTS (SELECT * FROM ' . Common::prefixTable('option') . ' WHERE option_name = ? and autoload = ? )
                    BEGIN
                        INSERT INTO ' . Common::prefixTable('option') .' (option_name, option_value, autoload) VALUES (?, ?, ?)
                    END
		    END';
              $bind = array($value, $name, $autoLoad, $name, $autoLoad, $name, $value, $autoLoad);
        } else {
            $sql = 'INSERT INTO `' . Common::prefixTable('option') . '` (option_name, option_value, autoload) ' .
                ' VALUES (?, ?, ?) ' .
                ' ON DUPLICATE KEY UPDATE option_value = ?';
            $bind = array($name, $value, $autoLoad, $value);
        }

        Db::query($sql, $bind);
        CacheHelper::setValue('option_' . $name, $value);
        $this->all[$name] = $value;
    }

    protected function deleteValue($name, $value)
    {
        $sql    = 'DELETE FROM `' . Common::prefixTable('option') . '` WHERE option_name = ?';
        $bind[] = $name;

        if (isset($value)) {
            $sql   .= ' AND option_value = ?';
            $bind[] = $value;
        }

        Db::query($sql, $bind);

        CacheHelper::deleteValue('option_' . $name);
        $this->clearCache();
    }

    protected function deleteNameLike($name, $value = null)
    {
        $sql    = 'DELETE FROM `' . Common::prefixTable('option') . '` WHERE option_name LIKE ?';
        $bind[] = $name;

        if (isset($value)) {
            $sql   .= ' AND option_value = ?';
            $bind[] = $value;
        }

        Db::query($sql, $bind);

        $this->clearCache();
        CacheHelper::flush();
    }

    protected function getNameLike($name)
    {
        $sql  = 'SELECT option_name, option_value FROM `' . Common::prefixTable('option') . '` WHERE option_name LIKE ?';
        $bind = array($name);
        $rows = Db::fetchAll($sql, $bind);

        $result = array();
        foreach ($rows as $row) {
            $result[$row['option_name']] = $row['option_value'];
        }

        return $result;
    }

    /**
     * Initialize cache with autoload settings.
     *
     * @return void
     */
    protected function autoload()
    {
        if ($this->loaded) {
            return;
        }
        if (CacheHelper::getValue('option_autoload') == null)
        {
            $table = Common::prefixTable('option');
            $sql = 'SELECT option_value, option_name FROM `' . $table . '` WHERE autoload = 1';
            $all = Db::fetchAll($sql);

            foreach ($all as $option) {
                $this->all[$option['option_name']] = $option['option_value'];
                CacheHelper::setValue($option['option_name'], $option['option_value'], false);
            }
            CacheHelper::setValue('autoload', true);
            $this->loaded = true;
        }
    }
}