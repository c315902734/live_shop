<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "quiz_rule".
 *
 * @property string $rule_id
 * @property string $quiz_id
 * @property string $rule_name
 */
class QuizRule extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'quiz_rule';
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
            [['rule_id'], 'required'],
            [['rule_id', 'quiz_id'], 'integer'],
            [['rule_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rule_id' => 'Rule ID',
            'quiz_id' => 'Quiz ID',
            'rule_name' => 'Rule Name',
        ];
    }
}
