<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_user_collect".
 *
 * @property string $collect_id
 * @property string $user_id
 * @property string $live_id
 * @property string $create_time
 * @property string $update_time
 * @property integer $status
 */
class LiveUserCollect extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_user_collect';
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
            'collect_id' => 'Collect ID',
            'user_id' => 'User ID',
            'live_id' => 'Live ID',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'status' => 'Status',
        ];
    }
}
