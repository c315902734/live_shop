<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "news_reward".
 *
 * @property string $id
 * @property string $news_id
 * @property string $user_id
 * @property string $hw_money
 * @property integer $create_time
 * @property string $live_id
 */
class NewsReward extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news_reward';
    }

    public static function getDb()
    {
        return yii::$app->vrnews1;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['news_id', 'user_id', 'create_time', 'live_id'], 'integer'],
            [['hw_money'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'news_id' => 'News ID',
            'user_id' => 'User ID',
            'hw_money' => 'Hw Money',
            'create_time' => 'Create Time',
        	'live_id' => 'Live ID',
        ];
    }
    
    /*通过news_id获取打赏列表*/
    public static function GetRewardListByNewsId($news_id = NULL, $nickname = NULL, $mobile_phone = NULL, $sex = '', $pageStart = NULL, $pageEnd = NULL){
    	$returnDara = array('total_count'=>'','list'=>array());
    	$andwhere = '';
    	if($nickname) $andwhere .=" and nickname like '%$nickname%'";
    	if($mobile_phone) $andwhere .=" and mobile_phone like '%$mobile_phone%'";
    	if($sex) $andwhere .=" and sex= ".$sex;

    	if($news_id){
    		$model = new self();
    		$total_count = $model::find()
    		->innerJoin('vruser1.user','vrnews1.news_reward.user_id = vruser1.user.user_id')
    		->where("vrnews1.news_reward.news_id = $news_id  and vruser1.user.status = 1".$andwhere)
    		->count();
    		
    		$list = $model::find()
    		->select([
    			'vruser1.user.user_id',
    			'vruser1.user.nickname',
    			'vruser1.user.mobile_phone',
    			'vruser1.user.sex',
    			'vruser1.user.avatar',
    			'vrnews1.news_reward.create_time',
    			'vrnews1.news_reward.hw_money'
    		])
    		->innerJoin('vruser1.user','vrnews1.news_reward.user_id = vruser1.user.user_id')
    		->where("vrnews1.news_reward.news_id = $news_id  and vruser1.user.status = 1".$andwhere)
    		->orderBy("vrnews1.news_reward.hw_money desc")
    		->offset($pageStart)
    		->limit($pageEnd-$pageStart)
    		->asArray()->all();

    		if($list){
    			foreach($list as $key=>$value){
    				$list[$key]['create_time'] = date('Y',$value['create_time']).'年'.date('m',$value['create_time']).'月'.date('d',$value['create_time']).'日';
    			}
    		}
    		$returnDara['total_count'] = $total_count > 0 ? $total_count : 0;
    		$returnDara['list'] = $list;
    	}
    	return $returnDara;
    }
    
    
    /*通过news_id获取打赏列表*/
    public function GetRewardListByLiveId($live_id = NULL, $nickname = NULL, $mobile_phone = NULL, $sex = '', $pageStart = NULL, $pageEnd = NULL){
    	$returnDara = array('total_count'=>'','list'=>array());
    	$andwhere = '';
    	if($nickname) $andwhere .=" and nickname like '%$nickname%'";
    	if($mobile_phone) $andwhere .=" and mobile_phone like '%$mobile_phone%'";
    	if($sex) $andwhere .=" and sex= ".$sex;
    	
    	if($live_id){
    		$model = new self();
    		$total_count = $model::find()
    		->innerJoin('vruser1.user','vrnews1.news_reward.user_id = vruser1.user.user_id')
    		->where("vrnews1.news_reward.live_id = $live_id  and vruser1.user.status = 1".$andwhere)
    		->count();
    	
    		$list = $model::find()
    		->select([
    				'vruser1.user.user_id',
    				'vruser1.user.nickname',
    				'vruser1.user.mobile_phone',
    				'vruser1.user.sex',
    				'vruser1.user.avatar',
    				'vrnews1.news_reward.create_time',
    				'vrnews1.news_reward.hw_money'
    		])
    		->innerJoin('vruser1.user','vrnews1.news_reward.user_id = vruser1.user.user_id')
    		->where("vrnews1.news_reward.live_id = $live_id  and vruser1.user.status = 1".$andwhere)
    		->orderBy("vrnews1.news_reward.hw_money desc")
    		->offset($pageStart)
    		->limit($pageEnd-$pageStart)
    		->asArray()->all();
    	
    		if($list){
    			foreach($list as $key=>$value){
    				$list[$key]['create_time'] = date('Y',$value['create_time']).'年'.date('m',$value['create_time']).'月'.date('d',$value['create_time']).'日';
    			}
    		}
    		$returnDara['total_count'] = $total_count > 0 ? $total_count : 0;
    		$returnDara['list'] = $list;
    	}
    	return $returnDara;
    }
    
    
    /*通过user_id获取打赏列表*/
    public static function GetRewardListByUserId($user_id = NULL, $keyword = NULL, $source_name = NULL, $creator_name = NULL, $pageStart = NULL, $pageEnd = NULL){
    	$returnDara = array('total_count'=>'','list'=>array());
    	
    	$news_andwhere = '';
    	$live_andwhere = '';
    	if($keyword){
    		$news_andwhere .=" and title like '%$keyword%'";
    		$live_andwhere .=" and name like '%$keyword%'";
    	}
    	if($source_name) $news_andwhere .=" and source_name like '%$source_name%'";
    	if($creator_name) $news_andwhere .=" and creator_name like '%$creator_name%'";
    	
    	$model = new self();
    	$news_reward_list = $model::find()->select(['id','create_time','hw_money','news_id','live_id'])->where(['user_id'=>$user_id])->orderBy("hw_money desc")->asArray()->all();
    	$new_news_reward_list = array();
    	if($news_reward_list){
    		foreach($news_reward_list as $key=>$value){
    			if($value['news_id']){
    				$news_info = News::find()->select(['news_id','type','title','app_pub','web_pub','creator_name','source_name'])->where("news_id = ".$value['news_id'].$news_andwhere)->asArray()->one();
    				if($news_info){
    					$news_reward_list[$key]['type'] = $news_info['type'];
    					$news_reward_list[$key]['info'] = $news_info;
    				}else{
    					unset($news_reward_list[$key]);
    					continue;
    				}
    			}elseif($value['live_id']){
    				$live_info = Live::find()->select(['live_id','name as title'])->where("live_id = ".$value['live_id'].$live_andwhere)->asArray()->one();
    				if($live_info){
    					$news_reward_list[$key]['type'] = '8';
    					$news_reward_list[$key]['info'] = $live_info;
    				}else{
    					unset($news_reward_list[$key]);
    					continue;
    				}
    			}
    			
    			if($value['create_time']) $news_reward_list[$key]['create_time'] = date('Y',$value['create_time']).'年'.date('m',$value['create_time']).'月'.date('d',$value['create_time']).'日';
    			$new_news_reward_list[] = $news_reward_list[$key];
    		}
    	}


    	//键值从新排序
    	$new_data  = array_slice($new_news_reward_list,$pageStart,($pageEnd-$pageStart));
    	$returnDara = array('total_count'=>count($news_reward_list),'list'=>$new_data);
    	return $returnDara;
    	
    	
//     	$andwhere = '';
//     	if($keyword) $andwhere .=" and news.title like '%$keyword%'";
//     	if($source_name) $andwhere .=" and news.source_name like '%$source_name%'";
//     	if($creator_name) $andwhere .=" and news.creator_name like '%$creator_name%'";
    	
//     	if($user_id){
//     		$model = new self();
    		
//     		$total_count = $model::find()
//     		->innerJoin('news','news_reward.news_id = news.news_id')
//     		->where("news_reward.user_id = $user_id".$andwhere)
//     		->count();
    		
    		
//     		$list = $model::find()
//     		->select([
//     			'news.news_id',
//     			'news.type',
//     			'news.title',
//     			'news.app_pub',
//     			'news.web_pub',
//     			'news.creator_name',
//     			'news.source_name',
//     			'news_reward.create_time',
//     			'news_reward.hw_money',
//     		])
//     		->innerJoin('news','news_reward.news_id = news.news_id')
//     		->where("news_reward.user_id = $user_id".$andwhere)
//     		->orderBy("news_reward.hw_money desc")
//     		->offset($pageStart)
//     		->limit($pageEnd-$pageStart)
//     		->asArray()->all();
    		
//     		if($list){
//     			foreach($list as $key=>$value){
//     				$list[$key]['create_time'] = date('Y',$value['create_time']).'年'.date('m',$value['create_time']).'月'.date('d',$value['create_time']).'日';
//     			}
//     		}
//     		$returnDara['total_count'] = $total_count > 0 ? $total_count : 0;
//     		$returnDara['list'] = $list;
//     	}
//     	return $returnDara;
    }
    
    public static function GetRewardUsersByNewsId($news_id = NULL){	
    	$info = array();
    	//打赏总数和打赏人员列表
    	$reward_count = NewsReward::find()->select(['news_reward.id'])->innerJoin('news','news_reward.news_id = news.news_id')->where(['news_reward.news_id'=>$news_id])->count();
    	$info['total_count'] =  $reward_count > 0 ? $reward_count : '0';
    	$reward_users = NewsReward::find()
    	->select(['vruser1.user.user_id','vruser1.user.nickname as nick_name','vruser1.user.avatar'])
    	->innerJoin('vrnews1.news','vrnews1.news_reward.news_id = vrnews1.news.news_id')
    	->innerJoin('vruser1.user','vrnews1.news_reward.user_id = vruser1.user.user_id')
    	->where(['vrnews1.news_reward.news_id'=>$news_id,'vruser1.user.status'=>'1'])
    	->groupBy('vrnews1.news_reward.user_id')
    	->asArray()
    	->all();
    	$info['list'] = !empty($reward_users) ? $reward_users : array();
    	return $info;
    }
    
}
