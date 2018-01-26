<?php

namespace frontend\controllers;

use Yii;
use common\models\UploadFile;
use yii\web\UploadedFile;
use common\models\ResourceLibrary;
use common\models\Tencentyun\ImageV2;
use common\models\ImageProcess;
include_once Yii::$app->basePath."/../QcloudApi/QcloudApi.php";

class ResourceController extends PublicBaseController
{   
    
    /**
     * app端调用创建资料接口
     * @cong.zhao
     * @param $file_name 视频文件名
     * @param $category  视频文件类型
     * @param $operation_id  后台操作用户id
     * @param $reponse_data  app端口sdk上传返回数据
     */
    public function actionCreateResourceVideo(){
    	$file_name=isset($_REQUEST['file_name'])?$_REQUEST['file_name']:'';
    	$category=isset($_REQUEST['category'])?$_REQUEST['category']:'1';
    	$operation_id=isset($_REQUEST['operation_id'])?$_REQUEST['operation_id']:'0';
    	$reponse_data = isset($_REQUEST['reponse_data'])?$_REQUEST['reponse_data']:'';

    	if(!$file_name  || !$operation_id || !$reponse_data){
    		$this->_errorData('337','参数错误');
    	}
    	
    	$resource_id = ResourceLibrary::AddResource($reponse_data, $file_name, $category, $operation_id);
    	if(!$resource_id){
    		$this->_errorData('349','保存失败');
    	}
    	
    	$this->_successData(array('resource_id'=>$resource_id));
    }
    
    /**
     * app端调用删除资料接口
     * @cong.zhao
     * @param $resource_id 资源id
     */
    public function actionDeleteResource(){
    	$resource_id=isset($_REQUEST['resource_id'])?$_REQUEST['resource_id']:'';
    	$resource_id = '1494487312724193865';
    	if(!$resource_id){
    		$this->_errorData('337','参数错误');
    	}
    
    	$return_id = ResourceLibrary::DeteleResource($resource_id);
    	if(!$return_id){
    		$this->_errorData('349','保存失败');
    	}
    
    	$this->_successData(1,'删除成功');
    }
    
    
    /**
     * app端调用资源库视频列表接口
     * @cong.zhao
     * @param $admin_id 管理员id
     * @return array
     */
    public function actionResourceVideoList(){
    	$admin_id=isset($_REQUEST['admin_id'])?$_REQUEST['admin_id']:'';
    	$page = isset($_REQUEST['page'])?$_REQUEST['page']:'';
    	$page = (!empty($page) && $page > 0) ? $page : 1;
    	$pageSize = isset($_REQUEST['pageSize'])?$_REQUEST['pageSize']:'';
    	$pageSize = (!empty($pageSize) && $pageSize > 0) ? $pageSize : 10;
    	$pageStart = ($page - 1) * $pageSize;
    	$pageEnd = $page * $pageSize;

    	if(!$admin_id){
    		$this->_errorData('337','参数错误');
    	}
    	
    	$resource_video_list = ResourceLibrary::GetResourceVideoList($admin_id, $pageStart, $pageEnd);
    	$this->_successData($resource_video_list,'查询成功');
    }
    
