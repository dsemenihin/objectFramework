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
    protected function _connect($params) {
        var_dump($params);
    }
}

?>
