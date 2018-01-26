<?php
namespace backend\controllers;

use common\models\Live;
use common\models\News;
use common\models\NewsRecommend;
use common\models\NewsSource;
use common\models\NewsVideo;
use common\service\Jssdk;
use common\service\Record;
use frontend\controllers\NewslinkController;
use frontend\models\PasswordResetRequestForm;
use Yii;
use common\models\LoginForm;
use yii\db\Query;

/**
 * News controller
 */
class NewsController extends PublicBaseController
{

    /**
     * 发布、草稿、定时
     * 定时保存
     * 修改
     * 等处理 新闻 操作
     * @return string
     */
    public function actionCheckNews()
    {
        $video_info = new NewsVideo();
        $news = new News();
        $news_video = new NewsVideo();
        $news_id      = isset($this->params['news_id']) ? $this->params['news_id'] : '';
        if(!$news_id){
            $data = new News();
        }
        $save_type    = $this->params['save_type']; //新闻发布类型 0发布新闻，1存为草稿，2定时发布
        $news_type    = $this->params['news_type']; //新闻类型  2 轮播图  4 视频 5 图集 7图文
        $auto_save    = isset($this->params['auto_save']) ? $this->params['auto_save'] : 0; //是否自动保存  0 否，1 是
        $column_type  = isset($this->params['column_type'])  ? $this->params['column_type']  : ''; //栏目类型 0常规栏目，1本地栏目
        $column_id    = isset($this->params['column_id'])    ? $this->params['column_id']    : ''; //栏目ID
        $category     = isset($this->params['category'])     ? $this->params['category']     :  0; //是否VR 1是，0否
        $video_info   = isset($this->params['video_info'])   ? $this->params['video_info']   : ''; //视频信息
        $data['tags']     = isset($this->params['tags'])         ? $this->params['tags']     : ''; //标签
        $data['title']    = isset($this->params['title'])        ? $this->params['title']    : ''; //标题
        $data['weight']   = isset($this->params['weight'])       ? $this->params['weight']   :  0; //权重
        $data['app_pub']  = $this->params['app_pub']      ? $this->params['app_pub']  :  0; //发布渠道 app 是1，否0
        $data['web_pub']  = $this->params['web_pub']      ? $this->params['web_pub']  :  0; //发布渠道 门户 是1，否0
        $data['keywords'] = isset($this->params['keywords'])     ? $this->params['keywords'] : ''; //关键字
        $data['subtitle'] = isset($this->params['subtitle'])     ? $this->params['subtitle'] : ''; //副标
        $data['abstract'] = isset($this->params['abstract'])     ? $this->params['abstract'] : ''; //新闻摘要
        $data['vote_id']  = isset($this->params['vote_id'])      ? $this->params['vote_id']      :  0; //投票ID
        $data['external_link']   = isset($this->params['external_link']) ? $this->params['external_link'] : ''; //第三方外链
        $data['type_id']         = isset($this->params['type_id'])      ? $this->params['type_id']      :  0; //栏目类型ID或分栏ID
        $data['top_status']      = isset($this->params['top_status'])   ? $this->params['top_status']   :  0; //是否置顶 1是，0否
        $data['source_name']     = isset($this->params['source_name'])  ? $this->params['source_name']  : ''; //新闻来源
        $data['cover_image']     = isset($this->params['cover_image'])  ? $this->params['cover_image']  : ''; //封面图片
        $data['refresh_time']    = isset($this->params['refresh_time']) ? $this->params['refresh_time'] : ''; //发布时间
        $data['special_news_id'] = (isset($this->params['special_id']) && !empty($this->params['special_id']))  ? $this->params['special_id']   :  0; //专题ID
        $data['content'] = isset($this->params['content']) ? $this->params['content'] : '';
        $data['is_watermark']    = isset($this->params['is_watermark']) ? $this->params['is_watermark'] :  0; //是否添加图片水印 1是，0否
        
        $data['status']  = $save_type;

        if(empty($news_type) || !isset($save_type)){
            $this->_errorData("0101", "参数错误");
        }

        if (!preg_match('/^(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])$/', $data['weight'])) {
            $data['weight'] = 0;
        }

        if(empty($data['vote_id'])){
            $data['vote_id'] = 0;
        }
        if(!empty($data['refresh_time'])){
            $data['refresh_time'] = strtotime($data['refresh_time']);
        }
        //栏目类型
        if($column_type == 0){
            $data['column_id'] = $column_id;
            $data['area_id']   = 0;
        }else{
            $data['area_id']   = $column_id;
            $data['column_id'] = 0;
        }

        if(empty($data['title'])){
            $data['title'] = '无标题'.time().$this->getRange();
        }

        $reference  = isset($this->params['reference']) ? $this->params['reference'] :  ''; //内部链接ID
        //内部链接 ID \ type  （type 1 引用，直接记录。 type 2 复制 将内部ID信息复制到当前新闻）
        $type = '';
        if(!empty($reference)) {
            $id   = substr($reference, 3);
            $type = substr($reference, strrpos($reference, '=') + 1);
            $data['reference_id']   = substr($id, 0, strpos($id, '&'));
            $data['reference_type'] = $type;
        }
//        if($news_type == 5 && !empty($reference)){
//            $data['content'] = json_encode($this->setWatermark_img($news_id, $data['is_watermark'], $data['content'])); //新闻正文
//        }else{
//            $data['content'] = json_encode($this->setWatermark($news_id, $data['is_watermark'], $data['content'])); //新闻正文
//        }
        $data['content'] = json_encode($data['content']); //新闻正文
        //关联新闻
        $relation = isset($this->params['relation']) ? $this->params['relation']  : ''; //关联新闻
//        $relation = json_decode($relation);
        $recommendObj = new NewsRecommend();
        if($news_id){
            $recommendObj->deleteAll(['news_id'=>$news_id]);
        }

        //新闻来源
        if(!empty($data['source_name'])){
            $source_item = NewsSource::find()->where(array('name' => $data['source_name']))->asArray()->one();
            if(empty($source_item)){
                $news_source = new NewsSource();
                $news_source['name']        = $data['source_name'];
                $news_source['create_time'] = date('Y-m-d H:i:s', time());
                $news_source['creator_id']  = isset($this->params['admin_id']) ? $this->params['admin_id'] : 0;
                $add_source  = $news_source->save(); //查看结果
                $data['source_id'] = $add_source;
            }else{
                $data['source_id'] = $source_item['source_id'];
            }
        }

        //新闻类型
        if($data['cover_image']){
            $data['type'] = $news_type ? $news_type : 7;
        }else{
            if($news_type == 7){
                $data['type'] = 6;
            }else{
                $data['type'] = $news_type ? $news_type : 6;
            }
        }


        //处理 直播ID
        $live_id     = isset($this->params['live_id']) ? $this->params['live_id'] : 0; //直播 ID
        $from_status = 0; //直播ID 来源于创建
        if(strpos($live_id,'&')) {
            //创建
            $role_live_id = substr($live_id, 3, strpos($live_id, '&') - 3); //直播ID
        }else{
            //编辑
            $role_live_id = $live_id;
            $from_status  = 1; //直播ID 来源于编辑
        }
        if(!empty($role_live_id)){
            //查看 直播类型
            $live_info = Live::find()->where(['live_id'=>$role_live_id])->asArray()->one();
            if($live_info['category'] == 1){
                $data['type'] = 9;
            }else if($live_info['category'] == 2){
                $data['type'] = 10;
            }else if($live_info['category'] == 3){
                $data['type'] = 11;
            }else if($live_info['category'] == 4){
                $data['type'] = 12;
            }else if($live_info['category'] == 5){
                $data['type'] = 13;
            }else if($live_info['category'] == 6){
                $data['type'] = 14;
            }
            if($from_status == 0) { //创建新闻 关联 直播ID
                $data['live_id']     = $role_live_id;
                $data['live_status'] = 1;
            }
        }


        //外链接处理
        $outer_url_ishot = isset($this->params['outer_url_ishot']) ? $this->params['outer_url_ishot'] : 0; //是否设置新闻链接 1是，0否
        $data['outer_url'] = isset($this->params['outer_url']) ? $this->params['outer_url'] : ''; //原文链接
        if($outer_url_ishot == '1'){
            if(empty($data['outer_url'])){
                return $this->_errorData('0110',"无外链地址！");
            }
            $data['outer_url_ishot'] = '1';
        }else{
            $data['outer_url_ishot'] = '0';
        }

        //非自动保存 且 为引用 判断 被引用新闻ID是否正常 或 是否为再引用  不正常 返回报错
        if($auto_save == 0 && $type == 1){
            $news_info_1 = News::find()
                ->where(['news_id'=>$data['reference_id']])
                ->asArray()->one();
            if(!$news_info_1){
                $this->_errorData(0114, "被引用新闻不存在！");
            }
            if($news_info_1['reference_type'] == 1){
                $this->_errorData(0115, "被引用新闻 为再次引用新闻，请重新填写！");
            }
        }

        $redis = Yii::$app->cache;
        if($type == 2){ //复制
            //查看被复制 新闻详情
            $news_info = News::find()
                ->where(['news_id'=>$data['reference_id']])
                ->asArray()->one();

//            if(!$news_info && $save_type == 0){
//                //被复制 新闻ID异常 处理
//                $this->_errorData(0102,"此新闻信息不存在，请检查ID");
//            }
            if($news_info) {
                $news_info['column_id'] = $data['column_id'];
                $news_info['area_id'] = $data['area_id'];
                $news_info['app_pub'] = $data['app_pub'];
                $news_info['web_pub'] = $data['web_pub'];
                $news_info['creator_id'] = isset($this->params['admin_id']) ? $this->params['admin_id'] : 0;
                if(!$news_info['status']){
                    $news_info['status'] = 0;
                }
            }
            if($news_id){
                if(!empty($relation)){
                    foreach($relation as $list){
                        $datalist = new NewsRecommend();
                        if(!empty($list)){
                            $datalist['news_id']      = isset($data['news_id'])  ? $data['news_id'] : $news_id;
                            $datalist['recommend_id'] = substr($list['url'],3);
                            $datalist['weight']       = $list['weight'];
                            $datalist->save();
                        }
                    }
                }

                //查看 原新闻视频信息
                $before_video = NewsVideo::find()->where(['news_id'=>$news_id])->asArray()->one();
                //不复制 原新闻
                //判断新闻类型 是否改动  定时时间是否改动
                //查看当前新闻详情
                $now_news = News::find()
                    ->where(['news_id'=>$news_id])
                    ->asArray()->one();
                if(!$now_news){
                    $this->_errorData(0103,"当前新闻信息不存在，请检查ID");
                }

                $data['news_id']     = $news_id;
                $data['create_time'] = $now_news['create_time'];
                $data['refresh_time']   = $data['refresh_time'] ? $data['refresh_time'] : $now_news['refresh_time'];
                $data['update_time']    = time();

                if($now_news['status'] == 0){
                    //修改 发布新闻  （不需存缓存）
                    $data['status']  = 0;
//                    $data['news_id'] = $news_id;
//                    $data['special_news_id'] = $data['special_news_id'];
                    if($data['special_news_id']){
                        $data['type_id'] = $data['type_id'];
                    }
                    News::updateAll($data,['news_id'=>$news_id]);
                    //判断 是否需要 缓存 栏目热门新闻
//                    $redis_column = $this->getOneNew($data['column_id'],$data['area_id']);
//                    if($redis_column['news_id'] != $data['news_id']){
//                        //更新 栏目redis
//                        $redis_columns = $this->getOneNews($data['column_id'],$data['area_id']);
//                        if(!$redis_columns) {
//                            $redis->set(Yii::$app->params['environment'] . "_hotnews_" . $column_type . '_' . $column_id,$redis_columns);
//                        }
//                    }

//                    $news->where(['news_id'=>$news_id])->save($news_info);
                    //若 原视频ID和新闻ID 和目前不同 进行更新数据
                    if($news_type == 4 && $before_video['file_id'] && $before_video['file_id'] != $video_info[0]['file_id']){ //视频类型新闻
                        $res_video = $this->checkVideo($news_id,$category,$video_info);
                        if(!$res_video){
                            $this->_errorData(0104,"此视频信息不存在，请检查ID");
                        }
                    }

                    if($auto_save != 1) {
                        //生成 静态
                        $this->add_static($news_type, $data);

                        // 根据实际 更新 列表
                        $redis = Yii::$app->cache;
                        if ($column_type == 0) {
                            $is_area = 0;
                        } else if ($column_type == 1) {
                            $is_area = 1;
                        }
                        $column_ids = $column_id;
                        if ($data['special_news_id'] != 0) {
                            $name = Yii::$app->params['environment'] . "_special_list_" . $data['special_news_id'] . '_update';
                            $column_list = Yii::$app->params['environment'] . '_new_list_' . $is_area . '_' . $column_ids . '_update';
                            $redis->set($column_list, time());
                        } else {
                            $name = Yii::$app->params['environment'] . '_new_list_' . $is_area . '_' . $column_ids . '_update';
                        }
                        $redis->set($name, time());
                    }

                    $res_data['news_id'] = (String)$news_id;
                    $this->_successData($res_data);
                }else{
                    //更改类型或定时发布时间  需删除记录缓存 并 更新数据
                    if($now_news['status'] != $save_type || $data['refresh_time'] != $now_news['refresh_time']){
                        $data['status'] = $save_type;
                        $redis = Yii::$app->cache;
                        $redis_info = $redis->get('news_id-'.$news_id);
                        if($redis_info){
                            $redis->delete('news_id-'.$news_id);
                        }
                        if($save_type == 0){ //发布 更新数据 清缓存
                            $data['create_time']  = date("Y-m-d H:i:s");
                            $data['refresh_time'] = time();
                            $data['news_id'] = $news_id;

//                            $data['special_news_id'] = $data['special_news_id'];
                            if($news_info && $data['special_news_id']){
                                $news_info['type_id'] = $data['type_id'];
                            }
                            News::updateAll($data,['news_id'=>$news_id]);
                            if($news_type == 4 && $before_video['file_id'] && $before_video['file_id'] != $video_info[0]['file_id']){ //视频类型新闻
                                $news_video->deleteAll(['news_id'=>$news_id]);
                                $news_video['category']    = $category;
                                $news_video['news_id']     = $news_id;
                                $news_video['update_time'] = date("Y-m-d H:i:s");
                                if($video_info) {
                                    $news_video['duration'] = $video_info[0]['duration'];
                                    $news_video['file_name'] = $video_info[0]['file_name'];
                                    $news_video['file_id'] = $video_info[0]['file_id'];
                                    $news_video['height'] = $video_info[0]['height'];
                                    $news_video['width'] = $video_info[0]['width'];
                                    $news_video['size'] = $video_info[0]['size'];
                                    $news_video['thumbnail_url'] = $video_info[0]['image_url'];
                                    $news_video['video_url'] = $video_info[0]['url'];

                                    $news_video->save();
                                }
                            }

                            if($auto_save != 1) {
                                //生成 静态
                                $this->add_static($news_type, $data);

                                // 根据实际 更新 列表
                                $redis = Yii::$app->cache;
                                if ($column_type == 0) {
                                    $is_area = 0;
                                } else if ($column_type == 1) {
                                    $is_area = 1;
                                }
                                $column_ids = $column_id;
                                if ($data['special_news_id'] != 0) {
                                    $name = Yii::$app->params['environment'] . "_special_list_" . $data['special_news_id'] . '_update';
                                    $column_list = Yii::$app->params['environment'] . '_new_list_' . $is_area . '_' . $column_ids . '_update';
                                    $redis->set($column_list, time());
                                } else {
                                    $name = Yii::$app->params['environment'] . '_new_list_' . $is_area . '_' . $column_ids . '_update';
                                }
                                $redis->set($name, time());
                            }

                            $res_data['news_id'] = (String)$news_id;
                            $this->_successData($res_data);
                        }else {

                            if ($save_type == 2) { //定时发布  记录此次更改的 定时发布时间
                                $data['refresh_time'] = $data['refresh_time'];
                            }else{
                                $data['refresh_time'] = time();
                            }
//                        $data['special_news_id'] = $data['special_news_id'];
                            if ($data['special_news_id']) {
                                $data['type_id'] = $data['type_id'];
                            }
                            $data['create_time'] = date("Y-m-d H:i:s");
                            News::updateAll($data, ['news_id' => $news_id]);
                            $data['news_id'] = $news_id;
                            $redis->set('news_id-' . $news_id, $data);
                            if ($news_type == 4 && $before_video['file_id'] && $before_video['file_id'] != $video_info[0]['file_id']) { //视频类型新闻
                                $res_video = $this->checkVideo($news_id, $category, $video_info);
                                if (!$res_video) {
                                    $this->_errorData(0104, "此视频信息不存在，请检查ID");
                                }
                            }
                            $res_data['news_id'] = (String)$news_id;
                            $this->_successData($res_data);
                        }
                    }else{
                        //草稿 或 定时新闻 都需要更新缓存
                        $data['status']  = $save_type;
                        if($save_type == 2){
                            $data['refresh_time'] = $data['refresh_time'];
                        }else{
                            $data['refresh_time'] = time();
                        }
                        $redis = Yii::$app->cache;
//                        $redis_info = $redis->get('news_id-'.$news_id);
//                        if($redis_info){
//                            $redis->delete('news_id-'.$news_id);
//                        }
                        $data['create_time'] = date("Y-m-d H:i:s");
                        $data['news_id'] = $news_id;
                        $redis->set('news_id-'.$news_id, $data);
                        if($news_type == 4 && $before_video['file_id'] && $before_video['file_id'] != $video_info[0]['file_id']){ //视频类型新闻
                            $res_video = $this->checkVideo($news_id,$category,$video_info);
                            if(!$res_video){
                                $this->_errorData(0104,"此视频信息不存在，请检查ID");
                            }
                        }
                        $res_data['news_id'] = (String)$news_id;
                        $this->_successData($res_data);
                    }
                }
            }else{
                if($news_info) {
                    //新 创建到数据表
                    $news_info['news_id'] = time() . $this->getRange();
                    $true_newsid = $news_info['news_id'];
                    $true_specialid = $news_info['special_news_id'];
                }else{
                    $data['news_id'] = time() . $this->getRange();
                    $true_newsid = $data['news_id'];
                    $true_specialid = $data['special_news_id'];
                }

                //相关新闻
                if(!empty($relation)){
                    foreach($relation as $list){
                        $datalist = new NewsRecommend();
                        if(!empty($list)){
                            $datalist['news_id']      = isset($true_newsid)  ? $true_newsid : $news_id;
                            $datalist['recommend_id'] = substr($list['url'],3);
                            $datalist['weight']       = $list['weight'];
                            $datalist->save();
                        }
                    }
                }

                if($news_info) { //被复制新闻存在
                    $news_info['status'] = $save_type;
                    $news_info['create_time'] = date('Y-m-d H:i:s', time());
                    if ($save_type == 2) {
                        $news_info['refresh_time'] = $data['refresh_time'];
                    } else {
                        $news_info['refresh_time'] = time();
                    }
                    $news_info['reference_id'] = $data['reference_id'];
                    $news_info['reference_type'] = $data['reference_type'];

                    foreach ($news_info as $key => $val) {
                        $news[$key] = $val;
                    }
                    $news['click_count'] = 0;
                }else{ //被复制新闻不存在 直接使用当前编辑页面数据
                    $data['status'] = $save_type;
                    $data['create_time'] = date('Y-m-d H:i:s', time());
                    if ($save_type == 2) {
                        $data['refresh_time'] = $data['refresh_time'];
                    } else {
                        $data['refresh_time'] = time();
                    }

                    foreach ($data as $key => $val) {
                        $news[$key] = $val;
                    }
                    $news['full_status'] = 0;
                }
                $news['special_news_id'] = $data['special_news_id'];
                if($data['special_news_id']){
                    $news['type_id'] = $data['type_id'];
                }
                $news->save();
                if($save_type != 0){
                    $redis->set('news_id-'.$true_newsid,$news->attributes);
                }else{
                    //判断 是否需要 缓存 栏目热门新闻
//                    $redis_column = $this->getOneNew($data['column_id'],$data['area_id']);
//                    if($redis_column['news_id'] != $data['news_id']){
//                        //更新 栏目redis
//                        $redis_columns = $this->getOneNews($data['column_id'],$data['area_id']);
//                        if(!$redis_columns) {
//                            $redis->set(Yii::$app->params['environment'] . "_hotnews_" . $column_type . '_' . $column_id,$redis_columns);
//                        }
//                    }

                    if($auto_save != 1) {
                        if($news_info){
                            //生成 静态
                            $this->add_static($news_type, $news_info);
                        }else{
                            //生成 静态
                            $this->add_static($news_type, $news_info);
                        }

                        // 根据实际 更新 列表
                        $redis = Yii::$app->cache;
                        if ($column_type == 0) {
                            $is_area = 0;
                        } else if ($column_type == 1) {
                            $is_area = 1;
                        }
                        $column_ids = $column_id;
                        if ($data['special_news_id'] != 0) {
                            $name = Yii::$app->params['environment'] . "_special_list_" . $true_specialid . '_update';
                            $column_list = Yii::$app->params['environment'] . '_new_list_' . $is_area . '_' . $column_ids . '_update';
                            $redis->set($column_list, time());
                        } else {
                            $name = Yii::$app->params['environment'] . '_new_list_' . $is_area . '_' . $column_ids . '_update';
                        }
                        $redis->set($name, time());
                    }
                }

                if($news_type == 4){ //视频类型新闻
                    if($news_info) {
                        $is_video = NewsVideo::find()->where(['news_id' => $data['reference_id']])->asArray()->one();
                        if (!$is_video) {
                            $this->_errorData(0104, "此视频信息不存在，请检查ID");
                        }
                        foreach ($is_video as $video_k => $video_v) {
                            $news_video[$video_k] = $video_v;
                        }
                    }
                    $news_video['category']    = $category;
                    $news_video['news_id']     = $true_newsid;
                    $news_video['update_time'] = date("Y-m-d H:i:s");
                    if($news_info){
                        $news_video->save();
                    }else if($video_info) {
                        $news_video['duration'] = $video_info[0]['duration'];
                        $news_video['file_name'] = $video_info[0]['file_name'];
                        $news_video['file_id'] = $video_info[0]['file_id'];
                        $news_video['height'] = $video_info[0]['height'];
                        $news_video['width'] = $video_info[0]['width'];
                        $news_video['size'] = $video_info[0]['size'];
                        $news_video['thumbnail_url'] = $video_info[0]['image_url'];
                        $news_video['video_url'] = $video_info[0]['url'];

                        $news_video->save();
                    }
                }
//                $res_data = $news->attributes;
//                $res_data['news_id'] = "'".$res_data['news_id']."'";
                $res_data['news_id'] = (String)$true_newsid;
                $this->_successData($res_data);
            }
        }


        //关联新闻
        $relation = isset($this->params['relation']) ? $this->params['relation']  : ''; //关联新闻
//        $relation = json_decode($relation);
        $recommendObj = new NewsRecommend();
        if($news_id){
            $recommendObj->deleteAll(['news_id'=>$news_id]);
        }

        /*
         * [{     "a":{         "c":"aa",         "d":"bb"     },     "b":{         "e":"33",         "f":"df"     } }]
         * [{"id":"1487742641478591362","weight":"200"  },{"id":"1479782442819734265","weight":"200"  }]
         * */
        if(!$news_id){
            $data['news_id'] = time().$this->getRange();
        }

        if(!empty($relation)){
            foreach($relation as $list){
                $datalist = new NewsRecommend();
                if(!empty($list)){
                    $datalist['news_id']      = isset($data['news_id'])  ? $data['news_id'] : $news_id;
                    $datalist['recommend_id'] = substr($list['url'],3);
                    $datalist['weight']       = $list['weight'];
                    $datalist->save();
                }
            }
        }

        if($save_type == 0) {
            if (!empty($data['subtitle']) && mb_strlen(trim($data['subtitle']), 'UTF8') > 10) {
                $this->_errorData('0108', "副标题不能超过10个字");
            }
            if (!preg_match('/^(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])$/', $data['weight'])) {
                $this->_errorData('0107', "权重必须为0到255的数字！");
            }
        }

        $redis = Yii::$app->cache;
        if($news_id){
            $data['news_id'] = $news_id;
            //查看新闻详情
            $news_info = News::find()
                ->where(['news_id'=>$news_id])
                ->asArray()->one();
            //判断新闻类型 是否改动  定时时间是否改动
            if($news_info['status'] == 0){ //原来是发布类型 不可改变类型
                //修改 发布新闻  （不需存缓存）
                $data['create_time']    = $news_info['create_time'];
                $data['refresh_time']   = $news_info['refresh_time'];
                $data['update_time']    = time();
                $data['status'] = 0;

                News::updateAll($data,['news_id'=>$news_id]);

                //判断 是否需要 缓存 栏目热门新闻
//                $redis_column = $this->getOneNew($data['column_id'],$data['area_id']);
//                if($redis_column['news_id'] != $data['news_id']){
//                    //更新 栏目redis
//                    $redis_columns = $this->getOneNews($data['column_id'],$data['area_id']);
//                    if(!$redis_columns) {
//                        $redis->set(Yii::$app->params['environment'] . "_hotnews_" . $column_type . '_' . $column_id,$redis_columns);
//                    }
//                }

                if($news_type == 4){ //视频类型新闻
                    $news_video->deleteAll(['news_id'=>$news_id]);
                    $news_video['category']    = $category;
                    $news_video['news_id']     = $news_id;
                    $news_video['update_time'] = date("Y-m-d H:i:s");
                    if($video_info) {
                        $news_video['duration'] = $video_info[0]['duration'];
                        $news_video['file_name'] = $video_info[0]['file_name'];
                        $news_video['file_id'] = $video_info[0]['file_id'];
                        $news_video['height'] = $video_info[0]['height'];
                        $news_video['width'] = $video_info[0]['width'];
                        $news_video['size'] = $video_info[0]['size'];
                        $news_video['thumbnail_url'] = $video_info[0]['image_url'];
                        $news_video['video_url'] = $video_info[0]['url'];

                        $news_video->save();
                    }
                }

                if($auto_save != 1) {
                    //生成 静态
                    $this->add_static($news_type, $data);

                    // 根据时间 更新 列表
                    $redis = Yii::$app->cache;
                    if ($column_type == 0) {
                        $is_area = 0;
                    } else if ($column_type == 1) {
                        $is_area = 1;
                    }
                    $column_ids = $column_id;
                    if ($data['special_news_id'] != 0) {
                        $name = Yii::$app->params['environment'] . "_special_list_" . $data['special_news_id'] . '_update';
                        $column_list = Yii::$app->params['environment'] . '_new_list_' . $is_area . '_' . $column_ids . '_update';
                        $redis->set($column_list, time());
                    } else {
                        $name = Yii::$app->params['environment'] . '_new_list_' . $is_area . '_' . $column_ids . '_update';
                    }
                    $redis->set($name, time());
                }
                
                $res_data['news_id'] = (String)$news_id;
                $this->_successData($res_data);
            }else{
                //更改类型 更新数据，不需记录缓存
                if($news_info['status'] != $save_type || $data['refresh_time'] != $news_info['refresh_time']){
                    $news_info['status'] = $save_type;
                    $redis = Yii::$app->cache;
                    $redis_info = $redis->get('news_id-'.$news_id);

                    if($save_type == 0){ //发布 更新数据 清缓存
                        $data['create_time'] = date("Y-m-d H:i:s");
                        $data['refresh_time']   = time();
                        $data['update_time']    = time();
                        if($redis_info){
                            $redis->delete('news_id-'.$news_id);
                        }
                        $data['status'] = 0;

                        News::updateAll($data,['news_id'=>$news_id]);
                        //判断 是否需要 缓存 栏目热门新闻
//                        $redis_column = $this->getOneNew($data['column_id'],$data['area_id']);
//                        if($redis_column['news_id'] != $data['news_id']){
//                            //更新 栏目redis
//                            $redis_columns = $this->getOneNews($data['column_id'],$data['area_id']);
//                            if(!$redis_columns) {
//                                $redis->set(Yii::$app->params['environment'] . "_hotnews_" . $column_type . '_' . $column_id,$redis_columns);
//                            }
//                        }

                        if($news_type == 4){ //视频类型新闻
                            $news_video->deleteAll(['news_id'=>$news_id]);
                            $news_video['category']    = $category;
                            $news_video['news_id']     = $news_id;
                            $news_video['update_time'] = date("Y-m-d H:i:s");
                            if($video_info) {
                                $news_video['duration'] = $video_info[0]['duration'];
                                $news_video['file_name'] = $video_info[0]['file_name'];
                                $news_video['file_id'] = $video_info[0]['file_id'];
                                $news_video['height'] = $video_info[0]['height'];
                                $news_video['width'] = $video_info[0]['width'];
                                $news_video['size'] = $video_info[0]['size'];
                                $news_video['thumbnail_url'] = $video_info[0]['image_url'];
                                $news_video['video_url'] = $video_info[0]['url'];

                                $news_video->save();
                            }
//                            if( $data['column_id'] != 9){ //非视频栏目下 新闻 需在视频栏目下复制一条新闻
//                                $data['news_id']     = time().$this->getRange();
//                                $data['create_time'] = date("Y-m-d H:i:s");
//                                if($column_type == 1){
//                                    $data['area_id'] = 0;
//                                }
//                                $data['column_id']   = 9;
//                                $col_news = new News();
//                                foreach($data as $k=>$v){
//                                    if($k == "full_status"){
//                                        $col_news[$k] == 0;
//                                    }else{
//                                        $col_news[$k] = $v;
//                                    }
//                                }
//                                $col_news->save();
//                                if(!empty($video_info)){
//                                    $news_video['category']    = $category;
//                                    $news_video['news_id']     = $data['news_id'];
//                                    $news_video['update_time'] = date("Y-m-d H:i:s");
//                                    $news_video['duration']    = $video_info[0]['duration'];
//                                    $news_video['file_name']   = $video_info[0]['file_name'];
//                                    $news_video['file_id']     = $video_info[0]['file_id'];
//                                    $news_video['height']      = $video_info[0]['height'];
//                                    $news_video['width']       = $video_info[0]['width'];
//                                    $news_video['size']        = $video_info[0]['size'];
//                                    $news_video['thumbnail_url'] = $video_info[0]['image_url'];
//                                    $news_video['video_url']     = $video_info[0]['url'];
//
//                                    $col_video = new NewsVideo();
//                                    foreach($news_video as $col=>$vid){
//                                        if($col == 'play_count'){
//                                            $col_video[$col] = 0;
//                                        }else {
//                                            $col_video[$col] = $vid;
//                                        }
//                                    }
//                                    $col_video->save();
//                                }
//                            }
                        }

                        if($auto_save != 1) {
                            //生成 静态
                            $this->add_static($news_type, $data);

                            // 根据时间 更新 列表
                            $redis = Yii::$app->cache;
                            if ($column_type == 0) {
                                $is_area = 0;
                            } else if ($column_type == 1) {
                                $is_area = 1;
                            }
                            $column_ids = $column_id;
                            if ($data['special_news_id'] != 0) {
                                $name = Yii::$app->params['environment'] . "_special_list_" . $data['special_news_id'] . '_update';
                                $column_list = Yii::$app->params['environment'] . '_new_list_' . $is_area . '_' . $column_ids . '_update';
                                $redis->set($column_list, time());
                            } else {
                                $name = Yii::$app->params['environment'] . '_new_list_' . $is_area . '_' . $column_ids . '_update';
                            }
                            $redis->set($name, time());
                        }
                        $res_data['news_id'] = (String)$news_id;
                        $this->_successData($res_data);
                    }else {
                        //修改 发布类型 或 定时发布时间 不需要更新缓存 只更新数据
                        if ($save_type == 2) { //定时发布  记录此次更改的 定时发布时间
                            $data['refresh_time'] = $data['refresh_time'];
                        } else {
                            $data['refresh_time'] = time();
                        }
                        if($news_info['status'] == 1 && $save_type == 1){
                            unset($data['status']);
                        }
                        $data['create_time'] = date("Y-m-d H:i:s");
                        News::updateAll($data, ['news_id' => $news_id]);
                        $data['news_id'] = $news_id;
                        $redis->set('news_id-' . $news_id, $data);
                        if ($news_type == 4) { //视频类型新闻
                            $news_video->deleteAll(['news_id' => $news_id]);
                            $news_video['category'] = $category;
                            $news_video['news_id'] = $news_id;
                            $news_video['update_time'] = date("Y-m-d H:i:s");
                            if ($video_info) {
                                $news_video['duration'] = $video_info[0]['duration'];
                                $news_video['file_name'] = $video_info[0]['file_name'];
                                $news_video['file_id'] = $video_info[0]['file_id'];
                                $news_video['height'] = $video_info[0]['height'];
                                $news_video['width'] = $video_info[0]['width'];
                                $news_video['size'] = $video_info[0]['size'];
                                $news_video['thumbnail_url'] = $video_info[0]['image_url'];
                                $news_video['video_url'] = $video_info[0]['url'];

                                $news_video->save();
                            }
                        }
//                    $res_data = News::find()
//                        ->where(['news_id'=>$news_id])
//                        ->asArray()->one();
                        $res_data['news_id'] = (String)$news_id;
                        $this->_successData($res_data);
                    }
                }else {
                    //草稿 或 定时新闻 都需要更新缓存 不需更新数据
                    $redis = Yii::$app->cache;
                    $redis_info = $redis->get('news_id-'.$news_id);
                    if($redis_info){
                        $redis->delete('news_id-'.$news_id);
                    }
                    if($save_type == 1){
                        $data['refresh_time'] = time();
                    }
                    $data['create_time'] = date("Y-m-d H:i:s");
//                    News::updateAll($data,['news_id'=>$news_id]);
                    $data['news_id'] = $news_id;
                    $redis->set('news_id-'.$news_id, $data);
                    if($news_type == 4){ //视频类型新闻
                        $news_video->deleteAll(['news_id'=>$news_id]);
                        $news_video['category']    = $category;
                        $news_video['news_id']     = $news_id;
                        $news_video['update_time'] = date("Y-m-d H:i:s");
                        if($video_info) {
                            $news_video['duration'] = $video_info[0]['duration'];
                            $news_video['file_name'] = $video_info[0]['file_name'];
                            $news_video['file_id'] = $video_info[0]['file_id'];
                            $news_video['height'] = $video_info[0]['height'];
                            $news_video['width'] = $video_info[0]['width'];
                            $news_video['size'] = $video_info[0]['size'];
                            $news_video['thumbnail_url'] = $video_info[0]['image_url'];
                            $news_video['video_url'] = $video_info[0]['url'];

                            $video_info->save();
                        }
                    }
//                    $res_data = $redis->get('news_id-'.$news_id);
                    $res_data['news_id'] = (String)$news_id;
                    $this->_successData($res_data);
                }
            }
        }else{
            $data['news_id']     = $data['news_id'];
            $data['status']      = $save_type;
            $data['create_time'] = date('Y-m-d H:i:s', time());
            if($save_type == 2){
                $data['refresh_time'] = $data['refresh_time'];
            }else {
                $data['refresh_time'] = time();
            }
            $data['creator_id'] = isset($this->params['admin_id']) ? $this->params['admin_id'] : 0;

            $res_sta = $data->save();
            if(!$res_sta){
                $this->_errorData('0119','保存失败');
            }
            //非 发布的 记录缓存
            if($save_type != 0){
                $redis = Yii::$app->cache;
                $redis->set('news_id-'.$data['news_id'],$data->attributes);
            }else{ //发布新闻
                //判断 是否需要 缓存 栏目热门新闻
//                $redis_column = $this->getOneNew($data['column_id'],$data['area_id']);
//                if($redis_column['news_id'] != $data['news_id']){
//                    //更新 栏目redis
//                    $redis_columns = $this->getOneNews($data['column_id'],$data['area_id']);
//                    if(!$redis_columns) {
//                        $redis->set(Yii::$app->params['environment'] . "_hotnews_" . $is_area . '_' . $column_id,$redis_columns);
//                    }
//                }

                if($auto_save != 1) {
                    //生成静态页面
                    $this->add_static($news_type, $data);

                    // 根据时间 更新 列表
                    $redis = Yii::$app->cache;
                    if ($column_type == 0) {
                        $is_area = 0;
                    } else if ($column_type == 1) {
                        $is_area = 1;
                    }
                    $column_ids = $column_id;
                    if ($data['special_news_id'] != 0) {
                        $name = Yii::$app->params['environment'] . "_special_list_" . $data['special_news_id'] . '_update';
                        $column_list = Yii::$app->params['environment'] . '_new_list_' . $is_area . '_' . $column_ids . '_update';
                        $redis->set($column_list, time());
                    } else {
                        $name = Yii::$app->params['environment'] . '_new_list_' . $is_area . '_' . $column_ids . '_update';
                    }
                    $redis->set($name, time());
                }

            }
            $res_newid = $data['news_id'];

            if($news_type == 4){ //视频类型新闻
//                if($save_type == 0 && empty($video_info)){
//                    $this->_errorData('0105',"视频信息不能为空");
//                }
                if(!empty($video_info)){
                    $news_video['category']    = $category;
                    $news_video['news_id']     = $data['news_id'];
                    $news_video['update_time'] = date("Y-m-d H:i:s");
                    $news_video['duration']    = isset($video_info[0]['duration']) ? $video_info[0]['duration'] : '';
                    $news_video['file_name']   = isset($video_info[0]['file_name']) ? $video_info[0]['file_name'] : '';
                    $news_video['file_id']     = isset($video_info[0]['file_id']) ? $video_info[0]['file_id'] : '';
                    $news_video['height']      = isset($video_info[0]['height']) ? $video_info[0]['height'] : '';
                    $news_video['width']       = isset($video_info[0]['width']) ? $video_info[0]['width'] : '';
                    $news_video['size']        = isset($video_info[0]['size']) ? $video_info[0]['size'] : '';
                    $news_video['thumbnail_url'] = isset($video_info[0]['image_url']) ? $video_info[0]['image_url'] : '';
                    $news_video['video_url']     = isset($video_info[0]['url']) ? $video_info[0]['url'] : '';

                    $news_video->save();
                }

//                if($data['column_id'] != 9 && $save_type==0){ //非视频栏目下 新闻 需在视频栏目下复制一条新闻
//                    $data['news_id'] = time().$this->getRange();
//                    $data['create_time'] = date("Y-m-d H:i:s");
//                    if($column_type == 1){
//                        $data['area_id'] = 0;
//                    }
//                    $data['column_id'] = 9;
//                    $col_news = new News();
//                    foreach($data as $k=>$v){
//                        if($k == "full_status"){
//                            $col_news[$k] == 0;
//                        }else {
//                            $col_news[$k] = $v;
//                        }
//                    }
//                    $col_news->save();
//
//                    if(!empty($video_info)){
//                        $news_video['category'] = $category;
//                        $news_video['news_id'] = $data['news_id'];
//                        $news_video['update_time'] = date("Y-m-d H:i:s");
//                        $news_video['duration'] = isset($video_info[0]['duration']) ? $video_info[0]['duration'] : '';
//                        $news_video['file_name'] = isset($video_info[0]['file_name']) ? $video_info[0]['file_name'] : '';
//                        $news_video['file_id'] = isset($video_info[0]['file_id']) ? $video_info[0]['file_id'] : '';
//                        $news_video['height'] = isset($video_info[0]['height']) ? $video_info[0]['height'] : '';
//                        $news_video['width'] = isset($video_info[0]['width']) ? $video_info[0]['width'] : '';
//                        $news_video['size'] = isset($video_info[0]['size']) ? $video_info[0]['size'] : '';
//                        $news_video['thumbnail_url'] = isset($video_info[0]['image_url']) ? $video_info[0]['image_url'] : '';
//                        $news_video['video_url'] = isset($video_info[0]['url']) ? $video_info[0]['url'] : '';
//
//                        $col_video = new NewsVideo();
//                        foreach ($news_video as $col => $vid) {
//                            if ($col == 'play_count') {
//                                $col_video[$col] = 0;
//                            } else {
//                                $col_video[$col] = $vid;
//                            }
//                        }
//                        $col_video->save();
//                    }
//                }
            }
//            $res_data = $data->attributes;
//            $res_data['news_id'] = "'".$res_data['news_id']."'";
            $res_data['news_id'] = (String)$res_newid;
            $this->_successData($res_data);
        }
    }


