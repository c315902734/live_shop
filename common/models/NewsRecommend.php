<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "news_recommend".
 *
 * @property string $id
 * @property string $news_id
 * @property string $recommend_id
 * @property string $type
 */
class NewsRecommend extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news_recommend';
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
            [['news_id', 'recommend_id'], 'integer'],
            [['type'], 'string', 'max' => 20],
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
            'recommend_id' => 'Recommend ID',
            'type' => 'Type',
        ];
    }
}
