<?php
namespace frontend\controllers;
/**
 *
 * 我的资料相关接口
 */
use common\models\AreaCity;
use common\models\User1;
use common\models\UserTask;
use common\models\UserVerifyCode;
use Yii;
class InfoController extends BaseApiController{

    /**
     * 获取我的用户信息
     */
    public function actionGetMyInfo()
    {
        $user = $this->_getUserModel(true);
        $returnArr['username'] = $user['username'] . "";
        $returnArr['nickname'] = $user['nickname'] . "";
        $returnArr['mobile_phone'] = $user['mobile_phone'] . "";
        $returnArr['avatar'] = User1::getAvatarUrl($user['avatar']). "";
        $returnArr['sex'] = intval($user['sex']);
        $returnArr['birthday'] = $user['birthday'] . "";
        $returnArr['province_id'] = intval($user['province_id']);
        $returnArr['area_id'] = intval($user['area_id']);
        $returnArr['province'] = AreaCity::getProvinceName($returnArr['province_id']);
        $returnArr['area'] = AreaCity::getCityName($returnArr['area_id']);
        $returnArr['amount'] = $user['amount'];
        $returnArr['is_sign'] = UserTask::isSign($user['user_id']);
        $this->_successData($returnArr, "获取数据成功");
    }

    //绑定手机号
    public function actionBindPhone()
    {
        $user_id = isset($this->params['user_id']) ? $this->params['user_id'] : '';
        $mobile_phone = isset($this->params['mobilephone']) ? $this->params['mobilephone'] : '';
        $mobile_phone = trim($mobile_phone);
        $code         = isset($this->params['verifycode']) ? $this->params['verifycode'] : '';
        $code         = trim($code);
        $password     = isset($this->params['password']) ? $this->params['password'] : '';
        $password     = md5(md5($password));
        $fromType     = 3;

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
        if (!$this->_checkMobile($mobile_phone)) {
            $this->_errorData("0056", "手机号码格式错误");
        }
        $is_bind = User1::find()->where(['mobile_phone'=>$mobile_phone])->one();
        if ($is_bind) {
            $this->_errorData("0057", "此手机号码已被他人使用，请更换其他手机号");
        }

        //接收发送的验证码
        $vcode = UserVerifyCode::getUserVerifyCode($mobile_phone, $fromType);
        $verifyCode = $vcode['verify_code'];

        if($verifyCode == $code){
            $user->mobile_phone = $mobile_phone;
            $user->password     = $password;
            $up = $user->save();
            if($up){
                $this->_successData(null, "绑定成功");
            }else{
                $this->_errorData("0059", "验证码错误");
            }
        }else{
            $this->_errorData("0060", "绑定失败");
        }
    }

    /**
     * 忘记密码
     */
    public function actionForgetPwd()
    {
        $mobile = isset($this->params['mobile']) ? $this->params['mobile'] : '';
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
        if (!$this->_checkMobile($mobile)) {
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
        $this->checkVerifyCode($mobile, $verifyCode, 4);
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
     * 处理验证码
     */
    private function checkVerifyCode($mobile, $verifyCode, $fromType)
    {
        //处理验证码
        $code = UserVerifyCode::getUserVerifyCode($mobile, $fromType);
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
}
