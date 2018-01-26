<?php
namespace frontend\controllers;

use common\models\LiveNewsRelation;
use common\models\NewsPraise;
use common\models\NewsUserCollect;
use frontend\models\User;
use Yii;
use common\models\Live;
use common\models\LiveCameraAngle;
use common\models\LiveChannel;
use common\models\LiveCompetitor;
use common\models\LiveResourceVideo;
use common\models\LiveUserCollect;
use common\models\LiveUserSubscribe;
use common\models\LiveVideo;
use common\models\User1;
use common\models\VisitorToken;
use common\models\NewsQuiz;
use common\models\LivePk;
include_once Yii::$app->basePath."/../QcloudApi/QcloudApi.php";
class LiveController extends PublicBaseController{
    /**
     * 直播列表
     * @param $page 页码
     * @param $size 条数
     */
    public function actionLiveList(){
        $page    = isset($_REQUEST['page']) ? $_REQUEST['page'] : '1';
        $size    = isset($_REQUEST['size']) ? $_REQUEST['size'] : '20';
        $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
        $category = isset($_REQUEST['category']) ? $_REQUEST['category'] : '';
        $is_pc    = isset($_REQUEST['is_pc']) ? $_REQUEST['is_pc'] : '';
        $is_cloud    = isset($_REQUEST['live_tag']) ? $_REQUEST['live_tag'] : 0;
        $result = Live::liveList($page, $size, $user_id,$category,$is_pc,$is_cloud);
        if($is_pc == 1){
            $list = $result;
        }else{
            $list = $result['list'];
        }
        $this->_successData($list);
    }
    
    /**
     *  快直播列表
     *
     * @param $page 页码
     * @param $size 条数
     */
    public function actionFastLiveList()
    {
        $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '1';
        $size = isset($_REQUEST['size']) ? $_REQUEST['size'] : '20';
        $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
        $is_pc = isset($_REQUEST['is_pc']) ? $_REQUEST['is_pc'] : '0';
        $column_type= isset($_REQUEST['column_type']) ? $_REQUEST['column_type'] : '0';
        $column_id = isset($_REQUEST['column_id']) ? $_REQUEST['column_id'] : '';
        $cover_id = isset($_REQUEST['cover_news_id']) ? $_REQUEST['cover_news_id'] : '';
        if (!$column_id) {
            $this->_errorData('8000', '请输入正确的栏目类型和栏目ID');
        }
        $result = Live::fastliveList($page, $size, $user_id, $is_pc, $column_id, $column_type,$cover_id);
        
        if ($is_pc == 1) {
            $list = $result;
        } else {
            $list = $result['list'];
        }
        $this->_successData($list);
    }
    
    
    /**
     * 预约直播
     * @param $live_id 直播id
     * @param $user_id 用户id
     */
    public function actionSubscribeLive(){
        $live_id = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '';
        $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
        $status  = isset($_REQUEST['status']) ? $_REQUEST['status'] : '0';
        if(!$live_id || !$user_id){
            $this->_errorData("0001", '参数错误');
        }
        $result = LiveUserSubscribe::addSubscribe($live_id, $user_id, $status);
        if($result){
            $this->_successData("0000", '预约成功！');
        }else{
            $this->_errorData("0001", '直播或用户不存在！');
        }
    }

    /**
     * 相关新闻
     * @param $live_id 直播id
     * @param $is_live 1直播 2新闻
     */
    public function actionNewsRecommend(){
        $live_id = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '';
        $is_live = isset($_REQUEST['is_live']) ? $_REQUEST['is_live'] : '1';
        if(!$live_id){
            $this->_errorData(0001, '参数错误');
        }
        $list = array();
        if($is_live == 1){
            $list = LiveNewsRelation::newsRelation($live_id);
        }else if($is_live == 2){
            $list = $this->news_recommend->newsRelation($live_id);
        }
        $this->_successData($list);
    }

