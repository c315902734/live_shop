<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "vote_group".
 *
 * @property integer $id
 * @property string $group_name
 */
class VoteGroup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vote_group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_name'], 'required'],
            [['group_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_name' => 'Group Name',
        ];
    }

    public static function getDb(){
        return Yii::$app->tools;
    }
}
