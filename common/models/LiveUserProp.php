<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_user_prop".
 *
 * @property string $id
 * @property string $user_id
 * @property string $competitor_id
 * @property string $live_id
 * @property integer $prop_id
 * @property integer $amount
 * @property integer $prop_cnt
 * @property integer $sentiment_value
 * @property string $create_time
 */
class LiveUserProp extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_user_prop';
    }
    
    public static function getDb()
    {
    	return yii::$app->vrlive;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'competitor_id', 'live_id', 'prop_id', 'amount', 'prop_cnt', 'sentiment_value'], 'integer'],
            [['create_time'], 'safe'],
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
            'competitor_id' => 'Competitor ID',
            'live_id' => 'Live ID',
            'prop_id' => 'Prop ID',
            'amount' => 'Amount',
            'prop_cnt' => 'Prop Cnt',
            'sentiment_value' => 'Sentiment Value',
            'create_time' => 'Create Time',
        ];
    }
}
