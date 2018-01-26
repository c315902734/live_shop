<?php
namespace common\models;

use common\service\Service;
use Yii;
use common\service\ServicePublic;
use yii\imagine\Image;
use yii\helpers\BaseFileHelper;

/**
 * 文件上传类
 */
class UploadFile {
    public  $max_size = "500";//设置上传文件大小为500M
    public  $file_name = '';//重命名方式代表以时间命名，其他则使用给予的名称
    public  $allow_types;//允许上传的文件扩展名，不同文件类型用“|”隔开
    public  $errmsg = '';//错误信息
    public  $uploaded = '';//上传后的文件名(包括文件路径)
    public  $save_path;//上传文件保存路径
    public  $save_video_path; //保存到数据库的视频地址
    public  $save_pics_path = array();
    public  $big_pic;
    public  $type;
    public  $source = 'api_image';
    public  $small_pic;
    private $files;//提交的等待上传文件
    private $file_type = array();//文件类型
    public $ext = '';//上传文件扩展名
  
    //原图片路径
    public $origin_img_path = "";
    //小缩略图路径
    public $small_img_path  = "";
    //大缩略图路径
    public $large_img_path  = "";


    /**
     * 构造函数，初始化类
     * @access public
     * @param  int $type  1:上传图片 2上传视频
     */
    public function __construct() {
        $this->allow_types = 'jpg|png|jpeg|gif|bmp|mp4';

        //生成所需的文件夹
        $ymd_time = date('Y-m-d',time());
        $this->origin_img_path = Yii::$app->basePath.'/web/localFile/api_image/';
        $this->small_img_path  = Yii::$app->basePath.'/web/localFile/api_image/';
        $this->large_img_path  = Yii::$app->basePath.'/web/localFile/api_image/';
  
        /*if (!file_exists($this->origin_img_path)) {
        /* if (!file_exists($this->origin_img_path)) {
            BaseFileHelper::createDirectory($this->origin_img_path);
        }

        if (!file_exists($this->small_img_path)) {
            BaseFileHelper::createDirectory($this->small_img_path);
        }

        if (!file_exists($this->large_img_path)) {
            BaseFileHelper::createDirectory($this->large_img_path);
        }*/
    }
        
