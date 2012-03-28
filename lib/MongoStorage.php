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
     * �������� ������ �������� �� ���������� �����
     */
    protected function _loadObjectsById($collectionName, array $id) {
        // TODO: Implement _loadObjectsById() method.
    }

    /**
     * ������������� ������� �� �����.
     * �������� ����������� ����������
     */
    protected function _initObject($collectionName) {
        return array();
    }

    /**
     * ������� ������ id �������� �� ��������� �������
     * @param type $collectionName
     * @param array $criteria
     */
    public function getIdsByCriteria($collectionName, array $criteria) {
        // TODO: Implement getIdsByCriteria() method.
    }

    /**
     * ���������� ���������� ������ ��������.
     * ���������� �� ����������� ���������
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
     * ������� ���������� ����������
     */
    protected function _getDebugInfo() {
        // TODO: Implement _getDebugInfo() method.
    }

    protected function _connect($params) {
        $mongo = new Mongo();
        $this->_mongoDb = $mongo->selectDB($params['database']);
    }
}
