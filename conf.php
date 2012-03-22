<?php

class Config {
    static $vars = array(
        'defaultStorage' => 'MysqlDb1',
        'MysqlDb1'   => array (
            'adapter'       => 'MysqlStorage',
            'connectParams' => array(
                'host'     => 'localhost',
                'port'     => '3360',
                'user'     => 'root',
                'password' => '',
                'database' => 'test'
            )
        ),
    );
}
?>
