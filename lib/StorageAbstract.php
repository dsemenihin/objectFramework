<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StorageAbstract
 *
 * @author dsemenihin
 */
abstract class StorageAbstract {
    static protected $_storageCache = array();

        /**
     * @static
     * @param $storageName
     * @return ObjectStorage
     * @throws Exception
     */
    static public function create($storageName) {
        if (!isset(self::$_storageCache[$storageName])) {
            $storageConfig = Config::$vars['storages'][$storageName];
            if (!isset($storageConfig['adapter'])) {
                throw new Exception('Неизвестное хранилище объектов '. $storageName);
            }
            
            if (!isset($storageConfig['connectParams'])) {
                throw new Exception('Нет данных подключения к хранилищу '. $storageName);
            }

            if (!class_exists($storageConfig['adapter'])) {
                throw new Exception('Нет класса хранилища');
            }
            
            $debug = isset($storageConfig['debug']) 
                ? $storageConfig['debug']
                : false;

            self::$_storageCache[$storageName] =
                new $storageConfig['adapter']($storageConfig['connectParams'], $debug);
        }

        return self::$_storageCache[$storageName];
    }
    
    protected function __construct($params, $debug = false) {
        $this->_debugMode = (bool) $debug;
        $this->_connect($params);
    }
    
    abstract protected function _connect($params);
}

?>
