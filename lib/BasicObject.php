<?php

/**
 * Description of Basic
 *
 * @author dsemenihin
 */
abstract class BasicObject {
    
    protected 
        $_objectFields = array(),
        $_storage,
        $_cache,
        $_primaryKeyName,
        $_modifyFields = array();
    
    /**
     * @static
     * @param $id
     * @param ObjectStorage $storage
     * @param ObjectCache $cache
     * @return BasicObject
     * @throws Exception
     */
    static public function create($id = null, ObjectStorage $storage = null, ObjectCache $cache = null) {
        if (is_null($cache)) {
            $cache = ObjectCache::create(Config::$vars['defaultCache']);
        } 
        
        if (is_null($storage)) {
            $storage = ObjectStorage::create(Config::$vars['defaultStorage']);
        } 
        
        $objectClass = get_called_class();
        
        if (is_array($id)) {
            $objectData = $storage->loadObjectsById($objectClass, $id);
            $result = array();
            foreach ($objectData as $objectItem) {
                $result[] = new $objectClass($objectItem, $storage, $cache);
            }
            return $result;
        } else {
            $objectData = !is_null($id) ? $storage->loadObjectsById($objectClass, array($id)) : array();
            $object = new $objectClass(isset($objectData[0]) ? $objectData[0] : array(), $storage, $cache);
            if (is_null($id)) {
                foreach ($storage->initObject($objectClass) as $field => $value) {
                    $object->{'set'.$field}($value);
                }
            }
            return $object;
        }
    }
    
    /**
     *
     * @param type $id
     * @param ObjectStorage $storage 
     */
    protected function __construct($objectData, ObjectStorage $storage = null, ObjectCache $cache) {
        $this->setFields($objectData);
        $this->_cache = $cache;
        $this->_storage = $storage;
    }
    
    /**
     * 
     */
    public function __destruct() {
        if (count($this->_modifyFields)) {
            $this->_storage->saveObject($this);
        }
    }
    
    /**
     *
     * @param array $objectData 
     */
    public function setFields($objectData) {
        foreach ($objectData as $key => $value) {
            $key = str_replace('_', '', $key);
            $this->_objectFields[$key] = $value;
        }
    }

    /**
     *
     * @param string $method
     * @param array $params 
     */
    public function __call($method, $params) {
        $poc = array();
        if (preg_match("|^get(.*)ObjectList$|", $method, $poc)) {
            $listClass = $poc[1].'ObjectList';
            return new $listClass($this->_storage, $poc[1].'Object', 
                !empty($params[0]) ? $params[0] : array(), $this); 
        }
        
        $method = mb_strtolower($method);
        if ($method == 'getid') {
            $storageClass = get_class($this->_storage);
            return $this->_objectFields[$storageClass::getPrimaryKeyName()];
        }
        
        if (preg_match("|^get(.*)$|", $method, $poc)) {  
            $key = $poc[1];
            if (isset($this->_objectFields[$key])) {
                return $this->_objectFields[$key];
            }
        }
        
        if (preg_match("|^set(.*)$|", $method, $poc)) {  
            $key = $poc[1];
            if (count($params) == 1) {
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
