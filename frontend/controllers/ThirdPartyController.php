<?php
namespace frontend\controllers;
use common\models\AreaCity;
use common\models\ApiResponse;
use common\models\Invite;
use common\models\UserTask;
use common\models\VisitorToken;
use common\service\Exchange;
use OAuth2\Request;
use Yii;
use common\models\User;
use common\models\User1;
use common\models\UserVerifyCode;
use common\models\UserAmount;
use common\models\OauthAccessTokens;
use common\models\AdminUser;
use yii\db\Query;
use common\service\PublicFunction;

/**
 * 用户相关接口
 */
class ThirdPartyController extends PublicBaseController
{
	protected $VerifyCodeRegType = 1;
	protected $VerifyCodeResetType = 2;
	protected $VerifyCodeChangeMobileType = 3;
	protected $VerifyCodeForgetType = 4;
    
    /**
     * 第三方登录
     * @cong.zhao
     * @param $openId
     * @param $thirdparty
     * @param $sex
     * @param $nick_name
     * @param $avatar
	 * @param $registration_id
     */
    public function actionThirdPartyLogin()
    {
    	$openId = isset($this->params['openId'])?$this->params['openId']:'';
    	$openId = trim($openId);
    	$token = isset($this->params['token'])?$this->params['token']:'';
    	$is_pc = isset($this->params['is_pc'])?$this->params['is_pc']:'0';
    	$thirdparty = isset($this->params['thirdparty'])?$this->params['thirdparty']:'';
		$registration_id = isset($this->params['registration_id']) ? $this->params['registration_id'] : '';
    	if (!in_array($thirdparty, array("weixin","Wechat", "weibo", "qq"))) {
    		$this->_errorData("0002", "登录渠道错误");
    	}
    	
    	$sex = isset($this->params['sex'])?$this->params['sex']:'';
    	$sex = intval($sex);//性别 0未知 1 男  2 女
    	if ($sex != 0 && $sex != 1 && $sex != 2) {
    		$this->_errorData("0002", "性别传入错误");
    	}

    	$nickName = isset($this->params['nick_name'])?$this->params['nick_name']:'';
    	$nickName = trim($nickName);
    	$avatar = isset($this->params['avatar'])?$this->params['avatar']:'';
    	$avatar = trim($avatar);
    	$userType = ($thirdparty == 'weixin' || $thirdparty =='Wechat') ? 2 : ($thirdparty == 'weibo' ? 3 : 4);//用户类型 1 手机号注册  2 微信 3 微博 4 QQ;
    	if($token){
    		$user = User1::getUserByThirdPartyOpenId($openId, $userType);
    		if ($user) {
    			$user->login_time = date("Y-m-d H:i:s",time());
    			$user->login_token = $token;
				if(!empty($registration_id)){
					//清空 跟此reg_id 相同的 值
					User::updateAll(['registration_id'=>''],"registration_id = '".$registration_id."'");
					$user->registration_id = $registration_id;
				}
				$user->registration_id = $registration_id;
    			$user->save();
    			
    			$user = User1::getUserById($user->user_id);
    			$ReturnData['user_id'] = $user->user_id . "";
    			$ReturnData['token'] = $token . "";
    			$ReturnData['rcloud_token'] = empty($user->rcloud_token) ? "" : $user->rcloud_token . "";
    			$ReturnData['birthday'] = empty($user->birthday) ? "" : $user->birthday . "";
    			$ReturnData['avatar'] = $this->getAvatarUrl($user->avatar) . "";
    			$ReturnData['nick_name'] = empty($user->nickname) ? "" : $this->userTextDecode($user->nickname) . "";
    			$ReturnData['sex'] = intval($user->sex);//性别 1 男  2 女
    			$ReturnData['province_id'] = intval($user->province_id);
    			$ReturnData['area_id'] = intval($user->area_id);
    			$ReturnData['province'] = AreaCity::getProvinceName($ReturnData['province_id']);
    			$ReturnData['area'] = AreaCity::getCityName($ReturnData['area_id']);
    			$ReturnData['mobile_phone'] = empty($user->mobile_phone) ? "" : $user->mobile_phone . "";
    			$ReturnData['countries_regions'] = empty($user->countries_regions) ? "+86" : $user->countries_regions . "";
    			$ReturnData['source_type'] = 2;
    			$ReturnData['amount'] = $user->amount;

				$redis = Yii::$app->cache;
				$redis_info = $redis->get(array($user->user_id.$is_pc));
				$redis->delete(array($redis_info['token']));

				PublicFunction::SetRedis($user->user_id.$is_pc,array("token"=>$token,"mobile" => $user->mobile_phone, "third_party_openId" => "", "userId" => $user->user_id));

				// token有效期30天
    			if(PublicFunction::SetRedis($token, array("mobile" => $user->mobile_phone, "third_party_openId" => "", "userId" => $user->user_id))){
    				$access_token_model = OauthAccessTokens::find()->where(['access_token'=>$token])->one();
    				if($access_token_model){
    					$access_token_model->user_id = $user->user_id;
    					$access_token_model->expires = date('Y-m-d H:i:s',strtotime(" + 30 day"));
    					if($access_token_model->save()) $ReturnData['expires'] = $access_token_model->expires;
    					//其他token失效
    					OauthAccessTokens::updateAll(['expires'=>date('Y-m-d H:i:s',strtotime(" - 1 day"))],["access_token" => $redis_info['token'],"user_id" => $user->user_id]);
    				}
    				$this->_successData($ReturnData, "登录成功");
    			}else{
    				$this->_errorData("0070", "非常抱歉，系统繁忙");
    			}
    		}else{
    			$data = array(
    					'open_id' => $openId,
    					'register_time' => date("Y-m-d H:i:s",time()),
    					'login_time' => date("Y-m-d H:i:s",time()),
    					"user_type" => $userType,//手机号注册
    					"user_id" => $this->createId(),
    			);
    			$data['sex'] = $sex;
    			if (!empty($nickName)) {
    				$data['nickname'] = $this->userTextEncode($nickName);
    			}
    			if (!empty($avatar)) {
    				$data['avatar'] = $avatar;
    			}
    			$rcloud_token = $this->get_rcloud_token($data['user_id']);
    			if ($rcloud_token && !empty($rcloud_token)) {
    				$model = new User1();
    				$model->open_id = $data['open_id'];
    				$model->register_time = $data['register_time'];
    				$model->login_time = $data['login_time'];
    				$model->user_type = $data['user_type'];
    				$model->user_id = $data['user_id'];
    				$model->sex = $data['sex'];
    				$model->nickname = isset($data['nickname']) ? $data['nickname'] : '';
    				$model->avatar = isset($data['avatar']) ? $data['avatar'] : '';
    				$model->login_token = $token;
    				$model->rcloud_token = $rcloud_token;
					if(!empty($registration_id)){
						//清空 跟此reg_id 相同的 值
						User::updateAll(['registration_id'=>''],"registration_id = '".$registration_id."'");
						$model->registration_id = $registration_id;
					}
					$model->registration_id = $registration_id;
    				$rst = $model->save();
					$model->mobile_phone = '';  //redis用
					if ($rst) {
						$amount = new UserAmount();
						$param['user_id']     = $data['user_id'];
						$param['operate_cnt'] = '200';
						$param['operate']     = '1';
						$param['operate_name'] = '注册';
						UserAmount::addUserAmount($param);
    					$returnData['user_id'] = $data['user_id'];
						$returnData['token'] = $token;
						$returnData['rcloud_token'] = $rcloud_token;
						$returnData['avatar'] = $avatar . "";
						$returnData['nick_name'] = $this->userTextDecode($nickName . "");
						$returnData['sex'] = $sex;
						$returnData['province_id'] = 0;
						$returnData['area_id'] = 0;
						$returnData['province'] = AreaCity::getProvinceName($returnData['province_id']);
						$returnData['area'] = AreaCity::getCityName($returnData['area_id']);
						$returnData['mobile_phone'] = "";
						$returnData['source_type'] = 2;
						$user_info = User1::findOne($data['user_id']);
						$returnData['amount'] = $user_info->amount;

						$redis = Yii::$app->cache;
						$redis_info = $redis->get(array($model->user_id.$is_pc));
						$redis->delete(array($redis_info['token']));
						//设置用户其他token失效、设置有效期30天
						PublicFunction::SetRedis($data['user_id'].$is_pc,array("token"=>$token,"mobile" => $model->mobile_phone, "third_party_openId" => "", "userId" => $model->user_id));
						// token有效期30天
    					if(PublicFunction::SetRedis($token,array("mobile" => $model->mobile_phone, "third_party_openId" => "", "userId" => $model->user_id))){
    						$access_token_model = OauthAccessTokens::find()->where(['access_token'=>$token])->one();
    						if($access_token_model){
    							$access_token_model->user_id = $model->user_id;
    							$access_token_model->expires = date('Y-m-d H:i:s',strtotime(" + 30 day"));
    							if($access_token_model->save()){
    								$returnData['expires'] = $access_token_model->expires;
    								//其他token失效
//    								OauthAccessTokens::updateAll(['expires'=>date('Y-m-d H:i:s',strtotime(" - 1 day"))],["access_token" => $redis_info['token'],"user_id" => $model->user_id]);
    							}
    						}
	    					
	    					$this->_successData($returnData, "注册成功");
    					}else{
    						$this->_errorData("0070", "非常抱歉，系统繁忙");
    					}
    					
    				} else {
    					$this->_errorData("0070", "非常抱歉，系统繁忙");
    				}
    			} else {
    				$this->_errorData("0080", "非常抱歉，系统繁忙");
    			}	
    		}
    	}else{
    		$this->_errorData("0002", "用户名或密码错误");
    	}
    }
    	  	
    	

