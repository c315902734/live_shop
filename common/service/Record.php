<?php
namespace common\service;

use yii;
class Record
{
	public static function record_data($data, $string=''){
		$logFile = yii::$app->basePath."/runtime/logs/" . date('Ymd') . ".log";
		$log = "\n\n"."<===begin===".date('H:i:s')."===>". "\n";
		$log .= "param:  ".$string."\n";
		if(is_array($data)){
			$log .= "content:  ".print_r($data,true);
		}else{
			$log .= "\n"."content:  ".print_r($data,true)."\n";
		}
		$log .= "<===end===>". "\n";
		$fh = fopen($logFile, 'a+');
		fwrite($fh, $log);
		fclose($fh);
	}
}
