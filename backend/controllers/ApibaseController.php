<?php
namespace backend\controllers;
use yii\web\Controller;
use OAuth2\GrantType\RefreshToken;
use Yii;

use OAuth2\Storage\Pdo;

class ApibaseController extends Controller
{
    public $server;
    public function beforeAction($action)
    {
//
//        $dsn      = Yii::$app->params['api_dns'];
//        $username = Yii::$app->params['api_username'];
//        $password = Yii::$app->params['api_password'];
        $dsn      = 'mysql:host=127.0.0.1;dbname=nma-tools';
        $username = 'root';
        $password = '';

        $storage = new Pdo(array('dsn' => $dsn, 'username' => $username, 'password' => $password));
        $server = new \OAuth2\Server($storage, array('enforce_state'=>false,'refresh_token_lifetime'=>31*86400));
        $server->addGrantType(new \OAuth2\GrantType\ClientCredentials($storage));
        $server->addGrantType(new \OAuth2\GrantType\AuthorizationCode($storage));
        $server->addGrantType(new \OAuth2\GrantType\UserCredentials($storage));
        $server->addGrantType(new RefreshToken($storage,array('always_issue_new_refresh_token'=>true)));

        $this->server = $server;
        return true;
    }
}