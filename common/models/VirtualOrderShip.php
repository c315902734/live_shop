<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "virtual_order_ship".
 *
 * @property string $ship_id
 * @property string $order_id
 * @property string $goods_id
 * @property string $details_id
 * @property integer $create_time
 */
class VirtualOrderShip extends \yii\db\ActiveRecord
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
        return 'virtual_order_ship';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'goods_id', 'details_id', 'create_time'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ship_id' => 'Ship ID',
            'order_id' => 'Order ID',
            'goods_id' => 'Goods ID',
            'details_id' => 'Details ID',
            'create_time' => 'Create Time',
        ];
    }
}
