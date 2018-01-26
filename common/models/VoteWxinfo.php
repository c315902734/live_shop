<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vote_wxinfo".
 *
 * @property integer $id
 * @property string $app_key
 * @property string $app_secret
 * @property string $access_token
 * @property integer $access_expires_in
 * @property string $jsapi_ticket
 * @property integer $jsapi_expires_in
 */
class VoteWxinfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vote_wxinfo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['app_key', 'app_secret', 'access_token', 'access_expires_in', 'jsapi_ticket', 'jsapi_expires_in'], 'required'],
            [['access_expires_in', 'jsapi_expires_in'], 'integer'],
            [['app_key', 'app_secret', 'access_token', 'jsapi_ticket'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_key' => 'App Key',
            'app_secret' => 'App Secret',
            'access_token' => 'Access Token',
            'access_expires_in' => 'Access Expires In',
            'jsapi_ticket' => 'Jsapi Ticket',
            'jsapi_expires_in' => 'Jsapi Expires In',
        ];
    }

    public static function getDb(){
        return Yii::$app->tools;
    }
}
