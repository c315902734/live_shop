<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_prop".
 *
 * @property integer $prop_id
 * @property string $name
 * @property string $icon
 * @property string $remark
 * @property integer $amount
 * @property integer $sentiment_value
 * @property string $create_time
 * @property string $update_time
 * @property integer $creator_id
 * @property integer $status
 */
class LiveProp extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_prop';
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
            [['amount', 'sentiment_value', 'creator_id', 'status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['name'], 'string', 'max' => 10],
            [['icon'], 'string', 'max' => 200],
            [['remark'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'prop_id' => 'Prop ID',
            'name' => 'Name',
            'icon' => 'Icon',
            'remark' => 'Remark',
            'amount' => 'Amount',
            'sentiment_value' => 'Sentiment Value',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'creator_id' => 'Creator ID',
            'status' => 'Status',
        ];
    }
}
