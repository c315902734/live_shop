<?php
namespace frontend\controllers;
use common\models\ApiResponse;
use common\models\DuibaOrders;
use OAuth2\Request;
use Yii;
use common\service\Exchange;
use common\models\User;
use common\models\UserAmount;
use yii\web\Controller;

/**
 * 兑吧相关接口
 */
class ExchangeController extends \yii\rest\Controller
{
    /*
    *  积分消耗请求的解析方法
    *  当用户进行兑换时，兑吧会发起积分扣除请求，开发者收到请求后，可以通过此方法进行签名验证与解析，然后返回相应的格式
     * 兑吧 创建订单
     * 判断 用户是否有足够积分 下单、响应
    */
    function actionParseCreditConsume(){
        $result = array();
        $request_array = $_GET;

        //查看用户汇闻币 是否足够下单
        $user_info = User::find()->where(array('user_id'=>$request_array["uid"]))->asArray()->one();
        $amount    = $user_info['amount'];

        if($request_array["appKey"] != Yii::$app->params['appKey']){
            $result['status']       = 'fail';
            $result['errorMessage'] = 'appKey not match';
            $result['credits']      = $amount;
            print_r(json_encode($result));die;
        }
        if($request_array["timestamp"] == null ){
            $result['status']       = 'fail';
            $result['errorMessage'] = 'timestamp can\'t be null';
            $result['credits']      = $amount;
            print_r(json_encode($result));die;
        }
        $verify = Exchange::signVerify(Yii::$app->params['appSecret'],$request_array);
        Yii::info($verify);
        if(!$verify){
            $result['status']       = 'fail';
            $result['errorMessage'] = $verify;
            $result['credits']      = $amount;
            print_r(json_encode($result));die;
        }


        if($amount < $request_array['credits']){
            $result['status']       = 'fail';
            $result['errorMessage'] = 'Lack of gold';
            $result['credits']      = $amount;
            print_r(json_encode($result));die;
        }else{
            //预扣积分  并更改兑吧积分状态
            $param['user_id']      = $request_array["uid"];
            $param['operate_cnt']  = $request_array['credits'];
            $param['operate']      = '2';
            $param['operate_name'] = '兑换';
            $edit_cre = UserAmount::addUserAmount($param);

            if($edit_cre){
                $duiba_orders = new DuibaOrders();

                $bizId_cre =  time().$this->getRange();
                $duiba_orders->bizId           = $bizId_cre;
                $duiba_orders->user_id         = $request_array["uid"];   //用户id
                $duiba_orders->credits         = $request_array["credits"]; //本次兑换扣除的积分
                $duiba_orders->actual_price    = $request_array["actualPrice"]; //此次兑换实际扣除开发者账户费用，单位为分
                $duiba_orders->duiba_order_num = $request_array["orderNum"]; //兑吧订单号
                $duiba_orders->type            = $request_array["type"];  //兑换类型：alipay(支付宝), qb(Q币), coupon(优惠券), object(实物), phonebill(话费), phoneflow(流量), virtual(虚拟商品), turntable(大转盘), singleLottery(单品抽奖)，hdtoolLottery(活动抽奖),manualLottery(手动开奖),gameLottery(游戏)，所有类型不区分大小写
                $duiba_orders->credits_status  = 1; //积分状态 成功
                $duiba_orders->gmt_create      = date('Y-m-d H:i:s',time()); //创建时间
                $duiba_orders->params          = $request_array['params']; //详情参数，不同的类型，返回不同的内容，中间用英文冒号分隔
                $duiba_orders->description     = $request_array["description"]; //本次积分消耗的描述(带中文，请用utf-8进行url解码)
                $duiba_orders->face_price      = $request_array["facePrice"]; //兑换商品的市场价值
                $duiba_orders->ip              = $request_array["ip"]; //ip
                $duiba_orders->save();

                Yii::info('ok');
                $result['status']       = 'ok';
                $result['errorMessage'] = '';
                $result['bizId']        = $bizId_cre;
                $result['credits']      = $amount - $request_array['credits'];
                print_r(json_encode($result));die;
            }else{
                $result['status']       = 'fail';
                $result['errorMessage'] = 'Failure of withheld money';
                $result['credits']      = $amount;
                print_r(json_encode($result));die;
            }
        }

    }