    private function get_rcloud_token($user_id)
    {
        $nonce = mt_rand();
        $timeStamp = time();
        $sign = sha1(Yii::$app->params['ryAppSecret'] . $nonce . $timeStamp);
        $header = array(
            'RC-App-Key:' . Yii::$app->params['ryAppKey'],
            'RC-Nonce:' . $nonce,
            'RC-Timestamp:' . $timeStamp,
            'RC-Signature:' . $sign,
        );
        $data = 'userId=' . $user_id . '&name=新汇闻&portraitUri=';
        $result = $this->curl_http(Yii::$app->params['ryApiUrl'] . '/user/getToken.json', $data, $header);
        return $result['token'];
    }
    
    private function getAvatarUrl($avatar)
    {
    	if (!empty($avatar) && strlen($avatar) > 0) {
    		return $avatar;
    	} else {
    		return "";
    	}
    }



    /*
     * 生成 ID
     * */
    private function createId()
    {
        return date('Y') . time() . rand(0000, 9999);
    }
    
    
//    /**
//     * 把用户输入的文本转义（主要针对特殊符号和emoji表情）
//     * @param $str
//     * @return json
//     */
//    private function  userTextEncode($str){
//    	if(!is_string($str))return $str;
//    	if(!$str || $str=='undefined')return '';
//
//    	$text = json_encode($str);
//    	$text = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i",function($str){
//    		return addslashes($str[0]);
//    	},$text);
//    	return json_decode($text);
//    }
//
//    /**
//     *解码上面的转义
//     * @param $str
//     * @return string
//     */
//    private function userTextDecode($str){
//    	$text = json_encode($str);
//    	$text = preg_replace_callback('/\\\\\\\\/i',function($str){
//    		return '\\';
//    	},$text);
//    	return json_decode($text);
//    }

