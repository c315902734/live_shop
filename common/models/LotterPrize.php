<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "lotter_prize".
 *
 * @property string $prize_id
 * @property string $activity_id
 * @property string $goods_id
 * @property string $cover_img
 * @property string $info
 * @property integer $num
 * @property string $percentage
 * @property integer $max_num
 * @property integer $minimum_huiwenbi
 * @property integer $is_show
 * @property string $instructions
 * @property integer $create_time
 */
class LotterPrize extends \yii\db\ActiveRecord
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
        return 'lotter_prize';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['activity_id', 'goods_id', 'num', 'max_num', 'minimum_huiwenbi', 'is_show', 'create_time'], 'integer'],
            [['percentage'], 'number'],
            [['instructions'], 'string'],
            [['cover_img'], 'string', 'max' => 200],
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
            'minimum_huiwenbi' => 'Minimum Huiwenbi',
            'is_show' => 'Is Show',
            'instructions' => 'Instructions',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 后台添加编辑大转盘奖品
     * @param $activity_id
     * @param $prize_id
     * @param $goods_id
     * @param $cover_img
     * @param $info
     * @param $num
     * @param $percentage
     * @param $max_num
     * @param $minimum_huiwenbi
     * @param $is_show
     * @param $instructions
     * @return bool
     */
    public static function addLotteryPrize($activity_id, $prize_id, $goods_id, $cover_img, $info, $num, $percentage, $max_num, $minimum_huiwenbi, $is_show, $instructions){
        if($prize_id){
            $prize_model = self::findOne($prize_id);
        }else{
            $prize_model = new self();
            $prize_model->create_time = time();
        }
        $prize_model->activity_id = $activity_id;
        $prize_model->goods_id = $goods_id;
        $prize_model->cover_img = $cover_img;
        $prize_model->info = $info;
        $prize_model->num = $num;
        $prize_model->percentage = $percentage;
        $prize_model->max_num = $max_num;
        $prize_model->minimum_huiwenbi = $minimum_huiwenbi;
        $prize_model->is_show = $is_show;
        $prize_model->instructions = $instructions;
        $prize_model->create_time = time();
        return $prize_model->save();
    }
}
