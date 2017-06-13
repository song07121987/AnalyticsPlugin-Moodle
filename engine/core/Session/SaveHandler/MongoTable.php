<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Session\SaveHandler;

use Piwik\Db;
use Zend_Session;
use Zend_Session_SaveHandler_Interface;
use Piwik\Db\QueryHelper;

/**
 * Database-backed session save handler
 *
 */
class MongoTable implements Zend_Session_SaveHandler_Interface
{
    protected $config;
    protected $maxLifetime;

    /**
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->maxLifetime = ini_get('session.gc_maxlifetime');
    }

    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct()
    {
        Zend_Session::writeClose();
    }

    /**
     * Open Session - retrieve resources
     *
     * @param string $save_path
     * @param string $name
     * @return boolean
     */
    public function open($save_path, $name)
    {
        Db::get()->getConnection();

        return true;
    }

    /**
     * Close Session - free resources
     *
     * @return boolean
     */
    public function close()
    {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        $sql = 'SELECT ' . $this->config['dataColumn'] . ' FROM ' . $this->config['name']
            . ' WHERE ' . $this->config['primary'] . ' = ?'
            . ' AND ' . $this->config['modifiedColumn'] . ' + ' . $this->config['lifetimeColumn'] . ' >= ?';

        $result = Db::get()->fetchOne($sql, array($id, time()));
        if (!$result) {
            $result = '';
        }

        return $result;
    }

    /**
     * Write Session - commit data to resource
     *
     * @param string $id
     * @param mixed $data
     * @return boolean
     */
    public function write($id, $data)
    {
        if (QueryHelper::DEFAULT_SCHEMA == 'Mssql') {
            $sql = '
            BEGIN
                BEGIN
                    UPDATE ' . $this->config['name'] . ' SET '
                    . $this->config['modifiedColumn'] . ' = ?,'
                    . $this->config['lifetimeColumn'] . ' = ?,'
                    . $this->config['dataColumn'] . ' = ?  WHERE ' . $this->config['primary'] . ' = ?
                END
				IF NOT EXISTS (SELECT * FROM ' . $this->config['name'] . ' WHERE ' . $this->config['primary'] . ' = ?)
				BEGIN
					INSERT INTO ' . $this->config['name']
                . ' (' . $this->config['primary'] . ','
                . $this->config['modifiedColumn'] . ','
                . $this->config['lifetimeColumn'] . ','
                . $this->config['dataColumn'] . ')'
                . ' VALUES (?,?,?,?)
				END
			END';
            Db::get()->query($sql, array(time(), $this->maxLifetime, $data, $id, $id, $id, time(), $this->maxLifetime, $data));
        } else {

            $sql = 'INSERT INTO ' . $this->config['name']
                . ' (' . $this->config['primary'] . ','
                . $this->config['modifiedColumn'] . ','
                . $this->config['lifetimeColumn'] . ','
                . $this->config['dataColumn'] . ')'
                . ' VALUES (?,?,?,?)'
                . ' ON DUPLICATE KEY UPDATE '
                . $this->config['modifiedColumn'] . ' = ?,'
                . $this->config['lifetimeColumn'] . ' = ?,'
                . $this->config['dataColumn'] . ' = ?';
            Db::get()->query($sql, array($id, time(), $this->maxLifetime, $data, time(), $this->maxLifetime, $data));
        }

        return true;
    }

    /**
     * Destroy Session - remove data from resource for
     * given session id
     *
     * @param string $id
     * @return boolean
     */
    public function destroy($id)
    {
        $sql = 'DELETE FROM ' . $this->config['name'] . ' WHERE ' . $this->config['primary'] . ' = ?';

        Db::get()->query($sql, array($id));

        return true;
    }

    /**
     * Garbage Collection - remove old session data older
     * than $maxlifetime (in seconds)
     *
     * @param int $maxlifetime timestamp in seconds
     * @return bool  always true
     */
    public function gc($maxlifetime)
    {
        $sql = 'DELETE FROM ' . $this->config['name']
            . ' WHERE ' . $this->config['modifiedColumn'] . ' + ' . $this->config['lifetimeColumn'] . ' < ?';

        Db::get()->query($sql, array(time()));

        return true;
    }
}
