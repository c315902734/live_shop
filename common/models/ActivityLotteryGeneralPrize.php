<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "activity_lottery_general_prize".
 *
 * @property string $general_id
 * @property string $prize_name
 * @property integer $prize_type
 * @property integer $prize_num
 * @property integer $is_show
 * @property integer $create_time
 */
class ActivityLotteryGeneralPrize extends \yii\db\ActiveRecord
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
        return 'activity_lottery_general_prize';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['prize_type', 'prize_num', 'is_show', 'create_time'], 'integer'],
            [['prize_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'general_id' => 'General ID',
            'prize_name' => 'Prize Name',
            'prize_type' => 'Prize Type',
            'prize_num' => 'Prize Num',
            'is_show' => 'Is Show',
            'create_time' => 'Create Time',
        ];
    }
}
