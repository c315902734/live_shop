<?php

namespace common\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "task".
 *
 * @property string $id
 * @property string $task_name
 * @property string $content
 * @property integer $amount
 * @property integer $max_cnt
 */
class Task extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'task';
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
            [['amount', 'max_cnt'], 'integer'],
            [['task_name', 'content'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_name' => 'Task Name',
            'content' => 'Content',
            'amount' => 'Amount',
            'max_cnt' => 'Max Cnt',
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
    	//->createCommand()->getRawSql();
    	
    	if($today_task > 0){
    		/* $list = $query->from('vruser1.task')->join('left join', 'vruser1.user_task t on t.task_id = vruser1.task.id and t.user_id = "'.$user_id.'"
        			and (t.created_at >= "'.$start_time.'" and  t.created_at < "'.$end_time.'")')
            			->groupBy('task.id')->select('task.id,task_name,task.amount,max_cnt,sum(t.amount) as num')
            			->orderBy("task.id asc")->all(); */
    		
            $list = Task::find()->join('left join','vruser1.user_task t', 't.task_id = vruser1.task.id and user_id = "'.$user_id.'"
  							      and (t.created_at >= "'.$start_time.'" and  t.created_at < "'.$end_time.'")')
            			        ->groupBy('task.id')->select('task.id,task_name,task.amount,max_cnt,sum(t.amount) as num')
            			        ->orderBy("task.id asc")
            			        ->asArray()
            					->all();
            					//->createCommand()->getRawSql();
//             return $list;
            
    	}else{
    		$list = $query->from('vruser1.task')->select('id,task_name,task.amount,max_cnt')->where('1')
    		->orderBy("id asc")->all();
    	}
    	return $list;
    }
}
