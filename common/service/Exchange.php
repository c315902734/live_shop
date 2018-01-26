<?php
namespace common\service;
/*
*  md5签名，$array中务必包含 appSecret
*/
class Exchange{
    function sign($array){
        if(!empty($array['PHPSESSID'])){
            unset($array['PHPSESSID']);
        }
        if(!empty($array['sign'])){
            unset($array['sign']);
        }
        ksort($array);
//print_r($array);die;
        $string="";
        while (list($key, $val) = each($array)){
            $string = $string . $val ;
        }
//        print_r($string);die;
        return md5($string);
    }

    /*
    *  签名验证,通过签名验证的才能认为是合法的请求
    */
    function signVerify($appSecret,$array){
        $newarray=array();
        $newarray["appSecret"]=$appSecret;
        reset($array);
        while(list($key,$val) = each($array)){
            $array[$key] = urldecode($array[$key]);
            if($key != "sign" || $key != "PHPSESSID"){


                $encode = '';
                $encode = mb_detect_encoding($array[$key], array("ASCII","UTF-8","GB2312","GBK"));
//                \Think\Log::write($encode.$key);
//                iconv($array[$key], "UTF-8", $encode);
                iconv($encode, "UTF-8",$array[$key] );
//                \Think\Log::write(mb_detect_encoding($array[$key], array("ASCII","UTF-8","GB2312","GBK")).'后'.$key);
                $newarray[$key] = $array[$key];
            }
        }
//        ksort($newarray);
//        print_r($newarray);die;
        $sign = Exchange::sign($newarray);
        
        if($sign == $array["sign"]){
            return true;
        }else {
            return false;
        }
    }
    /*
    *  生成自动登录地址
    *  通过此方法生成的地址，可以让用户免登录，进入积分兑换商城
    */
    function buildCreditAutoLoginRequest($appKey,$appSecret,$uid,$credits,$currentUrl=''){
        $url = "http://www.duiba.com.cn/autoLogin/autologin?";
        $timestamp=time()*10000 . "";
        $array=array("uid"=>$uid,"credits"=>$credits,"appSecret"=>$appSecret,"appKey"=>$appKey,"timestamp"=>$timestamp);

        $redirect_is = '';
        $encode = mb_detect_encoding($currentUrl, array("ASCII","UTF-8","GB2312","GBK"));
        iconv($encode, "UTF-8",$currentUrl );

        if($currentUrl){
            $array['redirect'] = $currentUrl;
            $redirect_is = '&redirect='.urlencode($currentUrl);
        }
        $sign = Exchange::sign($array);
        $url = $url . "timestamp=".$timestamp."&uid=" . $uid . "&credits=" . $credits . "&appKey=" . $appKey . "&sign=" . $sign .$redirect_is;
        return $url;
    }
    /*
    *  生成订单查询请求地址
    *  orderNum 和 bizId 二选一，不填的项目请使用空字符串
    */
    function buildCreditOrderStatusRequest($appKey,$appSecret,$orderNum,$bizId){
        $url="http://www.duiba.com.cn/status/orderStatus?";
        $timestamp=time()*1000 . "";
        $array=array("orderNum"=>$orderNum,"bizId"=>$bizId,"appKey"=>$appKey,"appSecret"=>$appSecret,"timestamp"=>$timestamp);
        $sign=$this->sign($array);
        $url=$url . "orderNum=" . $orderNum . "&bizId=" . $bizId . "&appKey=" . $appKey . "&timestamp=" . $timestamp . "&sign=" . $sign ;
        return $url;
    }
    /*
    *  兑换订单审核请求
    *  有些兑换请求可能需要进行审核，开发者可以通过此API接口来进行批量审核，也可以通过兑吧后台界面来进行审核处理
    */
    function buildCreditAuditRequest($appKey,$appSecret,$passOrderNums,$rejectOrderNums){
        $url="http://www.duiba.com.cn/audit/apiAudit?";
        $timestamp=time()*1000 . "";
        $array=array("appKey"=>$appKey,"appSecret"=>$appSecret,"timestamp"=>$timestamp);
        if($passOrderNums !=null && !empty($passOrderNums)){
            $string=null;
            while(list($key,$val)=each($passOrderNums)){
                if($string == null){
                    $string=$val;
                }else{
                    $string= $string . "," . $val;
                }
            }
            $array["passOrderNums"]=$string;
        }
        if($rejectOrderNums !=null && !empty($rejectOrderNums)){
            $string=null;
            while(list($key,$val)=each($rejectOrderNums)){
                if($string == null){
                    $string=$val;
                }else{
                    $string= $string . "," . $val;
                }
            }
            $array["rejectOrderNums"]=$string;
        }
        $sign = $this->sign($array);
        $url=$url . "appKey=".$appKey."&passOrderNums=".$array["passOrderNums"]."&rejectOrderNums=".$array["rejectOrderNums"]."&sign=".$sign."&timestamp=".$timestamp;
        return $url;
    }
    /*
    *  积分消耗请求的解析方法
    *  当用户进行兑换时，兑吧会发起积分扣除请求，开发者收到请求后，可以通过此方法进行签名验证与解析，然后返回相应的格式
    *  返回格式为：
    *  {"status":"ok","message":"查询成功","data":{"bizId":"9381"}} 或者
    *  {"status":"fail","message":"","errorMessage":"余额不足"}
    */
    function parseCreditConsume($appKey,$appSecret,$request_array){
        if($request_array["appKey"] != $appKey){
            throw new Exception("appKey not match");
        }
        if($request_array["timestamp"] == null ){
            throw new Exception("timestamp can't be null");
        }
        $verify=$this->signVerify($appSecret,$request_array);
        if(!$verify){
            throw new Exception("sign verify fail");
        }
        $ret=array("appKey"=>$request_array["appKey"],"credits"=>$request_array["credits"],"timestamp"=>$request_array["timestamp"],"description"=>$request_array["description"],"orderNum"=>$request_array["orderNum"]);
        return $ret;
    }
    /*
    *  兑换订单的结果通知请求的解析方法
    *  当兑换订单成功时，兑吧会发送请求通知开发者，兑换订单的结果为成功或者失败，如果为失败，开发者需要将积分返还给用户
    */
    function parseCreditNotify($appKey,$appSecret,$request_array){
        if($request_array["appKey"] != $appKey){
            throw new Exception("appKey not match");
        }
        if($request_array["timestamp"] == null ){
            throw new Exception("timestamp can't be null");
        }
        $verify=$this->signVerify($appSecret,$request_array);
        if(!$verify){
            throw new Exception("sign verify fail");
        }
        $ret=array("success"=>$request_array["success"],"errorMessage"=>$request_array["errorMessage"],"bizId"=>$request_array["bizId"]);
        return $ret;
    }
}
?>
