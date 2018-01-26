<?php
namespace frontend\controllers;
use app\models\UserAreaPush;
use common\models\AreaCity;
use common\models\ApiResponse;
use common\models\FeedBack;
use common\models\Invite;
use common\models\InviteReg;
use common\models\News;
use common\models\UserTask;
use common\service\Textcode;
use OAuth2\Request;
use Yii;
use common\models\User;
use common\models\User1;
use common\models\UserVerifyCode;
use common\models\UserAmount;
use common\service\PublicFunction;

/**
 * 用户相关接口
 */
class UserController extends BaseApiController
{
    protected $VerifyCodeRegType = 1;
    protected $VerifyCodeResetType = 2;
    protected $VerifyCodeChangeMobileType = 3;
	protected $VerifyCodeForgetType = 4;

	/**
	 * 登录
	 * @cong.zhao
	 * @param $username
	 * @param $password
	 * @param $registration_id
	 */
    public function actionLogin(){
    	$username = isset($this->params['username'])?$this->params['username']:'';
    	$pwd      = isset($this->params['password'])?$this->params['password']:'';
    	$token    = isset($this->params['token'])?$this->params['token']:'';
    	$is_pc    = isset($this->params['is_pc'])?$this->params['is_pc']:'0';
		$registration_id = isset($this->params['registration_id']) ? $this->params['registration_id'] : '';
		$is_pc    = isset($this->params['is_pc']) ? $this->params['is_pc'] : '0';
    	$username = trim($username);
    	$pwd      = trim($pwd);
		$registration_id = trim($registration_id);
    	if($token){
    		if (empty($username)) {
    			$this->_errorData("0010", "请传入用户名");
    		}
    		if (empty($pwd)) {
    			$this->_errorData("0020", "请传入密码");
    		}
    		//校验密码
    		$user = User1::getUserByLogin($username);
		
    		/* 提示 未注册 */
    		if(!$user){
    			$this->_errorData("0004", "该手机号未注册");
    		}
    		
    		//用户状态
    		$status=$user->status;
    		if($status=='0'){
    			$this->_errorData("0003", "用户已被禁用");
    		}else{
    			if ($user && md5(md5($pwd)) == $user->password) {
					if($user->invite_status == 1){
						//邀请 且未激活的 用户
						$this->invite_login($user->user_id);
						$user = User1::getUserByLogin($username);
						$user->invite_status = 2;
					}
    				$ReturnData['user_id'] = $user->user_id . "";
    				$ReturnData['token'] = $token . "";
    				$ReturnData['rcloud_token'] = empty($user->rcloud_token) ? "" : $user->rcloud_token . "";
    				$ReturnData['avatar'] = User1::getAvatarUrl($user->avatar) . "";
    				$ReturnData['nick_name'] = empty($user->nickname) ? "" : $this->userTextDecode($user->nickname . "");
    				$ReturnData['birthday'] = empty($user->birthday) ? "" : $user->birthday . "";
    				$ReturnData['sex'] = intval($user->sex);//性别 1 男  2 女
    				$ReturnData['province_id'] = intval($user->province_id);
    				$ReturnData['area_id'] = intval($user->area_id);
    				$ReturnData['province'] = AreaCity::getProvinceName($ReturnData['province_id']);
    				$ReturnData['area'] = AreaCity::getCityName($ReturnData['area_id']);
    				$ReturnData['mobile_phone'] = empty($user->mobile_phone) ? "" : $user->mobile_phone . "";
    				$ReturnData['countries_regions'] = empty($user->countries_regions) ? "+86" : $user->countries_regions . "";
    				$ReturnData['source_type'] = 1;
    				$ReturnData['amount'] = $user->amount;
    				$user->login_time = date("Y-m-d H:i:s");
    				$user->login_token = $token;
					if(!empty($registration_id)){
						//清空 跟此reg_id 相同的 值
						User::updateAll(['registration_id'=>''],"registration_id = '".$registration_id."'");
						$user->registration_id = $registration_id;
					}
					$user->registration_id = $registration_id;
    				if($user->save()){
						$redis = Yii::$app->cache;
						$redis_info = $redis->get(array($user->user_id.$is_pc));
						$redis->delete(array($redis_info['token']));
						//设置用户其他token失效、设置有效期30天
						$expires = User1::setAccessToken($redis_info['token'], $user->user_id,$token);
						PublicFunction::SetRedis($user->user_id.$is_pc,array("token"=>$token,"mobile" => $user->mobile_phone, "third_party_openId" => "", "userId" => $user->user_id));


						if($expires) $ReturnData['expires'] = $expires;
    					// token有效期30天
    					PublicFunction::SetRedis($token,array("mobile" => $user->mobile_phone, "third_party_openId" => "", "userId" => $user->user_id));
					}
    				$this->_successData($ReturnData, "登录成功");
    			}else{
    				$this->_errorData("0002", "用户名或密码错误");
    			}
    		}
    	}else{
    		$this->_errorData("0002", "用户名或密码错误");
    	}
    }

