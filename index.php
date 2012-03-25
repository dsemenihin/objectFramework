<?php
require_once 'conf.php';
require_once 'common.php';


//$campaign = CampaignObject::create();
//$campaign->setName('test');

$user2 = UserObject::create('3859460461871800777');
$list = $user2->getCampaignObjectList(array('user_oid' => $user2->getId()));
var_dump($list->getItems());

