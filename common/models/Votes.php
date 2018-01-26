<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "votes".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $group_id
 * @property integer $sex
 * @property string $user_name
 * @property string $alias_name
 * @property string $image
 * @property integer $vote_cnt
 */
class Votes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'votes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'group_id', 'sex', 'user_name', 'alias_name', 'image', 'vote_cnt'], 'required'],
            [['user_id', 'group_id', 'sex', 'vote_cnt'], 'integer'],
            [['user_name', 'alias_name', 'image'], 'string', 'max' => 255],
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
            'group_id' => 'Group ID',
            'sex' => 'Sex',
            'user_name' => 'User Name',
            'alias_name' => 'Alias Name',
            'image' => 'Image',
            'vote_cnt' => 'Vote Cnt',
        ];
    }

    public static function getDb(){
        return Yii::$app->tools;
    }
}
