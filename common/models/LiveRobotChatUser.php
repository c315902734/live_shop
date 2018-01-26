<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_robot_chat_user".
 *
 * @property integer $id
 * @property string $username
 * @property string $avatar
 * @property integer $status
 * @property string $create_time
 */
class LiveRobotChatUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_robot_chat_user';
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
            [['status'], 'integer'],
            [['create_time'], 'safe'],
            [['username'], 'string', 'max' => 50],
            [['avatar'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'avatar' => 'Avatar',
            'status' => 'Status',
            'create_time' => 'Create Time',
        ];
    }
}
