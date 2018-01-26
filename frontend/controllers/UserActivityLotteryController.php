<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2017/7/12
 * Time: 11:06
 */

namespace frontend\controllers;

use common\models\ActivityLotteryGeneralPrize;
use common\models\ActivityLotteryGuestsPrize;
use common\models\ActivityLotteryPrize;
use common\models\ActivityLottery;
use common\models\ActivityLotteryRecord;
use common\models\ActivityLotteryWinRecord;
use common\models\Company;
use common\models\CompanyInfo;
use common\models\Goods;
use common\models\UserAddress;
use common\models\VirtualGoodsInfo;
use common\service\Record;
use yii;

class UserActivityLotteryController extends BaseApiController
{
    protected $general_prize_code;          //奖品表prize_type 通用奖品code
    protected $everyday_free_type_code;
    protected $everyday_limit_type_code;
    protected $general_prize_huiwenbi_code;
    protected $general_prize_num_code;
    protected $activity_opend_code;

    public function init()
    {
        parent::init();
        $this->general_prize_code = 2;
        $this->general_prize_huiwenbi_code = 0;
        $this->general_prize_num_code = 1;

        $this->everyday_free_type_code = 0;
        $this->everyday_limit_type_code = 0;
        $this->activity_opend_code = 1;
    }

    public function actionGetPrize(){
        $activity_id = yii::$app->request->post('activity_id', 0);
        if(!$activity_id) $this->_errorData('1000', '参数错误');

        $user_info = $this->_getUserModel(true);
        $activity_info = ActivityLottery::find()
            ->alias('al')
            ->select(['al.*', 'alb.*'])
            ->leftJoin('vrshop.activity_lottery_base alb', 'al.base_id = alb.base_id')
            ->where(['al.activity_id'=>$activity_id])
            ->asArray()->one();
        if(!$activity_info) $this->_errorData('1001', '活动ID错误');
        if($activity_info['status'] != $this->activity_opend_code) $this->_errorData('10021', '该活动已关闭');
        if(time() > strtotime($activity_info['end_time'])) $this->_errorData('10022', '该活动已经结束');

        /* 免费 */
        $used_free_num = 0; $win_free_num = 0;
        $todaty_start_time = strtotime(date('Y-m-d',time()));
        $today_end_time    = $todaty_start_time + 24 * 3600 - 1;

        /* 再来N次 */
        $win_free_num = ActivityLotteryWinRecord::find()->where(['activity_id'=>$activity_info['activity_id'], 'user_id'=>$user_info['user_id'], 'is_use'=>0])->count();

        if($activity_info['free_num_type'] == $this->everyday_free_type_code){
            /* 免费次数类型 每天 */
            $used_free_num = ActivityLotteryRecord::find()
                ->where(['activity_id'=>$activity_id, 'user_id'=>$user_info['user_id'], 'play_type'=>0])
                ->andWhere(['>', 'create_time', $todaty_start_time])
                ->andWhere(['<', 'create_time', $today_end_time])
                ->count();
        }else{
            /* 免费次数类型 永久 */
            $used_free_num = ActivityLotteryRecord::find()
                ->where(['activity_id'=>$activity_id, 'user_id'=>$user_info['user_id'], 'play_type'=>0])
                ->count();
        }
        if($used_free_num < $activity_info['free_num']){
            /* 还有免费次数 */
            $prize = [];
            $prize = $this->_getPrize($activity_info, $user_info, 0, 0);

            /* 计算剩余次数 */
            $prize['play_count'] = intval($activity_info['limit_num'] - $used_free_num + $win_free_num - 1);
            $this->_successData($prize);
        }
        /* 免费END */

        //计算汇闻币是否足够
        if($user_info['amount'] < $activity_info['cost_huiwenbi']) $this->_errorData('1002', '汇闻币不足，请充值');

        /* 付费 */
        if($activity_info['limit_num_type'] == $this->everyday_limit_type_code){
            $used_limit_num = ActivityLotteryRecord::find()
                ->where(['activity_id'=>$activity_id, 'user_id'=>$user_info['user_id'], 'play_type'=>1])
                ->andWhere(['>', 'create_time', $todaty_start_time])
                ->andWhere(['<', 'create_time', $today_end_time])
                ->count();
        }else{
            $used_limit_num = ActivityLotteryRecord::find()
                ->where(['activity_id'=>$activity_id, 'user_id'=>$user_info['user_id'], 'play_type'=>1])
                ->count();
        }

        // 有没有中过再来N次
        if($win_free_num){
            $prize = $this->_getPrize($activity_info, $user_info, 2, 1);
            $prize['play_count'] = intval($activity_info['limit_num'] - $used_limit_num - $used_free_num + $win_free_num - 1);
            $this->_successData($prize);
        }

        $pay_count = intval($activity_info['limit_num'] - $activity_info['free_num']);
        if($used_limit_num < $pay_count){
            /* 还有付费次数 */
            $prize = $this->_getPrize($activity_info, $user_info, 1, 0);
            $prize['play_count'] = intval($activity_info['limit_num'] - $activity_info['free_num'] - $used_limit_num - 1);
            $this->_successData($prize);
        }
        /* 付费 END */

        $this->_successData(['prize'=>0, 'prize_type'=>'', 'record_id'=>0, 'msg'=>'抽奖次数已用完']);
    }

