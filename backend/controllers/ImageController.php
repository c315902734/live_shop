<?php
namespace backend\controllers;

use common\models\Tencentyun\ImageV2;
use Yii;
use common\models\ResourceLibrary;
use common\models\ImageProcess;
use common\models\UploadFile;

/**
 * Image controller
 */
class ImageController extends PublicBaseController
{
//    public $enableCsrfValidation = false;
//    public $server;
//    public function actionUpload()
//    {
//        return $this->render('upload');
//    }

    public function actionImgUpload()
    {
        $pic=isset($_POST['files'])?$_POST['files']:'';
        $type=isset($_POST['type'])?$_POST['type']:'';
        $file_name=isset($_POST['file_name'])?$_POST['file_name']:'';
        $set_water=isset($_POST['set_water'])?$_POST['set_water']:'0';
        $admin_id = isset($_POST['admin_id'])?$_POST['admin_id']:'0';

        if(!$pic ||! $file_name){
            $this->_errorData('337','参数错误');
        }
        $file_type=pathinfo($file_name);
        if(empty($file_type['extension'])){
            $this->_errorData('337','参数错误');
        }else{
            $type=$file_type['extension'];
        }
        if($pic){
            $pic=base64_decode($pic);
            $time=time();
            $ymd_time=date('Y-m-d',$time);
            $temp=Yii::$app->basePath.'/web/uploadFiles/temp/'.$ymd_time;
            $imgPath=Yii::$app->basePath.'/web/uploadFiles/original/'.$ymd_time.'/';

            if (!is_dir($temp)) {
                mkdir($temp,0777,true);
            }

            $tmpPath =$temp."/".$time.'.data';
            if (@$fp = fopen ( $tmpPath, 'w+' )) {
                fwrite ( $fp, $pic );
                fclose ( $fp );
                if ( $imgInfo = $this->getimageInfo ( $tmpPath )) {
                    $filesize = $imgInfo['size'];
                    $imageType = $imgInfo['type'];
                    if(in_array(strtolower($imageType),array('jpeg', 'jpg', 'bmp', 'png','gif'))){
                        $imgName = date("his") . rand(100, 999);
                        $imgPath.=$imgName.'/';
                        if (!is_dir($imgPath)) {
                            mkdir($imgPath,0777,true);
                        }
                        if(!copy($tmpPath, $imgPath.strtolower($file_name))) {
                            $this->_errorData('724','文件服务器错误');
                        }else{
                            //资源表创建数据
                            $resource_model = new ResourceLibrary();
                            $resource_model->resource_id = time().$this->getRange();

                            $cname = 'uploadFiles/original/'.$ymd_time.'/'.$imgName.'/'.strtolower($file_name);
                            $newFileName = 'lv'.time().$file_name;  // 自定义文件名s

                            if($set_water == '1' && strtolower($imageType) != 'gif'){
                                $logo = 'logo.png';
                                //根据比例缩放水印图
                                $ginfo = getimagesize($cname);
                                $base = $ginfo[0] > $ginfo[1] ? $ginfo[0] : $ginfo[1];
                                $proportion = round($base/800,1);
                                $logo_process = new ImageProcess($logo);
                                $new_logo = $logo_process->scaleImage($proportion,$logo,'uploadFiles/original/'.$ymd_time.'/'.$imgName.'/new_logo.png');

                                $image_process = new ImageProcess($cname);
                                $new_pic = $image_process->watermarkImage($new_logo);
                                $uploadRet = ImageV2::upload($new_pic, 'vrlive', $newFileName);
                            }else{
                                $uploadRet = ImageV2::upload($cname, 'vrlive', $newFileName);
                            }

                            if($uploadRet['httpcode'] == 200){
                                $imageUrl = $uploadRet['data']['downloadUrl'];

                                $resource_model->thumbnail_url = $imageUrl;
                                $resource_model->file_name = $file_name;
                                $resource_model->type = '1';
                                $resource_model->create_time = date('Y-m-d H:i:s',time());
                                $resource_model->operation_id = $admin_id;
                                if($set_water == '1') $resource_model->is_water = '1';
                                if($resource_model->save()){
                                    //删除本地文件
                                    unlink($cname);
                                    if($set_water == '1' && strtolower($imageType) != 'gif'){
                                        unlink($new_logo);
                                        unlink($new_pic);
                                        unlink($tmpPath);
                                    }
                                    $this->_successData(array('imageUrl'=>$imageUrl));
                                }
                            }

                        }
                    }else{
                        $this->_errorData('349','保存失败');
                    }
                }else if($type){
                    $imgName = date("his") . rand(100, 999);
                    $imgPath.=$imgName.'/';
                    if (!is_dir($imgPath)) {
                        mkdir($imgPath,0777,true);
                    }
                    if(!copy($tmpPath, $imgPath.strtolower($file_name))) {
                        $this->_errorData('724','文件服务器错误');
                    }else{
                        //资源表创建数据
                        $resource_model = new ResourceLibrary();
                        $resource_model->resource_id = time().$this->getRange();

                        $cname = 'uploadFiles/original/'.$ymd_time.'/'.$imgName.'/'.strtolower($file_name);
                        $newFileName = 'lv'.time().$file_name;  // 自定义文件名s

                        if($set_water == '1' && strtolower($type) != 'gif'){
                            $logo = 'logo.png';
                            //根据比例缩放水印图
                            $ginfo = getimagesize($cname);
                            $base = $ginfo[0] > $ginfo[1] ? $ginfo[0] : $ginfo[1];
                            $proportion = round($base/800,1);
                            $logo_process = new ImageProcess($logo);
                            $new_logo = $logo_process->scaleImage($proportion,$logo,'uploadFiles/original/'.$ymd_time.'/'.$imgName.'/new_logo.png');

                            $image_process = new ImageProcess($cname);
                            $new_pic = $image_process->watermarkImage($new_logo);
                            $uploadRet = ImageV2::upload($new_pic, 'vrlive', $newFileName);
                        }else{
                            $uploadRet = ImageV2::upload($cname, 'vrlive', $newFileName);
                        }

                        if($uploadRet['httpcode'] == 200){
                            $imageUrl = $uploadRet['data']['downloadUrl'];

                            $resource_model->thumbnail_url = $imageUrl;
                            $resource_model->file_name = $file_name;
                            $resource_model->type = '1';
                            $resource_model->create_time = date('Y-m-d H:i:s',time());
                            $resource_model->operation_id = $admin_id;
                            if($set_water == '1') $resource_model->is_water = '1';
                            if($resource_model->save()){
                                //删除本地文件
                                unlink($cname);
                                if($set_water == '1' && strtolower($type) != 'gif'){
                                    unlink($new_logo);
                                    unlink($new_pic);
                                    unlink($tmpPath);
                                }
                                $this->_successData(array('imageUrl'=>$imageUrl));
                            }
                        }
                    }
                }else{
                    $this->_errorData('349','保存失败');
                }
            }else{
                $this->_errorData('349','保存失败');
            }
        }else{
            $this->_errorData('337','参数错误');
        }
    }

