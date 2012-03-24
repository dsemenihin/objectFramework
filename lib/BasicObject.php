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
    
    protected 
        $_objectFields = array(),
        $_storage,
        $_modifyFields = array();

    /**
     * @static
     * @param $id
     * @param null $storage
     * @return BasicObject
     * @throws Exception
     */
    static public function create($id = null, $storage = null) {
        if (is_null($storage)) {
            $storage = ObjectStorage::create(Config::$vars['defaultStorage']);
        } else if (!$storage instanceof ObjectStorage) {
            throw new Exception('Неизвестное хранилище');
        }
        
        $objectClass = get_called_class();
        $objectData = !is_null($id) ? $storage->loadObject($objectClass, $id) : array();
        $object = new $objectClass($objectData, $storage);
        
        if (is_null($id)) {
            foreach ($storage->getObjectSchema($objectClass) as $field => $data) {
                $object->{'set'.$field}($data['Default']);
            }
            $object->setOid(ObjectStorage::genId());
        }
        
        return $object;
    }
    
    /**
     *
     * @param type $id
     * @param ObjectStorage $storage 
     */
    protected function __construct($objectData, $storage) {
        foreach ($objectData as $key => $value) {
            $key = str_replace('_', '', $key);
            $this->_objectFields[$key] = $value;
        }
        
        $this->_storage = $storage;
    }
    
    public function __destruct() {
        if (count($this->_modifyFields)) {
            $this->_storage->saveObject($this);
        }
    }


    /**
     *
     * @param type $method
     * @param type $params 
     */
    public function __call($method, $params) {
        $poc = array();
        if (preg_match("|^get(.*)$|", $method, $poc)) {  
            $key = mb_strtolower($poc[1]);
            if (isset($this->_objectFields[$key])) {
                return $this->_objectFields[$key];
            }
        }
        
        if (preg_match("|^set(.*)$|", $method, $poc)) {  
            if (count($params) == 1) {
                $key = mb_strtolower($poc[1]);
                if (!isset($this->_objectFields[$key]) || 
                    (isset($this->_objectFields[$key]) && $this->_objectFields[$key] != $params[0])) {
                    $this->_modifyFields[$key] = true;
                }
                $this->_objectFields[$key] = $params[0];
                return;
            }
        }
        
        throw new Exception(get_class($this) . ': Не найден метод ' . $method);
    }
    
    public function getObjectFields() {
        return $this->_objectFields;
    }
    
}

?>