    protected function _getPrize(array $activity_info, array $user_info, $type, $is_win){
        $prize_list = ActivityLotteryPrize::find()
            ->alias('alp')
            ->select(['alp.*', 'algp.prize_name', 'algp.prize_type as general_prize_type'])
            ->leftJoin('vrshop.activity_lottery_general_prize algp', 'alp.general_id = algp.general_id')
            ->where(['activity_id'=>$activity_info['activity_id']])
            ->orderBy('percentage ASC')
            ->asArray()
            ->all();
        if(!$prize_list) return ['prize'=>0, 'prize_type'=>'', 'record_id'=>0, 'msg'=>'奖品信息错误'];

        $prize_arr = [];
        foreach ($prize_list as $key=>$item) {
            $prize_arr[$key]['id']    = $key + 1;
            $prize_arr[$key]['prize_id'] = $item['prize_id'];
            $prize_arr[$key]['prize'] = $item['info'];
            $prize_arr[$key]['v']     = $item['percentage'];
            $prize_arr[$key]['num']   = $item['num'];
            $prize_arr[$key]['max_num']  = $item['max_num'];
        }

        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['v'];
        }
        //$rid中奖的序列号码
        $rid = ActivityLotteryPrize::_getRand($arr);     //根据概率获取奖项id
        $prize_info = $prize_list[$rid - 1];             //中奖项

        if($prize_info['prize_type'] != $this->general_prize_code && $prize_info['num'] <= 0 ) {
            //中的奖项 没库存
            $this->_add_not_winning_record($activity_info, $user_info, $type, $is_win);
            return ['prize'=>0, 'prize_type'=>'', 'record_id'=>0];
        }

        /* 抽奖限制  该等奖最多中奖次数 */
        if($prize_info['max_num']){
            $prized_before_num = ActivityLotteryRecord::find()->where(['activity_id'=>$activity_info['activity_id'], 'user_id'=>$user_info['user_id'], 'prize'=>$rid])->count();
            if($prized_before_num >= $prize_info['max_num']){
                $this->_add_not_winning_record($activity_info, $user_info, $type, $is_win);
                return ['prize'=>0, 'prize_type'=>'', 'record_id'=>0];
            }
        }

        //
        if($prize_info['prize_type'] == $this->general_prize_code){
            if($prize_info['general_prize_type'] == $this->general_prize_huiwenbi_code){
                $prize_info['prize_type'] = 2;                      //汇闻币
            }elseif($prize_info['general_prize_type'] == $this->general_prize_num_code){
                $prize_info['prize_type'] = 3;                      //抽奖次数
            }else{
                $this->_errorData('6700', '抽奖参数错误');
            }
        }

