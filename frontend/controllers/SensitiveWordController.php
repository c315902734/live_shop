<?php
namespace frontend\controllers;

use Yii;


/**
 * Column controller
 */
class SensitiveWordController extends PublicBaseController
{

    /**
     * 敏感词过滤
     */
    public function actionIndex(){
        $str = isset($this->params['content'])?$this->params['content']:'';
        $redis = Yii::$app->cache;
        $sensitive_words = $redis->get(Yii::$app->params['environment'].'_sensitive_words');
        if($sensitive_words && count($sensitive_words) > 0){
            $words = array_column($sensitive_words, 'words');
            for($i=0;$i<count($words);$i++){
                $replace = '';
                if(strstr($str, $words[$i])){
                    for($j=1;$j<=strlen($words[$i])/3;$j++){
                        $replace .= '*';
                    }
                    $str = str_replace($words[$i], $replace, $str);
                }
            }
        }
        $this->_successData($str);
    }

}
