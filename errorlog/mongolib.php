<?php
require_once('../../../config-analytics.php');

class MongoAnalyticsDB {
    public static $database = null;
    public static $connection = null;
    public static $findTimeout = 20000;
    protected $collection = null;

    public $collectionName = '';

    public function __construct($cName)
    {
        $this->collectionName = $cName;
        if (self::$connection == null) {
            global $CFG;
            self::$connection = new MongoClient($CFG->mongoHost);
            self::$database = self::$connection->selectDB('analytics');
        }
        $this->collection = self::$database->selectCollection($this->collectionName);
    }

    public function update (array $criteria, array $attributes, array $options = array()) {
        $this->collection->update($criteria, $attributes, $options);
        return true;
    }

    public function save(array $attributes, array $options = array())
    {
        $this->collection->save($attributes, $options);
        return true;
    }

    public function delete($id)
    {
        $this->collection->remove(array('_id' => $id));
        return true;
    }

    private function _find($query = array(), $options = array())
    {
        if (isset($options['fields'])){
            $documents = $this->collection->find($query, $options['fields']);
        }
        else{
            $documents = $this->collection->find($query);
        }

        if (isset($options['sort']))
            $documents->sort($options['sort']);

        if (isset($options['offset']))
            $documents->skip($options['offset']);

        if (isset($options['limit']))
            $documents->limit($options['limit']);

        $documents->timeout(self::$findTimeout);
        return $documents;
    }

    public function findAll ($query = array(), $options = array())
    {
        $documents = $this->_find($query, $options);
        $ret = array();
        while ($documents->hasNext())
        {
            $document = $documents->getNext();
            $ret[] = $document;
        }
        return $ret;
    }

    public function findAndModify (array $query, $update = array(), $fields = array(), $options = array())
    {
        $retValue = $this->collection->findAndModify($query, $update, $fields, $options);
        return $retValue;
    }


    public function find($query = array(), $options = array())
    {
        $documents = $this->_find($query, $options);
        return $documents;
    }

    public function findOne($query = array(), $options = array())
    {
        return $this->collection->findOne($query, $options);
    }

    public function count($query = array())
    {
        $documents = $this->collection->count($query);
        return $documents;
    }

    public function getNextSequence () {

        $retval = self::$database->selectCollection('counters')->findAndModify (
            array ('_id' => $this->collectionName),
            array ('$inc' => array ('seq' => 1)),
            null,
            array ('upsert' => true, 'new' => true)
        );
        return $retval['seq'];

    }
}