	/**邀请 且未激活的 用户登陆
	 * 判断 积分 每日任务 更改邀请记录状态激活时间
	*/
	public function invite_login($user_id)
	{
		//被邀请人 首次登陆 正常增加 200
		$amount['user_id']      = $user_id;
		$amount['operate_cnt']  = '200';
		$amount['operate']      = '1';
		$amount['operate_name'] = '首次登陆';
		UserAmount::addUserAmount($amount);

		//被邀请人 首次登陆 邀请激活奖励 100
		$amount['user_id']      = $user_id;
		$amount['operate_cnt']  = '100';
		$amount['operate']      = '1';
		$amount['operate_name'] = '邀请激活奖励';
		UserAmount::addUserAmount($amount);


		//邀请朋友 奖励 --每日任务 100
		$invite = Invite::find()->where(['invite_user_id'=>$user_id])->asArray()->one();
		$amount['user_id']      = $invite['user_id'];
		$amount['operate_cnt']  = '100';
		$amount['operate']      = '1';
		$amount['operate_name'] = '邀请奖励';
		$amount['task_id']      = 6;
		UserAmount::addUserAmount($amount);

		//更新 邀请记录
		Invite::updateAll(['amount'=>100,'is_login'=>1,'login_time'=>date("Y-m-d H:i:s")],['invite_user_id'=>$user_id]);

		return true;

	}
    