    /**
     * 上传文件
     * @access public
     * @param $files 等待上传的文件(表单传来的$_FILES[])
     * @return boolean 返回布尔值
     * $type 1图片，2视频
     * $pictype 0单图，1多图
     */
    public function upload_file($files,$type=1,$pictype=0, $make_thumb=1) {
        if($pictype == 1){
            foreach($files as $key=>$val){
                $name = $val['name'];
                $type = $val['type'];
                $size = $val['size'];
                $tmp_name = $val['tmp_name'];
                $error = $val['error'];
                switch ($val['error']) {
                    case 0 : $this->errmsg = '';
                        break;
                    case 1 : $this->errmsg = '超过了php.ini中文件大小';
                        break;
                    case 2 : $this->errmsg = '超过了MAX_FILE_SIZE 选项指定的文件大小';
                        break;
                    case 3 : $this->errmsg = '文件只有部分被上传';
                        break;
                    case 4 : $this->errmsg = '没有文件被上传';
                        break;
                    case 5 : $this->errmsg = '上传文件大小为0';
                        break;
                    default : $this->errmsg = '上传文件失败！';
                        break;
                }

                if($error == 0 && is_uploaded_file($tmp_name)) {

                    //检测文件类型
                    if($this->check_file_type($name) == FALSE){
                        return FALSE;
                    }
                    //检测文件大小
                    if($size > $this->max_size*1024*1024){
                        $this->errmsg = '上传文件'.$name.'太大，最大支持'.ceil($this->max_size*1024*1024).'kb的文件';
                        return FALSE;
                    }

                    //设置文件存放路径
                    //$this->set_save_path();
                    $this->new_name = $this->file_name != 'date' ? $this->file_name : date('YmdHis').rand().'.'.$this->ext;//设置新文件名

                    $this->uploaded = $this->save_path.$this->new_name;//上传后的文件名
                    //$this->save_video_path = $this->save_video_path.$this->new_name;
                    $this->save_video_path = $this->origin_img_path.$this->new_name;
                    
                    $ymd_time = date('Y-m-d',time());
                    $this->save_pics_path['type'][$key] = $this->ext;
                    $this->save_pics_path['name'][$key] = $name;
                    $this->save_pics_path['info'][$key] = $this->origin_img_path.$this->new_name;
                    //移动文件
                    if(move_uploaded_file($tmp_name,$this->save_video_path)){
                        //对图片进行压缩处理
                        //$this->thumallImg($this->new_name);
                        $this->errmsg = '文件'.$this->uploaded.'上传成功！';
                    }else{
                        $this->errmsg = '文件'.$this->uploaded.'上传失败！';
                        return FALSE;
                    }
                }
            }
            return TRUE;
        }
        $name = $files['name'];
        $type = $files['type'];
        $size = $files['size'];
        $tmp_name = $files['tmp_name'];
        $error = $files['error'];
        switch ($error) {
            case 0 : $this->errmsg = '';
                break;
            case 1 : $this->errmsg = '超过了php.ini中文件大小';
                break;
            case 2 : $this->errmsg = '超过了MAX_FILE_SIZE 选项指定的文件大小';
                break;
            case 3 : $this->errmsg = '文件只有部分被上传';
                break;
            case 4 : $this->errmsg = '没有文件被上传';
                break;
            case 5 : $this->errmsg = '上传文件大小为0';
                break;
            default : $this->errmsg = '上传文件失败！';
                break;
        }
        if($error == 0 && is_uploaded_file($tmp_name)) {
            //检测文件类型
            if($this->check_file_type($name) == FALSE){
                return FALSE;
            }
            //检测文件大小
            if($size > $this->max_size*1024*1024){
                $this->errmsg = '上传文件'.$name.'太大，最大支持'.ceil($this->max_size*1024*1024).'kb的文件';
                return FALSE;
            }

            //设置文件存放路径
            //$this->set_save_path();
            $new_name = $this->file_name != 'date' ? $this->file_name : date('YmdHis').rand(10,100).'.'.$this->ext;//设置新文件名

            //上传后的文件名
            $this->uploaded        = $this->save_path.$new_name;
            //上传后文件的保存地址
            $this->save_video_path = $this->origin_img_path.$new_name;
            
            if (!is_dir($this->origin_img_path)) {
            	mkdir($this->origin_img_path,0777,true);
            }
            //移动文件
            if(move_uploaded_file($tmp_name,$this->save_video_path)){
            	if($make_thumb){
            		if($this->type == 1) {
            			//对图片进行压缩处理
            			$this->thumallImg($new_name);
            		}
            	}
                
                $this->errmsg = '文件'.$this->uploaded.'上传成功！';
                return $this->uploaded;
            }else{
                $this->errmsg = '文件'.$this->uploaded.'上传失败！';
                return FALSE;
            }
        }
    }

