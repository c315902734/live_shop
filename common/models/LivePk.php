<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_pk".
 *
 * @property string $id
 * @property string $title
 * @property string $live_id
 * @property string $create_time
 * @property integer $status
 */
class LivePk extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_pk';
    }
    
    public static function getDb(){
    	return yii::$app->vrlive;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'live_id', 'status'], 'integer'],
            [['create_time'], 'safe'],
            [['title'], 'string', 'max' => 200],
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
            'live_id' => 'Live ID',
            'create_time' => 'Create Time',
            'status' => 'Status',
        ];
    }
}
