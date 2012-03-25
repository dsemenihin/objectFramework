<?php
/**
 * Общий класс списков обектов приложения
 * Реализован интерфейс ArrayAccess, Iterator
 */

class BasicObjectList implements ArrayAccess, Iterator {
    
    protected
        /**
         * Массив ID объектов, составляющий список. В массиве ключ и значение равны ID элемента
         * @var array
         */    
        $_aIds,

        /**
         * Имя класса, составляющих списка
         * @var string
         */
        $_itemClass,
            
        /**
         * Хранилище объектов
         * @var ObjectStorage 
         */
        $_storage,       
         
        /**
         * Владелец класса.
         * Отсутствует, если список глобальный
         * @var BasicObject
         */
        $_owner,

        /**
         * Условия нахождения объекта в списке
         * @var array
         */
        $_listCriteria,

        /**
         * Указатель текущей позиции для реализации Iterator
         * @var int
         */
        $_position;


    public function __construct(ObjectStorage $storage, $itemClass, 
        $listCriteria = array(), BasicObject $owner = null) {
        
        $this->_owner        = $owner;
        $this->_itemClass    = $itemClass;
        $this->_listCriteria = $listCriteria;
        $this->_storage      = $storage;
        $this->_aIds         = array();
        $this->_position     = 0;
        
        $this->_initList();
    }

    public function current() {
        $itemKey = array_keys(array_slice($this->_aIds, $this->_position, 1, true));

        return isset($itemKey[0]) ? $this->offsetGet($itemKey[0]) : null;
    }

    public function key() {
        return $this->_position;
    }

    public function next() {
        ++$this->_position;
    }

    public function rewind() {
        $this->_position = 0;
    }

    public function valid() {
        return ($this->current() || false);
    }

    public function offsetExists($offset) {
        return key_exists($offset, $this->_aIds);
    }

    public function offsetGet($offset) {
        if ($this->offsetExists($offset)) {
            $class = $this->_itemClass;
            return $class::create($offset);
        }
        return false;
    }

    public function offsetSet($offset, $item) {
        if (!$item instanceof BasicObject) {
            throw new Exception('Некорректный тип объекта');
        }
        
        if(empty($this->_owner)) {
            throw new Exception('Нельзя добавлять элемент в глобальный список');
        }
        
        if (!empty($item)) {
            $item->setFields($this->_listCriteria);
            $this->_aIds[$offset] = $offset;
            return true;
        }
        return false;
    }

    public function offsetUnset($offset) {
        throw new Exception('Недопустимая операция. Чтобы удалить элемент из списка '. 
            'требуется изменить его свойства напрямую');
    }

    public function addItem($item) {
        $this->offsetSet($item->getId(), $item);
    }

    public function getItemsCount() {
        return sizeof($this->_aIds);
    }

    public function getItems($start = null, $count = null) {
        $items = array_slice($this->_aIds, $start, $count);
        $itemClass = $this->_itemClass;
        return $itemClass::create(array_keys($items));
    }
    
    /**
     * Инициализировать из базы массив id объектов
     */
    private function _initList() {
        $this->_aIds = array_fill_keys($this->_storage->getIdsByCriteria($this->_itemClass, $this->_listCriteria), 1);
    }

}

?>