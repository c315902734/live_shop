<?php

namespace common\models;


use common\service\Record;
use Pingpp\Charge;
use Pingpp\Pingpp;
use Pingpp\WxpubOAuth;

use Yii;
use yii\data\Sort;
use yii\helpers\Url;
use yii\test\InitDbFixture;

/**
 * This is the model class for table "shop_order".
 *
 * @property integer $order_id
 * @property integer $company_id
 * @property string $user_id
 * @property string $order_number
 * @property string $huiwenbi
 * @property integer $reciver_id
 * @property string $freight
 * @property string $express
 * @property string $express_no
 * @property integer $ship_time
 * @property integer $complete_time
 * @property integer $status
 * @property integer $order_type
 * @property string $activity_id
 * @property string $order_remarks
 * @property string $business_remarks
 * @property integer $is_del
 * @property integer $create_time
 */
class ShopOrder extends \yii\db\ActiveRecord
{
    /* 订单相关 */
    const ORDER_PAID_CODE = 1;
    const ORDER_UNPAID_CODE = 0;
    const ORDER_NOT_DEL_CODE = 0;

    /* 支付方式相关 */
    const ALIPAY_WAP_PAY_CODE = 1;
    const WECHAT_H5_PAY_CODE  = 2;
    const WECHAT_PUB_PAY_CODE = 3;
    const ALIPAY_APP_PAY_CODE = 4;
    const WECHAT_APP_PAY_CODE = 5;


    /* 收费直播商品 */
    const CHARGE_LIVE_GOODS_CODE = 2;


