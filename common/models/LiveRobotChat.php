<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_robot_chat".
 *
 * @property integer $id
 * @property string $name
 * @property string $photo
 * @property integer $type
 * @property string $target
 * @property string $content
 * @property integer $num
 * @property string $live_id
 * @property string $create_time
 */
class LiveRobotChat extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_robot_chat';
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
            [['type', 'num', 'live_id'], 'integer'],
            [['create_time'], 'safe'],
            [['name', 'target'], 'string', 'max' => 50],
            [['photo'], 'string', 'max' => 200],
            [['content'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'photo' => 'Photo',
            'type' => 'Type',
            'target' => 'Target',
            'content' => 'Content',
            'num' => 'Num',
            'live_id' => 'Live ID',
            'create_time' => 'Create Time',
        ];
    }
}
