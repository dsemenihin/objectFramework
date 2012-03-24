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
    /**
     * @var array
     */
    static protected $_factoryCache = array();
    
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
        if (!isset(self::$_factoryCache[$storageName])) {
            if (!isset(Config::$vars[$storageName]['adapter'])) {
                throw new Exception('Неизвестное хранилище объектов '. $storageName);
            }
            
            if (!isset(Config::$vars[$storageName]['connectParams'])) {
                throw new Exception('Нет данных подключения к хранилищу '. $storageName);
            }

            if (!class_exists(Config::$vars[$storageName]['adapter'])) {
                throw new Exception('Нет класса хранилища');
            }

            self::$_factoryCache[$storageName] =
                new Config::$vars[$storageName]['adapter'](Config::$vars[$storageName]['connectParams']);
        }

        return self::$_factoryCache[$storageName];
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
    
    public function saveObject($object) {
        if (!$object instanceof BasicObject) {
            throw new Exception('Не тот объект');
        }
        $this->_saveObjectData[get_class($object)][$object->getOid()] = $object->getObjectFields();
    }


    abstract public function loadObject($collectionName, $id);
    
    abstract public function getObjectSchema($collectionName);
    
    abstract protected function _saveObjectData();
        
    
}

?>
