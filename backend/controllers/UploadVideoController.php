<?php
namespace backend\controllers;

use common\models\LiveFriendCircle;
use common\models\LiveWeme;
use common\models\LiveWemeVideo;
use common\models\NewsColumnType;
use common\models\SpecialColumnType;
use common\service\Record;
use Yii;
use yii\helpers\VarDumper;
use yii\log\FileTarget;

include_once Yii::$app->basePath."/../QcloudApi/Module/VodUpload.php";
/**
 * Column controller
 */
class UploadVideoController extends PublicBaseController
{

    /**
     * 上传视频到腾讯云服务器
     * @param url 视频地址
     */
    public function actionIndex(){
        ignore_user_abort();
         $data = Yii::$app->redis->blpop('up',1); 
        if ($data) {
        
        $url     = isset($this->params['url']) ? $this->params['url'] : '';
        $weme_id = isset($this->params['weme_id']) ? $this->params['weme_id'] : '';
        $friend_circle_id = isset($this->params['friend_circle_id']) ? $this->params['friend_circle_id'] : '';
       

        $filepath = self::download_video($url);
        $file_id  = self::upload_video_to_txy($filepath);
        $video    = new LiveWemeVideo();
        $video['video_url']   = $url;
        $video['file_id']     = $file_id;
        $video['create_time'] = date('Y-m-d H:i:s', time());
        $video->save();
        LiveWeme::updateAll(['file_id'=>$file_id], 'id = '.$weme_id);
        LiveFriendCircle::updateAll(['file_id'=>$file_id], 'tid in( '.$friend_circle_id .')');

         }
    }


   public function actionBroadcast(){
        ignore_user_abort();
        $url     = isset($this->params['url']) ? $this->params['url'] : '';
        $weme_id = isset($this->params['weme_id']) ? $this->params['weme_id'] : '';
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : '';
        $content = isset($this->params['content']) ? $this->params['content'] : '';
        $w_data['content']=$content;
        $w_data['live_id']=$live_id;

        $weme_video_data = LiveWemeVideo::findOne(['video_id'=>$weme_id]);
        if ($weme_video_data) {
            $weme_video_data=$weme_video_data->toArray();
            // $time            = microtime(true); 
            // $log             = new FileTarget();  
            // $log->logFile    = Yii::$app->getRuntimePath() . '/logs/test.log';  
            // $log->messages[] = ['数据'.PHP_EOL ,1, ($weme_video_data['status']),$time];  
            // $log->export();  
        }

        if ($weme_video_data['status']==2) {

            $data=unserialize($weme_video_data['data']);
            array_push($data , $w_data);
            $data=serialize($data);

            $time            = microtime(true); 
            $log             = new FileTarget();  
            $log->logFile    = Yii::$app->getRuntimePath() . '/logs/test.log';  
            $log->messages[] = ['数据'.PHP_EOL ,1, ($data),$time];  
            $log->export();  


            LiveWemeVideo::updateAll(['data'=>$data], 'id = '.$weme_video_data['id']);

        } else {

            $filepath = self::download_video($url);
            $file_id  = self::upload_video_to_txy($filepath);
            $video    = new LiveWemeVideo();
            $video['video_url']   = $url;
            $video['file_id']     = $file_id;
            $video['create_time'] = date('Y-m-d H:i:s', time());
            $video['relation_ids']     = serialize($live_id);
            $video['type']   =1;
            $tmp_data['0'] =   $w_data;        
            $video['data']   = serialize($tmp_data);
            $video['video_id']   = $weme_id;
            $video['status']   = 2;
            $video->save();
            LiveWeme::updateAll(['file_id'=>$file_id], 'id = '.$weme_id);
        }
    }

    /**
     * 腾讯云回调
     */
    public function actionVideoCallback()
    {
        $contents = file_get_contents('php://input');
        Record::record_data($contents);
    }

    /**
     * 根据视频地址下载视频到本地服务器
     */
    public static function download_video($url)
    {
        $header = array("Connection: Keep-Alive",
                        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                        "Pragma: no-cache", "Accept-Language: zh-Hans-CN,zh-Hans;q=0.8,en-US;q=0.5,en;q=0.3",
                        "User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:29.0) Gecko/20100101 Firefox/29.0");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');

        $content = curl_exec($ch);
        $curlinfo = curl_getinfo($ch);

        //关闭连接
        curl_close($ch);

        if ($curlinfo['http_code'] == 200) {


            $var = Yii::$app->redis->lpush("up","lisr");

            $exf = substr($url, strrpos($url, '.'));
            //存放图片的路径及图片名称  *****这里注意 你的文件夹是否有创建文件的权限 chomd -R 777 mywenjian

            $filename = Yii::$app->basePath.'/web/uploadFiles/temp/'.date("YmdHis") . uniqid() . $exf;
            $res = file_put_contents($filename, $content);//同样这里就可以改为$res = file_put_contents($filepath, $content);
            if($res)
            {
                return $filename;
            }else
            {
                return false;
            }
        }else
        {
            return false;
        }
    }

    public static function upload_video_to_txy($filepath)
    {
        ob_start();
        $vod = new \VodApi();
        $vod->Init(Yii::$app->params['API_SecretId'],Yii::$app->params['API_SecretKey'],
            \VodApi::USAGE_UPLOAD, "gz");
        $vod->SetConcurrentNum(10); //设置并发上传的分片数目，不调用此函数时默认并发上传数为6
        $vod->SetRetryTimes(10);    //设置每个分片可重传的次数，不调用此函数时默认值为5
        $package = array(
            'fileName' => $filepath,			//文件的绝对路径，包含文件名
            'dataSize' => 1024*1024,			//分片大小，单位Bytes。断点续传时，dataSize强制使用第一次上传时的值。
            'isTranscode' => 1,					//是否转码
            'isScreenshot' => 1,				//是否截图
            'isWatermark' => 1,					//是否添加水印
            //'notifyUrl' =>$_SERVER['SERVER_NAME'].'/'.C("resource_notify_url")     //已经不再生效
        );
        $vod->UploadVideo($package);
       
        if($vod->getFileId() == '-1'){

            upload_video_to_txy($filepath);
            // return false;
        }else{
            
            unlink($filepath);
            $fileId = $vod->getFileId();
            ob_end_clean();
            return $fileId;
        }
    }

}
