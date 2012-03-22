<?php
require_once 'conf.php';

function __autoload($className) {
    if (!class_exists($className)) {
        if ($className == 'BasicObject') {
            require_once 'lib/BasicObject.php';
        } else if (preg_match('|^.+Object$|', $className)) {
            eval('class '.$className.' extends BasicObject {}');
        } else if (preg_match('|^.+Storage$|', $className)) {
            require_once 'lib/'.$className.'.php';
        }
    }
}

$user = UserObject::create(1);
var_dump($user->setTitle('group2'));