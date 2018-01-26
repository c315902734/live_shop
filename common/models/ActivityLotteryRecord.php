<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "activity_lottery_record".
 *
 * @property string $record_id
 * @property string $activity_id
 * @property string $session_id
 * @property string $user_id
 * @property integer $play_type
 * @property string $cost_huiwenbi
 * @property integer $prize
 * @property string $prize_cover_img
 * @property string $prize_name
 * @property integer $prize_type
 * @property string $address_id
 * @property string $express_company
 * @property string $express_no
 * @property string $virtual_id
 * @property integer $send_prize
 * @property string $send_prize_time
 * @property string $create_time
 */
class ActivityLotteryRecord extends \yii\db\ActiveRecord
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
        return 'activity_lottery_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['activity_id', 'user_id', 'play_type', 'prize', 'prize_type', 'address_id', 'virtual_id', 'send_prize', 'send_prize_time', 'create_time'], 'integer'],
            [['cost_huiwenbi'], 'number'],
            [['session_id', 'prize_name'], 'string', 'max' => 200],
            [['prize_cover_img'], 'string', 'max' => 150],
            [['express_company', 'express_no'], 'string', 'max' => 100],
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
            'session_id' => 'Session ID',
            'user_id' => 'User ID',
            'play_type' => 'Play Type',
            'cost_huiwenbi' => 'Cost Huiwenbi',
            'prize' => 'Prize',
            'prize_cover_img' => 'Prize Cover Img',
            'prize_name' => 'Prize Name',
            'prize_type' => 'Prize Type',
            'address_id' => 'Address ID',
            'express_company' => 'Express Company',
            'express_no' => 'Express No',
            'virtual_id' => 'Virtual ID',
            'send_prize' => 'Send Prize',
            'send_prize_time' => 'Send Prize Time',
            'create_time' => 'Create Time',
        ];
    }
}
