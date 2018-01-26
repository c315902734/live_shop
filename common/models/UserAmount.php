<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use common\models\OauthAccessTokens;

/**
 * UserVerifyCode model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class UserAmount extends \yii\db\ActiveRecord
{
	
	public static $taskArr = array(
			'1' => 1,
			'2' => 1,
			'3' => 1,
			'4' => 1,
			'5' => 3,
			'6' => 30,
            '7' => 999, //投票增加汇闻币
	);
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_amount';
    }


    public static function getDb(){
        return Yii::$app->vruser1;
    }
    
    /**
     * 用户消费记录
     */
    public static function addUserAmount($param){
    	$user_info = User1::find()->where(['user_id'=>$param['user_id']])->one();
    	if(!$user_info){
    		return false;
    	}
    	if(!empty($param['task_id'])){ //做任务
    		$user_task = new UserTask();
    		$count = UserTask::find()->where(['user_id'=>$param['user_id'],'task_id'=>$param['task_id']])->andWhere(['>=','created_at',date('Y-m-d',time()).' 00:00:00'])->andWhere(['<','created_at',date('Y-m-d',time()).' 23:59:59'])->count();

    		if($count < self::$taskArr[$param['task_id']]){
    			$user_task->task_id = $param['task_id'];
    			$user_task->user_id = $param['user_id'];
    			$user_task->amount = $param['operate_cnt'];
    			$user_task->created_at = date('Y-m-d H:i:s', time());
    			$user_task->user_task = time().rand(0,9);
    			$user_task->save();
    		}else{
    			return false;
    		}
    	}
    	$user_amount = new UserAmount();
    	$user_data = array();
    	$user_amount->user_id = $param['user_id'];
    	$user_amount->operate_cnt = $param['operate_cnt'];
    	 
    	 
    	if($param['operate'] == 1){ //获得
    		$user_amount->surplus = $user_info->amount + $param['operate_cnt'];
    		$user_info->amount = $user_info->amount + $param['operate_cnt']; //更新用户的汇闻币总数
    	}elseif ($param['operate'] == 2){ //消耗
    		if($user_info->amount < $param['operate_cnt']){
    			return false;
    		}
    		$user_amount->surplus = $user_info->amount - $param['operate_cnt'];
    		$user_info->amount = $user_info->amount - $param['operate_cnt']; //更新用户的汇闻币总数
    	}
    	$user_amount->operate = $param['operate'];
    	$user_amount->operate_name = $param['operate_name'];
    	$user_amount->created_at = date('Y-m-d H:i:s', time());
    	$user_amount->save(); //增加记录
    	$user_info->save();
    	return array('amount'=>$user_info->amount);
    }

	/**
	 * 获取用户金币明细
	 */
	public static function getUserAmountList($user_id, $page, $size){
		$offset = $size*($page - 1);
		$data['list']  = static::find()->where(['user_id'=>$user_id])->orderBy("created_at desc,id desc")
			->limit($size)->offset($offset)->asArray()->all();
		$data['count'] = static::find()->where(['user_id'=>$user_id])->count();
		$data['totalCount'] = $data['count'];
		$data['user_id'] = $user_id;
		return $data;
	}
}
