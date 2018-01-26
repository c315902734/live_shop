<?php
namespace frontend\controllers;

use common\models\QuizRule;
use common\models\UserQuiz;
use common\models\User1;

class MyquizController extends BaseApiController{

    /**
     * 获取每个新闻的竞猜情况
     * @return quizeList
     */
    public function actionGetMyQuize(){    	
        $token   = isset($_REQUEST['token']) ? $_REQUEST['token'] : '';
        $user_id = '';
        if(!$token){
        	$this->_errorData(9001, '未经授权访问');
        	exit;
        } 
        else{
        	$userData = $this->_checkToken($token);
        	if($userData == false){
        		$this->_errorData(0055, '用户未登录');
        		exit;
        	}        	
        }        
        
        //$userData = array('userId'=>'201614747844623579');//测试数据
        $user_id = $userData['userId'];
        
        $page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $size = !empty($_REQUEST['size']) ? $_REQUEST['size'] : 10;
        $offset  = $size*($page - 1);

        //查询我参与的竞猜         
         /*ThinkPhp
         $count = $this->user_quiz_model
         ->field("quiz.`quiz_name`,quiz_rule.`rule_name` as my_rule_name,user_quiz.`amount`,user_quiz.`won_cnt` as won_amount , quiz.`correct_id` , user_quiz.`created_at`")
        ->join("quiz on quiz.quiz_id = user_quiz.quiz_id","left")
        ->join("quiz_rule on quiz_rule.rule_id = user_quiz.rule_id","left")
        ->where("user_id=".$user_id)
        ->count();
        */
        /* Yii2 */
        $quiz_cnt = UserQuiz::find()->select("quiz.`quiz_name`,quiz_rule.`rule_name` as my_rule_name,user_quiz.`amount`,user_quiz.`won_cnt` as won_amount , quiz.`correct_id` , user_quiz.`created_at`")
        ->join('left join','vrnews1.quiz','quiz.quiz_id = user_quiz.quiz_id')
        ->join('left join','vrnews1.quiz_rule','quiz_rule.rule_id = user_quiz.rule_id')
        ->where(["user_id"=>$user_id])
        ->count();
        $total   = ceil($quiz_cnt/$size); 
        /*
         * ThinkPhp
        $user_quiz = $this->user_quiz_model
        ->field("vrlive.live.live_id, vrlive.live.name, vrlive.live.create_time, user_quiz.news_id, quiz.`quiz_name`,quiz_rule.`rule_name` as my_rule_name,user_quiz.`amount`,user_quiz.`won_cnt` as won_amount , quiz.`correct_id` , user_quiz.`created_at`")
        ->join("quiz on quiz.quiz_id = user_quiz.quiz_id","left")
        ->join("quiz_rule on quiz_rule.rule_id = user_quiz.rule_id","left")
        ->join('vrlive.live ON user_quiz.news_id=vrlive.live.live_id', 'left')
        ->where("user_id=".$user_id)
        ->order("user_quiz.`created_at` desc,quiz.created_at,quiz_rule.rule_id")
        */        
        $user_quiz = UserQuiz::find()->select('vrlive.live.live_id, vrlive.live.name, vrlive.live.create_time, user_quiz.news_id, quiz.`quiz_name`,quiz_rule.`rule_name` as my_rule_name,user_quiz.`amount`,user_quiz.`won_cnt` as won_amount , quiz.`correct_id` , user_quiz.`created_at`')
        ->join('left join', 'vrnews1.quiz', 'quiz.quiz_id = user_quiz.quiz_id')
        ->join('left join', 'vrnews1.quiz_rule', 'quiz_rule.rule_id = user_quiz.rule_id')
        ->join('left join', 'vrlive.live', 'user_quiz.news_id=vrlive.live.live_id')
        ->where(["user_id"=>$user_id])
        ->orderBy("user_quiz.`created_at` desc,quiz.created_at,quiz_rule.rule_id")
        //->groupBy('user_quiz.news_id')
        //->limit($size)->offerset($offset)        
        ->asArray()
        ->all();
			
			$res = array();
			foreach ($user_quiz as $k => $v) {
				$res[$v['live_id']][] = $v;
			}

			//过滤数组获得正确答案
            foreach ($res as $k => &$v) {
            	foreach($v as &$val){
            		if($val['correct_id']!=0){            			
            			$quiz_rule = QuizRule::find()->where(['rule_id'=> $val['correct_id']])->asArray()->one();            			
            			if($quiz_rule){
            				$val['correct_name'] = $quiz_rule['rule_name'];
            			}else{
            				$val['correct_name'] = '';
            			}
            		}else{
            			$val['correct_name'] = '';
            		}
            		$created_at = $val['created_at'];
            	}
            	unset($val);
            }
            unset($v);
            //查询我的金币			
			$user = User1::getUserById($user_id);
            
			/*
			$this->assign('page', $page);
            $this->assign('token', $token);
            $this->assign('total', $total);
            $this->assign('amount', $user['amount']);
            $this->assign('user_quiz', $res);
            $this->assign('user_id', $user_id);
            $this->display("myquiz");
            */
            $ret = array('page'=>$page,'token'=>$token,'total'=>$total,'amount'=>$user['amount'],'user_quiz'=>$res,'user_id'=>$user_id,'totalCount'=>$quiz_cnt);
            $this->_successData($ret);
    }
}