    /*
     * 查看新闻详情
     *
     * */
    public function actionNewinfo(){
        $news_id     = isset($this->params['news_id'])   ? $this->params['news_id']   : '';
        $save_type   = isset($this->params['save_type']) ? $this->params['save_type'] :  0; //新闻发布类型 0发布新闻，1存为草稿，2定时发布

        if(!$news_id){
            $this->_errorData(0101, "参数错误");
        }
        $news_info = array();

        $redis = Yii::$app->cache;
        if($save_type != 0){
            //读缓存
            $news_info = $redis->get('news_id-'.$news_id);

            if(!$news_info){
                $news_info = News::find()->where(['news_id'=>$news_id])->asArray()->one();
            }else{
                if(!isset($news_info['news_id'])){
                    $news_info['news_id'] = $news_id;
                }else {
                    $news_info['news_id'] = strval($news_info['news_id']);
                }
            }
        }else{
            $news_info = News::find()->where(['news_id'=>$news_id])->asArray()->one();
        }
        if(!$news_info){
            $this->_errorData(0111, "新闻不存在");
        }

        $video_info = NewsVideo::find()->where(['news_id'=>$news_id])->asArray()->one();
        if(!$video_info){
            $news_info['video_info'] = array();
        }else{
            $news_info['video_info'] = $video_info;
        }
        $relation = NewsRecommend::find()->where(['news_id'=>$news_id])->asArray()->all();
        if(!$relation){
            $news_info['relation'] = array();
        }else{
            $news_info['relation'] = $relation;
        }

        if($news_info['refresh_time']) {
            $news_info['refresh_time'] = date('Y-m-d H:i:s', $news_info['refresh_time']);
        }
        $news_info['content'] = json_decode($news_info['content']);

        $this->_successData($news_info);
    }


