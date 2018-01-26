<?php
namespace backend\controllers;

use OAuth2\Response;
use OAuth2\Request;
use common\models\ApiResponse;


class OauthController extends ApibaseController
{
    public $enableCsrfValidation = false;
    public $server;
    public $tokenData;
    
    /**
     * 令牌方式
     */
    public function actionAuthorize()
    {
        $response     = new Response();
        $request = Request::createFromGlobals();
        $response = $this->server->handleAuthorizeRequest(
            Request::createFromGlobals(),$response,true
        );
        $headers = $response->getHttpHeaders();
        $location = $headers['Location'];
        
        $rule = "/^(.*?)code=(.*?)$/";
        preg_match($rule,$location,$result);
        $code = "";
        if(isset($result)){
            if(isset($result[2])){
                $code = $result[2];
            }
        }
        $data['code'] = $code;
        $api_response = new ApiResponse();
        $api_response->send($data);
    }
    
    public function actionToken()
    {
        $response = $this->server->handleTokenRequest(Request::createFromGlobals());
        $data = $response->getParameters();
        $api_response = new ApiResponse();
        $api_response->send($data);
    }
    
}
