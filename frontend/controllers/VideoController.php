<?php
namespace frontend\controllers;


use common\models\LiveProp;
use common\models\LiveCompetitor;
use common\models\LiveUserProp;
use common\models\NewsColumnType;
use common\models\NewsPraise;
use common\models\NewsUserCollect;
use common\models\User1;
use common\models\UserAmount;
use Yii;
use common\models\LiveVideo;
use common\models\News;
use common\models\NewsComment;
use common\models\User;
use common\models\Area;
use common\models\NewsColumn;
use common\models\NewsVideo;
use common\models\LiveCameraAngle;
use common\models\LiveChannel;
use common\models\LiveResourceVideo;
use common\models\Live;
use yii\db\Query;

class VideoController extends PublicBaseController{
	
	/**
	 * 普通视频
	 * @cong.zhao
	 */
	public function actionVideo()
	{
		$returnData = array();
		$news_id = isset($this->params['news_id'])?$this->params['news_id']:'';
		$type = isset($this->params['type'])?$this->params['type']:'';
		if(empty($news_id)){
			$this->_errorData("0001", "视频编号不能为空");
		}
	
		$data1=NewsVideo::find()->select(['news_video.*','news.*'])->innerJoin('news','news.news_id = news_video.news_id')->where(['news_video.news_id'=>$news_id,'news.status'=>0])->asArray()->one();
		$num=$data1['play_count'];

		/* 如果这是个引用的视频  点击次数为新的次数 */
		if($data1['reference_id']){
			$quote_info = NewsVideo::find()->where(['news_id'=>$data1['reference_id']])->asArray()->one();
		}else{
			$quote_info = '';
		}

		if($quote_info) $num = $quote_info['play_count'] ? $quote_info['play_count'] : $num;
		$v_url = '';
		if(isset($quote_info['video_url'])) $v_url = $quote_info['video_url'];
		
		$category = $data1['category'];
		$img_url  = $data1['thumbnail_url'];
		$video_url= $data1['video_url'] ? $data1['video_url'] : $v_url;

		if(!$video_url) $video_url = $data1['video_url1'];
		if(!$video_url) $video_url = $data1['video_url2'];
		$data2=News::find()->where(['news_id'=>$news_id,'type'=>'4','status'=>0])->asArray()->one();
		
		$data3=NewsComment::find()->where(['news_id'=>$news_id])->limit('5')->asArray()->all();
		
		foreach($data3 as $key=>$v){
			$user_message=User::find()->where(['user_id'=>$v['user_id']])->asArray()->one();
			$user_name=$user_message['username'];
			$avatar=$user_message['avatar'];
				
			$data4[$v['user_id']]=$user_name;
			$data5[$v['user_id']]=$avatar;
		}
		
	
		//推荐阅读数据
		$recommend_arr = array();
		if(News::getFirstNewsInfoByColumnName('要闻')){
			$recommend_arr[] = News::getFirstNewsInfoByColumnName('要闻');
		}
		if(News::getFirstNewsInfoByColumnName('本地')){
			$recommend_arr[] = News::getFirstNewsInfoByColumnName('本地');
		}
		if(News::getFirstNewsInfoByColumnName('跤坛')){
			$recommend_arr[] = News::getFirstNewsInfoByColumnName('跤坛');
		}
		if(News::getFirstNewsInfoByColumnName('说法')){
			$recommend_arr[] = News::getFirstNewsInfoByColumnName('说法');
		}
		//处理不同类型 新闻 图片大小
		foreach ($recommend_arr as $k=>$v){
			$recommend_arr[$k] = $this->getcheckinfo($v);
		}
	
		$returnData['recommend_data'] = isset($recommend_arr) ? $recommend_arr : '';
		$returnData['img_url'] = isset($img_url) ? $img_url : '';
		$returnData['category'] = isset($category) ? $category : '';
		$returnData['num'] = isset($num) ? $num : 0;
		$returnData['type'] = $type;
		$returnData['user_name'] = isset($data4) ? $data4 : '';
		$returnData['avatar'] = isset($data5) ? $data5 : '';
		$returnData['video_url'] = isset($video_url) ? $video_url : '';
		$returnData['video_more'] = isset($data2) ? $data2 : '';
		$returnData['video_comment'] = isset($data3) ? $data3 : '';
		$this->_successData($returnData, "查选成功");
	}
	
	
	/**
	 * 直播
	 * @cong.zhao
	 */
	function actionLivevideo()
	{
		$returnData = array();
		$live_id = isset($this->params['live_id'])?$this->params['live_id']:'';
		$type = isset($this->params['type'])?$this->params['type']:'';
		
		if(empty($live_id)){
			$this->_errorData("0001", "视频编号不能为空");
		}
	
		$data6=LiveCameraAngle::find()->where(['live_id'=>$live_id,'status'=>'1'])->orderBy('camera_id asc')->asArray()->one();  //直播机位
		$channel_id = $data6['source_id'];  //channel_id
		if($data6['signal_source'] == 1){
			$data7=LiveChannel::find()->where(['channel_id'=>$channel_id,'status'=>'1'])->asArray()->one(); //机位频道
			$txy_channel_id=$data7['txy_channel_id'];
			$category = $data7['device_type']=='1' ? '2' :'1';
		}else if($data6['signal_source'] == 2){
			$res_info = LiveResourceVideo::find()->where(['video_id'=>$channel_id,'status'=>2])->asArray()->one();
			$video_url = $res_info['video_url'];   //视频资源url
			$category = $res_info['category'];
		}

		$data=LiveVideo::find()->where(['live_id'=>$live_id])->asArray()->one(); //新闻视频
		$num=$data['play_count'];
	
		$data2=Live::find()->where(['live_id'=>$live_id])->asArray()->one(); //直播信息
		$model = Live::findOne($live_id);
		$model->play_count =$model->play_count+1;
		$model->save();
		$num=$model->play_count;
	
		$img_url = $data2['image_url'];
		$status = Live::getLiveStatus($data2['start_time'], $data2['status']);
		$data3=NewsComment::find()->where(['news_id'=>$live_id])->limit('5')->asArray()->all(); //评论
	
		foreach($data3 as $key=>$v){
			$user_message=User::find()->where(['user_id'=>$v['user_id']])->asArray()->one();
			$user_name=$user_message['username'];
			$avatar=$user_message['avatar'];
				
			$data4[$v['user_id']]=$user_name;
			$data5[$v['user_id']]=$avatar;
		}
		//回放url
		$rever_url = $data2['rever_url'] ? $data2['rever_url'] : '';
		//未开始url
		$before_start_url = $data2['befor_start_url'] ? $data2['befor_start_url'] : '';
		//直播拉流地址
		if(isset($data7)) $live_url = $data7['pull_url'] ? $data7['pull_url'] : '';
		$url = $rever_url;
		if(empty($rever_url)){
			$url = $before_start_url;
			if(empty($before_start_url)){
				$url = isset($video_url) ? $video_url : '';
			}
		}
	
		if(!isset($category)){
			$category = $data2['rever_video_category'];
			if(!$category) $category = $data2['befor_video_category'];
		}
		
		
		if($status != 4){  //不在直播中的状态
			$returnData['status'] = $status;
			$returnData['img_url'] = isset($img_url) ? $img_url : '';
			$returnData['video_more'] = isset($data2) ? $data2 : '';
			$returnData['url'] = isset($url) ? $url : '';
			$returnData['num'] = isset($num) ? $num : 0;
			$returnData['type'] = isset($type) ? $type : '';
			$returnData['category'] = isset($category) ? $category : '';
		}else{
			$returnData['status'] = isset($status) ? $status : '';
			$returnData['img_url'] = isset($img_url) ? $img_url : "";
			$returnData['type'] = isset($type) ? $type : '';
			$returnData['url'] = isset($live_url) ? $live_url : '';
			$returnData['txy_channel_id'] = isset($txy_channel_id) ? $txy_channel_id : '';
			$returnData['num'] = isset($num) ? $num : 0;
			$returnData['user_name'] = isset($data4) ? $data4 : '';
			$returnData['avatar'] = isset($data5) ? $data5 : '';
			$returnData['video_comment'] = isset($data3) ? $data3 : '';
			$returnData['video_more'] = isset($data2) ? $data2 : '';
			$returnData['data'] = isset($data) ? $data : '';
			$returnData['l_url'] = isset($url) ? $url : '';
			$returnData['video_url'] = isset($video_url) ? $video_url : '';
			$returnData['category'] = isset($category) ? $category : '';
		}
		$this->_successData($returnData, "查选成功");
	}
	
	
	/**
 * 获取视频栏目列表
 */
    public function actionVideoList(){
        $page      = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $count     = !empty($_REQUEST['count']) ? $_REQUEST['count'] : 20;

        $column_id = NewsColumn::getColumnId('视频');

        $videoList = News::GetListNew($column_id, 0, 0, '', $page, $count, '', '');

        foreach ($videoList as $key=>$val){
            $videoList[$key]['cover_image'] = substr($val['cover_image'], 0,strrpos($val['cover_image'],'/')).'/562.5x315!';
            $videoList[$key]['full_cover_image'] = $val['full_cover_image'] ? substr($val['full_cover_image'],0,strrpos($val['full_cover_image'],'/')).'/562.5x315!' : '';
            if($val['type'] != 5){
                $videoList[$key]['content'] = array();
            }
        }

        /* 微信小程序审核 */
        /*if (isset($_REQUEST['wechat'])) {
            $videoList['mini_app'] = 1;
        }*/

        $this->_successData($videoList);
    }

