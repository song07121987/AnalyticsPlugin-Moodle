<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker\Db\Pdo;

use Exception;
use PDO;
use Piwik\Db\QueryHelper;

/**
 * PDO PostgreSQL wrapper
 *
 */
class Mssql extends Mysql
{

    /**
     * Builds the DB object
     *
     * @param array $dbInfo
     * @param string $driverName
     */
    public function __construct($dbInfo, $driverName = 'mssql')
    {
        parent::__construct($dbInfo, $driverName);

        $serverName = $dbInfo["host"];
        $database   = $dbInfo["dbname"];
        if (is_null($database)) {
            $database = 'master';
        }
        $this->dsn = "sqlsrv:server=$serverName;Database=$database";
        $this->charset = '';
    }

    /**
     * Connects to the DB
     *
     * @throws Exception if there was an error connecting the DB
     */
    public function connect()
    {
        if (self::$profiling) {
            $timer = $this->initProfiler();
        }


        // $this->_connection = new PDO("sqlsrv:$serverName", $uid, $pwd, array('Database' => $database));
        // $constr = "sqlsrv:server=$serverName;Database=$database";
        // print_r ($constr);
        if ($this->username == "") {
            $this->connection = new PDO($this->dsn);
        } else {
        $this->connection = new PDO($this->dsn, $this->username, $this->password, $config = array());
        }
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // we may want to setAttribute(PDO::ATTR_TIMEOUT ) to a few seconds (default is 60) in case the DB is locked
        // the piwik.php would stay waiting for the database... bad!
        // we delete the password from this object "just in case" it could be printed
        $this->password = '';

        if (!empty($this->charset)) {
            $sql = "SET NAMES '" . $this->charset . "'";
            $this->connection->exec($sql);
        }

        if (self::$profiling && isset($timer)) {
            $this->recordQueryProfile('connect', $timer);
        }
    }

    /**
     * Disconnects from the server
     */
    public function disconnect()
    {
        $this->connection = null;
    }

    /**
     * Returns an array containing all the rows of a query result, using optional bound parameters.
     *
     * @param string $query Query
     * @param array $parameters Parameters to bind
     * @return array|bool
     * @see query()
     * @throws Exception|DbException if an exception occurred
     */
    public function fetchAll($query, $parameters = array())
    {
        try {
            $sth = $this->query($query, $parameters);
            if ($sth === false) {
                return false;
            }
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DbException("Error query: " . $e->getMessage());
        }
    }

    /**
     * Fetches the first column of all SQL result rows as an array.
     *
     * @param string $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @throws \Piwik\Tracker\Db\DbException
     * @return string
     */
    public function fetchCol($sql, $bind = array())
    {
        try {
            $sth = $this->query($sql, $bind);
            if ($sth === false) {
                return false;
            }
            $result = $sth->fetchAll(PDO::FETCH_COLUMN, 0);
            return $result;
        } catch (PDOException $e) {
            throw new DbException("Error query: " . $e->getMessage());
        }
    }

    /**
     * Returns the first row of a query result, using optional bound parameters.
     *
     * @param string $query Query
     * @param array $parameters Parameters to bind
     * @return bool|mixed
     * @see query()
     * @throws Exception|DbException if an exception occurred
     */
    public function fetch($query, $parameters = array())
    {
        try {
	    ///	$query=str_replace("LIMIT 1","OFFSET 0 ROWS FETCH NEXT 1 ROWS ONLY",$query);
	    //echo $query;
            $sth = $this->query($query, $parameters);
            if ($sth === false) {
                return false;
            }
            return $sth->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DbException("Error query: " . $e->getMessage());
        }
    }

    /**
     * Executes a query, using optional bound parameters.
     *
     * @param string $query Query
     * @param array|string $parameters Parameters to bind array('idsite'=> 1)
     * @return PDOStatement|bool  PDOStatement or false if failed
     * @throws DbException if an exception occured
     */
    public function query($query, $parameters = array(), $ignore = false)
    {
        $query = $this->cleanQuery($query);
        if (is_null($this->connection)) {
            return false;
        }

        try {
            if (self::$profiling) {
                $timer = $this->initProfiler();
            }

            if (!is_array($parameters)) {
                $parameters = array($parameters);
            }
            $sth = $this->connection->prepare($query);
            $sth->execute($parameters);

            if (self::$profiling && isset($timer)) {
                $this->recordQueryProfile($query, $timer);
            }
            return $sth;
        } catch (PDOException $e) {
            $message = $e->getMessage() . " In query: $query Parameters: " . var_export($parameters, true);
            throw new DbException("Error query: " . $message, (int) $e->getCode());
        }
    }

