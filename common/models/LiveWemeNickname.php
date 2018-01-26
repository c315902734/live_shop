<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_weme_nickname".
 *
 * @property integer $id
 * @property string $url
 * @property string $mirrtalkID
 * @property string $nickname
 */
class LiveWemeNickname extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_weme_nickname';
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
            [['url'], 'string', 'max' => 1000],
            [['mirrtalkID', 'nickname'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'Url',
            'mirrtalkID' => 'Mirrtalk ID',
            'nickname' => 'Nickname',
        ];
    }
}
