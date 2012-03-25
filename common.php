<?php

function __autoload($className) {
    if (!class_exists($className)) {
        if ($className == 'BasicObject') {
            require_once 'lib/BasicObject.php';
        } else if ($className == 'BasicObjectList') {
            require_once 'lib/BasicObjectList.php';    
        } else if (preg_match('|^.+Object$|', $className)) {
            eval('class '.$className.' extends BasicObject {}');
        } else if (preg_match('|^.+ObjectList$|', $className)) {
            eval('class '.$className.' extends BasicObjectList {}');
        } else if (preg_match('|^.+Storage$|', $className)) {
            require_once 'lib/'.$className.'.php';
        }
    }
}

?>
