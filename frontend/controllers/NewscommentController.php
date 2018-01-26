<?php
namespace frontend\controllers;
use OAuth2\Request;
use Yii;
use common\models\NewsComment;
use common\models\UserAmount;
use yii\db\Query;

/**
 * 评论相关接口
 */
class NewscommentController extends BaseApiController
{
	
	
	/**
	 * 添加评论/回复
	 * @cong.zhao
	 */
	function actionCommentAdd(){
		$content = isset($this->params['content'])?$this->params['content']:'';
		$news_id = isset($this->params['news_id'])?$this->params['news_id']:'';
		$user_id = isset($this->params['user_id'])?$this->params['user_id']:'';
		$to_comment_id = isset($this->params['to_comment_id'])?$this->params['to_comment_id']:'0';
		$ReturnData = array();

		if(!$news_id || !$user_id || !$content){
			$this->_errorData('0001','参数错误');
		}
		//验证当前登录的用户和传递的user_id是否一致
		$user_model = $this->_getUserModel();
		if($user_model->user_id != $user_id){
			$this->_errorData('0002','用户信息错误');
		}

		//验证 用户评论间隔时长 是否超过15秒
		$befor_comment = NewsComment::find()->where(['user_id'=>$user_id])->orderBy("create_time desc")->asArray()->one();
		$befor_time = strtotime($befor_comment['create_time']);
		$now_time = time();
		if(($now_time - $befor_time) < 15){
			$this->_errorData('0090','您的回复太快了！15秒后才可再次回复。');
		}

		if(mb_strlen(trim($content),'UTF8') > 120){
			$this->_errorData('0003','字数超过限制');
		}elseif(!$this->preg_content($content)){
			$this->_errorData('3333','优质的评论   更能扩大影响力');
		}else{
			// token有效
			$model = new NewsComment();
			$model->comment_id = time().$this->getRange();
			$model->content = $this->userTextEncode($this->filter_words($content));
			$model->news_id = $news_id;
			$model->user_id = $user_id;
			$model->to_comment_id = $to_comment_id;
			$model->like_count = 0;
			$model->create_time = date('Y-m-d H:i:s',time());
			$model->update_time = date('Y-m-d H:i:s',time());
			$model->status = 1;
			if($model->save()){
				if($to_comment_id){
					$query = new Query();
					$query->select(
							'news_comment.content,
							vruser1.user.nickname,
							vruser1.user.avatar')
					->from('news_comment')->innerJoin("vruser1.user","news_comment.user_id = vruser1.user.user_id")
					->where('news_comment.comment_id = '.$to_comment_id);
					$command = $query->createCommand(NewsComment::getDb());
					$to_comment_content_model = $command->queryOne();
					if($to_comment_content_model){
						$returnData['to_comment_content']  = $this->userTextDecode($to_comment_content_model['content']);
						$returnData['to_comment_nickname'] = $this->userTextDecode($to_comment_content_model['nickname']);
						$returnData['to_comment_avatar']   = $to_comment_content_model['avatar'];
					}
				}
				$returnData['content'] = $this->userTextDecode($content);
	
				$param['user_id']     = $user_id;
				$param['operate_cnt'] = '5';
				$param['operate']     = '1';
				$param['operate_name'] = '新闻评论';
				$param['task_id']      = '4';
				UserAmount::addUserAmount($param);
				$this->_successData($returnData, "提交成功");
			}else{
				$this->_errorData('0002','提交失败');
			}
		}
	}
	
	
	/**
	 * 删除评论/回复
	 * @cong.zhao
	 */
	function actionCommentDel(){
		$user_id = isset($this->params['user_id'])?$this->params['user_id']:'';
		$comment_id = isset($this->params['comment_id'])?$this->params['comment_id']:'';
		$ReturnData = array();
		if(!$comment_id || !$user_id){
			$this->_errorData('0001','参数错误');
		}else{
			$result = NewsComment::delCommentList($user_id,$comment_id);
			if(!$result){
				$this->_errorData('0002','提交失败');
			}else{
				$this->_successData($ReturnData, "删除成功");
			}
		}
	}


	/*
	 * 评论回复内容 正则过滤
	 * */
	function preg_content($content){
		$content = trim($content); //去掉两边空格
		//中文标点
		$char = "。、！？：；﹑•＂…‘’“”〝〞∕¦‖—　〈〉﹞﹝「」‹›〖〗】【»«』『〕〔》《﹐¸﹕︰﹔！¡？¿﹖﹌﹏﹋＇´ˊˋ―﹫︳︴¯＿￣﹢﹦﹤‐­˜﹟﹩﹠﹪﹡﹨﹍﹉﹎﹊ˇ︵︶︷︸︹︿﹀︺︽︾ˉ﹁﹂﹃﹄︻︼（） ，+-~！@#￥%……&*（）{}|：“”《》？ ";

		if(preg_match("/^[a-zA-Z\d]*[[:space:]]+/",$content)){
//                    echo 555;die;
			return true;
		}else if (preg_match("/^([\x{4e00}-\x{9fa5}])/u",$content)) { //utf8 纯汉字少于6个汉字
			if(mb_strlen($content) < 6){
//                        echo 1;die;
				return false;
			}else{
				return true;
			}
		} else if (preg_match("/^[a-zA-Z]+$/", $content)) { //纯字母
//                    echo 2;die;
			return false;
		} else if(preg_match("/^\d+$/", $content)){ // 纯数字
//                    echo 3;die;
			return false;
		}else if(preg_match("/^([[:punct:]]|[[:space:]])+$/", $content)){
//                    echo 4;die;
			return false;
		}else if(preg_match("/^([".$char."]|[[:space:]])[^a-zA-Z\d]+$/",$content)){
//                    echo 42;die;
			return false;
		}else if (preg_match("/^\[em_(\d+)\]+$/", $content)) { //表情特殊符号
//                    echo 5;die;
			return false;
		}else if (preg_match("/^(\\\[a-zA-Z\d]+)+$/", $content)) { //纯 表情符号 \ude31
//                    echo 6;die;
			return false;
		} else if (preg_match("/^([a-zA-Z\d]|[[:punct:]])*$/", $content)) { //穿插错乱
//                    echo 7;die;
			return false;
		}else{
//			echo 43;
			return true;
		}
	}

}