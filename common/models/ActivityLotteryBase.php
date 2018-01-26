<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "activity_lottery_base".
 *
 * @property string $base_id
 * @property string $company_id
 * @property string $cost_huiwenbi
 * @property integer $limit_num
 * @property integer $limit_num_type
 * @property integer $free_num
 * @property integer $free_num_type
 * @property integer $create_time
 */
class ActivityLotteryBase extends \yii\db\ActiveRecord
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
        return 'activity_lottery_base';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'limit_num', 'limit_num_type', 'free_num', 'free_num_type', 'create_time'], 'integer'],
            [['cost_huiwenbi'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'base_id' => 'Base ID',
            'company_id' => 'Company ID',
            'cost_huiwenbi' => 'Cost Huiwenbi',
            'limit_num' => 'Limit Num',
            'limit_num_type' => 'Limit Num Type',
            'free_num' => 'Free Num',
            'free_num_type' => 'Free Num Type',
            'create_time' => 'Create Time',
        ];
    }
}
