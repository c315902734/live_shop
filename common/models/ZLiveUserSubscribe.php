<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_user_subscribe".
 *
 * @property string $subscribe_id
 * @property string $user_id
 * @property string $live_id
 * @property string $create_time
 * @property string $update_time
 * @property integer $status
 */
class ZLiveUserSubscribe extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'z_live_user_subscribe';
    }
    
    public static function getDb()
    {
        return Yii::$app->vrlive;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'live_id', 'status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'subscribe_id' => 'Subscribe ID',
            'user_id' => 'User ID',
            'live_id' => 'Live ID',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'status' => 'Status',
        ];
    }

    
}
