<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_quiz".
 *
 * @property string $id
 * @property string $user_id
 * @property string $news_id
 * @property string $quiz_id
 * @property string $rule_id
 * @property integer $amount
 * @property integer $won_cnt
 * @property string $created_at
 */
class UserQuiz extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_quiz';
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
            [['user_id', 'news_id', 'quiz_id', 'rule_id', 'amount', 'won_cnt'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'news_id' => 'News ID',
            'quiz_id' => 'Quiz ID',
            'rule_id' => 'Rule ID',
            'amount' => 'Amount',
            'won_cnt' => 'Won Cnt',
            'created_at' => 'Created At',
        ];
    }
}
