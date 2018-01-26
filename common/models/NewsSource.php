<?php

namespace common\models;

use Yii;
use yii\db\Query;
use common\service\Record;

/**
 * This is the model class for table "news_source".
 *
 * @property integer $source_id
 * @property string $name
 * @property string $create_time
 * @property integer $creator_id
 */
class NewsSource extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news_source';
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
            [['create_time'], 'safe'],
            [['creator_id'], 'integer'],
            [['name'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'source_id' => 'Source ID',
            'name' => 'Name',
            'create_time' => 'Create Time',
            'creator_id' => 'Creator ID',
        ];
    }
    
    
    /**
     * 获取订阅列表
     * @zc
     */
    public static function  getSourceList($list_ids = '', $pageStart, $pageEnd, $keyword = null,$user_id = null, $is_pc = '0'){
    	$list_ids = rtrim($list_ids,',');
    	$returnData = array();
    	$query = new Query();
    	if($keyword){
    		$query->select('news_source.*')
    		->from('news_source')->innerJoin("news","news_source.source_id = news.source_id")
    		->where("news.news_id is not null and  news.weight >=70 and news.status=0 and news_source.name like '%$keyword%' ");
    		if($list_ids) $query->andwhere("news_source.source_id not in ($list_ids)");
    		$query->groupBy("news_source.source_id");
    		$command = $query->createCommand(self::getDb());
    		$source_list = $command->queryAll();
    	}else{
    		$query->select('news_source.*')
    		->from('news_source')->innerJoin("news","news_source.source_id = news.source_id")
    		->where("news.news_id is not null and  news.weight >=70 and news.status=0 ");
    		if($list_ids) $query->andwhere("news_source.source_id not in ($list_ids)");
    		$query->groupBy("news_source.source_id");
    		if($is_pc == '0'){
    			$query->offset(0);
    		}else{
    			$query->offset($pageStart);
    		}
    		$query->limit($pageEnd-$pageStart);
    		$command = $query->createCommand(self::getDb());
    		$source_list = $command->queryAll();
    	}
    	foreach($source_list as $key=>$value){
    		//登录用户是否订阅
    		$user_subscribe_count = UserSubscribeSource::find()->where(['source_id'=>$value['source_id'],'user_id'=>$user_id,'status'=>1])->count();
    		if($user_subscribe_count > 0){
    			$source_list[$key]['is_subscribe'] = '1';
    		}else{
    			$source_list[$key]['is_subscribe'] = '0';
    		}
    		$source_list[$key]['subscribe_count'] = self::getRealCount(UserSubscribeSource::find()->where(['source_id'=>$value['source_id']])->count());
    	}
    	//数据总数
    	$totalCount = count($source_list);
    	$returnData['totalCount'] = $totalCount;
    	$returnData['list'] = $source_list;
    	return $returnData;
    }
    
    
    /**
     * 获取订阅号详情
     * @zc
     */
    public static function getSourceDetail($source_id, $pageStart, $pageEnd, $keyword = null, $user_id = null,$is_pc){
    	$returnData = array();
    	$query = new Query();
    	$query->select([
    			"news.news_id",
    			"news.title",
    			"news.subtitle",
    			"news.source_id",
    			"news.source_name",
    			"news.type",
    			"news.column_id",
    			"news.type_id",
    			"news.abstract as news_abstract",
    			"news.cover_image",
    			"news.reference_id",
    			"news.reference_type",
    			"news.outer_url",
    			"news.special_news_id",
    			"news.top_status",
    			"news.full_status",
    			"news.full_title",
    			"news.full_subtitle",
    			"news.full_cover_image",
    			"news.create_time",
    			"news.special_entry",
    			"news.special_title",
    			"news.special_abstract",
    			"news.special_image",
    			'case `news`.`type`  when 5 then `news`.`content` else "" end as content',
    			"news_video.play_count",
    			"news_video.category",
    			"news_video.duration"
    	])
    	->from('news')->leftJoin("news_video","news.news_id = news_video.news_id")
    	->where("news.source_id = ".$source_id." and news.weight>=70 and news.status=0 ");
    	if($keyword) $query->andWhere("news.title like '%$keyword%' ");
    	//$query->orderBy("news.weight desc,news.create_time desc");
    	$query->orderBy("news.create_time desc");
    	$count = $query->count('*',self::getDb());
    	$returnData['totalCount'] = $count;
    	$query->offset($pageStart);
    	$query->limit($pageEnd-$pageStart);
    	$command = $query->createCommand(self::getDb());
    	$news_info = $command->queryAll();
    	
    	if($news_info){
    		if($user_id){
    			$subscribe_count = UserSubscribeSource::find()->where(['source_id'=>$source_id,'user_id'=>$user_id,'status'=>1])->count();
    			if($subscribe_count>0){
    				$is_subscribe = '1';
    			}else{
    				$is_subscribe = '0';
    			}
    		}else{
    			$is_subscribe = '0';
    		}
    		$returnData['is_subscribe'] = $is_subscribe;
    		foreach($news_info as $key=>&$val){
    			/* 修复我的订阅中视频新闻不显示时长 */
    			if($val['reference_id'] && $val['reference_type'] == 1 && $val['type'] == 4){
    				$_video_news_info = NewsVideo::find()->where(['news_id'=>$val['reference_id']])->asArray()->one();
    				$val['duration']  = $_video_news_info['duration'];
    			}

				$news_info[$key] = NewsUserCollect::getcheckinfo($news_info[$key],$is_pc);

    		}
    		unset($val);
    	}
    	$returnData['list'] = $news_info;
    	return $returnData;
    }
    
    
    /**
     *根据数量转换显示内容
     */
    public static function getRealCount($count){
    	$realcount = '';
    	if(!empty($count)){
    		if($count < 10000){
    			$realcount = $count;
    		}else{
    			$realcount = round($count/10000,'1').'万';
    		}
    	}
    	return $realcount;
    }
    
    
}
