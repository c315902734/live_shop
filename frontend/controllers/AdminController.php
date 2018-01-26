<?php

namespace frontend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\log\FileTarget;
use common\service\redis;
use common\models\AdminUser;
use common\models\User1;
use common\models\UserVerifyCode;


class AdminController extends Controller
{

     /**
     * 绑定手机号相关接口
     */

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

        protected function _successData($returnData, $msg = "查询成功")
    {
        $data = array('Success' => true,
            'ResultCode' => '0000',
            'ReturnData' => $returnData,
            'Message' => $msg
        );
        header('Content-Type:application/json; charset=utf-8');
//        if(isset($_REQUEST['is_show']) && $_REQUEST['is_show'] == 1){
//            header('Access-Control-Allow-Origin: *');
//            header('Content-Type:application/json; charset=utf-8');
//            $jsonp_header_start = '';
//            $jsonp_header_end = '';
//            if(isset($_REQUEST['callback'])){
//                if(!empty($_REQUEST['callback'])){
//                    $jsonp_header_start = $_REQUEST['callback'].'(';
//                    $jsonp_header_end = ')';
//                }
//            }
//            exit($jsonp_header_start.json_encode($data).$jsonp_header_end);
//
//        }
        exit(json_encode($data));
        //header('Access-Control-Allow-Origin: *');
//         header('Content-Type:application/json; charset=utf-8');
//         $jsonp_header_start = '';
//         $jsonp_header_end = '';
//         if(isset($_REQUEST['callback'])){
//          if(!empty($_REQUEST['callback'])){
//              $jsonp_header_start = $_REQUEST['callback'].'(';
//              $jsonp_header_end = ')';
//          }
//         }
//         exit($jsonp_header_start.json_encode($data).$jsonp_header_end);
    }

    protected function _errorData($code, $message)
    {
        $ReturnData = NULL;
        $data = array('Success' => false,
            'ResultCode' => $code . "",
            'ReturnData' => $ReturnData,
            'Message' => $message . ''
        );
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
//         header('Access-Control-Allow-Origin: *');
//         header('Content-Type:application/json; charset=utf-8');
//         $jsonp_header_start = '';
//         $jsonp_header_end = '';
//         if(isset($_REQUEST['callback'])){
//          if(!empty($_REQUEST['callback'])){
//              $jsonp_header_start = $_REQUEST['callback'].'(';
//              $jsonp_header_end = ')';
//          }
//         }
//         exit($jsonp_header_start.json_encode($data).$jsonp_header_end);
    }
   
    
  

    

}