<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "news_praise".
 *
 * @property string $id
 * @property string $news_id
 * @property string $user_id
 * @property integer $create_time
 * @property integer $status
 */
class NewsPraise extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news_praise';
    }

    public static function getDb()
    {
        return yii::$app->vrnews1;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['news_id', 'user_id', 'create_time', 'status'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'news_id' => 'News ID',
            'user_id' => 'User ID',
            'create_time' => 'Create Time',
            'status' => 'Status',
        ];
    }
}