    /**
     * 直播详情
     * @param $live_id 直播id
     * @param $token 用户登录token 可不传
     * @param $user_id 用户id 可不传
     */
    public function actionGetLiveById(){
        $live_id = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '';
        $token   = isset($_REQUEST['token']) ? $_REQUEST['token'] : '';
        $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';

        //查询直播主信息
        $live = Live::getLiveById($live_id);
        if($live){
            //点击次数 加1
            $live_cou = $live['play_count'];
            $true_live_cou = $live['true_play_count'];
            $live['play_count'] = Live::countAdd($live_id, $live_cou, $true_live_cou);

            if($live['red_competitor_id']){
                $red_info = LiveCompetitor::getCompetitorInfo($live['red_competitor_id']);
                $live['red_name'] = $red_info->real_name;
                $live['red_photo'] = $red_info->avatar;
            }
            if($live['blue_competitor_id']){
                $blue_info = LiveCompetitor::getCompetitorInfo($live['blue_competitor_id']);
                $live['blue_name'] = $blue_info->real_name;
                $live['blue_photo'] = $blue_info->avatar;
            }
            $start_time = strtotime($live['start_time']);
            $status = $live['status'];
            //状态 1 正常 0 删除 2 直播结束 3 未开始 4 正在直播 5 直播回顾
            if($start_time > time()){  //开始时间大于当前时间：未开始
                $live['status'] = 3;
            }else if(2 == $status || 5 == $status){ //已结束

            }else{ //直播中
                $live['status'] = 4;
            }

            //查询机位
            $live['cameraAngleList'] = array();
            $list = LiveCameraAngle::getCameraAngleList($live_id);
            if($live['is_fast'] == 1 && $live['status'] == 4 && in_array($live['category'], array(1,4,6))){
                $live['cameraAngleList'] = $list;
                $live_channel_info = LiveChannel::find()->where(['channel_id'=>$list[0]['source_id']])->asArray()->one();
                $live['cameraAngleList'][0]['url'] = '';
                $live['cameraAngleList'][0]['device_type'] = '';
                if($live_channel_info){
                    $live['cameraAngleList'][0]['url'] = $live_channel_info['pull_url'];
                    $live['cameraAngleList'][0]['device_type'] = $live_channel_info['device_type'];
                }
            }else if($live['is_fast'] != 1) {
                if(!$live['rever_url']) {
                    $channels = $this->_get_channels();
                    $videos  = $this->_get_resource_videos($live_id);
                    //过滤有效机位
                    $cameraAngleList = array();
                    if ($list && !empty($list)) {
                        foreach ($list as $key => $val) {
                            if (1 == $val['signal_source']) {
                                if ($channels['u_' . $val['source_id']]) {
                                    $val['url'] = $channels['u_' . $val['source_id']];
                                    $val['device_type'] = $channels['t_' . $val['source_id']];
                                    $val['txy_channel_id'] = $channels['x_' . $val['source_id']];
                                    $cameraAngleList[] = $val;
                                }
                            } else if (2 == $val['signal_source']) {
                                if ($videos['u_' . $val['source_id']]) {
                                    $val['url'] = $videos['u_' . $val['source_id']];
                                    $val['file_id'] = $videos['f_' . $val['source_id']];
                                    // 加3 之后 变成 4 普通视频 或者5 vr视频
                                    $val['device_type'] = $videos['t_' . $val['source_id']] + 3;
                                    $cameraAngleList[] = $val;
                                }
                            }
                        }
                    }
                    $live['cameraAngleList'] = $cameraAngleList;
                }
            }

            //查询是否收藏
            if($user_id){
                $collect_model = NewsUserCollect::find()->where(['news_id'=>$live_id, 'user_id'=>$user_id, 'type'=>4, 'status'=>1])
                                ->asArray()->one();
                if($collect_model){
                    $live['subscribe_status'] = 1;
                    $live['subscribe']        = $collect_model['collect_id'];
                }else{
                    $live['subscribe_status'] = 0;
                    $live['subscribe']        = '';
                }
            }else {
                $live['subscribe_status'] = 0;
                $live['subscribe']        = '';
            }

            $live['is_subscribe'] = 0;
            if(!empty($user_id)){
                $is_subscribe = LiveUserSubscribe::find()->where(['user_id'=>$user_id, 'live_id'=>$live_id, 'status'=>1])->count();
                $live['is_subscribe'] = $is_subscribe;
            }

            $live['chatroom_id'] = 'room_'.$live_id;
            
            //判断竞猜状态
            if($live['quiz_status'] != '0'){
            	$quiz_count = NewsQuiz::find()->where(['news_id'=>$live_id])->count();
            	if($quiz_count == '0'){
            		$live['quiz_status'] = '0';
            	}
            }
            //判断相关新闻状态
            if($live['news_status'] != '0'){
            	$about_news_count = LiveNewsRelation::find()->innerJoin('vrnews1.news','vrnews1.news.news_id = vrlive.live_news_relation.news_id')->where(['vrlive.live_news_relation.live_id'=>$live_id])->count();
            	if($about_news_count == '0'){
            		$live['news_status'] = '0';
            	}
            }
            //判断打赏状态
            if($live['is_props'] != '0'){
            	$props_count = LivePk::find()->where(['live_id'=>$live_id])->count();
            	if($props_count == '0'){
            		$live['is_props'] = '0';
            	}
            }

            //点赞数量
            $praise_count = NewsPraise::find()->where(['news_id'=>$live_id, 'news_type'=>0, 'status'=>1])->count();
            $live['praise_count'] = $praise_count;

            //判断是否点赞
            if($user_id){
                $is_praise = NewsPraise::find()->where(['news_id'=>$live_id, 'user_id'=>$user_id, 'news_type'=>0, 'status'=>1])->count();
                $live['is_praise'] = 0;
                if($is_praise){
                    $live['is_praise'] = 1;
                }
            }
            
            
            $this->_successData($live);
        }else{
            $this->_errorData('1001', '直播或用户不存在！');
        }

    }

