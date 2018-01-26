<?php
/**
 * 公共方法
 *  @author ZhaoBo
 */
namespace common\service;
use Yii;
use common\models\User;
class Textcode{
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
}