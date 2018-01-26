<?php
namespace frontend\controllers;
use common\models\UserAmount;
use Yii;
use common\models\Gold;
use common\models\Order;

class RechargeController extends BaseApiController{

    /**
     * 订单信息
     */
    public function actionOrder(){
        $user_id  = isset($this->params['user_id']) ? $this->params['user_id'] : '';
        $goods_id = isset($this->params['goods_id']) ? $this->params['goods_id'] : '';
        $token    = isset($this->params['token']) ? $this->params['token'] : '';

        if (!$user_id || !$goods_id || !$token) {
            $this->_errorData(0001, '参数错误');
        }
        if($token){
            $userData = $this->_checkToken($token);
            $user = $this->_getUserModel();
            if($userData == false){
                $this->_errorData(0055, '用户未登录');
                exit;
            }
            $user_id = $userData['userId'];
            $user = $this->_getUserModel();
        }
        $gold_info = Gold::find()->where(['id'=>$goods_id, 'status'=>1])->asArray()->one();
        if (!$gold_info) {
            $this->_errorData(0001, '商品不存在');
        }
        $order_ping = new Order();
        $order_number = date('YmdHis', time()) . mt_rand(1000, 9999);
        $data['user_id'] = $user_id;
        $data['goods_id'] = $goods_id;
        $data['order_ch_id'] = '';
        $data['order_number'] = $order_number;
        $data['total_price'] = $gold_info['money'];
        $data['pay_type'] = 'ios';
        $data['pay_status'] = '待支付';
        $data['create_time'] = time();
        $data['edit_time'] = time();
        $order_ping->setAttributes($data);
//        $order_ping->user_id = $user_id;
//        $order_ping->goods_id = $goods_id;
//        $order_ping->order_ch_id = '';
//        $order_ping->order_number = $order_number;
//        $order_ping->total_price  = $gold_info['money'];
//        $order_ping->pay_type     = 'ios';
//        $order_ping->pay_status   = '待支付';
//        $order_ping->create_time  = time();
//        $order_ping->edit_time    = time();
        $order_ping->save();
        $this->_successData($data);
    }


    function  actionIndex(){
        //用户发来的参数
        $receipt_data = isset($this->params['receipt-data']) ? $this->params['receipt-data'] : '';
//        $order_number = I("order_number");
        Yii::trace(json_encode($receipt_data));
        if(!$receipt_data){
            $this->_errorData(0001, '参数错误');
        }
        $error = '';
        $data = '';
        //验证参数
        if (strlen($receipt_data)<20){
            $error = '非法参数';
        }else{
            //请求验证
            $html = $this->acurl($receipt_data, 1);
            $data = json_decode($html,1);
            Yii::trace(json_encode($data));
            //如果是沙盒数据 则验证沙盒模式
            if($data['status']=='21007'){
                //请求验证
                $html = $this->acurl($receipt_data, $sandbox=1);
                $data = json_decode($html,1);
                $data['sandbox'] = '1';
            }

            if (isset($_GET['debug'])) {
                exit(json_encode($data));
            }
            if($data['status']==0){
                $data = '购买成功';
            }else{
                $error = '购买失败';
            }
        }

        if($error){
            $this->_errorData(0001, $error);
        }
        $this->_successData($data);
    }


    /**
     * 21000 App Store不能读取你提供的JSON对象
     * 21002 receipt-data域的数据有问题
     * 21003 receipt无法通过验证
     * 21004 提供的shared secret不匹配你账号中的shared secret
     * 21005 receipt服务器当前不可用
     * 21006 receipt合法，但是订阅已过期。服务器接收到这个状态码时，receipt数据仍然会解码并一起发送
     * 21007 receipt是Sandbox receipt，但却发送至生产系统的验证服务
     * 21008 receipt是生产receipt，但却发送至Sandbox环境的验证服务
     */
    function acurl($receipt_data, $sandbox=0){

        //小票信息
        $POSTFIELDS = array("receipt-data" => $receipt_data);
        $POSTFIELDS = json_encode($POSTFIELDS);

        //正式购买地址 沙盒购买地址
        $url_buy     = "https://buy.itunes.apple.com/verifyReceipt";
        $url_sandbox = "https://sandbox.itunes.apple.com/verifyReceipt";
        $url = $sandbox ? $url_sandbox : $url_buy;

        //简单的curl
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTFIELDS);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($ch);
        Yii::trace(json_encode($result));
        curl_close($ch);
        return $result;
    }

    /**
     * ios支付通知
     */
    public function actionNotice(){
        $order_number = isset($this->params['order_number']) ? $this->params['order_number'] : '';
        $status       = isset($this->params['status']) ? $this->params['status'] : '';
        if($status == '1'){
            $result = '已支付';
        }else{
            $result = '支付失败';
        }
        $order_info = Order::find()->where(['order_number'=>$order_number, 'pay_status'=>'待支付'])->one();
        if(!$order_info){
            $this->_errorData(0001, '订单不存在');
        }
        $order_info->order_number = $order_number;
        $order_info->pay_status   = $result;
        $order_info->pay_time     = time();
        $order_info->save();
        if($status == '1'){
            $gold_info = Gold::find()->where(['id'=>$order_info['goods_id'], 'status'=>'1'])->asArray()->one();
            $param['user_id'] = $order_info['user_id'];
            $param['operate_cnt'] = $gold_info['number'];
            $param['operate'] = 1;
            $param['operate_name'] = '充值';
            $return = UserAmount::addUserAmount($param);
            $this->_successData($return);
        }else{
            $this->_errorData(0001, '订单支付失败');
        }
    }
}