<?php
namespace frontend\controllers;
use Yii;
use yii\base\Controller;
use Tencentyun\ImageV2;
use common\models\User;
use common\models\UploadFile;
use common\models\User1;
use frontend\models\Task;

class UserinfoController extends BaseApiController
{
	/**
	 *  获取用户信息接口
	 */
	public function actionGetinfo(){
		
	}
	
	/* 上传头像（修改头像  */
	public function actionUploadavatar(){
		if(Yii::$app->request->isPost){
			$image = $_POST['image'];

			if($image){
				$image_str 	  = base64_decode($image);
				$image_name   = time().'.jpg';
				$image_path   = Yii::$app->basePath.'/web/localFile/temp/';
				
				if (!is_dir($image_path)) {
					mkdir($image_path,0777,true);
				}
				
				if(file_put_contents($image_path.$image_name, $image_str)){
					if(filesize($image_path.$image_name) > 5 * 1024 * 1024){
						$this->_errorData("5002", "文件过大");
					}
					$uploadRet = ImageV2::upload($image_path.$image_name, 'vrlive');

					//删除本地图片
					unlink($image_path.$image_name);

					if ($uploadRet['httpcode'] == 200) {
						$imageUrl = $uploadRet['data']['downloadUrl'];
						if (strlen($imageUrl) > 0) {
							$user = $this->_getUserModel();
							$user->avatar = $imageUrl;
							$result = $user->save();
							if ($result !== false) {
								$this->_successData(array("img_url" => $imageUrl), "上传头像成功");
							} else {
								$this->_errorData("5000", "非常抱歉，系统繁忙");
							}
						} else {
							$this->_errorData("5000", "解析返回值失败");
						}
					} else {
						//上传失败，返回错误
						$this->_errorData("5000", "非常抱歉，上传失败");
					}
				}
				$this->_errorData("5001", "文件写入失败");
			}else{
                $this->_errorData('5004', '请选择图片');
            }
		}else{
		    $this->_errorData('5003', '非法请求');
        }
	}
}