<?php

class Config {
    static $vars = array(
        'defaultStorage' => 'MysqlDb1',
        'defaultCache'   => 'Memcache',
        'storages' => array(
            'MysqlDb1'   => array (
                'adapter'       => 'MysqlStorage',
                'connectParams' => array(
                    'host'     => 'localhost',
                    'port'     => '3360',
                    'user'     => 'root',
                    'password' => '',
                    'database' => 'test'
                ),
                'debug'         => true
            ),
            'Memcache' => array(
                'adapter'       => 'MemcacheCache',
                'connectParams' => array(
                    'host'     => 'localhost',
                    'port'     => '11211',
                ),
                'debug'         => true
            )
        ),
        
    );
}
?>
