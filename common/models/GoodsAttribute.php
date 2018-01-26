<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "goods_attribute".
 *
 * @property integer $attribute_id
 * @property integer $company_id
 * @property string $attribute_name
 * @property string $create_time
 */
class GoodsAttribute extends \yii\db\ActiveRecord
{
    public static function getDb()
    {
        return yii::$app->vrshop;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'goods_attribute';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id'], 'integer'],
            [['create_time'], 'safe'],
            [['attribute_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'attribute_id' => 'Attribute ID',
            'company_id' => 'Company ID',
            'attribute_name' => 'Attribute Name',
            'create_time' => 'Create Time',
        ];
    }

    /*
     * 获取列表
     */
    public static function GetList($company_id, $page, $size){
        $model = new self();
        $offset = ($page - 1) * $size;
        $list = $model->find()
            ->select(['attribute_id','attribute_name'])
            ->where(['company_id'=>$company_id])
            ->orderBy('create_time DESC')
            ->offset($offset)
            ->limit($size)
            ->asArray()
            ->all();

        $count = $model->find()
            ->select(['attribute_id','attribute_name'])
            ->where(['company_id'=>$company_id])
            ->asArray()
            ->count();

        $return_data = ['count'=>$count, 'list'=>$list];
        return $return_data;
    }

    public static function addAttr($company_id, $attr_name){
        $goods_attr_model = new self();
        $goods_attr_model->company_id = $company_id;
        $goods_attr_model->attribute_name = $attr_name;
        $goods_attr_model->create_time = date('Y-m-d H:i:s', time());
        return $goods_attr_model->save();
    }
}
