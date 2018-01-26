<?php
namespace common\models;

use Yii;
use common\models\OauthAccessTokens;
use yii\db\Query;

/**
 * News model
 */
class NewsUserCollect extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news_user_collect';
    }


    public static function getDb()
    {
        return Yii::$app->vrnews1;
    }
    
    /**
     * 收藏列表
     * @zc
     */
    function getCollectList($user_id = null, $type,$pageStart,$pageEnd,$is_pc){
    	$returnData = array();
    	if($user_id){
    		$query = new Query();
    		$where = array();
    		$where['news_user_collect.user_id'] = $user_id;
    		$where['news_user_collect.type'] = $type;
    		$where['news_user_collect.status'] = 1;
    		switch($type){
    			case 1:
    				$query->select([
    						"news_user_collect.collect_id",
    						"news_user_collect.create_time as collect_create_time",
    						"news_user_collect.update_time as collect_update_time",
    						"news.news_id",
    						"news.abstract as news_abstract",
    						"news.title",
    						"news.subtitle",
    						"news.content",
    						"news.cover_image",
    						"news.reference_type",
    						"news.reference_id",
    						"news.type",
    						"news.column_id",
    						"news.area_id",
    						"DATE_FORMAT(news.create_time,'%Y/%m/%d %H:%i') as create_time",
    						"news.type_id",
    						"news.special_news_id",
    						"news.top_status",
    						"news.full_status",
    						"news.full_title",
    						"news.full_subtitle",
    						"news.full_cover_image",
    						"news.source_id",
    						"news.source_name",
    						"news.special_id",
    						"news.special_type",
    						"news.special_entry",
    						"news.special_title",
    						"news.special_abstract",
							"news.special_image",
					])
    				->from('news_user_collect')->innerJoin("news","news_user_collect.news_id = news.news_id")
    				->where($where)
    				->andwhere("news.type in (1,2,5,6,7)  AND news.weight >= 70 AND news.status=0");
					$count = $query->count('*',self::getDb());
    				$query->orderBy([
    						'case  when `collect_update_time` is null then `collect_create_time` else `collect_update_time` end'    => SORT_DESC
    				]);

    					$query->offset($pageStart);
        				$query->limit($pageEnd-$pageStart);
        				$command = $query->createCommand(self::getDb());
        				$collect_list = $command->queryAll();
        				break;
        			case 2:
        				$where['news.type'] = 4;
        				$query->select([
        						"news_user_collect.collect_id",
    							"news_user_collect.collect_id",
    							"news_user_collect.create_time as collect_create_time",
    							"news_user_collect.update_time as collect_update_time",
    							"news.news_id",
    							"news.abstract as news_abstract",
    							"news.title",
    							"news.subtitle",
    							"news.content",
    							"news.cover_image",
    							"news.reference_type",
    							"news.reference_id",
    							"news.type",
    							"news.column_id",
    							"news.area_id",
    							"DATE_FORMAT(news.create_time,'%Y/%m/%d %H:%i') as create_time",
    							"news.type_id",
    							"news.special_news_id",
    							"news.top_status",
    							"news.full_status",
    							"news.full_title",
    							"news.full_subtitle",
    							"news.full_cover_image",
    							"news.source_id",
    							"news.source_name",
    							"news.special_id",
    							"news.special_type",
    							"news.special_entry",
    							"news.special_title",
    							"news.special_abstract",
    							"news.special_image",
    							"news_video.thumbnail_url",
    							"news_video.duration",
    							"news_video.play_count",
    							"news_video.category",
    							"news_video.video_url as video_url",
    							"news_video.`file_id` as file_id",
								])
        				->from('news_user_collect')
        				->innerJoin("news","news_user_collect.news_id = news.news_id")
        				->innerJoin("news_video","news_user_collect.news_id = news_video.news_id")
        				->where($where)
        				->andWhere('news.weight >= 70 AND news.status=0');
        				$count = $query->count('*',self::getDb());
        				$query->orderBy([
        						'case  when `collect_update_time` is null then `collect_create_time` else `collect_update_time` end'    => SORT_DESC
        				]);
        				$query->offset($pageStart);
        				$query->limit($pageEnd-$pageStart);
        				$command = $query->createCommand(self::getDb());
        				$collect_list = $command->queryAll();
        				break;
        			case 3:
        				$where['news.type'] = 3;
        				$query->select([
        					   "news_user_collect.collect_id",
    							"news_user_collect.create_time as collect_create_time",
    							"news_user_collect.update_time as collect_update_time",
    							"news.news_id",
    							"news.abstract as news_abstract",
    							"news.title",
    							"news.subtitle",
    							"news.content",
    							"news.cover_image",
    							"news.reference_type",
    							"news.reference_id",
    							"news.type",
    							"news.column_id",
    							"news.area_id",
    							"DATE_FORMAT(news.create_time,'%Y/%m/%d %H:%i') as create_time",
    							"news.type_id",
    							"news.special_news_id",
    							"news.top_status",
    							"news.full_status",
    							"news.full_title",
    							"news.full_subtitle",
    							"news.full_cover_image",
    							"news.source_id",
    							"news.source_name",
    							"news.special_id",
    							"news.special_type",
    							"news.special_entry",
    							"news.special_title",
    							"news.special_abstract",
    							"news.special_image",
						])
        				->from('news_user_collect')
        				->innerJoin("news","news_user_collect.news_id = news.news_id")
        				->where($where)
        				->andWhere('news.weight >= 70 AND news.status=0');
        				$count = $query->count('*',self::getDb());
        				$query->orderBy([
        						'case  when `collect_update_time` is null then `collect_create_time` else `collect_update_time` end'    => SORT_DESC
        				]);
        				$query->offset($pageStart);
        				$query->limit($pageEnd-$pageStart);
        				$command = $query->createCommand(self::getDb());
        				$collect_list = $command->queryAll();
        				break;
        			case 4:
						//供生产环境测试使用
						$test_where = " 1 ";
						$test_uid = array('201614754871496408','20161479954020413','201614829033721473','201714930951045991','201614751389465462','201614802974723737','201614762909851436','201614764605844322','201714999087559769','201714842110831357','201614764363288566','201714990988402222');
						$test_adminid_str = "48,74,88,89,141,163,164,165,166,167,168,169,174,175,176";
						if(Yii::$app->params['environment'] == 'prod') {
							if (!in_array($user_id, $test_uid)) {
								$test_where = " creator_id not in(" . $test_adminid_str . ")";
							}
						}
        				$query->select([
        						"news_user_collect.collect_id",
    							"news_user_collect.create_time as collect_create_time",
    							"news_user_collect.update_time as collect_update_time",
    							"vrlive.live.live_id",
    							"vrlive.live.status",
    							"vrlive.live.name",
    							"vrlive.live.start_time",
    							"vrlive.live.image_url",
    							"vrlive.live.type",
    							"vrlive.live.category",
    							"vrlive.live.red_competitor_id",
    							"vrlive.live.blue_competitor_id",
    							"vrlive.live.red_news_id",
    							"vrlive.live.blue_news_id",
    							"vrlive.live.red_score",
    							"vrlive.live.blue_score",
    							"vrlive.live.quiz_status",
    							"vrlive.live.is_props",
								"vrlive.live.is_fast",
								"vrlive.live.screen",
								"vrlive.live.creator_id",
						])
        				->from('news_user_collect')
        				->innerJoin("vrlive.live","news_user_collect.news_id = vrlive.live.live_id")
        				->where($where)
        				->andwhere("vrlive.live.status != 0")
						->andWhere($test_where);
        				$count = $query->count('*',self::getDb());
        				$query->orderBy([
        						'case  when `collect_update_time` is null then `collect_create_time` else `collect_update_time` end'    => SORT_DESC
        				]);
        				$query->offset($pageStart);
        				$query->limit($pageEnd-$pageStart);
        				$command = $query->createCommand(self::getDb());
        				$collect_list = $command->queryAll();
    
        				$returnData['totalCount'] = $count;
        				foreach($collect_list as $key=>$val){
        					if(isset($collect_list[$key]['image_url'])){
        						if($collect_list[$key]['image_url']) $collect_list[$key]['image_url'] = $collect_list[$key]['image_url'].'/y';
        					}
        					
        					$collect_list[$key]['start_time'] = date('m/d H:i', strtotime($val['start_time']));
        					$red_info = LiveCompetitor::getCompetitorInfo($val['red_competitor_id']);
        					$collect_list[$key]['red_name']  = $red_info['real_name'];
        					$collect_list[$key]['red_photo'] = $red_info['avatar'];
        					$blue_info = LiveCompetitor::getCompetitorInfo($val['blue_competitor_id']);
        					$collect_list[$key]['blue_name']  = $blue_info['real_name'];
        					$collect_list[$key]['blue_photo'] = $blue_info['avatar'];
        					$collect_list[$key]['status']     = self::getLiveStatus($val['start_time'], $val['status']);
        					$collect_list[$key]['is_subscribe'] = 0;
        					$collect_list[$key]['chatroom_id']  = 'room_'.$val['live_id'];
        					if(!empty($user_id)){
        						$is_subscribe = LiveUserSubscribe::find()->where(['user_id'=>$user_id,'live_id'=>$val['live_id'],'status'=>'1'])->count();
        						$collect_list[$key]['is_subscribe'] = $is_subscribe;
        					}
        					$returnData[] = $collect_list[$key];
        				}
        				return $returnData;
        				break;
        		}
        
        		$returnData['totalCount'] = $count;
        		foreach($collect_list as $key=>$val){
					//处理 各类型新闻图片 大小
					$collect_list[$key] = NewsUserCollect::getcheckinfo($val,$is_pc);


        			if(isset($collect_list[$key]['special_image'])){
        				if($collect_list[$key]['special_image']) $collect_list[$key]['special_image'] = $collect_list[$key]['special_image'].'/y';
        			}
        			if(isset($collect_list[$key]['image_url'])){
        				if($collect_list[$key]['image_url']) $collect_list[$key]['image_url'] = $collect_list[$key]['image_url'].'?imageMogr2/thumbnail/375x180!';
        			} 
        			
        			$collect_list[$key]['title'] = htmlspecialchars_decode($collect_list[$key]['title']);
        			
        			if($val['special_type'] == 5){
        				$collect_list[$key]['special_image'] = json_decode($val['special_image']);
        			}else{
        				$collect_list[$key]['special_image'] = array();
        			}
        
        			//入口数据
        			if($val['special_id']){
        				$collect_list[$key]['special_entry_info'] = News::find([
        						"news_id",
        						"abstract as news_abstract",
        						"title",
        						"subtitle",
        						"content",
        						"cover_image",
        						"reference_type",
        						"reference_id",
        						"type",
        						"column_id",
        						"area_id",
        						"DATE_FORMAT(`create_time`,'%Y/%m/%d %H:%i') as create_time",
        						"type_id",
        						"special_news_id",
        						"top_status",
        						"full_status",
        						"full_title",
        						"full_subtitle",
        						"full_cover_image",
        						"source_id",
        						"source_name",
        						"special_id",
        						"special_title",
        						"special_abstract",
        						"special_entry",
        						"special_image"])->where(['news_id'=>$val['special_id']])->asArray()->one();
						//处理 各种新闻类型 图片大小
						$collect_list[$key]['special_entry_info'] = NewsUserCollect::getcheckinfo($collect_list[$key]['special_entry_info'], $is_pc);

        				$collect_list[$key]['special_entry_info']['title'] = htmlspecialchars_decode($collect_list[$key]['special_entry_info']['title']);
        			}
        			$returnData[] = $collect_list[$key];
        		}
        	}
        	return $returnData;
        }
        
        /**
         * 判断直播状态
         * $param $start_time
         */
        public static function getLiveStatus($start_time, $status){
        	//状态 1 正常 0 删除 2 直播结束 3 未开始 4 正在直播 5 直播回顾
        	$start_time = strtotime($start_time);
        	if(in_array($status,array(2,5) )){
        		return $status;
        	}else if($start_time > time()){  //开始时间大于当前时间：未开始
        		return 3;
        	}else { //直播中
        		return  4;
        	}
        
        }
        
        
        /**
         * 删除收藏
         */
        
        public static function  delCollectList($user_id = null,$collect_id = null){
        	if($collect_id && $user_id){
        		$collect_id_arr = explode(',', rtrim($collect_id,','));
        		$collect_count = count($collect_id_arr);
        		$success_count = 0;
        		foreach($collect_id_arr as $key=>$value){
        			//判断如果当前操作者非评论/回复的发布者删除失败
        			$collect_model = self::find()->where(['user_id'=>$user_id,'collect_id'=>$value])->one();
        			if($collect_model){
        				$collect_model->status = 0;
        				$rid = $collect_model->save();
        				if($rid !==false){
        					$success_count++;
        				}
        			}
        		}
        		if($success_count !=0 && $success_count == $collect_count) return true;
        	}
        	return false;
        }
        
        
        /**
         *根据时间获取此时的比对时间
         *
         */
        function getRealTime($date){
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

	 function getcheckinfo($value,$is_pc){
		$live_type = array(0=>'9',1=>'10',2=>'11',3=>'12',4=>'13',5=>'14');
		if($is_pc == 1){
			if ($value['type'] == 2) { //轮播图
				$value['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/145x100!' : '';
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
				$value['content'] = array();
			}else if ($value['type'] == 3) { //专题
				$value['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
				$value['content'] = array();
			} else if ($value['type'] == 4) { //视频
				$value['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/145x100!' : '';
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
				$value['content'] = array();
			} else if ($value['type'] == 5 && !empty($value['content'])) { //图集
				if(!empty($value['reference_type']) && intval($value['reference_type']) == 1 && !empty($value['reference_id'])){
					//查看 被引用图集信息
					$ref_news = News::find()->where(['news_id'=>$value['reference_id']])->asArray()->one();
					if(!empty($ref_news['content'])){
						$ref_news['content'] = json_decode($ref_news['content']);
						foreach ($ref_news['content'] as $re_k=>$re_v){
							if($re_k < 4) {
								if(is_object($ref_news['content'][$re_k])){
									$str_con = substr($ref_news['content'][$re_k]->img,-2);
									if($str_con == '/s'){
										$value['content'][$re_k]->img = substr($ref_news['content'][$re_k]->img,0,-2);
									}
									if($value['content']=='""'){
										$tmp = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/150x100!' : '';
										$value[$re_k]['content'] = array($re_k=>array('img'=>$tmp));
									}else{
										$value[$re_k]['content'][$re_k]['img'] = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/150x100!' : '';
									}
								}else {
									$str_con = substr($ref_news['content'][$re_k]['img'],-2);
									if($str_con == '/s'){
										$value['content'][$re_k]['img'] = substr($ref_news['content'][$re_k]['img'],0,-2);
									}
									$value['content'][$re_k]['img'] = $re_v['img'] ? $re_v['img'] . '?imageMogr2/thumbnail/150x100!' : '';
								}
							}
						}
					}else{
						$value['content'] = array();
					}
				}else {
					$value['content'] = json_decode($value['content']);
					foreach ($value['content'] as $k => $v) {
						if ($k < 4) {
							if (is_object($value['content'][$k])) {
								$str_con = substr($value['content'][$k]->img, -2);
								if ($str_con == '/s') {
									$value['content'][$k]->img = substr($value['content'][$k]->img, 0, -2);
								}
								$value['content'][$k]->img = $v->img ? $v->img . '?imageMogr2/thumbnail/150x100!' : '';
							} else {
								$str_con = substr($value['content'][$k]['img'], -2);
								if ($str_con == '/s') {
									$value['content'][$k]['img'] = substr($value['content'][$k]['img'], 0, -2);
								}
								$value['content'][$k]['img'] = $v['img'] ? $v['img'] . '?imageMogr2/thumbnail/150x100!' : '';
							}

						}
					}
				}
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
			} else if ($value['type'] == 7) { //图文
				$value['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/145x100!' : '';
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
				$value['content'] = array();
			} else if (in_array($value['type'], $live_type)) { //直播类型新闻
				$value['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/208x100!' : '';
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
				$value['content'] = array();
			}
		} else{
			if ($value['type'] == 2) { //轮播图
				$value['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/206x142!' : '';
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
				$value['content'] = array();
			}else if ($value['type'] == 3) { //专题
				$value['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
				$value['content'] = array();
			} else if ($value['type'] == 4) { //视频
				$value['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/206x142!' : '';
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
				$value['content'] = array();
			} else if ($value['type'] == 5 && !empty($value['content'])) { //图集
				if(!empty($value['reference_type']) && intval($value['reference_type']) == 1 && !empty($value['reference_id'])){
					//查看 被引用图集信息
					$ref_news = News::find()->where(['news_id'=>$value['reference_id']])->asArray()->one();
					if(!empty($ref_news['content'])){
						$ref_news['content'] = json_decode($ref_news['content']);
						foreach ($ref_news['content'] as $re_k=>$re_v){
							if($re_k < 3) {
								if(is_object($ref_news['content'][$re_k])){
									$str_con = substr($ref_news['content'][$re_k]->img,-2);
									if($str_con == '/s'){
										$value['content'][$re_k]->img = substr($ref_news['content'][$re_k]->img,0,-2);
									}
									if($value['content']=='""'){
										$tmp = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/224x150!' : '';
										$value['content'] = array($re_k=>array('img'=>$tmp));
									}else{
										$value['content'][$re_k]['img'] = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/224x150!' : '';
									}
								}else {
									$str_con = substr($ref_news['content'][$re_k]['img'],-2);
									if($str_con == '/s'){
										$value['content'][$re_k]['img'] = substr($ref_news['content'][$re_k]['img'],0,-2);
									}
									$value['content'][$re_k]['img'] = $re_v['img'] ? $re_v['img'] . '?imageMogr2/thumbnail/224x150!' : '';
								}
							}
						}
					}
				}else {
					$value['content'] = json_decode($value['content']);
					if (!empty($value['content'])) {
						foreach ($value['content'] as $k => $v) {
							if ($k < 3) {
								if (is_object($value['content'][$k])) {
									$str_con = substr($value['content'][$k]->img, -2);
									if ($str_con == '/s') {
										$value['content'][$k]->img = substr($value['content'][$k]->img, 0, -2);
									}
									$value['content'][$k]->img = $v->img ? $v->img . '?imageMogr2/thumbnail/224x150!' : '';
								} else {
									$str_con = substr($value['content'][$k]['img'], -2);
									if ($str_con == '/s') {
										$value['content'][$k]['img'] = substr($value['content'][$k]['img'], 0, -2);
									}
									$value['content'][$k]['img'] = $v['img'] ? $v['img'] . '?imageMogr2/thumbnail/224x150!' : '';
								}

							}
						}
					}
				}
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
			} else if ($value['type'] == 7) { //图文
				$value['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/206x142!' : '';
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
				$value['content'] = array();
			} else if (in_array($value['type'], $live_type)) { //直播类型新闻
				$value['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/710x340!' : '';
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
				$value['content'] = array();
			}
		}
		 if($value['type'] != 5){
			 $value['content'] = array();
		 }

		return $value;
	}
}