    /*
     * 草稿 新闻列表 / 定时发布新闻 列表
     * type_one 筛选-新闻分类 1焦点轮播，2栏目类型
     * type_two 筛选-新闻类型 1全部 3专题 4普通视频 5VR视频 6图集 7文本 8图文 9视频直播 10VR直播 11图文直播
     * */
    public function actionSaveNews(){
        $type        = isset($this->params['type']) ? $this->params['type'] : 0; //接口类型 0草稿箱列表, 1定时发布列表
        $column_type = isset($this->params['column_type']) ? $this->params['column_type'] : ''; //栏目类型 0常规栏目，1本地栏目
        $column_id   = isset($this->params['column_id'])   ? $this->params['column_id']   : ''; //栏目ID
        $keyword     = !empty($_REQUEST['keyword'])   ? $_REQUEST['keyword']   : ''; //关键字 标题
        $type_one    = !empty($_REQUEST['type_one'])  ? $_REQUEST['type_one']  : ''; //搜索分类
        $type_two    = !empty($_REQUEST['type_two'])  ? $_REQUEST['type_two']  : ''; //搜索 类型
        $page        = !empty($this->params['page'])  ? $this->params['page']  :  1;
        $count       = !empty($this->params['count']) ? $this->params['count'] : 20;

        $offset = ($page-1)*$count;
        //栏目类型 区分
        if($column_type == 1){
            $where_type = 'area_id';
        }else{
            $where_type = 'column_id';
        }

        //接口类型 处理
        if($type == 1){
            $type = 2;
        }else{
            $type = 1;
        }

        $where_str = '';
        $where_search = '';
        //关键字 处理 标题
        if($keyword){
            $where_search = " title like '%".$keyword."%' ";
        }
        //由于 分类 和 类型 有重叠部分，做以下处理
        if($type_one == 1){
            if($type_two == 3){
                $where_str .= " type = 2 AND type = 3";
            }else if($type_two == 4){
                //普通视频
                $where_str = ' type = 2 AND (vrnews1.news.type=4 AND vrnews1.news_video.category=1)';
            }elseif($type_two == 5){
                //VR视频
                $where_str = ' type = 2 AND (vrnews1.news.type=4 AND vrnews1.news_video.category=2)';
            }elseif($type_two == 6){
                //VR视频
                $where_str = ' type = 2 AND vrnews1.news.type=5';
            }elseif($type_two == 7){
                $where_str = " type = 2 AND vrnews1.news.type=6 ";
            }elseif($type_two == 8){
                $where_str = " type = 2 AND vrnews1.news.type=7 ";
            }else{
                $where_str = " type = 2 ";
            }

        }else if($type_one == 2){
            if($type_two == 3){
                $where_str .= " type !=2 AND type = 3";
            }else if($type_two == 4){
                //普通视频
                $where_str = ' type !=2 AND (vrnews1.news.type=4 AND vrnews1.news_video.category=1)';
            }elseif($type_two == 5){
                //VR视频
                $where_str = ' type !=2 AND (vrnews1.news.type=4 AND vrnews1.news_video.category=2)';
            }elseif($type_two == 6){
                //VR视频
                $where_str = ' type !=2 AND vrnews1.news.type=5';
            }elseif($type_two == 7){
                $where_str = " type !=2 AND vrnews1.news.type=6 ";
            }elseif($type_two == 8){
                $where_str = " type !=2 AND vrnews1.news.type=7 ";
            }else{
                $where_str = " type !=2 ";
            }
        }else{
            if($type_two == 3){
                $where_str .= "  type = 3";
            }else if($type_two == 4){
                //普通视频
                $where_str = ' vrnews1.news.type=4 AND vrnews1.news_video.category=1';
            }elseif($type_two == 5){
                //VR视频
                $where_str = ' vrnews1.news.type=4 AND vrnews1.news_video.category=2';
            }elseif($type_two == 6){
                //VR视频
                $where_str = ' vrnews1.news.type=5';
            }elseif($type_two == 7){
                $where_str = " vrnews1.news.type=6 ";
            }elseif($type_two == 8){
                $where_str = " vrnews1.news.type=7 ";
            }
        }

        $news = array();
        $res_news = array();

//        $news_count = News::find()
//            ->leftJoin("vrnews1.news_video",'vrnews1.news.news_id = vrnews1.news_video.news_id')
//            ->where([$where_type=>$column_id,'news.status'=>$type])
//            ->andWhere($where_str)->count();

        //查看新闻 列表
        $query = new Query();
        $select_all = "news.news_id,abstract as news_abstract,title,subtitle,content,cover_image,app_pub,web_pub,vrnews1.news.vote_id,reference_type,news.vote_id,reference_id,type,column_id,area_id,DATE_FORMAT(`create_time`,'%Y/%m/%d %H:%i') as create_time,type_id,special_news_id,top_status,full_status,full_title,full_subtitle,full_cover_image,source_id,source_name,special_id,special_type,special_entry,special_title,special_abstract,special_image,thumbnail_url,duration,play_count,category,outer_url_ishot,outer_url,from_unixtime(refresh_time) as refresh_time,vrnews1.news_video.video_url as video_url,vrnews1.news_video.video_url1,vrnews1.news_video.video_url2,vrnews1.news_video.width,vrnews1.news_video.width1,vrnews1.news_video.width2,vrnews1.news_video.height,vrnews1.news_video.height1,vrnews1.news_video.height2,vrnews1.news_video.size,vrnews1.news_video.size1,vrnews1.news_video.size2 ,vrnews1.news_video.`file_id` as file_id";
        $query->select([$select_all])->from("vrnews1.news");
        $query->leftJoin('vrnews1.news_video','vrnews1.news.news_id = vrnews1.news_video.news_id');
        $query->where($where_type."=".$column_id." and news.status=".$type);
        $query->andWhere($where_str);
        if($where_search) {
            $query->andWhere($where_search);
        }

        $news_count = $query->count('*',Yii::$app->vrnews1);
        $query->orderBy(" create_time desc ");
        $query->offset($offset);
        $query->limit($count);
        $command = $query->createCommand();
        $news    =  $command->queryAll();

        $redis = Yii::$app->cache;
        foreach($news as $key=>$val){
            //如果有缓存 取缓存数据
            $news_info = $redis->get('news_id-'.$val['news_id']);
            if($news_info){
                $res_news[$key] = $news_info;
                if(!isset($news_info['news_id'])){
                    $res_news[$key]['news_id'] = $val["news_id"];
                }else{
                    $res_news[$key]['news_id'] = strval($news_info["news_id"]);
                }

                $res_news[$key]['refresh_time'] = date("Y-m-d H:i:s",$news_info["refresh_time"]);
            }else{
                $res_news[$key] = $val;
            }
            $res_news[$key]['type_zh'] = $this->_getNewType($val);
        }
        $return_news['count'] = $news_count;
        $return_news['news'] = $res_news;

        $this->_successData($return_news);
    }

