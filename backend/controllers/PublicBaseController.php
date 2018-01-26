<?php
namespace backend\controllers;
/**
 * Created by PhpStorm.
 * User: jd
 * Date: 2016/11/18
 * Time: 17:13
 */
use common\models\AdminUser;
use common\models\User;
use common\models\User1;
use common\service\redis;
use OAuth2\Request;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use filsh\yii2\oauth2server\filters\auth\CompositeAuth;

class PublicBaseController extends Controller{

    public $params = array();
    public $token;
    public $user_infos;
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $verify = $this->signVerify();
        if(!$verify){
            $this->_errorData(0001, '签名错误');
        }
        $this->user_infos = $this->_getUserModel(true);
        $request = Request::createFromGlobals();
        $params  = $request->getAllQueryParameters();
        if(empty($params)){
            $params = $_REQUEST;
        }
        $this->params = $params;
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class' => CompositeAuth::className(),
//                 'authMethods' => [
//                     ['class' => HttpBearerAuth::className()],
//                     ['class' => QueryParamAuth::className(), 'tokenParam' => 'accessToken'],
//                 ]
            ],
            'exceptionFilter' => [
                'class' => ErrorToExceptionFilter::className()
            ],
        ]);
    }

    /**
     * 签名
     * @param $array
     * @return string
     */
    private function sign($array){
        if(!empty($array['sign'])){
            unset($array['sign']);
        }
        ksort($array);
        $string="";
        while (list($key, $val) = each($array)){
            $string .= $key.'='.$val.'&';
        }
        $string = rtrim($string, '&');
//        echo md5($string);exit();
        return md5($string);
    }

    /*
    *  签名验证,通过签名验证的才能认为是合法的请求
    */
    function signVerify(){
        $newarray=array();
        if(!isset($_REQUEST['timestamp']) || !isset($_REQUEST['app_key']) || !isset($_REQUEST['unique']) || !isset($_REQUEST['sign']) || empty($_REQUEST['timestamp'])|| empty($_REQUEST['app_key'])|| empty($_REQUEST['unique'])|| empty($_REQUEST['sign'])){
            $this->_errorData('0100', "签名验证参数异常");
        }
        $array['timestamp'] = isset($_REQUEST['timestamp']) ? $_REQUEST['timestamp'] : '';
        $array['app_key']   = isset($_REQUEST['app_key']) ? $_REQUEST['app_key'] : '';
        $array['unique']    = 'xinhuiwen,fighting!';
        $array['sign']      = isset($_REQUEST['sign']) ? $_REQUEST['sign'] : '';
        reset($array);
//        if(time() - $array['timestamp'] >= 600){
//            $this->_errorData(0001, '签名错误');
//        }
        while(list($key,$val) = each($array)){
//            $array[$key] = urldecode($array[$key]);
            if($key != "sign"){
                $encode = '';
                $encode = mb_detect_encoding($array[$key], array("ASCII","UTF-8","GB2312","GBK"));
//                iconv($array[$key], "UTF-8", $encode); 
                $newarray[$key] = $array[$key];
            }
        }
        $sign=$this->sign($newarray);
        if($sign == $array["sign"]){
            return true;
        }else {
            return false;
        }
    }
    
    protected function _successData($returnData, $msg = "查询成功")
    {
        $data = array('Success' => true,
            'ResultCode' => '0000',
            'ReturnData' => $returnData,
            'Message' => $msg
        );
        header('Content-Type:application/json; charset=utf-8');
        if(isset($this->params['is_show']) && $this->params['is_show'] == 1){
            header('Access-Control-Allow-Origin: *');
            header('Content-Type:application/json; charset=utf-8');
            $jsonp_header_start = '';
            $jsonp_header_end = '';
            if(isset($this->params['callback'])){
                if(!empty($this->params['callback'])){
                    $jsonp_header_start = $this->params['callback'].'(';
                    $jsonp_header_end = ')';
                }
            }
            exit($jsonp_header_start.json_encode($data).$jsonp_header_end);

        }
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

    /*
     * 验证手机号
    * */
    protected function _checkMobile($countries_regions, $mobile)
    {
    	
    	/* 如果是国内大陆手机号 */
    	if($countries_regions == ''){
    		if (preg_match("/^1[34578]\d{9}$/", $mobile)) {
    			return true;
    		} else {
    			return false;
    		}
    	}else{
    		if(is_numeric($mobile)){
    			return true;
    		}else{
    			return false;
    		}
    	}
    }
    
    /**
     * CMF密码加密方法
     * @param string $pw 要加密的字符串
     * @return string
     */
    function sp_password($pw,$authcode=''){
        //    if(empty($authcode)){
//        $authcode=C("AUTHCODE");
//    }
//	$result="###".md5(md5($authcode.$pw));
//	return $result;
        $result=md5(md5($pw));
        return $result;
    }

    public function curl_http($url, $post_data = '', $header=array(), $timeout=30, $SSL = FALSE){
    	$SSL = substr($url, 0, 8) == "https://" ? true : false;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($SSL) {
        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 信任任何证书
        	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 检查证书中是否设置域名
        }
//         $header[] = 'Content-Type:application/x-www-form-urlencoded';
//         $header[] = 'Accept-Charset: utf-8';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        if(!empty($post_data)){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
        $response = curl_exec($ch);

        if($error = curl_error($ch)){
            die($error);
        }

        curl_close($ch);
        //Yii::info($url.$post_data.'--response：'.$response, 'INFO');
//        \Think\Log::record($url.$post_data.'--response：'.$response, 'INFO');
        return json_decode($response, true);
    }

    protected function _checkToken($token)
    {
        try{
            $redis = Yii::$app->cache;
            return $redis->get(array($token));
        }catch(\Exception $e){
            return S($token);
        }
    }

    protected function _getUserData()
    {
        $this->token = $this->getToken();
        if(empty($this->token)) return true;
        $userData = $this->_checkToken($this->token);
//        $userData =array('mobile'=>'13220113068',"userId"=>'201614747844623579');
        if ($userData == false) {
            $this->_errorData('0005', '用户未登录');
        } else {
            return $userData;
        }
    }

    protected function _getUserModel($isArray = false)
    {
        $userData = $this->_getUserData();
        if(is_array($userData)){
            $user = AdminUser::find()->where(['admin_id'=>$userData['AdminId']])->asArray($isArray)->one();
            if (empty($user)) {
                $this->_errorData('404', '查找用户失败');
            }
            return $user;
        }
        return $userData;
    }

    protected function getToken(){
        $request = Yii::$app->request->headers;
        $authHeader = $request['authorization'];
        if ($authHeader !== null && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
    
    /**
     * 中文按照1个字计算，进行截取
     */
    static function strsub_utf8 ($str,$start_position,$length) {
    	$i = 0;
    	$count = 0;
    	$len = strlen ($str);
    	$end_position = $len;
    	while ($i < $len)
    	{
    		$chr = ord ($str[$i]);
    		$count++;
    		$i++;
    
    		if ($i >= $len)
    			break;
    
    			if ($chr & 0x80)
    			{
    				$chr <<= 1;
    				while ($chr & 0x80)
    				{
    					$i++;
    					$chr <<= 1;
    				}
    			}
    			if($count>=$length) {
    				$end_position = $i;
    				break;
    			}
    	}
    
    	return substr($str,$start_position,$end_position-$start_position);
    }

    /**
     * 把用户输入的文本转义（主要针对特殊符号和emoji表情）
     * @param $str
     * @return json
     */
    public function  userTextEncode($str){
        if(!is_string($str))return $str;
        if(!$str || $str=='undefined')return '';

        $text = json_encode($str);
        $text = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i",function($str){
            return addslashes($str[0]);
        },$text);
        return json_decode($text);
    }

    /**
     *解码上面的转义
     * @param $str
     * @return string
     */
    public function userTextDecode($str){
        $text = json_encode($str);
        $text = preg_replace_callback('/\\\\\\\\/i',function($str){
            return '\\';
        },$text);
        return json_decode($text);
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
    
    
}
