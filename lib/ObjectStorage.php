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
    static public function create($storageName) {
        if (!isset(Config::$vars[$storageName]['adapter']) 
                || !isset(Config::$vars[$storageName]['connectParams'])) {
            throw new Exception('Неизвестное хранилище объектов');
        }
        
        if (!class_exists(Config::$vars[$storageName]['adapter'])) {
            throw new Exception('Нет класса хранилища');
        }
        
        return new Config::$vars[$storageName]['adapter'](Config::$vars[$storageName]['connectParams']);
    }
    
    protected function __construct($params) {
        $this->_connect($params);
    }
    
}

?>
