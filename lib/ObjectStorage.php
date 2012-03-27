<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author dsemenihin
 */
abstract class ObjectStorage extends StorageAbstract {
    
    static protected 
        $_primaryKeyName = 'oid';
    
    protected 
        $_objectSchema = array(),
        $_saveObjectData = array(),
        $_debugMode = false;

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
    
    public function __destruct() {
        $this->_saveObjectData();
        if ($this->_debugMode) {
            var_dump($this->_getDebugInfo());
        }
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


    abstract public function loadObjectsById($collectionName, array $id);
    
    abstract public function initObject($collectionName);
    
    /**
     * Вернуть список id объектов по заданному условию
     * @param type $collectionName
     * @param array $criteria 
     */
    abstract public function getIdsByCriteria($collectionName, array $criteria);
    
    abstract protected function _saveObjectData();
    
    /**
     * Вернуть отладочную информацию 
     */
    abstract protected function _getDebugInfo();
        
    
}

?>
