<?php

namespace console\controllers;
use yii\console\Controller;
use yii\helpers\Console;
use common\models\VoteClient;

class VoteController extends Controller
{
    public function actionIndex()
    {
        date_default_timezone_set('Asia/Shanghai');
		$sTime = strtotime(date('Y-m-d 00:00:00',strtotime("-1 day")));
		// $this->stdout($sTime."\n", Console::BOLD);
		$date = date('Y-m-d',strtotime("-1 day"));
        $eTime = strtotime(date('Y-m-d 23:59:59',strtotime("-1 day")));
        // $this->stdout($eTime."\n", Console::BOLD);
        // $this->stdout(strtotime(date('Y-m-d 00:00:00',time()))."\n", Console::BOLD);
        // $this->stdout(strtotime(date('Y-m-d 23:59:59',time()))."\n", Console::BOLD);
        VoteClient::deleteAll('created_at>=:stime AND created_at<=:etime',[':stime'=>$sTime,':etime'=>$eTime]);
        $this->stdout($date . "/delete ok"."\n", Console::BOLD);
        // $this->stdout("cnt=".count($voteClient)."\n", Console::BOLD);
        // $this->stdout("eTime=".$eTime."\n", Console::BOLD);
    }
}
