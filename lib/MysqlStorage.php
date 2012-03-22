<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MysqlStorage
 *
 * @author dsemenihin
 */
class MysqlStorage extends ObjectStorage {
    protected 
        $_connectParams,
        /**
         * Ресурс базы
         * @var PDOStatement 
         */
        $_dbh;
    
    protected function _connect($params) {
        $this->_connectParams = $params;
        $dsn = 'mysql:dbname='.$params['database'].';host='.$params['host'].';port='.$params['port'];
        $user = $params['user'];
        $password = $params['password'];
        $this->_dbh = new PDO($dsn, $user, $password);
    }
    
    public function loadObject($collectionName, $id) {
        if ($this->_hasCollection($collectionName)) {
            //TODO
            $result = $this->_dbh->query('select * from '.$collectionName.' where oid = '.$id, PDO::FETCH_NUM);
        } else {
            throw new Exception('Нет таблицы '.$this->_connectParams['database'].'.'.$collectionName);
        }
    }
    
    protected function _hasCollection($collectionName) {
        $sql = 'show tables';
        $collectionName = strtolower($collectionName);
        foreach ($this->_dbh->query($sql, PDO::FETCH_NUM) as $table) {
            if ($collectionName == $table[0]) {
                return true;
            }
        }
        return false;
    }
    
    public function saveObject($collectionName, $object) {
        ;
    }
}

?>
