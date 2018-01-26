<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "company".
 *
 * @property integer $company_id
 * @property string $company_name
 * @property string $create_time
 * @property integer $is_del
 */
class Company extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'company';
    }
    
    public static function getDb()
    {
    	return Yii::$app->vrnews1;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time'], 'safe'],
            [['is_del'], 'integer'],
            [['company_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'company_id' => 'Company ID',
            'company_name' => 'Company Name',
            'create_time' => 'Create Time',
            'is_del' => 'Is Del',
        ];
    }
    
    public static function GetList(){
    	$model = new self();
    	$list = $model->find()
    	->select(['company_id','company_name'])
    	->where(['is_del'=>'0'])
    	->asArray()
    	->all();
    	
    	return $list;
    }
}
