<?php

namespace frontend\controllers;
use OAuth2\Request;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use common\models\LiveFriendComment;
use common\models\LiveFriendCircle;
use common\models\User1;
use yii\data\ActiveDataProvider;


class FriendCommentReplyController extends BaseApiController
{

     /**
     * 汇友圈评论相关接口
     */

    public function actionFriendCommentAdd()
    {
        $tid = yii::$app->getRequest()->post("tid");
        $token = yii::$app->getRequest()->post("token");
        $user=User1::find()->where(['login_token'=>$token])->select("user_id,nickname,avatar")->one();
        $user_id = $user->user_id;
        $avatar=$user->avatar;
        // $message= yii::$app->getRequest()->post("message");
        $message=$_POST['message'];
        $message=$this->userTextEncode($message);
        (!$avatar) &&$this->_errorData("0059", "未上传头像");
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
        $return['author']=$user->nickname;
        $return['replies']=$replies+1;
        ($ret) ? $this->_successData($return,"评论成功") : $this->_errorData("0059", "评论失败");
    }
    
    

}