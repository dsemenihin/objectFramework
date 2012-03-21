<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Basic
 *
 * @author dsemenihin
 */
abstract class BasicObject {
    
    static protected $_defaultStorage;
    
    static public function create($id, $storage = null) {
        if (is_null($storage)) {
            if (empty(self::$_defaultStorage)) {                
                $storage = ObjectStorage::create(Config::$vars['defaultStorage']);
                self::$_defaultStorage = $storage;
            } else {
                $storage = self::$_defaultStorage;
            }
        } else if (!$storage instanceof ObjectStorage) {
            throw new Exception('Неверное хранилище');
        }
        
        eval('$obj = new '.get_called_class().'($id, $storage);');
        return $obj;
    }
    
    protected function __construct($id, $storage) {
        var_dump(get_called_class(), $storage);
    }
    
}

?>
