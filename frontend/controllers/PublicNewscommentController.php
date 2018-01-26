<?php
namespace frontend\controllers;
use OAuth2\Request;
use Yii;
use common\models\NewsComment;
use common\models\UserAmount;
use yii\db\Query;

/**
 * 评论相关接口
 */
class PublicNewscommentController extends PublicBaseController
{
	/**
	 * 评论/回复列表
	 * @cong.zhao
	 */
	function actionCommentList(){
		$page = isset($this->params['page'])?$this->params['page']:'';
		$page = (!empty($page) && $page > 0) ? $page : 1;
		$pageSize = isset($this->params['pageSize'])?$this->params['pageSize']:'';
		$pageSize = (!empty($pageSize) && $pageSize > 0) ? $pageSize : 10;
		$pageStart = ($page - 1) * $pageSize;
		$pageEnd = $page * $pageSize;
		$status = isset($this->params['status']) ? $this->params['status'] : 0; // 来源 默认0手机端，1 pc端
		$user_id = isset($this->params['user_id'])?$this->params['user_id']:'';
		$news_id = isset($this->params['news_id'])?$this->params['news_id']:'';
		$comment_id = isset($this->params['comment_id'])?$this->params['comment_id']:'';

		if(!$news_id){
			$this->_errorData('0001','参数错误');
		}else{
			$returnData = NewsComment::getCommentList($user_id,$news_id,$comment_id,$pageStart,$pageEnd,$status);
			if($status == 0){
				unset($returnData['totalCount']);
				foreach ($returnData as $key=>$val){
					$returnData[$key]['content'] = parent::filter_words($this->userTextDecode($val['content']));
					$returnData[$key]['nickname'] = $this->userTextDecode($val['nickname']);
					if(isset($val['to_comment'])){
						$returnData[$key]['to_comment']['to_comment_content'] = parent::filter_words($this->userTextDecode($val['to_comment']['to_comment_content']));
						$returnData[$key]['to_comment']['to_comment_nickname'] = $this->userTextDecode($val['to_comment']['to_comment_nickname']);
					}
				}
			}else{
				unset($returnData['comment_list']['totalCount']);
				foreach ($returnData['comment_list'] as $key=>$val){
					$returnData['comment_list'][$key]['content'] = parent::filter_words($this->userTextDecode($returnData['comment_list'][$key]['content']));
					$returnData['comment_list'][$key]['nickname'] = $this->userTextDecode($returnData['comment_list'][$key]['nickname']);
					if(isset($returnData['comment_list'][$key]['to_comment'])){
						$returnData['comment_list'][$key]['to_comment']['to_comment_content'] = parent::filter_words($this->userTextDecode($returnData['comment_list'][$key]['to_comment']['to_comment_content']));
						$returnData['comment_list'][$key]['to_comment']['to_comment_nickname'] = $this->userTextDecode($returnData['comment_list'][$key]['to_comment']['to_comment_nickname']);
					}
				}
			}
// 			if($status == 1){
// 				$returnData['comment_list']['totalCount'] = $returnData['totalCount'];
// 			}

			$this->_successData($returnData, "查选成功");
		}
	}
	


}