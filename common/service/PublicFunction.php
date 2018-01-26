<?php
namespace common\service;
use Yii;
/*
*  md5签名，$array中务必包含 appSecret
*/
class PublicFunction{
 	public static function SetRedis($key, $value, $time = 2592000000){
 		$key = array($key);
 		Yii::$app->cache->set($key,$value,$time);
 		return True;
 	}
}
?>
