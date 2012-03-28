<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dsemenihin
 * Date: 28.03.12
 * Time: 16:38
 * To change this template use File | Settings | File Templates.
 */
class MongoStorage extends ObjectStorage {
    static protected 
        $_primaryKeyName = '_id';
    
    protected
        /**
         * @var MongoDB
         */
        $_mongoDb,
        $_debugInfo = array();

    /**
     * Загрузка данных объектов по первичному ключу
     */
    protected function _loadObjectsById($collectionName, array $id) {
        $result = iterator_to_array($this->_mongoDb->$collectionName
            ->find(array(self::getPrimaryKeyName() => array('$in' => $id))));
        
        $this->_debugInfo[] = 'load '.$collectionName.' by id: '. implode(', ', $id);
        return $result;
    }

    /**
     * Инициализация объекта из схемы.
     * Значения заполняются дефолтными
     */
    protected function _initObject($collectionName) {
        return array();
    }

    /**
     * Вернуть список id объектов по заданному условию
     * @param type $collectionName
     * @param array $criteria
     */
    public function getIdsByCriteria($collectionName, array $criteria) {
        $result = iterator_to_array($this->_mongoDb->$collectionName
            ->find($criteria));
        
        $this->_debugInfo[] = 'load '.$collectionName.' by criteria: '.  var_export($criteria, true);
        
        return array_keys($result);
    }

    /**
     * Реализация сохранения данных объектов.
     * Вызывается из деструктора хранилища
     */
    protected function _saveObjectData(BasicObject $object = null) {
        if (!empty($object)) {
            $collectionName = get_class($object);
            $collection = $this->_mongoDb->selectCollection($collectionName);
            $collection->save($object->getObjectFields());
            unset($this->_saveObjectData[$collectionName][$object->getId()]);
            
            $this->_debugInfo[] = 'save '.$collectionName.': _id='.$object->getId();
            return;
        }
        
        foreach ($this->_saveObjectData as $collectionName => $objects) {
            $collection = $this->_mongoDb->selectCollection($collectionName);
            foreach ($objects as $oid => $data) {
                $collection->save($data);
                
                $this->_debugInfo[] = 'save '.$collectionName.': _id='.$oid;
            }
        }
    }

    /**
     * Вернуть отладочную информацию
     */
    protected function _getDebugInfo() {
        return $this->_debugInfo;
    }

    protected function _connect($params) {
        $mongo = new Mongo();
        $this->_mongoDb = $mongo->selectDB($params['database']);
    }
}
