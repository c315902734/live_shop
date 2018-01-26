<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "area".
 * @property integer $id
 * @property string  $name
 * @property string  $status
 */
class Plugin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'plugin';
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
            [['id'], 'integer'],
            [['status'], 'integer'],
            [['name'], 'string', 'max' => 60],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'name' => 'Name',
            'status' => 'Status'
        ];
    }
    
    
    
}
