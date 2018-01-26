<?php

namespace frontend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use common\models\AdminApiUserAction;
use yii\log\FileTarget;

class AppActionController extends Controller
{

     /**
     * 获取tab
     */

    public function actionIndex()
    {
    }
    
      public function actionApiUserActionDetail()
    {
        $id = yii::$app->getRequest()->get("id");
        $ret=AdminApiUserAction::getApiUserActionDetail($id);
        ($ret) ? $this->_successData($ret) : $this->_errorData("0059", "查询失败");
    }
    
    protected function _successData($returnData, $msg = "查询成功")
    {
        $data = array('Success' => true,
            'ResultCode' => '0000',
            'ReturnData' => $returnData,
            'Message' => $msg
        );
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }

    protected function _errorData($code, $message)
    {
        $ReturnData = NULL;
        $data = array('Success' => false,
            'ResultCode' => $code . "",
            'ReturnData' => $ReturnData,
            'Message' => $message . ''
        );
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }

    

}