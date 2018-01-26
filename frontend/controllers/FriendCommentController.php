<?php

namespace frontend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use common\models\LiveFriendComment;
use yii\data\ActiveDataProvider;


class FriendCommentController extends Controller
{

     /**
     * 汇友圈评论相关接口
     */

    public function actionFriendCommentList()
    {
        $tid = yii::$app->getRequest()->get("tid");
        $pageSize = yii::$app->getRequest()->get("pageSize");
        $ret=LiveFriendComment::getFriendCommentList($pageSize,$tid);
       
        ($ret) ? $this->_successData($ret) : $this->_errorData("0059", "查询失败");
    }
    
      public function actionFriendCommentDetail()
    {
        $pid = yii::$app->getRequest()->get("pid");
        $ret=LiveFriendComment::getFriendCommentDetail($pid);
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