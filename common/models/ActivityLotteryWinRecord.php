<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "activity_lottery_win_record".
 *
 * @property string $record_id
 * @property string $activity_id
 * @property string $user_id
 * @property integer $num
 * @property integer $is_use
 * @property string $create_time
 */
class ActivityLotteryWinRecord extends \yii\db\ActiveRecord
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
        return 'activity_lottery_win_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['activity_id', 'user_id', 'num', 'is_use', 'create_time'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'record_id' => 'Record ID',
            'activity_id' => 'Activity ID',
            'user_id' => 'User ID',
            'num' => 'Num',
            'is_use' => 'Is Use',
            'create_time' => 'Create Time',
        ];
    }
}