    /*
     * 直播（视频、录播 快直播类型） 点赞
     *
     * */
    public function actionLivePraise(){
        $live_id   = isset($this->params['live_id']) ? $this->params['live_id'] :  0; //直播ID
        $user_id   = isset($this->params['user_id']) ? $this->params['user_id'] :  0; //用户ID
        $praises   = isset($this->params['praises']) ? $this->params['praises'] :  0; //点赞次数
        if(!$live_id || !$user_id || !$praises){
            $this->_errorData('0133', "参数错误");
        }
        //查看直播是否存在
        $live_info = Live::find()->where('live_id = '.$live_id)->asArray()->one();
        if(!$live_info){
            $this->_errorData('0134', "直播不存在");
        }
        $user_info = User::find()->where('user_id='.$user_id.' and status = 1')->one();
        if(!$user_info){
            $this->_errorData('0011',"用户不存在");
        }
        if($live_info['is_fast'] != 1 || !in_array($live_info['category'], array(1,6))){
            $this->_errorData('0137', "直播类型有误");
        }

        $praise_info = NewsPraise::find()->where(array('news_id'=>$live_id, 'user_id'=>$user_id, 'news_type'=>0))->one();
        if($praise_info){
            $praise_info->live_count = $praise_info->live_count + $praises;
            $ret = $praise_info->update();
            if($ret <= 0 || $ret === false) $this->_errorData(1007, '点赞失败');

            $this->_successData('点赞成功');
        }else{
            $praise_model = new NewsPraise();
            $praise_model->news_id   = $live_id;
            $praise_model->user_id   = $user_id;
            $praise_model->news_type = 0;
            $praise_model->status    = 1;
            $praise_model->create_time = time();
            $praise_model->live_count  = $praises;
            $ret = $praise_model->save();
            if(!$ret) $this->_errorData(1003, '点赞失败');

            $this->_successData('点赞成功');
        }

    }



    private function _get_channels(){
        //查询频道
        $channelList = LiveChannel::getLiveChannelList();
        $channels = array();
        foreach($channelList as $key=>$value){
            $channels['t_'.$value['channel_id']] = $value['device_type'];
            $channels['u_'.$value['channel_id']] = $value['pull_url'];
            $channels['x_'.$value['channel_id']] = $value['txy_channel_id'];
        }
        return $channels;
    }

    private function _get_resource_videos($live_id){
        //查询视频资源
        $videoList = LiveResourceVideo::get_list($live_id);
        $videos = array();
        foreach($videoList as $key=>$value){
            $videos['t_'.$value['video_id']] = $value['category'];
            $videos['u_'.$value['video_id']] = $value['video_url'];
            $videos['f_'.$value['video_id']] = $value['file_id'];
        }
        return $videos;
    }

