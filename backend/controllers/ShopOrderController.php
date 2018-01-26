<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2017/6/12
 * Time: 10:57
 */

namespace backend\controllers;

use common\models\ShopOrder;
use common\models\User1;
use common\models\VirtualGoodsInfo;
use common\models\VirtualOrderShip;
use common\service\Record;
use Pingpp\Charge;
use Pingpp\Pingpp;
use yii;

class ShopOrderController extends PublicBaseController
{
    protected $order_pay_code;
    protected $order_confirm_code;
    protected $order_shipped_code;
    protected $order_cancel_code;
    protected $order_complete_code;
    protected $spent_huiwenbi_code;
    protected $received_huiwenbi_code;
    protected $bargain_order_code;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub

        $this->order_pay_code = 1;
        $this->order_confirm_code = 2;
        $this->order_shipped_code = 3;
        $this->order_complete_code = 4;
        $this->order_cancel_code  = 6;
        $this->spent_huiwenbi_code = 2;
        $this->received_huiwenbi_code = 1;

        $this->bargain_order_code = 3;
    }

    /**
     * 后台订单列表
     */
    public function actionOrderList(){
        $company_id   = yii::$app->request->post('company_id', 0);
        $order_type   = yii::$app->request->post('order_type', false);   //0 普通订单， 1 砍价帮订单  2虚拟商品订单
        $order_status = yii::$app->request->post('order_status', 0);
        $order_sn     = yii::$app->request->post('order_sn', '');
        $consignee    = yii::$app->request->post('consignee', '');
        $start_time   = yii::$app->request->post('start_time', '');
        $end_time     = yii::$app->request->post('end_time', '');
        $phone        = yii::$app->request->post('phone', '');
        $page         = yii::$app->request->post('page', 1);
        $size         = yii::$app->request->post('size');
        if(!$company_id) $this->_errorData('1000', '参数错误');

        $return_data = ShopOrder::BackendOrder($company_id, $order_type, $order_status, $order_sn, $consignee, $start_time, $end_time, $phone, $page, $size);
        $this->_successData($return_data);
    }

    /**
     * 后台订单详情
     */
    public function actionOrderInfo(){
        $company_id   = yii::$app->request->post('company_id');
        $order_id   = yii::$app->request->post('order_id');

        if(!$order_id) $this->_errorData('1007', '参数错误');

        $order_info = ShopOrder::BackendOrderInfo($order_id);
        if(!$order_info) $this->_errorData('1008', '订单信息错误');

        $this->_successData($order_info);
    }

    /**
     * 确认订单
     */
    public function actionConfirmOrder(){
        $company_id   = yii::$app->request->post('company_id');
        $order_id   = yii::$app->request->post('order_id');
        if(!$order_id) $this->_errorData('1001', '参数错误');

        $order_info = ShopOrder::findOne($order_id);
        if($order_info['status'] != $this->order_pay_code || $order_info['is_del'] == 1) $this->_errorData('1003', '订单信息错误');

        $order_info->status = $this->order_confirm_code;
        $ret = $order_info->save();
        if($ret){
            $this->_successData('确认订单成功');
        }
        $this->_errorData('1004', '确认订单失败');
    }

    /**
     * 取消订单
     */
    public function actionCancelOrder(){
        $company_id = yii::$app->request->post('company_id');
        $order_id   = yii::$app->request->post('order_id');
        if(!$order_id) $this->_errorData('1001', '参数错误');

        $order_info = $this->getOrderInfo($order_id);
        if($order_info['status'] >= $this->order_shipped_code){
            $this->_errorData('1005', '取消失败，订单状态错误');
        }

        $user_info = User1::findOne($order_info['user_id'])->toArray();

        $trans = yii::$app->db->beginTransaction();
        try{
            //更新订单状态
            $up_order_ret = yii::$app->db->createCommand()->update('vrshop.shop_order', ['status'=>$this->order_cancel_code], "order_id = {$order_info['order_id']}")->execute();
            if(!$up_order_ret) throw new \Exception('更新订单失败，请重试');

            //退换总库存
            $up_goods_stock_ret = yii::$app->db->createCommand()->update('vrshop.goods', [
                'goods_stock' => new yii\db\Expression("goods_stock + {$order_info['goods_num']}")
            ], "goods_id = {$order_info['goods_id']} AND goods_stock >= 0")->execute();
            if (!$up_goods_stock_ret) throw new \Exception('退还总存库失败');

            //退换属性库存
            if($order_info['goods_type'] == 1){
                if(is_numeric($order_info['attribute_value_id'])) {
                    $up_goods_attr_stock_ret = yii::$app->db->createCommand()->update('vrshop.goods_attribute_values', [
                        'stock' => new yii\db\Expression("stock + {$order_info['goods_num']}")
                    ], "values_id = {$order_info['attribute_value_id']} AND stock >= 0")->execute();
                    if (!$up_goods_attr_stock_ret) throw new \Exception('退还属性库存失败');
                }
            }

            //退还汇闻币
            $up_user_ret = yii::$app->db->createCommand()->update('vruser1.user', ['amount'=>new yii\db\Expression("amount + {$order_info['huiwenbi']}")], "user_id = {$order_info['user_id']}")->execute();
            if(!$up_user_ret) throw new \Exception('返还用户汇闻币失败，请重试');

            //增加汇闻币明细
            $surplus = (int)($user_info['amount'] + $order_info['huiwenbi']);
            $insert_user_amount_ret = yii::$app->db->createCommand()->insert('vruser1.user_amount', [
                'user_id' => $user_info['user_id'],
                'operate_cnt' => $order_info['huiwenbi'],
                'surplus' => $surplus,
                'operate' => $this->received_huiwenbi_code,
                'operate_name' => '商家取消订单：'.$order_info['order_id'],
                'created_at' => date('Y-m-d H:i:s', time()),
            ])->execute();
            if(!$insert_user_amount_ret) throw new \Exception('更新用户汇闻币明细错误');

            /*
            //退換人民幣
            if ($order_info['pay_type'] == 1 || $order_info['pay_type'] == 2) {
                Pingpp::setApiKey(Yii::$app->params['PING_API_KEY']);
                if (!$order_info['pingxx_sn']) throw new \Exception('参数错误，请联系相关人员');
                $ch = Charge::retrieve($order_info['pingxx_sn']);
                $re = $ch->refunds->create([
                    'amount' => $order_info['rmb_price'],
                    'description' => '订单退款',
                ]);
            }
            */

            $trans->commit();
            $this->_successData('取消订单成功');
        }catch (\Exception $e){
            $trans->rollBack();
            $this->_errorData('10061', $e->getMessage());
        }
        $this->_errorData('1006', '取消订单失败');
    }

    /**
     * 后台 订单发货
     */
    public function actionOrderExpress(){
        $company_id = yii::$app->request->post('company_id', 0);
        $order_id   = yii::$app->request->post('order_id', 0);
        $express      = yii::$app->request->post('express', '');
        $express_no   = yii::$app->request->post('express_no', '');
        $virtual_goods_id = yii::$app->request->post('virtual_goods_id', 0);
        if(!$order_id) $this->_errorData('1008', '参数错误');

        $order_info = $this->getOrderInfo($order_id);
        if($order_info['status'] != $this->order_confirm_code){
            $this->_errorData('0001', '发货失败，此订单未确认。');
        }

        $goods_type = $order_info['goods_type'];
        if($goods_type == 1){
            /* 实体商品订单 */
            if(!$order_id || !$express || !$express_no) $this->_errorData('1008', '请填写快递信息');
            $order_model = ShopOrder::findOne($order_info['order_id']);
            $order_model->status  = $this->order_shipped_code;
            $order_model->express = $express;
            $order_model->express_no = $express_no;
            $order_model->ship_time = time();
            $ret = $order_model->save();
            if($ret){
                $this->_successData('订单发货成功');
            }
            $this->_errorData('1009', '订单发货失败');
        }elseif($goods_type == 2){
            $virtual_goods_id = json_decode($virtual_goods_id, true);
            //防止多发货
            if(count($virtual_goods_id) > $order_info['goods_num']) $this->_errorData('100811', '所选虚拟商品大于购买数量');

            if(!$virtual_goods_id) $this->_errorData('10081', '参数错误');

            $virtual_info = VirtualGoodsInfo::findAll($virtual_goods_id);
            foreach ($virtual_info as $key=>$val){
                if($val->is_sold == 1) $this->_errorData('10082', '已出售');
            }

            //开始事物
            $trans = Yii::$app->db->beginTransaction();

            try{
                $_time = time();
                $insert_arr = [];
                $v_goods_id_arr = '';
                foreach ($virtual_goods_id as $k=>$v){
                    $insert_arr[$k]['order_id'] = $order_id;
                    $insert_arr[$k]['goods_id'] = $order_info['goods_id'];
                    $insert_arr[$k]['details_id'] = $v;
                    $insert_arr[$k]['create_time'] = time();
                    $v_goods_id_arr .= $v.',';
                }
                //插入虚拟商品订单发货表
                $insert_ret = yii::$app->db->createCommand()->batchInsert('vrshop.virtual_order_ship', ['order_id', 'goods_id', 'details_id', 'create_time'], $insert_arr)->execute();
                if(!$insert_ret) throw new \Exception('error1');

                //更新虚拟商品表
                $virtual_goods_id = trim($v_goods_id_arr, ',');
                $up_virtual_goods_info_sql = "UPDATE vrshop.virtual_goods_info SET `is_sold` = 1 WHERE `details_id` IN ({$virtual_goods_id})";
                $up_virtual_goods_info_ret = yii::$app->db->createCommand($up_virtual_goods_info_sql)->execute();
                if(!$up_virtual_goods_info_ret) throw new \Exception('error2');

                //更新订单表 发货时间 状态
                $up_order_sql = "UPDATE vrshop.shop_order SET `status` = {$this->order_shipped_code}, `ship_time` = {$_time} WHERE `order_id` = '{$order_id}'";
                $up_order_ret = yii::$app->db->createCommand($up_order_sql)->execute();
                if(!$up_order_ret) throw new \Exception('error3');

                $trans->commit();
                $this->_successData('订单发货成功');
            }catch (\Exception $e){
                $trans->rollBack();
                $this->_errorData('50011', $e->getMessage());
            }
            $this->_errorData('50011', 'error');
        }else{
            $this->_errorData('1008', '参数错误');
        }
    }

    /**
     * 确认收货
     */
    public function actionCompleteOrder(){
        $company_id = yii::$app->request->post('company_id');
        $order_id   = yii::$app->request->post('order_id');
        if(!$order_id) $this->_errorData('1010', '参数错误');

        $order_info = ShopOrder::findOne($order_id);
        if($order_info->status != $this->order_shipped_code){
            $this->_errorData('1011', '订单信息错误');
        }

        $order_info->status = $this->order_complete_code;
        $ret = $order_info->save();
        if($ret){
            $this->_successData('操作成功');
        }
        $this->_errorData('1012', '操作失败');
    }

    /**
     * 订单商家备注
     */
    public function actionBusinessRemarks(){
        $order_id = yii::$app->request->post('order_id', 0);
        $business_remarks = yii::$app->request->post('business_remarks', 0);
        if(!$order_id || !$business_remarks) $this->_errorData('10000', '参数错误');

        $order_info = ShopOrder::findOne($order_id);
        if(!$order_info) $this->_errorData('10001', '订单信息错误');

        $order_info->business_remarks = $business_remarks;
        $up_order_ret = $order_info->save();
        if(!$up_order_ret) $this->_errorData('10002', '添加备注失败');
        $this->_successData('添加备注成功');
    }

    /**
     * 批量确认订单
     */
    public function actionBatchConfirmOrder(){
        $order_json_id = yii::$app->request->post('order_id', 0);
        if(!$order_json_id) $this->_errorData('11000', '参数错误');

        $order_id_arr = json_decode($order_json_id, true);
        if(!$order_id_arr) $this->_errorData('11001', '参数错误');

        $up_order_ret = ShopOrder::updateAll(['status'=>$this->order_confirm_code], ['order_id'=>$order_id_arr, 'status'=>$this->order_pay_code]);
        if(!$up_order_ret) $this->_errorData('11002', '批量操作失败');
        $this->_successData('批量操作成功');
    }

    /**
     * 获取订单详情
     * @param $order_id
     * @return array|null|yii\db\ActiveRecord
     *
     */
    private function getOrderInfo($order_id){
        $order_info = ShopOrder::find()
            ->alias('o')
            ->select('o.*, ogr.goods_id, ogr.goods_num, ogr.attribute_value_id, g.goods_type')
            ->leftJoin('vrshop.order_goods_relation ogr', 'o.order_id = ogr.order_id')
            ->leftJoin('vrshop.goods g', 'ogr.goods_id = g.goods_id')
            ->where(['o.order_id'=>$order_id, 'o.is_del'=>0])
            ->asArray()
            ->one();
        if(!$order_info) $this->_errorData('1002', '订单信息错误');
        return $order_info;
    }
}