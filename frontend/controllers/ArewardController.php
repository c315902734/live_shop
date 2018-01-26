<?php
namespace frontend\controllers;

use Yii;


class ArewardController extends PublicBaseController{
	
	/**
	 * 人气排行列表
	 * @cong.zhao
	 */

	function actionIndex(){
		$returnData = array();
		$type = isset($this->params['type'])?$this->params['type']:'';
		$timestamp = isset($this->params['timestamp'])?$this->params['timestamp']:'';
		$app_key = isset($this->params['app_key'])?$this->params['app_key']:'';
		$unique = isset($this->params['unique'])?$this->params['unique']:'';
		$sign = isset($this->params['sign'])?$this->params['sign']:'';
		$token = isset($this->params['token'])?$this->params['token']:'';
		$headers = [];
		if($token){
			$header = 'Bearer '.$token;
			$headers = array('Authorization:'.$header);
		}
		
		$user = $this->_getUserModel();
		$amount = 0;
		if(is_object($user)) $amount = $user->amount;
		 
		$header = 'Bearer '.$token;
		
		//请求礼物列表
		$live_prop_data = $this->curl_http(
				Yii::$app->params['api_host'].'/prop/getproplist',
				array(
    				'timestamp'=>$timestamp,
    				'app_key'=>$app_key,
    				'unique'=>$unique,
    				'sign'=>$sign
    			),
				$headers
		);

		if(isset($live_prop_data['Success'])){
			if($live_prop_data['Success'] == '1'){
				$live_prop = $live_prop_data['ReturnData'];
			}else{
				$this->_errorData($live_prop_data['ResultCode'], $live_prop_data['Message']);
			}		
		}else{
			$this->_errorData('401', '请求错误');
		}

		//请求选手列表
		$competitor_data = $this->curl_http(
				Yii::$app->params['api_host'].'/competitor/getcompetitorlist',
				array(
						'timestamp'=>$timestamp,
						'app_key'=>$app_key,
						'unique'=>$unique,
						'sign'=>$sign
				),
				$headers
		);
		if(isset($competitor_data['Success'])){
			if($competitor_data['Success'] == '1'){
				$competitor = $competitor_data['ReturnData'];
			}else{
				$this->_errorData($competitor['ResultCode'], $competitor['Message']);
			}
		}else{
			$this->_errorData('401', '请求错误');
		}


		$returnData['type'] = isset($type) ? $type : '';
		$returnData['token'] = isset($token) ? $token : '';
		$returnData['amount'] = isset($amount) ? $amount : '';
		$returnData['live_prop'] = isset($live_prop) ? $live_prop : '';
		$returnData['data'] = isset($competitor) ? $competitor : '';
		$this->_successData($returnData, "查选成功");
		
	}
	
	

}