	//根据设备获取融云token
	public function actionGetVisitorRcloudToken()
	{
		$phone_id = isset($_REQUEST['phone_id']) ? $_REQUEST['phone_id'] : '';   //接收设备id
		$user_id  = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';   //用户id
		if(empty($phone_id)){
			$this->_errorData("0061", "设备id不能为空");
		}
		if(!empty($user_id)){
			$rcloud_token = $this->get_rcloud_token($user_id);			//根据设备id获取token
			$visitor = new VisitorToken();
			$visitor->phone_id     = $phone_id;
			$visitor->rcloud_token = $rcloud_token;
			$add = $visitor->save();
			if($add=='0'){
				$this->_errorData("0062", "数据添加失败");
			}
			$user = User1::findOne($user_id);
			if(!$user){
				$this->_errorData("0005", "用户不存在");
			}
			$user->rcloud_token = $rcloud_token;
			$user->save();
		}else{
			$rcloud_token = $this->get_rcloud_token($phone_id);			//根据设备id获取token
			$visitor = new VisitorToken();
			$visitor->phone_id     = $phone_id;
			$visitor->rcloud_token = $rcloud_token;
			$add = $visitor->save();
			if($add=='0'){
				$this->_errorData("0062", "数据添加失败");
			}
		}

		if(!empty($rcloud_token)){
			$this->_successData($rcloud_token, "token获取成功");
		}else{
			$this->_errorData("0063", "token获取失败");
		}						//返回token
	}

