<?php

namespace frontend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use common\models\LiveWeme;
use common\models\Area;
use common\models\Company;
use yii\log\FileTarget;

class WemeController extends Controller
{

     /**
     * 视频数据接收(对外合作)
     */

    public function actionReceiveWeme()
    {
        ignore_user_abort();
        $entityBody      = file_get_contents('php://input');
        $entityBody      = json_decode($entityBody,true);
        $time            = microtime(true);  
        $log             = new FileTarget();  
        $log->logFile    = Yii::$app->getRuntimePath() . '/logs/songlin.log';  
        $log->messages[] = ['数据'.PHP_EOL ,1, json_encode($entityBody),$time];  
        $log->export();  

        $accountID   =  isset($entityBody['accountID']) ?$entityBody['accountID'] : '';
        $mirrtalkID  =  isset($entityBody['mirrtalkID']) ?$entityBody['mirrtalkID'] : '';
        $aid         =  isset($entityBody['aid']) ?$entityBody['aid'] : '';
        $au          =  isset($entityBody['au']) ?$entityBody['au'] : '';
        $mediatype   =  isset($entityBody['mediatype']) ?$entityBody['mediatype'] : '';
        $pt          =  isset($entityBody['pt']) ?$entityBody['pt'] : '';
        $url         =  isset($entityBody['url']) ?$entityBody['url'] : '';
        $fr          =  isset($entityBody['fr']) ?$entityBody['fr'] : '';
        $at          =  isset($entityBody['at']) ?$entityBody['at'] : '';
        $mTime       =  isset($entityBody['mTime']) ?$entityBody['mTime'] : '';
        $pw          =  isset($entityBody['pw']) ?$entityBody['pw'] : '';
        $ph          =  isset($entityBody['ph']) ?$entityBody['ph'] : '';
        $sz          =  isset($entityBody['sz']) ?$entityBody['sz'] : '';
        $videoLength =  isset($entityBody['videoLength']) ?$entityBody['videoLength'] : '';
        $videoTime   =  isset($entityBody['videoTime']) ?$entityBody['videoTime'] : '';
        $gpslist     =  isset($entityBody['gpslist']) ?serialize($entityBody['gpslist']): '';
        $T           =  isset($entityBody['T']) ?$entityBody['T'] : '';
        $N           =  isset($entityBody['N']) ?$entityBody['N'] : '';
        $E           =  isset($entityBody['E']) ?$entityBody['E'] : '';
        $V           =  isset($entityBody['V']) ?$entityBody['V'] : '';
        $A           =  isset($entityBody['A']) ?$entityBody['A'] : '';
        $D           =  isset($entityBody['D']) ?$entityBody['D'] : '';

        $wemeModel = new LiveWeme();
        $wemeModel->accountID     = $accountID;
        $wemeModel->mirrtalkID    = $mirrtalkID;
        $wemeModel->aid           = $aid;
        $wemeModel->au            = $au;
        $wemeModel->mediatype     = $mediatype;
        $wemeModel->pt            = $pt;
        $wemeModel->url           = $url;
        $wemeModel->fr            = $fr;
        $wemeModel->at            = $at;
        $wemeModel->mtime         = $mTime;
        $wemeModel->pw            = $pw;
        $wemeModel->ph            = $ph;
        $wemeModel->sz            = $sz;
        $wemeModel->videoLength   = $videoLength;
        $wemeModel->videoTime     = $videoTime;
        $wemeModel->gpslist       = $gpslist;
        $wemeModel->T             = $T;
        $wemeModel->N             = $N;
        $wemeModel->E             = $E;
        $wemeModel->V             = $V;
        $wemeModel->A             = $A;
        $wemeModel->D             = $D;
        
        if($this->is_serialized($gpslist)){
        $g_arr= unserialize($gpslist);
            if($this->arrayLevel($g_arr)>1){
                $N=$g_arr[0]["N"];
                if($N!=0){
                        $E                     =$g_arr[0]["E"];
                        $data                  =$this->_object2array($this->_actionGeo($N.",".$E));
                        $city                  =$data['city'];
                        $city                  = str_replace('市', '', $city);
                        $wemeModel->area_name  = $city;
                        $areaModel             = new Area();
                        $area_list             =$areaModel::find()->select(['area_id'])->where(['name'=>$city])->asArray()->one();
                        $companyModel          = new Company();
                        $company_list          =$companyModel::find()->select(['company_id'])->where(['city'=>$city])->asArray()->one();
                        $wemeModel->company_id = $company_list['company_id'];
                        $wemeModel->area_id    = $area_list['area_id'];
                }
            }
        }
        $ret=$wemeModel->save();
        ($ret) ? $this->_successData($accountID, "插入成功") : $this->_errorData("0059", "插入失败");
    }
    
