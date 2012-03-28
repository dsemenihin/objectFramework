<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dsemenihin
 * Date: 28.03.12
 * Time: 16:38
 * To change this template use File | Settings | File Templates.
 */
class MongoStorage extends ObjectStorage {
    protected
        /**
         * @var MongoDB
         */
        $_mongoDb;

    /**
     * Загрузка данных объектов по первичному ключу
     */
    protected function _loadObjectsById($collectionName, array $id) {
        // TODO: Implement _loadObjectsById() method.
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
        // TODO: Implement getIdsByCriteria() method.
    }

    /**
     * Реализация сохранения данных объектов.
     * Вызывается из деструктора хранилища
     */
    protected function _saveObjectData() {
        foreach ($this->_saveObjectData as $collectionName => $objects) {
            $collection = $this->_mongoDb->selectCollection('$collectionName');
            foreach ($objects as $oid => $data) {
                $collection->save($data);
            }
        }
    }

    /**
     * Вернуть отладочную информацию
     */
    protected function _getDebugInfo() {
        // TODO: Implement _getDebugInfo() method.
    }

    protected function _connect($params) {
        $mongo = new Mongo();
        $this->_mongoDb = $mongo->selectDB($params['database']);
    }
}
