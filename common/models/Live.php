<?php

namespace common\models;

use Yii;
use common\models\AdminUser;
use common\models\AdminRole;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "live".
 *
 * @property string  $live_id
 * @property string  $name
 * @property integer $weight
 * @property string  $start_time
 * @property string  $image_url
 * @property integer $introduction_status
 * @property string  $title
 * @property string  $introduction
 * @property string  $remark
 * @property integer $type
 * @property string  $create_time
 * @property string  $update_time
 * @property integer $creator_id
 * @property integer $status
 * @property integer $category
 * @property integer $play_count
 * @property integer $true_play_count
 * @property string  $rule
 * @property integer $red_competitor_id
 * @property string  $red_news_id
 * @property integer $red_score
 * @property integer $blue_competitor_id
 * @property string  $blue_news_id
 * @property string  $befor_start_url
 * @property string  $rever_url
 * @property integer $befor_video_category
 * @property integer $rever_video_category
 * @property integer $blue_score
 * @property integer $news_status
 * @property integer $quiz_status
 * @property integer $is_props
 * @property string  $view_ranage
 */
class Live extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live';
    }
    
    public static function getDb()
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
            [['live_id', 'weight', 'introduction_status', 'type', 'creator_id', 'status', 'category', 'play_count', 'true_play_count', 'red_competitor_id', 'red_news_id', 'red_score', 'blue_competitor_id', 'blue_news_id', 'befor_video_category', 'rever_video_category', 'blue_score', 'news_status', 'quiz_status', 'is_props'], 'integer'],
            [['start_time', 'create_time', 'update_time'], 'safe'],
            [['introduction'], 'string'],
            [['name'], 'string', 'max' => 45],
            [['image_url', 'rule', 'befor_start_url', 'rever_url', 'view_ranage'], 'string', 'max' => 200],
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
            'live_id'              => 'Live ID',
            'name'                 => 'Name',
            'weight'               => 'Weight',
            'start_time'           => 'Start Time',
            'image_url'            => 'Image Url',
            'introduction_status'  => 'Introduction Status',
            'title'                => 'Title',
            'introduction'         => 'Introduction',
            'remark'               => 'Remark',
            'type'                 => 'Type',
            'create_time'          => 'Create Time',
            'update_time'          => 'Update Time',
            'creator_id'           => 'Creator ID',
            'status'               => 'Status',
            'category'             => 'Category',
            'play_count'           => 'Play Count',
            'true_play_count'      => 'True Play Count',
            'rule'                 => 'Rule',
            'red_competitor_id'    => 'Red Competitor ID',
            'red_news_id'          => 'Red News ID',
            'red_score'            => 'Red Score',
            'blue_competitor_id'   => 'Blue Competitor ID',
            'blue_news_id'         => 'Blue News ID',
            'befor_start_url'      => 'Befor Start Url',
            'rever_url'            => 'Rever Url',
            'befor_video_category' => 'Befor Video Category',
            'rever_video_category' => 'Rever Video Category',
            'blue_score'           => 'Blue Score',
            'news_status'          => 'News Status',
            'quiz_status'          => 'Quiz Status',
            'is_props'             => 'Is Props',
            'view_ranage'          => 'View Ranage',
        ];
    }
    
    /**
     * 直播列表
     */
    public static function liveList($page, $size, $user_id,$category = NULL,$is_pc,$live_tag){
        $offset = ($page - 1) * $size;
//		$redis = Yii::$app->cache;
//		$update_time = $redis->get("live_list_update");
//		$name = "live_list_".$category."_".$page."_".$update_time;
//		$redis_info = $redis->get($name);
//		if($redis_info && count($redis_info) > 0){
//			return $redis_info;
//		}
        //供生产环境测试使用
        $test_where = " 1 ";
        $test_uid = array('201614754871496408', '20161479954020413', '201614829033721473', '201714930951045991', '201614751389465462', '201614802974723737', '201614762909851436', '201614764605844322', '201714999087559769', '201714842110831357', '201614764363288566', '201714990988402222');
        $test_adminid_str = "48,74,88,89,141,163,164,165,166,167,168,169,174,175,176";
        if (Yii::$app->params['environment'] == 'prod') {
            if (!$user_id || !in_array($user_id, $test_uid)) {
                $test_where = " creator_id not in(" . $test_adminid_str . ")";
            }
        }
        $where = array();
        if ($category == 2) {
            $where['category'] = $category;
        }


        /*if(empty($is_pc)){
            $where['is_fast'] = 0;
        }*/
		$trans_where = "  case when category = 6  then rever_url<>'' else (rever_url is null or rever_url is not null) end";
		$tag_where = '';
		if($live_tag)
		{
			$tag_list  = explode(',', $live_tag);
			$tag_count = count($tag_list);
			$str = '';
			for ($i=0;$i<$tag_count;$i++)
			{
				$str .= ' live_tags_relation.tag_id = '.$tag_list[$i].' or';
			}
			$tag_where = ' and ('.rtrim($str, 'or').')';
//			$trans_where .= " and locate($live_tag, live.live_tag)";

		}else
		{
            //普通直播列表里包含高管创建的快直播
		   
            if(empty($is_pc)) {
                $tag_where = 'and (is_fast = 0 or live_tags_relation.tag_id =2)'; //or live_tags_relation.tag_id =2';
            }
		    
		}
        $list = Live::find()->leftJoin('live_tags_relation', 'live_tags_relation.live_id = live.live_id')
				->where($where)->andWhere($trans_where)->andWhere($test_where.$tag_where)->andWhere(['>=', 'weight', 70])->andWhere(['!=', 'status', 0])->andWhere(['=', 'reviewed_status', 0])->select([
        		"live.live_id",
        		"name",
        		"start_time",
        		"image_url",
        		"live.type",
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
        		"year(live.create_time) as year1",
        		"month(live.create_time) as month1",
        		"day(live.create_time) as day1",
        		"year(refresh_time) as year",
        		"month(refresh_time) as month",
        		"day(refresh_time) as day",
        		"refresh_time",
				"screen",
				"creator_id",
				"is_fast"]
        	)->limit($size)->offset($offset)->orderBy([
        			'CASE WHEN refresh_time <> 0 THEN DATE_FORMAT(refresh_time, \'%y%m%d\') ELSE DATE_FORMAT(live.create_time, \'%y%m%d\') END'=>SORT_DESC,
//         			'case  when `year` is null then `year1` else `year` end' => SORT_DESC,
//         			'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
//         			'case  when `day` is null then `day1` else `day` end' => SORT_DESC,
        			'weight' => SORT_DESC,
        			'refresh_time' => SORT_DESC,
        			'live.create_time' => SORT_DESC
        	])->asArray()->all();
		$count = Live::find()->where($where)->andWhere($trans_where)->andWhere($test_where)->andWhere(['>=', 'weight', 70])->andWhere(['!=', 'status', 0])->andWhere(['reviewed_status'=>0])->count();
        if(!empty($list)){
           
            
            foreach ($list as $key => $val) {
                // 直播名正则
                if (!preg_match('/^\"\\\u76f4\\\u64ad\|/u', json_encode($val['name']))) {
                    $list[$key]['name'] = '直播| ' . $val['name'];
                }
                $list[$key]['start_time'] = date('m/d H:i', strtotime($val['start_time']));
                $red_info = LiveCompetitor::findOne($val['red_competitor_id']);
                $list[$key]['red_name'] = $red_info['real_name'];
                $list[$key]['red_photo'] = $red_info['avatar'];
                $blue_info = LiveCompetitor::findOne($val['blue_competitor_id']);
                $list[$key]['blue_name'] = $blue_info['real_name'];
                $list[$key]['blue_photo'] = $blue_info['avatar'];
                $list[$key]['status'] = self::getLiveStatus($val['start_time'], $val['status']);
                $list[$key]['is_subscribe'] = 0;
                $list[$key]['chatroom_id'] = 'room_' . $val['live_id'];
                if (!empty($user_id)) {
                    $is_subscribe = LiveUserSubscribe::find()->where(['user_id' => $user_id, 'live_id' => $val['live_id'], 'status' => 1])->count();
                    $list[$key]['is_subscribe'] = $is_subscribe;
                }
                if (empty($is_pc)) {
                    $list[$key]['image_url'] = $val['image_url'] . '?imageMogr2/thumbnail/562.5x270!';
                }
            }
        }
        $info = array('totalCount' => $count, 'list' => $list);

//		$redis->set($name, $info);
        return $info;
    }



    /**
     * 判断直播状态
     * $param $start_time
     */
    public static function getLiveStatus($start_time, $status)
    {
        //状态 1 正常 0 删除 2 直播结束 3 未开始 4 正在直播 5 直播回顾
        $start_time = strtotime($start_time);
        if (in_array($status, array(2, 5))) {
            return $status;
        } else if ($start_time > time()) {  //开始时间大于当前时间：未开始
            return 3;
        } else { //直播中
            return 4;
        }
    }
    
    public static function getLiveById($liveId)
    {
        $live = static::find()->where(['live_id' => $liveId])->select('live_id, name, title, introduction, start_time, image_url, type, status, category, befor_start_url,rever_url,befor_video_category,rever_video_category, red_competitor_id,play_count, blue_competitor_id, red_news_id, blue_news_id, red_score,blue_score, quiz_status, news_status, is_props,live_man_cate,live_man_alias,live_man_avatar_url,true_play_count,is_fast,screen')->asArray()->one();
        // 添加分享标题字段
        if ($live) {
            if (!preg_match('/^\"\\\u76f4\\\u64ad\|/u', json_encode($live['name']))) {
                $live['name'] = '直播| ' .$live['name'];
                $live['share_title'] = $live['name'] . ' |法制与新闻客户端';
            }
            
        }
        
        return $live;
    }
    
    //直播点击次数 加1
    public static function countAdd($liveId, $live_cou, $true_live_cou)
    {
        $live = static::findOne($liveId);
        $view_ranage = $live->view_ranage;
        $browse_count = '1';
        if ($view_ranage) {
            $view_ranage_arr = explode('|', $view_ranage);
            $no_start_arr = array();
            $loading_arr = array();
            $end_arr = array();
            if (isset($view_ranage_arr[0])) $no_start_arr = explode('-', $view_ranage_arr[0]);
            if (isset($view_ranage_arr[1])) $loading_arr = explode('-', $view_ranage_arr[1]);
            if (isset($view_ranage_arr[2])) $end_arr = explode('-', $view_ranage_arr[2]);
            if (-1 != $live->status) {
                if ($live->status == '3' && ($live->start_time > date('Y-m-d H:i:s'))) {
                    if (!empty($no_start_arr)) $browse_count = mt_rand($no_start_arr[0], $no_start_arr[1]);
                }
                if (in_array($live->status, array(1, 4)) && ($live->start_time <= date('Y-m-d H:i:s'))) {
                    if (!empty($loading_arr)) $browse_count = mt_rand($loading_arr[0], $loading_arr[1]);
                }
                if (in_array($live->status, array(2, 5))) {
                    if (!empty($end_arr)) $browse_count = mt_rand($end_arr[0], $end_arr[1]);
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
    public static function GetTwo($live_id)
    {
        $live = static::find()->where(['live_id' => $live_id])->select("image_url,live_id")->one();
        
        return $live;
    }
    
    /**
     * 热门直播列表
     */
    public static function HotLiveList($user_id = null)
    {
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
            'case  when `year` is null then `year1` else `year` end'    => SORT_DESC,
            'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
            'case  when `day` is null then `day1` else `day` end'       => SORT_DESC,
            'weight'                                                    => SORT_DESC,
            'refresh_time'                                              => SORT_DESC,
            'create_time'                                               => SORT_DESC
        ])->asArray()->all();
        $new_data = array();
        $loading_data_key = array();
        $review_data_key = array();
        $not_start_data_key = array();
        $has_ended_data_key = array();
        $checkout_key = array();
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $list[$key]['starttime'] = $val['start_time'];
                $list[$key]['start_time'] = date('m/d H:i', strtotime($val['start_time']));
                $red_info = LiveCompetitor::findOne($val['red_competitor_id']);
                $list[$key]['red_name'] = $red_info['real_name'];
                $list[$key]['red_photo'] = $red_info['avatar'];
                $blue_info = LiveCompetitor::findOne($val['blue_competitor_id']);
                $list[$key]['blue_name'] = $blue_info['real_name'];
                $list[$key]['blue_photo'] = $blue_info['avatar'];
                $list[$key]['status'] = self::getLiveStatus($val['start_time'], $val['status']);
                $list[$key]['is_subscribe'] = 0;
                $list[$key]['chatroom_id'] = 'room_' . $val['live_id'];
                if (!empty($user_id)) {
                    $is_subscribe = LiveUserSubscribe::find()->where(['user_id' => $user_id, 'live_id' => $val['live_id'], 'status' => 1])->count();
                    $list[$key]['is_subscribe'] = $is_subscribe;
                }
                switch ($list[$key]['status']) {
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
            foreach ($loading_data_key as $value) {
                if (count($checkout_key) < 2) {
                    $new_data[] = $list[$value];
                    $checkout_key[] = $value;
                }
            }
            if (count($checkout_key) < 2) {
                foreach ($review_data_key as $value) {
                    if (count($checkout_key) < 2) {
                        $new_data[] = $list[$value];
                        $checkout_key[] = $value;
                    }
                }
            }
            if (count($checkout_key) < 2) {
                foreach ($not_start_data_key as $value) {
                    if (count($checkout_key) < 2) {
                        $new_data[] = $list[$value];
                        $checkout_key[] = $value;
                    }
                }
            }
            if (count($checkout_key) < 2) {
                foreach ($has_ended_data_key as $value) {
                    if (count($checkout_key) < 2) {
                        $new_data[] = $list[$value];
                        $checkout_key[] = $value;
                    }
                }
            }
            foreach ($list as $new_key => $new_value) {
                if (!in_array($new_key, $checkout_key)) $new_data[] = $list[$new_key];
            }
            
            
        }
        
        return $new_data;
    }
    
    /**
     *  标准直播
     */
    public static function BiaoZhunLiveList()
    {
        $list = Live::find()->andWhere(['>=', 'weight', 70])
            ->andWhere(['!=', 'status', 0])
            ->andWhere(['reviewed_status' => 0])
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
                'CASE WHEN refresh_time <> 0 THEN DATE_FORMAT(refresh_time, \'%y%m%d\') ELSE DATE_FORMAT(create_time, \'%y%m%d\') END' => SORT_DESC,
                'weight'                                                                                                               => SORT_DESC,
                'refresh_time'                                                                                                         => SORT_DESC,
                'create_time'                                                                                                          => SORT_DESC
            ])
            ->asArray()
            ->all();
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $list[$key]['starttime'] = $val['start_time'];
                $list[$key]['start_time'] = date('m/d H:i', strtotime($val['start_time']));
                $list[$key]['status'] = self::getLiveStatus($val['start_time'], $val['status']);
            }
        }
        
        return $list;
    }
    
    /**
     * 跤坛，保定 右侧直播列表
     */
    public function BaoDindLive($user_id = null, $aid = 0, $cid = 0)
    {
        if ($cid) {
            $andWhere = 'news.type in(9,10,11,12,13,14) AND news.web_pub=1 AND news.column_id = ' . $cid;
        } else {
            $andWhere = 'news.type in(9,10,11,12,13,14) AND news.web_pub=1 AND news.area_id = ' . $aid;
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
                'case  when `year` is null then `year1` else `year` end'    => SORT_DESC,
                'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
                'case  when `day` is null then `day1` else `day` end'       => SORT_DESC,
                'news.weight'                                               => SORT_DESC,
                'news.refresh_time'                                         => SORT_DESC,
                'news.create_time'                                          => SORT_DESC
            ])
            ->limit(5)->offset(0)
            ->asArray()->all();
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $list[$key]['starttime'] = $val['start_time'];
                $list[$key]['start_time'] = date('m/d H:i', strtotime($val['start_time']));
                $list[$key]['status'] = self::getLiveStatus($val['start_time'], $val['status']);
                /* 关联的直播显示新建新闻的标题和图片 */
                $list[$key]['name'] = $val['title'];
                $list[$key]['image_url'] = $val['cover_image'];
            }
        }
        
        return $list;
    }
    
    public static function GetLiveInfoByTitle($keyword = null)
    {
        $list = Live::find()
            ->select([
                "live.live_id",
                "live.start_time",
                "live.name",
                "live_channel.push_url"
            ])
            ->leftJoin('vrlive.live_camera_angle', 'live.live_id = live_camera_angle.live_id')
            ->innerJoin('vrlive.live_channel', 'live_camera_angle.source_id = live_channel.channel_id')
            ->where(['like', 'live.name', $keyword])->andwhere(['live_camera_angle.name' => '主持人视角'])
            ->groupBy('live.live_id')
            ->orderBy([
                'live.weight'     => SORT_DESC,
                'live.start_time' => SORT_DESC
            ])
            ->asArray()->all();
        foreach ($list as $key => $value) {
            $list[$key]['chatroom_id'] = 'room_' . $value['live_id'];
        }
        
        return $list;
//     	echo "<pre>";
//     	print_r($list);exit;
    }
    
    
    public static function LiveLogin($username = null, $password = null)
    {
        if ($username && $password) {
            $model = LiveAdmin::find()->where(['username' => $username])->one();
            if ($model) {
                if (md5(md5($password)) == $model->password) {
                    
                    return true;
                }
            }
        }
        
        return false;
    }
    
    
    public static function get_rcloud_token($user_id)
    {
        $nonce = mt_rand();
        $timeStamp = time();
        $sign = sha1(Yii::$app->params['ryAppSecret'] . $nonce . $timeStamp);
        $header = array(
            'RC-App-Key:' . Yii::$app->params['ryAppKey'],
            'RC-Nonce:' . $nonce,
            'RC-Timestamp:' . $timeStamp,
            'RC-Signature:' . $sign,
        );
        $data = 'userId=' . $user_id . '&name=新汇闻&portraitUri=';
        $result = $this->curl_http(Yii::$app->params['ryApiUrl'] . '/user/getToken.json', $data, $header);
        
        return $result['token'];
    }
    
    
    //获取直播员信息
    public static function getCompere($live_id)
    {
        $ret = array("creator_id" => 0, "live_man_cate" => "", "live_man_alias" => "", "live_man_avatar_url" => "");
        $list = static::find()->where(array('live_id' => $live_id))->asArray()->one();
        if ($list && !empty($list)) {
            $ret['category'] = $list['category'];
            $ret['creator_id'] = $list['creator_id'];
            $ret['live_man_cate'] = $list['live_man_cate'];
            $ret['live_man_alias'] = $list['live_man_alias'];
            $ret['live_man_avatar_url'] = $list['live_man_avatar_url'];
            
        }
        
        return $ret;
    }
    
    //获取直播详情
    public static function getLiveInfo($live_id)
    {
        $list = Live::find()
            ->with(['news'   => function ($q) {
                $q->select('column_id,area_id');
            }, 'news.column' => function ($q) {
                $q->select('column_id,name');
            }, 'news.area'   => function ($q) {
                $q->select('area_id,name');
            }])
            ->select([
                "live.live_id",
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
            ->where(['live.live_id' => $live_id])
            ->asArray()->one();
        print_r($list);die;
        $list['news_column'] = ['column_id' => '', 'column_type' => '', 'column_name' => ''];
        if ($list['news']['column_id']) {
            $list['news_column']['column_id'] = $list['news']['column_id'];
            $list['news_column']['column_type'] = '0';
            $list['news_column']['column_name'] = $list['news']['column']['name'];
        }
        if ($list['news']['area']) {
            $list['news_column']['column_id'] = $list['news']['area_id'];
            $list['news_column']['column_type'] = '1';
            $list['news_column']['column_name'] = $list['news']['area']['name'];
            
        }
        unset($list['news']);
        
        return $list;
    }
    
    //获取 录播列表
    public static function getRecordList($admin_id, $page, $count)
    {
        $offset = ($page - 1) * $count;
        $list = Live::find()->with(['news'   => function ($q) {
                    $q->select('live_id,column_id,area_id');
                },'news.column' => function ($q) {
                    $q->select('column_id,name');
                },
                'news.area'   => function ($q) {
                    $q->select('area_id,name');
                }])
                ->select([
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
                ->where("category = 6 and creator_id = " . $admin_id . " and status = 5 ")
                ->orderBy(['case  when `year` is null then `year1` else `year` end'    => SORT_DESC,
                           'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
                           'case  when `day` is null then `day1` else `day` end'       => SORT_DESC,
                           'weight'                                                    => SORT_DESC,
                           'refresh_time'                                              => SORT_DESC,
                           'create_time'                                               => SORT_DESC])
                ->offset($offset)
                ->limit($count)
                ->asArray()
                ->all();
     
        foreach ($list as $key => $value) {
            
            $list[$key]['news_column'] = ['column_id'=>'','column_name'=>'','column_type'=>''];
            if ($value['news']['column_id']) {
                $list[$key]['news_column']['column_id'] = $value['news']['column_id'];
                $list[$key]['news_column']['column_name'] = $value['news']['column']['name'];
                $list[$key]['news_column']['column_type'] = '0';
            }
            if ($value['news']['area']) {
                $list[$key]['news_column']['column_id'] = $value['news']['area_id'];
                $list[$key]['news_column']['column_name'] = $value['news']['area']['name'];
                $list[$key]['news_column']['column_type'] = '1';
            }
            unset($list[$key]['news']);
            
        }
        
        return $list;
    }
    
    
    /**
     *
     *  返回后台管理员所有频道
     *
     * @param $admin_id int 管理员id
     *
     * @return array
     */
    public static function getBackAdminColumnsList($admin_id,$view=0)
    {
        //$editors=NewsColumn::find()->innerJoin('news_column_admin','news_column.column_id=news_column_admin.column_id ')->where(['admin_id'=>$admin_id])
        //->asArray()->all();
        
        if ($admin_id == 1) {
            $admin_columns = NewsColumn::find()->where(['!=','name','视频'])->andWhere(['!=', 'name', '图说'])->andWhere(['!=','status',0])->asArray()->all();
            if ($view) {
                $default_column = [
                    "column_id"   => 0,
                    "name"        => "全部",
                    "weight"      => 0,
                    "column_type" => 0,
                ];
                array_push($admin_columns, $default_column);
            }
            $admin_areas = Area::find()->asArray()->all();
        }else{
            $admin = AdminUser::find()->where(['admin_id' => $admin_id])->one();
            if (!$admin) {
                return ['error' => '未查到此管理员'];
            }
            $review_power = AdminRole::findRole($admin_id);
            if(!$review_power){
                $all_columns = [[
                    "column_id" => -100,
                   "name"      => "高管默认栏目",
                   "weight"    => 0,
                   "column_type"=>0,
               ]];
                return  $all_columns;
            }
            $admin_columns = $admin->newsColumns;
            //print_r($admin_columns);die;
            //所有账户屏蔽视频和图说栏目,以及状态未开启的栏目
            foreach ($admin_columns as $key=> $col){
                if($col->name=='视频'||$col->name=='图说'||$col->status==0){
                    unset($admin_columns[$key]);
                }
            }
            $admin_areas   = $admin->areas;
        }
        $admin = AdminUser::find()->where(['admin_id' => $admin_id])->one();
        $all_columns = ArrayHelper::merge($admin_columns, $admin_areas);
        $all_columns = ArrayHelper::toArray($all_columns);
        //未分栏目的用户判定
        if(!count($all_columns)){
            $all_columns = [[
                "column_id"   => -200,
                "name"        => "系统默认栏目",
                "weight"      => 0,
                "column_type" => 0,
            ]];
            return $all_columns;
        }
        $splice=-1;
        foreach ($all_columns as $key => $value) {
            if (isset($value['column_id'])) {
                $all_columns[$key]['column_type'] = 0;
                if($value['column_id']==2){
                    $splice= $key;
                    
                }
            }
            if (isset($value['area_id'])) {
                $all_columns[$key]['column_id'] = $value['area_id'];
                $all_columns[$key]['column_type'] = 1;
            }
        }
        if($splice!=-1){
            array_splice($all_columns, $splice, 1);
        }
        
        return $all_columns;
        
    }
    
    
    /**
     * 返回当前频道的直播新闻列表
     *
     * @param  $column_type
     * @param  $column_id
     * @param  $news_status //新闻状态 待审核3 未通过审核4 通过审核的发布状态0
     *
     * @return array
     */
    public static function newsIdsWithLiveType($column_type, $column_id, $news_status = 0)
    {
        
        
        // 传入栏目id，获取直播对应新闻列表
        if (!$column_type) {
            $column_where = ['column_id' => $column_id];
            
        } else {
            $column_where = ['area_id' => $column_id];
        }
        $news = News::find()->select(['news_id', 'column_id', 'area_id'])->where($column_where)->andWhere(['!=', 'live_id', 0])->andWhere(['=', 'status', $news_status])->select(['news_id'])->asArray()->all();
        //print_r($news);die;
        //$aaa= ArrayHelper::toArray($editors);
        $news_ids = [];
        if (!empty($news)) {
            
            $news_ids = ArrayHelper::getColumn($news, 'news_id');
            
            //print_r($news_ids);die;
        }
        
        return $news_ids;
        
    }
    
    
    public static function getBackLiveList($admin_id, $name, $type, $status=0, $page, $count, $gao_admin = 0, $column_id, $column_type)
    {
        
        
        $offset = ($page - 1) * $count;
        //解决mysql 当机问题
        self::getDb()->createCommand('SET SESSION wait_timeout = 28800;')->execute();
        $where = "is_fast = 1 and reviewed_status = 0 ";
        
        if($admin_id==1 && $column_id==0){
            //针对超管栏目下的“全部”标签不显示已删除
            $status = -1;
        }
        if ($column_id) {
            
            //返回直播新闻ids数组
            $news_ids = self::newsIdsWithLiveType($column_type, $column_id);
            if ($news_ids) {
                $news_ids = implode(",", $news_ids);
                $where .= ' and news_id in(' . $news_ids . ')';
            }else{
                $return['totalCount'] = 0;
                $return['live_list'] = [];
    
                return $return;
            }
        }
        if ($admin_id != 1 && $gao_admin != 0 && !$column_id) {
            
            
            $where .= " and creator_id = '" . $admin_id . "'";
            
            
        }
        if (!empty($name)) {
            $where .= "and name like '%$name%'";
        }
        if (!empty($type)) {
            if ($type == 7) {
                $where .= " and category in(1,4)";
            } else {
                $where .= " and category = '" . $type . "'";
            }
            
        }
        if (-1 != $status) {
            $time = date('Y-m-d H:i:s', time());
            //状态 1 正常 0 删除 2 直播结束 3 未开始 4 正在直播 5 直播回顾
            if (3 == $status) {
                $where .= " and status in (1,3) and start_time > '" . $time . "'";
            } else if (2 == $status) {
                $where .= " and status = 2 ";
            } else if (4 == $status) {
                $where .= " and status in (1,4) and start_time <= '" . $time . "'";
                $andwhere['status'] = array('IN', array('1', '4'));
                $andwhere['start_time'] = array('ELT', date('Y-m-d H:i:s'));
            } else if (5 == $status) {
                $where .= " and status = 5 ";
                $andwhere['status'] = array('EQ', 5);
            }
        } else {
            //0 为删除
            $where .= " and status != 0 ";
            $andwhere['status'] = array('NEQ', 0);
        }
        $entry = Entry::ExistFastLiveEntry($column_type, $column_id);
        
            $first_where= [
                'CASE WHEN refresh_time <> 0 THEN DATE_FORMAT(refresh_time, \'%y%m%d\') ELSE DATE_FORMAT(create_time, \'%y%m%d\') END' => SORT_DESC,
                'weight'                                                                                                               => SORT_DESC,
                'refresh_time'                                                                                                         => SORT_DESC,
                'create_time'                                                                                                          => SORT_DESC
            ];
        
        $list = Live::find()->where($where)->select([
                "live_id",
                "name",
                "type",
                "category",
                "rever_url",
                "start_time",
                "refresh_time",
                "status",
                "play_count",
                "true_play_count",
                "creator_id",
                "weight",
                'news_id',
               ]
        )->orderBy($first_where)->asArray()->all();
        if (count($list) > 0) {
            //判定当前栏目是否开启篮子
            $basket_is_active =1;
            $basket_count=0;
            $basket_check = Basket::BasketCheck($column_type, $column_id, $basket_is_active);
            if($basket_check){
                $basket_count = 1;
            }

            foreach ($list as $key => $value) {
               $list[$key]['basket_is_open']=$basket_count;
                
                $list[$key]['is_entry'] = 0;
                $admin_info = AdminUser::find()->alias('a')->leftJoin('vrnews1.company c', 'a.company_id = c.company_id')->select("a.username, a.real_name,c.name")->where(['admin_id' => $value['creator_id']])->asArray()->one();
                $list[$key]['source'] = $admin_info['name'];
                if (!empty($admin_info['name'])) {
                    $list[$key]['source'] = $admin_info['name'] . ':' . $admin_info['real_name'];
                }else{
                    $list[$key]['source'] =  $admin_info['real_name'];
                }
                $list[$key]['creator_name'] = $admin_info['username'];
                $list[$key]['status'] = static::getLiveStatus($value['start_time'], $value['status']);
                if (isset($entry['entry_id'])) {
                    if ($entry['entry_id'] == $value['news_id']) {
                        $list[$key]['is_entry']=1;
                        $temp = $list[$key];
                        unset($list[$key]);
                        array_unshift($list, $temp);
                    }
        
                }
            }
            if($count) {
                $list = array_slice($list, $offset, $count);
            }
            
        }
       
        
        $total_count = Live::find()->where($where)->count();
        $return['totalCount'] = $total_count;
        $return['live_list'] = $list;
        
        return $return;
    }
    
    
    /*
	 * 快直播列表
     *
	 * */
    public static function fastliveList($page, $size, $user_id, $is_pc, $column_id, $column_type, $cover_id = 0)
    {
        $offset = ($page - 1) * $size;
        //供生产环境测试使用
        $test_where = " 1 ";
        $test_uid = array('201614754871496408', '20161479954020413', '201614829033721473', '201714930951045991', '201614751389465462', '201614802974723737', '201614762909851436', '201614764605844322', '201714999087559769', '201714842110831357', '201614764363288566', '201714990988402222');
        $test_adminid_str = "48,74,88,89,141,163,164,165,166,167,168,169,174,175,176";
        if (Yii::$app->params['environment'] == 'prod') {
            if (!$user_id || !in_array($user_id, $test_uid)) {
                $test_where = " li.creator_id not in(" . $test_adminid_str . ")";
            }
        }
        $where = array();
        $where['is_fast'] = 1;
        $collect = ['column_id', 'area_id'];
        if ($column_id) {
            $collect = ['column_id', 'area_id'];
            $lives = Live::find()->alias('li')
                ->leftJoin('vrnews1.news n', 'n.news_id =li.news_id')
                ->where(['li.is_fast' => 1])
                ->andWhere(['!=', 'li.status', 0])
                ->andWhere(['n.' . $collect[$column_type] => $column_id])
                ->asArray()->all();
            //$value['news']['fast_live_count'] = count($lives);
        }
        $trans_where = "  case when category = 6  then rever_url<>'' else (rever_url is null or rever_url is not null) end";
        $list = Live::find()
            ->alias('li')
            ->leftJoin('vrnews1.news n', 'n.news_id =li.news_id')
            ->where($where)
            ->andWhere($trans_where)
            ->andWhere($test_where)
            ->andWhere(['>=', 'li.weight', 70])
            ->andWhere(['!=', 'li.status', 0])
            ->andWhere(['=', 'li.reviewed_status', 0])
            ->andWhere(['n.' . $collect[$column_type] => $column_id])
            ->select([
                "li.live_id",
                "li.name",
                "li.start_time",
                "li.image_url",
                "li.type",
                "li.category",
                "li.red_competitor_id",
                "li.blue_competitor_id",
                "li.red_news_id",
                "li.blue_news_id",
                "li.red_score",
                "li.blue_score",
                "li.status",
                "li.quiz_status",
                "li.news_status",
                "li.is_props",
                "li.reviewed_status",
                "year(li.create_time) as year1",
                "month(li.create_time) as month1",
                "day(li.create_time) as day1",
                "year(li.refresh_time) as year",
                "month(li.refresh_time) as month",
                "day(li.refresh_time) as day",
                "li.refresh_time",
                "li.screen",
                "li.creator_id",
                "li.is_fast",
                "li.news_id",
                "n.news_id",
                "n.column_id",
                "n.area_id"
            ])->limit($size)->offset($offset)->orderBy([
                'CASE WHEN li.refresh_time <> 0 THEN DATE_FORMAT(li.refresh_time, \'%y%m%d\') ELSE DATE_FORMAT(li.create_time, \'%y%m%d\') END' => SORT_DESC,
                'li.weight'                                                                                                                     => SORT_DESC,
                'li.refresh_time'                                                                                                               => SORT_DESC,
                'li.create_time'                                                                                                                => SORT_DESC
            ])->asArray()->all();
        $count = Live::find()
            ->alias('li')
            ->leftJoin('vrnews1.news n', 'n.news_id =li.news_id')
            ->where($where)
            ->andWhere($trans_where)
            ->andWhere(['>=', 'li.weight', 70])
            ->andWhere(['!=', 'li.status', 0])
            ->andWhere(['=', 'li.reviewed_status', 0])
            ->andWhere(['n.' . $collect[$column_type] => $column_id])
            ->count();
        if (!empty($list)) {
            
            
            foreach ($list as $key => $val) {
                
                
                $list[$key]['start_time'] = date('m/d H:i', strtotime($val['start_time']));
                $red_info = LiveCompetitor::findOne($val['red_competitor_id']);
                $list[$key]['red_name'] = $red_info['real_name'];
                $list[$key]['red_photo'] = $red_info['avatar'];
                $blue_info = LiveCompetitor::findOne($val['blue_competitor_id']);
                $list[$key]['blue_name'] = $blue_info['real_name'];
                $list[$key]['blue_photo'] = $blue_info['avatar'];
                $list[$key]['status'] = self::getLiveStatus($val['start_time'], $val['status']);
                $list[$key]['is_subscribe'] = 0;
                $list[$key]['chatroom_id'] = 'room_' . $val['live_id'];
                if (!empty($user_id)) {
                    $is_subscribe = LiveUserSubscribe::find()->where(['user_id' => $user_id, 'live_id' => $val['live_id'], 'status' => 1])->count();
                    $list[$key]['is_subscribe'] = $is_subscribe;
                }
                if (empty($is_pc)) {
                    $list[$key]['image_url'] = $val['image_url'] . '?imageMogr2/thumbnail/562.5x270!';
                    $list[$key]['fast_live_count'] = $count;
                }
                if ($cover_id) {
                    if ($cover_id == $val['news_id']) {
                        $temp = $list[$key];
                        unset($list[$key]);
                        array_unshift($list, $temp);
                    }
                    
                }
            }
        }
        $info = array('totalCount' => $count, 'list' => $list);
        
        return $info;
    }
    
    //快直播 待审核列表
    public static function getBackWaitList($admin_id, $name, $type, $page, $count, $admin_type, $column_id, $column_type)
    {
        $offset = ($page - 1) * $count;
        $where = " 1=1 and is_fast = 1 and reviewed_status = 1  and  status != 0 ";
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
        $news_ids = [];
        $total_count = 0;
        
        if ($admin_id != 1 && !$admin_type && $column_id) { //传入栏目id,则返回本栏目直播
            //返回直播新闻ids数组
            $news_status = 3;//当快直播还未通过审核，其对应的新闻状态为待审核状态，值为3
            $news_ids = self::newsIdsWithLiveType($column_type, $column_id, $news_status);
            if (count($news_ids)>0) {
                $news_ids = implode(",", $news_ids);
                $where .= ' and news_id in(' . $news_ids . ')';
            }else{
    
                $return['totalCount'] = $total_count;
                $return['live_list'] = $news_ids;
                return $return;
                
            }
        }
        if ($admin_id != 1 && !$admin_type && !$column_id) { //普通账号,且没有传栏目 只能看见自己创建的
            $where .= " and creator_id = '" . $admin_id . "'";
        }
        if (!empty($name)) {
            $where .= " and name like '%$name%'";
        }
        if (!empty($type)) {
            $where .= " and category = '" . $type . "'";
        }
        
            $list = Live::find()->with(['news' => function ($q) { $q->select('live_id,news_id,column_id,area_id,type_id');
            }, 'news.column' => function ($q) {  $q->select('column_id,name'); }, 'news.area'   => function ($q) {
                $q->select('area_id,name');}])->where($where)->select([
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
        
        if (count($list) > 0) {
            foreach ($list as $key => $value) {
                $admin_info = AdminUser::find()->alias('a')->leftJoin('vrnews1.company c', 'a.company_id = c.company_id')->select("a.username, a.real_name,c.name")->where(['admin_id' => $value['creator_id']])->asArray()->one();
                $list[$key]['source'] = $admin_info['real_name'];
                $list[$key]['creator_name'] = $admin_info['real_name'];
                if(!empty($admin_info['name'])){
                    $list[$key]['source'] = $admin_info['name'] . ':' . $list[$key]['source'];
                    $list[$key]['creator_name'] = $admin_info['name'] . ':'.$admin_info['real_name'];
                }
                
                $list[$key]['status1'] = static::getLiveStatus($value['start_time'], $value['status']);
                $list[$key]['news']['column_name'] = '无栏目'; //栏目默认名
                if ($value['news']['column_id']) {
                    $list[$key]['news']['column_name'] = $value['news']['column']['name']; //栏目名
                    $list[$key]['news']['column_type'] = 0; //栏目类型
                }
                if ($value['news']['area_id']) {
                    $list[$key]['news']['area_name'] = $value['news']['area']['name']; //地区栏目名
                    $list[$key]['news']['column_name'] = $value['news']['area']['name']; //地区栏目名
                    $list[$key]['news']['column_id'] = $value['news']['area_id']; //地区栏目id
                    $list[$key]['news']['column_type'] = 1; //栏目类型
                }
                
                unset($list[$key]['news']['column']);
                unset($list[$key]['news']['area']);
            
            }
            
                $total_count = Live::find()->where($where)->count();
            
            
        }
        $return['totalCount'] = $total_count;
        $return['live_list'] = $list;
        
        return $return;
    }

//快直播 审核未通过列表
    public static function getBackNopassList($admin_id, $name, $type, $page, $count, $admin_type, $column_id, $column_type)
    {
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
        $total_count = 0;
       
        
        if ($admin_id!=1 && !$admin_type && $column_id) { //普通账号,传入栏目id,则返回本栏目直播
            
            //返回直播新闻ids数组
            $news_status = 4;//快直播审核未通过对应的news状态
            $news_ids = self::newsIdsWithLiveType($column_type, $column_id, $news_status);
            
            if (count($news_ids)>0) {
                $news_ids = implode(",", $news_ids);
                $where .= ' and news_id in(' . $news_ids . ')';
            }else {
    
                $return['totalCount'] = $total_count;
                $return['live_list'] = [];
                return $return;
            }
            
        }
        if ($admin_id != 1 && !$admin_type && !$column_id) { //普通账号,且没有传栏目 只能看见自己创建的
            $where .= " and creator_id = '" . $admin_id . "'";
        }
        if (!empty($name)) {
            $where .= " and name like '%$name%'";
        }
        if (!empty($type)) {
            $where .= " and category = '" . $type . "'";
        }
        $list = Live::find()->with(['news' => function ($q) {
            $q->select('live_id,news_id,column_id,area_id,type_id');
        }, 'news.column'                   => function ($q) {
            $q->select('column_id,name');
        }, 'news.area'                     => function ($q) {
            $q->select('area_id,name');
        }])->where($where)->andWhere(['!=', 'status', 0])->select([
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
        if (count($list) > 0) {
            foreach ($list as $key => $value) {
                $admin_info = AdminUser::find()->alias('a')->leftJoin('vrnews1.company c', 'a.company_id = c.company_id')->select("a.username,a.real_name, c.name")->where(['admin_id' => $value['creator_id']])->asArray()->one();
                $list[$key]['source'] = $admin_info['name'];
                if (!empty($admin_info['name'])) {
                    $list[$key]['source'] = $admin_info['name'] . ':' . $admin_info['real_name'];
                } else {
                    $list[$key]['source'] = $admin_info['real_name'];
                }
                $list[$key]['creator_name'] = $admin_info['username'];
                $list[$key]['status'] = static::getLiveStatus($value['start_time'], $value['status']);
                if($value['news']['column_id']){
                $list[$key]['news']['column_name'] = $value['news']['column']['name']; //栏目名
            }
                if ($value['news']['area_id']) {
                    $list[$key]['news']['area_name'] = $value['news']['area']['name']; //地区栏目名
                }
                unset($list[$key]['news']['column']);
                unset($list[$key]['news']['area']);
            }
            $total_count = Live::find()->where($where)->andWhere(['!=', 'status', 0])->count();
        }
        
        
            $return['totalCount'] = $total_count;
            $return['live_list'] = $list;
            return $return;
    }
    
    public
    function getNews()
    {
        return $this->hasOne(News::className(), ['live_id' => 'live_id']);
        
    }
    
}

