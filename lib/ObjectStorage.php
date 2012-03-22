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
    
    protected function __construct($params) {
        $this->_connect($params);
    }
    
    abstract public function loadObject($collectionName, $id);
    
    abstract public function saveObject($collectionName, $object);
        
    
}

?>
