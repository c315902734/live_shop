<?php
namespace frontend\controllers;

use common\models\Commodity;
use common\models\Gold;
use common\models\Order;
use common\models\User1;
use Pingpp\WxpubOAuth;
use Yii;
use Pingpp\Pingpp;
use Pingpp\Charge;
use Pingpp\Error\Base;
use common\service\PingPlus;
use common\models\User;

class PayController extends BaseApiController
{

    public $enableCsrfValidation = false;
    /**
     * 购买金币
     */
    function actionIndex()
    {
        $user_id  = isset($this->params['user_id']) ? $this->params['user_id'] : '';
        $goods_id = isset($this->params['goods_id']) ? $this->params['goods_id'] : '';
        $channel  = isset($this->params['channel']) ? $this->params['channel'] : '';
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
        //判断支付方式是否合法
        if (!in_array($channel, PingPlus::$channel_type)) {
            $this->_errorData(0001, '错误的支付方式');
        }

        $user_info = User1::findOne($user_id);
        if (!$user_info) {
            $this->_errorData(0001, '用户不存在');
        }
        if ($user_info->status != '1') {
            $this->_errorData(0001, '用户已禁用');
        }

        $gold_info = Gold::find()->where(['id'=>$goods_id, 'status'=>1])->asArray()->one();
        if (!$gold_info) {
            $this->_errorData(0001, '商品不存在');
        }
        $extra = array();
        switch ($channel) {
            case 'alipay':
                $alipay_open_id  = isset($this->params['alipay_open_id']) ? $this->params['alipay_open_id'] : '';
                $rn_check = isset($this->params['rn_check']) ? $this->params['rn_check'] : 'F';//T 代表发起实名校验；F 代表不发起实名校验；
//
//                $extra = array(
//                    'alipay_open_id' => $alipay_open_id,
//                    'rn_check' => $rn_check,
//                );

                break;
            case 'wx':
                $extra = array(
                    'goods_tag' => $goods_id,
                );
                break;

            case 'wx_pub':
                $open_id = isset($this->params['open_id'])?$this->params['open_id']:'';

//                        file_put_contents('a.tXT','+++++'.$open_id,FILE_APPEND);

                if(!$open_id){
                    $open_id = $user_info->open_id;
                    if(!$open_id){
                        $this->_errorData(0011, 'open_id不能为空');
                    }
                }
                $extra = array(
                    'open_id' => $open_id,
                );
                break;

            case 'wx_pub_qr':

                $extra = array(
                    'product_id' => $goods_id,
                );
                break;
        }

        $order_number = date('YmdHis', time()) . mt_rand(1000, 9999);

        Pingpp::setApiKey(Yii::$app->params['PING_API_KEY']);
        try {
            $ch = Charge::create(
                array(
                    "subject" => "[" . $this->userTextDecode($user_info['nickname']) . "] 购买 " . $gold_info['title'],
                    "body" => "支付订单",
                    "amount" => $gold_info['money']*100,
                    "order_no" => $order_number,
                    "currency" => "cny",
                    "extra" => $extra,//特定渠道发起交易时需要的额外参数以及部分渠道支付成功返回的额外参数。
                    "channel" => $channel,
                    "client_ip" => $_SERVER["REMOTE_ADDR"],
                    "app" => array("id" => Yii::$app->params['PING_ID']),
                    //                             "metadata"  => ['type'=>$type],
                    "time_expire" => time() + 2 * 3600
                )
            );

            if ($ch['id']) {
                $order_ping = new Order();
                $order_ping->user_id = $user_id;
                $order_ping->goods_id = $goods_id;
                $order_ping->order_number = $order_number;
                $order_ping->order_ch_id  = $ch['id'];
                $order_ping->total_price  = $gold_info['money'];
                $order_ping->pay_status   = '待支付';
                $order_ping->pay_type     = $channel;
                $order_ping->create_time  = time();
                $order_ping->edit_time    = time();
                $order_ping->save();
            }
            $this->_successData($ch);
        } catch (Base $e) {
            $error_body = json_decode($e->getHttpBody(), true);
            $message = $error_body['error']['message'];
            $type    = $error_body['error']['type'];
            $ping_plus_err_array = array_flip(PingPlus::$error_desc);

            $code = isset($ping_plus_err_array[$type]) ? $ping_plus_err_array[$type] : 16007;

            if ($code != 16007) {
                $this->_errorData(0001, $message);
            }

            $this->_errorData($code, $message);
        }
    }
}