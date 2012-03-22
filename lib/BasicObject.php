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

    /**
     * @static
     * @param $id
     * @param null $storage
     * @return BasicObject
     * @throws Exception
     */
    static public function create($id, $storage = null) {
        if (is_null($storage)) {
            $storage = ObjectStorage::create(Config::$vars['defaultStorage']);
        } else if (!$storage instanceof ObjectStorage) {
            throw new Exception('Неверное хранилище');
        }
        
        eval('$object = new '.get_called_class().'($id, $storage);');
        return $object;
    }
    
    /**
     *
     * @param type $id
     * @param ObjectStorage $storage 
     */
    protected function __construct($id, $storage) {
        $object = $storage->loadObject(get_called_class(), $id);
    }
    
}

?>