	/**
	 * 获取轮播图视频详情(pc)
	 */
	public function actionBannerInfo(){
		$news_id  = !empty($_REQUEST['news_id']) ? $_REQUEST['news_id'] : '';
		$size     = !empty($_REQUEST['size']) ? $_REQUEST['size'] : '';
		if(!$news_id || !is_numeric($news_id)){
			$this->_errorData('0003', '错误的新闻ID');
		}
		if($news_id){
			$news_info = News::find()->where(['news_id' => $news_id])->asArray()->one();
			if(!$news_info){
				$this->_errorData('0002', '新闻不存在');
			}
		}
		$info_list = NewsVideo::getBannerInfo($size, $news_id);
		$this->_successData($info_list);

	}
	

	/**
	 * 视频列表（pc）
	 * 
	 */
	public function actionVideoColumnList(){
		$size  = !empty($_REQUEST['size']) ? $_REQUEST['size'] : 5;
		$is_pc = !empty($_REQUEST['is_pc']) ? $_REQUEST['is_pc'] : 1;
		$num       = !empty($_REQUEST['num']) ? $_REQUEST['num'] : '0';
		$redis = Yii::$app->cache;
		$is_areas  = 0;
		$column_id = 9;
		$last_update_time = Yii::$app->params['environment']."_new_list_".$is_areas.'_'.$column_id.'_update';
		$news_video_column_update = $redis->get($last_update_time); //最新更新时间
		$name = Yii::$app->params['environment']."_video_list_".'_'.$column_id.'_pc_'.$news_video_column_update;
		$info_list = $redis->get($name);
		if($info_list && count($info_list)){
			$this->_successData($info_list);
		}else {
			$info_list = NewsColumnType::find()->select('type_id,name,weight')->where(['column_id' => $column_id, 'status'=> '1'])->andWhere(['>=', 'weight', '70'])
						->orderBy("weight desc")->asArray()->all();
			if(count($info_list) > 0){
				$result = array();
				foreach ($info_list as $key=>$value){
					if($key == 0){
						$news_list = NewsVideo::getVideoInfo(5, 1, $is_pc, $num, $value['type_id'], '');
					}else{
						$news_list = NewsVideo::getVideoInfo(6, 1, $is_pc, $num, $value['type_id'], '');
					}
					$info_list[$key]['news_list'] = $news_list;
					$result['column_'.$key] = $info_list[$key];
				}
				$this->_successData($result);
			}
		}
	}

