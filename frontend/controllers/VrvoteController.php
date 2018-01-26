<?php

namespace frontend\controllers;

use common\models\VoteActivity;
use common\models\VoteCategory;
use common\models\VoteParticipant;
use common\models\VoteLog;
class VrvoteController extends PublicBaseController{


	/**
	 * 投票详情接口
	 * @cong.zhao
	 * @param $activity_id 投票活动id
	 * @return array
	 */
	public function actionVoteInfo(){
		$activity_id   = isset($_REQUEST['activity_id']) ? $_REQUEST['activity_id'] : '0';
		if(!$activity_id){
			$this->_errorData('0001','参数错误');
		}
		$returnData = VoteActivity::GetVoteInfo($activity_id);
		$this->_successData($returnData, "查选成功");
	}
	
	/**
	 * 获取投票下的一级分类列表
	 * @cong.zhao
	 * @param $activity_id 投票活动id
	 * @return array
	 */
	public function actionPrimaryCategoryList(){
		$activity_id   = isset($_REQUEST['activity_id']) ? $_REQUEST['activity_id'] : '0';
		$parent_id   = isset($_REQUEST['parent_id']) ? $_REQUEST['parent_id'] : '0';
		if(!$activity_id){
			$this->_errorData('0001','参数错误');
		}
		$returnData = VoteCategory::GetCategoryList($activity_id, $parent_id);
		$this->_successData($returnData, "查选成功");
	}
	
	/**
	 * 获取分类下的投票参与者列表
	 * @cong.zhao
	 * @param $category_id 分类id
	 * @param $activity_id 投票活动id
	 * @return array
	 */
	public function actionParticipantList(){
		$activity_id   = isset($_REQUEST['activity_id']) ? $_REQUEST['activity_id'] : '0';
		$category_id   = isset($_REQUEST['category_id']) ? $_REQUEST['category_id'] : '0';
		if(!$activity_id || !$category_id){
			$this->_errorData('0001','参数错误');
		}
		$returnData = VoteParticipant::GetParticipantList($activity_id, $category_id);
		$this->_successData($returnData, "查选成功");
	}
	
	/**
	 * 投票接口
	 * @cong.zhao
	 * @param $activity_id 投票活动id
	 * @param $user_id 投票用户id
	 * @param $type  类型 1:投票 2:选项
	 * @param $option_id 投票或选项的id
	 * @return bool
	 */
	public function actionDoVote(){
		$activity_id   = isset($_REQUEST['activity_id']) ? $_REQUEST['activity_id'] : '0';
		$user_id   = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '0';
		$type   = isset($_REQUEST['type']) ? $_REQUEST['type'] : '1';
		$option_id   = isset($_REQUEST['option_id']) ? $_REQUEST['option_id'] : '0';
		if(!$activity_id || !$user_id || !$option_id){
			$this->_errorData('0001','参数错误');
		}
		$returnData = VoteLog::DoVote($activity_id, $user_id, $type, $option_id);
		if($returnData['code'] == '0000'){
			$this->_successData('0000', "投票成功");
		}else{
			$this->_errorData($returnData['code'],$returnData['msg']);
		}
	}
	
	/**
	 * 搜索接口
	 * @cong.zhao
	 * @param @keyword 关键字
	 * @param @activity_id 投票活动id
	 * @return array
	 */
	public function actionSearchParticipant(){
		$activity_id   = isset($_REQUEST['activity_id']) ? $_REQUEST['activity_id'] : '0';
		$keyword   = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : '';
		if(!$activity_id){
			$this->_errorData('0001','参数错误');
		}
		$returnData = VoteParticipant::SearchGetParticipant($activity_id, $keyword);
		$this->_successData($returnData, "查选成功");
		
	}
	
	
}
