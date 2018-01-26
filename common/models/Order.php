<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "order".
 *
 * @property integer $id
 * @property string $user_id
 * @property integer $goods_id
 * @property string $order_ch_id
 * @property string $order_number
 * @property string $total_price
 * @property string $pay_type
 * @property string $pay_status
 * @property integer $create_time
 * @property integer $edit_time
 * @property integer $pay_time
 */
class Order extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order';
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
            [['user_id', 'goods_id', 'create_time', 'edit_time', 'pay_time'], 'integer'],
            [['total_price'], 'number'],
            [['order_ch_id'], 'string', 'max' => 100],
            [['order_number'], 'string', 'max' => 50],
            [['pay_type'], 'string', 'max' => 10],
            [['pay_status'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'goods_id' => 'Goods ID',
            'order_ch_id' => 'Order Ch ID',
            'order_number' => 'Order Number',
            'total_price' => 'Total Price',
            'pay_type' => 'Pay Type',
            'pay_status' => 'Pay Status',
            'create_time' => 'Create Time',
            'edit_time' => 'Edit Time',
            'pay_time' => 'Pay Time',
        ];
    }
}