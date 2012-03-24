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
    
    public function _saveObjectData() {
        foreach ($this->_saveObjectData as $collectionName => $objects) {
            if ($this->_hasCollection($collectionName)) {
                $sqlFields = array();
                $sqlUpdate = array();

                foreach ($this->getObjectSchema($collectionName) as $field => $fieldData) {
                    $sqlFields[] = '`'.$field.'`';
                    $sqlUpdate[] = '`'.$field.'`=values(`'.$field.'`)';
                }

                $sqlInsert = array();
                foreach ($objects as $oid => $data) {
                    $insertValue = array();
                    foreach ($this->getObjectSchema($collectionName) as $field => $fieldData) {
                        $field = str_replace('_', '', $field);
                        $value = isset($data[$field]) ? $data[$field] : $fieldData['Default'];
                        if (!is_null($value)) {
                            $value = '"'.$value.'"';
                        } else {
                            $value = 'NULL';
                        }
                        $insertValue[] = $value;
                    }

                    $sqlInsert[] = '('.implode(',', $insertValue).')';

                }

                $sql = '
                    insert into '.$collectionName.' ('.implode(',', $sqlFields).') values ';
                $sql .= implode(',', $sqlInsert);
                $sql .= ' ON DUPLICATE KEY UPDATE';
                $sql .= implode(',', $sqlUpdate);

                try {
                    $result = $this->_dbh->exec($sql); 
                } catch (PDOException $e) {
                    throw new Exception('PDO error: '.$e->getMessage());
                }
                
                if ($this->_dbh->errorCode() !== PDO::ERR_NONE) {
                    $info = $this->_dbh->errorInfo();
                    throw new Exception('Mysql error: '.$info[2]);
                }
            } else {
                throw new Exception('Нет таблицы '.$this->_connectParams['database'].'.'.$collectionName);
            }
        }
    }
    
    public function getObjectSchema($collectionName) {
        if (!isset($this->_objectSchema[$collectionName])) {
            $sql = 'desc '.$collectionName;
            $this->_objectSchema[$collectionName] = array();
            foreach ($this->_dbh->query($sql, PDO::FETCH_ASSOC) as $field) {
                $this->_objectSchema[$collectionName][$field['Field']] = $field;
            }
        }
        
        return $this->_objectSchema[$collectionName];
    }
}

?>
