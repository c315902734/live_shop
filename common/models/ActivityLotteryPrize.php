<?php

namespace common\models;

use Yii;
use yii\db\Expression;
use yii\helpers\VarDumper;

/**
 * This is the model class for table "activity_lottery_prize".
 *
 * @property string $prize_id
 * @property string $activity_id
 * @property string $goods_id
 * @property string $cover_img
 * @property string $info
 * @property integer $num
 * @property integer $percentage
 * @property integer $max_num
 * @property string $instructions
 * @property integer $prize_type
 * @property string $general_id
 * @property integer $is_show
 * @property string $hidename
 * @property string $hidepic
 * @property string $hidehuiwenbi
 * @property string $create_time
 */
class ActivityLotteryPrize extends \yii\db\ActiveRecord
{
    public static function getDb()
    {
        return yii::$app->vrshop;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity_lottery_prize';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['activity_id', 'goods_id', 'num', 'percentage', 'max_num', 'prize_type', 'general_id', 'is_show', 'create_time'], 'integer'],
            [['cover_img', 'info', 'instructions', 'hidename', 'hidepic'], 'required'],
            [['instructions'], 'string'],
            [['hidehuiwenbi'], 'number'],
            [['cover_img', 'hidename', 'hidepic'], 'string', 'max' => 200],
            [['info'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'prize_id' => 'Prize ID',
            'activity_id' => 'Activity ID',
            'goods_id' => 'Goods ID',
            'cover_img' => 'Cover Img',
            'info' => 'Info',
            'num' => 'Num',
            'percentage' => 'Percentage',
            'max_num' => 'Max Num',
            'instructions' => 'Instructions',
            'prize_type' => 'Prize Type',
            'general_id' => 'General ID',
            'is_show' => 'Is Show',
            'hidename' => 'Hidename',
            'hidepic' => 'Hidepic',
            'hidehuiwenbi' => 'Hidehuiwenbi',
            'create_time' => 'Create Time',
        ];
    }

    public static function _getRand($proArr){
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset($proArr);
        return $result;
    }

    public static function delPrize($prize_id = 0){
        // 奖项信息
        $prize_info = self::findOne($prize_id);
        if (empty($prize_info)) return '奖项不存在';

        // 有人参加了活动则不能编辑和删除
        $is_participate = ActivityLottery::isParticipateActivities($prize_info->activity_id);
        if ($is_participate == false) {
            return '操作失败，该活动已经有人参与了';
        }

        if ($prize_info['general_id']) {
            // 通用奖品
            $del_guest_prize_ret = ActivityLotteryPrize::deleteAll(['prize_id'=>$prize_id]);
            if ($del_guest_prize_ret) {
                return true;
            }
            return '删除通用奖品错误';
        } else {
            // 实体虚拟奖品
            try {
                if ($prize_info->num > 0) {
                    $goods_info = Goods::findOne($prize_info->goods_id);
                    if (!$goods_info) throw new \Exception('没有该商品');

                    $updata_goods_stock_ret = yii::$app->db->createCommand()->update('vrshop.goods', [
                        'goods_stock' => new Expression("goods_stock + {$prize_info->num}")
                    ], "goods_id = {$prize_info->goods_id}")->execute();
                    if (!$updata_goods_stock_ret) {
                        throw new \Exception("更新商品[{$prize_info->info}] 库存失败，请重试");
                    }

                    if ($prize_info->goods_attr_id) {
                        // 增加属性库存
                        $updata_goods_attr_stock_ret = yii::$app->db->createCommand()->update('vrshop.goods_attribute_values', [
                            'stock' => new Expression("stock + {$prize_info->num}"),
                        ], "values_id = {$prize_info->goods_attr_id}")->execute();

                        if (!$updata_goods_attr_stock_ret) {
                            throw new \Exception("更新商品 [{$prize_info->info}] 库存失败，请重试");
                        }
                    }
                }

                // 删除奖项信息
                $del_prize_ret = self::deleteAll(['prize_id' => $prize_id]);
                if (!$del_prize_ret) throw new \Exception('归还库存成功，删除奖项失败');

                return true;
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }

    /**
     * 后台 添加/编辑 奖品
     * @param int $activity_id
     * @param int $prize_id
     * @param array $prize_info
     * @return bool|string
     */
    public static function addPrize($activity_id = 0, $prize_id = 0, $prize_info = []){
        $activity_info = ActivityLottery::findOne($activity_id);
        if (!$activity_info) return '该活动不存在';

        //编辑  删除该奖项
        if ($prize_id) {
            $del_ret = self::delPrize($prize_id);
            if ($del_ret !== true) return $del_ret;
        }

        $prize_count = ActivityLotteryPrize::find()->where(['activity_id'=>$activity_id])->count();
        if ($prize_count >= 8) return '奖品数量已达到上限';

        // 概率重复
        /*if (!$prize_info['percentage']) return '请设置概率';
        $percentage_repeat = self::find()->where(['activity_id'=>$activity_id, 'percentage'=>$prize_info['percentage']])->count();
        if ($percentage_repeat) return '概率不能重复';*/

        $insert_data = [];
        $trans = yii::$app->db->beginTransaction();
        try {
            $insert_data['activity_id'] = $activity_id;
            $insert_data['goods_id'] = isset($prize_info['goods_id']) ? intval($prize_info['goods_id']) : '0';
            if (!$prize_info['general_id']) {
                if ($prize_info['num'] > 0) {
                    $goods_info = Goods::findOne($prize_info['goods_id'])->toArray();

                    if ($goods_info['goods_stock'] < $prize_info['num']) {
                        throw new \Exception("商品 [{$prize_info['info']}] 库存不足");
                    }

                    $updata_goods_stock_ret = yii::$app->db->createCommand()->update('vrshop.goods', [
                        'goods_stock' => new Expression("goods_stock - {$prize_info['num']}")
                    ], "goods_id = {$prize_info['goods_id']}")->execute();
                    if (!$updata_goods_stock_ret) throw new \Exception("更新商品 [{$prize_info['info']}]库存失败，请重试。");

                    if ($prize_info['attr_id']) {
                        $goods_attr_info = GoodsAttributeValues::findOne($prize_info['attr_id']);
                        if (!$goods_attr_info) throw new \Exception('所选商品属性错误');
                        if ($goods_attr_info->stock < $prize_info['num']) {
                            throw new \Exception("商品 [{$prize_info['info']}] 库存不足");
                        }

                        // 减属性库存
                        $updata_goods_attr_stock_ret = yii::$app->db->createCommand()->update('vrshop.goods_attribute_values', [
                            'stock' => new Expression("stock - {$prize_info['num']}")
                        ], "values_id = {$prize_info['attr_id']}")->execute();
                        if (!$updata_goods_attr_stock_ret) throw new \Exception("更新商品 [{$prize_info['info']}] 库存失败，请重试。");

                        $insert_data['goods_attr_id'] = $prize_info['attr_id'];
                    } else {
                        $insert_data['goods_attr_id'] = '0';
                    }
                } else {
                    return '剩余奖品必须大于零';
                }
            } else {
                $insert_data['goods_attr_id'] = '0';
            }
            $insert_data['cover_img'] = isset($prize_info['cover_img']) ? $prize_info['cover_img'] : '';
            $insert_data['info'] = isset($prize_info['info']) ? $prize_info['info'] : '';
            if ($prize_info['general_id']) {
                $general_info = ActivityLotteryGeneralPrize::findOne($prize_info['general_id'])->toArray();
                $insert_data['num'] = $general_info['prize_count'];
            } else {
                $insert_data['num'] = $prize_info['num'];
            }
            $insert_data['percentage']   = isset($prize_info['percentage']) ? intval($prize_info['percentage']) : '0';
            $insert_data['max_num']      = isset($prize_info['max_num']) ? intval($prize_info['max_num']) : '0';
            $insert_data['instructions'] = isset($prize_info['instructions']) ? $prize_info['instructions'] : '';
            $insert_data['prize_type']   = isset($prize_info['prize_type']) ? intval($prize_info['prize_type']) : '0';
            $insert_data['general_id']   = isset($prize_info['general_id']) ? intval($prize_info['general_id']) : '0';
            $insert_data['is_show']      = isset($prize_info['is_show']) ? intval($prize_info['is_show']) : '0';
            /*$insert_data['hidename']     = isset($prize_info['hidename']) ? $prize_info['hidename'] : '';
            $insert_data['hidepic']      = isset($prize_info['hidepic']) ? $prize_info['hidepic'] : '';
            $insert_data['hidehuiwenbi'] = isset($prize_info['hidehuiwenbi']) ? $prize_info['hidehuiwenbi'] : '0';*/
            $insert_data['create_time']  = time();

            $insert_prize_ret = yii::$app->db->createCommand()->insert(
                'vrshop.activity_lottery_prize',
                $insert_data
            )->execute();
            if (!$insert_prize_ret) throw new \Exception('插入数据库失败');

            $trans->commit();
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            return $e->getMessage();
        }
    }

    /**
     * 后台 奖品详情
     * @param int $prize_id
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function prizeInfo($prize_id = 0){
        $prize_info = self::find()
            ->alias('alp')
            ->select(['alp.*', 'gav.values_content'])
            ->leftJoin('vrshop.goods_attribute_values gav', 'alp.goods_id = gav.goods_id AND alp.goods_attr_id = gav.values_id')
            ->where(['alp.prize_id'=>$prize_id])
            ->asArray()
            ->one();

        $prize_info['goods_name'] = '';
        $prize_info['goods_banner_image'] = '';
        $prize_info['goods_huiwenbi'] = '0';
        if (isset($prize_info['goods_id'])) {
            $goods_info = Goods::find()
                ->alias('g')
                ->select("g.goods_name, g.abstract, g.banner_image, g.huiwenbi")
                ->where(['g.goods_id'=>$prize_info['goods_id']])
                ->asArray()->one();
            $prize_info['goods_name'] = $goods_info['goods_name'];
            $prize_info['goods_banner_image'] = $goods_info['banner_image'];
            $prize_info['goods_huiwenbi'] = $goods_info['huiwenbi'];
        }

        $prize_info['general_name'] = '';
        $prize_info['general_type'] = '';
        if (isset($prize_info['general_id']) && $prize_info['general_id'] != 0) {
            $general_info = ActivityLotteryGeneralPrize::find()
                ->select("prize_name, prize_type, prize_num")
                ->where(['general_id'=>$prize_info['general_id']])->asArray()->one();

            $prize_info['general_name'] = $general_info['prize_name'];
            $prize_info['general_type'] = $general_info['prize_type'];
        }

        return $prize_info;
    }
}
