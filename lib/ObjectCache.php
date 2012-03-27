<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ObjectCache
 *
 * @author dsemenihin
 */
abstract class ObjectCache extends StorageAbstract {
    abstract public function val($var, $value = null);
    abstract public function del($var);
    
    public function hash($key) {
        return md5(serialize($key));
    }
}

?>
