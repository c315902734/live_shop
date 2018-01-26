<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vote_class".
 *
 * @property integer $class_id
 * @property string $vote_id
 * @property string $parent_id
 * @property string $class_name
 * @property integer $create_time
 */
class VoteClass extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vote_class';
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
            [['vote_id', 'parent_id', 'create_time'], 'integer'],
            [['class_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'class_id' => 'Class ID',
            'vote_id' => 'Vote ID',
            'parent_id' => 'Parent ID',
            'class_name' => 'Class Name',
            'create_time' => 'Create Time',
        ];
    }
}
