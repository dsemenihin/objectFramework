<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author dsemenihin
 */
abstract class ObjectStorage {
    
    static protected 
        $_primaryKeyName = 'oid',
        $_storageCache = array();
    
    protected 
        $_objectSchema = array(),
        $_saveObjectData = array();

    /**
     * @static
     * @param $storageName
     * @return ObjectStorage
     * @throws Exception
     */
    static public function create($storageName) {
        if (!isset(self::$_storageCache[$storageName])) {
            if (!isset(Config::$vars[$storageName]['adapter'])) {
                throw new Exception('Неизвестное хранилище объектов '. $storageName);
            }
            
            if (!isset(Config::$vars[$storageName]['connectParams'])) {
                throw new Exception('Нет данных подключения к хранилищу '. $storageName);
            }

            if (!class_exists(Config::$vars[$storageName]['adapter'])) {
                throw new Exception('Нет класса хранилища');
            }

            self::$_storageCache[$storageName] =
                new Config::$vars[$storageName]['adapter'](Config::$vars[$storageName]['connectParams']);
        }

        return self::$_storageCache[$storageName];
    }
    
    
    static public function getPrimaryKeyName() {
        return self::$_primaryKeyName;
    }


    /**
     * Генерирование уникального ID для нового объекта
     * @return bigint
     */
    public static function genId() {
        list($usec, $sec) = explode(" ", microtime());
        $start = date('U', strtotime('2000-01-01 00:00:00'));
        $p = rand(1, 1000);
        $sec = $sec - $start;
        $sec = bcadd($sec, $usec, 5);
        $sec = bcmul($sec, 100000, 0);
        $sec = bcmul($sec, 100000, 0);
        $pp = bcadd($sec, $p, 0);
        return $pp;
    }
    
    protected function __construct($params) {
        $this->_connect($params);
    }
    
    public function __destruct() {
        $this->_saveObjectData();
    }
    
    /**
     *
     * @param BasicObject $object
     * @throws Exception 
     */
    public function saveObject(BasicObject $object) {
        $storageClass = get_called_class();
        $this->_saveObjectData[get_class($object)][$object->{'get'.$storageClass::$_primaryKeyName}()] = 
            $object->getObjectFields();
    }


    abstract public function loadObject($collectionName, $id);
    
    abstract public function initObject($collectionName);
    
    abstract protected function _saveObjectData();
        
    
}

?>
