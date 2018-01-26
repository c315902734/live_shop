<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vote".
 *
 * @property integer $vote_id
 * @property string $title
 * @property integer $vote_num
 * @property string $cover_image
 * @property string $abstract
 * @property integer $start_time
 * @property integer $end_time
 * @property string $host
 * @property integer $contractors
 * @property string $vote
 * @property integer $type
 * @property integer $create_time
 */
class Vote extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vote';
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
            [['title'], 'required'],
            [['vote_num', 'start_time', 'end_time', 'contractors', 'type', 'create_time'], 'integer'],
            [['title', 'cover_image', 'abstract', 'host', 'vote'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'vote_id' => 'Vote ID',
            'title' => 'Title',
            'vote_num' => 'Vote Num',
            'cover_image' => 'Cover Image',
            'abstract' => 'Abstract',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'host' => 'Host',
            'contractors' => 'Contractors',
            'vote' => 'Vote',
            'type' => 'Type',
            'create_time' => 'Create Time',
        ];
    }
}
