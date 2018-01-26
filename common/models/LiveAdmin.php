<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_admin".
 *
 * @property integer $admin_id
 * @property string $username
 * @property string $password
 */
class LiveAdmin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_admin';
    }
    
    public static function getDb(){
    	return yii::$app->vrlive;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['username', 'password'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'admin_id' => 'Admin ID',
            'username' => 'Username',
            'password' => 'Password',
        ];
    }
}
