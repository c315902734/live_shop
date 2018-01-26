<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "news_comment_like".
 *
 * @property string $like_id
 * @property string $comment_id
 * @property string $user_id
 * @property string $create_time
 * @property string $update_time
 * @property integer $status
 */
class NewsCommentLike extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news_comment_like';
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
            [['user_id', 'status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['comment_id'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'like_id' => 'Like ID',
            'comment_id' => 'Comment ID',
            'user_id' => 'User ID',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'status' => 'Status',
        ];
    }
    
    /**
     * 评论/回复点赞
     */
    public static function zanCommentList($user_id = null,$comment_id = null){
    	$returnData = array();
    	if($user_id && $comment_id){
    		$comment_like_model = self::find()->where(['comment_id'=>$comment_id,'user_id'=>$user_id])->one();
    		if($comment_like_model){
    			$data = array();
    			$comment_like_model->status = $comment_like_model->status == 1 ? 0 : 1;
    			$rid = $comment_like_model->save();
    			if($rid){
    				$model = NewsComment::find()->where(['comment_id'=>$comment_id])->one();
    				if($comment_like_model->status == '1'){
    					$model->like_count +=1;
    				}else{
    					$model->like_count =$model->like_count-1;
    				}
    				$model->save();
    			}
    		}else{
    			$comment_like_model = new NewsCommentLike();
    			$comment_like_model->like_id = time().self::getRange();
    			$comment_like_model->comment_id = $comment_id;
    			$comment_like_model->user_id = $user_id;
    			$comment_like_model->create_time = date('Y-m-d H:i:s',time());;
    			$comment_like_model->update_time = date('Y-m-d H:i:s',time());;
    			$comment_like_model->status = 1;
    			if($comment_like_model->save()){
    				$model = NewsComment::find()->where(['comment_id'=>$comment_id])->one();
    				$model->like_count +=1;
    				$model->save();
    			}
    		}
    		
    		$comment_model = new NewsComment();
    		
    		$comment_info= NewsComment::find(['like_count'])->where(['comment_id'=>$comment_id])->one();
    		$returnData['is_zan'] = $comment_like_model->status;
    		$returnData['like_count'] = $comment_info->like_count;
    		return $returnData;

    	}
    }
    
    public static  function getRange($cnt=9){
    	$numbers = range (1,$cnt);
    	//播下随机数发生器种子，可有可无，测试后对结果没有影响
    	srand ((float)microtime()*1000000);
    	shuffle ($numbers);
    	//跳过list第一个值（保存的是索引）
    	$n = '';
    	while (list(, $number) = each ($numbers)) {
    		$n .="$number";
    	}
    	return $n;
    }
    
}