    /*
     * 修改发布时间 立即发布
     * */
    public function actionEdittime(){
        $news_id = isset($this->params['news_id']) ? $this->params['news_id'] : 0;
        $type    = isset($this->params['type']) ? $this->params['type'] : 0; //0 发布时间 ；1立即发布
        $refresh_time = isset($this->params['refresh_time']) ? $this->params['refresh_time'] : 0; //发布时间

        $news = new News();
        $news_info = $news->find()->where(['news_id'=>$news_id])->asArray()->one();

        if(!$news_info || $news_info['status'] != 2){
            $this->_errorData("0101", "参数错误");
        }
        //非自动保存 且 为引用 判断 被引用新闻ID是否正常 或 是否为再引用  不正常 返回报错
        if($news_info['reference_type'] == 1){
            $news_info_1 = News::find()
                ->where(['news_id'=>$news_info['reference_id']])
                ->asArray()->one();
            if(!$news_info_1){
                $this->_errorData(0114, "被引用新闻不存在！");
            }
            if($news_info_1['reference_type'] == 1){
                $this->_errorData(0115, "被引用新闻 为再次引用新闻，请重新填写！");
            }
        }

        $redis = Yii::$app->cache;
        $redis_info = $redis->get('news_id-'.$news_id);

        if($type == 0){ // 发布时间 修改
            if(!$refresh_time){
                //类型改为 草稿 发布时间清空
                $data['refresh_time']   = 0;
                $data['status'] = 1;
            }else{
                $data['refresh_time'] = strtotime($refresh_time);
                $data['status'] = 2;
            }

            News::updateAll($data,['news_id'=>$news_id]);
            if($redis_info){
                $redis_info['refresh_time'] = $data['refresh_time'];
                if($data['refresh_time'] == 0){
                    $redis_info['status'] = 1;
                }else{
                    $redis_info['status'] = 2;
                }
                $redis_info["news_id"] = $news_id;
                $redis->delete('news_id-'.$news_id);
                $redis->set('news_id-'.$news_id,$redis_info);
            }
            $this->_successData(true);
        }else{
            $data['status'] = 0;
            $data['create_time']  = date("Y-m-d H:i:s");
            $data['refresh_time'] = time();
            $data['update_time']  = time();

            if($redis_info){
                $redis_info['status'] = 0;
                $redis_info['create_time']  = date("Y-m-d H:i:s");
                $redis_info['refresh_time'] = time();
                $redis_info['update_time']  = time();
                $redis->delete('news_id-'.$news_id);
            }
            News::updateAll($data,['news_id'=>$news_id]);

            $this->add_static($news_info['status'], $news_info);
            // 根据时间 更新 列表
            $redis = Yii::$app->cache;
            if($news_info['column_id'] != 0){
                $is_area = 0;
                $column_ids  = $news_info['column_id'];
            }else if($news_info['area_id'] != 0){
                $is_area = 1;
                $column_ids  = $news_info['area_id'];
            }

            if($news_info['special_news_id'] != 0){
                $name = Yii::$app->params['environment']."_special_list_" . $news_info['special_news_id'] . '_update';
                $column_list = Yii::$app->params['environment'].'_new_list_'.$is_area.'_'.$column_ids.'_update';
                $redis->set($column_list, time());
            }else{
                $name = Yii::$app->params['environment'].'_new_list_'.$is_area.'_'.$column_ids.'_update';
            }
            $redis->set($name, time());
            $this->_successData(true);
        }

    }


