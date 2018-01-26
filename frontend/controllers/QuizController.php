<?php
namespace frontend\controllers;

use common\models\Quiz;

class QuizController extends PublicBaseController{

    /**
     * 获取每个新闻的竞猜情况
     * @return quizeList
     */
    public function actionGetNewsQuize(){
        $token   = isset($_REQUEST['token']) ? $_REQUEST['token'] : '';
        $news_id = isset($_REQUEST['news_id']) ? $_REQUEST['news_id'] : '';
        if($token){
            $userData = $this->_checkToken($token);
            if($userData == false){
                $this->_errorData(0055, '用户未登录');
                exit;
            }
            $user_id = $userData['userId'];
        }else{
            $user_id = '';
        }
        if(1==1){
            $res = Quiz::getQuizInfo($news_id, $user_id);
            if(isset($res['code'])){
                $this->_errorData($res['code'], $res['message']);
            }else{
                $this->_successData($res);
            }
        }
    }
}