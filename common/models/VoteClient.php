<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vote_client".
 *
 * @property integer $id
 * @property string $finger
 * @property integer $vote_id
 * @property integer $created_at
 */
class VoteClient extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vote_client';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['finger', 'vote_id', 'created_at'], 'required'],
            [['vote_id', 'created_at'], 'integer'],
            [['finger'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'finger' => 'Finger',
            'vote_id' => 'Vote ID',
            'created_at' => 'Created At',
        ];
    }

    public function save($runValidation = true, $attributeNames = null){
        $this->created_at = time();
        parent::save($runValidation,$attributeNames);
    }

    public static function getDb(){
        return Yii::$app->tools;
    }
}