	/**
	 * 发送验证码
	 */
	public function actionSendVerifyCode()
	{
		$mobile    = $this->params["mobile"];
		$mobile    = trim($mobile);
		//拼接手机号
		$countries_regions = isset($this->params['countries_regions']) ? trim($this->params['countries_regions']) : '';

		$timestamp = $this->params["timestamp"];
		$timestamp = trim($timestamp);
		$signature = $this->params["signature"];
		$signature = trim($signature);
		$fromType  = $this->params["from_type"];
		$fromType  = intval($fromType);//1注册|2重置密码|3更换手机号|4找回密码|5采集端绑定手机号
		//echo $mobile."---".$timestamp."<br>";
		$tmp = md5($mobile . "_xhw_" . $timestamp);
//        echo $tmp;die();
		if (empty($mobile)) {
			$this->_errorData("0010", "请传入手机号");
		}
		if (!$this->_checkMobile($countries_regions, $mobile)) {
			$this->_errorData("0011", "手机号码格式错误");
		}
		if (empty($timestamp)) {
			$this->_errorData("0020", "请传入时间戳");
		}
		if (empty($signature)) {
			$this->_errorData("0030", "请传入签名");
		}
		if ($fromType <= 0) {
			$this->_errorData("0041", "请传入类型");
		}
		if (!in_array($fromType, array(1, 2, 3, 4,5))) {
			$this->_errorData("0042", "类型错误");
		}
		//1注册|2重置密码|3更换手机号|4找回密码|5白名单使用
		if ($fromType == 1) {
			$user = User::getUserByLogin($countries_regions, $mobile);
                        if (is_array($user)) {
 				$this->_errorData("0043", "此手机号码已被占用");
			}
		} elseif ($fromType == 2) {  //------
			$user = $this->_getUserModel();
			if ($countries_regions.$mobile != $user['countries_regions'].$user['mobile_phone']) {
				$this->_errorData("0043", "输入的手机号与当前绑定的手机号不一致");
			}
		} elseif ($fromType == 3) {
			$user = User::getUserByLogin($countries_regions, $mobile);
                        if (is_array($user)&&$user['open_id']) {
                                $this->_errorData("0043", "该手机号已经注册,请使用手机号或者第三方账号登录!");
                        }
		} elseif ($fromType == 4) {
			$user = User::getUserByLogin($countries_regions, $mobile);
			if (!is_array($user)) {
				$this->_errorData("0043", "此手机号码未注册，请您注册");
			}
		}

		elseif ($fromType == 5) {
			$is_bind       = AdminUser::find()->where(['mobile'=>$mobile])->one();
			 if($is_bind){
			 		$this->_errorData("0043", "此手机号码已绑定!");

			 }

		}

		// 验证签名
		if ($tmp == $signature) {
			$code = UserVerifyCode::getUserVerifyCode($countries_regions, $mobile, $fromType);
			//验证码短信控制一分钟只能发一次
			if ($code == NULL || (time() - strtotime($code['create_time'])) > 60) {
				$verifyCode = "8888";
				//$flag = true;
				$flag = false;
				//5分钟内有效 无需重新生成验证码
				if ($code == NULL || (time() - strtotime($code['create_time'])) > 300) {
					$verifyCode = rand(1000, 9999);
					$flag = true;
				} else {
					$verifyCode = $code['verify_code'];
				}
				//发送验证码
				if(preg_match("/^1[34578]\d{9}$/", $mobile) && $countries_regions == ''){
					//如果是国内大陆手机号  11位数
					$rst = $this->_sendVerifyCode($mobile, $verifyCode);
				}else{
					if(strpos($countries_regions, '+') !== false){
						$countries_regions = substr($countries_regions, 1);
					}
					$International_mobile = '00'.$countries_regions.$mobile;
					$rst = $this->_sendInternationalVerifyCode($International_mobile, $verifyCode);
				}
				
				if ($rst == 0) {
					$this->_errorData("0002", "发送失败");
				} else {
					if ($flag) {
						$user_model = new  UserVerifyCode();
						//5分钟内有效
						//S($mobile, $verifyCode, 60*5);
						$user_model->countries_regions = $countries_regions;
						$user_model->mobile_phone = $mobile;
						$user_model->verify_code  = (String)$verifyCode;
						$user_model->create_time  = date('Y-m-d H:i:s');
						$user_model->status    = 0;
						$user_model->from_type = $fromType;
						//if ($code == NULL) {//没有则添加
						//$data['code_id'] = time() . rand();
						$user_model->code_id = $this->createId();
						$user_model->save();
						//echo  $this->user_verify_code_model->getLastSql();
						// } else {
						//    $this->user_verify_code_model->where("code_id={$code['code_id']}")->save($data);
						//}
					}
					//S($mobile . '_flag', $verifyCode, 60);
					$this->_successData(null, "发送成功");
				}
			} else {
				$this->_errorData("0003", "发送太频繁");
			}
		} else {
			$this->_errorData("0010", "签名错误");
		}

	}

