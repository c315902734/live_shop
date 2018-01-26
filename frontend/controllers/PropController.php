<?php
namespace frontend\controllers;


use common\models\LiveProp;
use common\models\LiveCompetitor;
use common\models\LiveUserProp;
use common\models\User1;
use common\models\UserAmount;
use Yii;

class PropController extends PublicBaseController{
	
	/**
	 * 道具礼物列表 
	 */
	public function actionGetproplist(){
		//查询道具列表
		/* $live_prop = $this->live_prop_model
		->field("prop_id,name,icon,amount,sentiment_value")
		->where("status = 1")
		->select(); */
		$live_prop = LiveProp::find(['prop_id','name','icon','amount','sentiment_value'])->where(['status'=>1])->asArray()->all();
		$this->_successData($live_prop);
	}
}