    /*
     * 删除新闻
     *
     * */
    public function actionDelNews(){
        $ids = isset($this->params['news_ids']) ? $this->params['news_ids'] : '';
        if(!$ids){
            $this->_errorData(0101, "参数错误");
        }
        $arr_ids = explode(',',$ids );
        $redis = Yii::$app->cache;
        foreach ($arr_ids as $key=>$val){
            $news_info = $redis->get('news_id-'.$val);
            if($news_info){
                $redis->delete("news_id-".$val);
            }
        }
        News::deleteAll('news_id in ('.$ids.')');
        $this->_successData(true);
    }


    /*
     * 采集端 发布新闻、上传至草稿箱
     *
     * */
    public function actionCreateNews(){
        $data = new News();
        $save_type    = $this->params['save_type']; //新闻发布类型 0发布新闻，1存为草稿
        $data['type'] = $this->params['news_type'] ? $this->params['news_type'] : 6; //新闻类型  2 轮播图  6 文本
        $column_type  = isset($this->params['column_type'])  ? $this->params['column_type']  : ''; //栏目类型 0常规栏目，1本地栏目
        $column_id    = isset($this->params['column_id'])    ? $this->params['column_id']    : ''; //栏目ID
        $data['title']    = isset($this->params['title'])        ? $this->params['title']    : ''; //标题
        $data['subtitle'] = isset($this->params['subtitle'])     ? $this->params['subtitle'] : ''; //副标
        $data['weight']   = isset($this->params['weight'])       ? $this->params['weight']   :  0; //权重
        $data['top_status'] = isset($this->params['top_status'])   ? $this->params['top_status']   :  0; //是否置顶 1是，0否
        $data['type_id']    = isset($this->params['type_id'])      ? $this->params['type_id']      :  0; //栏目类型ID
        $data['app_pub']    = $this->params['app_pub']      ? $this->params['app_pub']  :  0; //发布渠道 app 是1，否0
        $data['web_pub']    = $this->params['web_pub']      ? $this->params['web_pub']  :  0; //发布渠道 门户 是1，否0
        $data['content']    = isset($this->params['content']) ? $this->params['content'] : '';
        $content_img        = isset($this->params['content_img']) ? $this->params['content_img'] : ''; //正文中的图片
        if(!isset($save_type)){
            $this->_errorData("0101", "参数错误");
        }

        if (!preg_match('/^(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])$/', $data['weight'])) {
            $data['weight'] = 0;
        }
        //栏目类型
        if($column_type == 0){
            $data['column_id'] = $column_id;
            $data['area_id']   = 0;
        }else{
            $data['area_id']   = $column_id;
            $data['column_id'] = 0;
        }

        if(empty($data['title'])){
            $data['title'] = '无标题'.time().$this->getRange();
        }
        //拼接 正文
        if($content_img != ''){
            $content_img = "<p><img style='max-width:100%;' src='".$content_img."' /><br></p>";
            $data['content'] .= $data['content'].$content_img;
        }
        $data['content'] = '<p>'.str_replace("\t","<p><br></p>",$data['content']).'</p>'; //替换换行 /t 为 <br>
        $data['content'] = json_encode($data['content']); //新闻正文

        if($save_type == 0) {
            if (!empty($data['subtitle']) && mb_strlen(trim($data['subtitle']), 'UTF8') > 10) {
                $this->_errorData('0108', "副标题不能超过10个字");
            }
            if (!preg_match('/^(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])$/', $data['weight'])) {
                $this->_errorData('0107', "权重必须为0到255的数字！");
            }
        }

        $redis = Yii::$app->cache;
        $data['news_id']      = time().$this->getRange();
        $data['status']       = $save_type;
        $data['create_time']  = date('Y-m-d H:i:s', time());
        $data['refresh_time'] = time();
        $data['creator_id']   = 1;

        $data->save();
        //非 发布的 记录缓存
        if($save_type != 0){
            $redis->set('news_id-'.$data['news_id'],$data->attributes);
        }else{ //发布新闻
            //生成静态页面
            $this->add_static($data['type'], $data);

            // 根据时间 更新 列表
            if ($column_type == 0) {
                $is_area = 0;
            } else if ($column_type == 1) {
                $is_area = 1;
            }
            $column_ids = $column_id;
            $name = Yii::$app->params['environment'] . '_new_list_' . $is_area . '_' . $column_ids . '_update';

            $redis->set($name, time());
        }

        $this->_successData("成功");
    }
    /*
     * 生成 静态页面
     * */
    public function add_static($news_type,$data){
        //生成静态页面
        ob_start();

        if($news_type == 5){ //图集
            if(isset($data['reference_type']) && isset($data['reference_id'])){
                if($data['reference_type'] == 1 && !empty($data['reference_id'])) {
                    return true;
                }
            }
            $share = Yii::$app->params['admin_host'].'/photos/' . $data['news_id'] . '.html';
            $file_name = __DIR__.'/../web/html/photos/'.$data['news_id'].'.html';
            require_once(__DIR__.'/../web/template/images_batch.html');

            $content = ob_get_contents();
//            Record::record_data($content, $data['news_id']);
            if(!$content){
                return $this->_errorData('0113',"生产静态页面失败");
            }
            $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http';
            $wx_appKey    = Yii::$app->params['wx_appKey'];
            $wx_appSecret = Yii::$app->params['wx_appSecret'];
            $jssdk = new Jssdk($wx_appKey, $wx_appSecret);
            $signPackage = $jssdk->GetSignPackage();
            $appId = $signPackage["appId"];
            $timestamp = $signPackage["timestamp"];
            $nonceStr = $signPackage["nonceStr"];
            $signature = $signPackage["signature"];
            $description = $data['abstract'] ? $data['abstract'] : 'live.lawnewsw.com';
            if(!empty($data['reference_type']) && intval($data['reference_type']) == 1 && !empty($data['reference_id'])){
                //查看 被引用图集信息
                $ref_news = News::find()->where(['news_id'=>$data['reference_id']])->asArray()->one();
                if(!empty($ref_news['content'])){
                    $ref_news['content'] = json_decode($ref_news['content']);
                    $contents = array();
                    foreach ($ref_news['content'] as $re_k=>$re_v){
                        if($re_k < 3) {
                            if(is_object($ref_news['content'][$re_k])){
                                $str_con = substr($ref_news['content'][$re_k]->img,-2);
                                if($str_con == '/s'){
                                    $contents[$re_k]->img = substr($ref_news['content'][$re_k]->img,0,-2);
                                }else{
                                    if($contents[$re_k]['content']=='""'){
                                        $tmp = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/224x150!' : '';
                                        $contents[$re_k]['content'] = array($re_k=>array('img'=>$tmp));
                                    }else{
                                        $contents[$re_k]['content'][$re_k]['img'] = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/224x150!' : '';
                                    }
                                }
                            }else {
                                $str_con = substr($ref_news['content'][$re_k]['img'],-2);
                                if($str_con == '/s'){
                                    $contents[$re_k]['img'] = substr($ref_news['content'][$re_k]['img'],0,-2);
                                }
                                $contents[$re_k]['img'] = $re_v['img'] ? $re_v['img'] . '?imageMogr2/thumbnail/224x150!' : '';
                            }
                        }
                    }
                }
            }else{
                $contents = json_decode($data['content'],true);
            }

            if(isset($data['new_title'])){
                $title = $data['new_title'];
            }else{
                $title = $data['title'];
            }
            if(isset($data['new_cover_image'])){
                $cover_img =  $data['new_cover_image'];
            }else{
                if($data['cover_image']){
                    $cover_img = $data['cover_image'];
                }else{
                    $cover_img = Yii::$app->params['m_host'].'/public/H5/images/default.png';
                }
            }
            if(isset($data['new_cover_image'])){
                $news_cover_img =  $data['new_cover_image'];
            }else{
                if($data['cover_image']){
                    $news_cover_img =  $data['cover_image'];
                }else{
                    $news_cover_img =  Yii::$app->params['m_host'].'/public/H5/images/default.png';
                }
            }
            if(isset($data['new_abstract'])){
                $new_abstract = $data['new_abstract'];
            }else{
                if($data['abstract']){
                    $new_abstract = $data['abstract'];
                }else{
                    $new_abstract = 'live.lawnewsw.com';
                }
            }
            $text = '';

            $news_content = '';
            if($contents) {
                foreach ($contents as $key => $val) {
                    $text .= '<img src="' . $val['img'] . '" name="' . $key . '" onclick="clickImage()">';
                }
                foreach($contents as $keys=>$value){
                    $news_content .= '<div class="neirong" ';
                    if($keys == 0) {$news_content .= 'style="display:block;" ';}
                    $news_content .= '><div class="biaoti">
                            <div class="left">'.$data['title'].'</div>
                            <div class="right"></div>
                        </div>
                        <p>'.$value['text'].'</p>
                    </div>';
                }
            }

            $h5_host = Yii::$app->params['m_host'];
            $host    = Yii::$app->params['host_api'];
            $content = str_replace('{{appId}}',$appId ,$content );
            $content = str_replace('{{timestamp}}',$timestamp ,$content );
            $content = str_replace('{{nonceStr}}',$nonceStr ,$content );
            $content = str_replace('{{signature}}',$signature ,$content );
            $content = str_replace('{{title}}',$title ,$content );
            $content = str_replace('{{share}}',$share ,$content );
            $content = str_replace('{{description}}',$description ,$content );
            $content = str_replace('{{cove_img}}',$cover_img ,$content );
            $content = str_replace('{{new_cover_img}}',$news_cover_img ,$content );
            $content = str_replace('{{text}}',$text ,$content );
            $content = str_replace('{{abstract}}',$new_abstract ,$content );
            $content = str_replace('{{content}}',$news_content ,$content );
            $content = str_replace('{{h5_host}}',$h5_host ,$content );
            $content = str_replace('{{host}}',$host ,$content );
//            Record::record_data($content, $data['news_id'].'_add_static_content');
            file_put_contents($file_name, $content);
            ob_end_clean();

        }else{ //非图集

            $share = Yii::$app->params['admin_host'].'/text/' . $data['news_id'] . '.html';
            $file_name = __DIR__.'/../web/html/text/'.$data['news_id'].'.html';

            require_once(__DIR__.'/../web/template/image_text_batch.html');
            $content = ob_get_contents();
//            Record::record_data($content, $data['news_id']);
            if(!$content){
                return $this->_errorData('0113',"生产静态页面失败");
            }
            $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http';
            $wx_appKey    = Yii::$app->params['wx_appKey'];
            $wx_appSecret = Yii::$app->params['wx_appSecret'];
            $jssdk = new Jssdk($wx_appKey, $wx_appSecret);
            $signPackage = $jssdk->GetSignPackage();
            $appId = $signPackage["appId"];
            $timestamp = $signPackage["timestamp"];
            $nonceStr = $signPackage["nonceStr"];
            $signature = $signPackage["signature"];

            if(isset($data['new_abstract'])){
                $description = $data['new_abstract'];
            }else{
                if($data['abstract']){
                    $description =  $data['abstract'];
                }else{
                    $description =  'live.lawnewsw.com';
                }
            }
            if(isset($data['new_cover_image'])){
                $cover_img =  $data['new_cover_image'];
            }else{
                if($data['cover_image']){
                    $cover_img = $data['cover_image'];
                }else{
                    $cover_img = Yii::$app->params['m_host'].'/public/H5/images/default.png';
                }
            }
            if(isset($data['new_cover_image'])){
                $news_cover_img =  $data['new_cover_image'];
            }else{
                if($data['cover_image']){
                    $news_cover_img =  $data['cover_image'];
                }else{
                    $news_cover_img =  Yii::$app->params['m_host'].'/public/H5/images/default.png';
                }
            }
            if(isset($data['new_abstract'])){
                $new_abstract = $data['new_abstract'];
            }else{
                if($data['abstract']){
                    $new_abstract = $data['abstract'];
                }else{
                    $new_abstract = 'live.lawnewsw.com';
                }
            }
            $time = date('m月d日 H:i',strtotime($data['create_time']));
            if($data['source_name']) {
                $source = '来源：<span ><a style="color:#129aee; display:inline;" href="#" onclick="toSourceId(\'' . $data["source_id"] . '\',\'' . $data["source_name"] . '\')" >' . $data["source_name"] . '</a></span>';
            }else{
                $source = '';
            }
            $news_content = json_decode($data['content']);

            if(!empty($data['outer_url'])) {
                $out_url = '<div class="ReadText" style="text-align:left;margin:0 5%;font-size:1.6rem;height:30px;"><a class="up-btn oper-btn">0</a><a class="oper-btn collect-btn"></a><a href="' . $data['outer_url'] . '" style="float:right;color: #2ea7e0;">阅读原文</a></div>';
            }else{
                $out_url = '';
            }
            if(isset($data['new_title'])){
                $title = $data['new_title'];
            }else{
                $title = $data['title'];
            }
            $external_link = '';
            if(isset($data['external_link'])){
                $external_link = $data['external_link'];
            }

            $h5_host = Yii::$app->params['m_host'];
            $host    = Yii::$app->params['host_api'];
            $content = str_replace('{{appId}}',$appId ,$content );
            $content = str_replace('{{timestamp}}',$timestamp ,$content );
            $content = str_replace('{{nonceStr}}',$nonceStr ,$content );
            $content = str_replace('{{signature}}',$signature ,$content );
            $content = str_replace('{{share}}',$share ,$content );
            $content = str_replace('{{description}}',$description ,$content );
            $content = str_replace('{{title1}}',$title ,$content );
            $content = str_replace('{{cove_img}}',$cover_img ,$content );
            $content = str_replace('{{new_cover_image}}',$news_cover_img ,$content );
            $content = str_replace('{{title}}',$data['title'] ,$content );
            $content = str_replace('{{abstract}}',$new_abstract ,$content );
            $content = str_replace('{{data}}',$time ,$content );
            $content = str_replace('{{source}}',$source ,$content );
            $content = str_replace('{{content}}',$news_content ,$content );
            $content = str_replace('{{out_url}}',$out_url ,$content );
            $content = str_replace('{{h5_host}}',$h5_host ,$content );
            $content = str_replace('{{host}}',$host ,$content );
            $content = str_replace('{{external_link}}',$external_link ,$content );
//            Record::record_data($content, $data['news_id'].'_add_static_content');
            file_put_contents($file_name, $content);
            ob_end_clean();
        }
        return true;
    }

