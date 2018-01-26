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
class NewssourceController extends PublicBaseController
{
	/**
	 * 订阅号详情
	 * @zc
	 * @param $source_id  订阅号id 即来源id
	 */
	function actionSourceDetail(){
		$page = isset($this->params['page'])?$this->params['page']:'';
		$page = (!empty($page) && $page > 0) ? $page : 1;
		$pageSize = isset($this->params['pageSize'])?$this->params['pageSize']:'';
		$pageSize = (!empty($pageSize) && $pageSize > 0) ? $pageSize : 10;
		$pageStart = ($page - 1) * $pageSize;
		$pageEnd = $page * $pageSize;
		$user_id = isset($this->params['user_id'])?$this->params['user_id']:'';
		$source_id = isset($this->params['source_id'])?$this->params['source_id']:'';
		$keyword = isset($this->params['keyword'])?$this->params['keyword']:'';
		$is_pc = isset($this->params['is_pc'])?$this->params['is_pc']:'';

		if(!$source_id){
			$this->_errorData('0001','参数错误');
		}else{
			$returnData = NewsSource::getSourceDetail($source_id,$pageStart,$pageEnd,$keyword,$user_id,$is_pc);
			$this->_successData($returnData, "查选成功");
		}
	}

	/**
	 * 获取订阅号信息
	 */
	function actionSourceInfo(){
		$source_id = isset($this->params['source_id'])?$this->params['source_id']:'';
		if(!$source_id){
			$this->_errorData('0001','参数错误');
		}
		$returnData = NewsSource::find()->where(['source_id'=>$source_id])->asArray()->one();
		$this->_successData($returnData, "查选成功");
	}
	

}