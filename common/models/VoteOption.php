<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vote_option".
 *
 * @property integer $option_id
 * @property string $vote_id
 * @property string $group_id
 * @property string $class_id
 * @property string $cover_image
 * @property string $name
 * @property string $abstract
 * @property integer $create_time
 */
class VoteOption extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vote_option';
    }

    public static function getDb(){
        return Yii::$app->vrnews1;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vote_id', 'group_id', 'class_id', 'create_time'], 'integer'],
            [['cover_image', 'abstract'], 'string', 'max' => 255],
            [['name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'option_id' => 'Option ID',
            'vote_id' => 'Vote ID',
            'group_id' => 'Group ID',
            'class_id' => 'Class ID',
            'cover_image' => 'Cover Image',
            'name' => 'Name',
            'abstract' => 'Abstract',
            'create_time' => 'Create Time',
        ];
    }
}