    //查看 某栏目下的 热门新闻信息
    function getOneNew($cid, $aid){
        $model = new News();
        $news_info = '';
        if($cid){
            $andwhere = 'news.column_id = '.$cid;
        }else{
            $andwhere = 'news.area_id = '.$aid;
        }

        $news_info = $model::find()
            ->join('LEFT JOIN', 'news_video', 'news.news_id = news_video.news_id')
            ->where("news.weight >= 70 and news.type not in (2,9,10,11,12,13,14) and news.top_status = 0 and news.special_news_id = 0 and news.status = 0 and ".$andwhere)
            ->select([
                "news.news_id",
                "title",
                "subtitle",
                "type",
                "column_id",
                "area_id",
            ])
            ->orderBy([
                'case when `year` is null then `year1` else `year` end' => SORT_DESC,
                'case when `month` is null then `month1` else `month` end'    => SORT_DESC,
                'case when `day` is null then `day1` else `day` end' 			=> SORT_DESC,
                'top_status' => SORT_DESC,
                'weight'=>SORT_DESC,
                'refresh_time' => SORT_DESC,
                'create_time' => SORT_DESC,
            ])
            ->asArray()
            ->one();

        return $news_info;
    }


    //获取栏目的热门新闻
    function getOneNews($cid, $aid){
        $model = new News();
        $news_info = '';
        if($cid){
            $andwhere = 'news.column_id = '.$cid;
        }else{
            $andwhere = 'news.area_id = '.$aid;
        }
        $trans_field = ' ,vrnews1.news_video.video_url as video_url,vrnews1.news_video.video_url1,vrnews1.news_video.video_url2,vrnews1.news_video.width,vrnews1.news_video.width1,vrnews1.news_video.width2,vrnews1.news_video.height,vrnews1.news_video.height1,vrnews1.news_video.height2,vrnews1.news_video.size,vrnews1.news_video.size1,vrnews1.news_video.size2 ,vrnews1.news_video.`file_id` as file_id';
        $news_info = $model::find()
            ->join('LEFT JOIN', 'news_video', 'news.news_id = news_video.news_id')
            ->where("news.weight >= 70 and news.type not in (2,9,10,11,12,13,14) and news.top_status = 0 and news.special_news_id = 0 and news.status = 0 and ".$andwhere)
            ->select([
                "news.news_id",
                "abstract as news_abstract",
                "title",
                "subtitle",
                "content",
                "cover_image",
                "reference_type",
                "reference_id",
                "type",
                "column_id",
                "area_id",
                "vote_id",
                "DATE_FORMAT(create_time,'%Y/%m/%d %H:%i:%s') as create_time",
                "type_id",
                "special_news_id",
                "top_status",
                "full_status",
                "full_title",
                "full_subtitle",
                "full_cover_image",
                "source_id",
                "source_name",
                "special_id",
                "special_type",
                "special_title",
                "special_abstract",
                "special_image",
                "thumbnail_url",
                "duration",
                "play_count",
                "category",
                "outer_url_ishot",
                "outer_url",
                "external_link",
                "year(create_time) as year1",
                "month(create_time) as month1",
                "day(create_time) as day1",
                "year(from_unixtime(refresh_time)) as year",
                "month(from_unixtime(refresh_time)) as month",
                "day(from_unixtime(refresh_time)) as day",
                "from_unixtime(refresh_time) as refresh_time".$trans_field
            ])
            ->orderBy([
                'case when `year` is null then `year1` else `year` end' => SORT_DESC,
                'case when `month` is null then `month1` else `month` end'    => SORT_DESC,
                'case when `day` is null then `day1` else `day` end' 			=> SORT_DESC,
                'top_status' => SORT_DESC,
                'weight'=>SORT_DESC,
                'refresh_time' => SORT_DESC,
                'create_time' => SORT_DESC,
            ])
            ->asArray()
            ->one();

        if($news_info['type'] == 5){
            $news_info['content'] = json_decode($news_info['content']);
        }else{
            $news_info['content'] = [];
        }

        if($news_info['title']){
            $news_info['title'] = html_entity_decode($news_info['title'], ENT_QUOTES);
        }

        if($news_info['vote_id']){
            //如果是投票 增加url字段跳转
            $news_info['vote_url'] = yii::$app->params['vote_url'].'?vote_id='.$news_info['vote_id'];
        }

        $news_info = $this->Processdata($news_info);
        return $news_info;
    }

