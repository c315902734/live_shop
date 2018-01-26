<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "activity_goods_relation".
 *
 * @property string $id
 * @property string $activity_id
 * @property string $goods_id
 * @property integer $goods_num
 * @property integer $goods_type
 * @property integer $activity_type
 * @property integer $create_time
 */
class ActivityGoodsRelation extends \yii\db\ActiveRecord
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
        return 'activity_goods_relation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['activity_id', 'goods_id', 'goods_num', 'goods_type', 'activity_type', 'create_time'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'activity_id' => 'Activity ID',
            'goods_id' => 'Goods ID',
            'goods_num' => 'Goods Num',
            'goods_type' => 'Goods Type',
            'activity_type' => 'Activity Type',
            'create_time' => 'Create Time',
        ];
    }
}
