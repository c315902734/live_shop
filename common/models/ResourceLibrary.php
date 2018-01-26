<?php

namespace common\models;

use Yii;
include_once Yii::$app->basePath."/../QcloudApi/QcloudApi.php";
/**
 * This is the model class for table "resource_library".
 *
 * @property string $resource_id
 * @property string $thumbnail_url
 * @property string $file_name
 * @property string $file_id
 * @property string $url
 * @property string $url1
 * @property string $url2
 * @property string $duration
 * @property integer $height
 * @property integer $height1
 * @property integer $height2
 * @property integer $width
 * @property integer $width1
 * @property integer $width2
 * @property integer $size
 * @property integer $size1
 * @property integer $size2
 * @property integer $category
 * @property string $create_time
 * @property integer $status
 * @property integer $type
 * @property integer $is_water
 * @property string $operation_id
 */
class ResourceLibrary extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'resource_library';
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
            [['resource_id'], 'required'],
            [['resource_id', 'height', 'height1', 'height2', 'width', 'width1', 'width2', 'size', 'size1', 'size2', 'category', 'status', 'type', 'is_water', 'operation_id'], 'integer'],
            [['create_time'], 'safe'],
            [['thumbnail_url', 'url', 'url1', 'url2'], 'string', 'max' => 200],
            [['file_name'], 'string', 'max' => 300],
            [['file_id'], 'string', 'max' => 60],
            [['duration'], 'string', 'max' => 11],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'resource_id' => 'Resource ID',
            'thumbnail_url' => 'Thumbnail Url',
            'file_name' => 'File Name',
            'file_id' => 'File ID',
            'url' => 'Url',
            'url1' => 'Url1',
            'url2' => 'Url2',
            'duration' => 'Duration',
            'height' => 'Height',
            'height1' => 'Height1',
            'height2' => 'Height2',
            'width' => 'Width',
            'width1' => 'Width1',
            'width2' => 'Width2',
            'size' => 'Size',
            'size1' => 'Size1',
            'size2' => 'Size2',
            'category' => 'Category',
            'create_time' => 'Create Time',
            'status' => 'Status',
            'type' => 'Type',
            'is_water' => 'Is Water',
        	'operation_id' => 'Operation ID',
        ];
    }
    
    public static function AddResource($videoId = NULL,$coverURL = NULL, $file_name = NULL, $duration = NULL, $category = NULL, $operation_id = NULL){
    	if($videoId){
    		
    		//调用腾讯云视频详情接口
    		$config = array(
    				'SecretId'       => Yii::$app->params['API_SecretId'],
    				'SecretKey'      => Yii::$app->params['API_SecretKey'],
    				'RequestMethod'  => 'GET',
    				'DefaultRegion'  => Yii::$app->params['API_DefaultRegion']);
    		$service = \QcloudApi::load(\QcloudApi::MODULE_VIDEO, $config);
    		$package = array(
    				'fileId'=>$videoId
    		);
    		$video_info = $service->DescribeVodPlayUrls($package);
    		if($video_info){
    			if($video_info['codeDesc'] == 'Success'){
	    			$model = new self();
	    			$model->resource_id =time().self::getRange();
	    			$model->file_id = $videoId;
	    			$model->file_name = $file_name;
	    			$model->thumbnail_url = $coverURL;  //封面
	    			/*视频地址1*/
	    			$model->url = isset($video_info['playSet'][2]['url']) ? $video_info['playSet'][2]['url'] : '';
	    			$model->height = isset($video_info['playSet'][2]['vheight']) ? $video_info['playSet'][2]['vheight'] : '';
	    			$model->width = isset($video_info['playSet'][2]['vwidth']) ? $video_info['playSet'][2]['vwidth'] : '';
	    			$model->size = isset($video_info['playSet'][2]['size']) ? $video_info['playSet'][2]['size'] : '';
	    			/*视频地址2*/
	    			$model->url1 = isset($video_info['playSet'][1]['url']) ? $video_info['playSet'][1]['url'] : '';
	    			$model->height1 = isset($video_info['playSet'][1]['vheight']) ? $video_info['playSet'][1]['vheight'] : '';
	    			$model->width1 = isset($video_info['playSet'][1]['vwidth']) ? $video_info['playSet'][1]['vwidth'] : '';
	    			$model->size1 = isset($video_info['playSet'][1]['size']) ? $video_info['playSet'][1]['size'] : '';
	    			/*视频地址3*/
	    			$model->url2 = isset($video_info['playSet'][0]['url']) ? $video_info['playSet'][0]['url'] : '';
	    			$model->height2 = isset($video_info['playSet'][0]['vheight']) ? $video_info['playSet'][0]['vheight'] : '';
	    			$model->width2 = isset($video_info['playSet'][0]['vwidth']) ? $video_info['playSet'][0]['vwidth'] : '';
	    			$model->size2 = isset($video_info['playSet'][0]['size']) ? $video_info['playSet'][0]['size'] : '';
	    			
	    			$model->duration = $duration;
	    			$model->category = $category;
	    			$model->operation_id = $operation_id;
	    			$model->create_time = date('Y-m-d H:i:s',time());
	    			$model->status = '2';
	    			$model->type = '2';
	    			if($model->save()){
						//把资源池视频复制一份到行车导航仪
						$admin_info = AdminUser::find()
							->select('admin_user.admin_id, admin_user.real_name, company.name')
							->leftJoin('vrnews1.company', 'vradmin1.admin_user.company_id = vrnews1.company.company_id')
							->where(['admin_id'=>$operation_id])->asArray()->one();
						if($model->url)
						{
							$url = $model->url;
						}elseif($model->url1)
						{
							$url = $model->url1;
						}elseif($model->url2)
						{
							$url = $model->url2;
						}
						$live_weme = new LiveWeme();
						$live_weme['accountID']  = rand(1000, 100000);
						$live_weme['mirrtalkID'] = $admin_info['admin_id'];
						$live_weme['url']       = $url;
						$live_weme['mtime']     = time();
						$live_weme['device']    = 2;
						$live_weme['screenshot'] = $coverURL;
						$live_weme['tencent_url']= $url;
						$live_weme['area_name']  = '北京';
						$live_weme['company_id']= '1';
						$live_weme['is_forward'] = '1';
						$live_weme['area_id']    = '1';
						$live_weme->save();
						$live_weme_nickname = LiveWemeNickname::find()
							->where(['mirrtalkID'=>$admin_info['admin_id']])->one();
						if(!$live_weme_nickname)
						{
							$live_weme_nickname = new LiveWemeNickname();
						}
						$live_weme_nickname->url = 'http://vrlive-10047449.image.myqcloud.com/lv1512543452photo.png';
						$live_weme_nickname->mirrtalkID = $admin_info['admin_id'];
						$live_weme_nickname->nickname   = $admin_info['name'].' : '.$admin_info['real_name'];
						$live_weme_nickname->save();
						return $model->resource_id;
	    			}
    			}	
    		}
    		return FALSE;
    	}
    	
    	return FALSE;
    }
    
    
    //删除资源
    public static function DeteleResource($resource_id = NULL){
    	if(ResourceLibrary::deleteAll(['resource_id'=>$resource_id])){
    		return true;
    	}
    	return false;
    }
    
    
    private static function getRange($cnt=9){
    	$numbers = range (1,$cnt);
    	//播下随机数发生器种子，可有可无，测试后对结果没有影响
    	srand ((float)microtime()*1000000);
    	shuffle ($numbers);
    	//跳过list第一个值（保存的是索引）
    	$n = '';
    	while (list(, $number) = each ($numbers)) {
    		$n .="$number";
    	}
    	return $n;
    }
    
    public static function GetResourceVideoList($admin_id = NULL, $pageStart = NULL, $pageEnd = NULL){	
    	$resultData = array();
    	$where = '(url is not null or url1 is not null or url2 is not null)';
    	
    	if($admin_id){
    		if($admin_id != '1'){
    			if($where) $where .=' and ';
    			$where .= ' operation_id = '.$admin_id;
    		}
    	}
    	$model = new self();
    	$count_Total = $model::find()->where($where)->count();
    	$resources_list = $model::find()
    	->where($where)
    	->orderBy(['create_time'=>SORT_DESC])
    	->offset($pageStart)
    	->limit($pageEnd-$pageStart)
    	->asArray()->all();
    	
		foreach($resources_list as $key=>$value){
			$video_url = '';
			$video_height = '';
			$video_width = '';
			$video_size = '';
			if($value['url']) $video_url = $value['url'];
			if($value['height']) $video_height = $value['height'];
			if($value['width']) $video_width = $value['width'];
			if($value['size']) $video_size = $value['size'];		
			
			if(!$video_url) $video_url = $value['url1'];
			if(!$video_url) $video_url = $value['url2'];
			
			if(!$video_height) $video_height = $value['height1'];
			if(!$video_height) $video_height = $value['height2'];
			
			if(!$video_width) $video_width = $value['width1'];
			if(!$video_width) $video_width = $value['width2'];
			
			if(!$video_size) $video_size = $value['size1'];
			if(!$video_size) $video_size = $value['size2'];
			
			$resources_list[$key]['video_url'] = $video_url;
			$resources_list[$key]['video_height'] = $video_height;
			$resources_list[$key]['video_width'] = $video_width;
			$resources_list[$key]['video_size'] = $video_size;
		}
		
    	$resultData['list'] = $resources_list;
    	$resultData['count_Total'] = $count_Total;
    	return $resultData; 	
    }
    
}
