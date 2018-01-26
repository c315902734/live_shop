<?php
namespace backend\controllers;

use common\models\NewsColumnType;
use common\models\SpecialColumnType;
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
        $title       = isset($this->params['title']) ? $this->params['title'] : '';
        $subtitle    = isset($this->params['subtitle']) ? $this->params['subtitle'] : '';
        $keywords    = isset($this->params['keywords']) ? $this->params['keywords'] : '';
        $source_name = isset($this->params['source_name']) ? $this->params['source_name'] : '';
        $tags        = isset($this->params['tags']) ? $this->params['tags'] : '';
        $abstract    = isset($this->params['abstract']) ? $this->params['abstract'] : '';
        $content     = isset($this->params['content']) ? $this->params['content'] : '';
        $sensitive = '';
        $redis = Yii::$app->cache;
        $word_list = $redis->get(Yii::$app->params['environment'].'_sensitive_words');
        if(!$word_list && count($word_list) < 1){
            $this->_successData($sensitive);
        }
        if(!empty($content) || count($content) > 0){
            if(is_array($content)){
                for($i=0;$i<count($content);$i++){
                    foreach ($word_list as $key=>$value){
                        if(strstr($content[$i], $value['words'])){
                            $sensitive .= $value['words'].',';
                        }
                    }
                }
            }else{
                foreach ($word_list as $key=>$value){
                    if(strstr($content, $value['words'])){
                        $sensitive .= $value['words'].',';
                    }
                }
            }
        }
        if(!empty($abstract)){
            foreach ($word_list as $key=>$value){
                if(strstr($abstract, $value['words'])){
                    $sensitive .= $value['words'].',';
                }
            }
        }
        if(!empty($title)){
            foreach ($word_list as $key=>$value){
                if(strstr($title, $value['words'])){
                    $sensitive .= $value['words'].',';
                }
            }
        }
        if(!empty($subtitle)){
            foreach ($word_list as $key=>$value){
                if(strstr($subtitle, $value['words'])){
                    $sensitive .= $value['words'].',';
                }
            }
        }
        if(!empty($keywords)){
            foreach ($word_list as $key=>$value){
                if(strstr($keywords, $value['words'])){
                    $sensitive .= $value['words'].',';
                }
            }
        }
        if(!empty($source_name)){
            foreach ($word_list as $key=>$value){
                if(strstr($source_name, $value['words'])){
                    $sensitive .= $value['words'].',';
                }
            }
        }
        if(!empty($tags)){
            foreach ($word_list as $key=>$value){
                if(strstr($tags, $value['words'])){
                    $sensitive .= $value['words'].',';
                }
            }
        }
        $news_arr = explode(',', $sensitive);
        $news_arrs = array_unique($news_arr);
        $sensitives = implode(',', $news_arrs);
        $this->_successData($sensitives);
    }

}
