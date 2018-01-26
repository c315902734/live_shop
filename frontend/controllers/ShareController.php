<?php
namespace frontend\controllers;
use common\service\PublicFunction;
use yii\web\Controller;

/**
 * Created by PhpStorm.
 * User: jd
 * Date: 2016/12/21
 * Time: 10:30
 */
class ShareController extends Controller{

    /**
     * 分享
     */
    public function actionIndex(){
        $news_id  = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';  //新闻id
        $type     = isset($_REQUEST['type']) ? $_REQUEST['type'] : ''; //新闻类型
        $token    = isset($_REQUEST['token']) ? $_REQUEST['token'] : '';
        $category = isset($_REQUEST['category']) ? $_REQUEST['category'] : ''; //类型 images，text，video
        $timestamp = time();
        $app_key   = 'hello';
        $unique    = 'xinhuiwen,fighting!';
        $sign      = $this->sign(array('timestamp'=>$timestamp,'app_key'=>$app_key,'unique'=>$unique));
        $is_pc     = isset($_REQUEST['is_pc']) ? $_REQUEST['is_pc'] : '';  //pc端orM端
        $na_id     = isset($_REQUEST['na_id']) ? $_REQUEST['na_id'] : '';  //一级栏目id
        $nav_name  = isset($_REQUEST['nav_name']) ? $_REQUEST['nav_name'] : '';  //一级栏目名称
        $to_nav_name = isset($_REQUEST['to_nav_name']) ? $_REQUEST['to_nav_name'] : '';  //二级栏目名称
        $nav_type  = isset($_REQUEST['nav_type']) ? $_REQUEST['nav_type'] : '';  //栏目类型 1普通栏目 2地区栏目
        $stype_id  = isset($_REQUEST['stype_id']) ? $_REQUEST['stype_id'] : '';  //二级栏目id
        $url       = 'timestamp='.$timestamp.'&app_key='.$app_key.'&sign='.$sign;
        $url      .= '&id='.$news_id.'&news_id='.$news_id;
        if($token){
            $url .= '&token='.$token;
        }
        if($is_pc == 'pc'){
            if($category == 'images'){
                $action = 'NewsPhotos';
                $url .= '&id='.$news_id;
            }elseif ($category == 'text'){
                $action = 'NewsText';
                $url .= '&hotid='.$news_id;
            }elseif ($category == 'video'){
                $action = 'NewsVideo';
                $url .= '&video_id='.$news_id;
            }elseif($category == 'new_video'){
                $curl = \Yii::$app->params['video_host'].'videos';
                if($news_id){
                    $curl .= '/'.$news_id;
                }
                echo "<script language='javascript' type='text/javascript'>";
                echo "window.location.href='$curl'";
                echo "</script>";die;
            }elseif($category == 'new_news'){
                $curl = \Yii::$app->params['video_host'].'news';
                if($news_id){
                    $curl .= '/'.$news_id;
                }
                echo "<script language='javascript' type='text/javascript'>";
                echo "window.location.href='$curl'";
                echo "</script>";die;
            }else if($category == 'new_photos'){
                $curl = \Yii::$app->params['video_host'].'photos';
                if($news_id){
                    $curl .= '/'.$news_id;
                }
                echo "<script language='javascript' type='text/javascript'>";
                echo "window.location.href='$curl'";
                echo "</script>";die;
            }else{
                $action = 'index';
            }
            $curl = \Yii::$app->params['pc_host'].'index.php?g=Details&m=NewsDetails&a='.$action.'&'.$url;
            if($category == 'special'){
                $curl = \Yii::$app->params['pc_host'].'index.php?g=SubscriptionNum&m=SubscriptionNum&a=Topics&id='.$news_id;
            }
            if($na_id){
                $curl .= '&na_id='.$na_id;
            }
            if($nav_name){
                $curl .= '&nav_name='.$nav_name;
            }
            if($nav_type){
                $curl .= '&nav_type='.$nav_type;
            }
            if($to_nav_name){
                $curl .= '&to_nav_type='.$to_nav_name;
            }
            if($stype_id){
                $curl .= '&stype_id='.$stype_id;
            }
        }else if($is_pc == 'm'){
            $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'index';
            if($action == 'index'){
//                var_dump($_REQUEST);exit();
                if($nav_type == 1 && $na_id == 1){
                    $curl = \Yii::$app->params['m3_host'];
                }elseif($nav_type == 1 && !$stype_id){
                    $curl = \Yii::$app->params['m3_host'].'list/'.$na_id.'/info?type=1&id='.$na_id.'&cyname='.$nav_name;
                }else if($nav_type == 1 && !empty($stype_id)){
                    $curl = \Yii::$app->params['m3_host'].'list/'.$stype_id.'/info?type=1&c_id='.$stype_id.'&name='.$to_nav_name.'&id='.$na_id;
                }else if($nav_type == 2 && $na_id == 1 && !$stype_id){
                    $curl = \Yii::$app->params['m3_host'].'list/'.$na_id.'/info?type=2&id='.$na_id.'&cyname='.$nav_name;
                }else if($nav_type == 2 && $na_id == 1 && !empty($stype_id)){
                    $curl = \Yii::$app->params['m3_host'].'list/'.$stype_id.'/info?type=2&c_id='.$stype_id.'&cyname='.$to_nav_name.'&id='.$stype_id;
                }
                if($nav_name == "直播" && $to_nav_name == "直播"){
                    $curl = \Yii::$app->params['m3_host'].'livelist?category=1';
                }else if($nav_name == "直播" && $to_nav_name == "VR"){
                    $curl = \Yii::$app->params['m3_host'].'livelist?category=2';
                }else if($nav_name == "直播"){
                    $curl = \Yii::$app->params['m3_host'].'livelist?category=1';
                }
            }elseif (strtolower($action) == 'livedetail'){
                $curl = \Yii::$app->params['m2_host'].'live?id='.$news_id;
            }elseif(strtolower($action) == 'newsphotos'){
                $curl = \Yii::$app->params['m3_host'].'atlas/'.$news_id.'/detail?mark='.$news_id;
            }elseif(strtolower($action) == 'newstext'){
                $curl = \Yii::$app->params['m3_host'].'news/'.$news_id.'/detail?mark='.$news_id;
            }elseif(strtolower($action) == 'newsvideo'){
                $curl = \Yii::$app->params['m3_host'].'video/'.$news_id.'/detail?mark='.$news_id;
            }elseif(strtolower($action) == 'topics'){
                $curl = \Yii::$app->params['m3_host'].'special/'.$news_id.'/detail?mark='.$news_id;
            }else{
                $curl = \Yii::$app->params['m_host'];
            }

        }
        echo "<script language='javascript' type='text/javascript'>";
        echo "window.location.href='$curl'";
        echo "</script>";die;

//        $this->redirect($curl);
//        $this->_successData($curl);
//        header("location:$curl");
    }

    function sign($array){
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
}