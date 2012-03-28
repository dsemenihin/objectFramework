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
        $_storageName,
        /**
         * @var ObjectCache 
         */
        $_cache,
        $_debugMode = false;

    static public function getPrimaryKeyName() {
        $className = get_called_class();
        return $className::$_primaryKeyName;
    }
    
    protected function __construct($storageName, $params, $debug = false) {
        parent::__construct($storageName, $params, $debug);
        if (!empty(Config::$vars['storages'][$storageName]['cache'])) {
            $this->_cache = ObjectCache::create(Config::$vars['storages'][$storageName]['cache'], $debug);
        }
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
        if (!empty($this->_saveObjectData)) {
            $this->_saveObjectData();
            $this->_saveToCache();
        }

        if ($this->_debugMode) {
            var_dump($this->_getDebugInfo());
        }
    }
    
    /**
     * Сохрание в кеш измененных объектов
     * @return boolean 
     */
    protected function _saveToCache(BasicObject $object = null) {
        if (!$this->_cache) {
            return;
        }
        
        if (!empty($object)) {
            $this->_cache->val($this->_getCacheKey(get_class($object), $object->getId()), $object->getObjectFields());
            return;
        }
            
        
        if (empty($this->_saveObjectData)) {
            return;
        }
        
        foreach ($this->_saveObjectData as $collectionName => $objects) {
            foreach ($objects as $oid => $data) {
                $this->_cache->val($this->_getCacheKey($collectionName, $oid), $data);
            }
        }
    }

    /**
     * Накопление данных для сохранения объектов
     * @param BasicObject $object
     * @throws Exception 
     */
    public function saveObject(BasicObject $object, $flush = false) {
        $this->_saveObjectData[get_class($object)][$object->getId()] = 
            $object->getObjectFields();
        
        if ($flush) {
            $this->_saveObjectData($object);
            $this->_saveToCache($object);
        }
    }
    
    protected function _getCacheKey($collectionName, $id) {
        if (!$this->_cache) {
            return false;
        }
        return $this->_cache->hash(array($collectionName, $id));
    }
    
    /**
     * Загрузка данных объектов.
     * Если есть данные в кеше, берем из него, иначе подгружаем из постоянного хранилища.
     * @param type $collectionName
     * @param array $id
     * @return type 
     */
    public function loadObjectsById($collectionName, array $id) {
        $cacheResult   = array();
        if ($this->_cache) {
            foreach ($id as $idItem) {
                $cacheItem = $this->_cache->val($this->_getCacheKey($collectionName, $idItem));
                if ($cacheItem) {
                    $cacheResult[$idItem] = $cacheItem;
                }
            }
        }
        
        $notFound = array_diff($id, array_keys($cacheResult));
        $storageResult = !empty($notFound) ? $this->_loadObjectsById($collectionName, $notFound) : array();
        
        if ($this->_cache) {
            foreach ($storageResult as $object) {
                $this->_cache->val(
                    $this->_getCacheKey($collectionName, $object[self::getPrimaryKeyName()]), 
                    $object);
            }
        }
        
        return array_merge(array_values($cacheResult), $storageResult);
    }

    public function initObject($collectionName) {
        $initData = $this->_initObject($collectionName);
        $initData[self::getPrimaryKeyName()] = self::genId();
        return $initData;
    }

    /**
     * Загрузка данных объектов по первичному ключу 
     */
    abstract protected function _loadObjectsById($collectionName, array $id);
    
    /**
     * Инициализация объекта из схемы.
     * Значения заполняются дефолтными 
     */
    abstract protected function _initObject($collectionName);

    /**
     * Вернуть список id объектов по заданному условию
     * @param type $collectionName
     * @param array $criteria 
     */
    abstract public function getIdsByCriteria($collectionName, array $criteria);
    
    /**
     * Реализация сохранения данных объектов.
     * Вызывается из деструктора хранилища 
     */
    abstract protected function _saveObjectData(BasicObject $object = null);
    
    /**
     * Вернуть отладочную информацию 
     */
    abstract protected function _getDebugInfo();
        
    
}

?>