    /**
     * Returns the last inserted ID in the DB
     * Wrapper of PDO::lastInsertId()
     * 
     * @param  String $sequenceCol Column on which the sequence is created.
     *         Pertinent for DBMS that use sequences instead of auto_increment.
     *         Zend adapter appends the "_seq" which has to be repeated here, to
     *         avoid passing different values for the argument based on which
     *         adaper (zend or piwik_tracker_db) is being used.
     * @return int
     */
    public function lastInsertId()
    {
	//changed for mssql supprt permanenty
	//This method may not return a meaningful or consistent result across different PDO drivers, because the underlying database may not even support the notion of auto-increment fields or sequences.
	// return $this->connection->lastInsertId($sequenceCol);
	  
	$sql = 'SELECT @@IDENTITY';        
	return (int)$this->fetchOne($sql);
	   
    }

    /**
     * Test error number
     *
     * @param Exception $e
     * @param string $errno
     * @return bool
     */
    public function isErrNo($e, $errno)
    {
        // map MySQL driver-specific error codes to PostgreSQL SQLSTATE
        $map = array(
            // MySQL: Unknown database '%s'
            // PostgreSQL: database "%s" does not exist
            '1049' => '08006',

            // MySQL: Table '%s' already exists
            // PostgreSQL: relation "%s" already exists
            '1050' => '42P07',

            // MySQL: Unknown column '%s' in '%s'
            // PostgreSQL: column "%s" does not exist
            '1054' => '42703',

            // MySQL: Duplicate column name '%s'
            // PostgreSQL: column "%s" of relation "%s" already exists
            '1060' => '42701',

            // MySQL: Duplicate entry '%s' for key '%s'
            // PostgreSQL: duplicate key violates unique constraint
            '1062' => '23505',

            // MySQL: Can't DROP '%s'; check that column/key exists
            // PostgreSQL: index "%s" does not exist
            '1091' => '42704',

            // MySQL: Table '%s.%s' doesn't exist
            // PostgreSQL: relation "%s" does not exist
            '1146' => '42P01',
        );

        if (preg_match('/([0-9]{2}[0-9P][0-9]{2})/', $e->getMessage(), $match)) {
            return ($match[1] == $errno) || ($match[1] == $map[$errno]);
        }
        return false;
    }

    /**
     * Return number of affected rows in last query
     *
     * @param mixed $queryResult Result from query()
     * @return int
     */
    public function rowCount($queryResult)
    {
        return $queryResult->rowCount();
    }
    
    /**
     *  Quote Identifier
     *
     *  Not as sophisiticated as the zend-db quoteIdentifier. Just encloses the
     *  given string in backticks and returns it.
     *
     *  @param string $identifier
     *  @return string
     */
    public function quoteIdentifier($identifier)
    {
        return '[' . $identifier . ']';
    }

    /*
     * Start Transaction
     * @return string TransactionID
     */
    public function beginTransaction()
    {
        if (!$this->activeTransaction === false) {
            return;
        }

        if ($this->connection->beginTransaction()) {
            $this->activeTransaction = uniqid();
            return $this->activeTransaction;
        }
    }

    /**
     * Commit Transaction
     * @param $xid
     * @throws DbException
     * @internal param TransactionID $string from beginTransaction
     */
    public function commit($xid)
    {
        if ($this->activeTransaction != $xid || $this->activeTransaction === false) {
            return;
        }

        $this->activeTransaction = false;

        if (!$this->connection->commit()) {
            throw new DbException("Commit failed");
        }
    }

    /**
     * Rollback Transaction
     * @param $xid
     * @throws DbException
     * @internal param TransactionID $string from beginTransaction
     */
    public function rollBack($xid)
    {
        if ($this->activeTransaction != $xid || $this->activeTransaction === false) {
            return;
        }

        $this->activeTransaction = false;

        if (!$this->connection->rollBack()) {
            throw new DbException("Rollback failed");
        }
    }
    
    public function cleanQuery ($sql) {
        return QueryHelper::cleanQuery($sql);
    }

}
