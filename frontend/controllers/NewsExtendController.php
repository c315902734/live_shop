<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2017/4/19
 * Time: 11:45
 */

namespace frontend\controllers;

use common\models\NewsPraise;
use common\models\NewsReward;
use Yii;
use common\models\User1;
use common\models\NewsRecommend;

class NewsExtendController extends PublicBaseController
{
    public  $token;
    private $news_id;
    private $request;
    private $max_hw_money;
    private $hw_money_model;
    private $news_praise_model;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->request = Yii::$app->request;
        $this->token   = $this->request->post('token');
        $this->news_id = $this->request->post('news_id');
        $this->max_hw_money      = 9999;
        $this->hw_money_model    = new NewsReward();
        $this->news_praise_model = new NewsPraise();
    }

    /**
     *  打赏接口
     */    
    public function actionReward(){
    	$returnData = array();
    	if($this->request->isPost){
    		$token = yii::$app->request->post('token', 0);
    		if(!$token) $this->_errorData(1031, 'token错误');
    		$user_info = $this->getUserInfoByToken($token);
    		if($user_info['user_id']){
    			$news_id  = $this->request->post('news_id', 0);
    			$live_id  = $this->request->post('live_id', 0);
    			$hw_money = $this->request->post('money', 0);
    
    			if((!$news_id && !$live_id) || !$hw_money) $this->_errorData(1008, '参数错误');
    			if($hw_money > $this->max_hw_money) $this->_errorData(1009, '汇闻币数量过大');
    			if($hw_money > $user_info['amount']) $this->_errorData(1012, '汇闻币不足');
    
    			$this->hw_money_model->user_id = $user_info['user_id'];
    			$this->hw_money_model->news_id = $news_id;
    			$this->hw_money_model->live_id = $live_id;
    			$this->hw_money_model->hw_money    = $hw_money;
    			$this->hw_money_model->create_time = time();
    
    			if($this->hw_money_model->save()){
    				$user_model = User1::findOne($user_info['user_id']);
    				$user_model->amount = $user_model->amount - $hw_money;
    				if($user_model->save()){
    					$returnData['avatar'] = $user_info['avatar'];
    					$reward_count = NewsReward::find()->where(['user_id'=>$user_info['user_id']])->count();
    					$returnData['reward_count'] = $reward_count > 0 ? $reward_count : 0;
    					$this->_successData($returnData,'打赏成功');
    				}else{
    					NewsReward::deleteAll(['id'=>$this->hw_money_model->id]);
    					$this->_errorData(1010, '打赏失败');
    				}
    			}else{
    				$this->_errorData(1010, '打赏失败');
    			}	
    		}
    		$this->_errorData(1012, '用户信息错误');
    	}
    	$this->_errorData(1011, '请求错误');
    }


    /**
     * 点赞接口 记录点赞次数
     */
    public function actionPraise(){
        if($this->request->isPost){
            $token       = $this->request->post('token', 0);
            if(!$token) $this->_errorData(1030, '用户未登录');
            $news_id     = $this->request->post('news_id');
            $news_type   = $this->request->post('news_type', 0);  //新闻类型  0 直播新闻（图文直播，vr直播。。。） 1 普通新闻（图文，视频，图集。。。）
            $praised_status = $this->request->post('praised_status', 0); //默认是1  如果是取消点赞 传0

            if(!$news_id || !in_array($praised_status, array(0 , 1))) $this->_errorData(1003, '参数错误');

            $user_info = $this->getUserInfoByToken($token);

            if($user_info['user_id']){
                if($praised_status == 1){
                    $db_date = NewsPraise::find()->where(array('news_id'=>$news_id, 'user_id'=>$user_info['user_id'], 'news_type'=>$news_type))->one();
                    if($db_date){
                        $db_date->status = 1;
                        $ret = $ret = $db_date->update();
                        if($ret <= 0 || $ret === false) $this->_errorData(1007, '点赞失败');
                        $this->_successData('点赞成功');
                    }else{
                        $this->news_praise_model->news_id = $news_id;
                        $this->news_praise_model->user_id = $user_info['user_id'];
                        $this->news_praise_model->news_type = $news_type;
                        $this->news_praise_model->status  = 1;
                        $this->news_praise_model->create_time = time();
                        $ret = $this->news_praise_model->save();
                        if(!$ret) $this->_errorData(1003, '点赞失败');
                        $this->_successData('点赞成功');
                    }
                    $this->_errorData(1007, '参数错误');
                }else{
                    //取消点赞 修改数据
                    $db_date = NewsPraise::find()->where(array('news_id'=>$news_id, 'user_id'=>$user_info['user_id'], 'news_type'=>$news_type))->one();
                    if(!$db_date) $this->_errorData(1005, '数据匹配失败');
                    $db_date->status = 0;
                    $ret = $db_date->save();
                    if($ret <= 0 || $ret === false) $this->_errorData(1006, '取消点赞失败');
                    $this->_successData('取消点赞成功');
                }
                $this->_errorData(1004, '参数错误');
            }
            $this->_errorData(1002, '获取用户信息失败');
        }
        $this->_errorData(1001, '请求错误');
    }
    
    /**
     * 根据新闻id获取所有新闻的打赏记录
     * @cong.zhao
     * @param news_id  新闻id
     */
    public function actionGetRewardListByNewsId(){
    	$news_id = isset($this->params['news_id'])?$this->params['news_id']:'';
    	$nickname = isset($this->params['nickname'])?$this->params['nickname']:'';
    	$mobile_phone = isset($this->params['mobile_phone'])?$this->params['mobile_phone']:'';
    	$sex = isset($this->params['sex'])?$this->params['sex']:'';
    	$page = isset($_REQUEST['page'])?$_REQUEST['page']:'';
    	$page = (!empty($page) && $page > 0) ? $page : 1;
    	$pageSize = isset($_REQUEST['pageSize'])?$_REQUEST['pageSize']:'';
    	$pageSize = (!empty($pageSize) && $pageSize > 0) ? $pageSize : 10;
    	$pageStart = ($page - 1) * $pageSize;
    	$pageEnd = $page * $pageSize;

    	if(!$news_id){
    		$this->_errorData('0001','参数错误');
    	}
    	
    	$reward_list = NewsReward::GetRewardListByNewsId($news_id, $nickname, $mobile_phone, $sex, $pageStart, $pageEnd);
    	$this->_successData($reward_list, "查选成功");
    }
    
    
    /**
     * 根据直播id获取所有新闻的打赏记录
     * @cong.zhao
     * @param live_id  直播id
     */
    public function actionGetRewardListByLiveId(){
    	$live_id = isset($this->params['live_id'])?$this->params['live_id']:'';
    	$nickname = isset($this->params['nickname'])?$this->params['nickname']:'';
    	$mobile_phone = isset($this->params['mobile_phone'])?$this->params['mobile_phone']:'';
    	$sex = isset($this->params['sex'])?$this->params['sex']:'';
    	$page = isset($_REQUEST['page'])?$_REQUEST['page']:'';
    	$page = (!empty($page) && $page > 0) ? $page : 1;
    	$pageSize = isset($_REQUEST['pageSize'])?$_REQUEST['pageSize']:'';
    	$pageSize = (!empty($pageSize) && $pageSize > 0) ? $pageSize : 10;
    	$pageStart = ($page - 1) * $pageSize;
    	$pageEnd = $page * $pageSize;
    
    	if(!$live_id){
    		$this->_errorData('0001','参数错误');
    	}
    	 
    	$reward_list = NewsReward::GetRewardListByLiveId($live_id, $nickname, $mobile_phone, $sex, $pageStart, $pageEnd);
    	$this->_successData($reward_list, "查选成功");
    }
    
    
    /**
     * 根据用户id获取所有新闻的打赏记录
     * @cong.zhao
     * @param user_id  用户id
     */
    public function actionGetRewardListByUserId(){
    	$user_id = isset($this->params['user_id'])?$this->params['user_id']:'';
    	$keyword = isset($this->params['keyword'])?$this->params['keyword']:'';
    	$source_name = isset($this->params['source_name'])?$this->params['source_name']:'';
    	$creator_name = isset($this->params['creator_name'])?$this->params['creator_name']:'';
    	$page = isset($_REQUEST['page'])?$_REQUEST['page']:'';
    	$page = (!empty($page) && $page > 0) ? $page : 1;
    	$pageSize = isset($_REQUEST['pageSize'])?$_REQUEST['pageSize']:'';
    	$pageSize = (!empty($pageSize) && $pageSize > 0) ? $pageSize : 10;
    	$pageStart = ($page - 1) * $pageSize;
    	$pageEnd = $page * $pageSize;
    	 
    	if(!$user_id){
    		$this->_errorData('0001','参数错误');
    	}
    	 
    	$reward_list = NewsReward::GetRewardListByUserId($user_id, $keyword, $source_name, $creator_name, $pageStart, $pageEnd);
    	$this->_successData($reward_list, "查选成功");
    }
    
    /**
     * 根据用户id获取所有新闻的打赏记录
     * @cong.zhao
     * @param user_id  用户id
     */
    public function actionGetRewardUsersByNewsId(){
    	$news_id = isset($this->params['news_id'])?$this->params['news_id']:''; 
    
    	if(!$news_id){
    		$this->_errorData('0001','参数错误');
    	}
    
    	$user_list = NewsReward::GetRewardUsersByNewsId($news_id);
    	$this->_successData($user_list, "查选成功");
    }
    
    
}