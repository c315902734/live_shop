<?php
namespace common\models;

use Yii;

/**
 * DuibaOrders model
 *
 */
class DuibaOrders extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'duiba_orders';
    }
    public static function getDb(){
        return Yii::$app->vruser1;
    }
}