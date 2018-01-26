<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "category_goods_relation".
 *
 * @property integer $relation_id
 * @property integer $category_id
 * @property string $goods_id
 */
class CategoryGoodsRelation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category_goods_relation';
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
            [['category_id', 'goods_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'relation_id' => 'Relation ID',
            'category_id' => 'Category ID',
            'goods_id' => 'Goods ID',
        ];
    }
}
