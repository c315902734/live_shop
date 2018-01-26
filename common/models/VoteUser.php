<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vote_user".
 *
 * @property integer $id
 * @property string $open_id
 * @property string $nickname
 * @property integer $sex
 * @property string $language
 * @property string $city
 * @property string $province
 * @property string $country
 * @property string $headimgurl
 * @property string $subscribe_time
 * @property string $unionid
 */
class VoteUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vote_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['open_id', 'nickname', 'sex', 'language', 'city', 'province', 'country', 'subscribe_time', 'unionid'], 'required'],
            [['sex'], 'integer'],
            [['headimgurl'], 'string'],
            [['open_id', 'nickname', 'language', 'city', 'province', 'country', 'subscribe_time', 'unionid'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'open_id' => 'Open ID',
            'nickname' => 'Nickname',
            'sex' => 'Sex',
            'language' => 'Language',
            'city' => 'City',
            'province' => 'Province',
            'country' => 'Country',
            'headimgurl' => 'Headimgurl',
            'subscribe_time' => 'Subscribe Time',
            'unionid' => 'Unionid',
        ];
    }

    public static function getDb(){
        return Yii::$app->tools;
    }
}
