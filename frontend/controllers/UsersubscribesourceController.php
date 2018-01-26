<?php
namespace frontend\controllers;
use OAuth2\Request;
use Yii;
use common\models\NewsComment;
use common\models\UserAmount;
use common\models\UserSubscribeSource;
use common\models\NewsSource;

/**
 * 订阅相关接口
 */
class UsersubscribesourceController extends BaseApiController
{
	
	/**
	 * 我的订阅列表
	 * @zc
	 * @param $page          页码
	 * @param $pageSize   页数
	 * @param $user_id      用户id
	 */
	function actionSubscribeList(){
		$page = isset($this->params['page'])?$this->params['page']:'';
		$page = (!empty($page) && $page > 0) ? $page : 1;
		$pageSize = isset($this->params['pageSize'])?$this->params['pageSize']:'';
		$pageSize = (!empty($pageSize) && $pageSize > 0) ? $pageSize : 10;
		$pageStart = ($page - 1) * $pageSize;
		$pageEnd = $page * $pageSize;
		$user_id = isset($this->params['user_id'])?$this->params['user_id']:'';
		$is_pc = isset($this->params['is_pc'])?$this->params['is_pc']:'';
		
		if(!$user_id){
			$this->_errorData('0001','参数错误');
		}else{
			$returnData = UserSubscribeSource::getSubscribeList($user_id,$pageStart,$pageEnd);
			if($is_pc){
				$new_returnData = array();
				$new_returnData['totalCount'] = $returnData['totalCount'];
				unset($returnData['totalCount']);
				$new_returnData['list'] = $returnData;
				$returnData = $new_returnData;
			}else{
				unset($returnData['totalCount']);
			}
			$this->_successData($returnData, "查选成功");
		}
	}
	
	
	/**
	 * 订阅操作
	 * @zc
	 * @param $source_id    新闻来源
	 * @param $user_id        用户id
	 */
	function actionSubscribeAdd(){
		$source_id = isset($this->params['source_id'])?$this->params['source_id']:'';
		$user_id = isset($this->params['user_id'])?$this->params['user_id']:'';
		$ReturnData = array();
		if(!$source_id || !$user_id){
			$this->_errorData('0001','参数错误');
		}else{
			//判断是否存在此用户关于此新闻的收藏假删信息
			$subscribe_model = UserSubscribeSource::find()->where(['user_id'=>$user_id,'source_id'=>$source_id])->one();
			if($subscribe_model){
				$subscribe_model->status = 1;
				$subscribe_model->update_time = date('Y-m-d H:i:s',time());
				$rid = $subscribe_model->save();
			}else{
				$model = new UserSubscribeSource();
				$model->subscribe_id = time().$this->getRange();
				$model->source_id = $source_id;
				$model->user_id = $user_id;
				$model->status = 1;
				$model->create_time = date('Y-m-d H:i:s',time());
				$model->update_time = date('Y-m-d H:i:s',time());
				$rid = $model->save();
			}
			if($rid !==false){
				$this->_successData($ReturnData, "订阅成功");
			}else{
				$this->_errorData('0002','订阅失败');
			}
		}
	}
	
	
	/**
	 * 删除订阅操作
	 * @zc
	 * @param $user_id            用户id
	 * @param $subscribe_id   订阅id
	 */
	function actionSubscribeDel(){
		$source_id = isset($this->params['source_id'])?$this->params['source_id']:'';
		$user_id = isset($this->params['user_id'])?$this->params['user_id']:'';
		$ReturnData = array();
		if(!$source_id || !$user_id){
			$this->_errorData('0001','参数错误');
		}else{
			$result = UserSubscribeSource::delSubscribeList($user_id,$source_id);
			if($result === false){
				$this->_errorData('0002','取消订阅失败');
			}else{
				$this->_successData($ReturnData, "取消订阅成功");
			}
		}
	}
	
	
	/**
	 * 更多订阅列表
	 * @zc
	 * @param $user_id 用户id，如果存在获取用户和订阅列表之间是否订约过的关系
	 */
	function actionSourceList(){
		$page = isset($this->params['page'])?$this->params['page']:'';
		$page = (!empty($page) && $page > 0) ? $page : 1;
		$pageSize = isset($this->params['pageSize'])?$this->params['pageSize']:'10';
		$pageStart = ($page - 1) * $pageSize;
		$pageEnd = $page * $pageSize;
		$is_pc = isset($this->params['is_pc'])?$this->params['is_pc']:'0';
		$list_ids = isset($this->params['list_ids'])?$this->params['list_ids']:'';
		$user_id = isset($this->params['user_id'])?$this->params['user_id']:'';
		$keyword = isset($this->params['keyword'])?$this->params['keyword']:'';
		if(!$user_id){
			$this->_errorData('0001','参数错误');
		}else{
			$returnData = NewsSource::getSourceList($list_ids,$pageStart, $pageEnd, $keyword,$user_id , $is_pc);
			$this->_successData($returnData, "查选成功");
		}
	}



}