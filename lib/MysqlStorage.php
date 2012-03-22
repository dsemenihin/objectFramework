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
            try {
                $sql = $this->_dbh->prepare('select * from '.$collectionName.' where oid = :id');
                $sql->bindValue(':id', $id, PDO::PARAM_INT);
                $sql->execute();
                if ($sql->errorCode() !== PDO::ERR_NONE) {
                    throw new Exception('Mysql error: '.$sql->errorInfo());
                }
                $result = $sql->fetchObject();
                if (empty($result)) {
                    throw new Exception('Нет объекта : '.$collectionName.' с oid = '.$id);
                }
                
               return (array) $result;
                
            } catch (PDOException $e) {
                throw new Exception('PDO error: '.$e->getMessage());
            }
        } else {
            throw new Exception('Нет таблицы '.$this->_connectParams['database'].'.'.$collectionName);
        }
    }
    
    protected function _hasCollection($collectionName) {
        $sql = 'show tables';
        foreach ($this->_dbh->query($sql, PDO::FETCH_NUM) as $table) {
            if ($collectionName == $table[0]) {
                return true;
            }
        }
        return false;
    }
    
    public function saveObject($object) {
        if (!$object instanceof BasicObject) {
            throw new Exception('Не тот объект');
        }
        
        var_dump('save', get_class($object), $object->getObjectFields());
    }
}

?>
