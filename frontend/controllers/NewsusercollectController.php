<?php
namespace frontend\controllers;
use OAuth2\Request;
use Yii;
use common\models\NewsComment;
use common\models\UserAmount;
use common\models\UserSubscribeSource;
use common\models\NewsUserCollect;

/**
 * 订阅相关接口
 */
class NewsusercollectController extends BaseApiController
{

	/**
	 * 收藏列表
	 * @cong.zhao
	 */
	function actionCollectList(){
		$page = isset($this->params['page'])?$this->params['page']:'';
		$page = (!empty($page) && $page > 0) ? $page : 1;
		$pageSize = isset($this->params['pageSize'])?$this->params['pageSize']:'';
		$pageSize = (!empty($pageSize) && $pageSize > 0) ? $pageSize : 10;
		$pageStart = ($page - 1) * $pageSize;
		$pageEnd = $page * $pageSize;
		$user_id = isset($this->params['user_id'])?$this->params['user_id']:'';
		$type = isset($this->params['type'])?$this->params['type']:'1';
		$is_pc = isset($this->params['is_pc'])?$this->params['is_pc']:'';
	
		if(!$user_id){
			$this->_errorData('0001','参数错误');
		}else{
			$returnData = NewsUserCollect::getCollectList($user_id,$type,$pageStart,$pageEnd,$is_pc);
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
	 * 添加收藏
	 * @zc
	 */
	function actionCollectAdd(){
		$news_id = isset($this->params['news_id'])?$this->params['news_id']:'';
		$user_id = isset($this->params['user_id'])?$this->params['user_id']:'';
		$type = isset($this->params['type'])?$this->params['type']:'1';
		
		$ReturnData = array();
		if(!$news_id || !$user_id){
			$this->_errorData('0001','参数错误');
		}else{
			//判断是否存在此用户关于此新闻的收藏假删信息
			$collect_model = NewsUserCollect::find()->where(['news_id'=>$news_id,'user_id'=>$user_id])->one();
			if($collect_model){
				$collect_model->status = 1;
				$collect_model->type = $type;
				$collect_model->update_time = date('Y-m-d H:i:s',time());
				if($collect_model->save()){
					$rid = $collect_model->collect_id;
				}
			}else{
				$model = new NewsUserCollect();
				$model->collect_id = time().$this->getRange();
				$model->news_id = $news_id;
				$model->user_id = $user_id;
				$model->status = 1;
				$model->type = $type;
				$model->create_time = date('Y-m-d H:i:s',time());
				$model->update_time = date('Y-m-d H:i:s',time());
				if($model->save()){
					$rid = $model->collect_id;
				}
			}
			if($rid){
				$ReturnData = "$rid";
				$this->_successData($ReturnData, "收藏成功");
			}else{
				$this->_errorData('0002','收藏失败');
			}
		}
	}
	
	
	
	/**
	 * 删除收藏
	 * @zc
	 */
	function actionCollectDel(){
		$user_id = isset($this->params['user_id'])?$this->params['user_id']:'';
		$collect_id = isset($this->params['collect_id'])?$this->params['collect_id']:'';
		$ReturnData = array();
		if(!$collect_id || !$user_id){
			$this->_errorData('0001','参数错误');
		}else{
			$result = NewsUserCollect::delCollectList($user_id,$collect_id);
			if(!$result){
				$this->_errorData('0002','取消收藏失败');
			}else{
				$this->_successData($ReturnData, "取消收藏成功");
			}
		}
	}
	


}