    public static function getDb()
    {
        return yii::$app->vrshop;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'user_id', 'reciver_id', 'ship_time', 'complete_time', 'status', 'order_type', 'is_del', 'create_time'], 'integer'],
            [['huiwenbi', 'freight'], 'number'],
            [['order_remarks', 'business_remarks'], 'string'],
            [['order_number', 'express', 'express_no'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_id' => 'Order ID',
            'company_id' => 'Company ID',
            'user_id' => 'User ID',
            'order_number' => 'Order Number',
            'huiwenbi' => 'Huiwenbi',
            'reciver_id' => 'Reciver ID',
            'freight' => 'Freight',
            'express' => 'Express',
            'express_no' => 'Express No',
            'ship_time' => 'Ship Time',
            'complete_time' => 'Complete Time',
            'status' => 'Status',
            'order_type' => 'Order Type',
            'order_remarks' => 'Order Remarks',
            'business_remarks' => 'Business Remarks',
            'is_del' => 'Is Del',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 收费直播间查询用户付费情况
     * @param int $user_id
     * @param int $goods_id
     * @return bool
     */
    public static function getGoodsPayStatusByUser($user_id = 0, $goods_id = 0)
    {
        if (!$user_id || !$goods_id) return false;

        $order_info = ShopOrder::find()
            ->alias('so')
            ->leftJoin(OrderGoodsRelation::tableName(), 'so.order_id = order_goods_relation.order_id')
            ->where(['order_goods_relation.goods_id'=>$goods_id])
            ->andWhere(['>=', 'so.status', self::ORDER_PAID_CODE])
            ->count();

        if ($order_info) {
            return true;
        }
        return false;
    }

    /**
     * @param int $goods_id
     * @param int $goods_num
     * @param int $goods_attr_id
     * @param array $user_info
     * @return array
     */
    public static function submitOrder($goods_id = 0, $goods_num = 0, $goods_attr_id = 0, array $user_info = []){
        $return_data = array();
        //商品信息
        if ($goods_attr_id) {
            $goods_info = Goods::find()
                ->alias('g')
                ->innerJoin('vrshop.goods_attribute_values gav', 'g.goods_id = gav.goods_id')
                ->select(['g.goods_id', 'g.company_id', 'g.goods_name', 'g.banner_image', 'g.goods_type', 'g.huiwenbi', 'g.freight', 'gav.pay_type', 'gav.values_content', 'gav.price', 'gav.rmb_price'])
                ->where(['g.goods_id'=>$goods_id, 'gav.values_id'=>$goods_attr_id])
                ->asArray()
                ->one();
            $goods_price = $goods_info['price'];
            $goods_rmb_price = $goods_info['rmb_price'];
        } else {
            $goods_info = Goods::find()
                ->alias('g')
                ->select(['g.goods_id', 'g.company_id', 'g.goods_name', 'g.banner_image', 'g.goods_type', 'g.pay_type', 'g.huiwenbi', 'g.rmb_price', 'g.freight'])
                ->where(['g.goods_id'=>$goods_id])
                ->asArray()->one();
            $goods_price = $goods_info['huiwenbi'];
            $goods_rmb_price = $goods_info['rmb_price'];
        }

        //用户收货地址
        $user_default_addr = UserAddress::find()
            ->select("address_id, consignee, prov, city, county, address, zipcode, phone")
            ->where(['user_id'=>$user_info['user_id'], 'is_default'=>1, 'is_del'=>0])->asArray()->one();
        $return_data['user_default_addr'] = $user_default_addr;

        //商品所在公司信息
        $return_data['company_info'] = CompanyInfo::find()
            ->select("company_id, company_name, company_contact_person, company_phone, company_email")
            ->where(['company_id'=>$goods_info['company_id']])
            ->asArray()->one();

        $return_data['goods_info'] = $goods_info;

        //运费
        $freight = 0;
        if ($user_default_addr['address_id']) {
            //有默认收货地址
            $freight = self::getFreight($goods_id, $user_default_addr['address_id']);
        } else {
            //没有默认收货地址
            $freight = $goods_info['freight'];
        }

        $settlement = array(
            'price'       => $goods_price,
            'rmb_price'   => $goods_rmb_price,
            'goods_num'   => $goods_num,
            'total_price' => intval($goods_price * $goods_num),
            'total_rmb_price' => round($goods_rmb_price * $goods_num, 2),
            'user_total_hwb'  => $user_info['amount'],
            'freight'         => $freight,
        );
        $return_data['settlement'] = $settlement;

        return $return_data;
    }

    /**
     * 前台  获取订单类表   or  单订单详情
     * @param $order_id
     * @param string $goods_name
     * @param string $company_name
     * @param int $user_id
     * @param bool $order_status
     * @param int $page
     * @param int $size
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function GetOrder($order_id, $goods_name = '', $company_name = '', $user_id = 0, $order_status = false, $page = 1, $size = 10){
        $offset = ($page - 1) * $size;
        $and_where = '1 = 1 AND o.is_del = 0';
        if($order_id){
            $and_where .= " AND o.order_id = {$order_id}";
        }
        if($goods_name){
            $and_where .= " AND g.goods_name LIKE '%{$goods_name}%'";
        }
        if($company_name){
            $and_where .= " AND c.name LIKE '%{$company_name}%'";
        }
        if($order_status !== false){
            $and_where .= " AND o.status = {$order_status}";
        }

        $order_list = self::find()
            ->alias('o')
            ->select([
                'o.order_id',
                'o.user_id',
                'o.order_number',
                'o.company_id',
                'c.name as company_name',
                'o.huiwenbi as total_price',
                'o.rmb_price as total_rmb_price',
                'o.order_type',
                'o.freight',
                'o.express',
                'o.express_no',
                'o.ship_time',
                'o.complete_time',
                'o.status',
                'FROM_UNIXTIME(o.create_time) as create_time',
                'r.goods_num',
                'g.goods_id',
                'g.goods_name',
                'g.goods_type',
                'g.huiwenbi as price',
                'g.banner_image',
                'gav.price as attr_price',
                'gav.values_content',
            ])
            ->leftJoin('vrnews1.company c', 'o.company_id = c.company_id')
            ->leftJoin('vrshop.order_goods_relation r', 'o.order_id = r.order_id')
            ->leftJoin('vrshop.goods g', 'r.goods_id = g.goods_id')
            ->leftJoin('vrshop.goods_attribute_values gav', 'r.attribute_value_id = gav.values_id')
            ->where(['o.user_id'=>$user_id])
            ->andWhere($and_where)
            ->orderBy('o.create_time DESC')
            ->offset($offset)
            ->limit($size)
            ->asArray()
            ->all();

        $order_count = self::find()
            ->alias('o')
            ->leftJoin('vrnews1.company c', 'o.company_id = c.company_id')
            ->leftJoin('vrshop.order_goods_relation r', 'o.order_id = r.order_id')
            ->leftJoin('vrshop.goods g', 'r.goods_id = g.goods_id')
            ->where(['o.user_id'=>$user_id])
            ->andWhere($and_where)
            ->orderBy('o.create_time DESC')
            ->asArray()
            ->count();

        $order_list || $order_list = [];
        $order_count || $order_count = 0;

        if($order_id){
            return $order_list;
        }else{
            return ['count'=>$order_count, 'list'=>$order_list];
        }
        return [];
    }

    /**
     * 后台订单列表
     * @param $company_id
     * @param int $order_status
     * @param $order_sn
     * @param $consignee
     * @param $start_time
     * @param $end_time
     * @param $phone
     * @param int $page
     * @param int $size
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function BackendOrder($company_id, $order_type = false, $order_status = 0, $order_sn, $consignee, $start_time, $end_time, $phone, $page = 1, $size = 10){
        $offset = ($page - 1) * $size;
        $and_where = "1 = 1 AND o.is_del = 0 AND order_type <> 2";
        if($company_id){
            $and_where .= " AND o.company_id = {$company_id}";
        }
        if($order_type !== false){
            $and_where .= " AND g.goods_type = {$order_type}";
        }
        if($order_status){
            $and_where .= " AND o.status = {$order_status}";
        }
        if($order_sn){
            $and_where .= " AND o.order_number = '{$order_sn}'";
        }
        if($consignee){
            $and_where .= " AND a.consignee like '%{$consignee}%'";
        }
        if($start_time){
            $start_time = strtotime($start_time);
            $and_where .= " AND o.create_time > '{$start_time}'";
        }
        if($end_time){
            $end_time = strtotime($end_time);
            $and_where .= " AND o.create_time < '{$end_time}'";
        }
        if($phone){
            $and_where .= " AND a.phone = {$phone}";
        }

        $order_count = self::find()
            ->alias('o')
            ->leftJoin('vrshop.order_goods_relation r', 'o.order_id = r.order_id')
            ->leftJoin('vrshop.goods g', 'r.goods_id = g.goods_id')
            ->leftJoin('vrshop.user_address a', 'o.reciver_id = a.address_id')
            ->where($and_where)
            ->orderBy('o.create_time DESC')
            ->asArray()
            ->count();

        $order_list = self::find()
            ->alias('o')
            ->select([
                'o.order_id',
                'o.order_number',
                'o.huiwenbi as total_price',
                'r.goods_num',
                'g.goods_name',
                'g.huiwenbi as price',
                'g.banner_image',
                'g.goods_type',
                'o.freight',
                'o.order_type',
                'a.consignee',
                'a.phone',
                'CONCAT(a.prov,a.city,a.county,a.address) as full_address',
                //"IF(o.status >= 1, '已支付', '未支付') as pay_status",
                'o.status as order_status',
                'FROM_UNIXTIME(o.create_time) as create_time',
            ])
            ->leftJoin('vrshop.order_goods_relation r', 'o.order_id = r.order_id')
            ->leftJoin('vrshop.goods g', 'r.goods_id = g.goods_id')
            ->leftJoin('vrshop.user_address a', 'o.reciver_id = a.address_id')
            ->where($and_where)
            ->orderBy('o.create_time DESC')
            ->offset($offset)
            ->limit($size)
            ->asArray()
            ->all();

        $return_data = [];
        $order_list || $order_list = [];
        $order_count || $order_count = 0;

        $return_data = ['count'=>$order_count, 'list'=>$order_list];
        return $return_data;
    }

    /**
     * 后台  订单详情
     */
    public static function BackendOrderInfo($order_id){
        $order_info = self::find()
            ->alias('o')
            ->select([
                'o.order_id',
                'o.order_number',
                'o.huiwenbi as total_price',
                'o.rmb_price as total_rmb_price',
                'o.freight',
                'o.ship_time',
                'o.complete_time',
                'r.goods_num',
                'g.goods_id',
                'g.goods_name',
                'g.goods_type',
                'g.huiwenbi as price',
                'g.banner_image',
                'a.consignee',
                'a.phone',
                'a.zipcode',
                'CONCAT(a.prov,a.city,a.county,a.address) as full_address',
                'o.status',
                'o.order_type',
                'o.order_remarks',
                'o.business_remarks',
                'o.express',
                'o.express_no',
                'o.status as order_status',
                'FROM_UNIXTIME(o.create_time) as create_time',
            ])
            ->leftJoin('vrshop.order_goods_relation r', 'o.order_id = r.order_id')
            ->leftJoin('vrshop.goods g', 'r.goods_id = g.goods_id')
            ->leftJoin('vrshop.user_address a', 'o.reciver_id = a.address_id')
            ->orderBy('o.create_time DESC')
            ->where(['o.order_id'=>$order_id])
            ->asArray()
            ->all();

        foreach ($order_info as &$order){
            if($order['goods_type'] == 2){
                $order['virtual_ship_info'] = VirtualOrderShip::find()
                    ->alias('vos')
                    ->select(['vos.ship_id', 'vgi.serial_number', 'vgi.password', 'vgi.deadline'])
                    ->leftJoin('vrshop.virtual_goods_info vgi', 'vos.details_id = vgi.details_id')
                    ->where(['vos.order_id'=>$order['order_id']])
                    ->asArray()
                    ->all();
            }else{
                $order['virtual_ship_info'] = '';
            }
        }
        unset($order);

        return $order_info;
    }

    /**
     * 根据订单获取商品信息
     * @param $order_id
     */
    public static function GetOrderGoods($order_id, $field = "g.*, o.*"){
        $info = self::find()
            ->alias('o')
            ->select($field)
            ->leftJoin('vrshop.order_goods_relation r', 'o.order_id = r.order_id')
            ->leftJoin('vrshop.goods g', 'r.goods_id = g.goods_id')
            ->where(['o.order_id'=>$order_id])
            ->asArray()
            ->one();

        $info || $info = [];
        return $info;
    }

    public static function getFreight($goods_id, $address_id){
        //商品信息
        $goods_info   = Goods::findOne($goods_id);
        //收货地址信息
        $address_info = UserAddress::findOne($address_id);
        //公司所在地区
        $company_info = Company::findOne($goods_info->company_id);
        if($goods_info->freight_type == 0){
            $freight = $goods_info->freight;
        }else if($goods_info->freight_type == 1){
            //外省费用
            if($address_info->prov == $company_info->prov || $address_info->city == $company_info->prov){
                /* 同城 */
                $freight = 0;
            }else{
                $freight = $goods_info->freight;
            }
        }
        return $freight;
    }


    /**
     * @param $order_type
     * @param $user_id
     * @param $channel
     * @param $pay_type
     * @return string
     */
    public static function _createOsn($order_type, $user_id, $channel, $pay_type){
        $orderSn = date('y')                            //获取年份对应的映射值
            . date('m')                                 //月份
            . date('d')                                //日期
            . $channel                       //当前时间戳 截取后5位
            . $pay_type                      //当前微秒数 截取
            . substr($user_id, -4)
            . self::_getMicrotime();

        if ($order_type == self::CHARGE_LIVE_GOODS_CODE) {
            $orderSn = 'zb'.$orderSn;
        }
        return $orderSn;
    }

    public static function _getMicrotime(){
        $_time = number_format(microtime(true), 4, '', '');
        $_time = substr($_time, -10);
        return $_time;
    }

    /**

     * 确认下单（汇闻币支付
     * @param array $user_info
     * @param int $company_id
     * @param int $from_live_company_id
     * @param array $goods_info
     * @param int $goods_num
     * @param int $order_type
     * @param int $backend_hwb_total_price
     * @param int $address_id
     * @param int $goods_attr_id
     * @param int $freight
     * @param string $order_remarks
     * @return string
     */
    public static function confirmPayHwbOrder(array $user_info = [], $from_live_id = 0, $from_live_company_id = 0, array $goods_info = [], $goods_num = 0, $order_type = 0, $address_id = 0, $freight = 0, $order_remarks = ''){
        if(!$user_info || !$goods_info || !$goods_num || !$address_id) return '参数错误';

        $transaction = yii::$app->db->beginTransaction();
        try{
            //订单入库
            $insert_order_ret = yii::$app->db->createCommand()->insert('vrshop.shop_order', [
                'company_id' => $goods_info['company_id'],
                'user_id'    => $user_info['user_id'],
                'order_number' => self::_createOsn($order_type, $user_info['user_id'], 0, 0),
                'huiwenbi'   => intval($goods_info['goods_hwb_price'] * $goods_num),
                'reciver_id' => $address_id,
                'freight'    => $freight,
                'order_type' => $order_type,
                'order_remarks' => $order_remarks,
                'status' => '1',
                'is_del' => '0',
                'create_time'=> time(),
            ])->execute();
            if(!$insert_order_ret) throw new \Exception('增加订单失败');

            $order_id = yii::$app->db->getLastInsertID();

            //订单 商品表
            $insert_order_goods_ret = yii::$app->db->createCommand()->insert('vrshop.order_goods_relation',[
                'order_id'  => $order_id,
                'goods_id'  => $goods_info['goods_id'],
                'goods_hwb_price'  => $goods_info['goods_hwb_price'],
                'goods_rmb_price'  => $goods_info['goods_rmb_price'],
                'goods_num' => $goods_num,
                'attribute_value_id'   => intval($goods_info['goods_attr_id']),
                'from_live_id'   => $from_live_id,
                'from_live_company_id' => $from_live_company_id,
                'create_time' => time()
            ])->execute();
            if(!$insert_order_goods_ret) throw new \Exception('增加订单失败');

            $transaction->commit();
            return $order_id;
        }catch(\Exception $e){
            return '订单提交失败，'.$e->getMessage();
        }
        return '订单提交失败，请重试';
    }

    /**
     * 包含第三方支付（创建订单
     * @param $user_info
     * @param $company_id
     * @param $from_live_company_id
     * @param $goods_info
     * @param $goods_num
     * @param $order_type
     * @param $backend_hwb_total_price
     * @param $address_id
     * @param $goods_attr_id
     * @param $freight
     * @param $order_remarks
     * @return string
     */
    public static function createOrder($user_info = [], $from_live_id = 0, $from_live_company_id = 0, $goods_info = [], $goods_num = 0, $order_type = 0, $address_id = 0, $freight = 0, $order_remarks = ''){
        if(!$user_info || !$goods_info || !$goods_num) return '参数错误';

        $goods_rmb_price_count = round($goods_info['goods_rmb_price'] * $goods_num, 2);
        $goods_hwb_price_count = intval($goods_info['goods_hwb_price'] * $goods_num);

        if ($goods_rmb_price_count <= 0) {
            return '商品价格必须大于零';
        }

        //创建订单
        $trans = yii::$app->db->beginTransaction();
        try {
            $insert_order_ret = yii::$app->db->createCommand()->insert('vrshop.shop_order', [
                'company_id'   => $goods_info['company_id'],
                'user_id'      => $user_info['user_id'],
                'order_number' => ShopOrder::_createOsn($order_type, $user_info['user_id'], 0, 0),
                'huiwenbi'     => $goods_hwb_price_count,
                'rmb_price'    => $goods_rmb_price_count,
                'reciver_id'   => $address_id,
                'freight'      => $freight,
                'order_remarks' => $order_remarks,
                'order_type'   => $order_type,
                'status'       => self::ORDER_UNPAID_CODE,
                'is_del'       => '0',
                'create_time'  => time(),
            ])->execute();
            if (!$insert_order_ret) throw new \Exception('增加订单失败');

            $order_id = yii::$app->db->getLastInsertID();

            //订单 商品
            $insert_order_goods_ret = yii::$app->db->createCommand()->insert('vrshop.order_goods_relation',[
                'order_id' => $order_id,
                'goods_id' => $goods_info['goods_id'],
                'goods_hwb_price' => $goods_info['goods_hwb_price'],
                'goods_rmb_price' => $goods_info['goods_rmb_price'],
                'goods_num' => $goods_num,
                'attribute_value_id'   => intval($goods_info['goods_attr_id']),
                'from_live_id'         => $from_live_id,
                'from_live_company_id' => $from_live_company_id,
                'create_time' => time()
            ])->execute();
            if(!$insert_order_goods_ret) throw new \Exception('增加订单失败');

            $trans->commit();
            return $order_id;
        } catch (\Exception $e) {
            return '订单提交失败，'.$e->getMessage();
        }
    }

    /**
     * @param int $order_id
     * @param string $user_token
     * @param array $user_info
     * @param string $open_id
     * @param string $client_ip
     * @param int $pay_type                     支付方式 0：汇闻币 1：汇闻币 + 支付宝 2：汇闻币 + 微信H5 3：汇闻币+微信公众号 4:支付宝app 5:wechat_app
     * @param int $live_id
     * @param string $success_redirect_url
     * @param string $cancel_redirect_url
     * @return array|bool|string
     */
    public static function orderPay($order_id = 0, $user_token = '', array $user_info = [], $open_id = '', $client_ip = '', $pay_type = 0, $live_id = 0, $success_redirect_url = '', $cancel_redirect_url = ''){
        if (!$order_id || !$user_token || empty($user_info) || !$client_ip || !$pay_type) return [];

        if ($pay_type == self::WECHAT_PUB_PAY_CODE && !$open_id) {
            return '参数错误';
        }

        // 订单信息
        $order_info = ShopOrder::find()
            ->where(['order_id'=>$order_id, 'user_id'=>$user_info['user_id'], 'status'=>self::ORDER_UNPAID_CODE])
            ->asArray()->one();

        if (!$order_info) return '订单信息错误';
        if ($order_info['rmb_price'] * 100 <= 0) return '订单金额错误';
        if ($order_info['status'] > 0) return '该订单已经支付';

        $channel = '';
        switch (intval($pay_type)){
            case self::WECHAT_H5_PAY_CODE:
                //wechat_h5 pay
                $channel = 'wx_wap';
                $extra = [
                    'result_url' => $success_redirect_url,
                ];
                break;
            case self::ALIPAY_WAP_PAY_CODE:
                // alipay wap
                $channel = 'alipay_wap';
                $extra = [
                    'success_url' => $success_redirect_url,
                    'cancel_url'  => $cancel_redirect_url,
//                    'app_pay'     => true,   //打开支付宝APP
                ];
                break;
            case self::WECHAT_PUB_PAY_CODE:
                // wechat_pub 微信公众号支付
                $channel = 'wx_pub';
                $extra = [
                    "open_id" => $open_id,
                ];
                break;
            case self::ALIPAY_APP_PAY_CODE:
                // alipay  支付宝APP支付
                $channel = 'alipay';
                $extra = [];
                break;
            case self::WECHAT_APP_PAY_CODE:
                $channel = 'wx';
                $extra = [];
                break;
            default:
                $channel = '';
        }

        if($channel == '') return '支付方式错误';

        Pingpp::setApiKey(Yii::$app->params['PING_API_KEY']);
        try {
            $ch = Charge::create(
                array(
                    "subject" => "[支付订单，ID：[{$order_info['order_id']}]",
                    "body" => "支付订单",
                    "amount" => intval($order_info['rmb_price'] * 100),
                    "order_no" => $order_info['order_number'],
                    "currency" => "cny",
                    "extra" => $extra,                                       //特定渠道发起交易时需要的额外参数以及部分渠道支付成功返回的额外参数。
                    "channel" => $channel,
                    "client_ip" => $client_ip,
                    "app" => array("id" => Yii::$app->params['PING_ID']),
                    "time_expire" => time() + 2 * 3600
                )
            );

            $ch_arr = json_decode(json_encode($ch), true);

            switch ($pay_type) {
                case self::ALIPAY_WAP_PAY_CODE:
                    foreach ($ch_arr['credential']['alipay_wap'] as $key=>$val) {
                        if ($key == 'channel_url') {
                            $server_url = $ch_arr['credential']['alipay_wap']['channel_url'];
                            unset($ch_arr['credential']['alipay_wap']['channel_url']);
                        }
                    }
                    $redirect_url_query = http_build_query($ch_arr['credential']['alipay_wap']);
                    $redirect_url = $server_url.'?'.$redirect_url_query;
                    $pay_params = ['pay_type'=>self::ALIPAY_WAP_PAY_CODE, 'redirect_url'=>$redirect_url];
                    break;
                case self::WECHAT_H5_PAY_CODE:
                    $redirect_url = $ch_arr['credential']['wx_wap'];
                    $pay_params = ['pay_type'=>self::WECHAT_H5_PAY_CODE, 'redirect_url'=>$redirect_url];
                    break;
                case self::WECHAT_PUB_PAY_CODE:
                    $wxApi_pay_param = $ch_arr['credential']['wx_pub'];
                    $pay_params = ['pay_type'=>self::WECHAT_PUB_PAY_CODE, 'wechat_pub_pay_params'=>$wxApi_pay_param];
                    break;
                case self::ALIPAY_APP_PAY_CODE:
                    $pay_params = ['pay_type'=>self::ALIPAY_APP_PAY_CODE, 'app_pay_params'=>$ch_arr];
                    break;
                case self::WECHAT_APP_PAY_CODE:
                    $pay_params = ['pay_type'=>self::WECHAT_APP_PAY_CODE, 'app_pay_params'=>$ch_arr];
                    break;
                default :
                    return false;
            }

            return $pay_params;
        } catch (Base $e) {
            $error_body = json_decode($e->getHttpBody(), true);
            $message = $error_body['error']['message'];
            $type    = $error_body['error']['type'];
            $ping_plus_err_array = array_flip(PingPlus::$error_desc);

            $code = isset($ping_plus_err_array[$type]) ? $ping_plus_err_array[$type] : 16007;

            if ($code != 16007) {
                return $message;
            }

            return $message;
        }
    }


    /**
     * 直播间商品排序
     * 销售量倒序
     * @param $section_id
     * @param int $page
     * @param int $size
     * @return array
     */
    public static function hotGoodsSort($section_id = 1, $page = 1, $size = 10){
        $goods_list = SectionGoods::find()
            ->alias('sg')
            ->select([
                'g.goods_id',
                'g.company_id',
                'g.goods_name',
                'g.banner_image',
                'g.abstract',
                'g.huiwenbi',
                'g.rmb_price',
                'SUM(ogr.goods_num) as paynum'
            ])
            ->leftJoin('vrshop.goods g', 'sg.good_id = g.goods_id')
            ->leftJoin('vrshop.order_goods_relation ogr', 'ogr.goods_id = sg.good_id')
            ->where(['sg.section_id'=>$section_id])
            ->groupBy('sg.good_id')
            ->orderBy(['paynum'=>SORT_DESC])
            ->limit($size)
            ->offset(($page - 1) * $size)
            ->asArray()->all();

        return $goods_list;
    }

}
