<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "activity_m_ad_config".
 *
 * @property string $ad_id
 * @property string $icon_url
 * @property string $jump_url
 * @property integer $type
 * @property integer $status
 * @property string $crate_time
 */
class ActivityMAdConfig extends \yii\db\ActiveRecord
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
        return 'activity_m_ad_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['icon_url', 'jump_url'], 'required'],
            [['type', 'status', 'crate_time'], 'integer'],
            [['icon_url', 'jump_url'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ad_id' => 'Ad ID',
            'icon_url' => 'Icon Url',
            'jump_url' => 'Jump Url',
            'type' => 'Type',
            'status' => 'Status',
            'crate_time' => 'Crate Time',
        ];
    }
}
