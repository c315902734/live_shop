<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "gold".
 *
 * @property integer $id
 * @property string $title
 * @property integer $number
 * @property integer $gold
 * @property string $money
 * @property string $summary
 * @property string $create_time
 * @property integer $sort
 * @property integer $status
 */
class Gold extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'gold';
    }

    public static function getDb(){
        return Yii::$app->vruser1;
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['number', 'gold', 'sort', 'status'], 'integer'],
            [['money'], 'number'],
            [['summary'], 'string'],
            [['create_time'], 'safe'],
            [['title'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'number' => 'Number',
            'gold' => 'Gold',
            'money' => 'Money',
            'summary' => 'Summary',
            'create_time' => 'Create Time',
            'sort' => 'Sort',
            'status' => 'Status',
        ];
    }
}
