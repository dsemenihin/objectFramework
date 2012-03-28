<?php
require_once 'conf.php';
require_once 'common.php';



//$campaign = CampaignObject::create()->save();
//$campaign->setName('test');

$user2 = UserObject::create('3862947220103300555');
$user2->setTitle('123');
var_dump($user2);

//$list = $user2->getCampaignObjectList(array('user_oid' => $user2->getId()));
//$list->addItem($campaign);
//$campaign->save();


//var_dump($list->getItems());

