<?php
namespace backend\controllers;

use common\models\AdminUser;
use common\models\AdminVersion;
use common\models\NewsColumnType;
use common\models\SpecialColumnType;
use common\service\PublicFunction;
use common\models\User1;
use common\models\UserVerifyCode;
use Yii;


/**
 * Admin controller
 */
class AdminController extends BaseApiController
{

    /**
     * 管理员登陆
     * username 用户名或邮箱
     * password 密码
     * token  token
     * @return string
     */
    public function actionLogin()
    {
        $username = isset($this->params['username']) ? $this->params['username'] : '';
        $pwd      = isset($this->params['password']) ? $this->params['password']:'';
        $token    = isset($this->params['token']) ? $this->params['token']:'';
        //极光推送设备ID
        $registration_id = isset($this->params['registration_id']) ? $this->params['registration_id']:'';
        $version_info = isset($this->params['version_info']) ? $this->params['version_info']:'';
        $phone_type = isset($this->params['phone_type']) ? $this->params['phone_type']:0;

        $username = trim($username);
        $pwd      = trim($pwd);
        if(empty($username) || empty($pwd) || empty($token)){
            $this->_errorData("0101", "参数错误");
        }

        if($token){
            if (empty($username)) {
                $this->_errorData("0010", "请传入用户名或邮箱");
            }
            if (empty($pwd)) {
                $this->_errorData("0020", "请传入密码");
            }
            //校验密码
            $admin = AdminUser::getUserByLogin($username);

            /* 提示 未注册 */
            if(!$admin){
                $this->_errorData("0004", "该用户不存在");
            }

            //用户状态
            $status=$admin->status;
            if($status=='0'){
                $this->_errorData("0003", "用户已被禁用");
            }else{
                if ($admin && AdminUser::sp_password($pwd) == $admin->admin_pwd) {
                    $ReturnData['admin_id'] = $admin->admin_id . "";
                    $ReturnData['token'] = $token . "";
                    $ReturnData['user_name'] = empty($admin->username) ? "" : $this->userTextDecode($admin->username . "");
                    $ReturnData['real_name'] = empty($admin->real_name) ? "" : $admin->real_name . "";
                    $ReturnData['user_url']  = empty($admin->user_url) ? "" : $admin->user_url . "";
                    $ReturnData['is_update']  = 0;
                    $ReturnData['mobile']  = $admin->mobile;
                    $ReturnData['user_id']  = $admin->user_id;
                    $ReturnData['mirrtalkid']  = $admin->mirrtalkid;

                    //查看当前最新版本
                    $version = new AdminVersion();
                    $get_info = $version->find()->where(['type'=>$phone_type])->asArray()->one();

                    $user_arr = explode('.', $version_info);
                    $new_arr = explode('.', $get_info['version']);
                    $new_str = 0; // 当前用户为 最新版本
                    if($new_arr[0] > $user_arr[0]){
                        $new_str = 1; //当前用户为非 最新版本
                    }else if($new_arr[0] == $user_arr[0]){
                        if($new_arr[1] > $user_arr[1]){
                            $new_str = 1;
                        }else if($new_arr[1] == $user_arr[1]) {
                            if(!isset($new_arr[2])){
                                $new_arr[2] = 0;
                            }
                            if(!isset($user_arr[2])){
                                $user_arr[2] = 0;
                            }
                            if ($new_arr[2] > $user_arr[2]) {
                                $new_str = 1;
                            }
                        }
                    }
                    if($new_str == 1){
                        $ReturnData['is_update'] = 1;
                    }

//                    if($version_info != $get_info['version'] && $admin->is_update == 1){
//                        $ReturnData['is_update'] = 1;
//                    }


                    $admin->last_login_time = date("Y-m-d H:i:s");
                    $admin->registration_id = $registration_id;

                    if($admin->save()){
                        //清空 与此推送ID相同的 其它账号推送ID信息
                        if($registration_id){
                            AdminUser::updateAll(["registration_id"=>''], "registration_id = '".$registration_id."' and admin_id != ".$admin->admin_id);
                        }
                        $redis = Yii::$app->cache;
                        $redis_info = $redis->get(array($admin->admin_id));
                        $redis->delete(array($redis_info['token']));
                        //设置用户其他token失效、设置有效期30天
                        $expires = AdminUser::setAccessToken($redis_info['token'], $admin->admin_id,$token);
                        PublicFunction::SetRedis($admin->admin_id,array("token"=>$token,"AdminId" => $admin->admin_id));

                        if($expires) $ReturnData['expires'] = $expires;
                        // token有效期30天
                        PublicFunction::SetRedis($token,array("AdminId" => $admin->admin_id));
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

    /*
     * 强制更新
     *
     * */
    public function actionIsupdate(){
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';
        $admin_id = trim($admin_id);
        $admin = AdminUser::findIdentity($admin_id);
        if(!$admin){
            $this->_errorData("0011", "账号不存在");
        }
        //更改用户 是否更新状态
        AdminUser::updateAll(['is_update' => 0], "admin_id = $admin_id");

        $this->_successData(1,"更新成功");
    }

    /*
     * 退出登录
     *
     * */
    public function actionOutLogin(){
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';
        $admin_id = trim($admin_id);
        $admin = AdminUser::findIdentity($admin_id);
        if(!$admin){
            $this->_errorData("0011", "账号不存在");
        }
        //清空 registration_id
        if(!empty($admin->registration_id)) {
            AdminUser::updateAll(['registration_id' => ''], "admin_id = $admin_id");
        }
        $this->_successData(1,"退出成功");
    }


    public function actionBindPhone()
    {

        $admin_id          = isset($_REQUEST['admin_id']) ? $_REQUEST['admin_id'] : '';
        $mobile            = isset($_REQUEST['mobile']) ? $_REQUEST['mobile'] : '';
        $code              = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
        $countries_regions = isset($_REQUEST['countries_regions']) ? trim($_REQUEST['countries_regions']) : '';
        (!$admin_id) &&$this->_errorData("0059", "id未指定");
        (!$mobile) &&$this->_errorData("0059", "手机号不能为空");
        (!$code) &&$this->_errorData("0059", "验证码不能为空");
        $is_bind           = AdminUser::find()->where(['admin_id'=>$admin_id])->one();
        $user              = User1::find()->where(['mobile_phone'=>$mobile])->select("user_id")->one();
        $vcode             = UserVerifyCode::getUserVerifyCode($countries_regions, $mobile, 5);
        $verifyCode        = $vcode['verify_code'];
        if($verifyCode == $code){
                if ($is_bind){
                        if(!$is_bind->mobile){
                                if($user){
                                        $user_id =$user->user_id;
                                        $ret     = AdminUser::updateAll(["mobile" => $mobile,"user_id" => $user_id], 'admin_id =' . $admin_id);

                                }
                                else{

                                        $ret     = AdminUser::updateAll(["mobile" => $mobile], 'admin_id =' . $admin_id);

                                }
                                ($ret) ? $this->_successData("0001", "绑定成功") : $this->_errorData("0059", "绑定失败");

                        }
                        else{

                            $this->_errorData("0059", "该帐号已经绑定手机号!");
                           
                        }
                }

                $this->_errorData("0059", "id错误");
        }
        $this->_errorData("0059", "验证码错误!");
       
        
    }

    public function actionBindMirrtalkid()
    {

        $admin_id          = isset($_REQUEST['admin_id']) ? $_REQUEST['admin_id'] : '';
        $mirrtalkid        = isset($_REQUEST['mirrtalkid']) ? $_REQUEST['mirrtalkid'] : '';
        (!$admin_id) &&$this->_errorData("0059", "id未指定");
        (!$mirrtalkid) &&$this->_errorData("0059", "设备id不能为空");
        $ret     = AdminUser::updateAll(["mirrtalkid" => $mirrtalkid], 'admin_id =' . $admin_id);
        ($ret) ? $this->_successData("0001", "绑定成功") : $this->_errorData("0059", "绑定失败");

    }


    public function actionAcquisitionInfo()
    {

        $admin_id          = isset($_REQUEST['admin_id']) ? $_REQUEST['admin_id'] : '';
        (!$admin_id) &&$this->_errorData("0059", "id未指定");
        $ret=AdminUser::find()->select(['admin_id','username','mobile','user_id','mirrtalkid'])->where(['admin_id'=>$admin_id])->orderBy('admin_id DESC')->asArray()->one();
        ($ret) ? $this->_successData($ret) : $this->_errorData("0059", "查询失败");


    }



}