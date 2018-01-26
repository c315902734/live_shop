<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vote_ballot".
 *
 * @property string $ballot_id
 * @property string $vote_id
 * @property string $option_id
 * @property string $user_id
 * @property integer $create_time
 */
class VoteBallot extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vote_ballot';
    }

    public static function getDb(){
        return yii::$app->vrnews1;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vote_id', 'option_id', 'create_time'], 'integer'],
            [['user_id'], 'required'],
            [['user_id'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ballot_id' => 'Ballot ID',
            'vote_id' => 'Vote ID',
            'option_id' => 'Option ID',
            'user_id' => 'User ID',
            'create_time' => 'Create Time',
        ];
    }
}
