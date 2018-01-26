<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property string $user_id
 * @property string $username
 * @property string $nickname
 * @property string $mobile_phone
 * @property string $password
 * @property integer $sex
 * @property string $birthday
 * @property integer $province_id
 * @property integer $area_id
 * @property integer $location_status
 * @property string $rcloud_token
 * @property string $register_time
 * @property integer $user_type
 * @property integer $score
 * @property integer $amount
 * @property string $login_token
 * @property string $login_time
 * @property string $avatar
 * @property integer $status
 * @property string $open_id
 */
class User1 extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }
    
    public static function getDb(){
    	return Yii::$app->vruser1;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'sex', 'province_id', 'area_id', 'location_status', 'user_type', 'score', 'amount', 'status'], 'integer'],
            [['birthday', 'register_time', 'login_time'], 'safe'],
            [['username', 'login_token'], 'string', 'max' => 45],
            [['nickname'], 'string', 'max' => 30],
            [['mobile_phone'], 'string', 'max' => 15],
            [['password'], 'string', 'max' => 35],
            [['rcloud_token'], 'string', 'max' => 120],
            [['avatar'], 'string', 'max' => 200],
            [['open_id'], 'string', 'max' => 64],
            [['username'], 'unique'],
            [['mobile_phone'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'username' => 'Username',
            'nickname' => 'Nickname',
            'mobile_phone' => 'Mobile Phone',
            'password' => 'Password',
            'sex' => 'Sex',
            'birthday' => 'Birthday',
            'province_id' => 'Province ID',
            'area_id' => 'Area ID',
            'location_status' => 'Location Status',
            'rcloud_token' => 'Rcloud Token',
            'register_time' => 'Register Time',
            'user_type' => 'User Type',
            'score' => 'Score',
            'amount' => 'Amount',
            'login_token' => 'Login Token',
            'login_time' => 'Login Time',
            'avatar' => 'Avatar',
            'status' => 'Status',
            'open_id' => 'Open ID',
        ];
    }
    
    /**
     *
     * @param string $username
     */
    public static function getUserByLogin($username, $is_Array = false)
    {
    	return static::find()->where(['username' => $username])->orWhere(['mobile_phone'=>$username])->asArray($is_Array)->one();
    }
    
    public static function getAvatarUrl($avatar)
    {
    	if (!empty($avatar) && strlen($avatar) > 0) {
    		return $avatar;
    	} else {
    		return "";
    	}
    }
    
    public static function getUserByThirdPartyOpenId($openId,$userType){
    	$user = self::find()->where(['open_id'=>$openId,'user_type'=>$userType])->one();
    	return $user;
    }
    
    public static function getUserById($uid){
    	$user = self::findOne($uid);
    	return $user;
    }

    public static function setAccessToken($token = null, $user_id = null, $new_token=''){
        $expires = date('Y-m-d H:i:s',strtotime(" + 30 day"));
        if($token && $user_id){
            OauthAccessTokens::updateAll(['expires'=>date('Y-m-d H:i:s',strtotime(" - 1 day"))],["access_token" => $token,"user_id" => $user_id]);
        }
        if($new_token){
            if(OauthAccessTokens::updateAll(['expires'=>$expires],['access_token'=>$new_token])){
                return $expires;
            }
        }
    }
    

    
    
}
