<?php

namespace frontend\controllers;

use common\models\InviteDownload;

class KdemejkzewrmhhController extends \yii\web\Controller
{
	public $layout=false; //重写这个属性就可以了
    public function actionIndex()
    {
    	echo '敬请期待！！！';exit;
        return $this->render('index');
    }

    public function actionGfdgxereppxkyd(){
        $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
        $data = array();
    	$this->getView()->title = "法制与新闻客户端下载页面";
        if($user_id){
            $data['user_id'] = $user_id;
        }
        if($this->check_wap()){
            if($this->is_ios()){
                return $this->render('download',$data);
            }else{
                return $this->render('download_a',$data);
            }
        }else{
            return $this->render('download_pc');
        }
    }
    
    
    function check_wap() {
    	if (isset($_SERVER['HTTP_VIA'])) {
    		return true;
    	}
    	if (isset($_SERVER['HTTP_X_NOKIA_CONNECTION_MODE'])) {
    		return true;
    	}
    	if (isset($_SERVER['HTTP_X_UP_CALLING_LINE_ID'])) {
    		return true;
    	}
    	if (strpos(strtoupper($_SERVER['HTTP_ACCEPT']), "VND.WAP.WML") > 0) {
    		// Check whether the browser/gateway says it accepts WML.
    		$br = "WML";
    	} else {
    		$browser = isset($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '';
    		if (empty($browser)) {
    			return true;
    		}
    		$mobile_os_list = array('Google Wireless Transcoder', 'Windows CE', 'WindowsCE', 'Symbian', 'Android', 'armv6l', 'armv5', 'Mobile', 'CentOS', 'mowser', 'AvantGo', 'Opera Mobi', 'J2ME/MIDP', 'Smartphone', 'Go.Web', 'Palm', 'iPAQ');
    
    		$mobile_token_list = array('Profile/MIDP', 'Configuration/CLDC-', '160×160', '176×220', '240×240', '240×320', '320×240', 'UP.Browser', 'UP.Link', 'SymbianOS', 'PalmOS', 'PocketPC', 'SonyEricsson', 'Nokia', 'BlackBerry', 'Vodafone', 'BenQ', 'Novarra-Vision', 'Iris', 'NetFront', 'HTC_', 'Xda_', 'SAMSUNG-SGH', 'Wapaka', 'DoCoMo', 'iPhone', 'iPod');
    
    		$found_mobile = $this->checkSubstrs($mobile_os_list, $browser) || $this->checkSubstrs($mobile_token_list, $browser);
    		if ($found_mobile) {
    			$br = "WML";
    		} else {
    			$br = "WWW";
    		}
    	}
    	if ($br == "WML") {
    		return true;
    	} else {
    		return false;
    	}
    }
    
    /**
     * 是否是移动设备
     * @return boolean
     */
    function isMobile(){ 
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
            return true;
        } 
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])){
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        } 
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])){
            $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
            ); 
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
                return true;
            } 
        } 
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])){ 
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))){
                return true;
            } 
        } 
        return false;
    }
    /**
     * 判断是否是IOS设备
     * @return boolean
     */
    public function is_ios(){
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
            return true;
        }else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
            return false;
        }else{
            return false;
        }
    }

    /**
     * 点击下载数 加1
     * type ios/android
     * */
    public function actionDownload_add(){
        $type    = isset($_REQUEST['type']) ? $_REQUEST['type'] : 0; //0 ios, 1 android
        $user_id = isset($_REQUEST["user_id"]) ? $_REQUEST['user_id'] : '';
        if(!$user_id){
            echo 1;
        }
        //点击数加 1
        $invite_download = new InviteDownload();
        $invite_download->user_id = $user_id;
        $invite_download->type = $type;
        $invite_download->create_time = date("Y-m-d H:i:s");
        $invite_download->save();
        echo 1;
    }
    
    /**
     * 判断手机访问， pc访问
     */
    protected function checkSubstrs($list, $str) {
    	$flag = false;
    	for ($i = 0; $i < count($list); $i++) {
    		if (strpos($str, $list[$i]) > 0) {
    			$flag = true;
    			break;
    		}
    	}
    	return $flag;
    }

}
