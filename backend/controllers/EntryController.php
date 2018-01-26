<?php

namespace backend\controllers;


use common\models\Entry;
use common\models\Live;
use common\models\News;
use yii\helpers\ArrayHelper;
use Yii;

class EntryController extends PublicBaseController
{
    
    
    public function actionCreate()
    {
    
        
        $news_id = isset($this->params['news_id']) ? $this->params['news_id'] : ''; //直播相关新闻ID
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : ''; //管理员ID
        $weight = isset($this->params['weight']) ? $this->params['weight'] : 0; //权重
        $new  =isset($this->params['new']) ? $this->params['new'] : 1; //移除
        $entry_type_id = isset($this->params['entry_type_id']) ? $this->params['entry_type_id'] : true; //入口类型
       //rint_r($news_id);
        //e;
        
            //获取对应入口记录的新闻
            $news = News::find()->where(['news_id' => $news_id])->one();
            if(!$news){
    
                $this->_errorData('1000','无此条直播新闻纪录');
                
            }
            
            //设置此新闻状态为快直播入口状态17
            $news->type=17;
            $news->top_status =1;
            //保存
            $news->save();
            if ($news->area_id) {
                $column_type = 1;
                $column_id = $news->area_id;
            }else{
                $column_type = 0;
                $column_id= $news->column_id;
            }
            
            //判定快直播入口是否存在
            $entry = Entry::ExistFastLiveEntry($column_type, $column_id);
            //删除栏目原入口记录
            if ($entry['entry']==true) {
                //获取老入口新闻对象
                $old_news = News::find()->where(['news_id' => $entry['entry_id']])->one();
                //还原news状态为普通快直播
                $old_news->type = 15;
                $old_news->top_status = 0;
                $old_news->save();
                //删除入口记录
                $update = Entry::find()->where(['news_id' => $entry['entry_id']])->one()->delete();
            }
         if($new) {
             //创建入口记录
             $model = new Entry();
             $model->news_id = $news_id;
             $model->entry_type_id = $entry_type_id;
             $model->operater_id = $admin_id;
             $model->weight = $weight;
             $return = $model->save();
             if ($return) {
                 $return = '设置快直播入口成功';
             }
         }else{
                 
                 $return = '删除快直直播入口成功';
             
         }
        
        
        
        return $this->_successData($return);
    
    }
    
    public function actionList()
    {
        $result = Entry::EntryList();
        
        if(!$result){
          return $this->_errorData(5000,'查询失败');
        }
    
        return $this->_successData($result);
        
    }
    
    public function actionEntryCr()
    {
        $column_id = isset($this->params['column_id']) ? $this->params['column_id'] : ''; //栏目ID
        $column_type = isset($this->params['column_type']) ? $this->params['column_type'] : ''; //栏目类型 0常规栏目，1本地栏目
       
       $result = Entry::ExistFastLiveEntry($column_type, $column_id);
        if (!$result) {
            return $this->_errorData(5000, '查询失败');
        }
        
        return $this->_successData($result);
        
    }
    
}