    /*
     * 直播机位 详情
     * @param $camera_id  机位的ID号
     * */
    public function actionDescribelvbChannel(){
        $camera_id = isset($_REQUEST['camera_id']) ? $_REQUEST['camera_id'] : '';
        if(!$camera_id){
            $this->_errorData(0001,'参数错误' );
        }
        $res = array();
        //查看机位 来源id
        $camera_source = LiveCameraAngle::getSourceId($camera_id);

        //获取 直播 视频地址、回放地址
        $infos = LiveVideo::find()->where(['live_id'=>$camera_source['live_id']])->select("video_url,type")->asArray()->all();

        if($infos){
            foreach($infos as $key=>$val){
                if($val['type'] == 1){
                    $res['befor_url'] = $val['video_url'];
                }
                if($val['type'] == 2){
                    $res['rever_url'] = $val['video_url'];
                }
            }
        }else{
            $res['befor_url'] = '';
            $res['rever_url'] = '';
        }


        //查看 频道的腾讯云ID
        $txy_id = LiveChannel::getTxyId($camera_source['source_id']);
        if(!$txy_id){
            $this->_errorData(0001, '腾讯云ID不存在');
        }

        $config = array(
            'SecretId'       => Yii::$app->params['API_SecretId'],
            'SecretKey'      => Yii::$app->params['API_SecretKey'],
            'RequestMethod'  => 'GET',
            'DefaultRegion'  => Yii::$app->params['API_DefaultRegion']);

//        vendor("QcloudApi.QcloudApi");
        $service = \QcloudApi::load(\QcloudApi::MODULE_LIVE, $config);
        $package = array('channelId' => $txy_id);
        $get_txy = $service->DescribeLVBChannel($package);

        if($get_txy['codeDesc'] == "Success" && $get_txy['channelInfo']){
            foreach($get_txy['channelInfo'] as $key=>$val) {
                $res['channel_id']  = $val['channel_id'];
                $res['status']      = $val['channel_status'];
                $res['hls_address'] = $val['hls_downstream_address'];

                $this->_successData($res);
            }
        }
        $a = $service->generateUrl('DescribeLVBChannel', $package);
        $this->_errorData('0001', $a);
    }

    /*
     * 直播推流状态
     * */
    public function actionChannelStatus(){
        $live_id = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '';
        if(!$live_id){
            $this->_errorData(0001,'参数错误' );
        }
        //查看直播状态
        $live_info = Live::find()->where(['live_id'=>$live_id])->asArray()->one();
        $res_status['live_status'] = Live::getLiveStatus($live_info['start_time'], $live_info['status']);

        $res_status['channel_status'] = 1;
        if($live_info['is_fast'] == 1) {
            //查看直播码
            $channel = LiveChannel::find()->where(['channel_name' => '直播码-' . $live_id])->orderBy('create_time desc')->asArray()->one();
            if (!$channel) {
                $this->_errorData(0301, '无直播码信息');
            }
            //直播码
            $channel_code = $channel['txy_channel_id'];

            $appid = Yii::$app->params['appid'];//'1253999690';
            $zhiboma_key = Yii::$app->params['txy_zbm_key'];//'3ccce1558b5d4430135582a5f11582e9';
            $t = time() + 60;
            $sign = md5($zhiboma_key . $t);
            //调用腾讯云视频详情接口
            $interface = "Live_Channel_GetStatus";
            $config = array(
                'SecretId' => Yii::$app->params['API_SecretId'],
                'SecretKey' => Yii::$app->params['API_SecretKey'],
                'RequestMethod' => 'GET',
                'DefaultRegion' => Yii::$app->params['API_DefaultRegion']);

            $service = \QcloudApi::load(\QcloudApi::MODULE_FCGI, $config);
            $package = array(
                'appid' => $appid,
                'interface' => $interface,
                'Param.s.channel_id' => $channel_code,
                't' => $t,
                'sign' => $sign,
            );

            $res = $service->Send($package);
            $res_json = json_decode($res, true);

            //流状态 0异常，1正常
            $res_status['channel_status'] = 0;
            //从未 推过流
            if ($res_json['ret'] == 20601) {
                $res_status['channel_status'] = 0;
            }

            if (!empty($res_json['output'][0]['status'])) {
                //断流  更新断流时间 push_time
                if ($res_json['output'][0]['status'] == 0) {
                    $res_status['channel_status'] = 0;
                }
                //正常推流 跳过
                if ($res_json['output'][0]['status'] == 1) {
                    $res_status['channel_status'] = 1;
                }

                //流关闭状态 关闭直播
                if ($res_json['output'][0]['status'] == 3) {
                    $res_status['channel_status'] = 0;
                }
            }
        }
        $this->_successData($res_status);
    }
    
