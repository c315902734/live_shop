<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_robot_chat_answer".
 *
 * @property integer $id
 * @property string $name
 * @property string $photo
 * @property string $target
 * @property string $content
 * @property integer $num
 * @property string $question_id
 * @property string $create_time
 */
class LiveRobotChatAnswer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_robot_chat_answer';
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
            [['num', 'question_id'], 'integer'],
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
            'target' => 'Target',
            'content' => 'Content',
            'num' => 'Num',
            'question_id' => 'Question ID',
            'create_time' => 'Create Time',
        ];
    }
}
