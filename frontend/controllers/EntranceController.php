<?php
namespace frontend\controllers;

use common\models\Live;
use common\models\News;
use common\models\NewsColumnEntrance;

class EntranceController extends PublicBaseController{

    public function actionIndex(){
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';  // 0 栏目  1： 地区
        $eid  = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

        if($type == 0){
            $type = 'column_id';
        }else{
            $type = 'area_id';
        }

        $info = NewsColumnEntrance::find()->where([$type=>$eid,'status'=>1])
                ->select('title, subtitle, cover_image, link_url, link_type, weight, status')
                ->orderBy('weight DESC')->asArray()->all();
        foreach($info as &$entry){
            if($entry['link_type'] == 1){
                //类型是新闻
                $entry_info = News::find()->leftJoin('news_video as v', 'news.news_id = v.news_id')
                    ->where(['news.news_id'=>$entry['link_url'],'news.status'=>0])
                    ->select('news.type, news.cover_image,news.abstract, news.source_id, news.source_name, v.category')
                    ->asArray()->one();
                $entry['type']   	  	 = $entry_info['type'];
                $entry['category']    	 = $entry_info['category'];
                $entry['abstract'] 	  	 = $entry_info['abstract'];
                $entry['source_id']   	 = $entry_info['source_id'];
                $entry['source_name'] 	 = $entry_info['source_name'];
                $entry['news_share_img'] = $entry_info['cover_image'];
            }

            if($entry['link_type'] == 2){
                //类型是直播
                $entry_info = Live::find()->where(['live_id'=>$entry['link_url']])->select('image_url')->asArray()->one();
                $entry['news_share_img'] = $entry_info['image_url'];
            }

            if($entry['link_type'] == 1 || $entry['link_type'] == 2){
                $entry['news_id']  = $entry['link_url'];
                $entry['link_url'] = "";
            }elseif($entry['link_type'] == 3){
                $entry['news_id']  = "";
                $entry['link_url'] = $entry['link_url'];
            }
        }
        unset($entry);
        $this->_successData($info);
    }
}