    /**
     * 热门直播列表
     * @param $page 页码
     * @param $size 条数
     */
    public function actionHotLiveList(){
    	$user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
    	$list = Live::HotLiveList($user_id);
    	$this->_successData($list);
    }
    
    /**
     * BiaoZhunLiveList
     * @param $page 页码
     * @param $size 条数
     */
    public function actionBiaoZhunLiveList(){
//     	$user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
    	$list = Live::BiaoZhunLiveList();
    	$this->_successData($list);
    }
    
    /**
     * baoding
     */
    public function actionBaoDingLive(){
    	$user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
    	$list = Live::BaoDindLive($user_id, 3, 0);
    	$this->_successData($list);
    }
    
    public function actionJiaoTanLive(){
    	$user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
    	$list = Live::BaoDindLive($user_id, 0, 3);
    	$this->_successData($list);
    }
    
    /**
     * 根据直播名称搜索对应直播的信息
     * @cong.zhao
     */
    Public function actionGetLiveInfoByTitle(){
    	$keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : '';
    	$list = Live::GetLiveInfoByTitle($keyword);
    	$this->_successData($list);
    }
    
    
    /**
     * 采集端主持人登录
     * @cong.zhao
     */
    Public function actionLiveLogin(){
    	$username = isset($_REQUEST['username']) ? $_REQUEST['username'] : '';
    	$password = isset($_REQUEST['password']) ? $_REQUEST['password'] : '';
    	$phone_id = isset($_REQUEST['phone_id']) ? $_REQUEST['phone_id'] : '';
    	
    	if(!$username || !$password){
    		$this->_errorData(0001, '参数错误');
    	}
    	$list = Live::LiveLogin($username, $password);
    	if($list){
    		$rcloud_token = $this->get_rcloud_token($phone_id);			//根据设备id获取token
    		$visitor = new VisitorToken();
    		$visitor->phone_id     = $phone_id;
    		$visitor->rcloud_token = $rcloud_token;
    		if($visitor->save()){
    			$this->_successData(array('rcloud_token'=>$rcloud_token));
    		}
    	}else{
    		$this->_errorData('0002', '用户名或密码错误!!');
    	}
    }
    
    private function get_rcloud_token($user_id)
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
    
    
    /**
     * 根据直播id获取直播频道下的所有腾讯云回放视频
     * @cong.zhao
     * @by 2017-06-30
     * @return array
     */
    public function actionGetVideoListByLiveId(){
//     	$live_id = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '';
//     	$live_id ='201614775734421385';
//     	//根据直播id获取直播下的频道列表
//     	$channel_list = Live::GetChannelListByLiveId($live_id);

//     	$config = array(
//     			'SecretId'       => Yii::$app->params['API_SecretId'],
//     			'SecretKey'      => Yii::$app->params['API_SecretKey'],
//     			'RequestMethod'  => 'GET',
//     			'DefaultRegion'  => Yii::$app->params['API_DefaultRegion']);
//     	$service = \QcloudApi::load(\QcloudApi::MODULE_LIVE, $config);

//     	if($channel_list){
//     		foreach($channel_list as $key=>$value){
//     			$package = array(
//     					'channelId' => $value['txy_channel_id'],//9896587163770758746
//     					'startTime'=>urlencode($value['start_time']),
//     					'pageNum'=>'1',
//     					'pageSize'=>'20'
//     			);
//     			$channel_video_info_list = $service->GetVodRecordFiles($package);
//     			if($channel_video_info_list) $channel_list[$key]['channel_video_info_list'] = $channel_video_info_list;

//     		}
//     	}

//     	$this->_successData($channel_list);
    	
    	
    	$config = array(
    			'SecretId'       => Yii::$app->params['API_SecretId'],
    			'SecretKey'      => Yii::$app->params['API_SecretKey'],
    			'RequestMethod'  => 'GET',
    			'DefaultRegion'  => Yii::$app->params['API_DefaultRegion']);
    	$service = \QcloudApi::load(\QcloudApi::MODULE_LIVE, $config);

    	$package = array(
    			'channelId' => '9896587163629444374',
    			'startTime'=>'2017-06-06 15:59:20',//'2016-01-01%2000:00:00',
    			'pageNum'=>'1',
    			'pageSize'=>'20'
    	);
    	$channel_video_info_list = $service->GetVodRecordFiles($package);
    	print_r($channel_video_info_list);exit;
    }
    
}