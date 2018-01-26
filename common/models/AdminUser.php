<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\grid\Column;
use yii\web\IdentityInterface;
use common\models\OauthAccessTokens;
use common\models\NewsColumn;
use common\models\Area;
use common\models\NewsColumnAdmin;

/**
 * This is the model class for table "admin_user".
 *
<<<<<<< HEAD
 * @property integer $admin_id
 * @property string  $username
 * @property string  $admin_pwd
 * @property integer $type
 * @property string  $real_name
 * @property integer $status
 * @property string  $user_email
 * @property string  $user_url
 * @property string  $avatar
 * @property integer $sex
 * @property string  $birthday
 * @property string  $signature
 * @property string  $last_login_ip
 * @property string  $last_login_time
 * @property string  $create_time
 * @property integer $user_type
 * @property string  $mobile
 * @property string  $company_id
 * @property string  $registration_id
 * @property integer $is_update
=======
 * @property integer $id
 * @property string  $username
 * @property string  $password_hash
 * @property string  $password_reset_token
 * @property string  $email
 * @property string  $auth_key
 * @property integer $status
 * @property string  $password write-only password
>>>>>>> b4a98e71ee10f50c4e294535c857e728cf2e2f41
 */
class AdminUser extends ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
    public $created_at;
    public $updated_at;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_user';
    }
    
    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->vradmin1;
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'status', 'sex', 'user_type', 'company_id', 'is_update'], 'integer'],
            [['birthday', 'last_login_time', 'create_time'], 'safe'],
            [['username', 'user_email', 'user_url'], 'string', 'max' => 100],
            [['admin_pwd'], 'string', 'max' => 45],
            [['real_name'], 'string', 'max' => 15],
            [['avatar', 'signature'], 'string', 'max' => 255],
            [['last_login_ip'], 'string', 'max' => 16],
            [['mobile'], 'string', 'max' => 20],
            [['registration_id'], 'string', 'max' => 30],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'admin_id'        => 'Admin ID',
            'username'        => 'Username',
            'admin_pwd'       => 'Admin Pwd',
            'type'            => 'Type',
            'real_name'       => 'Real Name',
            'status'          => 'Status',
            'user_email'      => 'User Email',
            'user_url'        => 'User Url',
            'avatar'          => 'Avatar',
            'sex'             => 'Sex',
            'birthday'        => 'Birthday',
            'signature'       => 'Signature',
            'last_login_ip'   => 'Last Login Ip',
            'last_login_time' => 'Last Login Time',
            'create_time'     => 'Create Time',
            'user_type'       => 'User Type',
            'mobile'          => 'Mobile',
            'company_id'      => 'Company ID',
            'registration_id' => 'Registration ID',
            'is_update'       => 'Is Update',
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['admin_id' => $id, 'status' => self::STATUS_ACTIVE]);
    }
    
    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        //查询token是否有效
        /** @var \filsh\yii2\oauth2server\Module $module */
        $module = Yii::$app->getModule('oauth2');
        $token = $module->getServer()->getResourceController()->getToken();
        
        return !empty($token['admin_id'])
            ? static::findIdentity($token['admin_id'])
            : null;
    }
    
    /**
     * Finds user by username
     *
     * @param string $username
     *
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }
    
    /*
     * 获取用户信息
     * */
    public static function getUserByLogin($username)
    {
        $user = AdminUser::find()
            ->where(['username' => $username])
            ->andWhere(['status' => 1])
            ->orWhere(['user_email' => $username])
            ->one();
        
        return $user;
    }
    
    
    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     *
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['admin_user.passwordResetTokenExpire'];
        
        return $timestamp + $expire >= time();
    }
    
    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }
    
    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }
    
    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
    
    /**
     * Validates password
     *
     * @param string $password password to validate
     *
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }
    
    /**
     * Finds user by mobile_phone
     *
     * @param string $mobile_phone
     *
     * @return static|null
     */
    public static function findByMobilePhone($mobile_phone)
    {
        return static::findOne(['username' => $mobile_phone, 'status' => self::STATUS_ACTIVE]);
    }
    
    /**
     * CMF密码加密方法
     *
     * @param string $pw 要加密的字符串
     *
     * @return string
     */
    public function sp_password($pw, $authcode = '')
    {
        if (empty($authcode)) {
            $authcode = 'mMuv1JM8xVBLmdCyKH';
//            $authcode=  Yii::$app->params["AUTHCODE"];
        }
        $result = "###" . md5(md5($authcode . $pw));
        
        return $result;
    }
    
    public static function setAccessToken($token = null, $user_id = null, $new_token = '')
    {
        $expires = date('Y-m-d H:i:s', strtotime(" + 30 day"));
        if ($token && $user_id) {
            OauthAccessTokens::updateAll(['expires' => date('Y-m-d H:i:s', strtotime(" - 1 day"))], ["access_token" => $token, "user_id" => $user_id]);
        }
        if ($new_token) {
            if (OauthAccessTokens::updateAll(['expires' => $expires], ['access_token' => $new_token])) {
                return $expires;
            }
        }
    }
    
    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }
    
    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }
    
    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }
    
    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
    
    /*
     *查看用户信息
     * */
    /**
     * an admin user  has_many AD startup page solution via AdStartupAdmin.admin_id' -> 'id'
     * @return Response
     */
    public function getAdStartupAdmin()
    {
        return $this->hasMany(AdStartupAdmin::className(), ['admin_id' => 'admin_id']);
    }


    /**
     *
     * 定义后台编辑与普通频道之间多对多关系
     *
     */
    
    
    public  function getNewsColumnsAdmin()
    {
        return $this->hasMany(NewsColumnAdmin::className(), ['admin_id' => 'admin_id']);
        
    }
    
    public  function getNewsColumns()
    {
        return $this->hasMany(NewsColumn::className(), ['column_id' => 'column_id'])
            ->via('newsColumnsAdmin');
    }
    
    /**
     *
     * 定义后台编辑与本地频道之间多对多关系
     *
     */
    public function getAreas()
    {
        return $this->hasMany(Area::className(), ['area_id' => 'area_id'])
                ->viaTable('vrnews1.area_admin', ['admin_id' => 'admin_id']);
    }
    
    public function getEntry()
    {
        return $this->hasMany(Entry::className(), ['admin_id' => 'admin_id']);
        
    }


}
