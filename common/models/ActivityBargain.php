<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "activity_bargain".
 *
 * @property string $activity_id
 * @property string $company_id
 * @property string $activity_name
 * @property string $huiwenbi
 * @property string $reserve_price
 * @property string $bargain_price
 * @property integer $pay_num
 * @property integer $pay_num_type
 * @property integer $free_num
 * @property integer $free_num_type
 * @property string $end_time
 * @property string $goods_id
 * @property integer $goods_type
 * @property string $virtual_goods_id
 * @property string $goods_img
 * @property string $prize_info
 * @property string $prize_desc
 * @property integer $status
 * @property integer $create_time
 */
class ActivityBargain extends \yii\db\ActiveRecord
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
        return 'activity_bargain';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'pay_num', 'pay_num_type', 'free_num', 'free_num_type', 'goods_id', 'goods_type', 'virtual_goods_id', 'status', 'create_time'], 'integer'],
            [['activity_name'], 'required'],
            [['huiwenbi', 'reserve_price', 'bargain_price'], 'number'],
            [['end_time'], 'safe'],
            [['activity_name', 'goods_img', 'prize_info', 'prize_desc'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'activity_id' => 'Activity ID',
            'company_id' => 'Company ID',
            'activity_name' => 'Activity Name',
            'huiwenbi' => 'Huiwenbi',
            'reserve_price' => 'Reserve Price',
            'bargain_price' => 'Bargain Price',
            'pay_num' => 'Pay Num',
            'pay_num_type' => 'Pay Num Type',
            'free_num' => 'Free Num',
            'free_num_type' => 'Free Num Type',
            'end_time' => 'End Time',
            'goods_id' => 'Goods ID',
            'goods_type' => 'Goods Type',
            'virtual_goods_id' => 'Virtual Goods ID',
            'goods_img' => 'Goods Img',
            'prize_info' => 'Prize Info',
            'prize_desc' => 'Prize Desc',
            'status' => 'Status',
            'create_time' => 'Create Time',
        ];
    }

    public static function getList($company_id, $activity_name, $status, $order, $page, $size){
        $where = '';

        if($activity_name){
            $where .= "activity_name LIKE '%{$activity_name}%'";
        }

        if($status){
            $where .= "status = {$status}";
        }

        $offset = ($page - 1) * $size;

        $list = self::find()
            ->where($where)
            ->offset($offset)
            ->limit($size)
            ->orderBy($order)
            ->asArray()
            ->all();
        $list || $list = [];

        $count = self::find()
            ->where($where)
            ->asArray()
            ->count();
        $count || $count = 0;

        return ['count'=>$count, 'list'=>$list];
    }
}
