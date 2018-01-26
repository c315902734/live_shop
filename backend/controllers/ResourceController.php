<?php
namespace backend\controllers;

use Yii;
use common\models\UploadFile;
use yii\web\UploadedFile;
use common\models\ResourceLibrary;
use common\models\Tencentyun\ImageV2;
use common\models\ImageProcess;

class ResourceController extends PublicBaseController
{   
    
	/**
     * 获取签名
     * @cong.zhao
     */
	public function actionGetSignature(){
		$secret_id = Yii::$app->params['API_SecretId'];
		$secret_key = Yii::$app->params['API_SecretKey'];
		
		// 确定签名的当前时间和失效时间
		$current = time();
		$expired = $current + 86400;  // 签名有效期：1天
		
		// 向参数列表填入参数
		$arg_list = array(
				"secretId" => $secret_id,
				"currentTimeStamp" => $current,
				"expireTime" => $expired,
				"random" => rand());
		
		// 计算签名
		$orignal = http_build_query($arg_list);
		$signature = base64_encode(hash_hmac('SHA1', $orignal, $secret_key, true).$orignal);
		
		$this->_successData(array('signature'=>$signature));
	}
	
	
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
    	$duration = isset($_REQUEST['duration'])?$_REQUEST['duration']:'';
    	$category=isset($_REQUEST['category'])?$_REQUEST['category']:'1';
    	$operation_id=isset($_REQUEST['operation_id'])?$_REQUEST['operation_id']:'0';
    	$videoId = isset($_REQUEST['videoId'])?$_REQUEST['videoId']:'';
    	//$videoURL = isset($_REQUEST['videoURL'])?$_REQUEST['videoURL']:'';
    	$coverURL = isset($_REQUEST['coverURL'])?$_REQUEST['coverURL']:'';
    	

    	if(!$file_name  || !$operation_id || !$videoId || !$coverURL || !$duration){
    		$this->_errorData('337','参数错误');
    	}
    	
    	$resource_id = ResourceLibrary::AddResource($videoId,$coverURL, $file_name, $duration, $category, $operation_id);
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
    
}