<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MemcacheCache
 *
 * @author dsemenihin
 */
class MemcacheCache extends ObjectCache {
    protected $_resourse;
    
    protected function _connect($params) {
        $this->_resourse = new Memcache;
        $this->_resourse->connect($params['host'], $params['port']);
    }
    
    public function val($var, $value = null) {
        if(is_null($value)) {
            return $this->_resourse->get($var);
        }
        else {
            $this->_resourse->set($var, $value, false, 3600*24*3);
        }
        return null;
    }

    public function del($var) {
        $this->_resourse->delete($var);
    }
}

?>