    private function Processdata($news_info){
        //处理  返回值
        if($news_info['video_url']){
            unset($news_info['video_url1']);
            unset($news_info['video_url2']);
        }else if($news_info['video_url1']){
            $news_info['video_url'] = $news_info['video_url1'];
            unset($news_info['video_url1']);
            unset($news_info['video_url2']);
        }else if($news_info['video_url2']){
            $news_info['video_url'] = $news_info['video_url2'];
            unset($news_info['video_url1']);
            unset($news_info['video_url2']);
        }else{
            unset($news_info['video_url1']);
            unset($news_info['video_url2']);
        }
        if($news_info['height']){
            unset($news_info['height1']);
            unset($news_info['height2']);
        }else if($news_info['height1']){
            $news_info['height'] = $news_info['height1'];
            unset($news_info['height1']);
            unset($news_info['height2']);
        }else if($news_info['height2']){
            $news_info['height'] = $news_info['height2'];
            unset($news_info['height1']);
            unset($news_info['height2']);
        }else{
            unset($news_info['height1']);
            unset($news_info['height2']);
        }
        if($news_info['width']){
            unset($news_info['width1']);
            unset($news_info['width2']);
        }else if($news_info['width1']){
            $news_info['width'] = $news_info['width1'];
            unset($news_info['width1']);
            unset($news_info['width2']);
        }else if($news_info['width2']){
            $news_info['width'] = $news_info['width2'];
            unset($news_info['width1']);
            unset($news_info['width2']);
        }else{
            unset($news_info['width1']);
            unset($news_info['width2']);
        }
        if($news_info['size']){
            unset($news_info['size1']);
            unset($news_info['size2']);
        }else if($news_info['size1']){
            $news_info['size'] = $news_info['size1'];
            unset($news_info['size1']);
            unset($news_info['size2']);
        }else if($news_info['size2']){
            $news_info['size'] = $news_info['size2'];
            unset($news_info['size1']);
            unset($news_info['size2']);
        }else{
            unset($news_info['size1']);
            unset($news_info['size2']);
        }

        return $news_info;
    }