    /**
     * 检查上传文件类型
     * @access public
     * @param string $filename 等待检查的文件名
     * @return 如果检查通过返回TRUE 未通过则返回FALSE和错误消息
     */
    public function check_file_type($filename){
        $ext = strtolower($this->get_file_type($filename));
        $this->ext = $ext;
        $this->file_name = $filename;
        $allow_types = explode('|',$this->allow_types);//分割允许上传的文件扩展名为数组
        //echo $ext;
        //检查上传文件扩展名是否在请允许上传的文件扩展名中
        if(in_array($ext,$allow_types)){
            return TRUE;
        }else{
            $this->errmsg = '上传文件'.$filename.'类型错误，只支持上传'.str_replace('|',',',$this->allow_types).'等文件类型!';
            return FALSE;
        }
    }
    /**
     * 取得文件类型
     * @access public
     * @param string $filename 要取得文件类型的目标文件名
     * @return string 文件类型
     */
    public function get_file_type($filename){
        $info = pathinfo($filename);
        $ext = $info['extension'];
        return $ext;
    }
    /**
     * 设置文件上传后的保存路径
     */
    public function set_save_path(){
        $ymd_time = date('Y-m-d',time());
        if(in_array($this->ext,array('jpg','png','jpeg','gif'))){
            $this->type = 1;
            $imgPath = Yii::$app->basePath.'/../web/localFile/'.$this->source.'/';
            $small = Yii::$app->basePath.'/../web/small/localFile/'.$this->source.'/';
            $big = Yii::$app->basePath.'/../web/big/localFile/'.$this->source.'/';
            if (!is_dir($imgPath)) {
                mkdir($imgPath,0777,true);
            }
            if (!is_dir($big)) {
                mkdir($big,0777,true);
            }
            if (!is_dir($small)) {
                mkdir($small,0777,true);
            }
            $this->save_path       = $imgPath;
            $this->save_video_path = Yii::$app->basePath.'/../web/localFile/'.$this->source.'/';
            $this->origin_img_path = 'localFile/'.$this->source.'/';
        }else{
            $this->type = 2;
//            $this->source ='file';
            $this->origin_img_path = 'localFile/'.$this->source.'/';
            $this->save_path = Yii::$app->basePath.'/../web/localFile/'.$this->source.'/';
            $this->save_video_path = Yii::$app->basePath.'/../web/localFile/'.$this->source.'/';
            if(!is_dir($this->save_path)){
                //如果目录不存在，创建目录
                //$this->set_dir();
                mkdir($this->save_path,0777,true);
            }
        }
    }


    /**
     * 创建目录
     * @access public
     * @param string $dir 要创建目录的路径
     * @return boolean 失败时返回错误消息和FALSE
     */
    public function set_dir($dir = null){
        //检查路径是否存在
        if(!$dir){
            $dir = $this->save_path;
        }

        if(is_dir($dir)){
            $this->errmsg = '需要创建的文件夹已经存在！';
        }
        $dir = explode('/', $dir);
        $d = '';

        foreach($dir as $v){
            if($v){
                $d .= $v . '/';
                if(!is_dir($d)){
                    $state = mkdir($d, 0777);
                    if(!$state)
                        $this->errmsg = '在创建目录' . $d . '时出错！';
                }
            }
        }
        return true;
    }

    /**
     * 压缩图片
     * @param $file_name
     * @param $file_new 压缩后图片地址
     * @return bool
     */
    public function scal_pic($file_name,$file_new,$size){
        //验证参数
        if(!is_string($file_name) || !is_string($file_new)){
            return false;
        }
        //获取图片信息
        $pic_scal_arr = @getimagesize($file_name);

        if(!$pic_scal_arr){
            return false;
        }
        //获取图象标识符
        $pic_creat = '';
        switch($pic_scal_arr['mime']){
            case 'image/jpeg':
                $pic_creat = @imagecreatefromjpeg($file_name);
                break;
            case 'image/gif':
                $pic_creat = @imagecreatefromgif($file_name);
                break;
            case 'image/png':
                $pic_creat = @imagecreatefrompng($file_name);
                break;
            case 'image/wbmp':
                $pic_creat = @imagecreatefromwbmp($file_name);
                break;
            default:
                return false;
                break;
        }
        if(!$pic_creat){
            return false;
        }
        //判断/计算压缩大小
        $max_width = 511;//最大宽度,象素，高度不限制
        $min_width = 15;
        $min_heigth = 20;
        if($pic_scal_arr[0]<$min_width || $pic_scal_arr[1]<$min_heigth){
            return false;
        }
        $re_scal = 0;
        if($pic_scal_arr[0]>$max_width){
            $re_scal = ($max_width / $pic_scal_arr[0]);
        }

        if($size>100*1024){
            $re_width  = round($pic_scal_arr[0] * $re_scal);
            $re_height = round($pic_scal_arr[1] * $re_scal);
        }else{
            $re_width  = $pic_scal_arr[0];
            $re_height = $pic_scal_arr[1];
        }

        //创建空图象
        $new_pic = @imagecreatetruecolor($re_width,$re_height);

        if(!$new_pic){
            return false;
        }

        if($size>100*1024){
            if(!@imagecopyresampled($new_pic,$pic_creat,0,0,0,0,$re_width,$re_height,$pic_scal_arr[0],$pic_scal_arr[1])){
                return false;
            }
        }else{
            imagecopy( $new_pic, $pic_creat, 0, 0, 0, 0, $pic_scal_arr[0], $pic_scal_arr[1] );
        }

        //输出文件
        $out_file = '';
        switch($pic_scal_arr['mime']){
            case 'image/jpeg':
                $out_file = @imagejpeg($new_pic,$file_new);
                break;
            case 'image/jpg':
                $out_file = @imagejpeg($new_pic,$file_new);
                break;
            case 'image/gif':
                $out_file = @imagegif($new_pic,$file_new);
                break;
            case 'image/bmp':
                $out_file = @imagebmp($new_pic,$file_new);
                break;
            default:
                return false;
                break;
        }
        if($out_file){
            return true;
        }else{
            return false;
        }
    }


