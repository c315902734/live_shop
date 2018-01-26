<?php

namespace common\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "user_subscribe_source".
 *
 * @property string $subscribe_id
 * @property string $user_id
 * @property integer $source_id
 * @property string $create_time
 * @property string $update_time
 * @property integer $status
 */
class UserSubscribeSource extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_subscribe_source';
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
            [['user_id', 'source_id', 'status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'subscribe_id' => 'Subscribe ID',
            'user_id' => 'User ID',
            'source_id' => 'Source ID',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'status' => 'Status',
        ];
    }
    
    /**
     * 我的订阅列表
     */
    function getSubscribeList($user_id = null, $pageStart,$pageEnd){
    	$returnData = array();
    	if($user_id){
    		$where = array();
    		$where['user_subscribe_source.user_id'] = $user_id;
    		$where['user_subscribe_source.status'] = 1;
    		
    		$query = new Query();
    		$query->select(
    				'user_subscribe_source.subscribe_id,
					user_subscribe_source.source_id,
					user_subscribe_source.create_time,
					news_source.name')
    		->from('user_subscribe_source')->innerJoin("news_source","user_subscribe_source.source_id = news_source.source_id")
    		->where($where)
    		->orderBy("user_subscribe_source.create_time desc")
    		->groupBy("user_subscribe_source.source_id");
    		$count  = $query->count('*',self::getDb());
    		$returnData['totalCount'] = $count;
    		$query->offset($pageStart);
    		$query->limit($pageEnd-$pageStart);
    		$command = $query->createCommand(self::getDb());
    		$collect_list = $command->queryAll();

    		foreach($collect_list as $key=>$value){
    			$new_info = News::find()->where(['source_id'=>$value['source_id'],'status'=>0])->orderBy('create_time desc')->asArray()->one();
    			if($new_info){
    				$collect_list[$key]['title'] = $new_info['title'];
    				$collect_list[$key]['create_time'] = $new_info['create_time'];
    			}
    			$collect_list[$key]['real_time'] = self::getRealTime($collect_list[$key]['create_time']);
    			$returnData[] = $collect_list[$key];
    		}
    	}
    	return $returnData;
    }
    
    /**
     * 删除订阅
     */
    
    function delSubscribeList($user_id = null,$source_id = null){
    	if($source_id && $user_id){
    		$model = new self();
    		//判断如果当前操作者非订阅的的操作者删除失败
    		$subscribe_model = $model->find()->where(['user_id'=>$user_id,'source_id'=>$source_id])->one();
    		if($subscribe_model){
    			$subscribe_model->status = 0;
       			$rid = $subscribe_model->save();
    			if($rid !==false){
    				return true;
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
