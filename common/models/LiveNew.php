<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live".
 *
 * @property string $live_id
 * @property string $name
 * @property integer $weight
 * @property string $start_time
 * @property string $image_url
 * @property integer $introduction_status
 * @property string $title
 * @property string $introduction
 * @property string $remark
 * @property integer $type
 * @property string $create_time
 * @property string $update_time
 * @property integer $creator_id
 * @property integer $status
 * @property integer $category
 * @property integer $play_count
 * @property integer $true_play_count
 * @property string $rule
 * @property integer $red_competitor_id
 * @property string $red_news_id
 * @property integer $red_score
 * @property integer $blue_competitor_id
 * @property string $blue_news_id
 * @property string $befor_start_url
 * @property string $rever_url
 * @property integer $befor_video_category
 * @property integer $rever_video_category
 * @property integer $blue_score
 * @property integer $news_status
 * @property integer $quiz_status
 * @property integer $is_props
 * @property string $view_ranage
 */
class LiveNew extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_new';
    }

    public static  function getDb()
    {
        return Yii::$app->vrlive;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['live_id'], 'required'],
            [['live_id', 'weight', 'introduction_status', 'creator_id'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['introduction'], 'string'],
            [['name'], 'string', 'max' => 45],
            [['image_url', ], 'string', 'max' => 200],
            [['title'], 'string', 'max' => 60],
            [['remark'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'live_id' => 'Live ID',
            'name' => 'Name',
            'weight' => 'Weight',
            'image_url' => 'Image Url',
            'introduction_status' => 'Introduction Status',
            'title' => 'Title',
            'introduction' => 'Introduction',
            'remark' => 'Remark',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
			'refresh_time' => 'Refresh Time',
            'creator_id' => 'Creator ID',
            'category' => 'Category',
			'news_id' => 'News ID',
			'is_fast' => 'Is Fast',
			'show_type' => 'Show Type',
			'is_top' => 'Is Top',
        ];
    }

	/*
     * 获取 系列详情
     * */
	public static function GetInfo($live_id){
		$live = static::find()->where(['live_id'=>$live_id])->asArray()->one();
		return $live;
	}
	
	/**
	 * 系列直播列表
	 */
	public static function SectionList($page, $size, $user_id,$is_pc){
		$offset = ($page - 1) * $size;
		$where = array();
		if($is_pc == 2){
			//展示 m站数据
			$where['show_type'] = 1;
		}
		$list = LiveNew::find()
			->leftJoin('vrlive.live_section', 'live_new.new_section_id = live_section.section_id')
			->where($where)
			->andWhere(['>=', 'weight', 70])
			->andWhere(['!=', 'live_section.status', 0])
			->andWhere(['=', 'live_section.reviewed_status', 0])
			->select([
					"live_new.live_id",
					"live_section.*",
//        		"start_time",
//        		"image_url",
//        		"type",
//        		"category",
//        		"red_competitor_id",
//        		"blue_competitor_id",
//        		"red_news_id",
//        		"blue_news_id",
//        		"red_score",
//        		"blue_score",
//        		"status",
//        		"quiz_status",
//        		"news_status",
//        		"is_props",
//        		"year(create_time) as year1",
//        		"month(create_time) as month1",
//        		"day(create_time) as day1",
//        		"year(refresh_time) as year",
//        		"month(refresh_time) as month",
//        		"day(refresh_time) as day",
//        		"refresh_time",
//				"screen",
//				"creator_id",
//				"is_fast"
				]
			)->limit($size)->offset($offset)
//			->orderBy([
//        			'CASE WHEN refresh_time <> 0 THEN DATE_FORMAT(refresh_time, \'%y%m%d\') ELSE DATE_FORMAT(create_time, \'%y%m%d\') END'=>SORT_DESC,
//        			'weight' => SORT_DESC,
//        			'refresh_time' => SORT_DESC,
//        			'create_time' => SORT_DESC
//        	])
			->asArray()->all();
//		print_r($list);die;
		$count = LiveNew::find()
			->leftJoin('vrlive.live_section', 'live_new.new_section_id = live_section.section_id')
			->where(['>=', 'weight', 70])->andWhere(['!=', 'live_section.status', 0])->andWhere(['=', 'live_section.reviewed_status', 0])->count();
		if(!empty($list)){
			foreach($list as $key=>$val){
				// 直播名正则
				if (!preg_match('/^\"\\\u76f4\\\u64ad\|/u', json_encode($val['name']))) {
					$list[$key]['name'] = '直播| ' . $val['name'];
				}
				$list[$key]['start_time'] = date('m/d H:i', strtotime($val['start_time']));
				$list[$key]['status']     = self::getLiveStatus($val['start_time'], $val['status']);
				$list[$key]['is_subscribe'] = 0;
				$list[$key]['chatroom_id']  = 'room_'.$val['live_id'];
//                if(!empty($user_id)){
//                    $is_subscribe = LiveUserSubscribe::find()->where(['user_id'=>$user_id, 'live_id'=>$val['live_id'], 'status'=>1])->count();
//                    $list[$key]['is_subscribe'] = $is_subscribe;
//                }
				if(empty($is_pc)){
					$list[$key]['image_url']  = $val['image_url'] . '?imageMogr2/thumbnail/562.5x270!';
				}
			}
		}
		$info = array('totalCount'=>$count,'list'=>$list);
		return $info;
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

	//直播详情
    public static function getLiveById($liveId){
        $live = static::find()->where(['live_id' => $liveId])->select('live_id, name, title, introduction, start_time, image_url, type, status, category, befor_start_url,rever_url,befor_video_category,rever_video_category, red_competitor_id,play_count, blue_competitor_id, red_news_id, blue_news_id, red_score,blue_score, quiz_status, news_status, is_props,live_man_cate,live_man_alias,live_man_avatar_url,true_play_count,is_fast,screen')->asArray()->one();
        
        // 添加分享标题字段
        if($live){
            if (!preg_match('/^\"\\\u76f4\\\u64ad\|/u', json_encode($live['name']))) {
                $live['name'] = '直播| ' .$live['name'];
            }
            
        }
        
        
        return $live;
    }

    //直播点击次数 加1
    public static function countAdd($liveId,$live_cou,$true_live_cou){
        $live = static::findOne($liveId);
        $view_ranage = $live->view_ranage;
        $browse_count = '1';
        if($view_ranage){
        	$view_ranage_arr = explode('|', $view_ranage);
        	$no_start_arr = array();
        	$loading_arr = array();
        	$end_arr = array();
        	if(isset($view_ranage_arr[0])) $no_start_arr = explode('-', $view_ranage_arr[0]);
        	if(isset($view_ranage_arr[1])) $loading_arr = explode('-', $view_ranage_arr[1]);
        	if(isset($view_ranage_arr[2])) $end_arr = explode('-', $view_ranage_arr[2]);

        	if(-1 != $live->status){
        		if($live->status == '3' && ($live->start_time > date('Y-m-d H:i:s'))){
        			if(!empty($no_start_arr)) $browse_count = mt_rand($no_start_arr[0],$no_start_arr[1]);
        		}
        		
        		if(in_array($live->status, array(1,4)) && ($live->start_time <= date('Y-m-d H:i:s'))){
        			if(!empty($loading_arr)) $browse_count = mt_rand($loading_arr[0],$loading_arr[1]);
        		}
        		
        		if(in_array($live->status, array(2,5))){
        			if(!empty($end_arr)) $browse_count = mt_rand($end_arr[0],$end_arr[1]);
        		}
        		
        	}
        }
        $live->play_count = $live_cou + $browse_count;
        $live->true_play_count = $true_live_cou + 1;
        $live->save();
        return $live->play_count;
    }

    /*
     * 获取 直播的视频地址、回放地址
     * */
    public static function GetTwo($live_id){
        $live = static::find()->where(['live_id'=>$live_id])->select("image_url,live_id")->one();
        return $live;
    }

    
    /**
     * 热门直播列表
     */
    public static function HotLiveList($user_id = null){
    	$list = Live::find()->where(['>=', 'weight', 70])->andWhere(['!=', 'status', 0])->andWhere(['=', 'reviewed_status', 0])->select([
    			"live.live_id",
    			"name",
    			"start_time",
    			"image_url",
    			"type",
    			"category",
    			"red_competitor_id",
    			"blue_competitor_id",
    			"red_news_id",
    			"blue_news_id",
    			"red_score",
    			"blue_score",
    			"status",
    			"quiz_status",
    			"news_status",
    			"is_props",
    			"year(create_time) as year1",
    			"month(create_time) as month1",
    			"day(create_time) as day1",
    			"year(refresh_time) as year",
    			"month(refresh_time) as month",
    			"day(refresh_time) as day",
    			"refresh_time"
    	])->limit(8)->offset(0)->orderBy([
    			'case  when `year` is null then `year1` else `year` end' => SORT_DESC,
    			'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
    			'case  when `day` is null then `day1` else `day` end' => SORT_DESC,
    			'weight' => SORT_DESC,
    			'refresh_time' => SORT_DESC,
    			'create_time' => SORT_DESC
    	])->asArray()->all();
    	
    	$new_data = array();
    	$loading_data_key = array();
    	$review_data_key = array();
    	$not_start_data_key = array();
    	$has_ended_data_key = array();
    	$checkout_key = array();
    	if(!empty($list)){
    		foreach($list as $key=>$val){
    			$list[$key]['starttime'] = $val['start_time'];
    			$list[$key]['start_time'] = date('m/d H:i', strtotime($val['start_time']));
    			$red_info = LiveCompetitor::findOne($val['red_competitor_id']);
    			$list[$key]['red_name']  = $red_info['real_name'];
    			$list[$key]['red_photo'] = $red_info['avatar'];
    			$blue_info = LiveCompetitor::findOne($val['blue_competitor_id']);
    			$list[$key]['blue_name']  = $blue_info['real_name'];
    			$list[$key]['blue_photo'] = $blue_info['avatar'];
    			$list[$key]['status']     = self::getLiveStatus($val['start_time'], $val['status']);
    			$list[$key]['is_subscribe'] = 0;
    			$list[$key]['chatroom_id']  = 'room_'.$val['live_id'];
    			if(!empty($user_id)){
    				$is_subscribe = LiveUserSubscribe::find()->where(['user_id'=>$user_id, 'live_id'=>$val['live_id'], 'status'=>1])->count();
    				$list[$key]['is_subscribe'] = $is_subscribe;
    			}
    			
    			switch ($list[$key]['status']){
    				case '4':
    					$loading_data_key[] = $key;
    					break;
    				case '5':
    					$review_data_key[] = $key;
    					break;
    				case '3':
    					$not_start_data_key[] = $key;
    					break;
    				case '2':
    					$has_ended_data_key[] = $key;
    					break;
    			}
    		}
    		

    		
    		foreach($loading_data_key as $value){
    			if(count($checkout_key) < 2){
    				$new_data[] = $list[$value];
    				$checkout_key[] = $value;
    			}
    		}
    		
    		if(count($checkout_key) < 2){
	    		foreach($review_data_key as $value){
	    			if(count($checkout_key) < 2){
	    				$new_data[] = $list[$value];
	    				$checkout_key[] = $value;
	    			}
	    		}
    		}
    		
    		if(count($checkout_key) < 2){
	    		foreach($not_start_data_key as $value){
	    			if(count($checkout_key) < 2){
	    				$new_data[] = $list[$value];
	    				$checkout_key[] = $value;
	    			}
	    		}
    		}
    		
    		if(count($checkout_key) < 2){
	    		foreach($has_ended_data_key as $value){
	    			if(count($checkout_key) < 2){
	    				$new_data[] = $list[$value];
	    				$checkout_key[] = $value;
	    			}
	    		}
    		}
    		
    		foreach($list as $new_key=>$new_value){
    			if(!in_array($new_key, $checkout_key)) $new_data[] = $list[$new_key];
    		}
    		
    		
    	}
    	return $new_data;
    }
    
    /**
     *  标准直播
     */
    public static function BiaoZhunLiveList(){
    	$list = Live::find()->andWhere(['>=', 'weight', 70])
			->andWhere(['!=', 'status', 0])
			->andWhere(['reviewed_status'=>0])
			->select([
    			"live.live_id",
    			"name",
    			"start_time",
    			"image_url",
    			"type",
    			"category",
    			"red_competitor_id",
    			"blue_competitor_id",
    			"red_news_id",
    			"blue_news_id",
    			"red_score",
    			"blue_score",
    			"status",
    			"quiz_status",
    			"news_status",
    			"is_props",
    			"year(create_time) as year1",
    			"month(create_time) as month1",
    			"day(create_time) as day1",
    			"year(refresh_time) as year",
    			"month(refresh_time) as month",
    			"day(refresh_time) as day",
    			"refresh_time"]
    			)
    			->limit(5)
    			->orderBy([
    					'CASE WHEN refresh_time <> 0 THEN DATE_FORMAT(refresh_time, \'%y%m%d\') ELSE DATE_FORMAT(create_time, \'%y%m%d\') END'=>SORT_DESC,
    					'weight' => SORT_DESC,
    					'refresh_time' => SORT_DESC,
    					'create_time' => SORT_DESC
    			])
    			->asArray()
    			->all();
    	
    	if(!empty($list)){
    		foreach($list as $key=>$val){
    			$list[$key]['starttime'] = $val['start_time'];
    			$list[$key]['start_time'] = date('m/d H:i', strtotime($val['start_time']));
    			$list[$key]['status']     = self::getLiveStatus($val['start_time'], $val['status']);
    		}
    	}
    	return $list;
    }
    
    /**
     * 跤坛，保定 右侧直播列表
     */
    public function BaoDindLive($user_id = null, $aid = 0, $cid = 0){
    	if($cid){
    		$andWhere = 'news.type in(9,10,11,12,13,14) AND news.web_pub=1 AND news.column_id = '.$cid;
    	}else{
    		$andWhere = 'news.type in(9,10,11,12,13,14) AND news.web_pub=1 AND news.area_id = '.$aid;
    	}
    	$list = News::find()
    	->select([
    			"news.news_id", "news.title", "news.title", "news.create_time", "news.refresh_time", "news.update_time", "news.live_id", "news.cover_image",
    			"year(news.create_time) as year1",
    			"month(news.create_time) as month1",
    			"day(news.create_time) as day1",
    			"year(news.refresh_time) as year",
    			"month(news.refresh_time) as month",
    			"day(news.refresh_time) as day",
    			
    			"live.live_id",
    			"live..name",
    			"live.start_time",
    			"live.image_url",
    			"live.type",
    			"live.category",
    			"live.red_competitor_id",
    			"live.blue_competitor_id",
    			"live.red_news_id",
    			"live.blue_news_id",
    			"live.red_score",
    			"live.blue_score",
    			"live.status",
    			"live.quiz_status",
    			"live.news_status",
    			"live.is_props",
    	])
    	->leftJoin('vrlive.live', 'live.live_id = news.live_id')
    	->where(['>=', 'news.weight', 70])->andWhere($andWhere)
		->andWhere(['=', 'reviewed_status', 0])
//     	->groupBy("live.title")
    	->orderBy([
    			'case  when `year` is null then `year1` else `year` end' => SORT_DESC,
    			'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
    			'case  when `day` is null then `day1` else `day` end' => SORT_DESC,
    			'news.weight' => SORT_DESC,
    			'news.refresh_time' => SORT_DESC,
    			'news.create_time' => SORT_DESC
    	])
    	->limit(5)->offset(0)
    	->asArray()->all();
    	if(!empty($list)){
    		foreach($list as $key=>$val){
    			$list[$key]['starttime'] = $val['start_time'];
    			$list[$key]['start_time'] = date('m/d H:i', strtotime($val['start_time']));
    			$list[$key]['status']     = self::getLiveStatus($val['start_time'], $val['status']);
    			
    			/* 关联的直播显示新建新闻的标题和图片 */
    			$list[$key]['name'] = $val['title'];
    			$list[$key]['image_url'] = $val['cover_image'];
    		}
    	}
    	return $list;
    }
    
    public static function GetLiveInfoByTitle($keyword = null){
    	$list = Live::find()
    	->select([
    			"live.live_id",
    			"live.start_time",
    			"live.name",
    			"live_channel.push_url"
    	])
    	->leftJoin('vrlive.live_camera_angle','live.live_id = live_camera_angle.live_id')
    	->innerJoin('vrlive.live_channel', 'live_camera_angle.source_id = live_channel.channel_id')
    	->where(['like', 'live.name', $keyword])->andwhere(['live_camera_angle.name'=>'主持人视角'])
    	->groupBy('live.live_id')
    	->orderBy([
    			'live.weight' => SORT_DESC,
    			'live.start_time' => SORT_DESC
    	])
    	->asArray()->all();
    	
    	foreach($list as $key=>$value){
    		$list[$key]['chatroom_id'] = 'room_'.$value['live_id'];
    	}
    	return $list;
//     	echo "<pre>";
//     	print_r($list);exit;
    }
    
    
    public static function LiveLogin($username = null , $password = null){
    	if($username && $password){
    		$model = LiveAdmin::find()->where(['username'=>$username])->one();
    		if($model){
    			if(md5(md5($password)) == $model->password){
    				
    				return true;
    			}
    		}
    	}
    	return false;
    }
    



	//获取直播员信息
	public static function getCompere($live_id){
		$ret  = array("creator_id"=>0,"live_man_cate"=>"","live_man_alias"=>"","live_man_avatar_url"=>"");
		$list = static::find()->where(array('live_id'=>$live_id))->asArray()->one();

		if($list && !empty($list)){
			$ret['category'] = $list['category'];
			$ret['creator_id'] = $list['creator_id'];
			$ret['live_man_cate'] = $list['live_man_cate'];
			$ret['live_man_alias'] = $list['live_man_alias'];
			$ret['live_man_avatar_url'] = $list['live_man_avatar_url'];

		}
		return  $ret;
	}

	//获取直播详情
	public function getLiveInfo($live_id){
		$list = Live::find()
			->select([
				"live.category as type",
				"live.name as title",
				"live.image_url as cover_img",
				"live.start_time",
				"live.status",
				"live.screen",
				"live.live_man_avatar_url as admin_img",
				"live.live_man_cate as admin_cate",
				"live.live_man_alias as admin_alias",
				"reviewed_status",
				"amendments",
			])
			->where(['live.live_id'=>$live_id])
			->asArray()->one();
		
		return $list;
	}

	//获取 录播列表
	public function getRecordList($admin_id,$page,$count){
		$offset = ($page-1)*$count;
		$list = Live::find()->select([
			"live_id",
			"name",
			"image_url",
			"rever_url",
			"rever_img_url",
			"start_time",
			"weight",
			"screen",
		    "create_time",
			"year(create_time) as year1",
			"month(create_time) as month1",
			"day(create_time) as day1",
			"year(refresh_time) as year",
			"month(refresh_time) as month",
			"day(refresh_time) as day",
			"refresh_time",
			"reviewed_status",
			"amendments"])
			->where("category = 6 and creator_id = ".$admin_id." and status = 5 ")
			->orderBy(['case  when `year` is null then `year1` else `year` end' => SORT_DESC,
				'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
				'case  when `day` is null then `day1` else `day` end' => SORT_DESC,
				'weight' => SORT_DESC,
				'refresh_time' => SORT_DESC,
				'create_time' => SORT_DESC])
			->offset($offset)
			->limit($count)
			->asArray()
			->all();

		return $list;
	}


	/**
	 * 后台直播列表
	 */
	public static function getLiveList($column_type,$column_id,$admin_id, $name,  $page, $count,$gao_admin){
		$offset = ($page - 1) * $count;
		$where = " 1=1 and a.is_fast = 1  and a.type=0";

		//权限过滤
		if($admin_id != 1 && $gao_admin != 0){
			$where .= " and a.creator_id = '".$admin_id."'";
		}

		//标题 筛选
		if(!empty($name)){
			$where .= " and a.name like '%$name%'";
		}

		//匹配 栏目
		if($column_type == 0){
			$where .= " and column_id = ".$column_id;
		}else{
			$where .= " and area_id = ".$column_id;
		}

		$list = News::find()->leftJoin('vrlive.live_new a','news.live_id = a.live_id')
				->where($where)
				->andWhere(['!=', 'a.status', 0])
//				->andWhere(['=', 'news.status', 0])
				->select([
					"a.live_id",
					"a.name",
					"a.type",
					"a.create_time",
					"a.refresh_time",
					"a.status",
					"a.live_count",
					"a.title",
					"a.creator_id",
					"a.introduction",
					"a.news_id"
				]
		)->limit($count)->offset($offset)->orderBy([
				'CASE WHEN a.refresh_time <> 0 THEN DATE_FORMAT(a.refresh_time, \'%y%m%d\') ELSE DATE_FORMAT(a.create_time, \'%y%m%d\') END'=>SORT_DESC,
			'a.refresh_time' => SORT_DESC,
			'a.create_time' => SORT_DESC
		])->asArray()->all();
		if(count($list) > 0){
			foreach ($list as $key=>$value){
				$admin_info = AdminUser::find()->alias('a')->leftJoin('vrnews1.company c','a.company_id = c.company_id')->select("a.username, c.name")->where(['admin_id'=>$value['creator_id']])->asArray()->one();
				$list[$key]['source'] = $admin_info['username'];
				if(!empty($admin_info['name'])){
					$list[$key]['source'] = $admin_info['name'].':'.$admin_info['username'];
				}
//				$list[$key]['status'] = static::getLiveStatus($value['start_time'], $value['status']);
			}
		}
		$total_count = News::find()
			->leftJoin('vrlive.live_new a','news.live_id = a.live_id')
			->where($where)
			->andWhere(['!=', 'a.status', 0])
//			->andWhere(['=', 'news.status', 0])
			->count();
		$return['totalCount'] = $total_count;
		$return['live_list'] = $list;
		return $return;
	}

	//快直播 待审核列表
	public static  function  getBackWaitList($admin_id, $name, $type, $page, $count,$admin_type){
		$offset = ($page - 1) * $count;
		$where = " 1=1 and is_fast = 1 and reviewed_status = 1 ";
//		$admin_type = 0; //普通账号
//		if($admin_id != 1){
//			//判断 当前账号的 角色
//			$admin_role = AdminRole::findRole($admin_id);
//			if($admin_role == 0){
//				//高管
//				$admin_type = 1;
//			}
//		}else{
//			//超管
//			$admin_type = 1;
//		}

		if($admin_type != 1){ //普通账号 只能看见自己创建的
			$where .= " and creator_id = '".$admin_id."'";
		}
		if(!empty($name)){
			$where .= " and name like '%$name%'";
		}
		if(!empty($type)){
			$where .= " and category = '".$type."'";
		}

		$list = Live::find()->where($where)->andWhere(['!=', 'status', 0])->select([
				"live_id",
				"name",
				"category",
				"rever_url",
				"start_time",
				"status",
				"creator_id",
				"reviewed_status",
				"amendments"]
		)
			->limit($count)
			->offset($offset)
			->orderBy(['start_time' => SORT_ASC])
			->asArray()->all();
		if(count($list) > 0){
			foreach ($list as $key=>$value){
				$admin_info = AdminUser::find()->alias('a')->leftJoin('vrnews1.company c','a.company_id = c.company_id')->select("a.username, c.name")->where(['admin_id'=>$value['creator_id']])->asArray()->one();
				$list[$key]['source'] = $admin_info['username'];
				if(!empty($admin_info['name'])){
					$list[$key]['source'] = $admin_info['name'].':'.$admin_info['username'];
				}
				$list[$key]['creator_name']  = $admin_info['username'];
				$list[$key]['status'] = static::getLiveStatus($value['start_time'], $value['status']);
			}
		}
		$total_count = Live::find()->where($where)->andWhere(['!=', 'status', 0])->count();
		$return['totalCount'] = $total_count;
		$return['live_list'] = $list;
		return $return;
	}

	//快直播 审核未通过列表
	public static function getBackNopassList($admin_id, $name, $type, $page, $count,$admin_type){
		$offset = ($page - 1) * $count;
		$where = " 1=1 and is_fast = 1 and reviewed_status = 2 ";
//		$admin_type = 0; //普通账号
//		if($admin_id != 1){
//			//判断 当前账号的 角色
//			$admin_role = AdminRole::findRole($admin_id);
//			if($admin_role == 0){
//				//高管
//				$admin_type = 1;
//			}
//		}else{
//			//超管
//			$admin_type = 1;
//		}

		if($admin_type != 1){ //普通账号 只能看见自己创建的
			$where .= " and creator_id = '".$admin_id."'";
		}
		if(!empty($name)){
			$where .= " and name like '%$name%'";
		}
		if(!empty($type)){
			$where .= " and category = '".$type."'";
		}

		$list = Live::find()->where($where)->andWhere(['!=', 'status', 0])->select([
				"live_id",
				"name",
				"category",
				"rever_url",
				"start_time",
				"status",
				"creator_id",
				"reviewed_status",
				"amendments"]
		)
			->limit($count)
			->offset($offset)
			->orderBy(['start_time' => SORT_ASC])
			->asArray()->all();
		if(count($list) > 0){
			foreach ($list as $key=>$value){
				$admin_info = AdminUser::find()->alias('a')->leftJoin('vrnews1.company c','a.company_id = c.company_id')->select("a.username, c.name")->where(['admin_id'=>$value['creator_id']])->asArray()->one();
				$list[$key]['source'] = $admin_info['username'];
				if(!empty($admin_info['name'])){
					$list[$key]['source'] = $admin_info['name'].':'.$admin_info['username'];
				}
				$list[$key]['creator_name']  = $admin_info['username'];
				$list[$key]['status'] = static::getLiveStatus($value['start_time'], $value['status']);
			}
		}
		$total_count = Live::find()->where($where)->andWhere(['!=', 'status', 0])->count();
		$return['totalCount'] = $total_count;
		$return['live_list'] = $list;
		return $return;
	}

	public function getLiveSection()
	{
		return $this->hasOne(LiveSection::className(), ['live_id' => 'live_id']);
	}

	public function getNewsnew()
	{
		return $this->hasOne(News::className(), ['live_id' => 'live_id']);

	}


}
