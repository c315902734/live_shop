<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "visitor_token".
 *
 * @property integer $visitor_id
 * @property string $phone_id
 * @property string $rcloud_token
 */
class VisitorToken extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'visitor_token';
    }

    public static function getDb()
    {
        return Yii::$app->vruser1;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone_id'], 'string', 'max' => 50],
            [['rcloud_token'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'visitor_id' => 'Visitor ID',
            'phone_id' => 'Phone ID',
            'rcloud_token' => 'Rcloud Token',
        ];
    }
}
