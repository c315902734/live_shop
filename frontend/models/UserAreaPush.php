<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_area_push".
 *
 * @property integer $id
 * @property string $user_id
 * @property string $create_time
 * @property string $update_time
 * @property string $area
 */
class UserAreaPush extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_area_push';
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
            [['user_id'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['area'], 'string', 'max' => 50],
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
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'area' => 'Area',
        ];
    }
}
