<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "feed_back".
 *
 * @property integer $Id
 * @property integer $type
 * @property string $detail
 * @property string $feed_code
 * @property integer $user_code
 * @property string $user_name
 * @property string $phone
 * @property integer $state
 * @property string $created_date
 * @property string $creator
 * @property integer $isaudit
 * @property integer $isdeleted
 * @property string $modifier
 * @property string $updated_date
 * @property string $creator_deptcode
 */
class FeedBack extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'feed_back';
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
            [['type', 'user_code', 'state', 'isaudit', 'isdeleted'], 'integer'],
            [['created_date', 'updated_date'], 'safe'],
            [['detail'], 'string', 'max' => 300],
            [['feed_code', 'creator_deptcode'], 'string', 'max' => 32],
            [['user_name', 'creator', 'modifier'], 'string', 'max' => 50],
            [['phone'], 'string', 'max' => 16],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'ID',
            'type' => 'Type',
            'detail' => 'Detail',
            'feed_code' => 'Feed Code',
            'user_code' => 'User Code',
            'user_name' => 'User Name',
            'phone' => 'Phone',
            'state' => 'State',
            'created_date' => 'Created Date',
            'creator' => 'Creator',
            'isaudit' => 'Isaudit',
            'isdeleted' => 'Isdeleted',
            'modifier' => 'Modifier',
            'updated_date' => 'Updated Date',
            'creator_deptcode' => 'Creator Deptcode',
        ];
    }
}
