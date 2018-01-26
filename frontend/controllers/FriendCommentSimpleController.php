<?php

namespace frontend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use common\models\LiveFriendComment;
use common\models\LiveFriendCircle;
use common\models\User1;
use yii\data\ActiveDataProvider;


class FriendCommentSimpleController extends Controller
{

     /**
     * 汇友圈点赞评论相关接口
     */

    public function actionFriendCommentAdd()
    {
        $tid = yii::$app->getRequest()->get("tid");
        $user_id = yii::$app->getRequest()->get("user_id");
        $message= yii::$app->getRequest()->get("message");
        (!$tid) &&$this->_errorData("0059", "汇友圈未指定");
        (!$user_id) &&$this->_errorData("0059", "用户未指定");
        (!$message) &&$this->_errorData("0059", "评论内容不能为空");
        $model = new LiveFriendComment();
        $user=User1::find()->where(['user_id'=>$user_id])->select("user_id,nickname,avatar")->one();
        $friendCircle=LiveFriendCircle::find()->select(['replies','author','dateline','subject','recommends','icon'])->where(['tid'=>$tid])->one();
        (!$friendCircle) &&$this->_errorData("0059", "汇友圈不存在");
        $replies=$friendCircle->replies;
        $pid=$model::find()->max('pid');
        $model->pid = $pid+1;
        $model->tid = $tid ;
        $model->author = $user->nickname;
        $model->avatar = $user->avatar;
        $model->authorid =$user->user_id;
        $model->message = $message;
        $model->dateline = time();
        $model->useip =yii::$app->getRequest()->userIP;
        $ret=$model->save();
        $ret&&$up_ret  = LiveFriendCircle::updateAllCounters([ "replies" =>1], 'tid =' . $tid);
        ($ret) ? $this->_successData($ret,"评论成功") : $this->_errorData("0059", "评论失败");
    }
    
      public function actionFriendCircleUp()
    {
        $tid = yii::$app->getRequest()->get("tid");
        (!$tid) &&$this->_errorData("0059", "汇友圈未指定");
        $post = LiveFriendCircle::getFriendCircleDetail($tid);
        $ret  = LiveFriendCircle::updateAllCounters([ "recommends" =>1], 'tid =' . $tid);
        $ret_data['recommends']=$post['recommends']+1;
        ($ret) ? $this->_successData($ret_data) : $this->_errorData("0059", "操作失败");
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