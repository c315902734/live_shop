<?php
namespace common\models;

use Yii;
use common\models\OauthAccessTokens;
use yii\db\Query;

/**
 * News model
 */
class NewsVideo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news_video';
    }


    public static function getDb()
    {
        return Yii::$app->vrnews1;
    }

    public static function getVideoInfo($count,$page,$is_pc,$num,$type_id=0,$news_id='', $is_app=0, $user_id = '', $is_more='',$special_id=0){
        $redis  = Yii::$app->cache;
        $is_areas  = 0;
        $column_id = 9;
        $last_update_time = Yii::$app->params['environment']."_new_list_".$is_areas.'_'.$column_id.'_update';
        $news_video_column_update = $redis->get($last_update_time); //最新更新时间
        $name = Yii::$app->params['environment']."_video_list_".$is_areas.'_'.$column_id.'_'.$type_id.'_'.$is_pc.'_'.$special_id. '_'.$news_video_column_update;
        $info_list = $redis->get($name);
        if($info_list){
//            return $info_list;
        }
        $trans_field = ' ,vrnews1.news_video.video_url as video_url,vrnews1.news_video.video_url1,vrnews1.news_video.video_url2,vrnews1.news_video.width,vrnews1.news_video.width1,vrnews1.news_video.width2,vrnews1.news_video.height,vrnews1.news_video.height1,vrnews1.news_video.height2,vrnews1.news_video.size,vrnews1.news_video.size1,vrnews1.news_video.size2 ,vrnews1.news_video.`file_id` as file_id,vrnews1.news.abstract,vrnews1.news.abstract as abstracts';
        if(!$info_list){
            if ($is_pc == 1) {
                $pub_where = " and web_pub = 1 ";
            } else {
                $pub_where = " and app_pub = 1 ";
            }
            $pub_where .= " and news.special_news_id = '".$special_id."' ";
            if($type_id) {
                $pub_where .= " and news.type_id = '" . $type_id . "'";
            }
            if($is_app == 1){
                $pub_where .= " and news.type != 3";
            }
            $where_area = " and  (news.area_id = 0 or news.area_id is null)";
            $trans_where = " and ( case when news.type=4  then ( (video_url<>'' or video_url1<>'' or video_url2<>'') and file_id<> 'null') or ( reference_type <> null and reference_id <> null)  or ( reference_type is not null and reference_id is not  null) else file_id is null end)";
            $query = new Query();
            $query->select(["vrnews1.news.news_id,news.type,title,subtitle,cover_image,DATE_FORMAT(vrnews1.news.create_time,'%Y/%m/%d %H:%i') as create_time,reference_type,reference_id,source_id,source_name,thumbnail_url,duration,play_count,category,outer_url_ishot,outer_url,external_link,year(vrnews1.news.create_time) as year1,month(vrnews1.news.create_time) as month1,day(vrnews1.news.create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time, vrnews1.news_video.status as video_status, vrnews1.news.live_id, vrnews1.news.vote_id" . $trans_field])->from('vrnews1.news');
            $query->leftJoin('vrnews1.news_video','vrnews1.news.news_id = vrnews1.news_video.news_id');

            $query->where("news.weight >= 70 and news.status=0 and column_id = $column_id and  news.type != 2 and vote_id = 0".$where_area.$trans_where.$pub_where);

            $query->orderBy([
                'top_status' => SORT_DESC,
                'case  when `year` is null then `year1` else `year` end' => SORT_DESC,
                'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
                'case  when `day` is null then `day1` else `day` end' => SORT_DESC,
                'vrnews1.news.weight' => SORT_DESC,
                'refresh_time' => SORT_DESC,
                'create_time' => SORT_DESC]);
            $command = $query->createCommand();
            $info_list = $command->queryAll();
        }
//        var_dump($info_list);exit();
        $start = 0;
        $new_list = array();
        if (count($info_list) > 0) {
            foreach ($info_list as $key => $value) {
                if ($value['news_id'] == $news_id) {
                    $start = $key;
                }
            }
            if($is_app){
                if($is_more && $start+1 >= count($info_list)){
                    $new_list = array();
                }elseif ($is_more && $start+1 < count($info_list)){
                    $new_list = array_slice($info_list, $start+1, $count);
                }else{
                    $new_list = array_slice($info_list, $start, $count);
                }
            }else{
                if(!$is_pc){
                    $new_list = array_slice($info_list, $start+1, $count);
                }else{
                    $new_list = array_slice($info_list, $start, $count);
                }
            }
            if(count($new_list)) {
                foreach ($new_list as $key => $value) {
                    $new_list[$key]['share_title'] = $value['title'] . ' |法制与新闻客户端';
                    if (!empty($user_id)) {
                        //当前用户是否可点赞
                        $praise_count = NewsPraise::find()->where(['news_id'=>$news_id,'status'=>'1', 'news_type'=>1])->count();
                        $new_list[$key]['praise_count'] =  $praise_count > 0 ? $praise_count : '0';
                        $user_praise_count = NewsPraise::find()->where(['news_id' => $value['news_id'], 'status' => '1', 'user_id' => $user_id])->count();
                        $new_list[$key]['user_is_praise'] = $user_praise_count > 0 ? '1' : '0';
                        $is_collect = NewsUserCollect::find()
                            ->where(["news_id" => $value['news_id'], "user_id" => $user_id, 'status' => 1])
                            ->asArray()->one();
                        if ($is_collect) {
                            $new_list[$key]['collect_id'] = $is_collect['collect_id'];
                        }else{
                            $new_list[$key]['collect_id'] = '';
                        }
                    } else {
                        $new_list[$key]['collect_id'] = '';
                        $new_list[$key]['user_is_praise'] = '0';
                        $new_list[$key]['praise_count'] = '0';
                    }
                    $new_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '/y' : '';
                    //列表 如果是视频并且引用了其他视频  取出对应的植
                    if ($value['type'] == 4 && $value['reference_id']) {
                        $video_info = News::_getNewsInfo($value['reference_id']);
                        $value['thumbnail_url'] = $video_info['thumbnail_url'];
                        $value['duration'] = $video_info['duration'];
                        $value['category'] = $video_info['category'];
                        $value['video_url'] = $video_info['video_url'];
                        $value['video_url1'] = $video_info['video_url1'];
                        $value['video_url2'] = $video_info['video_url2'];
                        $value['width'] = $video_info['width'];
                        $value['width1'] = $video_info['width1'];
                        $value['width2'] = $video_info['width2'];
                        $value['height'] = $video_info['height'];
                        $value['height1'] = $video_info['height1'];
                        $value['height2'] = $video_info['height2'];
                        $value['file_id'] = $video_info['file_id'];
                    }

                    //处理  返回值
                    if ($value['video_url']) {
                        unset($new_list[$key]['video_url1']);
                        unset($new_list[$key]['video_url2']);
                    } else if ($value['video_url1']) {
                        $new_list[$key]['video_url'] = $value['video_url1'];
                        unset($new_list[$key]['video_url1']);
                        unset($new_list[$key]['video_url2']);
                    } else if ($value['video_url2']) {
                        $new_list[$key]['video_url'] = $value['video_url2'];
                        unset($new_list[$key]['video_url1']);
                        unset($new_list[$key]['video_url2']);
                    } else {
                        unset($new_list[$key]['video_url1']);
                        unset($new_list[$key]['video_url2']);
                    }
                    if ($value['height']) {
                        unset($new_list[$key]['height1']);
                        unset($new_list[$key]['height2']);
                    } else if ($value['height1']) {
                        $new_list[$key]['height'] = $value['height1'];
                        unset($new_list[$key]['height1']);
                        unset($new_list[$key]['height2']);
                    } else if ($value['height2']) {
                        $new_list[$key]['height'] = $value['height2'];
                        unset($new_list[$key]['height1']);
                        unset($new_list[$key]['height2']);
                    } else {
                        unset($new_list[$key]['height1']);
                        unset($new_list[$key]['height2']);
                    }
                    if ($value['width']) {
                        unset($new_list[$key]['width1']);
                        unset($new_list[$key]['width2']);
                    } else if ($value['width1']) {
                        $new_list[$key]['width'] = $value['width1'];
                        unset($new_list[$key]['width1']);
                        unset($new_list[$key]['width2']);
                    } else if ($value['width2']) {
                        $new_list[$key]['width'] = $value['width2'];
                        unset($new_list[$key]['width1']);
                        unset($new_list[$key]['width2']);
                    } else {
                        unset($new_list[$key]['width1']);
                        unset($new_list[$key]['width2']);
                    }
                    if ($value['size']) {
                        unset($new_list[$key]['size1']);
                        unset($new_list[$key]['size2']);
                    } else if ($value['size1']) {
                        $new_list[$key]['size'] = $value['size1'];
                        unset($new_list[$key]['size1']);
                        unset($new_list[$key]['size2']);
                    } else if ($value['size2']) {
                        $new_list[$key]['size'] = $value['size2'];
                        unset($new_list[$key]['size1']);
                        unset($new_list[$key]['size2']);
                    } else {
                        unset($new_list[$key]['size1']);
                        unset($new_list[$key]['size2']);
                    }
                }
            }
            unset($value);
            if(!empty($news_id) && !$is_app){
                $news_info = News::find()->leftJoin('vrnews1.news_video', 'vrnews1.news_video.news_id = news.news_id')->select(["vrnews1.news.news_id,news.type,title,subtitle,cover_image,DATE_FORMAT(vrnews1.news.create_time,'%Y/%m/%d %H:%i') as create_time,reference_type,reference_id,source_id,source_name,thumbnail_url,duration,play_count,category,outer_url_ishot,outer_url,external_link,year(vrnews1.news.create_time) as year1,month(vrnews1.news.create_time) as month1,day(vrnews1.news.create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time, vrnews1.news_video.status as video_status, vrnews1.news.live_id, vrnews1.news.vote_id" . $trans_field])->where(['vrnews1.news.news_id' => $news_id])->asArray()->one();
                if (!empty($user_id)) {
                    //当前用户是否可点赞
                    $praise_count = NewsPraise::find()->where(['news_id'=>$news_id,'status'=>'1', 'news_type'=>1])->count();
                    $news_info['praise_count'] =  $praise_count > 0 ? $praise_count : '0';
                    $user_praise_count = NewsPraise::find()->where(['news_id' => $news_id, 'status' => '1', 'user_id' => $user_id])->count();
                    $news_info['user_is_praise'] = $user_praise_count > 0 ? '1' : '0';
                    $is_collect = NewsUserCollect::find()
                        ->where(["news_id" => $news_id, "user_id" => $user_id, 'status' => 1])
                        ->asArray()->one();
                    if ($is_collect) {
                        $news_info['collect_id'] = $is_collect['collect_id'];
                    }else{
                        $news_info['collect_id'] = '';
                    }
                } else {
                    $news_info['collect_id'] = '';
                    $news_info['user_is_praise'] = '0';
                    $news_info['praise_count']   = '0';
                }
                $info_lists['news_info'] = $news_info;
                $info_lists['list']      = $new_list;
                $redis->set($name, $info_list, 86400);
                return $info_lists;
            }
        }
        return $new_list;
    }

    /*
     * 获取轮播图新闻详情
     */
    public static function getBannerInfo($size, $news_id){
        $result = array();
        $pub_where = " and web_pub = 1 ";
        $column_id = 9;
        $type_info = NewsColumnType::find()->where(['column_id' => 9, 'status' => 1])->orderBy('weight desc')->asArray()->one();
        $trans_field = ' ,vrnews1.news_video.video_url as video_url,vrnews1.news_video.video_url1,vrnews1.news_video.video_url2,vrnews1.news_video.width,vrnews1.news_video.width1,vrnews1.news_video.width2,vrnews1.news_video.height,vrnews1.news_video.height1,vrnews1.news_video.height2,vrnews1.news_video.size,vrnews1.news_video.size1,vrnews1.news_video.size2 ,vrnews1.news_video.`file_id` as file_id';

        $where_area = " and  (news.area_id = 0 or news.area_id is null)";
        $trans_where = " and ( case when news.type=4  then ( (video_url<>'' or video_url1<>'' or video_url2<>'') and file_id<> 'null') or ( reference_type <> null and reference_id <> null)  or ( reference_type is not null and reference_id is not  null) else file_id is null end)";
        $query = new Query();
        $query->select(["vrnews1.news.news_id,vrnews1.news.abstract,news.type,title,subtitle,cover_image,DATE_FORMAT(vrnews1.news.create_time,'%Y/%m/%d %H:%i') as create_time,reference_type,reference_id,source_id,source_name,thumbnail_url,duration,play_count,category,outer_url_ishot,outer_url,external_link,year(vrnews1.news.create_time) as year1,month(vrnews1.news.create_time) as month1,day(vrnews1.news.create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time, vrnews1.news_video.status as video_status, vrnews1.news.live_id, vrnews1.news.vote_id" . $trans_field])->from('vrnews1.news');
        $query->leftJoin('vrnews1.news_video','vrnews1.news.news_id = vrnews1.news_video.news_id');

        $query->where("news.weight >= 70 and news.status=0 and column_id = $column_id and type_id = ".$type_info['type_id'] ." and  news.type != 2  and news.special_news_id = 0 and vote_id = 0".$where_area.$trans_where.$pub_where);

        $query->orderBy([
            'top_status' => SORT_DESC,
            'case  when `year` is null then `year1` else `year` end' => SORT_DESC,
            'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
            'case  when `day` is null then `day1` else `day` end' => SORT_DESC,
            'vrnews1.news.weight' => SORT_DESC,
            'refresh_time' => SORT_DESC,
            'create_time' => SORT_DESC]);
        $query->limit($size-1);
        $command = $query->createCommand();
        $info_list = $command->queryAll();
//        var_dump($info_list);exit();
        $start = 0;
        if (count($info_list) > 0) {
            foreach ($info_list as $key => $value) {
                $new_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '/y' : '';
                //列表 如果是视频并且引用了其他视频  取出对应的植
                if ($value['type'] == 4 && $value['reference_id']) {
                    $video_info = News::_getNewsInfo($value['reference_id']);
                    $value['thumbnail_url'] = $video_info['thumbnail_url'];
                    $value['duration'] = $video_info['duration'];
                    $value['category'] = $video_info['category'];
                    $value['video_url'] = $video_info['video_url'];
                    $value['video_url1'] = $video_info['video_url1'];
                    $value['video_url2'] = $video_info['video_url2'];
                    $value['width'] = $video_info['width'];
                    $value['width1'] = $video_info['width1'];
                    $value['width2'] = $video_info['width2'];
                    $value['height'] = $video_info['height'];
                    $value['height1'] = $video_info['height1'];
                    $value['height2'] = $video_info['height2'];
                    $value['file_id'] = $video_info['file_id'];
                }

                //处理  返回值
                if ($value['video_url']) {
                    unset($info_list[$key]['video_url1']);
                    unset($info_list[$key]['video_url2']);
                } else if ($value['video_url1']) {
                    $info_list[$key]['video_url'] = $value['video_url1'];
                    unset($info_list[$key]['video_url1']);
                    unset($info_list[$key]['video_url2']);
                } else if ($value['video_url2']) {
                    $info_list[$key]['video_url'] = $value['video_url2'];
                    unset($info_list[$key]['video_url1']);
                    unset($info_list[$key]['video_url2']);
                } else {
                    unset($info_list[$key]['video_url1']);
                    unset($info_list[$key]['video_url2']);
                }
                if ($value['height']) {
                    unset($info_list[$key]['height1']);
                    unset($info_list[$key]['height2']);
                } else if ($value['height1']) {
                    $info_list[$key]['height'] = $value['height1'];
                    unset($info_list[$key]['height1']);
                    unset($info_list[$key]['height2']);
                } else if ($value['height2']) {
                    $info_list[$key]['height'] = $value['height2'];
                    unset($info_list[$key]['height1']);
                    unset($info_list[$key]['height2']);
                } else {
                    unset($info_list[$key]['height1']);
                    unset($info_list[$key]['height2']);
                }
                if ($value['width']) {
                    unset($info_list[$key]['width1']);
                    unset($info_list[$key]['width2']);
                } else if ($value['width1']) {
                    $info_list[$key]['width'] = $value['width1'];
                    unset($info_list[$key]['width1']);
                    unset($info_list[$key]['width2']);
                } else if ($value['width2']) {
                    $info_list[$key]['width'] = $value['width2'];
                    unset($info_list[$key]['width1']);
                    unset($info_list[$key]['width2']);
                } else {
                    unset($info_list[$key]['width1']);
                    unset($info_list[$key]['width2']);
                }
                if ($value['size']) {
                    unset($info_list[$key]['size1']);
                    unset($info_list[$key]['size2']);
                } else if ($value['size1']) {
                    $info_list[$key]['size'] = $value['size1'];
                    unset($info_list[$key]['size1']);
                    unset($info_list[$key]['size2']);
                } else if ($value['size2']) {
                    $info_list[$key]['size'] = $value['size2'];
                    unset($info_list[$key]['size1']);
                    unset($info_list[$key]['size2']);
                } else {
                    unset($info_list[$key]['size1']);
                    unset($info_list[$key]['size2']);
                }
            }
            unset($value);
            $result['type_id'] = $type_info['type_id'];
            $news_info = News::find()->leftJoin('vrnews1.news_video', 'vrnews1.news_video.news_id = news.news_id')->select(["vrnews1.news.news_id,vrnews1.news.abstract,news.type,title,subtitle,cover_image,DATE_FORMAT(vrnews1.news.create_time,'%Y/%m/%d %H:%i') as create_time,reference_type,reference_id,source_id,source_name,thumbnail_url,duration,play_count,category,outer_url_ishot,outer_url,external_link,year(vrnews1.news.create_time) as year1,month(vrnews1.news.create_time) as month1,day(vrnews1.news.create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time, vrnews1.news_video.status as video_status, vrnews1.news.live_id, vrnews1.news.vote_id" . $trans_field])->where(['vrnews1.news.news_id' => $news_id])->asArray()->all();
            $result['list'] = array_merge($news_info, $info_list);

        }
        return $result;
    }

    /**
     * pc视频列表换一换
     */

    public static function changeVideoInfo($count,$page,$is_pc,$num,$type_id=0){
        $redis  = Yii::$app->cache;
        $is_areas  = 0;
        $column_id = 9;
        $last_update_time = Yii::$app->params['environment']."_new_list_".$is_areas.'_'.$column_id.'_update';
        $news_video_column_update = $redis->get($last_update_time).time(); //最新更新时间
        $name = Yii::$app->params['environment']."_change_video_list_".$is_areas.'_'.$column_id.'_'.$type_id.'_'.$page.'_'.$num.'_'.$news_video_column_update;
        $info_list = $redis->get($name);
        if($info_list && count($info_list)){
            return $info_list;
        }else {
            $offset = ($page - 1) * $count;
            if ($is_pc == 1) {
                $pub_where = " and web_pub = 1 ";
            } else {
                $pub_where = " and app_pub = 1 ";
            }
            if($type_id) {
                $pub_where .= " and news.type_id = '" . $type_id . "'";
            }
            $trans_where = " and ( case when news.type=4  then ( (video_url<>'' or video_url1<>'' or video_url2<>'') and file_id<> 'null') or ( reference_type <> null and reference_id <> null)  or ( reference_type is not null and reference_id is not  null) else file_id is null end)";
            $trans_field = ' ,vrnews1.news_video.video_url as video_url,vrnews1.news_video.video_url1,vrnews1.news_video.video_url2,vrnews1.news_video.width,vrnews1.news_video.width1,vrnews1.news_video.width2,vrnews1.news_video.height,vrnews1.news_video.height1,vrnews1.news_video.height2,vrnews1.news_video.size,vrnews1.news_video.size1,vrnews1.news_video.size2,vrnews1.news.abstract ,vrnews1.news_video.`file_id` as file_id';

            $query = new Query();
            $query->select(["vrnews1.news.news_id,news.type,title,subtitle,cover_image,DATE_FORMAT(vrnews1.news.create_time,'%Y/%m/%d %H:%i') as create_time,reference_type,reference_id,source_id,source_name,thumbnail_url,duration,play_count,category,outer_url_ishot,outer_url,external_link,year(vrnews1.news.create_time) as year1,month(vrnews1.news.create_time) as month1,day(vrnews1.news.create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time, vrnews1.news_video.status as video_status, vrnews1.news.live_id, vrnews1.news.vote_id" . $trans_field])->from('vrnews1.news');
            $query->leftJoin('vrnews1.news_video', 'vrnews1.news.news_id = vrnews1.news_video.news_id');
            //客户端搜索  加入栏目权重条件
            $query->leftJoin('vrnews1.news_column', 'vrnews1.news.column_id = vrnews1.news_column.column_id');
            $query->where(" 1=1 ");
            $query->andWhere(" news.column_id = '" . $column_id . "'");

            $query->andWhere("news.weight >= 70 and news.status=0 and  (news.type != 3 or (news.type = 3 and news.special_news_id != 0)) " . $trans_where . $pub_where);
            $query->groupBy('news.title');

            $query->orderBy([
                'top_status' => SORT_DESC,
                'case  when `year` is null then `year1` else `year` end' => SORT_DESC,
                'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
                'case  when `day` is null then `day1` else `day` end' => SORT_DESC,
                'vrnews1.news.weight' => SORT_DESC,
                'refresh_time' => SORT_DESC,
                'create_time' => SORT_DESC]);
            $query->offset($offset);
            $query->limit($count);
            $command = $query->createCommand();
            $info_list = $command->queryAll();
            if (count($info_list) > 0) {
                foreach ($info_list as $key => $value) {
                    $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '/y' : '';
                    //列表 如果是视频并且引用了其他视频  取出对应的植
                    if ($value['type'] == 4 && $value['reference_id']) {
                        $video_info = News::_getNewsInfo($value['reference_id']);
                        $value['thumbnail_url'] = $video_info['thumbnail_url'];
                        $value['duration'] = $video_info['duration'];
                        $value['category'] = $video_info['category'];
                        $value['video_url'] = $video_info['video_url'];
                        $value['video_url1'] = $video_info['video_url1'];
                        $value['video_url2'] = $video_info['video_url2'];
                        $value['width'] = $video_info['width'];
                        $value['width1'] = $video_info['width1'];
                        $value['width2'] = $video_info['width2'];
                        $value['height'] = $video_info['height'];
                        $value['height1'] = $video_info['height1'];
                        $value['height2'] = $video_info['height2'];
                        $value['file_id'] = $video_info['file_id'];
                    }

                    //处理  返回值
                    if ($value['video_url']) {
                        unset($info_list[$key]['video_url1']);
                        unset($info_list[$key]['video_url2']);
                    } else if ($value['video_url1']) {
                        $info_list[$key]['video_url'] = $value['video_url1'];
                        unset($info_list[$key]['video_url1']);
                        unset($info_list[$key]['video_url2']);
                    } else if ($value['video_url2']) {
                        $info_list[$key]['video_url'] = $value['video_url2'];
                        unset($info_list[$key]['video_url1']);
                        unset($info_list[$key]['video_url2']);
                    } else {
                        unset($info_list[$key]['video_url1']);
                        unset($info_list[$key]['video_url2']);
                    }
                    if ($value['height']) {
                        unset($info_list[$key]['height1']);
                        unset($info_list[$key]['height2']);
                    } else if ($value['height1']) {
                        $info_list[$key]['height'] = $value['height1'];
                        unset($info_list[$key]['height1']);
                        unset($info_list[$key]['height2']);
                    } else if ($value['height2']) {
                        $info_list[$key]['height'] = $value['height2'];
                        unset($info_list[$key]['height1']);
                        unset($info_list[$key]['height2']);
                    } else {
                        unset($info_list[$key]['height1']);
                        unset($info_list[$key]['height2']);
                    }
                    if ($value['width']) {
                        unset($info_list[$key]['width1']);
                        unset($info_list[$key]['width2']);
                    } else if ($value['width1']) {
                        $info_list[$key]['width'] = $value['width1'];
                        unset($info_list[$key]['width1']);
                        unset($info_list[$key]['width2']);
                    } else if ($value['width2']) {
                        $info_list[$key]['width'] = $value['width2'];
                        unset($info_list[$key]['width1']);
                        unset($info_list[$key]['width2']);
                    } else {
                        unset($info_list[$key]['width1']);
                        unset($info_list[$key]['width2']);
                    }
                    if ($value['size']) {
                        unset($info_list[$key]['size1']);
                        unset($info_list[$key]['size2']);
                    } else if ($value['size1']) {
                        $info_list[$key]['size'] = $value['size1'];
                        unset($info_list[$key]['size1']);
                        unset($info_list[$key]['size2']);
                    } else if ($value['size2']) {
                        $info_list[$key]['size'] = $value['size2'];
                        unset($info_list[$key]['size1']);
                        unset($info_list[$key]['size2']);
                    } else {
                        unset($info_list[$key]['size1']);
                        unset($info_list[$key]['size2']);
                    }
                }
                unset($value);
                $redis->set($name, $info_list, 86400);
            }
        }
        return $info_list;
    }

    public static function getVideoDetail($news_id, $type_id, $option, $count, $is_pc=1,$user_id='', $special_id=0){
        $redis  = Yii::$app->cache;
        $is_areas  = 0;
        $column_id = 9;
        $last_update_time = Yii::$app->params['environment']."_new_list_".$is_areas.'_'.$column_id.'_update';
        $news_video_column_update = $redis->get($last_update_time).time(); //最新更新时间
        $name = Yii::$app->params['environment']."_video_detail_".$is_areas.'_'.$column_id.'_'.$type_id.'_'.$is_pc.'_'.$special_id. '_' .$news_id .'_'.$news_video_column_update;
        $info_list = $redis->get($name);
        if($info_list){
            return $info_list;
        }
        $trans_field = ' ,vrnews1.news_video.video_url as video_url,vrnews1.news_video.video_url1,vrnews1.news_video.video_url2,vrnews1.news_video.width,vrnews1.news_video.width1,vrnews1.news_video.width2,vrnews1.news_video.height,vrnews1.news_video.height1,vrnews1.news_video.height2,vrnews1.news_video.size,vrnews1.news_video.size1,vrnews1.news_video.size2 ,vrnews1.news_video.`file_id` as file_id';
        if(!$info_list){
            if ($is_pc == 1) {
                $pub_where = " and web_pub = 1 ";
            } else {
                $pub_where = " and app_pub = 1 ";
            }
            if($type_id) {
                $pub_where .= " and news.type_id = '" . $type_id . "'";
            }
            $pub_where .= " and news.special_news_id = '".$special_id."' ";
            $where_area = " and  (news.area_id = 0 or news.area_id is null)";
            $trans_where = " and ( case when news.type=4  then ( (video_url<>'' or video_url1<>'' or video_url2<>'') and file_id<> 'null') or ( reference_type <> null and reference_id <> null)  or ( reference_type is not null and reference_id is not  null) else file_id is null end)";
            $query = new Query();
            $query->select(["vrnews1.news.news_id,vrnews1.news.abstract,news.keywords,news.type,title,subtitle,cover_image,DATE_FORMAT(vrnews1.news.create_time,'%Y/%m/%d %H:%i') as create_time,reference_type,reference_id,source_id,source_name,thumbnail_url,duration,play_count,category,outer_url_ishot,outer_url,external_link,year(vrnews1.news.create_time) as year1,month(vrnews1.news.create_time) as month1,day(vrnews1.news.create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time, vrnews1.news_video.status as video_status, vrnews1.news.live_id, vrnews1.news.vote_id" . $trans_field])->from('vrnews1.news');
            $query->leftJoin('vrnews1.news_video','vrnews1.news.news_id = vrnews1.news_video.news_id');

            $query->where("news.weight >= 70 and news.status=0 and column_id = $column_id and  news.type != 2 and vote_id = 0".$where_area.$trans_where.$pub_where);

            $query->orderBy([
                'top_status' => SORT_DESC,
                'case  when `year` is null then `year1` else `year` end' => SORT_DESC,
                'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
                'case  when `day` is null then `day1` else `day` end' => SORT_DESC,
                'vrnews1.news.weight' => SORT_DESC,
                'refresh_time' => SORT_DESC,
                'create_time' => SORT_DESC]);
            $command = $query->createCommand();
            $info_list = $command->queryAll();
        }
        $start = 0;
        if (count($info_list) > 0) {
            foreach ($info_list as $key => $value) {
                if ($value['news_id'] == $news_id) {
                    $start = $key;
                }
            }
            if($option == 1){
                if($start < $count){
                    $new_list = array_slice($info_list, 0, $count);
                }else{
                    $new_list = array();
                    for($i=$count;$i>=1;$i--) {
                        if(count($new_list) < $count){
                            $new_list[] = $info_list[$start-$i];
                        }
                    }
                }
            }else{
                $new_list = array_slice($info_list, $start+1, $count);
            }

            foreach ($new_list as $key => $value) {
                if(!empty($user_id)){
                    //当前用户是否可点赞
                    $user_praise_count = NewsPraise::find()->where(['news_id'=>$value['news_id'],'status'=>'1','user_id'=>$user_id])->count();
                    $new_list[$key]['user_is_praise'] =  $user_praise_count > 0 ? '1' : '0';
                    $is_collect = NewsUserCollect::find()
                        ->where(["news_id" => $value['news_id'], "user_id" => $user_id, 'status' => 1])
                        ->asArray()->one();
                    if($is_collect){
                        $new_list[$key]['collect_id'] = $is_collect['collect_id'];
                    }
                }else{
                    $new_list[$key]['collect_id']     = '';
                    $new_list[$key]['user_is_praise'] = '0';
                }
                $new_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '/y' : '';
                //列表 如果是视频并且引用了其他视频  取出对应的植
                if ($value['type'] == 4 && $value['reference_id']) {
                    $video_info = News::_getNewsInfo($value['reference_id']);
                    $value['thumbnail_url'] = $video_info['thumbnail_url'];
                    $value['duration'] = $video_info['duration'];
                    $value['category'] = $video_info['category'];
                    $value['video_url'] = $video_info['video_url'];
                    $value['video_url1'] = $video_info['video_url1'];
                    $value['video_url2'] = $video_info['video_url2'];
                    $value['width'] = $video_info['width'];
                    $value['width1'] = $video_info['width1'];
                    $value['width2'] = $video_info['width2'];
                    $value['height'] = $video_info['height'];
                    $value['height1'] = $video_info['height1'];
                    $value['height2'] = $video_info['height2'];
                    $value['file_id'] = $video_info['file_id'];
                }

                //处理  返回值
                if ($value['video_url']) {
                    unset($new_list[$key]['video_url1']);
                    unset($new_list[$key]['video_url2']);
                } else if ($value['video_url1']) {
                    $new_list[$key]['video_url'] = $value['video_url1'];
                    unset($new_list[$key]['video_url1']);
                    unset($new_list[$key]['video_url2']);
                } else if ($value['video_url2']) {
                    $new_list[$key]['video_url'] = $value['video_url2'];
                    unset($new_list[$key]['video_url1']);
                    unset($new_list[$key]['video_url2']);
                } else {
                    unset($new_list[$key]['video_url1']);
                    unset($new_list[$key]['video_url2']);
                }
                if ($value['height']) {
                    unset($new_list[$key]['height1']);
                    unset($new_list[$key]['height2']);
                } else if ($value['height1']) {
                    $new_list[$key]['height'] = $value['height1'];
                    unset($new_list[$key]['height1']);
                    unset($new_list[$key]['height2']);
                } else if ($value['height2']) {
                    $new_list[$key]['height'] = $value['height2'];
                    unset($new_list[$key]['height1']);
                    unset($new_list[$key]['height2']);
                } else {
                    unset($new_list[$key]['height1']);
                    unset($new_list[$key]['height2']);
                }
                if ($value['width']) {
                    unset($new_list[$key]['width1']);
                    unset($new_list[$key]['width2']);
                } else if ($value['width1']) {
                    $new_list[$key]['width'] = $value['width1'];
                    unset($new_list[$key]['width1']);
                    unset($new_list[$key]['width2']);
                } else if ($value['width2']) {
                    $new_list[$key]['width'] = $value['width2'];
                    unset($new_list[$key]['width1']);
                    unset($new_list[$key]['width2']);
                } else {
                    unset($new_list[$key]['width1']);
                    unset($new_list[$key]['width2']);
                }
                if ($value['size']) {
                    unset($new_list[$key]['size1']);
                    unset($new_list[$key]['size2']);
                } else if ($value['size1']) {
                    $new_list[$key]['size'] = $value['size1'];
                    unset($new_list[$key]['size1']);
                    unset($new_list[$key]['size2']);
                } else if ($value['size2']) {
                    $new_list[$key]['size'] = $value['size2'];
                    unset($new_list[$key]['size1']);
                    unset($new_list[$key]['size2']);
                } else {
                    unset($new_list[$key]['size1']);
                    unset($new_list[$key]['size2']);
                }
            }
            unset($value);
            if(!empty($news_id)){
                $news_info = News::find()->leftJoin('vrnews1.news_video', 'vrnews1.news_video.news_id = news.news_id')->select(["vrnews1.news.news_id,vrnews1.news.abstract,news.keywords,news.type,title,subtitle,cover_image,DATE_FORMAT(vrnews1.news.create_time,'%Y/%m/%d %H:%i') as create_time,reference_type,reference_id,source_id,source_name,thumbnail_url,duration,play_count,category,outer_url_ishot,outer_url,external_link,year(vrnews1.news.create_time) as year1,month(vrnews1.news.create_time) as month1,day(vrnews1.news.create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time, vrnews1.news_video.status as video_status, vrnews1.news.live_id, vrnews1.news.vote_id" . $trans_field])->where(['vrnews1.news.news_id' => $news_id])->asArray()->one();
                if(!empty($user_id)){
                    //当前用户是否可点赞
                    $user_praise_count = NewsPraise::find()->where(['news_id'=>$news_id,'status'=>'1','user_id'=>$user_id])->count();
                    $news_info['user_is_praise'] =  $user_praise_count > 0 ? '1' : '0';
                    $is_collect = NewsUserCollect::find()
                        ->where(["news_id" => $news_id, "user_id" => $user_id, 'status'=>1])
                        ->asArray()->one();
                    if($is_collect){
                        $news_info['collect_id'] = $is_collect['collect_id'];
                    }
                }else{
                    $news_info['collect_id']     = '';
                    $news_info['user_is_praise'] = '0';
                }
                $info_lists['news_info'] = $news_info;
                $info_lists['list']      = $new_list;
                $redis->set($name, $info_lists, 86400);
                return $info_lists;
            }
            return $new_list;
        }
    }

    /**
     * 新版视频栏目视频列表首页
     */
    public static function getVideoList($size, $is_pc, $page){
        $column_id = 9;
        $column_type = NewsColumnType::find()->where(['column_id'=>$column_id, 'status'=>1])->select('type_id,name')->orderBy('weight desc')->asArray()->all();
        $new_list = array();
        $redis  = Yii::$app->cache;
        $is_areas  = 0;
        $last_update_time = Yii::$app->params['environment']."_new_list_".$is_areas.'_'.$column_id.'_update';
        $news_video_column_update = $redis->get($last_update_time).time(); //最新更新时间
        $name = Yii::$app->params['environment']."_video_list_index".$is_areas.'_'.$column_id.'_'.$is_pc. '_'.$news_video_column_update;
        $info_list = $redis->get($name);
        if($info_list){
            return $info_list;
        }
        if($column_type && count($column_type) > 0){
            foreach ($column_type as $key=>$val){
                $list = self::getVideo($is_pc, $column_id, $val['type_id'], '', '', $page, $size);
                $info_list = $list['list'];
                if (count($info_list) > 0) {
                    foreach ($info_list as $k=>$value){
                        //专题处理 如果是专题类型，返回专题下第一条视频的ID
                        if(isset($value['type']) && $value['type'] == 3){
                            $video_info = self::getVideo($is_pc, $column_id, '', $value['news_id'], '', 1, 1);
                            $video_info = $video_info['list'];
                            if($video_info){
                                $info_list[$k]['news_id'] = $video_info[0]['news_id'];
                                $info_list[$k]['special_id'] = $value['news_id'];
                            }
                            unset($video_info);
                        }
                        $info_list[$k]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '/y' : '';
                        //列表 如果是视频并且引用了其他视频  取出对应的植
                        if (isset($value['type']) && $value['type'] == 4 && $value['reference_id']) {
                            $video_info = News::_getNewsInfo($value['reference_id']);
                            $value['thumbnail_url'] = $video_info['thumbnail_url'];
                            $value['duration'] = $video_info['duration'];
                            $value['category'] = $video_info['category'];
                            $value['video_url'] = $video_info['video_url'];
                            $value['video_url1'] = $video_info['video_url1'];
                            $value['video_url2'] = $video_info['video_url2'];
                            $value['width'] = $video_info['width'];
                            $value['width1'] = $video_info['width1'];
                            $value['width2'] = $video_info['width2'];
                            $value['height'] = $video_info['height'];
                            $value['height1'] = $video_info['height1'];
                            $value['height2'] = $video_info['height2'];
                            $value['file_id'] = $video_info['file_id'];
                        }
                        //处理  返回值
                        if (isset($value['video_url'])) {
                            unset($info_list[$k]['video_url1']);
                            unset($info_list[$k]['video_url2']);
                        } else if (isset($value['video_url1'])) {
                            $info_list[$key]['video_url'] = $value['video_url1'];
                            unset($info_list[$k]['video_url1']);
                            unset($info_list[$k]['video_url2']);
                        } else if (isset($value['video_url2'])) {
                            $info_list[$key]['video_url'] = $value['video_url2'];
                            unset($info_list[$k]['video_url1']);
                            unset($info_list[$k]['video_url2']);
                        } else {
                            unset($info_list[$k]['video_url1']);
                            unset($info_list[$k]['video_url2']);
                        }
                        if (isset($value['height'])) {
                            unset($info_list[$k]['height1']);
                            unset($info_list[$k]['height2']);
                        } else if (isset($value['height1'])) {
                            $info_list[$key]['height'] = $value['height1'];
                            unset($info_list[$k]['height1']);
                            unset($info_list[$k]['height2']);
                        } else if (isset($value['height2'])) {
                            $info_list[$key]['height'] = $value['height2'];
                            unset($info_list[$k]['height1']);
                            unset($info_list[$k]['height2']);
                        } else {
                            unset($info_list[$k]['height1']);
                            unset($info_list[$k]['height2']);
                        }
                        if (isset($value['width'])) {
                            unset($info_list[$k]['width1']);
                            unset($info_list[$k]['width2']);
                        } else if (isset($value['width1'])) {
                            $info_list[$key]['width'] = $value['width1'];
                            unset($info_list[$k]['width1']);
                            unset($info_list[$k]['width2']);
                        } else if (isset($value['width2'])) {
                            $info_list[$key]['width'] = $value['width2'];
                            unset($info_list[$k]['width1']);
                            unset($info_list[$k]['width2']);
                        } else {
                            unset($info_list[$k]['width1']);
                            unset($info_list[$k]['width2']);
                        }
                        if (isset($value['size'])) {
                            unset($info_list[$k]['size1']);
                            unset($info_list[$k]['size2']);
                        } else if (isset($value['size1'])) {
                            $info_list[$key]['size'] = $value['size1'];
                            unset($info_list[$k]['size1']);
                            unset($info_list[$k]['size2']);
                        } else if (isset($value['size2'])) {
                            $info_list[$key]['size'] = $value['size2'];
                            unset($info_list[$k]['size1']);
                            unset($info_list[$k]['size2']);
                        } else {
                            unset($info_list[$k]['size1']);
                            unset($info_list[$k]['size2']);
                        }

                        unset($value);
                        $new_list[$key]['column'] = $val;
                        $new_list[$key]['list']   = $info_list;
                    }
                }
            }
        }
        $redis->set($name, $new_list);
        return $new_list;
    }

    /**
     * 获取视频栏目精彩视频
     */
    public static function getWonderfulVideo($size, $is_pc, $page){
        $redis  = Yii::$app->cache;
        $is_areas  = 0;
        $column_id = 9;
        $last_update_time = Yii::$app->params['environment']."_new_list_".$is_areas.'_'.$column_id.'_update';
        $news_video_column_update = $redis->get($last_update_time).time(); //最新更新时间
        $name = Yii::$app->params['environment']."_wonderful_video_list_".$is_areas.'_'.$column_id.'_'.$is_pc. '_'.$news_video_column_update;
        $new_list = $redis->get($name);
        if(!$new_list){
            $list = self::getVideo($is_pc, $column_id, '', '', '精彩推荐', $page, $size);
            $new_list = $list['list'];
        }
        if (count($new_list) > 0) {
            foreach ($new_list as $key => $value) {
                $new_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '/y' : '';
                //列表 如果是视频并且引用了其他视频  取出对应的植
                if ($value['type'] == 4 && $value['reference_id']) {
                    $video_info = News::_getNewsInfo($value['reference_id']);
                    $value['thumbnail_url'] = $video_info['thumbnail_url'];
                    $value['duration'] = $video_info['duration'];
                    $value['category'] = $video_info['category'];
                    $value['video_url'] = $video_info['video_url'];
                    $value['video_url1'] = $video_info['video_url1'];
                    $value['video_url2'] = $video_info['video_url2'];
                    $value['width'] = $video_info['width'];
                    $value['width1'] = $video_info['width1'];
                    $value['width2'] = $video_info['width2'];
                    $value['height'] = $video_info['height'];
                    $value['height1'] = $video_info['height1'];
                    $value['height2'] = $video_info['height2'];
                    $value['file_id'] = $video_info['file_id'];
                }

                //处理  返回值
                if ($value['video_url']) {
                    unset($new_list[$key]['video_url1']);
                    unset($new_list[$key]['video_url2']);
                } else if ($value['video_url1']) {
                    $new_list[$key]['video_url'] = $value['video_url1'];
                    unset($new_list[$key]['video_url1']);
                    unset($new_list[$key]['video_url2']);
                } else if ($value['video_url2']) {
                    $new_list[$key]['video_url'] = $value['video_url2'];
                    unset($new_list[$key]['video_url1']);
                    unset($new_list[$key]['video_url2']);
                } else {
                    unset($new_list[$key]['video_url1']);
                    unset($new_list[$key]['video_url2']);
                }
                if ($value['height']) {
                    unset($new_list[$key]['height1']);
                    unset($new_list[$key]['height2']);
                } else if ($value['height1']) {
                    $new_list[$key]['height'] = $value['height1'];
                    unset($new_list[$key]['height1']);
                    unset($new_list[$key]['height2']);
                } else if ($value['height2']) {
                    $new_list[$key]['height'] = $value['height2'];
                    unset($new_list[$key]['height1']);
                    unset($new_list[$key]['height2']);
                } else {
                    unset($new_list[$key]['height1']);
                    unset($new_list[$key]['height2']);
                }
                if ($value['width']) {
                    unset($new_list[$key]['width1']);
                    unset($new_list[$key]['width2']);
                } else if ($value['width1']) {
                    $new_list[$key]['width'] = $value['width1'];
                    unset($new_list[$key]['width1']);
                    unset($new_list[$key]['width2']);
                } else if ($value['width2']) {
                    $new_list[$key]['width'] = $value['width2'];
                    unset($new_list[$key]['width1']);
                    unset($new_list[$key]['width2']);
                } else {
                    unset($new_list[$key]['width1']);
                    unset($new_list[$key]['width2']);
                }
                if ($value['size']) {
                    unset($new_list[$key]['size1']);
                    unset($new_list[$key]['size2']);
                } else if ($value['size1']) {
                    $new_list[$key]['size'] = $value['size1'];
                    unset($new_list[$key]['size1']);
                    unset($new_list[$key]['size2']);
                } else if ($value['size2']) {
                    $new_list[$key]['size'] = $value['size2'];
                    unset($new_list[$key]['size1']);
                    unset($new_list[$key]['size2']);
                } else {
                    unset($new_list[$key]['size1']);
                    unset($new_list[$key]['size2']);
                }
            }
            unset($value);
            $redis->set($name, $new_list);
        }
        return $new_list;
    }

    /**
     * 获取栏目列表数据
     */
     public static function getColumnList($is_pc, $type_id, $page, $size){
        $redis  = Yii::$app->cache;
        $is_areas  = 0;
        $column_id = 9;
        $last_update_time = Yii::$app->params['environment']."_new_list_".$is_areas.'_'.$column_id.'_update';
        $news_video_column_update = $redis->get($last_update_time).time(); //最新更新时间
        $name = Yii::$app->params['environment']."_video_column_list_".$is_areas.'_'.$column_id.'_'.$is_pc. '_'.$type_id.'_'.$news_video_column_update;
        $new_list = $redis->get($name);
        if($new_list){
         return $new_list;
        }
        $list = self::getVideo($is_pc, $column_id, $type_id, '', '', $page, $size);
        $new_list = $list['list'];
        if (count($new_list) > 0) {
            foreach ($new_list as $key => $value) {
                $new_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '/y' : '';
                if ($value['type'] == 4 && $value['reference_id']) {
                    $video_info = News::_getNewsInfo($value['reference_id']);
                    $value['thumbnail_url'] = $video_info['thumbnail_url'];
                    $value['duration'] = $video_info['duration'];
                    $value['category'] = $video_info['category'];
                    $value['video_url'] = $video_info['video_url'];
                    $value['video_url1'] = $video_info['video_url1'];
                    $value['video_url2'] = $video_info['video_url2'];
                    $value['width'] = $video_info['width'];
                    $value['width1'] = $video_info['width1'];
                    $value['width2'] = $video_info['width2'];
                    $value['height'] = $video_info['height'];
                    $value['height1'] = $video_info['height1'];
                    $value['height2'] = $video_info['height2'];
                    $value['file_id'] = $video_info['file_id'];
                }

                //处理  返回值
                if ($value['video_url']) {
                    unset($new_list[$key]['video_url1']);
                    unset($new_list[$key]['video_url2']);
                } else if ($value['video_url1']) {
                    $new_list[$key]['video_url'] = $value['video_url1'];
                    unset($new_list[$key]['video_url1']);
                    unset($new_list[$key]['video_url2']);
                } else if ($value['video_url2']) {
                    $new_list[$key]['video_url'] = $value['video_url2'];
                    unset($new_list[$key]['video_url1']);
                    unset($new_list[$key]['video_url2']);
                } else {
                    unset($new_list[$key]['video_url1']);
                    unset($new_list[$key]['video_url2']);
                }
                if ($value['height']) {
                    unset($new_list[$key]['height1']);
                    unset($new_list[$key]['height2']);
                } else if ($value['height1']) {
                    $new_list[$key]['height'] = $value['height1'];
                    unset($new_list[$key]['height1']);
                    unset($new_list[$key]['height2']);
                } else if ($value['height2']) {
                    $new_list[$key]['height'] = $value['height2'];
                    unset($new_list[$key]['height1']);
                    unset($new_list[$key]['height2']);
                } else {
                    unset($new_list[$key]['height1']);
                    unset($new_list[$key]['height2']);
                }
                if ($value['width']) {
                    unset($new_list[$key]['width1']);
                    unset($new_list[$key]['width2']);
                } else if ($value['width1']) {
                    $new_list[$key]['width'] = $value['width1'];
                    unset($new_list[$key]['width1']);
                    unset($new_list[$key]['width2']);
                } else if ($value['width2']) {
                    $new_list[$key]['width'] = $value['width2'];
                    unset($new_list[$key]['width1']);
                    unset($new_list[$key]['width2']);
                } else {
                    unset($new_list[$key]['width1']);
                    unset($new_list[$key]['width2']);
                }
                if ($value['size']) {
                    unset($new_list[$key]['size1']);
                    unset($new_list[$key]['size2']);
                } else if ($value['size1']) {
                    $new_list[$key]['size'] = $value['size1'];
                    unset($new_list[$key]['size1']);
                    unset($new_list[$key]['size2']);
                } else if ($value['size2']) {
                    $new_list[$key]['size'] = $value['size2'];
                    unset($new_list[$key]['size1']);
                    unset($new_list[$key]['size2']);
                } else {
                    unset($new_list[$key]['size1']);
                    unset($new_list[$key]['size2']);
                }
            }
            unset($value);
            $video_list['totalCount'] = $list['totalCount'] ? $list['totalCount'] : 0;
            $video_list['list'] = $new_list;
            $redis->set($name, $new_list);
            return $video_list;
        }
    }

    /**
     * 获取视频列表数据
     */
    public static function getVideo($is_pc, $column_id, $type_id, $special_id, $subtitle, $page, $size){
        $offset = ($page-1) * $size;
        $trans_field = ' ,vrnews1.news_video.video_url as video_url,vrnews1.news_video.video_url1,vrnews1.news_video.video_url2,vrnews1.news_video.width,vrnews1.news_video.width1,vrnews1.news_video.width2,vrnews1.news_video.height,vrnews1.news_video.height1,vrnews1.news_video.height2,vrnews1.news_video.size,vrnews1.news_video.size1,vrnews1.news_video.size2 ,vrnews1.news_video.`file_id` as file_id,vrnews1.news.abstract,vrnews1.news.abstract as abstracts';
        if ($is_pc == 1) {
            $pub_where = " and web_pub = 1 ";
        } else {
            $pub_where = " and app_pub = 1 ";
        }
        if($type_id){
            $pub_where .= " and news.type_id = '".$type_id."'";
        }
        if($subtitle){
            $pub_where .= " and news.subtitle = '".$subtitle."'";
        }
        if($special_id){
            $where_specil = " and news.special_news_id = '".$special_id."'";
        }else{
            $where_specil = " and news.special_news_id = 0";
        }
        $where_area = " and  (news.area_id = 0 or news.area_id is null)";
        $trans_where = " and ( case when news.type=4  then ( (video_url<>'' or video_url1<>'' or video_url2<>'') and file_id<> 'null') or ( reference_type <> null and reference_id <> null)  or ( reference_type is not null and reference_id is not  null) else file_id is null end)";
        $query = new Query();
        $query->select(["vrnews1.news.news_id,news.type,title,subtitle,cover_image,DATE_FORMAT(vrnews1.news.create_time,'%Y/%m/%d %H:%i') as create_time,reference_type,reference_id,source_id,source_name,thumbnail_url,duration,play_count,category,outer_url_ishot,outer_url,external_link,year(vrnews1.news.create_time) as year1,month(vrnews1.news.create_time) as month1,day(vrnews1.news.create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time, vrnews1.news_video.status as video_status, vrnews1.news.live_id, vrnews1.news.vote_id" . $trans_field])->from('vrnews1.news');
        $query->leftJoin('vrnews1.news_video','vrnews1.news.news_id = vrnews1.news_video.news_id');

        $query->where("news.weight >= 70 and news.status = 0 and column_id = $column_id and  news.type != 2 and vote_id = 0".$where_area.$trans_where.$pub_where.$where_specil);
        $info_list['totalCount'] = $query->count('*',self::getDb());
        $query->orderBy([
            'top_status' => SORT_DESC,
            'case  when `year` is null then `year1` else `year` end' => SORT_DESC,
            'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
            'case  when `day` is null then `day1` else `day` end' => SORT_DESC,
            'vrnews1.news.weight' => SORT_DESC,
            'refresh_time' => SORT_DESC,
            'create_time' => SORT_DESC]);
        $query->offset($offset);
        $query->limit($size);
        $command = $query->createCommand();
        $info_list['list'] = $command->queryAll();
        return $info_list;
    }

    /**
     * 视频栏目 精彩推荐
     * 筛选条件 视频栏目下 VR 类型视频 四条
     * @param int $news_video_column_id
     * @param int $news_video_id
     * @return array
     */
    public static function videoRecommend($news_video_column_id = 9, $news_video_id = 0)
    {
        if (!$news_video_id) return [];

        $trans_field = ' ,vrnews1.news_video.video_url as video_url,vrnews1.news_video.video_url1,vrnews1.news_video.video_url2,vrnews1.news_video.width,vrnews1.news_video.width1,vrnews1.news_video.width2,vrnews1.news_video.height,vrnews1.news_video.height1,vrnews1.news_video.height2,vrnews1.news_video.size,vrnews1.news_video.size1,vrnews1.news_video.size2 ,vrnews1.news_video.`file_id` as file_id,vrnews1.news.abstract,vrnews1.news.abstract as abstracts';
        $pub_where = " and app_pub = 1 ";
        $where_area = " and  (news.area_id = 0 or news.area_id is null)";
        $trans_where = " and ( case when news.type=4  then ( (video_url<>'' or video_url1<>'' or video_url2<>'') and file_id<> 'null') or ( reference_type <> null and reference_id <> null)  or ( reference_type is not null and reference_id is not  null) else file_id is null end)";

        $query = new Query();
        $query->select(["vrnews1.news.news_id,news.type,title,subtitle,cover_image,DATE_FORMAT(vrnews1.news.create_time,'%Y/%m/%d %H:%i') as create_time,reference_type,reference_id,source_id,source_name,thumbnail_url,duration,play_count,category,outer_url_ishot,outer_url,external_link,year(vrnews1.news.create_time) as year1,month(vrnews1.news.create_time) as month1,day(vrnews1.news.create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time, vrnews1.news_video.status as video_status, vrnews1.news.live_id, vrnews1.news.vote_id" . $trans_field])->from('vrnews1.news');
        $query->leftJoin('vrnews1.news_video', 'vrnews1.news.news_id = vrnews1.news_video.news_id');
        $query->where("news.weight >= 70 and news.status = 0 and column_id = {$news_video_column_id} and news_video.category = 2 and news.news_id != {$news_video_id} and news.type = 4 and vote_id = 0" . $where_area . $trans_where . $pub_where);
        $query->orderBy(['create_time' => SORT_DESC]);
        $command = $query->createCommand();
        $new_list = $command->queryAll();

        $recommend_list = [];
        $rand_ids = [];
        if (count($new_list) > 4) {
            while (true) {
                $rand_ids[] = mt_rand(0, count($new_list) - 1);
                if (count(array_unique($rand_ids)) >= 4) {
                    $ids = array_unique($rand_ids);
                    break;
                }
            }

            foreach ($ids as $id) {
                $recommend_list[] = $new_list[$id];
            }
        } else {
            $recommend_list = $new_list;
        }

        if (count($recommend_list) > 0) {
            foreach ($recommend_list as $key => $value) {
                $recommend_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '/y' : '';
                if ($value['type'] == 4 && $value['reference_id']) {
                    $video_info = News::_getNewsInfo($value['reference_id']);
                    $value['thumbnail_url'] = $video_info['thumbnail_url'];
                    $value['duration'] = $video_info['duration'];
                    $value['category'] = $video_info['category'];
                    $value['video_url'] = $video_info['video_url'];
                    $value['video_url1'] = $video_info['video_url1'];
                    $value['video_url2'] = $video_info['video_url2'];
                    $value['width'] = $video_info['width'];
                    $value['width1'] = $video_info['width1'];
                    $value['width2'] = $video_info['width2'];
                    $value['height'] = $video_info['height'];
                    $value['height1'] = $video_info['height1'];
                    $value['height2'] = $video_info['height2'];
                    $value['file_id'] = $video_info['file_id'];
                }

                //处理  返回值
                if ($value['video_url']) {
                    unset($recommend_list[$key]['video_url1']);
                    unset($recommend_list[$key]['video_url2']);
                } else if ($value['video_url1']) {
                    $recommend_list[$key]['video_url'] = $value['video_url1'];
                    unset($recommend_list[$key]['video_url1']);
                    unset($recommend_list[$key]['video_url2']);
                } else if ($value['video_url2']) {
                    $recommend_list[$key]['video_url'] = $value['video_url2'];
                    unset($recommend_list[$key]['video_url1']);
                    unset($recommend_list[$key]['video_url2']);
                } else {
                    unset($recommend_list[$key]['video_url1']);
                    unset($recommend_list[$key]['video_url2']);
                }
                if ($value['height']) {
                    unset($recommend_list[$key]['height1']);
                    unset($recommend_list[$key]['height2']);
                } else if ($value['height1']) {
                    $recommend_list[$key]['height'] = $value['height1'];
                    unset($recommend_list[$key]['height1']);
                    unset($recommend_list[$key]['height2']);
                } else if ($value['height2']) {
                    $recommend_list[$key]['height'] = $value['height2'];
                    unset($recommend_list[$key]['height1']);
                    unset($recommend_list[$key]['height2']);
                } else {
                    unset($recommend_list[$key]['height1']);
                    unset($recommend_list[$key]['height2']);
                }
                if ($value['width']) {
                    unset($recommend_list[$key]['width1']);
                    unset($recommend_list[$key]['width2']);
                } else if ($value['width1']) {
                    $recommend_list[$key]['width'] = $value['width1'];
                    unset($recommend_list[$key]['width1']);
                    unset($recommend_list[$key]['width2']);
                } else if ($value['width2']) {
                    $recommend_list[$key]['width'] = $value['width2'];
                    unset($recommend_list[$key]['width1']);
                    unset($recommend_list[$key]['width2']);
                } else {
                    unset($recommend_list[$key]['width1']);
                    unset($recommend_list[$key]['width2']);
                }
                if ($value['size']) {
                    unset($recommend_list[$key]['size1']);
                    unset($recommend_list[$key]['size2']);
                } else if ($value['size1']) {
                    $recommend_list[$key]['size'] = $value['size1'];
                    unset($recommend_list[$key]['size1']);
                    unset($recommend_list[$key]['size2']);
                } else if ($value['size2']) {
                    $recommend_list[$key]['size'] = $value['size2'];
                    unset($recommend_list[$key]['size1']);
                    unset($recommend_list[$key]['size2']);
                } else {
                    unset($recommend_list[$key]['size1']);
                    unset($recommend_list[$key]['size2']);
                }
                unset($value);
            }
        } else {
            $recommend_list = [];
        }
        return $recommend_list;
    }
}