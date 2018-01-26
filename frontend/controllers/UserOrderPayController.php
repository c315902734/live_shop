<?php
/**
 * 不需要OAUTH认证
 * Created by PhpStorm.
 * User: PC
 * Date: 2017/11/14
 * Time: 17:05
 */

namespace frontend\controllers;

use common\models\ShopOrder;
use Pingpp\WxpubOAuth;
use yii;

class UserOrderPayController extends PublicBaseController
{
    private $_wechat_pub_pay_code;

    public function init()
    {
        parent::init();

        $this->_wechat_pub_pay_code = 3;
    }

    /**
     *
     */
    public function actionWechatPubPay()
    {
        /* 订单相关参数 */
        $device    = intval(yii::$app->request->get('device', 0));
        $order_id  = intval(yii::$app->request->get('order_id', 0));
        $pay_type  = intval(yii::$app->request->get('pay_type', 0));
        $client_ip = trim(yii::$app->request->get('ip', ''));
        $user_token = trim(yii::$app->request->get('token', ''));
        if (!$user_token || !$order_id || !$pay_type || $pay_type != $this->_wechat_pub_pay_code) $this->_errorData('1001','参数错误');

        /* wechat_pub支付参数 */
        $open_id = '';
        $wx_code = trim(yii::$app->request->get('code', ''));
        $wx_pub_appid     = yii::$app->params['wx_pub_appId'];
        $wx_pub_appSecret = yii::$app->params['wx_pub_appSecret'];
        if (!$wx_pub_appid || !$wx_pub_appSecret) $this->_errorData('', '后台未配置微信公众号');

        if ($wx_code) {
            $open_id = WxpubOAuth::getOpenid($wx_pub_appid, $wx_pub_appSecret, $wx_code);
        } else {
            $this->_errorData('1000', '获取参数错误，请重试');
        }

        $user_info = $this->getUserInfoByToken($user_token);

        $ret = ShopOrder::orderPay($order_id, $user_token, $user_info, $open_id, $client_ip, $pay_type, '');
        if (isset($ret['pay_type'])) {
            $this->_successData($ret['data']);
        }
        $this->_errorData('1010', $ret);
    }
}