	/**
	 * 视频列表换一换 不足补齐
	 *
	 */
	public function actionVideoChange(){
		$size     = !empty($_REQUEST['size']) ? $_REQUEST['size'] : 5;
		$page     = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
		$is_pc    = !empty($_REQUEST['is_pc']) ? $_REQUEST['is_pc'] : 1;
		$type_id  = !empty($_REQUEST['type_id']) ? $_REQUEST['type_id'] : '';
		if(!$type_id){
			$this->_errorData('0001','参数错误');
		}
		$info_list = NewsVideo::changeVideoInfo($size, $page, $is_pc, 0, $type_id);
		if(count($info_list) < 5){
			$count   = 5 - count($info_list);
			$replace = NewsVideo::changeVideoInfo($count, 1, $is_pc, 0, $type_id);
			if(count($replace) > $count){
				$replace = array_slice($replace,0, $count );
			}
			$info_list = array_merge_recursive($info_list, $replace);
		}
		$this->_successData($info_list);
	}

	private function getcheckinfo($value){

		$live_type = array(0=>'9',1=>'10',2=>'11',3=>'12',4=>'13',5=>'14');
		if($value && count($value) > 0){
			if ($value['type'] == '3') { //专题
				$value['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
			} else if ($value['type'] == '4') { //视频
				$value['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/206x142!' : '';
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
			} else if ($value['type'] == '5' && !empty($value['content'])) { //图集
//			$value['content'] = json_decode($value['content']);
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
					}else{
						$value['content'] = array();
					}
				}else {
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
					}else{
						$value['content'] = array();
					}
				}
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
			} else if ($value['type'] == '7') { //图文
				$value['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/206x142!' : '';
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
			} else if (in_array($value['type'], $live_type)) { //直播类型新闻
				$value['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/710x340!' : '';
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
			}
		}
		return $value;
	}



	/**
	 * 新版视频列表接口（视频栏目）
	 */
	public function actionGetVideoList(){
		$size  = !empty($_REQUEST['size']) ? $_REQUEST['size'] : 5;
		$page  = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
		$is_pc = !empty($_REQUEST['is_pc']) ? $_REQUEST['is_pc'] : 0;
		$list  = NewsVideo::getVideoList($size, $is_pc, $page);
		$this->_successData($list);
	}

	/**
	 * 视频栏目精彩推荐
	 */
	public function actionWonderfulVideo(){
		$size  = !empty($_REQUEST['size']) ? $_REQUEST['size'] : 5;
		$page  = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
		$is_pc = !empty($_REQUEST['is_pc']) ? $_REQUEST['is_pc'] : 0;
		$list  = NewsVideo::getWonderfulVideo($size, $is_pc, $page);
		$this->_successData($list);
	}

	/**
	 * 视频二级栏目列表
	 */
	public function actionColumnList(){
		$size  = !empty($_REQUEST['size']) ? $_REQUEST['size'] : 5;
		$page  = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
		$is_pc = !empty($_REQUEST['is_pc']) ? $_REQUEST['is_pc'] : 0;
		$type_id = !empty($_REQUEST['type_id']) ? $_REQUEST['type_id'] : 0;
		$list  = NewsVideo::getColumnList($is_pc, $type_id, $page, $size);
		$this->_successData($list);
	}


	/**
	 * 获取视频详情(H5)
     * 只有普通视频
	 */
	public function actionVideoInfo(){
		$count     = !empty($_REQUEST['size']) ? $_REQUEST['size'] : 5;
		$is_pc     = !empty($_REQUEST['is_pc']) ? $_REQUEST['is_pc'] : 0;
		$page      = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
		$num       = !empty($_REQUEST['num']) ? $_REQUEST['num'] : '0';
		$news_id   = !empty($_REQUEST['news_id']) ? $_REQUEST['news_id'] : '';
		$is_app    = !empty($_REQUEST['is_app']) ? $_REQUEST['is_app'] : '0';
		$user_id   = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
		$is_more   = !empty($_REQUEST['is_more']) ? $_REQUEST['is_more'] : '';

		if(!$news_id || !is_numeric($news_id)){
			$this->_errorData('0003', '错误的新闻ID');
		}
		$news_info = News::find()->where(['news_id' => $news_id])->asArray()->one();
		if(!$news_info){
			$this->_errorData('0002', '新闻不存在');
		}
		if($news_info['type'] == 3){
			$special_id = $news_id;
			$type_id = 0;
		}else if($news_info['special_news_id'] != 0){
			$special_id = $news_info['special_news_id'];
			$type_id = $news_info['type_id'];
		}else{
			$special_id = 0;
			$type_id = $news_info['type_id'];
		}

        $redis  = Yii::$app->cache;
        $is_areas  = 0;
        $column_id = 9;
        $last_update_time = Yii::$app->params['environment']."_new_list_".$is_areas.'_'.$column_id.'_update';
        $news_video_column_update = $redis->get($last_update_time); //最新更新时间
        $name = Yii::$app->params['environment']."_video_list_".$is_areas.'_'.$column_id.'_'.$type_id.'_'.$is_pc.'_'.$special_id. '_'.$news_video_column_update;
        $info_list = $redis->get($name);
        if($info_list){
//            return $info_list;
        }
        $trans_field = ' ,vrnews1.news_video.video_url as video_url,vrnews1.news_video.video_url1,vrnews1.news_video.video_url2,vrnews1.news_video.width,vrnews1.news_video.width1,vrnews1.news_video.width2,vrnews1.news_video.height,vrnews1.news_video.height1,vrnews1.news_video.height2,vrnews1.news_video.size,vrnews1.news_video.size1,vrnews1.news_video.size2 ,vrnews1.news_video.`file_id` as file_id,vrnews1.news.abstract,vrnews1.news.abstract as abstracts';
        if(!$info_list){
            if ($is_pc == 1) {
                $pub_where = " and web_pub = 1 ";
            } else {
                $pub_where = " and app_pub = 1 ";
            }
            $pub_where .= " and news.special_news_id = '".$special_id."' ";
            if($type_id) {
                $pub_where .= " and news.type_id = '" . $type_id . "'";
            }
            if($is_app == 1){
                $pub_where .= " and news.type != 3";
            }
            $where_area = " and  (news.area_id = 0 or news.area_id is null)";
            $trans_where = " and ( case when news.type=4  then ( (video_url<>'' or video_url1<>'' or video_url2<>'') and file_id<> 'null') or ( reference_type <> null and reference_id <> null)  or ( reference_type is not null and reference_id is not  null) else file_id is null end)";
            $query = new Query();
            $query->select(["vrnews1.news.news_id,news.type,title,subtitle,cover_image,DATE_FORMAT(vrnews1.news.create_time,'%Y/%m/%d %H:%i') as create_time,reference_type,reference_id,source_id,source_name,thumbnail_url,duration,play_count,category,outer_url_ishot,outer_url,external_link,year(vrnews1.news.create_time) as year1,month(vrnews1.news.create_time) as month1,day(vrnews1.news.create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time, vrnews1.news_video.status as video_status, vrnews1.news.live_id, vrnews1.news.vote_id" . $trans_field])->from('vrnews1.news');
            $query->leftJoin('vrnews1.news_video','vrnews1.news.news_id = vrnews1.news_video.news_id');

            $query->where("news.weight >= 70 and news.status=0 and column_id = $column_id and news_video.category = 1 and news.type != 2 and vote_id = 0".$where_area.$trans_where.$pub_where);

            $query->orderBy([
                'top_status' => SORT_DESC,
                'case  when `year` is null then `year1` else `year` end' => SORT_DESC,
                'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
                'case  when `day` is null then `day1` else `day` end' => SORT_DESC,
                'vrnews1.news.weight' => SORT_DESC,
                'refresh_time' => SORT_DESC,
                'create_time' => SORT_DESC]);
            $command = $query->createCommand();
            $info_list = $command->queryAll();
        }

        $start = 0;
        $new_list = array();
        if (count($info_list) > 0) {
            foreach ($info_list as $key => $value) {
                if ($value['news_id'] == $news_id) {
                    $start = $key;
                }
            }
            if($is_app){
                if($is_more && $start+1 >= count($info_list)){
                    $new_list = array();
                }elseif ($is_more && $start+1 < count($info_list)){
                    $new_list = array_slice($info_list, $start+1, $count);
                }else{
                    $new_list = array_slice($info_list, $start, $count);
                }
            }else{
                if(!$is_pc){
                    $new_list = array_slice($info_list, $start+1, $count);
                }else{
                    $new_list = array_slice($info_list, $start, $count);
                }
            }
            if(count($new_list)) {
                foreach ($new_list as $key => $value) {
                    $new_list[$key]['share_title'] = $value['title'] . ' |法制与新闻客户端';
                    if (!empty($user_id)) {
                        //当前用户是否可点赞
                        $praise_count = NewsPraise::find()->where(['news_id'=>$news_id,'status'=>'1', 'news_type'=>1])->count();
                        $new_list[$key]['praise_count'] =  $praise_count > 0 ? $praise_count : '0';
                        $user_praise_count = NewsPraise::find()->where(['news_id' => $value['news_id'], 'status' => '1', 'user_id' => $user_id])->count();
                        $new_list[$key]['user_is_praise'] = $user_praise_count > 0 ? '1' : '0';
                        $is_collect = NewsUserCollect::find()
                            ->where(["news_id" => $value['news_id'], "user_id" => $user_id, 'status' => 1])
                            ->asArray()->one();
                        if ($is_collect) {
                            $new_list[$key]['collect_id'] = $is_collect['collect_id'];
                        }else{
                            $new_list[$key]['collect_id'] = '';
                        }
                    } else {
                        $new_list[$key]['collect_id'] = '';
                        $new_list[$key]['user_is_praise'] = '0';
                        $new_list[$key]['praise_count'] = '0';
                    }
                    $new_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '/y' : '';
                    //列表 如果是视频并且引用了其他视频  取出对应的植
                    if ($value['type'] == 4 && $value['reference_id']) {
                        $video_info = News::_getNewsInfo($value['reference_id']);
                        $value['thumbnail_url'] = $video_info['thumbnail_url'];
                        $value['duration'] = $video_info['duration'];
                        $value['category'] = $video_info['category'];
                        $value['video_url'] = $video_info['video_url'];
                        $value['video_url1'] = $video_info['video_url1'];
                        $value['video_url2'] = $video_info['video_url2'];
                        $value['width'] = $video_info['width'];
                        $value['width1'] = $video_info['width1'];
                        $value['width2'] = $video_info['width2'];
                        $value['height'] = $video_info['height'];
                        $value['height1'] = $video_info['height1'];
                        $value['height2'] = $video_info['height2'];
                        $value['file_id'] = $video_info['file_id'];
                    }

                    //处理  返回值
                    if ($value['video_url']) {
                        unset($new_list[$key]['video_url1']);
                        unset($new_list[$key]['video_url2']);
                    } else if ($value['video_url1']) {
                        $new_list[$key]['video_url'] = $value['video_url1'];
                        unset($new_list[$key]['video_url1']);
                        unset($new_list[$key]['video_url2']);
                    } else if ($value['video_url2']) {
                        $new_list[$key]['video_url'] = $value['video_url2'];
                        unset($new_list[$key]['video_url1']);
                        unset($new_list[$key]['video_url2']);
                    } else {
                        unset($new_list[$key]['video_url1']);
                        unset($new_list[$key]['video_url2']);
                    }
                    if ($value['height']) {
                        unset($new_list[$key]['height1']);
                        unset($new_list[$key]['height2']);
                    } else if ($value['height1']) {
                        $new_list[$key]['height'] = $value['height1'];
                        unset($new_list[$key]['height1']);
                        unset($new_list[$key]['height2']);
                    } else if ($value['height2']) {
                        $new_list[$key]['height'] = $value['height2'];
                        unset($new_list[$key]['height1']);
                        unset($new_list[$key]['height2']);
                    } else {
                        unset($new_list[$key]['height1']);
                        unset($new_list[$key]['height2']);
                    }
                    if ($value['width']) {
                        unset($new_list[$key]['width1']);
                        unset($new_list[$key]['width2']);
                    } else if ($value['width1']) {
                        $new_list[$key]['width'] = $value['width1'];
                        unset($new_list[$key]['width1']);
                        unset($new_list[$key]['width2']);
                    } else if ($value['width2']) {
                        $new_list[$key]['width'] = $value['width2'];
                        unset($new_list[$key]['width1']);
                        unset($new_list[$key]['width2']);
                    } else {
                        unset($new_list[$key]['width1']);
                        unset($new_list[$key]['width2']);
                    }
                    if ($value['size']) {
                        unset($new_list[$key]['size1']);
                        unset($new_list[$key]['size2']);
                    } else if ($value['size1']) {
                        $new_list[$key]['size'] = $value['size1'];
                        unset($new_list[$key]['size1']);
                        unset($new_list[$key]['size2']);
                    } else if ($value['size2']) {
                        $new_list[$key]['size'] = $value['size2'];
                        unset($new_list[$key]['size1']);
                        unset($new_list[$key]['size2']);
                    } else {
                        unset($new_list[$key]['size1']);
                        unset($new_list[$key]['size2']);
                    }
                }
            }
            unset($value);
            if(!empty($news_id) && !$is_app){
                $news_info = News::find()->leftJoin('vrnews1.news_video', 'vrnews1.news_video.news_id = news.news_id')->select(["vrnews1.news.news_id,news.type,title,subtitle,cover_image,DATE_FORMAT(vrnews1.news.create_time,'%Y/%m/%d %H:%i') as create_time,reference_type,reference_id,source_id,source_name,thumbnail_url,duration,play_count,category,outer_url_ishot,outer_url,external_link,year(vrnews1.news.create_time) as year1,month(vrnews1.news.create_time) as month1,day(vrnews1.news.create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time, vrnews1.news_video.status as video_status, vrnews1.news.live_id, vrnews1.news.vote_id" . $trans_field])->where(['vrnews1.news.news_id' => $news_id])->asArray()->one();
                if (!empty($user_id)) {
                    //当前用户是否可点赞
                    $praise_count = NewsPraise::find()->where(['news_id'=>$news_id,'status'=>'1', 'news_type'=>1])->count();
                    $news_info['praise_count'] =  $praise_count > 0 ? $praise_count : '0';
                    $user_praise_count = NewsPraise::find()->where(['news_id' => $news_id, 'status' => '1', 'user_id' => $user_id])->count();
                    $news_info['user_is_praise'] = $user_praise_count > 0 ? '1' : '0';
                    $is_collect = NewsUserCollect::find()
                        ->where(["news_id" => $news_id, "user_id" => $user_id, 'status' => 1])
                        ->asArray()->one();
                    if ($is_collect) {
                        $news_info['collect_id'] = $is_collect['collect_id'];
                    }else{
                        $news_info['collect_id'] = '';
                    }
                } else {
                    $news_info['collect_id'] = '';
                    $news_info['user_is_praise'] = '0';
                    $news_info['praise_count']   = '0';
                }
                $info_lists['news_info'] = $news_info;
                $info_lists['list']      = $new_list;
                $redis->set($name, $info_list, 86400);
                return $info_lists;
            }
        }

		$this->_successData($new_list);

	}

	/**
	 * 获取视频栏目的二级栏目列表
	 */
	public function actionGetVideoColumn(){
		$column_id = 9;
		$info_list = NewsColumnType::find()->select('type_id,name,alias,weight')->where(['column_id' => $column_id, 'status'=> '1'])->andWhere(['>=', 'weight', '70'])
			->orderBy("weight desc")->asArray()->all();
		$this->_successData($info_list);
	}

	/**
	 * pc视频栏目新闻详情
	 */
	public function actionVideoDetail(){
		$size     = !empty($_REQUEST['size']) ? $_REQUEST['size'] : 4;
		$news_id  = !empty($_REQUEST['news_id']) ? $_REQUEST['news_id'] : '';
		$is_pc    = !empty($_REQUEST['is_pc']) ? $_REQUEST['is_pc'] : 1;
		$option   = !empty($_REQUEST['option']) ? $_REQUEST['option'] : '';
		$user_id  = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
		if(!$news_id || !is_numeric($news_id)){
			$this->_errorData('0003', '错误的新闻ID');
		}
		$news_info = News::find()->where(['news_id' => $news_id])->asArray()->one();
		if(!$news_info){
			$this->_errorData('0002', '新闻不存在');
		}
		$type_info = NewsColumnType::find()->where(['column_id' => 9, 'status' => 1])->select('type_id')->orderBy('weight desc')->asArray()->all();
		$type_list = array_column($type_info, 'type_id');
		$type_id = '';
		if($news_info['type'] == 3){
			$special_id = $news_id;
			//如果是专题就取专题列表中的第一条视频ID作为新闻的ID
			$trans_field = ' ,vrnews1.news_video.video_url as video_url,vrnews1.news_video.video_url1,vrnews1.news_video.video_url2,vrnews1.news_video.width,vrnews1.news_video.width1,vrnews1.news_video.width2,vrnews1.news_video.height,vrnews1.news_video.height1,vrnews1.news_video.height2,vrnews1.news_video.size,vrnews1.news_video.size1,vrnews1.news_video.size2 ,vrnews1.news_video.`file_id` as file_id';
			if ($is_pc == 1) {
				$pub_where = " and web_pub = 1 ";
			} else {
				$pub_where = " and app_pub = 1 ";
			}
			$pub_where .= " and news.special_news_id = '".$special_id."' ";
			$where_area = " and  (news.area_id = 0 or news.area_id is null)";
			$trans_where = " and ( case when news.type=4  then ( (video_url<>'' or video_url1<>'' or video_url2<>'') and file_id<> 'null') or ( reference_type <> null and reference_id <> null)  or ( reference_type is not null and reference_id is not  null) else file_id is null end)";
			$query = new Query();
			$query->select(["vrnews1.news.news_id,vrnews1.news.abstract,news.keywords,news.type,title,subtitle,cover_image,DATE_FORMAT(vrnews1.news.create_time,'%Y/%m/%d %H:%i') as create_time,reference_type,reference_id,source_id,source_name,thumbnail_url,duration,play_count,category,outer_url_ishot,outer_url,external_link,year(vrnews1.news.create_time) as year1,month(vrnews1.news.create_time) as month1,day(vrnews1.news.create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time, vrnews1.news_video.status as video_status, vrnews1.news.live_id, vrnews1.news.vote_id" . $trans_field])->from('vrnews1.news');
			$query->leftJoin('vrnews1.news_video','vrnews1.news.news_id = vrnews1.news_video.news_id');

			$query->where("news.weight >= 70 and news.status=0 and column_id = 9 and  news.type != 2 and vote_id = 0".$where_area.$trans_where.$pub_where);

			$query->orderBy([
				'top_status' => SORT_DESC,
				'case  when `year` is null then `year1` else `year` end' => SORT_DESC,
				'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
				'case  when `day` is null then `day1` else `day` end' => SORT_DESC,
				'vrnews1.news.weight' => SORT_DESC,
				'refresh_time' => SORT_DESC,
				'create_time' => SORT_DESC]);
			$query->offset(0);
			$query->limit(1);
			$command = $query->createCommand();
			$video = $command->queryOne();
			if($video){
				$news_id = $video['news_id'];
			}
		}else if($news_info['special_news_id'] != 0){
			$special_id = $news_info['special_news_id'];
		}else{
			$special_id = 0;
			if(!in_array($news_info['type_id'], $type_list)){
				$type_id = $type_info[0]['type_id'];
			}else{
				$type_id = $news_info['type_id'];
			}
		}
		$result = NewsVideo::getVideoDetail($news_id, $type_id, $option, $size, $is_pc, $user_id,$special_id);
		$this->_successData($result);
	}
}