    //处理是否水印
    function setWatermark($news_id = null, $is_watermark = null, $content = null){
        if($news_id){
            $news = new News();
            $news_info = $news->find()->where(['news_id'=>$news_id])->asArray()->one();
            if($news_info){
                if($is_watermark == '0'){ //不添加水印 将含有 /s 的去掉
                    $content = json_encode($content);
                    $content = preg_replace('/(src=\\\"http:\\\\\/\\\\\/vrlive-.*?(jpg|png))\\\\\/s/', '$1', $content);
                    return json_decode($content);
                }else{ //添加水印 将没有 /s 的添加
                    $content = json_encode($content);
                    $content = preg_replace('/(src=\\\"http:\\\\\/\\\\\/vrlive-.*?(jpg|png))\\\\\/s/', '$1', $content);
                    $content = preg_replace('/(src=\\\"http:\\\\\/\\\\\/vrlive-.*?(jpg|png))/', '$1/s', $content);
                    return json_decode($content);
                }
            }
        }else{
            //添加水印  加 /s
            if($is_watermark == 1){
                $content = json_encode($content);
                $content = preg_replace('/(src=\\\"http:\\\\\/\\\\\/vrlive-.*?(jpg|png))/', '$1/s', $content);
                return json_decode($content);
            }
        }
        return $content;
    }

    function setWatermark_img($news_id = null, $is_watermark = null, $content = null){
        // jpg/png 图片处理
        if($news_id){
            $news = new News();
            $news_info = $news->find()->where(['news_id'=>$news_id])->asArray()->one();
            if($news_info){
                if($is_watermark == '0'){ //不添加水印 将含有 /s 的去掉
                    $content = json_encode($content);
                    $content = preg_replace('/(http:\\\\\/\\\\\/vrlive-.*?(jpg|png))\\\\\/s/', '$1', $content);
                    return json_decode($content);
                }else{ //添加水印 将没有 /s 的添加
                    $content = json_encode($content);
                    $content = preg_replace('/(http:\\\\\/\\\\\/vrlive-.*?(jpg|png))\\\\\/s/', '$1', $content);
                    $content = preg_replace('/(http:\\\\\/\\\\\/vrlive-.*?(jpg|png))/', '$1/s', $content);
                    return json_decode($content);
                }
            }
        }else{
            //添加水印  加 /s
            if($is_watermark == 1){
                $content = json_encode($content);
                $content = preg_replace('/(http:\\\\\/\\\\\/vrlive-.*?(jpg|png))/', '$1/s', $content);
                return json_decode($content);
            }
        }
        return $content;
    }

    //引用：修改 视频类型 处理
    function checkVideo($news_id,$category,$video_info){
        $news_video = new NewsVideo();
        $news_video->deleteAll(['news_id'=>$news_id]);

        $news_video['category']    = $category;
        $news_video['news_id']     = $news_id;
        $news_video['update_time'] = date("Y-m-d H:i:s");
        if($video_info) {
            $news_video['duration'] = $video_info[0]['duration'];
            $news_video['file_name'] = $video_info[0]['file_name'];
            $news_video['file_id'] = $video_info[0]['file_id'];
            $news_video['height'] = $video_info[0]['height'];
            $news_video['width'] = $video_info[0]['width'];
            $news_video['size'] = $video_info[0]['size'];
            $news_video['thumbnail_url'] = $video_info[0]['image_url'];
            $news_video['video_url'] = $video_info[0]['url'];

            $news_video->save();
        }
        return true;
    }

    /**
     *  获取新闻类型
     */
    public function _getNewType($val){
        if(!$val['type'] || $val['type'] == 0){
            return '未知';
        }
        if($val['type'] == 1){
            return '快捷发布';
        }elseif($val['type'] == 2){
            return '轮播图';
        }elseif($val['type'] == 3){
            return '专题';
        }elseif($val['type'] == 4){
            if($val['category'] == 1 || empty($val['category'])){
                return '普通视频';
            }else{
                return 'VR视频';
            }
        }elseif($val['type'] == 5){
            return '图集';
        }elseif($val['type'] == 6){
            return '文本';
        }elseif($val['type'] == 7){
            return '图文';
        }elseif($val['type'] == 9){
            return '视频直播';
        }elseif($val['type'] == 10){
            return 'VR直播';
        }elseif($val['type'] == 11){
            return '图文直播';
        }elseif($val['type'] == 12){
            return '视频加图文直播';
        }elseif($val['type'] == 13){
            return 'VR加图文直播';
        }else{
            return '未知';
        }
    }

}
