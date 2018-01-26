<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2017/6/16
 * Time: 14:37
 */

namespace backend\controllers;

use common\models\ActivityLotteryGeneralPrize;
use common\models\ActivityLotteryGuestsPrize;
use common\models\ActivityLotteryPrize;
use common\models\ActivityLottery;
use common\models\ActivityLotteryBase;
use common\models\ActivityLotteryRecord;
use common\models\ActivityMAdConfig;
use common\models\Goods;
use common\models\GoodsAttributeValues;
use common\models\LotterPrize;
use common\models\VirtualGoodsInfo;
use yii;
class ActivityLotteryController extends PublicBaseController
{
    protected $virtual_goods_code;
    protected $activity_default_status;
    protected $activity_open_status;
    protected $activity_close_code;
    protected $activity_general_prize_code;
    protected $activity_real_goods_code;
    protected $activity_virtual_goods_code;

    private $_activity_code;
    private $_guest_activity_code;

    private $_activity_pay_play_code;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->virtual_goods_code = 2;
        $this->activity_default_status = 2;
        $this->activity_open_status = 1;
        $this->activity_close_code = 2;
        $this->activity_general_prize_code = 2;
        $this->activity_real_goods_code = 0;
        $this->activity_virtual_goods_code = 1;

        $this->_activity_code = 0;
        $this->_guest_activity_code = 1;

