<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "goods_attribute_values".
 *
 * @property integer $values_id
 * @property string $goods_id
 * @property integer $attribute_id
 * @property string $values_content
 * @property string $price
 * @property integer $stock
 * @property string $values_images
 */
class GoodsAttributeValues extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'goods_attribute_values';
    }
    
    public static function getDb()
    {
    	return Yii::$app->vrshop;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goods_id', 'attribute_id', 'stock'], 'integer'],
            [['price'], 'number'],
            [['values_images'], 'string'],
            [['values_content'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'values_id' => 'Values ID',
            'goods_id' => 'Goods ID',
            'attribute_id' => 'Attribute ID',
            'values_content' => 'Values Content',
            'price' => 'Price',
            'stock' => 'Stock',
            'values_images' => 'Values Images',
        ];
    }
    
    /*
     * 获取属性列表
     */
    public static function GetList($goods_id, $pageStart, $pageEnd){
    	$returnData = array();
    	$goods_info = Goods::find()->select(['goods_name'])->where(['goods_id'=>$goods_id])->asArray()->one();
    	 
    	$model = new self();
    	$list = $model->find()
    	->where(['goods_id'=>$goods_id])
    	->orderBy(['values_id'=>SORT_DESC])
    	->offset($pageStart)
    	->limit($pageEnd-$pageStart)
    	->asArray()->all();
//    	 ->createCommand()->getRawSql();
//    	echo $list;die;
    	if($list){
    		foreach($list as $key=>$value){
    			$list[$key]['goods_name'] = $goods_info['goods_name'];
    			$order_info = GoodsAttributeValues::find()
    			->select(['shop_order.order_number','goods_attribute.attribute_name'])
    			->innerJoin('goods_attribute','goods_attribute.attribute_id = goods_attribute_values.attribute_id')
    			->leftJoin('order_goods_relation','order_goods_relation.goods_id = goods_attribute_values.goods_id')
    			->leftJoin('shop_order','shop_order.order_id = order_goods_relation.order_id')
    			->where(['order_goods_relation.goods_id'=>$goods_id,'goods_attribute_values.attribute_id'=>$value['attribute_id']])
    			->asArray()->one();

    			$list[$key]['attribute_name'] = isset($order_info['attribute_name']) ? $order_info['attribute_name'] : '';
    			$list[$key]['order_number'] = isset($order_info['order_number']) ? $order_info['order_number'] : '';
    			$returnData[] = $list[$key];
    		}
    	}
    	return $returnData;
    }
}
