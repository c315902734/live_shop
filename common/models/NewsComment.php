<?php

namespace common\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "news_comment".
 *
 * @property string $comment_id
 * @property string $content
 * @property string $news_id
 * @property string $user_id
 * @property string $to_comment_id
 * @property integer $like_count
 * @property string $create_time
 * @property string $update_time
 * @property integer $status
 */
class NewsComment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news_comment';
    }
    
    public static function getDb(){
    	return Yii::$app->vrnews1;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['news_id', 'user_id', 'like_count', 'status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['to_comment_id'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'comment_id' => 'Comment ID',
            'content' => 'Content',
            'news_id' => 'News ID',
            'user_id' => 'User ID',
            'to_comment_id' => 'To Comment ID',
            'like_count' => 'Like Count',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'status' => 'Status',
        ];
    }
    
    /**
     * 获取评论列表
     */
    public static function getCommentList($user_id = null, $news_id,$comment_id = null, $pageStart, $pageEnd,$status){
    	$returnData = array();
    	$where = array();
    	$where['news_comment.news_id'] = $news_id;
    	//$where['news_comment.user_id'] = $user_id;
    	$where['news_comment.status']  = '1';
		$where['news_comment.is_show'] = '0';
    	$query = new Query();
    	$query->select(
    			    'news_comment.comment_id,
					news_comment.to_comment_id,
					news_comment.content,
					news_comment.user_id,
					news_comment.like_count,
				    news_comment.create_time,
					vruser1.user.nickname,
					vruser1.user.avatar')
    	->from('news_comment')->innerJoin("vruser1.user","news_comment.user_id = vruser1.user.user_id")
    	->where($where);
    	if($comment_id) $query->andwhere('news_comment.to_comment_id = '.$comment_id);
    	$query->orderBy("news_comment.create_time desc");
    	$count = $query->count('*',self::getDb());
    	$returnData['totalCount'] = $count;
    	$query->offset($pageStart);
    	$query->limit($pageEnd-$pageStart);
    	$command = $query->createCommand(self::getDb());
		$comment_list = $command->queryAll();
    	foreach($comment_list as $key=>$value){
    			
    		//被回复信息
    		if($value['to_comment_id'] && !$comment_id){
    			$where1['comment_id'] = $value['to_comment_id'];
				$where1['is_show'] = '0';
    			$query = new Query();
    			$query->select(
    			    		'news_comment.comment_id,
							news_comment.content,
							news_comment.user_id,
							news_comment.like_count,
						    news_comment.create_time,
							vruser1.user.nickname,
							vruser1.user.avatar')
		    	->from('news_comment')->innerJoin("vruser1.user","news_comment.user_id = vruser1.user.user_id")
		    	->where($where1);
		    	$command = $query->createCommand(self::getDb());
				$to_comment_content_model = $command->queryOne();
    			if($to_comment_content_model){
    				$comment_list[$key]['to_comment']['to_comment_content'] = $to_comment_content_model['content'];
    				$comment_list[$key]['to_comment']['to_comment_nickname'] = $to_comment_content_model['nickname'];
    				$comment_list[$key]['to_comment']['to_comment_avatar'] = $to_comment_content_model['avatar'];
    			}
    		}
    		//判断当前用户是否针对评论/回复列表已经点过赞
    		if($user_id){
    			$user_comment_like_count = NewsCommentLike::find()->where(['comment_id'=>$value['comment_id'],'user_id'=>$user_id,'status'=>'1'])->count();
    			if($user_comment_like_count > 0){
    				$comment_list[$key]['is_zan'] = '1';
    			}else{
    				$comment_list[$key]['is_zan'] = '0';
    			}
    		}
    		$comment_list[$key]['real_time'] = self::getRealTime($value['create_time']);
    		$returnData[] = $comment_list[$key];
    	}

		if($status == 1 ){
			$query = new Query();
			$query->select('count(*) as count')
				->from('news_comment')->innerJoin("vruser1.user","news_comment.user_id = vruser1.user.user_id")
				->where($where);
			if($comment_id) $query->andwhere('news_comment.to_comment_id = '.$comment_id);
			$command = $query->createCommand(self::getDb());
			$comment_list = $command->queryAll();

			$res = array();
			$count_list          = $comment_list[0]['count'];
			$res['totalCount']        = $count_list;
			$res['comment_list'] = $returnData;
			return $res;
		}else{
			return $returnData;
		}
    }
    
    
    /**
     * 删除评论/回复
     */
    
    function delCommentList($user_id = null,$comment_id = null){
    	if($comment_id && $user_id){
    		//判断如果当前操作者非评论/回复的发布者删除失败
    		$model = new self();
    		$comment_model = $model->find()->where(['user_id'=>$user_id,'comment_id'=>$comment_id])->one();
    		if($comment_model){
    			$comment_model->status = 0;
    			$hid = $comment_model->save();
    			if($hid !==false){
    				//如果存在回复的话也一并删除
    				$children_count = $model::find()->where(['to_comment_id'=>$comment_id])->count();
    				if($children_count > 0){
    					$model::updateAll(['status'=>'0'],['to_comment_id'=>$comment_id]);
    				}
    				return TRUE;
    			}
    		}
    	}
    	return false;
    }
    
    
    /**
     *根据时间获取此时的比对时间
     *
     */
    public static function  getRealTime($date){
    	$realtime = '';
    	if($date){
    		if(time() - strtotime($date) < 3600){
    			$realtime = round((time() - strtotime($date))/60)."分钟前";
    		}elseif((time() - strtotime($date) > 3600) && (time() - strtotime($date) < 86400)){
    			$realtime = round((time() - strtotime($date))/3600)."小时前";
    		}else{
    			$realtime = date('Y-m-d',strtotime($date));
    		}
    	}
    	return $realtime;
    }
    
}
