<?php

/**
 * Description of Basic
 *
 * @author dsemenihin
 */
abstract class BasicObject {
    
    protected 
        $_objectFields = array(),
        $_objectFieldsMap = array(),
        $_storage,
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
    static public function create($id = null, ObjectStorage $storage = null) {
        if (is_null($storage)) {
            $storage = ObjectStorage::create(Config::$vars['defaultStorage']);
        } 
        
        $objectClass = get_called_class();
        
        if (is_array($id)) {
            $objectData = $storage->loadObjectsById($objectClass, $id);
            $result = array();
            foreach ($objectData as $objectItem) {
                $result[] = new $objectClass($objectItem, $storage);
            }
            return $result;
        } else {
            if (is_null($id)) {
                $objectData = $storage->initObject($objectClass);
            } else {
                $objectData = !is_null($id) ? $storage->loadObjectsById($objectClass, array($id)) : array();
                $objectData = array_values($objectData);
                $objectData = isset($objectData[0]) ? $objectData[0] : array();
            }
            
            $object = new $objectClass($objectData, $storage);
            
            return $object;
        }
    }
    
    static public function getList($criteria = array(),  ObjectStorage $storage = null) {
        if (is_null($storage)) {
            $storage = ObjectStorage::create(Config::$vars['defaultStorage']);
        } 
        
        return new BasicObjectList($storage, get_called_class(), $criteria);
    }


    /**
     *
     * @param type $id
     * @param ObjectStorage $storage 
     */
    protected function __construct($objectData, ObjectStorage $storage = null) {
        $this->_storage = $storage;
        $this->setFields($objectData, false);
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
     * Метод преобразования иен полей во внутреннее представление
     * @param type $key
     * @return type 
     */
    final static private function _stripKey($key) {
        return str_replace('_', '', $key);
    }
    
    public function save() {
        $this->_storage->saveObject($this, true);
        $this->_modifyFields = array();
        return $this;
    }

        /**
     *
     * @param array $objectData 
     */
    public function setFields($objectData, $isNewValue = true) {
        foreach ($objectData as $key => $value) {
            $this->{'set'.$key}($value, $isNewValue);
        }
        
        return $this;
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
        $storageClass = get_class($this->_storage);
        if ($method == 'getid') {
            return $this->_objectFields[$storageClass::getPrimaryKeyName()];
        }
        
        if ($method == 'setid') {
            $this->_objectFields[$storageClass::getPrimaryKeyName()] = $params[0];
            return $this;
        }
        
        if (preg_match("|^get(.*)$|", $method, $poc)) {  
            $key = $poc[1];
            if (isset($this->_objectFieldsMap[$key])) {
                return $this->_objectFields[$this->_objectFieldsMap[$key]];
            }
        }
        
        if (preg_match("|^set(.*)$|", $method, $poc)) {  
            $innerKey = $poc[1];
            $key = isset($this->_objectFieldsMap[$innerKey]) ? $this->_objectFieldsMap[$innerKey] : $innerKey;
            if (!isset($this->_objectFields[$key]) || 
                (isset($this->_objectFields[$key]) 
                    && $this->_objectFields[$key] != $params[0])) {
                
                if (!isset($params[1]) || !empty($params[1])) {
                    $this->_modifyFields[$key] = true;
                }
            }
            $this->_objectFields[$key] = $params[0];
            $this->_objectFieldsMap[self::_stripKey($key)] = $key;
            return $this;
        }
        
        throw new Exception(get_class($this) . ': Не найден метод ' . $method);
    }
    
    public function getObjectFields() {
        return $this->_objectFields;
    }
    
}

?>
