<?php
namespace frontend\controllers;

use common\models\QrcodeDownload;
use common\models\Version;
use yii;

class QrcodeDownloadController extends yii\web\Controller{

    public function actionIndex(){
        $qrcode = new QrcodeDownload();
        if($this->is_ios()){
            $qrcode->type = 1;
            $qrcode->create_time = date('Y-m-d H:i:s', time());
            $qrcode->save();
            $down_url = 'itms-apps://itunes.apple.com/us/app/fa-zhi-yu-xin-wen-re-dian/id1133184252';
            header("Location:".$down_url);exit();
        }else{
            $qrcode->type = 2;
            $qrcode->create_time = date('Y-m-d H:i:s', time());
            $qrcode->save();
//            $version_info  = Version::find()->where(['status'=>1, 'system_type'=>0])->select("url")->asArray()->one();
            $down_url = 'http://app.xinhuiwen.com/kdemejkzewrmhh/gfdgxereppxkyd/';
            header("Location:".$down_url);exit();
        }
    }


    /**
     * 判断是否是IOS设备
     * @return boolean
     */
    public function is_ios(){
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
            return true;
        }else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
            return false;
        }else{
            return false;
        }
    }
}