	/**
	 * 注册
	 * mobile String  否  手机号
	 * verify_code String 否 短信验证码
	 * password String 否  密码
	 * re_password String 否 确认密码
	 * token  String 否 token
	 * registration_id String 是 推送注册ID
	 */
	public function actionRegister()
	{
		$countries_regions = isset($this->params["countries_regions"]) ? trim($this->params["countries_regions"]) : '';
		$mobile = $this->params["mobile"];
		$mobile = trim($mobile);
		$token = $this->params["token"];
		$token = trim($token);

		$verifyCode = $this->params["verify_code"];
		$verifyCode = trim($verifyCode);
		$password = $this->params["password"];
		$password = trim($password);
		$rePassword = $this->params["re_password"];
		$rePassword = trim($rePassword);
		$registration_id = isset($this->params["registration_id"]) ? $this->params["registration_id"] : '';
		$registration_id = trim($registration_id);
		if (empty($mobile)) {
			$this->_errorData("0010", "请传入手机号");
		}
		if (!$this->_checkMobile($countries_regions, $mobile)) {
			$this->_errorData("0011", "手机号码格式错误");
		}
		if (empty($verifyCode)) {
			$this->_errorData("0020", "请填写手机验证码");
		}
		if (empty($password)) {
			$this->_errorData("0030", "请输入密码");
		}
		if (empty($rePassword)) {
			$this->_errorData("0040", "请确认密码");
		}

		//处理验证码
		$this->checkVerifyCode($countries_regions, $mobile, $verifyCode, $this->VerifyCodeRegType);
		if (strlen($password) < 6 || strlen($password) > 18) {
			$this->_errorData("0031", "密码长度至少6位，最多18位！");

		}
		if ($password != $rePassword) {
			$this->_errorData("0032", "两次输入密码不一致");
		}

		$result = User::find()->where(["mobile_phone"=>$mobile])->one();

		if ($result) {
			$this->_errorData("0060", "手机号已被注册");
		} else {
			$user_model = new User();
			$user_model->mobile_phone  = $mobile;
			$user_model->password      = $this->sp_password($password);
			$user_model->register_time = date("Y-m-d H:i:s");
			$user_model->login_time    = date("Y-m-d H:i:s");
			$user_model->user_type     = 1;//手机号注册
			$create_uid = $this->createId();
			$user_model->user_id       = $create_uid;

			$rcloud_token = $this->get_rcloud_token($user_model->user_id);

			if ($rcloud_token && !empty($rcloud_token)) {
//				$token = $this->createToken();
				$user_model->login_token  = $token;
				$user_model->rcloud_token = $rcloud_token;
				$user_model->nickname     = "匿名用户".substr($mobile,7 );
				if(!empty($registration_id)){
					//清空 跟此reg_id 相同的 值
					User::updateAll(['registration_id'=>''],"registration_id = '".$registration_id."'");
					$user_model->registration_id = $registration_id;
				}
				if(!empty($countries_regions)){
					$user_model->countries_regions = $countries_regions;
				}
				$user_model->registration_id = $registration_id;

				$rst = $user_model->save();
				if ($rst) {
					//将用户 和token 绑定
					OauthAccessTokens::updateAll(['user_id' => $create_uid], "access_token = '".$token."'");

					//设置用户其他token失效、设置有效期30天
					$expires = User1::setAccessToken(null, $create_uid, $token);
					$returnData['expires']      = $expires;
					$returnData['user_id']      = $user_model->user_id;
					$returnData['token']        = $token;
					$returnData['rcloud_token'] = $rcloud_token;
					$returnData['avatar']       = "";
					$returnData['nick_name']    = "匿名用户".substr($mobile,7 );
					$returnData['sex']          = 0;
					$returnData['province_id']  = 0;
					$returnData['area_id']      = 0;
					$returnData['province']     = AreaCity::getProvinceName($returnData['province_id']);
					$returnData['area']         = AreaCity::getCityName($returnData['area_id']);
					$returnData['mobile_phone'] = $mobile . "";
					$returnData['countries_regions'] = empty($countries_regions) ? '+86' : $countries_regions; 
					// token有效期30天
					try {
						PublicFunction::SetRedis($token,array("mobile" => $mobile, "userId" => $user_model->user_id));
					} catch (\Exception $e) {
						S($token, array("mobile" => $mobile, "userId" => $user_model->user_id), 2592000000);
					}
					$amount['user_id']      = $user_model->user_id;
					$amount['operate_cnt']  = '200';
					$amount['operate']      = '1';
					$amount['operate_name'] = '注册';
					UserAmount::addUserAmount($amount);
					$this->_successData($returnData, "注册成功");
				} else {
					$this->_errorData("0070", "非常抱歉，系统繁忙");
				}
			} else {
				$this->_errorData("0080", "非常抱歉，系统繁忙");
			}
		}
	}

