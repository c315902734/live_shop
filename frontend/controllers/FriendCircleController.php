<?php

namespace frontend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use common\models\LiveFriendCircle;
use common\models\LiveFriendComment;
use yii\data\ActiveDataProvider;


class FriendCircleController extends Controller
{

     /**
     * 汇友圈相关接口
     */

    public function actionFriendCircleList()
    {
        $pageSize = yii::$app->getRequest()->get("pageSize");
        $live_id   = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : 0;
        $list=LiveFriendCircle::getFriendCircleList($pageSize,$live_id);        
        foreach ($list['data'] as $key => $val) {
               $list['data'][$key]['comment_list'] = LiveFriendComment::getFriendCommentList(3,$val['tid']);
        }
        ($list) ? $this->_successData($list) : $this->_errorData("0059", "查询失败");
    }
    
      public function actionFriendCircleDetail()
    {
        $tid = yii::$app->getRequest()->get("tid");
        $ret=LiveFriendCircle::getFriendCircleDetail($tid);
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