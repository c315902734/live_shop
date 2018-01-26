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
class LiveSection extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_section';
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
            [['section_id'], 'required'],
            [['live_id','section_id', 'creator_id'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['introduction'], 'string'],
            [['image_url', ], 'string', 'max' => 200],
            [['title'], 'string', 'max' => 60],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'live_id' => 'Live ID',
            'image_url' => 'Image Url',
            'title' => 'Title',
            'introduction' => 'Introduction',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
			'refresh_time' => 'Refresh Time',
            'creator_id' => 'Creator ID',
			'news_id' => 'News ID',
			'is_fast' => 'Is Fast',
			'show_type' => 'Show Type',
			'good_id' => 'Good Id',
        ];
    }

	/**
	 * 系列直播列表 (直播中、已结束、回顾)
	 */
	public static function LiveList($column_type,$column_id, $user_id,$is_pc,$page, $size){
		$offset = ($page - 1) * $size;
		$where = '1';
//		$where = "is_fast = 1";
		//普通直播列表里包含高管创建的快直播
		if(empty($is_pc)) {
			$where .= ' and (is_fast = 0 or live_tags_relation.tag_id =2)';
		}
		if($column_type && $column_id){
			if($column_type == 0){
				$where .= " and news.column_id = ".$column_id;
			}else{
				$where .= " and news.area_id = ".$column_id;
			}
		}

		$list = LiveSection::find()
			->leftJoin('live_tags_relation', 'live_tags_relation.live_id = live_section.section_id')
			->leftJoin('vrlive.live_new', 'live_new.live_id = live_section.live_id')
			->leftJoin('vrnews1.news', 'live_new.news_id = news.news_id')
			->where($where)
//			->andWhere(['>=', 'live_section.weight', 70])
			->andWhere(['live_section.status'=>[2,4,5]])
//			->andWhere(['=', 'live_section.reviewed_status', 0])
			->select([
				"case `live_section`.`status` when 4 then 6 when 2 then 7 when 5 then 8 else `live_section`.`status` end as `status_a`", 
				"live_new.type",
				"live_new.name",
				"live_new.is_fast",
				"live_new.live_count",
				"live_section.title",
				"live_section.section_id",
				"live_section.live_id",
        		"live_section.start_time",
        		"live_section.image_url",
        		"live_section.status",
        		"live_section.play_count",
        		"live_section.screen",
        		"live_section.show_type",
				"live_section.price",
				"live_section.password",
				"live_section.phones",
        		"live_section.good_id"
				]
			)->orderBy(['status_a' => SORT_ASC,'live_section.refresh_time'=>SORT_DESC])
			->limit($size)->offset($offset)
			->asArray()->all();

		$count = LiveSection::find()
			->leftJoin('live_tags_relation', 'live_tags_relation.live_id = live_section.section_id')
			->leftJoin('vrlive.live_new', 'live_new.live_id = live_section.live_id')
			->leftJoin('vrnews1.news', 'live_new.news_id = news.news_id')
			->where($where)
//			->andWhere(['>=', 'live_section.weight', 70])
			->andWhere(['live_section.status'=>[2,4,5]])
//			->andWhere(['=', 'live_section.reviewed_status', 0])
			->groupBy("live_section.section_id")
			->count();

		if(!empty($list)){
			foreach($list as $key=>$val){
				// 直播名正则
				if (!preg_match('/^\"\\\u76f4\\\u64ad\|/u', json_encode($val['title']))) {
					$list[$key]['title'] = '直播| ' . $val['title'];
				}
				$list[$key]['start_time'] = date('m/d H:i', strtotime($val['start_time']));
				$list[$key]['chatroom_id']  = 'room_'.$val['section_id'];
				if(empty($is_pc)){
					$list[$key]['image_url']  = $val['image_url'] . '?imageMogr2/thumbnail/562.5x270!';
				}
				
				//查看当前用户是否付费
				$user_pay = 0; //默认未付费
				if($user_id){
					$shop_order = ShopOrder::getGoodsPayStatusByUser($user_id,$val['good_id']);
					if($shop_order){
						$user_pay = 1;
					}
				}
				$list[$key]['user_pay'] = $user_pay;
				unset($list[$key]['status_a']);
				//返回 直播 所以标签
				$list[$key]['all_tags'] = LiveTagsRelation::getSection_alltags($val['section_id']);
			}
		}
		$info = array('totalCount'=>$count,'list'=>$list);
		return $info;
	}

	/**
	 * 系列直播列表 （未开始）
	 */
	public static function WaitList($column_type,$column_id, $user_id,$is_pc,$page, $size){
		$offset = ($page - 1) * $size;
		$where = '1';
//		$where = "is_fast = 1";
		//普通直播列表里包含高管创建的快直播
		if(empty($is_pc)) {
			$where .= ' and (is_fast = 0 or live_tags_relation.tag_id =2)';
		}
		if($column_type && $column_id){
			if($column_type == 0){
				$where .= " and news.column_id = ".$column_id;
			}else{
				$where .= " and news.area_id = ".$column_id;
			}
		}
		$list = LiveSection::find()
			->leftJoin('live_tags_relation', 'live_tags_relation.live_id = live_section.section_id')
			->leftJoin('vrlive.live_new', 'live_new.live_id = live_section.live_id')
			->leftJoin('vrnews1.news', 'live_new.news_id = news.news_id')
			->where($where)
//			->andWhere(['>=', 'live_section.weight', 70])
			->andWhere(['live_section.status'=>[1,3]])
//			->andWhere(['=', 'live_section.reviewed_status', 0])
			->select([
					"live_new.type",
					"live_new.name",
					"live_new.is_fast",
					"live_new.live_count",
					"live_section.title",
					"live_section.section_id",
					"live_section.live_id",
					"live_section.start_time",
					"live_section.image_url",
					"live_section.status",
					"live_section.play_count",
					"live_section.screen",
					"live_section.show_type",
					"live_section.price",
					"live_section.password",
					"live_section.phones",
					"live_section.good_id"
				]
			)->orderBy(['live_section.refresh_time'=>SORT_DESC])
			->limit($size)->offset($offset)
			->asArray()->all();

		$count = LiveSection::find()
			->leftJoin('live_tags_relation', 'live_tags_relation.live_id = live_section.section_id')
			->leftJoin('vrlive.live_new', 'live_new.live_id = live_section.live_id')
			->leftJoin('vrnews1.news', 'live_new.news_id = news.news_id')
			->where($where)
//			->andWhere(['>=', 'live_section.weight', 70])
			->andWhere(['live_section.status'=>[1,3]])
//			->andWhere(['=', 'live_section.reviewed_status', 0])
			->groupBy("live_section.section_id")
			->count();

		if(!empty($list)){
			foreach($list as $key=>$val){
				// 直播名正则
				if (!preg_match('/^\"\\\u76f4\\\u64ad\|/u', json_encode($val['title']))) {
					$list[$key]['title'] = '直播| ' . $val['title'];
				}
				$list[$key]['start_time'] = date('m/d H:i', strtotime($val['start_time']));
				$list[$key]['chatroom_id']  = 'room_'.$val['section_id'];
				if(empty($is_pc)){
					$list[$key]['image_url']  = $val['image_url'] . '?imageMogr2/thumbnail/562.5x270!';
				}
				//查看当前用户是否付费
				$user_pay = 0; //默认未付费
				if($user_id){
					$shop_order = ShopOrder::getGoodsPayStatusByUser($user_id,$val['good_id']);
					if($shop_order){
						$user_pay = 1;
					}
				}
				$list[$key]['user_pay'] = $user_pay;
				//返回 直播 所以标签
				$list[$key]['all_tags'] = LiveTagsRelation::getSection_alltags($val['section_id']);
			}
		}
		$info = array('totalCount'=>$count,'list'=>$list);
		return $info;
	}

	/**
	 * 后台 未开始直播列表
	 */
	public static function BackWaitList($column_type,$column_id,$admin_id, $name,$type,$live_name,  $page, $count,$gao_admin){
		$offset = ($page - 1) * $count;
		$where = " 1=1 and a.is_fast = 1 and a.status != 0 and live_section.status in (1,3)";

		//权限过滤
		if($admin_id != 1 && $gao_admin != 0){
			$where .= " and live_section.creator_id = '".$admin_id."'";
		}

		//标题 筛选
		if(!empty($name)){
			$where .= " and live_section.title like '%$name%'";
		}
		//系列名 筛选
		if(!empty($live_name)){
			$where .= " and a.name like '%$live_name%'";
		}

		//直播类型 1视频直播，2图文直播 3图文+视频 筛选
		$having = '1';
		$select = "e.id";
		$where .= " and (e.tag_id = 5 or e.tag_id = 7) ";
		if($type == 1){ //5
			$select = "group_concat(e.tag_id) as aa ";
			$having = " left(aa,1) != 7 and right(aa,1) != 7 ";
//			$where .= " and live_section.is_video = 1";
		}else if($type == 2){ //7
			$select = "group_concat(e.tag_id) as aa ";
			$having = " left(aa,1) != 5 and right(aa,1) != 5 ";
//			$where .= " and live_section.is_textvideo = 1";
		}else if($type == 3){
//			$where .= " and (e.tag_id = 5 or e.tag_id = 7)";
			$select = "Length(group_concat(e.tag_id)) as aa ";
			$having = " aa > 1";
		}

		//匹配 栏目
		if($column_type == 0){
			$where .= " and column_id = ".$column_id;
		}else{
			$where .= " and area_id = ".$column_id;
		}

		$list = LiveSection::find()
			->leftJoin('vrlive.live_new a','live_section.live_id = a.live_id')
			->leftJoin('vrnews1.news b','b.live_id = a.live_id')
			->leftJoin('vrlive.live_tags_relation e','e.live_id = live_section.section_id')
			->where($where)
//			->andWhere(['!=', 'a.status', 0])
//			->andWhere(['live_section.status'=>[1,3]])
//				->andWhere(['=', 'news.status', 0])
			->select([
					$select,
					"a.type",
					"a.live_id",
					"a.name",
					"live_section.section_id",
					"live_section.title",
					"live_section.is_video",
					"live_section.is_textvideo",
					"live_section.start_time",
					"live_section.refresh_time",
					"live_section.status",
					"live_section.play_count",
					"live_section.true_play_count",
					"live_section.creator_id",
					"b.news_id"
				]
			)->limit($count)->offset($offset)->orderBy([
				'CASE WHEN live_section.refresh_time <> 0 THEN DATE_FORMAT(live_section.refresh_time, \'%y%m%d\') ELSE DATE_FORMAT(live_section.create_time, \'%y%m%d\') END'=>SORT_DESC,
				'live_section.refresh_time' => SORT_DESC,
				'live_section.create_time' => SORT_DESC
			])->groupBy("section_id")
			->having($having)
			->asArray()->all();
		if(count($list) > 0){
			foreach ($list as $key=>$value){
				$admin_info = AdminUser::find()->alias('a')->leftJoin('vrnews1.company c','a.company_id = c.company_id')->select("a.username, c.name")->where(['admin_id'=>$value['creator_id']])->asArray()->one();
				$list[$key]['source'] = $admin_info['username'];
				if(!empty($admin_info['name'])){
					$list[$key]['source'] = $admin_info['name'].':'.$admin_info['username'];
				}
				//查看是否 有视频直播标签
				$video_tag = LiveTagsRelation::getSection_tags($value['section_id'],5,1);
				if($video_tag){
					$list[$key]['is_video'] = 1;
				}
				//查看是否 有图文直播标签
				$textvideo_tag = LiveTagsRelation::getSection_tags($value['section_id'],7,1);
				if($textvideo_tag){
					$list[$key]['is_textvideo'];
				}
				//返回所有 标签
				$all_tags = LiveTagsRelation::getSection_alltags($value['section_id']);
				$list[$key]['all_tags'] = $all_tags;
//				$list[$key]['status'] = static::getLiveStatus($value['start_time'], $value['status']);
			}
		}
		$total_count = LiveSection::find()
			->leftJoin('vrlive.live_new a','live_section.live_id = a.live_id')
			->leftJoin('vrnews1.news b','b.live_id = a.live_id')
			->leftJoin('vrlive.live_tags_relation e','e.live_id = live_section.section_id')
			->where($where)
//			->andWhere(['!=', 'a.status', 0])
//			->andWhere(['live_section.status'=>[1,3]])
			->select($select)
			->groupBy("section_id")
			->having($having)
//			->andWhere(['=', 'news.status', 0])
			->count();
		$return['totalCount'] = $total_count;
		$return['live_list'] = $list;
		return $return;
	}

	/**
	 * 后台 (直播中、已结束、回顾)直播列表
	 */
	public static function BackLiveList($column_type,$column_id,$admin_id, $name,$type,$live_name,  $page, $count,$gao_admin){
		$offset = ($page - 1) * $count;
		$where = " 1=1 and a.is_fast = 1 and a.status != 0 and live_section.status in (2,4,5)";

		//权限过滤
		if($admin_id != 1 && $gao_admin != 0){
			$where .= " and live_section.creator_id = '".$admin_id."'";
		}

		//标题 筛选
		if(!empty($name)){
			$where .= " and live_section.title like '%$name%'";
		}
		//系列名 筛选
		if(!empty($live_name)){
			$where .= " and a.name like '%$live_name%'";
		}

		//直播类型 1视频直播，2图文直播 3图文+视频 筛选
		$select = "e.id";
		$having = 1;
		$where .= " and (e.tag_id = 5 or e.tag_id = 7)";
		if($type == 1){
			$having = " left(aa,1) != 7 and right(aa,1) != 7 ";
			$select = "group_concat(e.tag_id) as aa ";
//			$where .= " and live_section.is_video = 1";
		}else if($type == 2){ //7
			$having = " left(aa,1) != 5 and right(aa,1) != 5 ";
			$select = "group_concat(e.tag_id) as aa ";
//			$where .= " and live_section.is_textvideo = 1";
		}else if($type == 3){
//			$where .= " and (e.tag_id = 5 or e.tag_id = 7)";
			$select = "Length(group_concat(e.tag_id)) as aa ";
			$having = " aa > 1";
		}


		//匹配 栏目
		if($column_type == 0){
			$where .= " and column_id = ".$column_id;
		}else{
			$where .= " and area_id = ".$column_id;
		}

		$list = LiveSection::find()
			->leftJoin('vrlive.live_new a','live_section.live_id = a.live_id')
			->leftJoin('vrnews1.news b','b.live_id = a.live_id')
			->leftJoin('vrlive.live_tags_relation e','e.live_id = live_section.section_id')
			->where($where)
//			->andWhere(['!=', 'a.status', 0])
//			->andWhere(['live_section.status'=>[2,4,5]])
//				->andWhere(['=', 'news.status', 0])
			->select([
					"case `live_section`.`status` when 4 then 6 when 2 then 7 when 5 then 8 else `live_section`.`status` end as `status_a`",
					$select,
					"a.type",
					"a.live_id",
					"a.name",
					"live_section.section_id",
					"live_section.is_video",
					"live_section.is_textvideo",
					"live_section.title",
					"live_section.start_time",
					"live_section.create_time",
					"live_section.refresh_time",
					"live_section.status",
					"live_section.play_count",
					"live_section.true_play_count",
					"live_section.creator_id",
					"b.news_id"
				]
			)->orderBy(['status_a' => SORT_ASC,'live_section.refresh_time'=>SORT_DESC])
			->limit($count)->offset($offset)
			->groupBy("section_id")
			->having($having)
			->asArray()->all();
		if(count($list) > 0){
			foreach ($list as $key=>$value){
				$admin_info = AdminUser::find()->alias('a')->leftJoin('vrnews1.company c','a.company_id = c.company_id')->select("a.username, c.name")->where(['admin_id'=>$value['creator_id']])->asArray()->one();
				$list[$key]['source'] = $admin_info['username'];
				if(!empty($admin_info['name'])){
					$list[$key]['source'] = $admin_info['name'].':'.$admin_info['username'];
				}
				//查看是否 有视频直播标签
				$video_tag = LiveTagsRelation::getSection_tags($value['section_id'],5,1);
				if($video_tag){
					$list[$key]['is_video'] = 1;
				}
				//查看是否 有图文直播标签
				$textvideo_tag = LiveTagsRelation::getSection_tags($value['section_id'],7,1);
				if($textvideo_tag){
					$list[$key]['is_textvideo'];
				}
				//返回所有 标签
				$all_tags = LiveTagsRelation::getSection_alltags($value['section_id']);
				$list[$key]['all_tags'] = $all_tags;
//				$list[$key]['status'] = static::getLiveStatus($value['start_time'], $value['status']);
			}
		}
		$total_count = LiveSection::find()
			->leftJoin('vrlive.live_new a','live_section.live_id = a.live_id')
			->leftJoin('vrnews1.news b','b.live_id = a.live_id')
			->leftJoin('vrlive.live_tags_relation e','e.live_id = live_section.section_id')
			->where($where)
//			->andWhere(['live_section.status'=>[2,4,5]])
//			->andWhere(['!=', 'a.status', 0])
			->select($select)
			->groupBy("section_id")
			->having($having)
//			->andWhere(['=', 'news.status', 0])
			->count();
		$return['totalCount'] = $total_count;
		$return['live_list'] = $list;

		return $return;
	}

	/*
	 * 查看直播详情
	 * */
	public static function getLiveById($liveId){
		$live = static::find()->where(['section_id' => $liveId])->asArray()->one();
		//查看对应的 系列 标题和 简介
		$live_rev = LiveNew::find()->where(["live_id"=>$live['live_id']])->asArray()->one();
		$live['section_title'] = '';
		$live['section_introduction'] = '';
		if($live_rev){
			$live['section_title'] = $live_rev['name'];
			$live['section_introduction'] = $live_rev['introduction'];
		}
		$live['live_count'] = $live_rev['live_count'];
		$live['start_time'] = substr($live['start_time'],0,-3);
		//查看拉流 及回顾地址
		$live_channel = ZLiveChannel::find()->select('pull_url,rever_url')->where(['section_id'=>$liveId])->asArray()->all();

		if($live_channel){
			$live['channel'] = $live_channel;
//			$live['pull_url'] = $live_channel['pull_url'];
//			$live['channel_rever_url'] = $live_channel['rever_url'];
		}

		// 添加分享标题字段
		if($live && $live['title']){
			if (!preg_match('/^\"\\\u76f4\\\u64ad\|/u', json_encode($live['title']))) {
				$live['name'] = '直播| ' .$live['title'];
			}
		}

		return $live;
	}

	/*
	 * 后台 查看直播详情
	 * */
	public static function getLiveInfo($sectionId,$admin_id=''){
		$sel_sql = "news.column_id,news.area_id,live_new.type,live_new.live_id,live_new.name,live_section.section_id,live_section.title,live_section.image_url,live_section.start_time,live_section.introduction,live_section.intro_title,live_section.watermark,live_section.screen,live_section.notice,live_section.creator_id,live_section.status,live_section.view_ranage,is_video,is_textvideo,live_section.good_id,live_section.show_type,live_section.price,live_section.password,live_section.phones,live_man_cate,live_man_alias,live_man_avatar_url,live_section.reviewed_status,amendments,live_section.rever_url";
		$where = '1';
		if($admin_id){
			$where .= " and live_section.creator_id = ".$admin_id;
		}
		$sectioninfo = LiveSection::find()
			->leftJoin('vrlive.live_new', 'live_new.live_id = live_section.live_id')
			->leftJoin('vrnews1.news', 'live_new.news_id = news.news_id')
			->where($where)
			->andWhere(['live_section.section_id'=>$sectionId])
			->select($sel_sql)
			->asArray()->one();

		if(!$sectioninfo){
			return false;
		}
		return $sectioninfo;
	}

	/*
	 * 采集端 获取新直播详情
	 * */
	public function getNewInfo($sectionId){
		$list = LiveSection::find()
			->with(['livenew'=>function($q){
				$q->select('live_new.live_id');
			},'livenew.newsnew'   => function ($q) {
				$q->select('news_id,column_id,area_id');
			}, 'livenew.newsnew.column' => function ($q) {
				$q->select('column_id,name');
			}, 'livenew.newsnew.area'   => function ($q) {
				$q->select('area_id,name');
			}])
			->select([
				"live_section.section_id",
				"live_section.live_id",
				"live_section.title",
				"live_section.image_url as cover_img",
				"live_section.start_time",
				"live_section.status",
				"live_section.screen",
				"live_section.live_man_avatar_url as admin_img",
				"live_section.live_man_cate as admin_cate",
				"live_section.live_man_alias as admin_alias",
				"reviewed_status",
				"amendments",
			])
			->where(['live_section.section_id' => $sectionId])
			->asArray()->one();

		$list['news_column'] = ['column_id' => '', 'column_type' => '', 'column_name' => ''];
		if ($list['livenew']['newsnew']['column_id']) {
			$list['news_column']['column_id'] = $list['livenew']['newsnew']['column_id'];
			$list['news_column']['column_type'] = '0';
			$list['news_column']['column_name'] = $list['livenew']['newsnew']['column']['name'];
		}
		if ($list['livenew']['newsnew']['area']) {
			$list['news_column']['column_id'] = $list['livenew']['newsnew']['area_id'];
			$list['news_column']['column_type'] = '1';
			$list['news_column']['column_name'] = $list['livenew']['newsnew']['area']['name'];

		}
		unset($list['livenew']);

		return $list;
	}

	/**
	 * 在直播详情内 显示的此系列内的直播列表 （全部状态）
	 */
	public static function SectionList($page, $size, $user_id,$section_id){
		$offset = ($page - 1) * $size;
		//查看 对应的直播ID
		$live_id = LiveSection::find()->where(['section_id'=>$section_id])->select("live_id")->column();
		if(!$live_id){
			return array();
		}
		$list = LiveSection::find()
			->leftJoin('vrlive.live_new', 'live_new.live_id = live_section.live_id')
//			->where(['>=', 'live_section.weight', 70])
			->andWhere(['live_section.live_id'=>$live_id[0]])
			->andWhere(['!=','live_section.section_id',$section_id])
			->andWhere(['!=','live_section.status',0])
			->andWhere(['=', 'live_section.reviewed_status', 0])
			->select([
//					"case `status` when 4 then 6 when 2 then 7 when 5 then 8 else `status` end as `status_a`",
					"live_new.type",
					"live_new.name",
					"live_new.is_fast",
					"live_new.live_count",
					"live_section.title",
					"live_section.section_id",
					"live_section.live_id",
					"live_section.start_time",
					"live_section.image_url",
					"live_section.status",
					"live_section.play_count",
					"live_section.screen",
					"live_section.show_type",
					"live_section.price",
					"live_section.password",
					"live_section.phones",
					"live_section.good_id"
				]
			)->orderBy(['live_section.refresh_time'=>SORT_DESC])
			->limit($size)->offset($offset)
			->asArray()->all();

		if(!empty($list)){
			foreach($list as $key=>$val){
				// 直播名正则
				if (!preg_match('/^\"\\\u76f4\\\u64ad\|/u', json_encode($val['title']))) {
					$list[$key]['name'] = '直播| ' . $val['title'];
				}
				$list[$key]['start_time'] = date('m/d H:i', strtotime($val['start_time']));
				$list[$key]['chatroom_id']  = 'room_'.$val['live_id'];

				//查看当前用户是否付费
				$user_pay = 0; //默认未付费
				if($user_id){
					$shop_order = ShopOrder::getGoodsPayStatusByUser($user_id,$val['good_id']);
					if($shop_order){
						$user_pay = 1;
					}
				}
				$list[$key]['user_pay'] = $user_pay;
				unset($list[$key]['status_a']);
			}
		}

		return $list;
	}

	//获取 录播列表
	public static function getRecordList($admin_id, $page, $count)
	{
		$offset = ($page - 1) * $count;
		$list = LiveSection::find()
			->with(['livenew'=>function($q){
				$q->select('live_new.live_id');
			},'livenew.newsnew'   => function ($q) {
				$q->select('news_id,live_id,column_id,area_id');
			}, 'livenew.newsnew.column' => function ($q) {
				$q->select('column_id,name');
			}, 'livenew.newsnew.area'   => function ($q) {
				$q->select('area_id,name');
			}])
			->leftJoin('live_tags_relation', 'live_tags_relation.live_id = live_section.section_id')
			->select([
				"live_section.live_id",
				"section_id",
				"title as name",
				"image_url",
				"rever_url",
				"rever_img_url",
				"start_time",
				"screen",
				"live_section.create_time",
				"year(live_section.create_time) as year1",
				"month(live_section.create_time) as month1",
				"day(live_section.create_time) as day1",
				"year(refresh_time) as year",
				"month(refresh_time) as month",
				"day(refresh_time) as day",
				"refresh_time",
				"reviewed_status",
				"amendments"])
			->where("creator_id = " . $admin_id . " and status = 5 and live_tags_relation.tag_id = 8")
			->orderBy(['case  when `year` is null then `year1` else `year` end'    => SORT_DESC,
				'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
				'case  when `day` is null then `day1` else `day` end'       => SORT_DESC,
				'refresh_time'                                              => SORT_DESC,
				'live_section.create_time'                                               => SORT_DESC])
			->offset($offset)
			->limit($count)
			->asArray()
			->all();

		foreach ($list as $key => $value) {
			$list[$key]['news_column'] = ['column_id'=>'','column_name'=>'','column_type'=>''];
			if ($value['livenew']['newsnew']['column_id']) {
				$list[$key]['news_column']['column_id'] = $value['livenew']['newsnew']['column_id'];
				$list[$key]['news_column']['column_name'] = $value['livenew']['newsnew']['column']['name'];
				$list[$key]['news_column']['column_type'] = '0';
			}
			if ($value['livenew']['newsnew']['area']) {
				$list[$key]['news_column']['column_id'] = $value['livenew']['newsnew']['area_id'];
				$list[$key]['news_column']['column_name'] = $value['livenew']['newsnew']['area']['name'];
				$list[$key]['news_column']['column_type'] = '1';
			}
			unset($list[$key]['livenew']);
			unset($list[$key]['live_id']);
			$list[$key]['live_id'] = $list[$key]['section_id'];
			unset($list[$key]['section_id']);

		}

		return $list;
	}
	
	//直播点击次数 加1
	public static function countAdd($liveId,$live_cou,$true_live_cou){
		$live_section = static::findOne($liveId);
		$view_ranage = $live_section->view_ranage;
		$browse_count = '1';
		if($view_ranage){
			$view_ranage_arr = explode('|', $view_ranage);
			$no_start_arr = array();
			$loading_arr = array();
			$end_arr = array();
			if(isset($view_ranage_arr[0])) $no_start_arr = explode('-', $view_ranage_arr[0]);
			if(isset($view_ranage_arr[1])) $loading_arr = explode('-', $view_ranage_arr[1]);
			if(isset($view_ranage_arr[2])) $end_arr = explode('-', $view_ranage_arr[2]);

			if(-1 != $live_section->status){
				if($live_section->status == '3' && ($live_section->start_time > date('Y-m-d H:i:s'))){
					if(!empty($no_start_arr)) $browse_count = mt_rand($no_start_arr[0],$no_start_arr[1]);
				}

				if(in_array($live_section->status, array(1,4)) && ($live_section->start_time <= date('Y-m-d H:i:s'))){
					if(!empty($loading_arr)) $browse_count = mt_rand($loading_arr[0],$loading_arr[1]);
				}

				if(in_array($live_section->status, array(2,5))){
					if(!empty($end_arr)) $browse_count = mt_rand($end_arr[0],$end_arr[1]);
				}

			}
		}
		$live_section->play_count = $live_cou + $browse_count;
		$live_section->true_play_count = $true_live_cou + 1;
		$live_section->save();
		return $live_section->play_count;
	}

	//获取直播员信息
	public static function getCompere($section_id)
	{
		$ret = array("creator_id" => 0, "live_man_cate" => "", "live_man_alias" => "", "live_man_avatar_url" => "");
		$list = static::find()->where(array('section_id' => $section_id))->asArray()->one();
		if ($list && !empty($list)) {
			$ret['creator_id'] = $list['creator_id'];
			$ret['live_man_cate'] = $list['live_man_cate'];
			$ret['live_man_alias'] = $list['live_man_alias'];
			$ret['live_man_avatar_url'] = $list['live_man_avatar_url'];

		}

		return $ret;
	}

	/*
	 * 客户端 直播列表
	 * */
	public static function appliveList($page, $size, $user_id,$category = NULL,$is_pc,$live_tag){
		$offset = ($page - 1) * $size;

		//供生产环境测试使用
		$test_where = " 1 ";
		$test_uid = array('201614754871496408', '20161479954020413', '201614829033721473', '201714930951045991', '201614751389465462', '201614802974723737', '201614762909851436', '201614764605844322', '201714999087559769', '201714842110831357', '201614764363288566', '201714990988402222');
		$test_adminid_str = "48,74,88,89,141,163,164,165,166,167,168,169,174,175,176";
		if (Yii::$app->params['environment'] == 'prod') {
			if (!$user_id || !in_array($user_id, $test_uid)) {
				$test_where = " live_section.creator_id not in(" . $test_adminid_str . ")";
			}
		}
		$where = array();
		if ($category == 2) {
			$where['live_tags_relation.tag_id'] = 6;
		}

		$trans_where = "  case when live_tags_relation.tag_id = 8  then rever_url<>'' else (rever_url is null or rever_url is not null) end";
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

		}else
		{
			//普通直播列表里包含高管创建的快直播
			if(empty($is_pc)) {
				$tag_where = 'and (is_fast = 0 or live_tags_relation.tag_id =2)'; //or live_tags_relation.tag_id =2';
			}

		}
		$list = LiveSection::find()
			->leftJoin('live_new', 'live_new.live_id = live_section.live_id')
			->leftJoin('live_tags_relation', 'live_tags_relation.live_id = live_section.section_id')
			->where($where)->andWhere($trans_where)
			->andWhere($test_where.$tag_where)->andWhere(['>=', 'weight', 70])
			->andWhere(['!=', 'live_section.status', 0])->andWhere(['=', 'reviewed_status', 0])
			->select([
					"live_section.section_id as live_id",
					"live_section.title as name",
					"start_time",
					"image_url",
					"live.type",
					"category",
					"live_section.status",
					"year(live.create_time) as year1",
					"month(live.create_time) as month1",
					"day(live.create_time) as day1",
					"year(refresh_time) as year",
					"month(refresh_time) as month",
					"day(refresh_time) as day",
					"refresh_time",
					"screen",
					"live_section.creator_id",
					"is_fast"]
			)->limit($size)->offset($offset)->orderBy([
				'CASE WHEN live_section.refresh_time <> 0 THEN DATE_FORMAT(live_section.refresh_time, \'%y%m%d\') ELSE DATE_FORMAT(live_section..create_time, \'%y%m%d\') END'=>SORT_DESC,
         			'case  when `year` is null then `year1` else `year` end' => SORT_DESC,
         			'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
         			'case  when `day` is null then `day1` else `day` end' => SORT_DESC,
				'live_section.refresh_time' => SORT_DESC,
				'live_section.create_time' => SORT_DESC
			])->asArray()->all();
		$count = LiveSection::find()
			->leftJoin('live_new', 'live_new.live_id = live_section.live_id')
			->leftJoin('live_tags_relation', 'live_tags_relation.live_id = live_section.section_id')
			->where($where)->andWhere($trans_where)
			->andWhere($test_where)->andWhere(['>=', 'weight', 70])
			->andWhere(['!=', 'live_section.status', 0])
			->andWhere(['reviewed_status'=>0])->count();

		if(!empty($list)){
			foreach ($list as $key => $val) {
				// 直播名正则
				if (!preg_match('/^\"\\\u76f4\\\u64ad\|/u', json_encode($val['name']))) {
					$list[$key]['name'] = '直播| ' . $val['name'];
				}
				$list[$key]['start_time'] = date('m/d H:i', strtotime($val['start_time']));
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

		return $info;
	}
	
	//定义section和plugin多对多orm表关系,以便查询每个section关联的plugin列表
	public  function getPlugin()
    {
        return $this->hasMany(Plugin::className(), ['id' => 'plugin_id'])
            ->viaTable('section_plugin', ['section_id' => 'section_id']);
    }

	public function getLivenew()
	{
		return $this->hasOne(LiveNew::className(), ['live_id' => 'live_id']);
	}
	public function getLivetags()
	{
		return $this->hasOne(LiveTagsRelation::className(), ['live_id' => 'section_id']);
	}
}
