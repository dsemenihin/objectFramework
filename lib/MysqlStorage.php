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
        
        if ($this->_debugMode) {
            $this->_dbh->exec('set profiling=1');
        }
        
        if (isset($params['charset'])) {
            $this->_dbh->exec('set names "'.$params['charset'].'";');
        }
    }
    
    protected function _loadObjectsById($collectionName, array $id) {
        if ($this->_hasCollection($collectionName)) {
            try {
                $sql = $this->_dbh->prepare('
                    select * from `'.$collectionName.'` 
                    where '.self::$_primaryKeyName.' in ('.implode(',', $id).')
                ');
                $sql->execute();
                if ($sql->errorCode() !== PDO::ERR_NONE) {
                    $info = $this->_dbh->errorInfo();
                    throw new Exception('Mysql error: '.$info[2]);
                }
                $result = $sql->fetchAll(PDO::FETCH_ASSOC);
                if (empty($result)) {
                    throw new Exception('Нет объекта : '.$collectionName.' с '.self::$_primaryKeyName.' = '.$id);
                }
                
               return $result;
                
            } catch (PDOException $e) {
                throw new Exception('PDO error: '.$e->getMessage());
            }
        } else {
            throw new Exception('Нет таблицы '.$this->_connectParams['database'].'.'.$collectionName);
        }
    }
    
    protected function _hasCollection($collectionName) {
        $cacheKey = get_class().$collectionName.'showtables';
        if ($this->_cache && $cacheHas = $this->_cache->val($cacheKey)) {
            return $cacheHas;
        }
        
        $sql = 'show tables';
        $result = false;
        foreach ($this->_dbh->query($sql, PDO::FETCH_NUM) as $table) {
            if ($collectionName == $table[0]) {
                $result = true;
                break;
            }
        }
        
        if ($this->_cache) {
            $this->_cache->val($cacheKey, $result);
        }
        
        return $result;
    }
    
    public function _saveObjectData() {
        if (empty($this->_saveObjectData)) {
            return $this;
        }
        $this->_dbh->beginTransaction();
        try {
            foreach ($this->_saveObjectData as $collectionName => $objects) {
                if ($this->_hasCollection($collectionName)) {
                    $sqlFields = array();
                    $sqlUpdate = array();

                    foreach ($this->_getObjectSchema($collectionName) as $field => $fieldData) {
                        $sqlFields[] = '`'.$field.'`';
                        $sqlUpdate[] = '`'.$field.'`=values(`'.$field.'`)';
                    }

                    $sqlInsert = array();
                    foreach ($objects as $oid => $data) {
                        $insertValue = array();
                        foreach ($this->_getObjectSchema($collectionName) as $field => $fieldData) {
                            $field = str_replace('_', '', $field);
                            $value = isset($data[$field]) 
                                ? '"'.$data[$field].'"' 
                                : (is_null($fieldData['Default']) ? 'NULL' : '"'.$fieldData['Default'].'"');
                            
                            $insertValue[] = $value;
                        }

                        $sqlInsert[] = '('.implode(',', $insertValue).')';

                    }

                    $sql = '
                        insert into `'.$collectionName.'` ('.implode(',', $sqlFields).') values ';
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
            $this->_dbh->commit();
        } catch (Exception $e) {
            $this->_dbh->rollback();
            throw $e;
        }
    }
    
    protected function _getObjectSchema($collectionName) {
        $cacheKey = get_class().$collectionName.'schema';
        if ($this->_cache && $cacheSchema = $this->_cache->val($cacheKey)) {
            return $cacheSchema;
        }
        if (!isset($this->_objectSchema[$collectionName])) {
            $sql = 'desc '.$collectionName;
            $this->_objectSchema[$collectionName] = array();
            foreach ($this->_dbh->query($sql, PDO::FETCH_ASSOC) as $field) {
                $this->_objectSchema[$collectionName][$field['Field']] = $field;
            }
        }
        
        if ($this->_cache) {
            $this->_cache->val($cacheKey, $this->_objectSchema[$collectionName]);
        }
        
        return $this->_objectSchema[$collectionName];
    }
    
    public function initObject($collectionName) {
        $initData = array();
        foreach ($this->_getObjectSchema($collectionName) as $field => $data) {
            $initData[$field] = $data['Default'];
        }

        $initData[self::$_primaryKeyName] = self::genId();

        return $initData;
    }
    
    public function getIdsByCriteria($collectionName, array $criteria) {
        if ($this->_hasCollection($collectionName)) {
            try {
                if (!empty($criteria)) {
                    $where = array();
                    $schema = $this->_getObjectSchema($collectionName);
                    foreach ($criteria as $field => $value) {
                        if (!isset($schema[$field])) {
                            continue;
                        }
                        $where[] = '`'.$field.'`="'.$value.'"';
                    }
                    
                    $where = implode(' AND ', $where);
                } else {
                    $where = '1';
                }
                
                $sql = '
                    select `'.self::$_primaryKeyName.'` from `'.$collectionName.'` 
                    where ' . $where;
                
                $data = $this->_dbh->query($sql);
                if ($this->_dbh->errorCode() !== PDO::ERR_NONE) {
                    $info = $this->_dbh->errorInfo();
                    throw new Exception('Mysql error: '.$info[2]);
                }
                $result = $data->fetchAll(PDO::FETCH_COLUMN, 0);
                
               return $result;
                
            } catch (PDOException $e) {
                throw new Exception('PDO error: '.$e->getMessage());
            }
        } else {
            throw new Exception('Нет таблицы '.$this->_connectParams['database'].'.'.$collectionName);
        }
    }
    
    protected function _getDebugInfo() {
        if (!$this->_debugMode) {
            return false;
        }
        $result = $this->_dbh->query('show profiles');
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
