<?php

class Config {
    static $vars = array(
        'defaultStorage' => 'MysqlDb1',
        //'defaultStorage' => 'Mongo',

        'storages' => array(
            'MysqlDb1'   => array (
                'adapter'       => 'MysqlStorage',
                'connectParams' => array(
                    'driver'   => 'MysqliMysqlDriver',
                    'host'     => 'localhost',
                    'port'     => 3360,
                    'user'     => 'root',
                    'password' => '',
                    'database' => 'test',
                    'charset'  => 'utf8',
                ),
                'debug'         => false,
                'cache'   => 'Memcache',
            ),
            'Memcache' => array(
                'adapter'       => 'MemcacheCache',
                'connectParams' => array(
                    'host'     => 'localhost',
                    'port'     => '11211',
                ),
                'debug'         => true
            ),
            'Mongo' => array(
                'adapter'       => 'MongoStorage',
                    'connectParams' => array(
                        'database' => 'test',
                    ),
                'debug'         => true,
                'cache'   => 'Memcache',
            )
        ),
        
    );
}
?>