    /*
    *  兑换订单的结果通知请求的解析方法
    *  当兑换订单成功时，兑吧会发送请求通知开发者，兑换订单的结果为成功或者失败，如果为失败，开发者需要将积分返还给用户
    */
    function actionParseCreditNotify(){
        $request_array = $_GET;
        if($request_array["appKey"] != Yii::$app->params['appKey']){
            Yii::info("appKey not match");
            print_r("ok");die;
        }
        if($request_array["timestamp"] == null ){
            Yii::info("timestamp can\'t be null");
            print_r("ok");die;
        }
        $verify = Exchange::signVerify(Yii::$app->params['appSecret'],$request_array);
        if(!$verify){
            Yii::info("sign verify fail");
            print_r("ok");die;
        }

        //查看兑吧订单信息
        $duiba_info  = DuibaOrders::find()->where(['bizId'=>$request_array['bizId']])->asArray()->one();

        //兑换失败
        if($request_array['success'] == 'false'){
            Yii::info("结果通知失败");
            //返还积分
            $credits = $duiba_info['credits'];  //被扣积分
            $param['user_id']      = $duiba_info['user_id'];
            $param['operate_cnt']  = $credits;
            $param['operate']      = '1';
            $param['operate_name'] = '兑换失败返还积分';
            UserAmount::addUserAmount($param);


            //更改订单、积分 状态
//            $duiba_arr = array(
//                "order_status"   => 2,
//                "credits_status" => 2,
//                "error_message"  => $request_array['errorMessage'],
//                "gmt_modified"   => date("Y-m-d H:i:s")
//            );

            DuibaOrders::updateAll(["order_status"   => 2,
                "credits_status" => 2,
                "error_message"  => $request_array['errorMessage'],
                "gmt_modified"   => date("Y-m-d H:i:s")],['bizId'=>$request_array['bizId'], 'duiba_order_num'=>$request_array['orderNum']]);

//            $duiba_order->where(array('bizId'=>$request_array['bizId'], 'duiba_order_num'=>$request_array['orderNum']))
//                ->data($duiba_arr)->save();

            print_r("ok");die;
        }else {
            Yii::info("结果通知成功");
            $duiba_arr = array(
                "order_status" => 1,
                "gmt_modified" => date("Y-m-d H:i:s")
            );
            DuibaOrders::updateAll(["order_status" => 1,
                "gmt_modified" => date("Y-m-d H:i:s")],['bizId' => $request_array['bizId'], 'duiba_order_num' => $request_array['orderNum']]);
//            $duiba_order->where(array('bizId' => $request_array['bizId'], 'duiba_order_num' => $request_array['orderNum']))
//                ->data($duiba_arr)->save();

            print_r("ok");die;
        }
    }