    /**
     * 对图片进行
     * @param $file_name
     */
    public function thumallImg($file_name)
    {
        $pic_scal_arr = @getimagesize($this->origin_img_path.$file_name);
        $cl = true;
        if(!$pic_scal_arr){
            $cl = false;
        }

        //处理两种尺寸的图片
        //处理小图
        /* $cl_small     = true;
        $option_samll = $this->getWh("small",$pic_scal_arr[0],$pic_scal_arr[1]);
        if($option_samll === false){
            $cl_small = $option_samll;
        }

        //处理大图
        $cl_big     = true;
        $option_big = $this->getWh("big",$pic_scal_arr[0],$pic_scal_arr[1]);
        if($option_big === false){
            $cl_big = $option_big;
        }
 */
//         if($cl && $cl_small) {
        if($cl && $cl_small) {
            Image::thumbnail($this->origin_img_path.$file_name, $option_samll['width'], $option_samll['height'])->save($this->small_img_path.$file_name, ['quality' => 80]);
        }else{
//            Image::thumbnail($this->origin_img_path.$file_name, $pic_scal_arr[0], $pic_scal_arr[1])->save($this->small_img_path.$file_name, ['quality' => 80]);
            if (!is_dir(dirname($this->small_img_path.$file_name))) {
                mkdir(dirname($this->small_img_path.$file_name), 0777, true);
            }
            return copy($this->origin_img_path.$file_name, $this->small_img_path.$file_name);
        }

        if($cl && $cl_big) {
            Image::thumbnail($this->origin_img_path.$file_name, $option_big['width'], $option_big['height'])->save($this->large_img_path.$file_name, ['quality' => 80]);
        }else{
            Image::thumbnail($this->origin_img_path.$file_name, $pic_scal_arr[0]*0.5, $pic_scal_arr[1]*0.5)->save($this->large_img_path.$file_name, ['quality' => 80]);
        }
    }

    /**
     * 获取缩略图尺寸
     * @param $type
     * @param $ori_width
     * @param $ori_height
     * @return bool
     */
    private function getWh($type,$ori_width,$ori_height)
    {
        $option['width'] = 0;
        $option['height'] = 0;

        if($type == "small"){
            //判断/计算压缩大小
            $max_width  = Yii::$app->params['small_thumb_max_width'];//最大宽度,象素，高度不限制
            $min_width  = Yii::$app->params['small_thumb_min_width'];
            $min_heigth = Yii::$app->params['small_thumb_min_height'];
        }elseif($type == "big"){
            //判断/计算压缩大小
            $max_width  = Yii::$app->params['larger_thumb_max_width'];//最大宽度,象素，高度不限制
            $min_width  = Yii::$app->params['larger_thumb_min_width'];
            $min_heigth = Yii::$app->params['larger_thumb_min_height'];
        }

        if($ori_width<$min_width || $ori_height<$min_heigth){
            return false;
        }

        $re_scal = 0;
        if($ori_width > $max_width){
            $re_scal = ($max_width / $ori_width);
        }
        if($re_scal == 0){
            $re_width  = $ori_width;
            $re_height = $ori_height;
        }else{
            $re_width  = round($ori_width * $re_scal);
            $re_height = round($ori_height * $re_scal);
        }
        $option['width']  = $re_width;
        $option['height'] = $re_height;
        return $option;
    }
    
    public static  function getExt(){
    	return $this->ext;
    }

}