    function getimageInfo($imageName = '') {
        $imageInfo = getimagesize($imageName);
        if ($imageInfo !== false) {
            $new_img_info = array (
                "name"=>basename($imageName),
                "width"=>$imageInfo[0],
                "height"=>$imageInfo[1],
                "type"=>strtolower ( substr ( image_type_to_extension ( $imageInfo [2] ), 1 ) ),
                "size"=>filesize($imageName)
            );
            return $new_img_info;
        }else {
            return false;
        }
    }

    function getRange($cnt=9){
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


    public function actionImgsUpload()
    {
    	$set_water=isset($_POST['set_water'])?$_POST['set_water']:'0';
    	$admin_id = isset($_POST['admin_id'])?$_POST['admin_id']:'0';

    	$upload = new UploadFile();
    	if(count($_FILES) == 1){
    		if($upload->upload_file($_FILES['pic'])){
    			$ymd_time=date('Y-m-d',time());
    			$imgName = date("his") . rand(100, 999);
    			
    			//资源表创建数据
    			$resource_model = new ResourceLibrary();
    			$resource_model->resource_id = time().$this->getRange();
    			
    			$cname = $upload->save_video_path;
    			$newFileName = 'lv'.time().$upload->file_name;  // 自定义文件名s

    			if($set_water == '1' && strtolower($upload->ext) != 'gif'){
    				$logo = 'logo.png';
    				//根据比例缩放水印图
    				$ginfo = getimagesize($cname);
    				$base = $ginfo[0] > $ginfo[1] ? $ginfo[0] : $ginfo[1];
    				$proportion = round($base/800,1);
    				$logo_process = new ImageProcess($logo);
    				$new_logo_path = 'uploadFiles/original/'.$ymd_time.'/'.$imgName;
    				if (!is_dir($new_logo_path)) {
    					mkdir($new_logo_path,0777,true);
    				}
    				$new_logo = $logo_process->scaleImage($proportion,$logo,$new_logo_path.'/new_logo.png');
    			
    				$image_process = new ImageProcess($cname);
    				$new_pic = $image_process->watermarkImage($new_logo);
    				$uploadRet = ImageV2::upload($new_pic, 'vrlive', $newFileName);
    			}else{
    				$uploadRet = ImageV2::upload($cname, 'vrlive', $newFileName);
    			}

    			if($uploadRet['httpcode'] == 200){
    				$imageUrl = $uploadRet['data']['downloadUrl'];
    			
    				$resource_model->thumbnail_url = $imageUrl;
    				$resource_model->file_name = $upload->file_name;
    				$resource_model->type = '1';
    				$resource_model->create_time = date('Y-m-d H:i:s',time());
    				$resource_model->operation_id = $admin_id;
    				if($set_water == '1') $resource_model->is_water = '1';
    				if($resource_model->save()){
    					//删除本地文件
    					unlink($cname);
    					if($set_water == '1' && strtolower($upload->ext) != 'gif'){
    						unlink($new_logo);
    						unlink($new_pic);
    					}
    					$this->_successData(array('imageUrl'=>$imageUrl));
    				}
    			}
    		}else{
    			$this->_errorData('349','保存失败');
    		}
    	}else{
    		if($upload->upload_file($_FILES,1,1)){
    			$data = array();
    			$save_video_path = $upload->save_pics_path['info'];
    			$save_video_type = $upload->save_pics_path['type'];
    			$save_video_name = $upload->save_pics_path['name'];
    			
    			$k = 0;
    			foreach($save_video_path as $key=>$val) {
    				
    				$ymd_time=date('Y-m-d',time());
    				$imgName = date("his") . rand(100, 999);
    				 
    				//资源表创建数据
    				$resource_model = new ResourceLibrary();
    				$resource_model->resource_id = time().$this->getRange();
    				 
    				$cname = $val;
    				$newFileName = 'lv'.time().$save_video_name[$key];  // 自定义文件名s
    				if($set_water == '1' && strtolower($save_video_type[$key]) != 'gif'){
    					$logo = 'logo.png';
    					//根据比例缩放水印图
    					$ginfo = getimagesize($cname);
    					$base = $ginfo[0] > $ginfo[1] ? $ginfo[0] : $ginfo[1];
    					$proportion = round($base/800,1);
    					$logo_process = new ImageProcess($logo);
    					$new_logo_path = 'uploadFiles/original/'.$ymd_time.'/'.$imgName;
    					if (!is_dir($new_logo_path)) {
    						mkdir($new_logo_path,0777,true);
    					}
    					$new_logo = $logo_process->scaleImage($proportion,$logo,$new_logo_path.'/new_logo.png');
    					 
    					$image_process = new ImageProcess($cname);
    					$new_pic = $image_process->watermarkImage($new_logo);
    					$uploadRet = ImageV2::upload($new_pic, 'vrlive', $newFileName);
    				}else{
    					$uploadRet = ImageV2::upload($cname, 'vrlive', $newFileName);
    				}
    				 
    				if($uploadRet['httpcode'] == 200){
    					$imageUrl = $uploadRet['data']['downloadUrl'];
    					 
    					$resource_model->thumbnail_url = $imageUrl;
    					$resource_model->file_name = $save_video_name[$key];
    					$resource_model->type = '1';
    					$resource_model->create_time = date('Y-m-d H:i:s',time());
    					$resource_model->operation_id = $admin_id;
    					if($set_water == '1') $resource_model->is_water = '1';
    					if($resource_model->save()){
    						//删除本地文件
    						unlink($cname);
    						if($set_water == '1' && strtolower($save_video_type[$key]) != 'gif'){
    							unlink($new_logo);
    							unlink($new_pic);
    						}
    						$data[$k]['imageUrl'] = $imageUrl;
    					}
    				}
    				$k = $k + 1;
    			}
    			$this->_successData($data);
    		}else{
    			$this->_errorData('349','保存失败');
    		}

    	}
    }

}