     protected function _actionGeo($location){
        $batch = isset($_REQUEST['batch']) ? $_REQUEST['batch'] : 'json';
        $pois = isset($_REQUEST['pois']) ? $_REQUEST['pois'] : '0';
        if (!$location) {
            $this->_errorData('0001', '参数错误');
        }
        //百度服务端应用AK码
        $ak ='iFBKSFXQWUWLLNIoHFp13DMPEnjt6Cd1';
        // 百度逆向地理编码接口 http://api.map.baidu.com/geocoder/v2/?callback=renderReverse&location=39.934,116.329&output=json&pois=1&ak=您的ak GET请求
        $url = 'http://api.map.baidu.com/geocoder/v2/?callback=renderReverse&location='.$location.'&output='.$batch.'&pois='.$pois.'&ak='.$ak;
        //$curl = new CURLFile();
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HEADER,0);
        $data =curl_exec($ch);
        curl_close($ch);
        //$result=[];
        if($data){
            preg_match('/\(.*\)/', $data, $result);
        }
        
        if($result[0]){
           $trim =  trim($result[0], '(');
            $trim = trim($trim, ')');
        }
        
       $trim =json_decode($trim);
        if (isset($trim->result)&& isset($trim->result->addressComponent)) {
            return $trim->result->addressComponent;
        } else {
            return 0;
        }
    }
    
    
        protected function _object2array($object) {
        if (is_object($object)) {
        foreach ($object as $key => $value) {
          $array[$key] = $value;
        }
        }
        else {
        $array = $object;
        }
        return $array;
        }
        
        function is_serialized( $data ) {
         $data = trim( $data );
         if ( 'N;' == $data )
             return true;
         if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
             return false;
         switch ( $badions[1] ) {
             case 'a' :
             case 'O' :
             case 's' :
                 if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
                     return true;
                 break;
             case 'b' :
             case 'i' :
             case 'd' :
                 if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
                     return true;
                 break;
         }
         return false;
     }
     
     function arrayLevel($arr){
        $al = array(0);
        function aL($arr,&$al,$level=0){
            if(is_array($arr)){
                $level++;
                $al[] = $level;
                foreach($arr as $v){
                    aL($v,$al,$level);
                }
            }
        }
        aL($arr,$al);
        return max($al);
    }

    protected function _successData($returnData, $msg = "查询成功")
    {
        $data = array('Success' => true,
            'ResultCode' => '0000',
            'ReturnData' => $returnData,
            'Message' => $msg
        );
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
        //header('Access-Control-Allow-Origin: *');
//         header('Content-Type:application/json; charset=utf-8');
//         $jsonp_header_start = '';
//         $jsonp_header_end = '';
//         if(isset($this->params['callback'])){
//         	if(!empty($this->params['callback'])){
//         		$jsonp_header_start = $this->params['callback'].'(';
//         		$jsonp_header_end = ')';
//         	}
//         }
//         exit($jsonp_header_start.json_encode($data).$jsonp_header_end);
    }

    protected function _errorData($code, $message)
    {
        $ReturnData = NULL;
        $data = array('Success' => false,
            'ResultCode' => $code . "",
            'ReturnData' => $ReturnData,
            'Message' => $message . ''
        );
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
//         header('Access-Control-Allow-Origin: *');
//         header('Content-Type:application/json; charset=utf-8');
//         $jsonp_header_start = '';
//         $jsonp_header_end = '';
//         if(isset($this->params['callback'])){
//         	if(!empty($this->params['callback'])){
//         		$jsonp_header_start = $this->params['callback'].'(';
//         		$jsonp_header_end = ')';
//         	}
//         }
//         exit($jsonp_header_start.json_encode($data).$jsonp_header_end);
    }

    

}