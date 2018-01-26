<?php
namespace frontend\controllers;


use common\models\LiveProp;
use common\models\LiveCompetitor;
use common\models\LiveUserProp;
use common\models\User1;
use common\models\UserAmount;
use Yii;

class GiftsController extends BaseApiController{
	
	
	/**
	 * 打赏接口
	 */
	public function actionGifts(){ 
		$token = !empty($_REQUEST['token']) ? $_REQUEST['token'] : '';
		$userData = $this->_checkToken($token);
		if($userData == false){
			$this->_errorData(0055, '用户未登录');
			exit;
		}
		$propId = !empty($_REQUEST['prop_id']) ? $_REQUEST['prop_id'] : '';
		$propCnt = !empty($_REQUEST['prop_cnt']) ? $_REQUEST['prop_cnt'] : '';
		$competitorId = !empty($_REQUEST['competitor_id']) ? $_REQUEST['competitor_id'] : '';
		$liveId = !empty($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '';
		$pk_group = !empty($_REQUEST['pk_group']) ? $_REQUEST['pk_group'] : '';
		if(!$propId || !$competitorId || !$propCnt || intval($propCnt)<=0 || !$liveId || !$pk_group){
			$this->_errorData(0058, '参数错误！');
			exit;
		}
		//限制999个
		if(intval($propCnt)>999){
			$this->_errorData(0159, '最多只能送出999个礼物！');
			exit;
		}
		//检查金币是否足够
		/* $live_prop = $this->live_prop_model
		->field("prop_id,name,icon,amount,sentiment_value")
		->where("status = 1 and prop_id=".$propId)
		->find(); */
		
		$live_prop_model = new LiveProp();
	
		$live_prop = LiveProp::find(['prop_id','name','icon','amount','sentiment_value'])
 					->where(['status'=>1,'prop_id'=>$propId])
 					->asArray()
					->one();
			
		if(!$live_prop){
			$this->_errorData(0154, '道具错误！');
			exit;
		}
		$amount = intval($live_prop['amount'])*intval($propCnt);
		/* $user   = $this->user_model->getUserById($userData["userId"]); */
		$user   = User1::getUserById($userData["userId"]);
		
		if(intval($user['amount'])>=$amount){
			//扣除金币，并且增加日志记录
			/* $param['user_id']      = $userData["userId"];
			$param['operate_cnt']  = $amount;
			$param['operate']      = 2;
			$param['operate_name'] = '打赏';
			$userAmount            = new UserAmountModel();
			$res = $userAmount->addUserAmount($param); */
			
			$param['user_id']      = $userData["userId"];
			$param['operate_cnt']  = $amount;
			$param['operate']      = 2;
			$param['operate_name'] = '打赏';
			
			$user_amount_model = new UserAmount();
			$res = $user_amount_model->addUserAmount($param);
			
			//给选手增加人气值
			/* $live_competitor = $this->live_competitor_model
			->where("status=1 and competitor_id=".$competitorId)
			->find(); */
			
			$live_competitor = LiveCompetitor::find()->where(['status'=>1, 'competitor_id'=>$competitorId])->asArray()->one();
			
			if(!$live_competitor){
				$this->_errorData(0160, '选手不存在！');
				exit;
			}else{
				unset($data);
				LiveCompetitor::updateAll(['popular_value'=>intval($live_competitor['popular_value'])+intval($live_prop['sentiment_value'])*intval($propCnt)],['status'=>1,'competitor_id'=>$competitorId]);
				/* $this->live_competitor_model
				->where("status=1 and competitor_id=".$competitorId)
				->save($data); */
			}
			//记录道具使用信息
			unset($data);
			
			$live_user_prop_model = new LiveUserProp();
			$live_user_prop_model->user_id       = $userData['userId'];
			$live_user_prop_model->competitor_id = $competitorId;
			
			if($liveId){
				$live_user_prop_model->live_id = $liveId;
				$live_user_prop_model->prop_id = $propId;
				$live_user_prop_model->pk_id = $pk_group;
				$live_user_prop_model->amount = $amount;
				$live_user_prop_model->prop_cnt = $propCnt;
				$live_user_prop_model->sentiment_value = intval($live_prop['sentiment_value'])*intval($propCnt);
				$live_user_prop_model->create_time = date('Y-m-d H:i:s',time());
				
				/* $data['live_id'] = $liveId;
				$data['prop_id'] = $propId;
				$data['pk_id']   = $pk_group;
				$data['amount']  = $amount;
				$data['prop_cnt'] = $propCnt;
				$data['sentiment_value'] = intval($live_prop['sentiment_value'])*intval($propCnt);
				$data['create_time'] = date('Y-m-d H:i:s',time()); */
				
				$res = $live_user_prop_model->insert();
				if($res){
					$this->_successData($res, "送礼成功");
				}
			}
		}else{
			$this->_errorData(0161, '余额不足！');
			exit;
		}
	}
	
	
	/**
	 * 人气打赏接口(跟打赏存在不一样的地方，所以拿出来单写)
	 * @cong.zhao
	 */
	public function actionArewardGifts(){
		$token = !empty($this->params['token']) ? $this->params['token'] : '';
		$userData = $this->_checkToken($token);
		if($userData == false){
			$this->_errorData('0055', '用户未登录');
			exit;
		}
		$propId = !empty($this->params['prop_id']) ? $this->params['prop_id'] : '';
		$propCnt = '1';
		$competitorId = !empty($this->params['competitor_id']) ? $this->params['competitor_id'] : '';
		if(!$propId || !$competitorId || !$propCnt || intval($propCnt)<=0){
			$this->_errorData(0058, '参数错误！');
			exit;
		}
		//限制999个
		if(intval($propCnt)>999){
			$this->_errorData('0159', '最多只能送出999个礼物！');
			exit;
		}
		//检查金币是否足够
		$live_prop = LiveProp::find()->select(['prop_id','name','icon','amount','sentiment_value'])
		->where(['status'=>1,'prop_id'=>$propId])
		->asArray()->one();
		if(!$live_prop){
			$this->_errorData('0154', '道具错误！');
			exit;
		}
		$amount = intval($live_prop['amount'])*intval($propCnt);
		$user = User1::getUserById($userData["userId"]);
		if(intval($user['amount'])>=$amount){
			//扣除金币，并且增加日志记录
			$param['user_id']      = $userData["userId"];
			$param['operate_cnt']  = $amount;
			$param['operate']      = 2;
			$param['operate_name'] = '打赏';
				
			$user_amount_model = new UserAmount();
			$res = $user_amount_model->addUserAmount($param);
			
			//给选手增加人气值
			$live_competitor = LiveCompetitor::find()
			->where(['status'=>1,'competitor_id'=>$competitorId])
			->one();
			if(!$live_competitor){
				$this->_errorData('0160', '选手不存在！');
				exit;
			}else{
				unset($data);
				LiveCompetitor::updateAll(['popular_value'=>intval($live_competitor['popular_value'])+intval($live_prop['sentiment_value'])*intval($propCnt)],['status'=>1,'competitor_id'=>$competitorId]);
				
			}
			//记录道具使用信息
			unset($data);
			$live_user_prop_model = new LiveUserProp();
			$live_user_prop_model->user_id = $userData['userId'];
			$live_user_prop_model->competitor_id = $competitorId;
			$live_user_prop_model->live_id = 0;
			$live_user_prop_model->prop_id = $propId;
			$live_user_prop_model->amount = $amount;
			$live_user_prop_model->prop_cnt = $propCnt;
			$live_user_prop_model->sentiment_value = intval($live_competitor['popular_value'])+intval($live_prop['sentiment_value'])*intval($propCnt);
			$live_user_prop_model->create_time = date('Y-m-d H:i:s',time());
			$res = $live_user_prop_model->save();
			if($res){
				$this->_successData($res, "送礼成功");
			}
		}else{
			$this->_errorData('0161', '余额不足！');
			exit;
		}
	}
	
	
	
	/**
	 * 送礼接口
	 */
	public function actionAddgifts(){
		$token = !empty($_REQUEST['token']) ? $_REQUEST['token'] : '';
		$userData = $this->_checkToken($token);
		if($userData == false){
			$this->_errorData(0055, '用户未登录');
			exit;
		}
		$propId = !empty($_REQUEST['prop_id']) ? $_REQUEST['prop_id'] : '';
		
		$propCnt = !empty($_REQUEST['prop_cnt']) ? $_REQUEST['prop_cnt'] : '';
		$competitorId = !empty($_REQUEST['competitor_id']) ? $_REQUEST['competitor_id'] : '';
		$liveId = !empty($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '';
		if(!$propId || !$competitorId || !$propCnt || intval($propCnt)<=0){
			$this->_errorData(0058, '参数错误！');
			exit;
		}
		//限制999个
		if(intval($propCnt)>999){
			$this->_errorData(0159, '最多只能送出999个礼物！');
			exit;
		}
		//检查金币是否足够
		/* $live_prop = $this->live_prop_model
		->field("prop_id,name,icon,amount,sentiment_value")
		->where("status = 1 and prop_id=".$propId)
		->find(); */
		
		$live_prop = LiveProp::find(['prop_id', 'name', 'icon', 'amount', 'sentiment_value'])
					->where(['status'=>1, 'prop_id'=>$propId])
					->asArray()
					->one();
		
		if(!$live_prop){
			$this->_errorData(0154, '道具错误！');
			exit;
		}
		$amount = intval($live_prop['amount'])*intval($propCnt);
		/* $user = $this->user_model->getUserById($userData["userId"]); */
		$user = User1::getUserById($userData["userId"]);
		
		if(intval($user['amount'])>=$amount){
			//扣除金币，并且增加日志记录
			$param['user_id']      = $userData["userId"];
			$param['operate_cnt']  = $amount;
			$param['operate']      = 2;
			$param['operate_name'] = '打赏';
			
			$user_amount_model = new UserAmount();
			$res = $user_amount_model->addUserAmount($param);
			
			/* $param['user_id']      = $userData["userId"];
			$param['operate_cnt']  = $amount;
			$param['operate']      = 2;
			$param['operate_name'] = '打赏';
			$userAmount            = new UserAmountModel();
			$res = $userAmount->addUserAmount($param); */
			
			//给选手增加人气值
			/* $live_competitor = $this->live_competitor_model
			->where("status=1 and competitor_id=".$competitorId)
			->find(); */
			$live_competitor = LiveCompetitor::find()->where(['status'=>1, 'competitor_id'=>$competitorId])->asArray()->one();
			
			if(!$live_competitor){
				$this->_errorData(0160, '选手不存在！');
				exit;
			}else{
				unset($data);
				
				/* $data['popular_value'] = intval($live_competitor['popular_value'])+intval($live_prop['sentiment_value'])*intval($propCnt);
				$this->live_competitor_model
				->where("status=1 and competitor_id=".$competitorId)
				->save($data); */
				
				$live_competitor_model = new LiveCompetitor();
				$live_competitor_model::updateAll(['popular_value'=>intval($live_competitor['popular_value'])+intval($live_prop['sentiment_value'])*intval($propCnt)],['status'=>1,'competitor_id'=>$competitorId]);
			}
			//记录道具使用信息
			unset($data);
			
			$live_user_prop_model = new LiveUserProp();
			$live_user_prop_model->user_id = $userData['userId'];
			$live_user_prop_model->competitor_id = $competitorId;
			
			/* $data['user_id'] = $userData['userId'];
			$data['competitor_id'] = $competitorId; */
			if($liveId)
				$live_user_prop_model->live_id = $liveId;
				$live_user_prop_model->prop_id = $propId;
				$live_user_prop_model->amount = $amount;
				$live_user_prop_model->prop_cnt =  $propCnt;
				$live_user_prop_model->sentiment_value = intval($live_competitor['popular_value'])+intval($live_prop['sentiment_value'])*intval($propCnt);
				$live_user_prop_model->create_time = date('Y-m-d H:i:s',time());
				$res = $live_user_prop_model->insert();
				
				/* $data['live_id'] = $liveId;
				$data['prop_id'] = $propId;
				$data['amount'] = $amount;
				$data['prop_cnt'] = $propCnt;
				$data['sentiment_value'] = intval($live_competitor['popular_value'])+intval($live_prop['sentiment_value'])*intval($propCnt);
				$data['create_time'] = date('Y-m-d H:i:s',time());
				$res = $this->live_user_prop_model->add($data); */
				if($res){
					$this->_successData($res, "送礼成功");
				}
		}else{
			$this->_errorData(0161, '余额不足！');
			exit;
		}
	}
}