        $this->_activity_pay_play_code = 1;
    }

    /**
     * 大转盘公共信息添加
     */
    public function actionSetActivityBaseInfo(){
        $base_id          = yii::$app->request->post('base_id', 0);
        $company_id       = yii::$app->request->post('company_id', 0);
        $cost_huiwenbi    = yii::$app->request->post('huiwenbi', 0);
        $limit_num   = yii::$app->request->post('limit_num', 0);
        $limit_num_type   = yii::$app->request->post('limit_num_type', 0);
        $free_num    = yii::$app->request->post('free_num', 0);
        $free_num_type    = yii::$app->request->post('free_num_type', 0);
        if(!$company_id || !$cost_huiwenbi || !$limit_num) $this->_errorData('5000', '参数错误');

        if($base_id){
            $activity_base_model = ActivityLotteryBase::findOne($base_id);
            if(!$activity_base_model) $this->_errorData('5002', '数据库中没有此信息');
        }else{
            $base_info_count = ActivityLotteryBase::find()->count();
            if($base_info_count > 1) $this->_errorData('50021', '基础信息已存在');
            $activity_base_model = new ActivityLotteryBase();
            $activity_base_model->create_time = time();
        }
        $activity_base_model->company_id = $company_id;
        $activity_base_model->cost_huiwenbi = $cost_huiwenbi;
        $activity_base_model->limit_num = $limit_num;
        $activity_base_model->limit_num_type = $limit_num_type;
        $activity_base_model->free_num = $free_num;
        $activity_base_model->free_num_type = $free_num_type;
        $save_ret = $activity_base_model->save();
        if($save_ret) $this->_successData('编辑成功');
        $this->_errorData('5001', '编辑失败');
    }

    /**
     * 创建活动 获取基本配置
     */
    public function actionGetActivityBaseInfo(){
        $base_info = ActivityLotteryBase::find()->where(['company_id'=>1])->asArray()->one();
        $base_info || $base_info = [];
        $this->_successData($base_info);
    }

    /**
     * 活动列表
     */
    public function actionActivityList(){
        $company_id = yii::$app->request->post('company_id', 0);
        $status     = yii::$app->request->post('status', 0);
        $title      = yii::$app->request->post('title', '');

        $page = yii::$app->request->post('page', 1);
        $size = yii::$app->request->post('size', 10);
        if(!$company_id) $this->_errorData('3000', '参数错误');

        $list = ActivityLottery::activityList($company_id, $status, $title, $page, $size);

        $this->_successData($list);
    }

    /**
     * 后台添加or编辑大转盘活动
     */
    public function actionAddActivity(){
        $company_id  = yii::$app->request->post('company_id', 0);
        $activity_id = yii::$app->request->post('activity_id', 0);
        $base_id     = yii::$app->request->post('base_id', 0);
        $title       = yii::$app->request->post('title', '');
        $cover_img   = yii::$app->request->post('cover_img', '');
        $security = yii::$app->request->post('security', 0);
        $end_time = yii::$app->request->post('end_time', '');
//        $prize_list = yii::$app->request->post('prize_list', '');
//        if($prize_list) $prize_list = json_decode($prize_list, true);
        if(!$base_id || !$company_id || !$title || !$end_time) $this->_errorData('1000', '参数错误');

        if($activity_id){
            $activity_info = ActivityLottery::find()->where(['activity_id'=>$activity_id])->asArray()->one();
            if($activity_info['status'] == $this->activity_open_status){
                /* 如果有人参与活动了 则不再允许编辑活动 */
                $user_played = ActivityLotteryRecord::find()->where(['activity_id'=>$activity_id])->count();
                if($user_played){
                    $this->_errorData('100021', '活动已经有人参与，不可以编辑活动了');
                }
            }
        }


        $edit_return = ActivityLottery::addActivity($company_id, $activity_id, $base_id, $title, $cover_img, $security, $this->activity_default_status, $end_time);
        if(!$edit_return) {
            $this->_errorData('10002', '编辑失败');
        }
        $this->_successData('编辑成功');
    }

    /**
     * 活动详情
     */
    public function actionActivityInfo(){
        $activity_id = yii::$app->request->post('activity_id', 0);
        if(!$activity_id) $this->_errorData('3000', '参数错误');

        $activity_info = ActivityLottery::find()
            ->alias('al')
            ->select(['al.*', 'alb.*'])
            ->leftJoin('vrshop.activity_lottery_base alb', 'al.base_id = alb.base_id')
            ->where(['activity_id'=>$activity_id])
            ->asArray()->one();

        $prize_list = ActivityLotteryPrize::find()->where(['activity_id'=>$activity_id])->asArray()->all();
        $activity_info['prize_list'] = $prize_list;

        $this->_successData($activity_info);
    }

    /**
     * 更改活动状态
     */
    public function actionChangeActivityStatus(){
        $company_id  = yii::$app->request->post('company_id', 0);
        $activity_id = yii::$app->request->post('activity_id', 0);
        $status = yii::$app->request->post('status', 1);
        if(!$activity_id || !$company_id) $this->_errorData('6000', '参数错误');
        if(!in_array($status, [1 ,2])) $this->_errorData('6002', '参数错误');

        // 活动只能开启一个
//        $opened = ActivityLottery::find()->select(['activity_id'])->where(['company_id'=>$company_id, 'status'=>$this->activity_open_status])->asArray()->one();
//        if($opened && $opened['activity_id'] != $activity_id){
//            $this->_errorData('6004', '只允许开启一个活动');
//        }



        $activity_info = ActivityLottery::findOne($activity_id);
        if(!$activity_info) $this->_errorData('6001', '活动id错误');

        // 活动是否关闭过  商品库存是否已经退还
        if ($activity_info->stock_return) {
            $this->_errorData('60010', '奖品信息已过期，请重新添加奖品。');
        }

        // 奖品数量
        $prize_count = ActivityLotteryPrize::find()->where(['activity_id'=>$activity_id])->count();
        if($prize_count != 8) $this->_errorData('6004', '奖品数量必须为八个');

        //关闭活动 归还商品库存
        if ($status == $this->activity_close_code) {
            $prize_list = ActivityLotteryPrize::find()->where(['activity_id'=>$activity_id])->andWhere('prize_type <> '.$this->activity_general_prize_code)->asArray()->all();           //奖品的商品列表
            foreach ($prize_list as $item) {
                if ($item['goods_id'] > 0) {
                    yii::$app->db->createCommand()->update('vrshop.goods', [
                        'goods_stock' => new yii\db\Expression("goods_stock + {$item['num']}"),
                    ], "goods_id = {$item['goods_id']}")->execute();

                    if ($item['goods_attr_id'] > 0) {
                        yii::$app->db->createCommand()->update('vrshop.goods_attribute_values', [
                            'stock' => new yii\db\Expression("stock + {$item['num']}"),
                        ], "goods_id = {$item['goods_id']} AND values_id = {$item['goods_attr_id']}")->execute();
                    }
                }
            }

            //更新活动表
            yii::$app->db->createCommand()->update('vrshop.activity_lottery', [
                'stock_return' => 1,
            ], "activity_id = {$activity_id}")->execute();
        }

        $activity_info->status = $status;
        $change_status_ret = $activity_info->save();
        if($change_status_ret) $this->_successData('修改状态成功');
        $this->_errorData('6003', '修改状态失败');
    }

    /**
     * 删除活动
     */
    public function actionDelActivity(){
        $activity_id = yii::$app->request->post('activity_id', 0);
        if(!$activity_id) $this->_errorData('6100', '参数错误');

        $activity_info = ActivityLottery::findOne($activity_id);
        if(!$activity_info || $activity_info->status == 1) $this->_errorData('6102', '请先关闭该活动');

        $ret = ActivityLottery::deleteAll(['activity_id'=>$activity_id]);
        if($ret) $this->_successData('删除成功');
        $this->_errorData('6101', '删除失败');
    }

    /**
     * 编辑/添加奖品
     */
    public function actionEditActivityPrize(){
        $prize_id      = yii::$app->request->post('prize_id', 0);
        $activity_id   = yii::$app->request->post('activity_id', 0);
        $activity_type = yii::$app->request->post('activity_type', false);   // 0：非游客  1：游客
        $prize_info    = yii::$app->request->post('prize_info', array());
        if ($activity_type === false || $prize_id === false || empty($prize_info)) $this->_errorData('6600','参数错误');

        $prize_info = json_decode($prize_info, true);
        if ($activity_type == $this->_activity_code) {
            $ret = ActivityLotteryPrize::addPrize($activity_id, $prize_id, $prize_info);
        } elseif ($activity_type == $this->_guest_activity_code){
            $ret = ActivityLotteryGuestsPrize::addPrize($prize_id, $prize_info);
        }

        if ($ret === true) {
            $this->_successData('操作成功');
        }
        $this->_errorData('6801', $ret);
    }

    /**
     * 奖品详情
     */
    public function actionPrizeInfo(){
        $prize_id      = yii::$app->request->post('prize_id', 0);
        $activity_type = yii::$app->request->post('activity_type', false);
        if (!$prize_id || $activity_type === false) $this->_errorData('6810', '参数错误');

        if ($activity_type == 0) {
            // 非游客
            $prize_info = ActivityLotteryPrize::prizeInfo($prize_id);
        } else {
            // 游客
            $prize_info = ActivityLotteryGuestsPrize::prizeInfo($prize_id);
        }

        $this->_successData($prize_info);
    }

    /**
     * 删除奖品
     */
    public function actionDelActivityPrize(){
        $prize_id      = yii::$app->request->post('prize_id', 0);
        $activity_type = yii::$app->request->post('activity_type', 0);   // 0：非游客  1：游客

        if ($activity_type == $this->_activity_code){
            $ret = ActivityLotteryPrize::delPrize($prize_id);
        } elseif ($activity_type == $this->_guest_activity_code) {
            $ret = ActivityLotteryGuestsPrize::delPrize($prize_id);
        }

        if ($ret === true) {
            $this->_successData('ok');
        }
        $this->_errorData('6800', $ret);
    }

    /**
     * 通用奖品列表
     */
    public function actionActivityGeneralList(){
        $company_id = yii::$app->request->post('company_id', 0);
        $prize_name = yii::$app->request->post('prize_name', '');
        $page = yii::$app->request->post('page', 1);
        $size = yii::$app->request->post('size', 10);

        $and_where = '1 = 1';
        if($company_id != 1) $and_where .= " AND is_show = 0";
        if($prize_name) $and_where .= " AND prize_name LIKE '%{$prize_name}%'";

        $offset = ($page - 1) * $size;
        $list = ActivityLotteryGeneralPrize::find()->where($and_where)->orderBy('create_time DESC')->offset($offset)->limit($size)->asArray()->all();
        $count = ActivityLotteryGeneralPrize::find()->where($and_where)->count();
        $list || $list = []; $count || $count = 0;

        $this->_successData(['list'=>$list, 'count'=>$count]);
    }

    /**
     * 通用奖品添加、编辑
     */
    public function actionActivityGeneralPrize(){
        $general_id = yii::$app->request->post('general_id', 0);
        $prize_name = yii::$app->request->post('prize_name', '');
        $prize_type = yii::$app->request->post('prize_type', 0);     // 0 是汇闻币  1 抽奖次数
        $prize_num  = yii::$app->request->post('prize_num', 0);      //汇闻币 或者 免费抽奖次数
        $prize_count = yii::$app->request->post('prize_count', 0);      //通用奖品数量
        $is_show    = yii::$app->request->post('is_show', 0);
        if(!$prize_name) $this->_errorData('7000', '奖品名称必须填写');

        if($general_id){
            $general_model = ActivityLotteryGeneralPrize::findOne($general_id);
            if(!$general_model) $this->_errorData('7002', '参数错误');
        }else{
            $general_model = new ActivityLotteryGeneralPrize();
            $general_model->create_time = time();
        }

        $general_model->prize_name = $prize_name;
        $general_model->prize_type = $prize_type;
        $general_model->prize_num  = $prize_num;
        $general_model->prize_count = $prize_count;
        $general_model->is_show    = $is_show;
        $ret = $general_model->save();
        if($ret) $this->_successData('编辑成功');
        $this->_errorData('7001', '编辑失败');
    }


    /**
     * 通用奖品详情
     */
    public function actionGeneralPrizeInfo(){
        $general_id = yii::$app->request->post('general_id', 0);
        if(!$general_id) $this->_errorData('8010', '参数错误');

        $prize_info = ActivityLotteryGeneralPrize::findOne($general_id)->toArray();
        $prize_info || $prize_info = [];
        $this->_successData($prize_info);
    }

    /**
     * 删除大转盘通用奖品

    public function actionDelGeneralPrize(){
        $general_id = yii::$app->request->post('general_id', 0);
        if(!$general_id) $this->_errorData('8000', '参数错误');

        $del_ret = ActivityLotteryGeneralPrize::deleteAll(['general_id'=>$general_id]);
        if($del_ret) $this->_successData('删除成功');
        $this->_errorData('8001', '删除失败');
    }
     */

    /**
     * 中奖列表
     */
    public function actionActivityPrizeList(){
        $page = yii::$app->request->post('page', 1);
        $size = yii::$app->request->post('size', 10);

        $activity_id = yii::$app->request->post('activity_id', 0);
        $name   = yii::$app->request->post('name', '');                   //中奖人
        $status = yii::$app->request->post('status', false);          //发货状态  0 未发货  1 已发货
        $start_time = yii::$app->request->post('start_time', 0);
        $end_time = yii::$app->request->post('end_time', 0);
        if(!$activity_id) $this->_errorData('11000', '参数错误');

        $and_where = '1 = 1';
        if($name) $and_where .= " AND u.nickname LIKE '%{$name}%'";
        if($status !== false) $and_where .= " AND alr.send_prize = {$status}";
        if($start_time){
            $start_time = strtotime($start_time);
            $and_where .= " AND alr.create_time > '{$start_time}'";
        }
        if($end_time){
            $end_time = strtotime($end_time);
            $and_where .= " AND alr.create_time < '{$end_time}'";
        }

        $prize_count = ActivityLotteryRecord::find()
            ->alias('alr')
            ->leftJoin('vruser1.user u', 'alr.user_id = u.user_id')
            ->where(['activity_id'=>$activity_id])
            ->andWhere(['<>', 'prize', '0'])
            ->andWhere($and_where)
            ->count();

        $offset = ($page - 1) * $size;
        $prize_list = ActivityLotteryRecord::find()
            ->alias('alr')
            ->select(['alr.*', 'u.nickname', 'u.mobile_phone', 'ua.prov', 'ua.city', 'ua.county', 'ua.address'])
            ->leftJoin('vruser1.user u', 'alr.user_id = u.user_id')
            ->leftJoin('vrshop.user_address ua', 'alr.address_id = ua.address_id')
            ->where(['alr.activity_id'=>$activity_id])
            ->andWhere(['<>', 'alr.prize', '0'])
            ->andWhere($and_where)
            ->orderBy('alr.create_time DESC')
            ->offset($offset)
            ->limit($size)
            ->asArray()
            ->all();
        $prize_list || $prize_list = [];

        $this->_successData(['count'=>$prize_count, 'list'=>$prize_list]);
    }

    /**
     * 中奖详情
     */
    public function actionActivityPrizeInfo(){
        $record_id = yii::$app->request->post('record_id', 0);
        if(!$record_id) $this->_errorData('12000', '参数错误');

        $prize_info = ActivityLotteryRecord::find()
            ->alias('alr')
            ->select(['alr.*', 'u.nickname', 'u.mobile_phone', 'ua.prov', 'ua.city', 'ua.county', 'ua.address'])
            ->leftJoin('vruser1.user u', 'alr.user_id = u.user_id')
            ->leftJoin('vrshop.user_address ua', 'alr.address_id = ua.address_id')
            ->where(['alr.record_id'=>$record_id])
            ->asArray()
            ->one();
        $prize_info || $prize_info = [];

        $prize_data_arr = ActivityLotteryPrize::find()->select(['prize_id', 'general_id', 'prize_id', 'prize_type', 'goods_id',])->where(['activity_id'=>$prize_info['activity_id']])->orderBy('percentage ASC')->asArray()->all();
        $prize_data = $prize_data_arr[intval($prize_info['prize'] - 1)];

        $goods_info = Goods::find()->select(['banner_image', 'goods_name'])->where(['goods_id'=>$prize_data['goods_id']])->asArray()->one();

        $prize_info['goods_info'] = $goods_info;
        $prize_info['prize_type'] = $prize_data['prize_type'];
        $this->_successData($prize_info);
    }

    /**
     * 发奖操作
     */
    public function actionActivitySendPrize(){
        $record_id = yii::$app->request->post('record_id', 0);
        if(!$record_id) $this->_errorData('13000', '参数错误');

        //中奖信息
        $prize_info = ActivityLotteryRecord::find()
            ->alias('alr')
            ->select(['alr.record_id', 'alr.activity_id', 'alr.user_id', 'alr.prize', 'alr.address_id', 'alr.send_prize'])
            ->where(['alr.record_id'=>$record_id])
            ->asArray()
            ->one();
        if($prize_info['send_prize'] >= 2) $this->_errorData('13003', '该奖项已发货');

        $prize_list = ActivityLotteryPrize::find()->select(['prize_id', 'general_id', 'prize_id', 'prize_type', 'goods_id',])->where(['activity_id'=>$prize_info['activity_id']])->orderBy('percentage ASC')->asArray()->all();

        //奖品信息
        $prize_data = $prize_list[intval($prize_info['prize'] - 1)];
        if($prize_data['general_id']) $this->_errorData('13004', '通用奖品不需要发货');

        if($prize_data['prize_type'] == 0){
            $express_company = yii::$app->request->post('express_company', '');
            $express_no = yii::$app->request->post('express_no', '');
            if(!$express_company || !$express_no) $this->_errorData('13001', '请填写快递公司和快递单号');

            if(!$prize_info['address_id']) $this->_errorData('13008', '用户未选择收货地址');

            //更新表
            $up_prize_record_ret = yii::$app->db->createCommand()->update('vrshop.activity_lottery_record', [
                'express_company' => $express_company,
                'express_no' => $express_no,
                'send_prize' => 2,
                'send_prize_time' => time(),
            ], "record_id = {$record_id} AND send_prize = 1")->execute();
            if($up_prize_record_ret) $this->_successData('发货成功');
            $this->_errorData('13002', '发货失败');
        }elseif($prize_data['prize_type'] == 1){
            /* 虚拟商品 */
            $goods_virtual_data = VirtualGoodsInfo::find()->where(['goods_id'=>$prize_data['goods_id'], 'is_sold'=>0])->orderBy('details_id ASC')->asArray()->one();
            if(!$goods_virtual_data) $this->_successData(['code'=>'13008', 'msg'=>'库存不足', 'goods_id'=>$prize_data['goods_id']]);

            $up_goods_virtual_ret = VirtualGoodsInfo::updateAll(['is_sold'=>1], "details_id = {$goods_virtual_data['details_id']}");
            if(!$up_goods_virtual_ret) $this->_successData(['code'=>'13100', 'msg'=>'发货失败', 'goods_id'=>$prize_data['goods_id']]);

            $up_prize_data = ['virtual_id'=>$goods_virtual_data['details_id'], 'send_prize'=>'2', 'send_prize_time'=>time()];
            $up_prize_record_ret = ActivityLotteryRecord::updateAll($up_prize_data, ['record_id'=>$prize_info['record_id']]);
            if (!$up_prize_record_ret) $this->_successData(['code'=>'13102', 'msg'=>'发货失败', 'goods_id'=>$prize_data['goods_id']]);

            $this->_successData(['code'=>'0000', 'msg'=>'发货成功']);
        }else{
            $this->_errorData('13005', '通用奖品不可发货');
        }
    }

    /**
     * 统计
     */
    public function actionActivityStatistics(){
        $activity_id = yii::$app->request->post('activity_id', 0);
        if(!$activity_id) $this->_errorData('14000', '参数错误');

        $prize_list = ActivityLotteryPrize::find()
            ->alias('alp')
            ->select(['alp.*', 'algp.*'])
            ->leftJoin('vrshop.activity_lottery_general_prize algp', 'alp.general_id = algp.general_id')
            ->where(['activity_id'=>$activity_id])
            ->orderBy('alp.percentage ASC')
            ->asArray()
            ->all();

        if(!$prize_list) $this->_errorData('14001', '参数错误');

        foreach ($prize_list as $key=>&$prize) {
            $prize['winning_count'] = ActivityLotteryRecord::find()->where(['activity_id'=>$activity_id, 'prize'=>intval($key + 1)])->count();     //中奖数量
            $prize['remaining_count'] = intval($prize['num'] + $prize['winning_count'] - $prize['winning_count']);   //剩余数量

            if($prize['general_id']){
                $general_prize_info = ActivityLotteryGeneralPrize::find()->where(['general_id'=>$prize['general_id']])->asArray()->one();
                if($general_prize_info['prize_type'] == 0){
                    $prize['winning_count_price'] =  $prize['winning_count'] * $general_prize_info['prize_num'];
                    $prize['remaining_count_price'] = $prize['remaining_count'] * $general_prize_info['prize_num'];
                    $prize['goods_price'] = $general_prize_info['prize_num'];
                }else{
                    $prize['winning_count_price'] =  0;
                    $prize['remaining_count_price'] = 0;
                    $prize['goods_price'] = '0';
                }
            }

            if($prize['goods_id']){
                $goods_price = Goods::find()->select(['goods_id', 'huiwenbi'])->where(['goods_id'=>$prize['goods_id']])->asArray()->one();
                if ($prize['goods_attr_id']) {
                    $goods_attr_info = GoodsAttributeValues::find()
                        ->select(['price'])
                        ->where(['values_id'=>$prize['goods_attr_id']])
                        ->asArray()->one();
                    $goods_price['huiwenbi'] = $goods_attr_info['price'];
                }
                $prize['remaining_count_price'] = $prize['remaining_count'] * $goods_price['huiwenbi'];
                $prize['winning_count_price']   = $prize['winning_count'] * $goods_price['huiwenbi'];
                $prize['goods_price'] = $goods_price['huiwenbi'];
            }
        }
        unset($prize);

        $winning_price = 0;
        $remaining_pirce = 0;
        foreach ($prize_list as $prize) {
             if($prize['winning_count_price']){
                 $winning_price += $prize['winning_count_price'];
             }
             if($prize['remaining_count_price']){
                 $remaining_pirce += $prize['remaining_count_price'];
             }
        }

        $prize_list['winning_price'] = $winning_price;
        $prize_list['remaining_pirce'] = $remaining_pirce;

        $activity_info = ActivityLottery::find()
            ->alias('al')
            ->select(['al.activity_id', 'al.base_id', 'alb.cost_huiwenbi'])
            ->innerJoin('vrshop.activity_lottery_base alb', 'al.base_id = alb.base_id')
            ->asArray()
            ->one();

        $prize_list['user_lottery_count'] = ActivityLotteryRecord::find()->where(['activity_id'=>$activity_id])->count();
        $prize_list['cost_huiwenbi'] = $activity_info['cost_huiwenbi'];
        $huiwenbi_sum_sql = "SELECT SUM(cost_huiwenbi) as huiwenbi_num FROM vrshop.activity_lottery_record WHERE play_type = {$this->_activity_pay_play_code} AND activity_id = {$activity_id}";
        $huiwenbi_sum_ret = yii::$app->db->createCommand($huiwenbi_sum_sql)->queryOne();
        $prize_list['huiwenbi_sum'] = $huiwenbi_sum_ret['huiwenbi_num'];
        $prize_list['profit'] = intval($prize_list['huiwenbi_sum'] - $prize_list['winning_price']);

        $this->_successData($prize_list);
    }


    /**
     * 未登录用户奖池
     * 添加奖品

    public function actionAddGuestsPrize()
    {
        $prize_list = yii::$app->request->post('prize_list', '');
        $prize_list = json_decode($prize_list, true);
//        if(!is_array($prize_list) || count($prize_list) != 8) $this->_errorData('1000', '参数错误');

        //先删除 再添加
        $del_ret = ActivityLotteryGuestsPrize::deleteAll();
        if($del_ret === false) $this->_errorData('删除原有数据失败，请重试');

        try {
            $trans = yii::$app->db->beginTransaction();
            $insert_data = [];
            foreach ($prize_list as $key => $prize) {
                $insert_data[$key]['goods_id'] = intval($prize['goods_id']);

                //减去属性库存
                if (!$prize['general_id']) {
                    if (!$prize['attr_id']) {
                        $goods_info = Goods::findOne($prize['goods_id'])->toArray();

                        if ($goods_info['goods_stock'] < $prize['num']) {
                            throw new \Exception("商品 [{$prize['info']}] 库存不足。".json_encode($prize));
                        }

                        $updata_goods_stock_ret = yii::$app->db->createCommand()->update('vrshop.goods', [
                            'goods_stock' => new yii\db\Expression("goods_stock - {$prize['num']}")
                        ], "goods_id = {$prize['goods_id']}")->execute();
                        if (!$updata_goods_stock_ret) throw new \Exception("更新商品 [{$prize['info']}] 库存失败，请重试。");

                        $insert_data[$key]['goods_attr_id'] = '0';
                    } else {
                        $goods_attr_info = GoodsAttributeValues::findOne($prize['attr_id']);
                        if (!$goods_attr_info) throw new \Exception('所选商品的属性错误');
                        if ($goods_attr_info->stock < $prize['num']) {
                            throw new \Exception("商品 [{$prize['info']}] 库存不足。");
                        }

                        // 减属性库存
                        $updata_goods_attr_stock_ret = yii::$app->db->createCommand()->update('vrshop.goods_attribute_values', [
                            'stock' => new yii\db\Expression("stock - {$prize['num']}")
                        ], "values_id = {$prize['attr_id']}")->execute();
                        if (!$updata_goods_attr_stock_ret) throw new \Exception("更新商品 [{$prize['info']}] 库存失败，请重试。");

                        $insert_data[$key]['goods_attr_id'] = $prize['attr_id'];
                    }
                } else {
                    $insert_data[$key]['goods_attr_id'] = '0';
                }

                $insert_data[$key]['cover_img'] = $prize['cover_img'];
                $insert_data[$key]['info'] = $prize['info'];
                $insert_data[$key]['num']  = $prize['num'];
                $insert_data[$key]['percentage'] = intval($prize['percentage']);
                $insert_data[$key]['instructions'] = $prize['instructions'];
                $insert_data[$key]['prize_type'] = intval($prize['prize_type']);
                $insert_data[$key]['general_id'] = intval($prize['general_id']);
                $insert_data[$key]['create_time'] = time();
            }

            $ret = yii::$app->db->createCommand()->batchInsert(
                'vrshop.activity_lottery_guests_prize',
                ['goods_id', 'goods_attr_id', 'cover_img', 'info', 'num', 'percentage', 'instructions', 'prize_type', 'general_id', 'create_time'],
                $insert_data
            )->execute();
            if(!$ret) throw new \Exception('保存活动失败');

            $trans->commit();
            $this->_successData('保存成功');
        } catch (\Exception $e) {
            $trans->rollBack();
            $this->_errorData('10003', $e->getMessage());
        }

        $this->_errorData('10001', '保存失败');
    }
     */
    /**
     * 后台 游客大转盘奖品列表
     */
    public function actionGuestPrizeDetail(){
        $sort_field  = yii::$app->request->post('sort_field', 'percentage');
        $sort_type   = yii::$app->request->post('sort_type', 'ASC');
        if (!in_array(strtolower($sort_type), ['asc', 'desc'])) $this->_errorData('6904', '排序方式错误');

        $prize_list = ActivityLotteryGuestsPrize::find()
            ->where('1')
            ->orderBy("{$sort_field} {$sort_type}")
            ->asArray()
            ->all();
        $this->_successData($prize_list);
    }

    /**
     * 后台 非游客
     * 大转盘奖品列表
     */
    public function actionPrizeList(){
        $activity_id = yii::$app->request->post('activity_id', 0);
        $sort_field  = yii::$app->request->post('sort_field', 'percentage');
        $sort_type   = yii::$app->request->post('sort_type', 'ASC');
        if (!$activity_id) $this->_errorData('6900', '参数错误');
        if (!in_array(strtolower($sort_type), ['asc', 'desc'])) $this->_errorData('6904', '排序方式错误');

//        $column_exist = yii::$app->getDb()->getSchema()->getTableSchema('activity_lottery_prize')->getColumn($sort_field);
//        if (!$column_exist) $this->_errorData('6903', '排序字段错误');

        $prize_list = ActivityLotteryPrize::find()
            ->where(['activity_id'=>$activity_id])
            ->orderBy("{$sort_field} {$sort_type}")
            ->asArray()
            ->all();
        $this->_successData($prize_list);
    }

    /**
     * 游客大转盘 编辑/删除奖品 退换库存

    public function actionChangeGuestPrize(){
        $prize_id   = yii::$app->request->post('prize_id', 0);
        $prize_info = ActivityLotteryGuestsPrize::findOne($prize_id);
        if (!$prize_info) $this->_errorData('6300', '没有该奖项');
        if ($prize_info['general_id']) $this->_successData('成功');

        try {
            if ($prize_info['goods_attr_id']) {
                // 增加属性库存
                $updata_goods_attr_stock_ret = yii::$app->db->createCommand()->update('vrshop.goods_attribute_values', [
                    'stock' => new yii\db\Expression("stock + {$prize_info['num']}")
                ], "values_id = {$prize_info['goods_attr_id']}")->execute();
                if (!$updata_goods_attr_stock_ret) {
                    throw new \Exception("更新商品ID【{$prize_info['goods_id']}】属性ID【{$prize_info['attr_id']}】库存失败，请重试。");
                }

            } else {
                $goods_info = Goods::findOne($prize_info['goods_id'])->toArray();

                $updata_goods_stock_ret = yii::$app->db->createCommand()->update('vrshop.goods', [
                    'goods_stock' => new yii\db\Expression("goods_stock + {$prize_info['num']}")
                ], "goods_id = {$prize_info['goods_id']}")->execute();
                if (!$updata_goods_stock_ret) {
                    throw new \Exception("更新商品ID【{$prize_info['goods_id']}】库存失败，请重试。");
                }
            }

            $this->_successData('成功');
        } catch (\Exception $e) {
            $this->_errorData('6301', $e->getMessage());
        }
    }
     */

    /**
     * 配置M站 广告跳转到大转盘活动
     */
    public function actionActivityConfig(){
        $status   = yii::$app->request->post('status', 0);
        $icon_url = yii::$app->request->post('icon_url', '');
        $jump_url = yii::$app->request->post('jump_url', '');
        $type     = yii::$app->request->post('type', 1);
        if(!$icon_url || !$jump_url) $this->_errorData('9090', '参数错误');

        $delete_ret = ActivityMAdConfig::deleteAll();
        if($delete_ret === false) $this->_errorData('9092', '添加失败');

        $insert_data = [
            'icon_url' => $icon_url,
            'jump_url' => $jump_url,
            'type'     => $type,
            'status'   => $status,
            'create_time' => time(),
        ];
        $insert_ret = yii::$app->db->createCommand()->insert('vrshop.activity_m_ad_config', $insert_data)->execute();
        if($insert_ret) $this->_successData('添加成功');
        $this->_errorData('9091', '添加失败');
    }

    /**
     * M站配置  状态修改
     */
    public function actionChangeActivityConfig(){
        $ad_id  = yii::$app->request->post('ad_id', 0);
        $status = yii::$app->request->post('status', 0);
        if(!$ad_id) $this->_errorData('9700', '参数错误');
        if(!in_array($status, [0, 1])) $this->_errorData('9702', '参错错误');

        $up_ret = yii::$app->db->createCommand()->update('vrshop.activity_m_ad_config', [
            'status' => $status,
        ], "ad_id = :ad_id")->bindValue(':ad_id', $ad_id)->execute();
        if($up_ret) $this->_successData('修改成功');
        $this->_errorData('9701', '修改失败');
    }

    /**
     * M站活动配置后台展示
     */
    public function actionActivityConfigDetail(){
        $config_detail = ActivityMAdConfig::find()->asArray()->one();
        $config_detail || $config_detail = [];
        $this->_successData($config_detail);
    }
}