	/**
	 * 忘记密码
	 */
	public function actionForgetPwd()
	{
		$mobile = isset($this->params['mobile']) ? $this->params['mobile'] : '';
		$countries_regions = isset($this->params['countries_regions']) ? trim($this->params['countries_regions']) : '';
		$mobile = trim($mobile);
		$verifyCode = isset($this->params['verify_code']) ? $this->params['verify_code'] : '';
		$verifyCode = trim($verifyCode);
		$password = isset($this->params['password']) ? $this->params['password'] : '';
		$password = trim($password);
		$rePassword = isset($this->params['re_password']) ? $this->params['re_password'] : '';
		$rePassword = trim($rePassword);
		if (empty($mobile)) {
			$this->_errorData("0010", "请传入手机号");
		}
		if (!$this->_checkMobile($countries_regions, $mobile)) {
			$this->_errorData("0011", "手机号码格式错误");
		}
		if (empty($verifyCode)) {
			$this->_errorData("0020", "请填写手机验证码");
		}
		if (empty($password)) {
			$this->_errorData("0030", "请输入密码");
		}
		if (empty($rePassword)) {
			$this->_errorData("0040", "请确认密码");
		}
		//处理验证码
		$this->checkVerifyCode($countries_regions, $mobile, $verifyCode, $this->VerifyCodeForgetType);
		if (strlen($password) < 6 || strlen($password) > 18) {
			$this->_errorData("0031", "密码长度至少6位，最多18位！");

		}
		if ($password != $rePassword) {
			$this->_errorData("0032", "两次输入密码不一致");
		}

		$result = User1::find()->where(['mobile_phone' => $mobile])->one();
		if ($result) {
			$password = md5(md5($password));
			$result->password = $password;
			$rst = $result->save();
			if ($rst !== false) {
				$this->_successData(NULL, "重置密码成功，请您重新登陆");
			} else {
				$this->_errorData("5000", "非常抱歉，上传失败");
			}

		} else {
			$this->_errorData("4004", "此手机号码未注册，请您注册");
		}
	}

