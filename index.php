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


$user2 = UserObject::create();
$user2->setUserType('Admin');
$user2->setTitle('test');

$campaign = CampaignObject::create();
$campaign->setUserOid($user2->getId());
$campaign->setName('campaign1');