        $trans = yii::$app->db->beginTransaction();
        try {
            /* 添加抽奖记录 */
            $insert_record_ret = yii::$app->db->createCommand()->insert('vrshop.activity_lottery_record',[
                'activity_id' => $activity_info['activity_id'],
                'user_id'     => $user_info['user_id'],
                'play_type'   => $type,
                'cost_huiwenbi' => $activity_info['cost_huiwenbi'],
                'prize'       => $rid,
                'prize_name'  => $prize_info['info'],
                'prize_type'  => $prize_info['prize_type'],
                'prize_cover_img' => $prize_info['cover_img'],
                'create_time' => time(),
            ])->execute();
            if(!$insert_record_ret) throw new \Exception('添加抽奖记录失败');
            $record_id = yii::$app->db->getLastInsertId();

            /* 如果是付费次数 扣除汇闻币 */
            if ($type == 1) {
                $up_user_ret = yii::$app->db->createCommand()->update('vruser1.user', [
                    'amount' => new yii\db\Expression("amount - {$activity_info['cost_huiwenbi']}")
                ], "user_id = {$user_info['user_id']} AND amount >= 0")->execute();
                if(!$up_user_ret) throw new \Exception('汇闻币扣除失败');

                /* 添加汇闻币明细 */
                $add_huiwenbi_detail_data = [
                    'user_id'     => $user_info['user_id'],
                    'operate_cnt' => $activity_info['cost_huiwenbi'],
                    'surplus'     => intval($user_info['amount'] - $activity_info['cost_huiwenbi']),
                    'operate'     => 2,
                    'operate_name'=> '参加活动',
                    'created_at'  => date('Y-m-d H:i:s', time())
                ];
                yii::$app->db->createCommand()->insert('vruser1.user_amount', $add_huiwenbi_detail_data)->execute();
            }


            // 减去奖品数量 (除通用奖品外
            if (!$prize_info['general_id']) {
                $up_prize_num_ret = yii::$app->db->createCommand()->update('vrshop.activity_lottery_prize', [
                    'num' => new yii\db\Expression("num - 1")
                ], "prize_id = {$prize_info['prize_id']} AND activity_id = {$activity_info['activity_id']} AND num > 0 ")->execute();
                if(!$up_prize_num_ret) throw new \Exception('扣除奖品数量失败');
            }

            /* 如果使用的抽奖机会是中奖的 */
            if($is_win){
                $up_win_record_sql = "UPDATE vrshop.activity_lottery_win_record SET is_use = 1 WHERE activity_id = {$activity_info['activity_id']} AND user_id = {$user_info['user_id']} AND is_use = 0 ORDER BY create_time ASC LIMIT 1";
                yii::$app->db->createCommand($up_win_record_sql)->execute();
            }

            $trans->commit();
            return ['prize'=>$rid, 'prize_type'=>$prize_info['prize_type'], 'record_id'=>$record_id];
        } catch (\Exception $e) {
            $trans->rollBack();
            return ['msg'=>$e->getMessage()];
        }
        return '请求错误';
    }

    /**
     * 增加未中奖记录
     */
    private function _add_not_winning_record($activity_info, $user_info, $type, $is_win){
        yii::$app->db->createCommand()->insert('vrshop.activity_lottery_record',[
            'activity_id' => $activity_info['activity_id'],
            'user_id'     => $user_info['user_id'],
            'play_type'   => $type,
            'cost_huiwenbi' => $activity_info['cost_huiwenbi'],
            'prize'       => 0,
            'create_time' => time(),
        ])->execute();

        /* 扣汇闻币 */
        yii::$app->db->createCommand()->update('vruser1.user', [
            'amount' => new yii\db\Expression("amount - {$activity_info['cost_huiwenbi']}")
        ], "user_id = {$user_info['user_id']} AND amount >= 0")->execute();

        /* 添加汇闻币明细 */
        $add_huiwenbi_detail_data = [
            'user_id' => $user_info['user_id'],
            'operate_cnt' => $activity_info['cost_huiwenbi'],
            'surplus' => intval($user_info['amount'] - $activity_info['cost_huiwenbi']),
            'operate' => 2,
            'operate_name' => '参加活动',
            'created_at' => date('Y-m-d H:i:s', time())
        ];
        yii::$app->db->createCommand()->insert('vruser1.user_amount', $add_huiwenbi_detail_data)->execute();

        /* 如果使用的抽奖机会是中奖的 */
        if($is_win){
            $up_win_record_sql = "UPDATE vrshop.activity_lottery_win_record SET is_use = 1 WHERE activity_id = {$activity_info['activity_id']} AND user_id = {$user_info['user_id']} AND is_use = 0 ORDER BY create_time ASC LIMIT 1";
            yii::$app->db->createCommand($up_win_record_sql)->execute();
        }
    }

    /**
     * 立即领取
     */
    public function actionReceivePrizes(){
        $activity_id = yii::$app->request->post('activity_id', 0);
        $record_id   = yii::$app->request->post('record_id', 0);
        if(!$record_id) $this->_errorData('6000', '参数错误');

        $user_info = $this->_getUserModel(true);
        if(!$user_info) $this->_errorData('6001', '用户信息错误');

        $user_prize = ActivityLotteryRecord::find()
            ->where(['user_id'=>$user_info['user_id'], 'activity_id'=>$activity_id, 'record_id'=>$record_id, 'send_prize'=>'0'])
            ->asArray()->one();
        if(!$user_prize) $this->_errorData('6002', '您已领取该奖品');

        if(empty($user_prize['session_id'])){
            $prize_list = ActivityLotteryPrize::find()
                ->alias('alp')
                ->select(['alp.*', 'algp.*'])
                ->leftJoin('vrshop.activity_lottery_general_prize algp', 'alp.general_id = algp.general_id')
                ->where(['activity_id'=>$activity_id])
                ->orderBy('percentage ASC')
                ->asArray()->all();
        }else{
            $prize_list = ActivityLotteryGuestsPrize::find()
                ->alias('alp')
                ->select(['alp.*', 'algp.*'])
                ->leftJoin('vrshop.activity_lottery_general_prize algp', 'alp.general_id = algp.general_id')
                ->orderBy('percentage ASC')
                ->asArray()->all();
        }

        if(!$prize_list) $this->_errorData('6003', '奖品信息错误');

        $prize_info = $prize_list[intval($user_prize['prize'] - 1)];
        if(!$prize_info) $this->_errorData('6004', '奖品信息错误');

        if($prize_info['general_id']){
            /* 中了 通用奖品 */
            if($prize_info['prize_type'] == $this->general_prize_num_code){
                /* 奖品是抽奖次数 */
                $add_play_num_arr = [];
                for ($i = 0; $i < $prize_info['prize_num']; ++$i){
                    $add_play_num_arr[$i]['activity_id'] = $activity_id;
                    $add_play_num_arr[$i]['user_id']     = $user_info['user_id'];
                    $add_play_num_arr[$i]['num']         = 1;
                    $add_play_num_arr[$i]['is_use']      = 0;
                    $add_play_num_arr[$i]['create_time'] = time();
                }
                $ret = yii::$app->db->createCommand()->batchInsert('vrshop.activity_lottery_win_record', ['activity_id', 'user_id', 'num', 'is_use', 'create_time'], $add_play_num_arr)->execute();

                /* 中奖状态置为已确认收奖 */
                $up_prize_record_ret = yii::$app->db->createCommand()->update('vrshop.activity_lottery_record', [
                    'send_prize' => '3',
                    'send_prize_time' => time(),
                ], "record_id = {$user_prize['record_id']}")->execute();
                if(!$up_prize_record_ret) $this->_errorData('6111', '抽奖状态更改失败');

                if($ret) $this->_successData(['code'=>1, 'msg'=>'已增加抽奖次数']);
                $this->_errorData('6006', '添加抽奖次数失败');
            }elseif($prize_info['prize_type'] == $this->general_prize_huiwenbi_code){
                /* 中了 汇闻币 */
                $trans = yii::$app->db->beginTransaction();

                try {
                    /* 增加汇闻币 */
                    $add_amount_ret = yii::$app->db->createCommand()->update('vruser1.user', [
                        'amount' => new yii\db\Expression("amount + {$prize_info['prize_num']}")
                    ], "user_id = {$user_info['user_id']}")->execute();
                    if (!$add_amount_ret) throw new \Exception('添加汇闻币失败');

                    /* cms金池扣除对应的汇闻币 */
                    $hwb_pool_balance = yii::$app->db->createCommand('SELECT SUM(huiwenbi) as balance FROM vradmin1.hwb_pool WHERE pool_type = 1')->queryScalar();
                    $hwb_pool_arr = [
                        'operator'    => '系统',
                        'pool_type'        => '1',
                        'type'        => '2',
                        'huiwenbi'    => intval('-'.$prize_info['prize_num']),
                        'balance'     => intval($hwb_pool_balance - $prize_info['prize_num']),
                        'create_time' => date('Y-m-d H:i:s', time()),
                        'remarks'     => "{$user_info['nickname']}参与活动ID：{$activity_id}抽中",
                    ];
                    $insert_hwb_pool_ret = yii::$app->db->createCommand()->insert('vradmin1.hwb_pool', $hwb_pool_arr)->execute();
                    if(!$insert_hwb_pool_ret) throw new \Exception('扣除资金池汇闻币失败');

                    /* 添加汇闻币明细 */
                    $add_huiwenbi_detail_data = [
                        'user_id' => $user_info['user_id'],
                        'operate_cnt' => $prize_info['prize_num'],
                        'surplus' => intval($user_info['amount'] + $prize_info['prize_num']),
                        'operate' => 1,
                        'operate_name' => '活动中奖',
                        'created_at' => date('Y-m-d H:i:s', time())
                    ];
                    $add_huiwenbi_detail_ret = yii::$app->db->createCommand()->insert('vruser1.user_amount', $add_huiwenbi_detail_data)->execute();
                    if(!$add_huiwenbi_detail_ret) throw new \Exception('添加汇闻币明细失败');

                    /* 中奖状态置为已发奖 */
                    $up_prize_record_ret = yii::$app->db->createCommand()->update('vrshop.activity_lottery_record', [
                        'send_prize' => '3',
                        'send_prize_time' => time(),
                    ], "record_id = {$user_prize['record_id']}")->execute();
                    if(!$up_prize_record_ret) throw new \Exception('抽奖状态更改失败');

                    $trans->commit();
                    $this->_successData(['code'=>2, 'msg'=>'汇闻币已存入账户']);
                } catch (\Exception $e){
                    $trans->rollBack();
                    $this->_errorData('6011', $e->getMessage());
                }
            }
        }else{
            /* 中奖状态置为已领奖
            $up_prize_record_ret = yii::$app->db->createCommand()->update('vrshop.activity_lottery_record', [
                'send_prize' => '1',
                'send_prize_time' => time(),
            ], "record_id = {$record_id}")->execute();
            if (!$up_prize_record_ret) $this->_errorData('6600', '更新状态失败');
            */

            $this->_successData(['code'=>3, 'msg'=>'']);
        }
        $this->_errorData('6100', '奖品有误');
    }

    /**
     * 虚拟实体商品 添加收货地址
     */
    public function actionAddPrizeDeliveryAddr(){
        $record_id   = yii::$app->request->post('record_id', 0);
        $address_id  = yii::$app->request->post('address_id', 0);
        if(!$record_id || !$address_id) $this->_errorData('7000', '参数错误');

        $user_info = $this->_getUserModel(true);

        $record_info = ActivityLotteryRecord::findOne($record_id);
        if(!$record_info) $this->_errorData('7001', '找不到该奖项');
        if($record_info->user_id != $user_info['user_id']) $this->_errorData('7004', '本人才可以领奖');
        if($record_info->address_id) $this->_errorData('7003', '已添加收货地址');

        $record_info->address_id = $address_id;
        $record_info->send_prize = 1;
        $save_addr_ret = $record_info->save();

        if($save_addr_ret) $this->_successData('添加收货地址成功');
        $this->_errorData('7002', '添加收货地址失败');
    }

    /**
     * 中奖纪录
     */
    public function actionPrizeList(){
        $page = yii::$app->request->post('page', 1);
        $size = yii::$app->request->post('size', 10);
        $status = yii::$app->request->post('status', false);

        $user_info = $this->_getUserModel(true);

        $and_where = '';
        if($status !== false) {
            $and_where = " send_prize = {$status}";
        }
        if ($status != 0) {
            $and_where .= " AND prize_type < {$this->general_prize_code}";
        }

        $prize_count = ActivityLotteryRecord::find()->where(['user_id'=>$user_info['user_id']])->andWhere(['<>', 'prize', '0'])->andWhere($and_where)->count();

        $offset = ($page - 1) * $size;
        $prize_list  = ActivityLotteryRecord::find()
            ->select(['record_id', 'activity_id', 'prize', 'prize_cover_img', 'prize_name', 'prize_type', 'express_company', 'express_no', 'address_id', 'send_prize'])
            ->where("user_id = '{$user_info['user_id']}' AND prize <> 0")
            ->andWhere($and_where)
            ->orderBy('create_time DESC')
            ->offset($offset)->limit($size)
            ->asArray()->all();

        $prize_list || $prize_list = [];

        $this->_successData(['count'=>$prize_count, 'list'=>$prize_list]);
    }

    /**
     * 中奖详情
     */
    public function actionPrizeInfo(){
        $record_id = yii::$app->request->post('record_id', 0);
        if(!$record_id) $this->_errorData('8000', '参数错误');

        $record_info = ActivityLotteryRecord::find()
            ->select(['alr.record_id', 'alr.activity_id', 'alr.prize', 'alr.prize_name', 'alr.prize_cover_img', 'alr.prize_type', 'alr.address_id', 'alr.send_prize', 'alr.express_company', 'alr.express_no', 'alr.virtual_id', 'alr.create_time'])
            ->alias('alr')
            ->where(['alr.record_id'=>$record_id])
            ->asArray()->one();

        if(!$record_info) $this->_errorData('8001', '参数错误');

        $prize_list = ActivityLotteryPrize::find()
            ->select(['activity_id', 'goods_id', 'goods_attr_id', 'instructions'])
            ->where(['activity_id'=>$record_info['activity_id']])
            ->orderBy('percentage ASC')
            ->asArray()->all();
        $prize_info = $prize_list[intval($record_info['prize'] - 1)];
        if(!$prize_info) $this->_errorData('8001', '奖项错误');
        $record_info['instructions'] = $prize_info['instructions'];

        //公司信息
        $activity_lottery_info = ActivityLottery::find()
            ->select(['company_id'])
            ->where(['activity_id'=>$record_info['activity_id']])
            ->asArray()->one();
        if (isset($activity_lottery_info['company_id']) && $activity_lottery_info['company_id'] > 0) {
            $record_info['company_info'] = CompanyInfo::companyInfo($activity_lottery_info['company_id']);
        }

        //奖品所关联奖品信息
        if ($prize_info['goods_id']) {
            if ($prize_info['goods_attr_id']) {
                $record_info['goods_info'] = Goods::find()
                    ->alias('g')
                    ->select(['g.goods_name', 'g.banner_image', 'g.abstract', 'ga.attribute_name', 'gav.values_content'])
                    ->innerJoin('vrshop.goods_attribute_values gav', 'g.goods_id = gav.goods_id')
                    ->innerJoin('vrshop.goods_attribute ga', 'gav.attribute_id = ga.attribute_id')
                    ->where(['g.goods_id'=>$prize_info['goods_id'], 'gav.values_id'=>$prize_info['goods_attr_id']])
                    ->asArray()
                    ->one();
            } else {
                $record_info['goods_info'] = Goods::find()->select(['goods_name', 'tags', 'abstract', 'art_no', 'video_url', 'huiwenbi', 'rmb_price', 'brand_name', 'banner_image', 'goods_introduce'])->where(['goods_id'=>$prize_info['goods_id']])->asArray()->one();
            }
        }

        if ($record_info['address_id'] > 0) {
            $record_info['address_info'] = UserAddress::find()->select("consignee, phone, prov, city, county, address, zipcode")->where(['address_id'=>$record_info['address_id']])->asArray()->one();
        } else {
            $record_info['address_info'] = [];
        }

        if($record_info['virtual_id'] != 0){
            $record_info['virtual_info'] = VirtualGoodsInfo::find()->select(['serial_number', 'password', 'deadline'])->where(['details_id'=>$record_info['virtual_id']])->asArray()->one();
        }

        $this->_successData($record_info);
    }

    /**
     * 确认收货（奖品
     */
    public function actionConfirmReceipt(){
        $activity_id = yii::$app->request->post('activity_id', 0);
        $record_id = yii::$app->request->post('record_id', 0);
        if(!$activity_id || !$record_id) $this->_errorData('14000', '参数错误');

        $user_info = $this->_getUserModel(true);

        $record_info = ActivityLotteryRecord::findOne($record_id);
        if($record_info->send_prize != 2) $this->_errorData('14001', '奖品状态错误');
        if($record_info->user_id != $user_info['user_id']) $this->_errorData('14001', '必须本人才能确认收货');

        $record_info->send_prize = 3;
        $save_ret = $record_info->save();
        if($save_ret) $this->_successData('确认收货成功');
        $this->_errorData('14002', '确认收货失败');
    }

    /**
     * 未登录用户抽到奖
     * session与user绑定
     */
    public function actionBindUserId(){
        yii::$app->session;
        $session_id = yii::$app->request->post('sid', '');                //md5过后的session
        $user_info  = $this->_getUserModel(true);
        if(!$session_id) $this->_errorData('1000', '参数错误');

        //是否有绑定记录
        $bind_record =  ActivityLotteryRecord::find()->where(['session_id'=>$session_id, 'user_id'=>$user_info['user_id'], 'activity_id'=>'0'])->orderBy('create_time DESC')->asArray()->one();
        if($bind_record) $this->_errorData('1002', '每位用户只能领取一次');

        //查找记录 修改record表 进行绑定
        $record_info = ActivityLotteryRecord::find()->where(['session_id'=>$session_id, 'user_id'=>'0', 'activity_id'=>'0', 'send_prize'=>'0'])->orderBy('create_time DESC')->asArray()->one();
        if(!$record_info) $this->_errorData('1001', '未匹配到抽奖记录');

        $up_record_ret = yii::$app->db->createCommand()
            ->update(
                'vrshop.activity_lottery_record',
                ['user_id' => $user_info['user_id']],
                "record_id = :record_id AND session_id = :session_id AND user_id = 0 AND activity_id = 0 AND send_prize = 0"
            )
            ->bindValue(':session_id', $session_id)
            ->bindValue(':record_id', $record_info['record_id'])->execute();

        if($up_record_ret) $this->_successData('绑定成功');

        $this->_errorData('绑定失败');
    }
}