    /**
     * 修改密码
     * @cong.zhao
     */
    public function actionChangePwd()
    {
    	$user = $this->_getUserModel();
    	if($user){
    		$countries_regions = isset($this->params['countries_regions'])?trim($this->params['countries_regions']):'';
    		$mobile = isset($this->params['mobile'])?$this->params['mobile']:'';
    		$verifyCode = isset($this->params['verify_code'])?$this->params['verify_code']:'';
    		$password = isset($this->params['password'])?$this->params['password']:'';
    		$mobile = trim($mobile);
    		$verifyCode = trim($verifyCode);
    		$password = trim($password);
    		if (empty($mobile)) {
    			$this->_errorData("0010", "请传入手机号");
    		}
    		if (!$this->_checkMobile($countries_regions, $mobile)) {
    			$this->_errorData("0011", "手机号码格式错误");
    		}
    		if ($mobile != $user->mobile_phone) {
    			$this->_errorData("0012", "输入的手机号与当前绑定的手机号不一致");
    		}
    		if (empty($verifyCode)) {
    			$this->_errorData("0020", "请填写手机验证码");
    		}
    		//处理验证码
    		$this->checkVerifyCode($countries_regions, $mobile, $verifyCode, $this->VerifyCodeResetType);
    		if (empty($password)) {
    			$this->_errorData("0030", "请输入密码");
    		}
    		
    		if (strlen($password) < 6 || strlen($password) > 18) {
    			$this->_errorData("0031", "密码长度至少6位，最多18位！");
    		}
    		$user->password = md5(md5($password));
    		if ($user->save()) {
    			$this->_successData(NULL, "修改密码成功");
    		} else {
    			$this->_errorData("5000", "非常抱歉，系统繁忙");
    		}
    	}else{
    		$this->_errorData("0002", "用户不存在");
    	}
    }
    
    
    /**
     * 修改手机号
     * @cong.zhao
     */
    public function actionChangeMobile()
    {
    	$user = $this->_getUserModel();
    	if($user){
    		$old_countries_regions = isset($this->params["old_countries_regions"]) ? trim($this->params["old_countries_regions"]) : '';
    		$mobile = isset($this->params['mobile'])?$this->params['mobile']:'';
    		//$mobile = $old_countries_regions.$mobile;
    		
    		$new_countries_regions = isset($this->params["new_countries_regions"]) ? trim($this->params["new_countries_regions"]) : '';
    		$newMobile = isset($this->params['new_mobile'])?$this->params['new_mobile']:'';
    		//$newMobile = $new_countries_regions.$newMobile;
    		
    		$verifyCode = isset($this->params['verify_code'])?$this->params['verify_code']:'';
    		$mobile = trim($mobile);
    		$newMobile = trim($newMobile);
    		$verifyCode = trim($verifyCode);
    		if (empty($mobile)) {
    			$this->_errorData("0010", "请传入手机号");
    		}
    		
    		if (!$this->_checkMobile($old_countries_regions, $mobile)) {
    			$this->_errorData("0011", "手机号码格式错误");
    		}
    		if ($mobile != $user['mobile_phone']) {
    			$this->_errorData("0012", "输入的手机号与当前绑定的手机号不一致");
    		}
    		if (empty($newMobile)) {
    			$this->_errorData("0030", "请输入新手机号");
    		}
    		if ($newMobile == $user->mobile_phone) {
    			$this->_errorData("0031", "新手机号与当前绑定的手机号一致，不可更换！");
    		}
    		if (empty($verifyCode)) {
    			$this->_errorData("0020", "请填写手机验证码");
    		}
    		//处理验证码
    		$this->checkVerifyCode($new_countries_regions, $newMobile, $verifyCode, $this->VerifyCodeChangeMobileType);
    		$user->mobile_phone = $newMobile;
    		$user->countries_regions = $new_countries_regions;
    		
    		if ($user->save()) {
    			$this->_successData(NULL, "更换手机号成功");
    		} else {
    			$this->_errorData("5000", "非常抱歉，系统繁忙");
    		}
    	}else{
    		$this->_errorData("0002", "用户不存在");
    	}
    }
    
    	
    	/**
    	 * 发送验证码
    	 */
    	public function sendVerifyCode()
    	{
    		$mobile = I("mobile");
    		$mobile = trim($mobile);
    		$timestamp = I("timestamp");
    		$timestamp = trim($timestamp);
    		$signature = I("signature");
    		$signature = trim($signature);
    		$fromType = I("from_type");
    		$fromType = intval($fromType);//1注册|2重置密码|3更换手机号|4找回密码
    		//echo $mobile."---".$timestamp."<br>";
    		$tmp = md5($mobile . "_xhw_" . $timestamp);
    		//echo $tmp;die();
    		if (empty($mobile)) {
    			$this->_errorData("0010", "请传入手机号");
    		}
    		if (!$this->_checkMobile($mobile)) {
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
    		if (!in_array($fromType, array(1, 2, 3, 4))) {
    			$this->_errorData("0042", "类型错误");
    		}
    		//1注册|2重置密码|3更换手机号|4找回密码
    		if ($fromType == 1) {
    			$user = $this->user_model->getUserByLogin($mobile);
    			if (is_array($user)) {
    				$this->_errorData("0043", "此手机号码已被占用");
    			}
    		} elseif ($fromType == 2) {
    			$user = $this->_getUserModel();
    			if ($mobile != $user['mobile_phone']) {
    				$this->_errorData("0043", "输入的手机号与当前绑定的手机号不一致");
    			}
    		} elseif ($fromType == 3) {
    			$user = $this->user_model->getUserByLogin($mobile);
    			if (is_array($user)) {
    				$this->_errorData("0043", "此手机号码已被他人使用，请更换其他手机号");
    			}
    		} elseif ($fromType == 4) {
    			$user = $this->user_model->getUserByLogin($mobile);
    			if (!is_array($user)) {
    				$this->_errorData("0043", "此手机号码未注册，请您注册");
    			}
    		}
    	
    		//echo $tmp  ."<br>". $signature;die();
    		// 验证签名
    		if ($tmp == $signature) {
    			$code = $this->user_verify_code_model->getUserVerifyCode($mobile, $fromType);
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
    				$rst = $this->_sendVerifyCode($mobile, $verifyCode);
    	
    				if ($rst == 0) {
    					$this->_errorData("0002", "发送失败");
    				} else {
    					if ($flag) {
    						//5分钟内有效
    						//S($mobile, $verifyCode, 60*5);
    						$data['mobile_phone'] = $mobile;
    						$data['verify_code'] = $verifyCode;
    						$data['create_time'] = date('Y-m-d H:i:s');
    						$data['status'] = 0;
    						$data['from_type'] = $fromType;
    						//if ($code == NULL) {//没有则添加
    						//$data['code_id'] = time() . rand();
    						$data['code_id'] = $this->createId();
    						$this->user_verify_code_model->add($data);
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
	 * 获取我的用户信息
	 */
	public function actionGetMyInfo()
	{
		$user = $this->_getUserModel(true);
		$returnArr['user_id'] = $user['user_id'];
		$returnArr['username'] = $this->userTextDecode($user['username'] . "");
		$returnArr['nickname'] = $this->userTextDecode($user['nickname'] . "");
		$returnArr['nick_name'] = $this->userTextDecode($user['nickname'] . "");
		$returnArr['mobile_phone'] = $user['mobile_phone'] . "";
		$returnArr['countries_regions'] = empty($user['countries_regions']) ? "+86" : $user['countries_regions'];
		$returnArr['avatar'] = User1::getAvatarUrl($user['avatar']). "";
		$returnArr['sex'] = intval($user['sex']);
		$returnArr['birthday'] = $user['birthday'] . "";
		$returnArr['province_id'] = intval($user['province_id']);
		$returnArr['area_id'] = intval($user['area_id']);
		$returnArr['province'] = AreaCity::getProvinceName($returnArr['province_id']);
		$returnArr['area'] = AreaCity::getCityName($returnArr['area_id']);
		$returnArr['amount'] = $user['amount'];
		$returnArr['user_type'] = $user['user_type'];
		$returnArr['is_sign'] = UserTask::isSign($user['user_id']);
		$this->_successData($returnArr, "获取数据成功");
	}

	//绑定手机号
	public function actionBindPhone()
	{
		$user_id = isset($this->params['user_id']) ? $this->params['user_id'] : '';
		$mobile_phone = isset($this->params['mobilephone']) ? $this->params['mobilephone'] : '';
		$mobile_phone = trim($mobile_phone);
		
		//第三方登陆绑定手机号
		$user_name	  = isset($this->params['username']) ? $this->params['username'] : '';
		$user_name	  = $this->userTextEncode($user_name);
		
		if(isset($this->params['username'])){
			//验证昵称唯一性
			$user_name = $this->userTextEncode($this->params['username']);
			$user_is_exist = User::find()->where(['nickname'=>$user_name])->one();
			
			if($user_is_exist && $user_is_exist['user_id'] != $user_id){
				$this->_errorData('0061', '此昵称已存在');
				exit();
			}
		}else{
			$user_name = '匿名用户'.substr($mobile_phone, -4);
		}
		
		//国际区号
		$countries_regions = isset($this->params['countries_regions']) ? trim($this->params['countries_regions']) : '';
		
		$code         = isset($this->params['verifycode']) ? $this->params['verifycode'] : '';
		$code         = trim($code);
		$password     = isset($this->params['password']) ? $this->params['password'] : '';
		$password     = md5(md5($password));
		$fromType     = $this->VerifyCodeChangeMobileType;

		$user = User1::findOne($user_id);
		if(empty($user)){
			$this->_errorData("0054", "用户不存在");
		}
		if (empty($mobile_phone)) {
			$this->_errorData("0055", "请传入手机号");
		}
		if (empty($code)) {
			$this->_errorData("0058", "验证码不能为空");
		}
		if (!$this->_checkMobile($countries_regions, $mobile_phone)) {
			$this->_errorData("0056", "手机号码格式错误");
		}
		
		$is_bind = User1::find()->where(['mobile_phone'=>$mobile_phone, 'countries_regions'=>$countries_regions])->one();
//		if ($is_bind) {
//			$this->_errorData("0057", "此手机号码已被他人使用，请更换其他手机号");
			
			/* $is_bind->mobile_phone = NULL;
			$clear_user_phone = $is_bind->save();
			if(!$clear_user_phone){
				$this->_errorData("0061", "请求错误，请稍后再试");
			} */
//		}

		//接收发送的验证码
		$vcode = UserVerifyCode::getUserVerifyCode($countries_regions, $mobile_phone, $fromType);
		$verifyCode = $vcode['verify_code'];

		if($verifyCode == $code){
                        if ($is_bind){
                                if(!$is_bind->open_id){
                                    $open_id    = $user->open_id;
                                    $user_type  = $user->user_type;
                                    $up_ret     = User::updateAll(["open_id" => $open_id, "user_type" => $user_type], 'mobile_phone =' . $mobile_phone);
                                    $del_ret    = User::updateAll(["open_id" => ''], 'user_id =' . $user_id);
                                    ($up_ret && $del_ret) ? $this->_successData(null, "绑定成功") : $this->_errorData("0059", "绑定失败");
                                }
                                else{
                                    $this->_errorData("0059", "该手机号已经注册,请使用手机号或者第三方账号登录!");
                                }
                        }
			$user->countries_regions = $countries_regions;
			$user->mobile_phone = $mobile_phone;
			$user->password     = $password;
			if($user_name){
				$user->nickname = $user_name;
			}
			$up = $user->save();
			if($up){
				$this->_successData(null, "绑定成功");
			}else{
				$this->_errorData("0059", "绑定失败");
			}
		}else{
			$this->_errorData("0060", "验证码错误");
		}
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
	

    /*
     * 生成 ID
     * */
    private function createId()
    {
        return date('Y') . time() . rand(0000, 9999);
    }

	/**
	 * 修改用户信息
	 */
	function actionUpdateUserInfo()
	{
//		if ($_POST) {
			//接受用户修改值
			$nickname = isset($this->params['nickname']) ? $this->params['nickname'] : '';
			$nickname = trim($nickname);
			$sex      = isset($this->params['sex']) ? $this->params['sex'] : '';
			$birthday = isset($this->params['birthday']) ? $this->params['birthday'] : '';
			$province_id = isset($this->params['province_id']) ? $this->params['province_id'] : '';
			$area_id     = isset($this->params['area_id']) ? $this->params['area_id'] : '';
			$location_status = isset($this->params['location_status']) ? $this->params['location_status'] : '';

			//用户不存在返回的信息
			$getUserinfo = $this->_getUserModel();

			if(!empty($nickname)){
				//验证昵称唯一性
				$user_name = $this->userTextEncode($nickname);
				$user_is_exist = User::find()->where(['nickname'=>$user_name])->one();

				if($user_is_exist && $user_is_exist['user_id'] != $getUserinfo->user_id){
					$this->_errorData('0061', '此昵称已存在');
					exit();
				}
			}else{
				$this->_errorData("2001", "昵称不能为空");
			}

			if (empty($getUserinfo)) {
				$this->_errorData("0002", "用户不存在");
			} else {
				$getUserinfo->nickname = $this->userTextEncode($nickname);
				$getUserinfo->sex      = $sex;
				$getUserinfo->birthday = $birthday;
				$getUserinfo->province_id = $province_id;
				$getUserinfo->area_id     = $area_id;
				$getUserinfo->location_status = $location_status;
				$up = $getUserinfo->save();
				if ($up != 0) {
					$this->_successData($this->_getUserModel(true),'修改成功');
				} else {
					$this->_errorData("3000", "未进行修改");
				}
			}
//		}
	}

	//判断token  返回用户ID  （投票 使用）
	public function actionGetUserId(){
		$token = isset($this->params['token']) ? $this->params['token'] : '';
		$userData = $this->_checkToken($token);
		if($userData == false){
			$this->_errorData('0055', '用户未登录');
			exit;
		}else {
			$data = array();
			$data['user_id'] = $userData['userId']."";
			$this->_successData($data);
		}
	}

	/**
	 * 获取我的汇闻币
	 */
	public function actionGetMyGold()
	{
		$user = $this->_getUserModel();
		$returnArr['amount'] = $user['amount'];
		$this->_successData($returnArr, "获取数据成功");
	}

	/**
	 * 获取省份列表
	 */
	public function actionGetProvinceList()
	{
		$provice = AreaCity::getProvinceList();
		$this->_successData($provice);
	}

	/**
	 * 获取城市列表
	 */
	public function actionGetCityList()
	{
		$provinceCode = isset($this->params['area_code']) ? $this->params['area_code'] : '';
		$provinceCode = trim($provinceCode);
		$provices = AreaCity::getCityList($provinceCode);
		$this->_successData($provices);
	}

	//用户反馈信息添加
	public function actionFedUserInfo()
	{
		if ($_POST) {
			//接受用户信息
			$user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';  //用户编号
			$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';          //反馈意见类型
			$detail = isset($_REQUEST['detail']) ? $_REQUEST['detail'] : '';    //意见详情

			//用户不存在返回的信息
			$user_id_no = array(
				'Success' => false,
				'ResultCode' => '0000',
				'Message' => '用户不存在'
			);
			$getUserinfo = User1::findOne($user_id);
			if (empty($getUserinfo)) {
				exit(json_encode($user_id_no, JSON_UNESCAPED_UNICODE));
			} else {
				//需添加的数据
				$feedback = new FeedBack();
				$feedback->user_code = $user_id;//用户编号
				$feedback->type      = $type;//反馈意见类型
				$feedback->detail    = $detail;//意见详情
				$feedback->created_date = date('Y-m-d H:i:s', time());//创建时间
				$feedback->user_name = $getUserinfo->username;        //用户名称
				$feedback->creator   = $getUserinfo->username;        //创建者
				$feedback->phone     = $getUserinfo->mobile_phone;        //手机号
				$addok = $feedback->save();
				//添加失败返回的内容
				$error = array(
					'Success' => false,
					'ResultCode' => '0002',
					'Message' => '反馈添加失败'
				);
				if ($addok) {
					$this->_successData(null, "反馈添加成功");
				} else {
					exit(json_encode($error, JSON_UNESCAPED_UNICODE));
				}
			}

		}
	}

	/**
	 * 退出登录
	 * @bo.zhao
	 * @param $user_id
	 */
	public function actionOutLogin()
	{
		$user_id = isset($this->params['user_id']) ? $this->params['user_id'] : '';
		$user_id = trim($user_id);
		$user = User1::getUserById($user_id);
		if(!$user){
			$this->_errorData("0011", "用户不存在");
		}
		//清空 registration_id
		if(!empty($user->registration_id)) {
			User::updateAll(['registration_id' => ''], "user_id = $user_id");
		}
		$this->_successData(1,"退出成功");
	}

	/**
	 * 获取用户位置（按地区推送用）
	 */
	public function actionUserArea(){
		$user_id = isset($this->params['user_id']) ? $this->params['user_id'] : '';
		$area    = isset($this->params['area']) ? trim($this->params['area']) : '';
		if(!$user_id || !$area){
			$this->_errorData(0000, '参数错误');
		}
		$user = User1::getUserById($user_id);
		if(!$user){
			$this->_errorData("0011", "用户不存在");
		}
		$user_area = UserAreaPush::find()->where(['user_id' => $user_id])->asArray()->one();
		if($user_area){
			UserAreaPush::updateAll(['area'=>$area,'update_time'=>date('Y-m-d H:i:s', time())], ['user_id'=>$user_id]);
			$this->_successData(1,'设置成功');
		}else{
			$model = new UserAreaPush();
			$model->user_id = $user_id;
			$model->area    = $area;
			$model->create_time = date('Y-m-d H:i:s', time());
			if($model->save()){
				$this->_successData(1,'设置成功');
			}else{
				$this->_successData(-1,'设置失败');
			}
		}
	}

	/**
	 * 获取最新邀请注册信息
	 */
	public function actionNewInvite(){
		$info = InviteReg::getNewInvite();
		$this->_successData($info);
	}
	
}