    /*
     * 虚拟商品充值接口
     * params 目前内部定义为   积分值
     * */
    public function actionVirtualAdd(){
        $request_array = $_GET;

        //查看用户汇闻币
        $user_info = User::find()->where(['user_id'=>$request_array["uid"]])->asArray()->one();
        $amount    = $user_info['amount'];

        //生成订单流水号
        $supplierBizld = time().$this->getRange();
//        $duiba_sup = array(
//            "supplierBizId" => $supplierBizld,
//            "description"   =>  $request_array['description'],
//            "gmt_modified"  => date("Y-m-d H:i:s")
//        );
        //免费抽奖 不会生成开发者订单号
        if($request_array['developBizId'] != 'null'){
            DuibaOrders::updateAll(["supplierBizId" => $supplierBizld,
                "description"   =>  $request_array['description'],
                "gmt_modified"  => date("Y-m-d H:i:s")],['bizId' => $request_array['developBizId']]);
        }else{
            $duiba_orders = new DuibaOrders();

            $duiba_orders->user_id         = $request_array["uid"];   //用户id
            $duiba_orders->credits         = 0; //本次兑换扣除的积分
            $duiba_orders->actual_price    = 0; //此次兑换实际扣除开发者账户费用，单位为分
            $duiba_orders->duiba_order_num = $request_array["orderNum"]; //兑吧订单号
//            $duiba_orders->type            = $request_array["type"];
            $duiba_orders->credits_status  = 1; //积分状态 成功
            $duiba_orders->gmt_create      = date('Y-m-d H:i:s',time()); //创建时间
            $duiba_orders->params          = $request_array['params']; //详情参数，不同的类型，返回不同的内容，中间用英文冒号分隔
            $duiba_orders->description     = $request_array["description"]; //本次积分消耗的描述(带中文，请用utf-8进行url解码)
//            $duiba_orders->face_price      = $request_array["facePrice"]; //兑换商品的市场价值
//            $duiba_orders->ip              = $request_array["ip"]; //ip
            $duiba_orders->save();
            $res_id = $duiba_orders->attributes['id'];
        }


        if($request_array["appKey"] != Yii::$app->params['appKey']){
            Yii::info("appKey not match");
            $result['status']        = 'fail';
            $result['errorMessage']  = 'appKey not match';
            $result['credits']       = $amount;
            $result['supplierBizId'] = $supplierBizld;
            print_r(json_encode($result));die;
        }
        if($request_array["timestamp"] == null ){
            Yii::info("timestamp can\'t be null");
            $result['status']        = 'fail';
            $result['errorMessage']  = "timestamp can\'t be null";
            $result['credits']       = $amount;
            $result['supplierBizId'] = $supplierBizld;
            print_r(json_encode($result));die;
        }
//        print_r($request_array);die;
        $verify =Exchange::signVerify( Yii::$app->params['appSecret'],$request_array);
        if(!$verify){
            Yii::info("sign verify fail");
            $result['status']        = 'fail';
            $result['errorMessage']  = "sign verify fail";
            $result['credits']       = $amount;
            $result['supplierBizId'] = $supplierBizld;
            print_r(json_encode($result));die;
        }

        //免费抽奖 不会生成开发者订单号
        if($request_array['developBizId'] != 'null'){
            //查看兑吧订单信息
            $duiba_info  = DuibaOrders::find()->where(['bizId'=>$request_array['developBizId']])->asArray()->one();
        }else{
            //查看兑吧订单信息
            $duiba_info  = DuibaOrders::find()->where(['id'=>$res_id])->asArray()->one();
        }

        if(!$duiba_info){
            Yii::info("订单不存在"."----------developBid:".$request_array['developBizId']."----------duibaid:".$request_array['orderNum']);
            $result['status']        = 'fail';
            $result['errorMessage']  = "order not match";
            $result['credits']       = $amount;
            $result['supplierBizId'] = $supplierBizld;
            print_r(json_encode($result));die;
        }

        //积分充值
        $param['user_id']      = $duiba_info['user_id'];
        $param['operate_cnt']  = $request_array['params'];
        $param['operate']      = '1';
        $param['operate_name'] = '兑吧虚拟商品充值';
        $amount_res = UserAmount::addUserAmount($param);
        Yii::info(json_encode($amount_res));
        //结果 更新 状态
        if(!$amount_res){
            Yii::info("虚拟充值 失败");
            //返回失败
            $result['status']        = 'fail';
            $result['errorMessage']  = "充值失败";
            $result['credits']       = $amount;
            $result['supplierBizId'] = $supplierBizld;
            print_r(json_encode($result));die;
        }else{
            Yii::info("虚拟充值 成功");
            $result['status']        = 'success';
            $result['credits']       = $amount_res['amount'];
            $result['supplierBizId'] = $supplierBizld;
            print_r(json_encode($result));die;
        }

    }

