<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "admin_user".
 *
 * @property integer $admin_id
 * @property string $username
 * @property string $admin_pwd
 * @property integer $type
 * @property string $real_name
 * @property integer $status
 * @property string $user_email
 * @property string $user_url
 * @property string $avatar
 * @property integer $sex
 * @property string $birthday
 * @property string $signature
 * @property string $last_login_ip
 * @property string $last_login_time
 * @property string $create_time
 * @property integer $user_type
 * @property string $mobile
 */
class AdminUser extends \yii\db\ActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->vradmin1;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'status', 'sex', 'user_type'], 'integer'],
            [['birthday', 'last_login_time', 'create_time'], 'safe'],
            [['username', 'user_email', 'user_url'], 'string', 'max' => 100],
            [['admin_pwd'], 'string', 'max' => 45],
            [['real_name'], 'string', 'max' => 15],
            [['avatar', 'signature'], 'string', 'max' => 255],
            [['last_login_ip'], 'string', 'max' => 16],
            [['mobile'], 'string', 'max' => 20],
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
            'admin_pwd' => 'Admin Pwd',
            'type' => 'Type',
            'real_name' => 'Real Name',
            'status' => 'Status',
            'user_email' => 'User Email',
            'user_url' => 'User Url',
            'avatar' => 'Avatar',
            'sex' => 'Sex',
            'birthday' => 'Birthday',
            'signature' => 'Signature',
            'last_login_ip' => 'Last Login Ip',
            'last_login_time' => 'Last Login Time',
            'create_time' => 'Create Time',
            'user_type' => 'User Type',
            'mobile' => 'Mobile',
        ];
    }
}