	/**
	 * 兑吧  生产免登陆url
	 */
	public function actionCreateAutoLoginRequest(){
		$user_id     = isset($this->params["user_id"]) ? $this->params["user_id"] : '';
		$current_url = isset($this->params["currentUrl"]) ? $this->params["currentUrl"] : '';

		if(!empty($user_id)){
			$user_info = User::find()->where(['user_id'=>$user_id])->asArray()->one();
			$amount    = $user_info['amount'];
		}else{
			$user_id   = 'not_login';
			$amount    = 0;
		}
		$url = Exchange::buildCreditAutoLoginRequest(Yii::$app->params['appKey'], Yii::$app->params['appSecret'], $user_id, $amount, $current_url);
		$data['url'] = $url;
		$this->_successData($data);




	}
	/*
	 * 供m站 观看直播 手机白名单 验证验证码 使用
	 *
	 * */
	public function actionCheckMCode(){
		$countries_regions = isset($this->params["countries_regions"]) ? trim($this->params["countries_regions"]) : '';
		$mobile = $this->params["mobile"];
		$mobile = trim($mobile);
		$verifyCode = $this->params["verify_code"];
		$verifyCode = trim($verifyCode);

		//处理验证码
		$this->checkVerifyCode($countries_regions, $mobile, $verifyCode, 5);
		$this->_successData(1);
	}

	/**
	 * 处理验证码
	 */
	private function checkVerifyCode($countries_regions, $mobile, $verifyCode, $fromType)
	{
		//处理验证码

		$code = UserVerifyCode::getUserVerifyCode($countries_regions, $mobile, $fromType);
		if ($code == NUll || $verifyCode != $code['verify_code']) {
			$this->_errorData("0050", "验证码错误");
		} else {
			if ((time() - strtotime($code['create_time'])) > 300) {
				$this->_errorData("0051", "验证码已过期");
			}
			$verify = UserVerifyCode::find()->where(['code_id'=>$code['code_id']])->one();
			$verify->status = 1;
			$verify->save();
		}
	}

	private function createToken()
	{
		//生成token
		$chars = md5(uniqid(mt_rand(), true));
		$token = substr($chars, 0, 8) . '-';
		$token .= substr($chars, 8, 4) . '-';
		$token .= substr($chars, 12, 4) . '-';
		$token .= substr($chars, 16, 4) . '-';
		$token .= substr($chars, 20, 12);
		return $token;
	}

//	protected function _getUserModel($isArray = false)
//	{
//		$userData = $this->_getUserData();
//		$user = User1::find()->where(['user_id'=>$userData['userId']])->asArray($isArray)->one();
//		if (empty($user)) {
//			$this->_errorData('404', '查找用户失败');
//		}
//		return $user;
//	}

	/*
     * 发送验证码
     * */
	private function _sendVerifyCode($mobile, $verifyCode)
	{
		//return 1;
		$account = 'fzyxw';
		$password = '557106';
		//$mobile = $info['mobile'];
		//$number = $info['content'];
		$content = "您的验证码为" . $verifyCode . "，如非本人操作请忽略。【法制与新闻】";
		$target = "http://sms.chanzor.com:8001/sms.aspx";
		$post_data = "action=send&userid=&account={$account}&password={$password}&mobile={$mobile}&sendTime=&content=" . rawurlencode($content);
		$url_info = parse_url($target);
		$httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
		$httpheader .= "Host:" . $url_info['host'] . "\r\n";
		$httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
		$httpheader .= "Content-Length:" . strlen($post_data) . "\r\n";
		$httpheader .= "Connection:close\r\n\r\n";
		$httpheader .= $post_data;

		$fd = fsockopen($url_info['host'], 80);
		fwrite($fd, $httpheader);
		$gets = "";
		while (!feof($fd)) {
			$gets .= fread($fd, 128);
		}
		fclose($fd);
		$start = strpos($gets, "<?xml");
		$data = substr($gets, $start);
		$xml = simplexml_load_string($data);
		$arr = json_decode(json_encode($xml), true);
//        print_r($arr);
		if ($arr['returnstatus'] == 'Success') {
			return 1;
		} else {
			return 0;
		}
	}
	
	/**
	 * 发送国际短信验证码
	 */
	private function _sendInternationalVerifyCode($mobile, $verifyCode)
	{
		$content   = 'Dear user, your verification code is '.$verifyCode;
		$post_url  = 'http://inter.chanzor.com/send?account=fzyxw2&password='.strtoupper(md5('557106')).'&mobile='.$mobile.'&content='.rawurlencode($content);
		
		$ret = $this->curl_http($post_url);
		if($ret){
			if($ret['status'] == 0){
				return 1;
			}else{
				return 0;
			}
		}else{
			return 0;
		}
	}