    public function actionOpen(){
    	$zhiboma_app_id = '1253999690';
    	$zhiboma_key = '3ccce1558b5d4430135582a5f11582e9';
    	$t = time()+60;
    	//调用腾讯云视频详情接口
    	$config = array(
    			'SecretId'       => Yii::$app->params['API_SecretId'],
    			'SecretKey'      => Yii::$app->params['API_SecretKey'],
    			'RequestMethod'  => 'GET',
    			'DefaultRegion'  => Yii::$app->params['API_DefaultRegion']);
    	$service = \QcloudApi::load(\QcloudApi::MODULE_FCGI, $config);
    	$package = array(
    			'appid'=>$zhiboma_app_id,
    			'interface'=>'Live_Tape_Start',
    			't'=>$t,
    			'Param.s.channel_id'=>'10248_sdjkfjsdjfd',
    			'Param.s.start_time'=>'2017-07-18 15:00:00',
    			'Param.s.end_time'=>'2017-07-19 16:00:00',
    			'sign'=>md5($zhiboma_key.$t),
//     			'Param.n.task_sub_type'=>'1',
//     			'Param.s.file_format'=>'mp4',
//     			'Param.s.record_type'=>'video'
    			
    	);
    	$video_info = $service->Send($package);
    	print_r($video_info);exit;
    	echo '123';exit;
    }
    //http://fcgi.video.qcloud.com/common_access? appid=1253999690&interface=Live_Tape_Stop&Param.s.channel_id=10248_sdjkfjsdjfd&Param.n.task_id=18000634&t=1471850187&sign=b17971b51ba0fe5916ddcd96692e9fb3
    //1500276440
//d2ebd0bd056582f84f62a2898ead39b8
    public function actionColse(){
    	$zhiboma_app_id = '1253999690';
    	$zhiboma_key = '3ccce1558b5d4430135582a5f11582e9';
    	$t = time()+60;

    	//调用腾讯云视频详情接口
    	$config = array(
    			'SecretId'       => Yii::$app->params['API_SecretId'],
    			'SecretKey'      => Yii::$app->params['API_SecretKey'],
    			'RequestMethod'  => 'GET',
    			'DefaultRegion'  => Yii::$app->params['API_DefaultRegion']);
    	$service = \QcloudApi::load(\QcloudApi::MODULE_FCGI, $config);
    	$package = array(
    			'appid'=>$zhiboma_app_id,
    			'interface'=>'Live_Tape_Stop',
    			't'=>$t,
    			'sign'=>md5($zhiboma_key.$t),
    			'Param.s.channel_id'=>'10248_sdjkfjsdjfd',
    			'Param.s.task_id'=>'18002914',
    			'Param.n.task_sub_type'=>'1'
    			//'Param.n.task_sub_type'=>'1'
    			 
    	);
    	$video_info = $service->Send($package);
    	print_r($video_info);exit;
    	echo '123';exit;
    }
    
	//rtmp://10248.livepush.myqcloud.com/live/10248_564474ac58?bizid=10248&txSecret=6dc19831b968ce27f7de0b263b155827&txTime=5968EA7F
    //rtmp://10248.livepush.myqcloud.com/live/10248_564474ac58?bizid=10248&txSecret=e20666d1cb79c09512fff7d84a18b203&txTime=59693EDF
    /**
     * 获取推流地址
     * 如果不传key和过期时间，将返回不含防盗链的url
     * @param bizId 您在腾讯云分配到的bizid
     *        streamId 您用来区别不通推流地址的唯一id
     *        key 安全密钥
     *        time 过期时间 sample 2016-11-12 12:00:00
     * @return String url */
    function actionGetPushUrl(){
    	date_default_timezone_set('PRC');
    	$bizId = '10248';
    	$streamId = '5c1fa91cfb';
    	$key = 'cdb9407b9c67b82c43a53b3a1d5da997';
    	$time = '2017-07-19 16:00:00';
    	
    	if($key && $time){
    		$txTime = strtoupper(base_convert(strtotime($time),10,16));
    		//txSecret = MD5( KEY + livecode + txTime )
    		//livecode = bizid+"_"+stream_id  如 8888_test123456
    		$livecode = $bizId."_".$streamId; //直播码
    		$txSecret = md5($key.$livecode.$txTime);
    		$ext_str = "?".http_build_query(array(
    				"bizid"=> $bizId,
    				"txSecret"=> $txSecret,
    				"txTime"=> $txTime
    		));
    	}
    	echo "rtmp://".$bizId.".livepush.myqcloud.com/live/".$livecode.(isset($ext_str) ? $ext_str : "");exit;
    	return "rtmp://".$bizId.".livepush.myqcloud.com/live/".$livecode.(isset($ext_str) ? $ext_str : "");
    }
    	
    /**
     * 获取播放地址
     * @param bizId 您在腾讯云分配到的bizid
     *        streamId 您用来区别不通推流地址的唯一id
     * @return String url */
    function actionGetPlayUrl(){
    	$bizId = '10248';
    	$streamId = 'test_cong_07_14';
    	$livecode = $bizId."_".$streamId; //直播码
    	echo "rtmp://".$bizId.".liveplay.myqcloud.com/live/".$livecode;exit;
    	return array(
    			"rtmp://".$bizId.".liveplay.myqcloud.com/live/".$livecode,
    			"http://".$bizId.".liveplay.myqcloud.com/live/".$livecode.".flv",
    			"http://".$bizId.".liveplay.myqcloud.com/live/".$livecode.".m3u8"
    	);
    }
   
    
}