<?php
/**

 */
namespace frontend\controllers;

use common\models\Commodity;
use common\models\ExceptionOrder;
use common\models\Gold;
use common\models\NewsColumn;
use common\models\RechargeRecord;
use common\models\ShopOrder;
use common\models\UserAmount;
use common\models\UserOwned;
use common\models\UserOwnedLog;
use common\service\Record;
use \Yii;
use yii\db\Expression;
use yii\web\Controller;

use common\models\Order;
//use common\service\BdPush;


class PingXxController extends Controller
{
    public $enableCsrfValidation = false;

    public function init()
    {
        parent::init();
    }

    function actionIndex()
    {

        $event = json_decode(file_get_contents("php://input"));
        $event_data = json_decode(file_get_contents("php://input"), true);


        // 对异步通知做处理
        if (!isset($event->type)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            exit("fail");
        }

        if (!isset($event_data['data']) || !isset($event_data['data']['object'])) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            exit("fail");
        }

        $input_data = $event_data['data']['object'];

        switch ($event->type) {
            case "charge.succeeded":
                if ($input_data['paid'] == true) {
                    $order_ping = Order::find()->where(['order_ch_id'=>$input_data['id'], 'pay_status' => '待支付'])->asArray()->one();
                    if ($order_ping) {
                        $gold_info = Gold::find()->where(['id'=>$order_ping['goods_id'], 'status'=>'1'])->asArray()->one();
                        if(!$gold_info){
                            echo "商品不存在";
                            die;
                        }
                        $order = new Order();
                        $order->pay_status = '已支付';
                        $order->pay_time   = $input_data['time_paid'];
                        $order->edit_time  = time();
                        if ($order->save()) {
                            $param['user_id'] = $order_ping['user_id'];
                            $param['operate_cnt'] = $gold_info['number'];
                            $param['operate'] = 1;
                            $param['operate_name'] = '充值';
                            UserAmount::addUserAmount($param);
                        } else {
                            echo "修改订单状态失败";
                        }
                    } else {
                        echo "订单不存在";
                        die;
                    }
                } else {
                    echo "返回值有误";
                }
                break;
            default:
                header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
                break;
        }
    }

    /**
     * Pingxx 支付回调
     * 更新订单状态
     * @return bool
     */
    public function actionShopPayCallback(){
        $event = json_decode(file_get_contents("php://input"));
        $event_data = json_decode(file_get_contents("php://input"), true);

        // 对异步通知做处理
        if (!isset($event->type)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            exit("fail");
        }

        if (!isset($event_data['data']) || !isset($event_data['data']['object'])) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            exit("fail");
        }

        $return_data = $event_data['data']['object'];
        Record::record_data($return_data,'$return_data');
        try {
            switch ($event->type) {
                case "charge.succeeded":
                    if ($return_data['paid'] == true) {
                        $order_info = ShopOrder::find()->where(['order_number'=>$return_data['order_no']])->one();
                        if (!$order_info) exit('未找到此订单');
                        if ($order_info->status >= 1) exit('此订单已支付');

                        $pay_type = 0;
                        switch (strtolower($return_data['channel'])) {
                            case 'alipay_wap':
                                $pay_type = ShopOrder::ALIPAY_WAP_PAY_CODE;  // 1
                                break;
                            case 'wx_wap':
                                $pay_type = ShopOrder::WECHAT_H5_PAY_CODE;  // 2
                                break;
                            case 'wx_pub';
                                $pay_type = ShopOrder::WECHAT_PUB_PAY_CODE;  // 3
                                break;
                            case 'alipay':
                                $pay_type = ShopOrder::ALIPAY_APP_PAY_CODE;  // 4
                                break;

                            case 'wx':
                                $pay_type = ShopOrder::WECHAT_APP_PAY_CODE;  // 5

                                break;
                            default:
                                $pay_type = 0;
                        }
                        if (!$pay_type) exit('支付状态错误');
 
                        //更新订单状态 status 1
                        $order_paid_code   = ShopOrder::ORDER_PAID_CODE;
                        $order_unpaid_code = ShopOrder::ORDER_UNPAID_CODE;
                        $updata_order_ret = yii::$app->db->createCommand()->update('vrshop.shop_order',[
                            'pingxx_sn' => $return_data['id'],
                            'status'    => $order_paid_code,
                            'pay_type'  => $pay_type,
                        ], "order_number = '{$return_data['order_no']}' AND status = {$order_unpaid_code}")->execute();
                        if (!$updata_order_ret) {
                            $updata_order_sql = yii::$app->db->createCommand()->update('vrshop.shop_order',[
                                'status'   => '1',
                                'pay_type' => $pay_type,
                            ], "order_number = '{$return_data['order_no']}' AND status = '0'")->getRawSql();


                            ExceptionOrder::exceptionOrderRecord(0, 0, 0, $return_data);
                        }
                        return true;
                    }
                    break;
                default :
                    exit('fail');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
