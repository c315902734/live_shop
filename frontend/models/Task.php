<?php
namespace frontend\models;
use Yii;
use yii\db\Query;

class Task extends \yii\db\ActiveRecord{
	public static function tableName()
	{
		return 'user';
	}
	
	public static function getDb(){
		return Yii::$app->vruser1;
	}
	
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
				[['user_id'], 'required'],
				[['user_id', 'sex', 'province_id', 'area_id', 'location_status', 'user_type', 'score', 'amount', 'status'], 'integer'],
				[['birthday', 'register_time', 'login_time'], 'safe'],
				[['username', 'login_token'], 'string', 'max' => 45],
				[['nickname'], 'string', 'max' => 30],
				[['mobile_phone'], 'string', 'max' => 15],
				[['password'], 'string', 'max' => 35],
				[['rcloud_token'], 'string', 'max' => 120],
				[['avatar'], 'string', 'max' => 200],
				[['open_id'], 'string', 'max' => 64],
				[['username'], 'unique'],
				[['mobile_phone'], 'unique'],
		];
	}
	
    /**
     * 获取任务列表
     */
    public function taskList($user_id){
        $query = new Query();
        
        if(!empty($user_id)){
        	$user_info = $query->from('vruser1.user')->where(['user_id'=>$user_id])->one();
        	if(!$user_info){
        		return 'user does not exist';
        	}
        }
        $start_time = date('Y-m-d', time()).' 00:00:00';
        $end_time   = date('Y-m-d', time()).' 23:59:59';
        $today_task = $query
        				->from('vruser1.user_task')
				        ->where("created_at >= '".$start_time."' and  created_at < '".$end_time."'")
				        ->count();
        
        if($today_task > 0){
        	$list = $query->from('vruser1.task')->join('left join', 'vruser1.user_task t on t.task_id = vruser1.task.id and t.user_id = "'.$user_id.'"
        			and (t.created_at >= "'.$start_time.'" and  t.created_at < "'.$end_time.'")')
        	        ->groupBy('task.id')->select('task.id,task_name,task.amount,max_cnt,sum(t.amount) as num')
        	        ->orderBy("task.id asc")->all();
        }else{
        	$list = $query->field('task.id,task_name,task.amount,max_cnt,0 as num')
        	->order("task.id asc")->select();
        }
        return $list;
    }
}