    /*
     * 加积分接口
     * 判断 此订单是否存在 并已有处理结果 ，如果有处理结果 直接返回之前的处理结果 给兑吧
     * 没有 则新创建
     * */
    public function actionCreaditAdd(){
        $request_array = $_GET;
        //查看用户汇闻币
        $user_info = User::find()->where(['user_id'=>$request_array["uid"]])->asArray()->one();
        $amount    = $user_info['amount'];

        if($request_array["appKey"] != Yii::$app->params['appKey']){
            Yii::info("appKey not match");
            $result['status']        = 'fail';
            $result['errorMessage']  = 'appKey not match';
            $result['credits']       = $amount;
            print_r(json_encode($result));die;
        }
        if($request_array["timestamp"] == null ){
            Yii::info("timestamp can\'t be null");
            $result['status']        = 'fail';
            $result['errorMessage']  = "timestamp can\'t be null";
            $result['credits']       = $amount;
            print_r(json_encode($result));die;
        }

        $verify = Exchange::signVerify( Yii::$app->params['appSecret'],$request_array);
        if(!$verify){
            Yii::info("sign verify fail");
            $result['status']        = 'fail';
            $result['errorMessage']  = "sign verify fail";
            $result['credits']       = $amount;
            print_r(json_encode($result));die;
        }


        //查看兑吧订单信息
        $duiba_info  = DuibaOrders::find()->where(['duiba_order_num'=>$request_array['orderNum']])->asArray()->one();

        if($duiba_info && $duiba_info['order_status'] != 0){
            Yii::info("加积分 订单已有处理结果");
            //此订单 已有处理结果
            if($duiba_info['order_status'] == 1){
                $result['status']        = 'success';
                $result['errorMessage']  = "";
                $result['bizId']         = $duiba_info['bizId'];
                $result['credits']       = $amount;
                print_r(json_encode($result));die;
            }else{
                $result['status']        = 'fail';
                $result['errorMessage']  = "订单加积分失败";
                $result['credits']       = $amount;
                print_r(json_encode($result));die;
            }
        }
        //添加积分
        $param['user_id']      = $request_array['uid'];
        $param['operate_cnt']  = $request_array['credits'];
        $param['operate']      = '1';
        $param['operate_name'] = '兑吧加积分';
        $amount_res = UserAmount::addUserAmount($param);


        // 创建订单信息 并更改状态
        if(!$amount_res){
            Yii::info("订单加积分失败");
            $duiba_find = new DuibaOrders();
            $duiba_find->bizId           = time().$this->getRange();
            $duiba_find->user_id         = $request_array["uid"];   //用户id
            $duiba_find->credits         = $request_array["credits"]; //本次添加的积分
            $duiba_find->duiba_order_num = $request_array["orderNum"]; //兑吧订单号
            $duiba_find->type            = $request_array["type"];  //game（游戏），report（签到）。所有类型不区分大小写
            $duiba_find->gmt_create      = date('Y-m-d H:i:s',time()); //创建时间
            $duiba_find->description     = $request_array["description"]; //本次积分消耗的描述(带中文，请用utf-8进行url解码)
            $duiba_find->order_status    = 2 ;
            $duiba_find->error_message   = "订单加积分失败";
            $duiba_find->ip              = $request_array["ip"]; //ip
            $duiba_find->save();

            $result['status']        = 'fail';
            $result['errorMessage']  = "订单加积分失败";
            $result['credits']       = $amount;
            print_r(json_encode($result));die;
        }else{
            Yii::info("订单加积分成功");
            $duiba_find = new DuibaOrders();

            $duiba_find->bizId           = time().$this->getRange();
            $duiba_find->user_id         = $request_array["uid"];   //用户id
            $duiba_find->credits         = $request_array["credits"]; //本次添加的积分
            $duiba_find->duiba_order_num = $request_array["orderNum"]; //兑吧订单号
            $duiba_find->type            = $request_array["type"];  //game（游戏），report（签到）。所有类型不区分大小写
            $duiba_find->gmt_create      = date('Y-m-d H:i:s',time()); //创建时间
            $duiba_find->description     = $request_array["description"]; //本次积分消耗的描述(带中文，请用utf-8进行url解码)
            $duiba_find->order_status    = 1 ;
            $duiba_find->ip              = $request_array["ip"]; //ip
            $duiba_find->save();

            $result['status']        = 'success';
            $result['errorMessage']  = "";
            $result['bizId']         = $duiba_find->bizId;
            $result['credits']       = $amount_res['amount'];
            print_r(json_encode($result));die;
        }
    }


    public function getRange($cnt=9){
        $numbers = range (1,$cnt);
        //播下随机数发生器种子，可有可无，测试后对结果没有影响
        srand ((float)microtime()*1000000);
        shuffle ($numbers);
        //跳过list第一个值（保存的是索引）
        $n = '';
        while (list(, $number) = each ($numbers)) {
            $n .="$number";
        }
        return $n;
    }
}