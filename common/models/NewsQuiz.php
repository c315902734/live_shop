<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "news_quiz".
 *
 * @property string $id
 * @property string $news_id
 * @property string $quiz_id
 */
class NewsQuiz extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news_quiz';
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
            [['id'], 'required'],
            [['id', 'news_id', 'quiz_id'], 'integer'],
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
            'quiz_id' => 'Quiz ID',
        ];
    }
}