	/**
	 * 邀请-获取用户信息
	 * user_id 用户ID
	 */
	public function actionGetuser(){
		$user_id = $this->params['user_id'];
		if(!$user_id){
			$this->_errorData('0054','用户不存在' );
		}
		$user_info = User::find()->where(['user_id'=>$user_id])->select("user_id,nickname,avatar")->asArray()->one();
		$user_info['nickname'] = $this->userTextDecode($user_info['nickname']);
		if(!$user_info){
			$this->_errorData('0054','用户不存在' );
		}
		if(empty($user_info['avatar'])){
			$user_info['avatar'] = '';
		}
		$this->_successData($user_info);
	}

	/**
	 * 被邀请人注册
	 * user_id     Int     否   邀请人ID
	 * invite_type Int     否   邀请方式
	 * mobile      String  否   手机号
	 * nickname   String  否   昵称
	 * verify_code String  否  短信验证码
	 * password    String  否  密码
	 */
	public function actionInviteRegister()
	{
		$user_id   = $this->params["user_id"];
		$user_id   = trim($user_id);
		$mobile    = $this->params["mobile"];
		$mobile    = trim($mobile);
//		$nick_name = $this->params["nickname"];
//		$nick_name = trim($nick_name);
		$invite_type = $this->params["invite_type"];
		$invite_type = trim($invite_type);

		$verifyCode = $this->params["verify_code"];
		$verifyCode = trim($verifyCode);
		$password = $this->params["password"];
		$password = trim($password);

		if (empty($user_id)) {
			$this->_errorData("0009", "请传入用户ID");
		}
		if (empty($mobile)) {
			$this->_errorData("0010", "请传入手机号");
		}
		if (!$this->_checkMobile('', $mobile)) {
			$this->_errorData("0011", "手机号格式不正确");
		}
		if (empty($verifyCode)) {
			$this->_errorData("0020", "请填写手机验证码");
		}
		if (empty($password)) {
			$this->_errorData("0030", "请输入密码");
		}
//		if (empty($nick_name)) {
//			$this->_errorData("0040", "请输入昵称");
//		}
		
		//处理验证码
		$this->checkVerifyCode('', $mobile, $verifyCode, $this->VerifyCodeRegType);
		if (strlen($password) < 6 || strlen($password) > 18) {
			$this->_errorData("0031", "请输入正确的密码");
		}

		$result = User::find()->where(["mobile_phone"=>$mobile])->one();

		if ($result) {
			$this->_errorData("0060", "该手机号已被占用");
		} else {
			//验证昵称唯一性
//			$user_name = $this->userTextEncode($nick_name);
//			$user_is_exist = User::find()->where(['nickname'=>$user_name])->one();
//
//			if($user_is_exist){
//				$this->_errorData('0061', '该昵称已被占用');
//			}

			$user_model = new User();
			$user_model->mobile_phone  = $mobile;
			$user_model->password      = $this->sp_password($password);
			$user_model->register_time = date("Y-m-d H:i:s");
			$user_model->user_type     = 1;//手机号注册
			$create_uid = $this->createId();
			$user_model->user_id       = $create_uid;
//			$user_model->nickname      = $this->userTextEncode($nick_name);
			$phone_str = substr($mobile,-4);
			$user_model->nickname = "匿名用户".$phone_str;
			$user_model->invite_status = 1; //邀请方式注册

			$rcloud_token = $this->get_rcloud_token($user_model->user_id);

			if ($rcloud_token && !empty($rcloud_token)) {
				$user_model->rcloud_token = $rcloud_token;

				$rst_user = $user_model->save();
				//记录到邀请表
				$invite = new Invite();
				$invite->user_id = $user_id;
				$invite->invite_user_id = $create_uid;
//				$invite->invite_user_name = $this->userTextEncode($nick_name);
				$phone_str = substr($mobile,-4);
				$invite->invite_user_name = "匿名昵称".$phone_str;
				$invite->invite_type = $invite_type;
				$invite->create_time = date("Y-m-d H:i:s");
				$rst_invite = $invite->save();


				if ($rst_user && $rst_invite) {
					$this->_successData('0000', "注册成功");
				} else {
					$this->_errorData("0070", "非常抱歉，系统繁忙");
				}
			} else {
				$this->_errorData("0080", "非常抱歉，系统繁忙");
			}
		}
	}


}