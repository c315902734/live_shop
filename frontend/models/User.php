<?php
namespace frontend\models;

use Yii;
use common\models\User as BaseUser;

class User extends BaseUser implements \OAuth2\Storage\UserCredentialsInterface
{

    /**
     * Implemented for Oauth2 Interface
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
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
        // $user = static::findByUsername($username);
        // 手机号唯一
        $user = static::findByMobilePhone($username);
        if (empty($user)) {
            return false;
        }
        return $user['password']==static::sp_password($password);
        // return $user->validatePassword($password);
    }

    /**
     * Implemented for Oauth2 Interface
     */
    public function getUserDetails($username)
    {
        // $user = static::findByUsername($username);
        // 手机号唯一
        $user = static::findByMobilePhone($username);
        return ['user_id' => $user->getId()];
    }
}