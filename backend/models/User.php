<?php
namespace backend\models;

use Yii;
use common\models\AdminUser as BaseUser;

class User extends BaseUser implements \OAuth2\Storage\UserCredentialsInterface
{

    /**
     * Implemented for Oauth2 Interface
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
//        return static::findOne(['access_token'=>$token]);
        /** @var \filsh\yii2\oauth2server\Module $module */
        $module = Yii::$app->getModule('oauth2');
        $token = $module->getServer()->getResourceController()->getToken();
        return !empty($token['user_id'])
                    ? static::findIdentity($token['user_id'])
                    : null;
    }

    /**
     * Implemented for Oauth2 Interface
     */
    public function checkUserCredentials($username, $password)
    {
        // username
        $user = static::findByMobilePhone($username);
        if (empty($user)) {
            return false;
        }
        return $user['admin_pwd']==static::sp_password($password);
    }

    /**
     * Implemented for Oauth2 Interface
     */
    public function getUserDetails($username)
    {
        // $user = static::findByUsername($username);
        // username
        $user = static::findByMobilePhone($username);
        return ['admin_id' => $user->getId()];
    }
}