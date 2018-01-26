<?php
require_once QCLOUDAPI_ROOT_PATH . '/Module/Base.php';
/**
 * QcloudApi_Module_Live
 * 直播模块类
 */
class QcloudApi_Module_Fcgi extends QcloudApi_Module_Base
{
    /**
     * $_serverHost
     * 接口域名
     * @var string
     */
    protected $_serverHost = 'fcgi.video.qcloud.com/common_access';
    
    
    /**
     * dispatchRequest
     * 发起接口请求
     * @param  array $arguments 接口参数
     * @return
     */
    public function Send($paramArray, $https=false)
    {
    	$request['query'] = http_build_query($paramArray);
        $request['query'] = str_replace('+','%20',$request['query']);
    
        $url = $this->_serverHost.'?'.$request['query'];
    
        
        if($https)
        {
            $url = 'https://'.$url;
        }
        else
        {
            $url = 'http://'.$url;
        }
        
        $response = self::curl_http($url);
    
    	return $response;
    }
    
    
    public static function curl_http($url, $post_data = '', $header=array(), $timeout=30){
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
		return $response;
    	if($error = curl_error($ch)){
    		die($error);
    	}
    
    	curl_close($ch);
    	Yii::info($url.$post_data.'--response：'.$response, 'INFO');
    	//        \Think\Log::record($url.$post_data.'--response：'.$response, 'INFO');
    	return json_decode($response, true);
    }
}
