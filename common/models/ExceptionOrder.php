<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "exception_order".
 *
 * @property string $id
 * @property string $user_id
 * @property string $order_id
 * @property integer $pay_type
 * @property string $error
 * @property string $create_time
 */
class ExceptionOrder extends \yii\db\ActiveRecord
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
        return 'exception_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'order_id', 'pay_type'], 'integer'],
            [['create_time'], 'safe'],
            [['error'], 'string', 'max' => 255],
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
            'order_id' => 'Order ID',
            'pay_type' => 'Pay Type',
            'error' => 'Error',
            'create_time' => 'Create Time',
        ];
    }

    public static function exceptionOrderRecord($user_id = 0, $order_id = 0, $pay_type = 0, $error = ''){
        $model = new self;
        $model->user_id = $user_id;
        $model->order_id = $order_id;
        $model->pay_type = $pay_type;
        $model->error = $error;
        $model->save();
    }
}
