<?php
namespace frontend\controllers;
use OAuth2\Request;
use Yii;
use common\models\NewsComment;
use common\models\UserAmount;
use common\models\UserSubscribeSource;
use common\models\NewsCommentLike;

/**
 * 订阅相关接口
 */
class NewscommentlikeController extends BaseApiController
{
	
	/**
	 * 评论/回复点赞
	 * @cong.zhao
	 */
	function actionCommentZan(){
		$user_id = isset($this->params['user_id'])?$this->params['user_id']:'';
		$comment_id = isset($this->params['comment_id'])?$this->params['comment_id']:'';
		if(!$comment_id || !$user_id){
			$this->_errorData('0001','参数错误');
		}else{
			$returnData = NewsCommentLike::zanCommentList($user_id,$comment_id);
			if(!$returnData){
				$this->_errorData('0002','失败');
			}else{
				$this->_successData($returnData, "成功");
			}
		}
	}



}