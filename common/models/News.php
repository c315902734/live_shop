<?php
namespace common\models;

use common\service\Record;
use Yii;
use common\models\OauthAccessTokens;
use common\models\NewsColumn;
use common\models\NewsColumnType;
use common\models\Area;
use common\models\Live;
use common\models\Entry;
use yii\helpers\ArrayHelper;
use yii\db\Query;


/**
 * This is the model class for table "news".
 *
 * @property string $news_id
 * @property string $title
 * @property string $subtitle
 * @property string $keywords
 * @property integer $source_id
 * @property string $source_name
 * @property string $tags
 * @property integer $type
 * @property integer $column_id
 * @property string $type_id
 * @property string $abstract
 * @property string $cover_image
 * @property string $content
 * @property string $reference_id
 * @property integer $reference_type
 * @property string $outer_url
 * @property integer $outer_url_ishot
 * @property string $external_link
 * @property integer $app_pub
 * @property integer $web_pub
 * @property integer $is_watermark
 * @property integer $weight
 * @property string $special_news_id
 * @property integer $top_status
 * @property integer $full_status
 * @property string $full_title
 * @property string $full_subtitle
 * @property string $full_cover_image
 * @property string $create_time
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $area_id
 * @property integer $competitor_id
 * @property integer $refresh_time
 * @property integer $update_time
 * @property integer $click_count
 * @property integer $special_entry
 * @property string $special_id
 * @property integer $special_type
 * @property string $special_title
 * @property string $special_abstract
 * @property string $special_image
 */
class News extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news';
    }
    
    public static function getDb(){
    	return Yii::$app->vrnews1;
    }

    //新闻列表 含搜索

    function GetList($column_id,$is_area,$pub_type,$key_word,$page,$count,$is_pc='',$type_id, $get_vote=0){
        $offset = ($page-1)*$count;
//        if($page != 1){
//            $offset = $offset-1;
//        }
        $pub_where = '';
        $is_areas = $is_area;
        $redis = Yii::$app->cache;
        if((in_array($column_id, array(3,4,10,7,8,14,6)) && $is_areas == 0) || ($column_id == 1 && $is_areas == 1)){
            $update = Yii::$app->params['environment']."_new_list_".$is_areas.'_'.$column_id.'_update';
            $update_time = $redis->get($update);
            if(!$key_word){
                $name = Yii::$app->params['environment']."_new_list_old_".$is_areas.'_'.$column_id.'_'.$type_id.'_'.$pub_type.'_'.$page.'_'.$update_time;
                $redis_info = $redis->get($name);
            }else{
                $name = Yii::$app->params['environment']."_new_list_old_".$key_word.'_'.$type_id.'_'.$pub_type.'_'.$update_time;
                $redis_info = $redis->get($name);
            }
            if(!$is_pc){
                if($redis_info && count($redis_info) > 0){
                    return $redis_info;
                }
            }else{
                if($redis_info && $redis_info['totalCount'] > 0){
                    return $redis_info;
                }
            }
        }
        if($pub_type == 1){
            $pub_where = " and web_pub = 1 ";
        }else{
            $pub_where = " and app_pub = 1 ";
        }

        if(!$get_vote){
            $pub_where .= ' and vote_id=0 ';
        }

        $type_where = " and news.type not in(9,10,11,12,13,14)";

        $live_types = array(0=>'9',1=>'10',2=>'11',3=>'12',4=>'13',5=>'14');

        //type !=2  不能是轮播图
        //type_id  栏目ID
        //key_word 关键字搜索 只匹配标题 可以含有轮播图类型新闻
        //type = 3 专题   special_entry=1 专题入口
        //根据 置顶\权重\时间 依次倒叙排序

        //modify by dawei - 没有转码成功的视频新闻不再列表里出现
        //$trans_where = " and (file_id is null or  ( (video_url<>'' or video_url1<>'' or video_url2<>'') and file_id<> 'null') )";
        $trans_where = " and ( case when news.type=4  then ( (video_url<>'' or video_url1<>'' or video_url2<>'') and file_id<> 'null') or ( reference_type is not null and reference_id is not null) else file_id is null end)";
        $trans_field = ' ,vrnews1.news_video.video_url as video_url,vrnews1.news_video.video_url1,vrnews1.news_video.video_url2,vrnews1.news_video.width,vrnews1.news_video.width1,vrnews1.news_video.width2,vrnews1.news_video.height,vrnews1.news_video.height1,vrnews1.news_video.height2,vrnews1.news_video.size,vrnews1.news_video.size1,vrnews1.news_video.size2 ,vrnews1.news_video.`file_id` as file_id';

        $query = new Query();

        if($key_word){
            if($type_id){
                $pub_where .= " and news.type_id = '".$type_id."'";
            }
            if($is_pc){
                $pub_where .= " and (news.reference_type is NULL or news.reference_type != 5)";
            }
            $query->select(["vrnews1.news.news_id,abstract as news_abstract,title,subtitle,content,cover_image,news.vote_id,vrnews1.news.vote_id,reference_type,reference_id,vrnews1.news.type,vrnews1.news.column_id,area_id,DATE_FORMAT(vrnews1.news.create_time,'%Y/%m/%d %H:%i') as create_time,type_id,special_news_id,top_status,full_status,full_title,full_subtitle,full_cover_image,source_id,source_name,special_id,special_type,special_title,special_abstract,special_image,thumbnail_url,duration,play_count,category,outer_url_ishot,outer_url,external_link,year(vrnews1.news.create_time) as year1,month(vrnews1.news.create_time) as month1,day(vrnews1.news.create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time".$trans_field])->from('vrnews1.news');
            $query->leftJoin('vrnews1.news_video','vrnews1.news.news_id = vrnews1.news_video.news_id');
            //客户端搜索  加入栏目权重条件
            $query->leftJoin('vrnews1.news_column','vrnews1.news.column_id = vrnews1.news_column.column_id');
            $query->where(" 1=1 ");
            $query->andWhere("news.weight >= 70 and news.status=0 and  (news.type != 3 or (news.type = 3 and news.special_news_id != 0)) and news.title like '%$key_word%'".$trans_where.$pub_where.$type_where);
            $query->andWhere(" (case when news.column_id !=0 then vrnews1.news_column.weight >= 70 else 1=1 END)");
            $query->groupBy('news.title');
            $totalCount = $query->count('*',self::getDb());
            $query->orderBy([
                            'top_status' => SORT_DESC,
                            'case  when `year` is null then `year1` else `year` end'    => SORT_DESC,
                            'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
                            'case  when `day` is null then `day1` else `day` end'       => SORT_DESC,
                            'vrnews1.news.weight'       => SORT_DESC,
                            'refresh_time' => SORT_DESC,
                            'create_time'  => SORT_DESC]);
            $query->offset($offset);
            $query->limit($count);
            $command   = $query->createCommand();
            $info_list = $command->queryAll();

        }else {
            if($is_area == 1){
                //常规栏目 ID
                $is_area = "news.area_id";
                $where_area = " ";
            }else{
                //本地栏目 ID
                $is_area = "news.column_id";
                $where_area = " and  (news.area_id = 0 or news.area_id is null)";
            }
            if($type_id){
                $where_area .= " and news.type_id = '".$type_id."'";
            }
            if($is_pc){
                $where_area .= " and (news.reference_type is NULL or news.reference_type != 5)";
            }
            $query->select(["news.news_id,abstract as news_abstract,title,subtitle,content,cover_image,vrnews1.news.vote_id,reference_type,news.vote_id,reference_id,type,column_id,area_id,DATE_FORMAT(`create_time`,'%Y/%m/%d %H:%i') as create_time,type_id,special_news_id,top_status,full_status,full_title,full_subtitle,full_cover_image,source_id,source_name,special_id,special_type,special_entry,special_title,special_abstract,special_image,thumbnail_url,duration,play_count,category,outer_url_ishot,outer_url,external_link,year(create_time) as year1,month(create_time) as month1,day(create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time".$trans_field])->from('vrnews1.news');
            $query->leftJoin('vrnews1.news_video','vrnews1.news.news_id = vrnews1.news_video.news_id');

            $query->where("news.weight >= 70 and news.status=0 and ".$is_area." = $column_id and  news.type != 2  and news.special_news_id = 0".$where_area.$trans_where.$pub_where.$type_where);
            $totalCount = $query->count('*',self::getDb());

            $query->orderBy([
                		'top_status' => SORT_DESC,
		                'case  when `year` is null then `year1` else `year` end' => SORT_DESC,
		                'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
		                'case  when `day` is null then `day1` else `day` end' => SORT_DESC,
		                'weight' => SORT_DESC,
		                'refresh_time' => SORT_DESC,
		                'create_time' => SORT_DESC,]);
            $query->offset($offset);
            $query->limit($count);
            $command   = $query->createCommand();
            $info_list = $command->queryAll();
//            print_r($command->getSql());die;

        }

        $query = new Query();
        $live_type = array(0=>'9',1=>'10',2=>'11',3=>'12',4=>'13',5=>'14');

        foreach ($info_list as $key=>&$value){
            //直播类型新闻 返回值
            if(in_array($value['type'],$live_type )){
                //查看直播 详情
                $live_info = Live::find()->where(['live_id'=>$value['live_id']])->asArray()->one();
                $info_list[$key]['status'] = Live::getLiveStatus($live_info['start_time'], $live_info['status']); //直播状态
                $info_list[$key]['live_is_subscribe'] = 0; //是否预约
                if(!empty($user_id)){
                    $is_subscribe = LiveUserSubscribe::find()->where(['user_id'=>$user_id, 'live_id'=>$value['live_id'], 'status'=>1])->count();
                    $info_list[$key]['live_is_subscribe'] = $is_subscribe;
                }
                $info_list[$key]['chatroom_id']      = 'room_'.$value['live_id']; //聊天室ID
                $info_list[$key]['live_play_count']  = $live_info['play_count']; //直播点击数量
                $info_list[$key]['is_fast']  = $live_info['is_fast']; //是否快直播
                $info_list[$key]['screen']  = $live_info['screen']; //横竖屏
                $info_list[$key]['live_category']  = $live_info['category']; //直播类型：0：表示未设置直播类型,请选择;1视频直播;2VR直播;3图文直播;4视频加图文直播;5VR加图文直播;6录播
            }
            //如果 有入口 返回入口新闻信息
            if($value['special_id']){
                $info_list[$key]['special_entry_info']  = $query->select(["news_id,abstract as news_abstract,title,subtitle,content,cover_image,reference_type,reference_id,type,column_id,area_id,DATE_FORMAT(`create_time`,'%Y/%m/%d %H:%i') as create_time,type_id,special_news_id,top_status,full_status,full_title,full_subtitle,full_cover_image,source_id,source_name,special_id,special_title,special_abstract,special_entry,special_image"])->from("vrnews1.news")->where("news_id = ".$value['special_id']." and status=0")->one();

                /* 修复：把视频新闻设为入口，列表中不显示时长和播放次数 */
                if($info_list[$key]['special_entry_info']['type'] == 4){
                	$_news_video_info = $query->select('duration, play_count')->from('vrnews1.news_video')->where(['news_id'=>$value['special_id']])->one();
                	$info_list[$key]['special_entry_info']['duration']   = $_news_video_info['duration'];
                	$info_list[$key]['special_entry_info']['play_count'] = $_news_video_info['play_count'];
                }
                
                if($info_list[$key]['special_entry_info']['type'] == 5){
                    $info_list[$key]['special_entry_info']['content'] = json_decode($info_list[$key]['special_entry_info']['content']);
                }else{
                    $info_list[$key]['special_entry_info']['content'] = array();
                }
                $info_list[$key]['special_entry_info']['title'] = htmlspecialchars_decode($info_list[$key]['special_entry_info']['title']);
                if(!$is_pc){
                    if($info_list[$key]['special_entry_info']['type'] == 3){ //专题
                        $info_list[$key]['special_entry_info']['cover_image'] = $info_list[$key]['special_entry_info']['cover_image'] ? $info_list[$key]['special_entry_info']['cover_image'].'?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['special_entry_info']['content'] = array();
                    }else if($info_list[$key]['special_entry_info']['type'] == 4){ //视频
                        $info_list[$key]['special_entry_info']['cover_image'] = $info_list[$key]['special_entry_info']['cover_image'] ? $info_list[$key]['special_entry_info']['cover_image'].'?imageMogr2/thumbnail/206x142!' : '';
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['special_entry_info']['content'] = array();
                    }else if($info_list[$key]['special_entry_info']['type'] == 5 && !empty($info_list[$key]['special_entry_info']['content'])){ //图集

                        if(!empty($info_list[$key]['special_entry_info']['content'])) {
                            foreach ($info_list[$key]['special_entry_info']['content'] as $k => $v) {
                                if ($k < 3) {
                                    if (is_object($info_list[$key]['special_entry_info']['content'][$k])) {
                                        $str_con = substr($info_list[$key]['special_entry_info']['content'][$k]->img, -2);
                                        if ($str_con == '/s') {
                                            $info_list[$key]['special_entry_info']['content'][$k]->img = substr($info_list[$key]['special_entry_info']['content'][$k]->img, 0, -2);
                                        }
                                        $info_list[$key]['special_entry_info']['content'][$k]->img = $info_list[$key]['special_entry_info']['content'][$k]->img ? $info_list[$key]['special_entry_info']['content'][$k]->img . '?imageMogr2/thumbnail/224x150!' : '';
                                    } else {
                                        $str_con = substr($info_list[$key]['special_entry_info']['content'][$k]['img'], -2);
                                        if ($str_con == '/s') {
                                            $info_list[$key]['special_entry_info']['content'][$k]['img'] = substr($info_list[$key]['special_entry_info']['content'][$k]['img'], 0, -2);
                                        }
                                        $info_list[$key]['special_entry_info']['content'][$k]['img'] = $info_list[$key]['special_entry_info']['content'][$k]['img'] ? $info_list[$key]['special_entry_info']['content'][$k]['img'] . '?imageMogr2/thumbnail/224x150!' : '';
                                    }

                                }

                            }
                        }
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/710x236!' : '';
                    }else if($info_list[$key]['special_entry_info']['type'] == 7){ //图文
                        $info_list[$key]['special_entry_info']['cover_image'] = $info_list[$key]['special_entry_info']['cover_image'] ? $info_list[$key]['special_entry_info']['cover_image'].'?imageMogr2/thumbnail/206x142!' : '';
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['special_entry_info']['content'] = array();
                    }else if(in_array( $info_list[$key]['special_entry_info']['type'],$live_types)){ //直播类型新闻
                        $info_list[$key]['special_entry_info']['cover_image'] = $info_list[$key]['special_entry_info']['cover_image'] ? $info_list[$key]['special_entry_info']['cover_image'].'?imageMogr2/thumbnail/710x340!' : '';
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['special_entry_info']['content'] = array();
                    }

                }else{
                    if($info_list[$key]['special_entry_info']['type'] == 3){ //专题
                        $info_list[$key]['special_entry_info']['cover_image'] = $info_list[$key]['special_entry_info']['cover_image'] ? $info_list[$key]['special_entry_info']['cover_image'].'?imageMogr2/thumbnail/640x213!' : '';
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/640x213!' : '';
                    }else if($info_list[$key]['special_entry_info']['type'] == 4){
                        $info_list[$key]['special_entry_info']['cover_image'] = $info_list[$key]['special_entry_info']['cover_image'] ? $info_list[$key]['special_entry_info']['cover_image'].'?imageMogr2/thumbnail/145x100!' : '';
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/640x213!' : '';
                    }else if($info_list[$key]['special_entry_info']['type'] == 5 && !empty($info_list[$key]['special_entry_info']['content'])){
                        
                        foreach ($info_list[$key]['special_entry_info']['content'] as $k=>$v){
                            if($k < 4) {
                                if(is_object($info_list[$key]['special_entry_info']['content'][$k])){
                                    $str_con = substr($info_list[$key]['special_entry_info']['content'][$k]->img,-2);
                                    if($str_con == '/s'){
                                        $info_list[$key]['special_entry_info']['content'][$k]->img = substr($info_list[$key]['special_entry_info']['content'][$k]->img,0,-2);
                                    }
                                    $info_list[$key]['special_entry_info']['content'][$k]->img = $info_list[$key]['special_entry_info']['content'][$k]->img ? $info_list[$key]['special_entry_info']['content'][$k]->img . '?imageMogr2/thumbnail/150x100!' : '';
                                }else {
                                    $str_con = substr($info_list[$key]['special_entry_info']['content'][$k]['img'],-2);
                                    if($str_con == '/s'){
                                        $info_list[$key]['special_entry_info']['content'][$k]['img'] = substr($info_list[$key]['special_entry_info']['content'][$k]['img'],0,-2);
                                    }
                                    $info_list[$key]['special_entry_info']['content'][$k]['img'] = $info_list[$key]['special_entry_info']['content'][$k]['img'] ? $info_list[$key]['special_entry_info']['content'][$k]['img'] . '?imageMogr2/thumbnail/150x100!' : '';
                                }


                            }

                        }
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/640x213!' : '';
                    }else if($info_list[$key]['special_entry_info']['type'] == 7){
                        $info_list[$key]['special_entry_info']['cover_image'] = $info_list[$key]['special_entry_info']['cover_image'] ? $info_list[$key]['special_entry_info']['cover_image'].'?imageMogr2/thumbnail/145x100!' : '';
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/640x213!' : '';
                    }else if(in_array( $info_list[$key]['special_entry_info']['type'],$live_types)){ //直播类型新闻
                        $info_list[$key]['special_entry_info']['cover_image'] = $info_list[$key]['special_entry_info']['cover_image'] ? $info_list[$key]['special_entry_info']['cover_image'].'?imageMogr2/thumbnail/208x100!' : '';
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/640x213!' : '';
                    }
                }
            }else {
                //非入口
                if (!$is_pc) {
                    if ($value['type'] == 3) { //专题
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['content'] = array();
                    } else if ($value['type'] == 4) { //视频
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/206x142!' : '';
                        $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['content'] = array();
                    } else if ($value['type'] == 5 && !empty($value['content'])) { //图集
                        $info_list[$key]['content'] = json_decode($info_list[$key]['content']);
                        if(!empty($value['reference_type']) && intval($value['reference_type']) == 1 && !empty($value['reference_id'])){
                            //查看 被引用图集信息
                            $ref_news = News::find()->where(['news_id'=>$value['reference_id']])->asArray()->one();
                            if(!empty($ref_news['content'])){
                                $ref_news['content'] = json_decode($ref_news['content']);
                                foreach ($ref_news['content'] as $re_k=>$re_v){
                                    if($re_k < 3) {
                                        if(is_object($ref_news['content'][$re_k])){
                                            $str_con = substr($ref_news['content'][$re_k]->img,-2);
                                            if($str_con == '/s'){
                                                $info_list[$key]['content'][$re_k]->img = substr($ref_news['content'][$re_k]->img,0,-2);
                                            }
                                            if($info_list[$key]['content']=='""'){
                                                $tmp = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/224x150!' : '';
                                                $info_list[$key]['content'] = array($re_k=>array('img'=>$tmp));
                                            }else{
                                                $info_list[$key]['content'][$re_k]['img'] = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/224x150!' : '';
                                            }
                                        }else {
                                            $str_con = substr($ref_news['content'][$re_k]['img'],-2);
                                            if($str_con == '/s'){
                                                $info_list[$key]['content'][$re_k]['img'] = substr($ref_news['content'][$re_k]['img'],0,-2);
                                            }
                                            $info_list[$key]['content'][$re_k]['img'] = $re_v['img'] ? $re_v['img'] . '?imageMogr2/thumbnail/224x150!' : '';
                                        }
                                    }
                                }
                            }else{
                                $info_list[$key]['content'] = array();
                            }
                        }else { //非引用
                            if (!empty($info_list[$key]['content'])) {
                                foreach ($info_list[$key]['content'] as $k => $v) {
                                    if ($k < 3) {
                                        if (is_object($info_list[$key]['content'][$k])) {
                                            $str_con = substr($info_list[$key]['content'][$k]->img, -2);
                                            if ($str_con == '/s') {
                                                $info_list[$key]['content'][$k]->img = substr($info_list[$key]['content'][$k]->img, 0, -2);
                                            }
                                            $info_list[$key]['content'][$k]->img = $info_list[$key]['content'][$k]->img ? $info_list[$key]['content'][$k]->img . '?imageMogr2/thumbnail/224x150!' : '';
                                        } else {
                                            $str_con = substr($info_list[$key]['content'][$k]['img'], -2);
                                            if ($str_con == '/s') {
                                                $info_list[$key]['content'][$k]['img'] = substr($info_list[$key]['content'][$k]['img'], 0, -2);
                                            }
                                            $info_list[$key]['content'][$k]['img'] = $info_list[$key]['content'][$k]['img'] ? $info_list[$key]['content'][$k]['img'] . '?imageMogr2/thumbnail/224x150!' : '';
                                        }

                                    }

                                }
                            }else{
                                $info_list[$key]['content'] = array();
                            }
                        }
                        $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                    } else if ($value['type'] == 7) { //图文
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/206x142!' : '';
                        $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['content'] = array();
                    } else if (in_array($value['type'], $live_types)) { //直播类型新闻
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/710x340!' : '';
                        $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['content'] = array();
                    }

                } else {
                    if ($value['type'] == 3) { //专题
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                        $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                    } else if ($value['type'] == 4) {
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/145x100!' : '';
                        $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                    } else if ($value['type'] == 5 && !empty($value['content'])) {
                        if(!empty($value['reference_type']) && intval($value['reference_type']) == 1 && !empty($value['reference_id'])){
                            //查看 被引用图集信息
                            $ref_news = News::find()->where(['news_id'=>$value['reference_id']])->asArray()->one();
                            if(!empty($ref_news['content'])){
                                $ref_news['content'] = json_decode($ref_news['content']);
                                foreach ($ref_news['content'] as $re_k=>$re_v){
                                    if($re_k < 4) {
                                        if(is_object($ref_news['content'][$re_k])){
                                            $str_con = substr($ref_news['content'][$re_k]->img,-2);
                                            if($str_con == '/s'){
                                                $info_list[$key]['content'][$re_k]->img = substr($ref_news['content'][$re_k]->img,0,-2);
                                            }
                                            if($info_list[$key]['content']=='""'){
                                                $tmp = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/150x100!' : '';
                                                $info_list[$key]['content'] = array($re_k=>array('img'=>$tmp));
                                            }else{
                                                $info_list[$key]['content'][$re_k]['img'] = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/150x100!' : '';
                                            }
                                        }else {
                                            $str_con = substr($ref_news['content'][$re_k]['img'],-2);
                                            if($str_con == '/s'){
                                                $info_list[$key]['content'][$re_k]['img'] = substr($ref_news['content'][$re_k]['img'],0,-2);
                                            }
                                            $info_list[$key]['content'][$re_k]['img'] = $re_v['img'] ? $re_v['img'] . '?imageMogr2/thumbnail/150x100!' : '';
                                        }
                                    }
                                }
                            }else{
                                $info_list[$key]['content'] = array();
                            }
                        }else {
                            $info_list[$key]['content'] = json_decode($info_list[$key]['content']);
                            foreach ($info_list[$key]['content'] as $k => $v) {
                                if ($k < 4) {
                                    if (is_object($info_list[$key]['content'][$k])) {
                                        $str_con = substr($info_list[$key]['content'][$k]->img, -2);
                                        if ($str_con == '/s') {
                                            $info_list[$key]['content'][$k]->img = substr($info_list[$key]['content'][$k]->img, 0, -2);
                                        }
                                        $info_list[$key]['content'][$k]->img = $info_list[$key]['content'][$k]->img ? $info_list[$key]['content'][$k]->img . '?imageMogr2/thumbnail/150x100!' : '';
                                    } else {
                                        $str_con = substr($info_list[$key]['content'][$k]['img'], -2);
                                        if ($str_con == '/s') {
                                            $info_list[$key]['content'][$k]['img'] = substr($info_list[$key]['content'][$k]['img'], 0, -2);
                                        }
                                        $info_list[$key]['content'][$k]['img'] = $info_list[$key]['content'][$k]['img'] ? $info_list[$key]['content'][$k]['img'] . '?imageMogr2/thumbnail/150x100!' : '';
                                    }


                                }
                            }
                        }
                        $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                    } else if ($value['type'] == 7) {
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/145x100!' : '';
                        $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                    } else if (in_array($value['type'], $live_types)) { //直播类型新闻
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/208x100!' : '';
                        $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                    }
                }
            }


            //列表 如果是视频并且引用了其他视频  取出对应的植
            if($value['type'] == 4 && $value['reference_id']){
                $video_info   = News::_getNewsInfo($value['reference_id']);
                $value['thumbnail_url'] = $video_info['thumbnail_url'];
                $value['duration']      = $video_info['duration'];
//         		$value['play_count']    = $video_info['play_count'];
                $value['video_url']     = $video_info['video_url'];
                $value['video_url1']    = $video_info['video_url1'];
                $value['video_url2']    = $video_info['video_url2'];
                $value['width']         = $video_info['width'];
                $value['width1']        = $video_info['width1'];
                $value['width2']        = $video_info['width2'];
                $value['height']        = $video_info['height'];
                $value['height1']       = $video_info['height1'];
                $value['height2']       = $video_info['height2'];
                $value['file_id']       = $video_info['file_id'];
            }

            //投票  跳转
            if($value['vote_id']){
                $value['vote_url'] = yii::$app->params['vote_url'].'?vote_id='.$value['vote_id'];
            }

            //处理  返回值
            if($value['video_url']){
                unset($info_list[$key]['video_url1']);
                unset($info_list[$key]['video_url2']);
            }else if($value['video_url1']){
                $info_list[$key]['video_url'] = $value['video_url1'];
                unset($info_list[$key]['video_url1']);
                unset($info_list[$key]['video_url2']);
            }else if($value['video_url2']){
                $info_list[$key]['video_url'] = $value['video_url2'];
                unset($info_list[$key]['video_url1']);
                unset($info_list[$key]['video_url2']);
            }else{
                unset($info_list[$key]['video_url1']);
                unset($info_list[$key]['video_url2']);
            }
            if($value['height']){
                unset($info_list[$key]['height1']);
                unset($info_list[$key]['height2']);
            }else if($value['height1']){
                $info_list[$key]['height'] = $value['height1'];
                unset($info_list[$key]['height1']);
                unset($info_list[$key]['height2']);
            }else if($value['height2']){
                $info_list[$key]['height'] = $value['height2'];
                unset($info_list[$key]['height1']);
                unset($info_list[$key]['height2']);
            }else{
                unset($info_list[$key]['height1']);
                unset($info_list[$key]['height2']);
            }
            if($value['width']){
                unset($info_list[$key]['width1']);
                unset($info_list[$key]['width2']);
            }else if($value['width1']){
                $info_list[$key]['width'] = $value['width1'];
                unset($info_list[$key]['width1']);
                unset($info_list[$key]['width2']);
            }else if($value['width2']){
                $info_list[$key]['width'] = $value['width2'];
                unset($info_list[$key]['width1']);
                unset($info_list[$key]['width2']);
            }else{
                unset($info_list[$key]['width1']);
                unset($info_list[$key]['width2']);
            }
            if($value['size']){
                unset($info_list[$key]['size1']);
                unset($info_list[$key]['size2']);
            }else if($value['size1']){
                $info_list[$key]['size'] = $value['size1'];
                unset($info_list[$key]['size1']);
                unset($info_list[$key]['size2']);
            }else if($value['size2']){
                $info_list[$key]['size'] = $value['size2'];
                unset($info_list[$key]['size1']);
                unset($info_list[$key]['size2']);
            }else{
                unset($info_list[$key]['size1']);
                unset($info_list[$key]['size2']);
            }
        }
        unset($value);
//         echo '<pre>';print_r($info_list);die;
        if($is_pc){
            $return['totalCount'] = $totalCount;
            $return['list'] = $info_list;
            if((in_array($column_id, array(3,4,10,7,8,14,6)) && $is_areas == 0) || ($column_id == 1 && $is_areas == 1)) {
                if (!$key_word) {
                    $redis->set($name, $return, 86400);
                } else {
                    $redis->set($name, $return, 86400);
                }
            }

            return $return;
        }
        if((in_array($column_id, array(3,4,10,7,8,14,6)) && $is_areas == 0) || ($column_id == 1 && $is_areas == 1)) {
            if (!$key_word) {
                $redis->set($name, $info_list, 86400);
            } else {
                $redis->set($name, $info_list, 86400);
            }
        }

        return $info_list;
    }
    static function _getNewsInfo($news_id){
        $ret = NewsVideo::find()->where(['news_id'=>$news_id])->asArray()->one();
        return $ret;
    }

    //查看新闻详情 不含视频\直播 仅供专题列表 头部显示
    function GetSinfo($info_id){
        $news = new Query();
        $info = $news
            ->where("news_id = $info_id")
            ->select("news_id,title,subtitle,type,column_id,cover_image,abstract as news_abstract,type_id,content,create_time,special_news_id,top_status,source_id,source_name,update_time")->from("vrnews1.news")
            ->one();
        return $info;
    }
    // 获取别名数组
    public static function getAlias($modelNames=[])
    {
        $data=[];
        foreach ($modelNames as $name){
            $temp = ArrayHelper::getColumn($name::find()->all(), 'alias');
            $data = ArrayHelper::merge($data, $temp);
        }
        return $data;
    }
    // 验证别名唯一性
    public static function checkAlias($alias,$lib){
        if(in_array($alias,$lib)){
            return false;
        }
        return true;
    }
    
    //新闻列表 含搜索 --- 新版 含 栏目直播新闻
    public static function GetListNew($column_id,$is_area=0,$pub_type,$key_word,$page,$count,$user_id, $type_id,$get_vote=0,$source = NULL,$alias=0,$sub_alias=0,$is_pc=0){
        $offset = ($page-1)*$count;
        $is_areas = $is_area;
        
        //pc端自动判断分类是否是本地
        if ($is_pc) {
            //获取所有城市字段
            $query = new Query();
            $result = $query->select("pinyin")
                ->from("vrnews1.area")
                ->column();
            $cities= $result;
            if($alias){
                if(in_array($alias,$cities)){
                    $is_areas = $is_area= 1;
                    
                }
            }
        }
        //判断是否本地资源
        if($is_areas){
            // 支持别名查询本地资源
            if ($alias && !$column_id) {
                //通过别名查询本地栏目id
                $query = new Query();
                $result = $query->where('pinyin=:status', [':status' => $alias])
                    ->select("area_id, name ")
                    ->from("vrnews1.area")
                    ->one();
                if ($result) {
                    $column_id= $result['area_id'];
                    $column_name = $result['name'];
                    
                }else{
                    $column_id=1;
                    $column_name = '北京';
                }
            }
        }else{
            //当传入的是栏目别名且无栏目id传入时，将有效别名转换成id
            if ($alias && !$column_id ) {
                // 一级栏目查询
                $result = '';
                $query = new Query();
                $result = $query->where('alias=:status', [':status' => $alias])
                    ->select("column_id, name")
                    ->from("vrnews1.news_column")
                    ->one();
                if ($result) {
                    //若查询返回结果，则储存一级目录id
                    $column_id = $result['column_id'];
                    if(isset($result['name']))
                    $column_name = $result['name'];
                    //判定是否有二级目录参数传人
                    if ($sub_alias) {
                        // 若有二级目录参数则开始二级栏目id查询
                        $sub_name='';
                        $query = new Query();
                        $subColumnResult = $query->where('alias=:status', [':status' => $sub_alias])
                                                 ->andWhere('column_id=:first', [':first' => $column_id])
                                                 ->select("type_id ,name")
                                                 ->from("vrnews1.news_column_type")
                                                 ->one();
                        //判定查询是否有结果
                        if ($subColumnResult) {
                            //若有结果则获取二级目录id到type_id
                            //$column_id = $subColumnResult['column_id'];
                            $type_id = $subColumnResult['type_id'];
                            if (isset($subColumnResult['name']))
                            $sub_name = $subColumnResult['name'];
                        } /*else {
                            //重置一级目录id为1
                            $column_id = 1;
                            $column_name = '北京';
                        }*/
                    }
                } else{
                    //重置一级目录id为1
                    $column_id = 1;
                    $column_name = '北京';
                }
                
            };
            
        }
       // redis 缓存
        if((in_array($column_id, array(3,4,10,7,8,14,6)) && $is_areas == 0) || ($column_id == 1 && $is_areas == 1)) {
             $redis = Yii::$app->cache;
            $update = Yii::$app->params['environment']."_new_list_" . $is_areas . '_' . $column_id . '_update';
            $update_time = $redis->get($update);
            if (!$key_word) {
                $name = Yii::$app->params['environment']."_new_list_" . $is_areas . '_' . $column_id .'_'.$type_id.'_'.$pub_type.'_'. $page . '_' . $update_time;
                $redis_info = $redis->get($name);
            } else {
                $name = Yii::$app->params['environment']."_new_list_" . $key_word . '_' .$type_id.'_'.$pub_type.'_'. $update_time;
                $redis_info = $redis->get($name);
            }
            if ($redis_info && count($redis_info) > 0) {
                return $redis_info;
            }
        }
        $pub_where = '';
        if($pub_type == 1){
            $pub_where = " and web_pub = 1 ";
        }else{
            $pub_where = " and app_pub = 1 ";
        }

        if(!$get_vote){
            $pub_where .= ' and vote_id = 0 ';
        }

        if($type_id) {
            $pub_where .= " and news.type_id = '" . $type_id . "'";
        }
        
        if($source){
        	$pub_where .= " and news.source_name like '%$source%' ";
        }
        //type !=2  不能是轮播图
        //type_id  栏目ID
        //key_word 关键字搜索 只匹配标题 可以含有轮播图类型新闻
        //type = 3 专题   special_entry=1 专题入口
        //根据 置顶\权重\时间 依次倒叙排序

        //modify by dawei - 没有转码成功的视频新闻不再列表里出现
        $trans_where = " and ( case when news.type=4  then ( (video_url<>'' or video_url1<>'' or video_url2<>'') and file_id<> 'null') or ( reference_type <> null and reference_id <> null)  or ( reference_type is not null and reference_id is not  null) else file_id is null end)";
        $trans_field = ' ,vrnews1.news_video.video_url as video_url,vrnews1.news_video.video_url1,vrnews1.news_video.video_url2,vrnews1.news_video.width,vrnews1.news_video.width1,vrnews1.news_video.width2,vrnews1.news_video.height,vrnews1.news_video.height1,vrnews1.news_video.height2,vrnews1.news_video.size,vrnews1.news_video.size1,vrnews1.news_video.size2 ,vrnews1.news_video.`file_id` as file_id,vrnews1.news.update_time';

        $query = new Query();

        //判定是否为关键字搜索
        if($key_word){
            $query->select(["vrnews1.news.news_id,abstract as news_abstract,title,subtitle,content,cover_image,reference_type,reference_id,vrnews1.news.type,vrnews1.news.column_id,area_id,DATE_FORMAT(vrnews1.news.create_time,'%Y/%m/%d %H:%i') as create_time,type_id,special_news_id,top_status,full_status,full_title,full_subtitle,full_cover_image,source_id,source_name,special_id,special_type,special_title,special_abstract,special_image,thumbnail_url,duration,play_count,category,outer_url_ishot,outer_url,external_link,year(vrnews1.news.create_time) as year1,month(vrnews1.news.create_time) as month1,day(vrnews1.news.create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time, vrnews1.news_video.status as video_status, vrnews1.news.live_id, vrnews1.news.vote_id".$trans_field])->from('vrnews1.news');
            $query->leftJoin('vrnews1.news_video','vrnews1.news.news_id = vrnews1.news_video.news_id');
            //客户端搜索  加入栏目权重条件
            $query->leftJoin('vrnews1.news_column','vrnews1.news.column_id = vrnews1.news_column.column_id');
            $query->where(" 1=1 ");
            $query->andWhere(" (case when news.column_id !=0 then vrnews1.news_column.weight >= 70 else 1=1 END)");

            $query->andWhere("news.weight >= 70 and news.status=0 and  (news.type != 3 or (news.type = 3 and news.special_news_id != 0)) and news.title like '%$key_word%'".$trans_where.$pub_where);
            $query->groupBy('news.title');

            $query->orderBy([
                'top_status' => SORT_DESC,
                'case  when `year` is null then `year1` else `year` end'    => SORT_DESC,
                'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
                'case  when `day` is null then `day1` else `day` end'       => SORT_DESC,
                'vrnews1.news.weight'       => SORT_DESC,
                'refresh_time' => SORT_DESC,
                'create_time'  => SORT_DESC]);
            $query->offset($offset);
            $query->limit($count);
            $command   = $query->createCommand();
            $info_list = $command->queryAll();

        }else {
            //非搜索查询
            if($is_area == 1){
                //本地栏目 ID
                $cid_aid= $is_area;
                $is_area = "news.area_id";
                $where_area = " ";
            }else{
                //常规栏目 ID
                $cid_aid = $is_area;
                $is_area = "news.column_id";
                $where_area = " and (news.area_id = 0 or news.area_id is null)";
            }
            //判断当前栏目，快直播篮子是否存在并开启
            
            $basket_active =  Basket::find()->where(['column_id' => $column_id,'column_type'=>$is_areas,'is_active'=>1])->one();
            
            $query->select(["news.news_id,abstract as news_abstract,vrnews1.news.vote_id,title,concat(title,' |法制与新闻客户端') as share_title,subtitle,content,cover_image,reference_type,reference_id,type,column_id,area_id,DATE_FORMAT(`create_time`,'%Y/%m/%d %H:%i') as create_time,type_id,special_news_id,top_status,full_status,full_title,full_subtitle,full_cover_image,source_id,source_name,special_id,special_type,special_entry,special_title,special_abstract,special_image,thumbnail_url,duration,play_count,category,outer_url_ishot,outer_url,external_link,year(create_time) as year1,month(create_time) as month1,day(create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time, vrnews1.news_video.status as video_status,vrnews1.news.live_id".$trans_field])->from('vrnews1.news');
            $query->leftJoin('vrnews1.news_video','vrnews1.news.news_id = vrnews1.news_video.news_id');

            $query->where("news.weight >= 70 and news.status=0 and ".$is_area." = $column_id and  news.type != 2  and news.special_news_id = 0".$trans_where.$pub_where);
            //如果当前栏目有快直播入口，则加快直播入口集规则
            if ($basket_active) {
               $query->andWhere(['NOT IN','news.news_id',Basket::BasketLiveIds($is_areas,$column_id)]);
                //$query->andWhere('AND ((vrlive.live.is_fast !=1 AND vrnews1.news.live_id != 0) OR (vrnews1.news.live_id = 0))');
            }
            $query->orderBy([
                'top_status' => SORT_DESC,
                'case  when `year` is null then `year1` else `year` end' => SORT_DESC,
                'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
                'case  when `day` is null then `day1` else `day` end' => SORT_DESC,
                'weight' => SORT_DESC,
                'refresh_time' => SORT_DESC,
                'create_time' => SORT_DESC,]);
            $query->offset($offset);
            $query->limit($count);
            $command   = $query->createCommand();
            $info_list = $command->queryAll();
            
            $is_entry= Entry::ExistFastLiveEntry($cid_aid,$column_id);
            
            
            if ($basket_active) {
                foreach ($info_list as $key => $value) {
                
                     //加开直播总数字段到入口news字段上
                     if($value['news_id']== $basket_active->news_id){
                         if($is_entry['entry']){
                             $info_list[$key]['title'] = $is_entry['news']['title'];
                             $info_list[$key]['cover_image'] = $is_entry['news']['cover_image'];
                         }
                         
                         $info_list[$key]['fast_live_count']= $is_entry['fast_live_count'];
                     }
                }
            }
    
        }
        //定义直播分类id
        $live_type = array(0=>'9',1=>'10',2=>'11',3=>'12',4=>'13',5=>'14',6=>'15',7=>'17');
        
        foreach ($info_list as $key=>&$value){
            //直播类型新闻 返回值
            if(in_array($value['type'],$live_type )){
                //查看直播 详情
                $live_info = Live::find()->where(['live_id'=>$value['live_id']])->asArray()->one();
                $info_list[$key]['status'] = Live::getLiveStatus($live_info['start_time'], $live_info['status']); //直播状态
                $info_list[$key]['live_is_subscribe'] = 0; //是否预约
                if(!empty($user_id)){
                    $is_subscribe = LiveUserSubscribe::find()->where(['user_id'=>$user_id, 'live_id'=>$value['live_id'], 'status'=>1])->count();
                    $info_list[$key]['live_is_subscribe'] = $is_subscribe;
                }
                $info_list[$key]['chatroom_id']      = 'room_'.$value['live_id']; //聊天室ID
                $info_list[$key]['live_play_count']  = $live_info['play_count']; //直播点击数量
                $info_list[$key]['is_fast']  = $live_info['is_fast']; //是否快直播
                $info_list[$key]['screen']  = $live_info['screen']; //横竖屏
                $info_list[$key]['live_category']  = $live_info['category']; //直播类型：0：表示未设置直播类型,请选择;1视频直播;2VR直播;3图文直播;4视频加图文直播;5VR加图文直播;6录播
                $info_list[$key]['share_title'] = $info_list[$key]['title'];
            }

            //如果 有入口 返回入口新闻信息
            if($value['special_id']){
                $query = new Query();
                $query->select(["vrnews1.news.news_id,abstract as news_abstract,title,subtitle,content,cover_image,reference_type,reference_id,type,column_id,area_id,DATE_FORMAT(`create_time`,'%Y/%m/%d %H:%i') as create_time,type_id,special_news_id,top_status,full_status,full_title,full_subtitle,full_cover_image,source_id,source_name,special_id,special_title,special_abstract,special_entry,special_image,duration"])
                    ->from("vrnews1.news");
                    $query->leftJoin('vrnews1.news_video','vrnews1.news.news_id = vrnews1.news_video.news_id');
                    $query->where("news.news_id = ".$value['special_id']." and news.status=0");
                $special_command   = $query->createCommand();
                $info_list[$key]['special_entry_info'] = $special_command->queryOne();

                if($info_list[$key]['special_entry_info']['type'] == 5){
                    $info_list[$key]['special_entry_info']['content'] = json_decode($info_list[$key]['special_entry_info']['content']);
                }else{
                    $info_list[$key]['special_entry_info']['content'] = array();
                }
                $info_list[$key]['special_entry_info']['title'] = htmlspecialchars_decode($info_list[$key]['special_entry_info']['title']);

                if(!$pub_type){
                    if($info_list[$key]['special_entry_info']['type'] == 3){ //专题
                        $info_list[$key]['special_entry_info']['cover_image'] = $info_list[$key]['special_entry_info']['cover_image'] ? $info_list[$key]['special_entry_info']['cover_image'].'?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['special_entry_info']['content'] = array();
                    }else if($info_list[$key]['special_entry_info']['type'] == 4){ //视频
                        $info_list[$key]['special_entry_info']['cover_image'] = $info_list[$key]['special_entry_info']['cover_image'] ? $info_list[$key]['special_entry_info']['cover_image'].'?imageMogr2/thumbnail/206x142!' : '';
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['special_entry_info']['content'] = array();
                    }else if($info_list[$key]['special_entry_info']['type'] == 5 && !empty($info_list[$key]['special_entry_info']['content'])){ //图集

                        if(!empty($info_list[$key]['special_entry_info']['content'])) {
                            foreach ($info_list[$key]['special_entry_info']['content'] as $k => $v) {
                                if ($k < 3) {
                                    if (is_object($info_list[$key]['special_entry_info']['content'][$k])) {
                                        $str_con = substr($info_list[$key]['special_entry_info']['content'][$k]->img, -2);
                                        if ($str_con == '/s') {
                                            $info_list[$key]['special_entry_info']['content'][$k]->img = substr($info_list[$key]['special_entry_info']['content'][$k]->img, 0, -2);
                                        }
                                        $info_list[$key]['special_entry_info']['content'][$k]->img = $info_list[$key]['special_entry_info']['content'][$k]->img ? $info_list[$key]['special_entry_info']['content'][$k]->img . '?imageMogr2/thumbnail/224x150!' : '';
                                    } else {
                                        $str_con = substr($info_list[$key]['special_entry_info']['content'][$k]['img'], -2);
                                        if ($str_con == '/s') {
                                            $info_list[$key]['special_entry_info']['content'][$k]['img'] = substr($info_list[$key]['special_entry_info']['content'][$k]['img'], 0, -2);
                                        }
                                        $info_list[$key]['special_entry_info']['content'][$k]['img'] = $info_list[$key]['special_entry_info']['content'][$k]['img'] ? $info_list[$key]['special_entry_info']['content'][$k]['img'] . '?imageMogr2/thumbnail/224x150!' : '';
                                    }
                                }

                            }
                        }else{
                            $info_list[$key]['special_entry_info']['content'] = array();
                        }
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/710x236!' : '';
                    }else if($info_list[$key]['special_entry_info']['type'] == 7){ //图文
                        $info_list[$key]['special_entry_info']['cover_image'] = $info_list[$key]['special_entry_info']['cover_image'] ? $info_list[$key]['special_entry_info']['cover_image'].'?imageMogr2/thumbnail/206x142!' : '';
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['special_entry_info']['content'] = array();
                    }else if(in_array( $info_list[$key]['special_entry_info']['type'],$live_type)){ //直播类型新闻
                        $info_list[$key]['special_entry_info']['cover_image'] = $info_list[$key]['special_entry_info']['cover_image'] ? $info_list[$key]['special_entry_info']['cover_image'].'?imageMogr2/thumbnail/710x340!' : '';
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['special_entry_info']['content'] = array();
                        $info_list[$key]['share_title'] = $info_list[$key]['title'];
                        
                    }

                }else{
                    if($info_list[$key]['special_entry_info']['type'] == 3){ //专题
                        $info_list[$key]['special_entry_info']['cover_image'] = $info_list[$key]['special_entry_info']['cover_image'] ? $info_list[$key]['special_entry_info']['cover_image'].'?imageMogr2/thumbnail/640x213!' : '';
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/640x213!' : '';
                    }else if($info_list[$key]['special_entry_info']['type'] == 4){
                        $info_list[$key]['special_entry_info']['cover_image'] = $info_list[$key]['special_entry_info']['cover_image'] ? $info_list[$key]['special_entry_info']['cover_image'].'?imageMogr2/thumbnail/145x100!' : '';
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/640x213!' : '';
                    }else if($info_list[$key]['special_entry_info']['type'] == 5 && !empty($info_list[$key]['special_entry_info']['content'])){
                        foreach ($info_list[$key]['special_entry_info']['content'] as $k=>$v){
                            if($k < 4) {
                                if(is_object($info_list[$key]['special_entry_info']['content'][$k])){
                                    $str_con = substr($info_list[$key]['special_entry_info']['content'][$k]->img,-2);
                                    if($str_con == '/s'){
                                        $info_list[$key]['special_entry_info']['content'][$k]->img = substr($info_list[$key]['special_entry_info']['content'][$k]->img,0,-2);
                                    }
                                    $info_list[$key]['special_entry_info']['content'][$k]->img = $info_list[$key]['special_entry_info']['content'][$k]->img ? $info_list[$key]['special_entry_info']['content'][$k]->img . '?imageMogr2/thumbnail/150x100!' : '';
                                }else {
                                    $str_con = substr($info_list[$key]['special_entry_info']['content'][$k]['img'],-2);
                                    if($str_con == '/s'){
                                        $info_list[$key]['special_entry_info']['content'][$k]['img'] = substr($info_list[$key]['special_entry_info']['content'][$k]['img'],0,-2);
                                    }
                                    $info_list[$key]['special_entry_info']['content'][$k]['img'] = $info_list[$key]['special_entry_info']['content'][$k]['img'] ? $info_list[$key]['special_entry_info']['content'][$k]['img'] . '?imageMogr2/thumbnail/150x100!' : '';
                                }

                            }
                        }
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/640x213!' : '';
                    }else if($info_list[$key]['special_entry_info']['type'] == 7){
                        $info_list[$key]['special_entry_info']['cover_image'] = $info_list[$key]['special_entry_info']['cover_image'] ? $info_list[$key]['special_entry_info']['cover_image'].'?imageMogr2/thumbnail/145x100!' : '';
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/640x213!' : '';
                    }else if(in_array( $info_list[$key]['special_entry_info']['type'],$live_type)){ //直播类型新闻
                        $info_list[$key]['special_entry_info']['cover_image'] = $info_list[$key]['special_entry_info']['cover_image'] ? $info_list[$key]['special_entry_info']['cover_image'].'?imageMogr2/thumbnail/208x100!' : '';
                        $info_list[$key]['special_entry_info']['full_cover_image'] = $info_list[$key]['special_entry_info']['full_cover_image'] ? $info_list[$key]['special_entry_info']['full_cover_image'].'?imageMogr2/thumbnail/640x213!' : '';
                        $info_list[$key]['share_title'] = $info_list[$key]['title'];
                    }
                }

            }else {

                if (!$pub_type) {
                    if ($value['type'] == 3) { //专题
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['content'] = array();
                    } else if ($value['type'] == 4) { //视频
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/206x142!' : '';
                        $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['content'] = array();
                    } else if ($value['type'] == 5 && !empty($value['content'])) { //图集
                        $info_list[$key]['content'] = json_decode($info_list[$key]['content']);

                        if(!empty($info_list[$key]['reference_type']) && intval($info_list[$key]['reference_type']) == 1 && !empty($info_list[$key]['reference_id'])){
                            //查看 被引用图集信息
                            $ref_news = News::find()->where(['news_id'=>$info_list[$key]['reference_id']])->asArray()->one();
                            if(!empty($ref_news['content'])){
                                $ref_news['content'] = json_decode($ref_news['content'],TRUE);
                                foreach ($ref_news['content'] as $re_k=>$re_v){
                                    if($re_k < 3) {
                                        if(is_object($ref_news['content'][$re_k])){
                                            $str_con = substr($ref_news['content'][$re_k]->img,-2);
                                            if($str_con == '/s'){
                                                $ref_news['content'][$re_k]->img = substr($ref_news['content'][$re_k]->img,0,-2);
                                            }
                                            if($ref_news['content']=='""'){
                                                $tmp = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/224x150!' : '';
                                                $ref_news['content'] = array($re_k=>array('img'=>$tmp));
                                            }else{
                                                $ref_news['content'][$re_k]['img'] = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/224x150!' : '';
                                            }
                                        }else {
                                            $str_con = substr($ref_news['content'][$re_k]['img'],-2);
                                            if($str_con == '/s'){
                                                $ref_news['content'][$re_k]['img'] = substr($ref_news['content'][$re_k]['img'],0,-2);
                                            }
                                            $ref_news['content'][$re_k]['img'] = $re_v['img'] ? $re_v['img'] . '?imageMogr2/thumbnail/224x150!' : '';
                                        }
                                    }
                                }

                            }
                        }else {
                            if (!empty($info_list[$key]['content'])) {
                                foreach ($info_list[$key]['content'] as $k => $v) {
                                    if ($k < 3) {
                                        if (is_object($info_list[$key]['content'][$k])) {
                                            $str_con = substr($info_list[$key]['content'][$k]->img, -2);
                                            if ($str_con == '/s') {
                                                $info_list[$key]['content'][$k]->img = substr($info_list[$key]['content'][$k]->img, 0, -2);
                                            }
                                            $info_list[$key]['content'][$k]->img = $v->img ? $v->img . '?imageMogr2/thumbnail/224x150!' : '';
                                        } else {
                                            $str_con = substr($info_list[$key]['content'][$k]['img'], -2);
                                            if ($str_con == '/s') {
                                                $info_list[$key]['content'][$k]['img'] = substr($info_list[$key]['content'][$k]['img'], 0, -2);
                                            }
                                            $info_list[$key]['content'][$k]['img'] = $v['img'] ? $v['img'] . '?imageMogr2/thumbnail/224x150!' : '';
                                        }
                                    }
                                }
                                $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                            }else{
                                $info_list[$key]['content'] = array();
                            }
                        }
                    } else if ($value['type'] == 7) { //图文
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/206x142!' : '';
                        $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['content'] = array();
                    } else if (in_array($value['type'], $live_type)) { //直播类型新闻
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/710x340!' : '';
                        $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                        $info_list[$key]['content'] = array();
                        $info_list[$key]['share_title'] = $info_list[$key]['title'];
                    }

                } else {
                    if ($value['type'] == 3) { //专题
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                        $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                    } else if ($value['type'] == 4) {
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/145x100!' : '';
                        $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                    } else if ($value['type'] == 5 && !empty($value['content'])) {
                        if(!empty($value['reference_type']) && intval($value['reference_type']) == 1 && !empty($value['reference_id'])){
                            //查看 被引用图集信息
                            $ref_news = News::find()->where(['news_id'=>$value['reference_id']])->asArray()->one();
                            if(!empty($ref_news['content'])){
                                $ref_news['content'] = json_decode($ref_news['content']);
                                foreach ($ref_news['content'] as $re_k=>$re_v){
                                    if($re_k < 4) {
                                        if(is_object($ref_news['content'][$re_k])){
                                            $str_con = substr($ref_news['content'][$re_k]->img,-2);
                                            if($str_con == '/s'){
                                                $info_list[$key]['content'][$re_k]->img = substr($ref_news['content'][$re_k]->img,0,-2);
                                            }
                                            if($info_list[$key]['content']=='""'){
                                                $tmp = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/150x100!' : '';
                                                $info_list[$key]['content'] = array($re_k=>array('img'=>$tmp));
                                            }else{
                                                $info_list[$key]['content'][$re_k]['img'] = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/150x100!' : '';
                                            }

                                        }else {
                                            $str_con = substr($ref_news['content'][$re_k]['img'],-2);
                                            if($str_con == '/s'){
                                                $info_list[$key]['content'][$re_k]['img'] = substr($ref_news['content'][$re_k]['img'],0,-2);
                                            }
                                            $info_list[$key]['content'][$re_k]['img'] = $re_v['img'] ? $re_v['img'] . '?imageMogr2/thumbnail/150x100!' : '';
                                        }
                                    }
                                }
                            }else{
                                $info_list[$key]['content'] = array();
                            }
                        }else {
                            $info_list[$key]['content'] = json_decode($info_list[$key]['content']);
                            foreach ($info_list[$key]['content'] as $k => $v) {
                                if ($k < 4) {
                                    if (is_object($info_list[$key]['content'][$k])) {
                                        $str_con = substr($info_list[$key]['content'][$k]->img, -2);
                                        if ($str_con == '/s') {
                                            $info_list[$key]['content'][$k]->img = substr($info_list[$key]['content'][$k]->img, 0, -2);
                                        }
                                        $info_list[$key]['content'][$k]->img = $info_list[$key]['content'][$k]->img ? $info_list[$key]['content'][$k]->img . '?imageMogr2/thumbnail/150x100!' : '';
                                    } else {
                                        $str_con = substr($info_list[$key]['content'][$k]['img'], -2);
                                        if ($str_con == '/s') {
                                            $info_list[$key]['content'][$k]['img'] = substr($info_list[$key]['content'][$k]['img'], 0, -2);
                                        }
                                        $info_list[$key]['content'][$k]['img'] = $info_list[$key]['content'][$k]['img'] ? $info_list[$key]['content'][$k]['img'] . '?imageMogr2/thumbnail/150x100!' : '';
                                    }

                                }
                            }
                        }
                        $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                    } else if ($value['type'] == 7) {
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/145x100!' : '';
                        $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] .  '?imageMogr2/thumbnail/640x213!' : '';
                    } else if (in_array($value['type'], $live_type)) { //直播类型新闻
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/208x100!' : '';
                        $info_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                    }
                }
            }

            //列表 如果是视频并且引用了其他视频  取出对应的植
            if($value['type'] == 4 && $value['reference_id']){
                $video_info   = News::_getNewsInfo($value['reference_id']);
                $value['thumbnail_url'] = $video_info['thumbnail_url'];
                $value['duration']      = $video_info['duration'];
         		$value['category']      = $video_info['category'];
                $value['video_url']     = $video_info['video_url'];
                $value['video_url1']    = $video_info['video_url1'];
                $value['video_url2']    = $video_info['video_url2'];
                $value['width']         = $video_info['width'];
                $value['width1']        = $video_info['width1'];
                $value['width2']        = $video_info['width2'];
                $value['height']        = $video_info['height'];
                $value['height1']       = $video_info['height1'];
                $value['height2']       = $video_info['height2'];
                $value['file_id']       = $video_info['file_id'];
            }

            //处理  返回值
            if($value['video_url']){
                unset($info_list[$key]['video_url1']);
                unset($info_list[$key]['video_url2']);
            }else if($value['video_url1']){
                $info_list[$key]['video_url'] = $value['video_url1'];
                unset($info_list[$key]['video_url1']);
                unset($info_list[$key]['video_url2']);
            }else if($value['video_url2']){
                $info_list[$key]['video_url'] = $value['video_url2'];
                unset($info_list[$key]['video_url1']);
                unset($info_list[$key]['video_url2']);
            }else{
                unset($info_list[$key]['video_url1']);
                unset($info_list[$key]['video_url2']);
            }
            if($value['height']){
                unset($info_list[$key]['height1']);
                unset($info_list[$key]['height2']);
            }else if($value['height1']){
                $info_list[$key]['height'] = $value['height1'];
                unset($info_list[$key]['height1']);
                unset($info_list[$key]['height2']);
            }else if($value['height2']){
                $info_list[$key]['height'] = $value['height2'];
                unset($info_list[$key]['height1']);
                unset($info_list[$key]['height2']);
            }else{
                unset($info_list[$key]['height1']);
                unset($info_list[$key]['height2']);
            }
            if($value['width']){
                unset($info_list[$key]['width1']);
                unset($info_list[$key]['width2']);
            }else if($value['width1']){
                $info_list[$key]['width'] = $value['width1'];
                unset($info_list[$key]['width1']);
                unset($info_list[$key]['width2']);
            }else if($value['width2']){
                $info_list[$key]['width'] = $value['width2'];
                unset($info_list[$key]['width1']);
                unset($info_list[$key]['width2']);
            }else{
                unset($info_list[$key]['width1']);
                unset($info_list[$key]['width2']);
            }
            if($value['size']){
                unset($info_list[$key]['size1']);
                unset($info_list[$key]['size2']);
            }else if($value['size1']){
                $info_list[$key]['size'] = $value['size1'];
                unset($info_list[$key]['size1']);
                unset($info_list[$key]['size2']);
            }else if($value['size2']){
                $info_list[$key]['size'] = $value['size2'];
                unset($info_list[$key]['size1']);
                unset($info_list[$key]['size2']);
            }else{
                unset($info_list[$key]['size1']);
                unset($info_list[$key]['size2']);
            }
        }
        unset($value);

        foreach ($info_list as $key => $val) {
//            $info_list[$key]['cover_image'] =  ['cover_image'] ? $val['cover_image'].'/s' : '';
//            $info_list[$key]['full_cover_image'] = $val['full_cover_image'] ? $val['full_cover_image'].'/y' : '';
//            $type_y = array(0=>3,1=>9,2=>10,3=>11,4=>12,5=>13);
//            if(in_array($val['type'],$type_y)){
//                $info_list[$key]['cover_image'] = $val['cover_image'] ? $val['cover_image'].'/y' : '';
//            }
            //有投票ID跳转到url
            if ($val['vote_id']) {
                $info_list[$key]['vote_url'] = yii::$app->params['vote_url'] . '?vote_id=' . $val['vote_id'];
            }
            $info_list[$key]['title'] = htmlspecialchars_decode($info_list[$key]['title']);
            if ($val['type'] == 5) { //图集 对内容进行处理
                $content = $val['content'];
                if ($val['reference_id']) {
                    $content_re = News::findOne($val['reference_id']);
                    $content = $content_re['content'];
                    $news_content = json_decode($content);
                    if (!empty($news_content) && $content_re['type'] == 5) {
                        if ($pub_type == 1) {
                            $for_k = 4;
                        } else {
                            $for_k = 3;
                        }
                        foreach ($news_content as $k => $v) {
                            if ($k < $for_k) {
                                if (is_object($news_content[$k])) {
                                    $str_con = substr($news_content[$k]->img, -2);
                                    if ($str_con == '/s') {
                                        $news_content[$k]->img = substr($news_content[$k]->img, 0, -2);
                                    }
                                    if ($pub_type == 1) {
                                        $news_content[$k]->img = $v->img . '?imageMogr2/thumbnail/150x100!';
                                    } else {
                                        $news_content[$k]->img = $v->img . '?imageMogr2/thumbnail/224x150!';
                                    }
                                } else {
                                    $str_con = substr($news_content[$k]['img'], -2);
                                    if ($str_con == '/s') {
                                        $news_content[$k]['img'] = substr($news_content[$k]['img'], 0, -2);
                                    }
                                    if ($pub_type == 1) {
                                        $news_content[$k]['img'] = $v['img'] . '?imageMogr2/thumbnail/150x100!';
                                    } else {
                                        $news_content[$k]['img'] = $v['img'] . '?imageMogr2/thumbnail/224x150!';
                                    }
                                }
                            } else {
                                break;
                            }
                        }
                    }
                } else {
                    $news_content = $content;
                }
                $info_list[$key]['content'] = $news_content;
            } else {
                $info_list[$key]['content'] = array();
            }
            if ($val['special_type'] == 5) {
                $info_list[$key]['special_image'] = json_decode($val['special_image']);
            } else {
                $info_list[$key]['special_image'] = array();
            }
        }
        //         echo '<pre>';print_r($info_list);die;
        if ((in_array($column_id, array(3, 4, 10, 7, 8, 14, 6)) && $is_areas == 0) || ($column_id == 1 && $is_areas == 1)) {
            if (!$key_word) {
                $redis->set($name, $info_list, 1);
            } else {
                $redis->set($name, $info_list, 1);
            }
        }
       
       if ($is_pc) {
            if($alias){
                $info = ['id'=>$column_id,'alias' => $alias, 'name' => $column_name];
                if ($sub_alias && $result) {
                    $sub_info = ['sub_alias' => $sub_alias, 'sub_name' => $sub_name];
                    $info = array_merge($info, $sub_info);
                };
                return ['info' => $info, 'list' => $info_list];
            }
       } else {
            return $info_list;
       }
        return $info_list;
    }

    //查看新闻详情  含视频 不含直播
    public static function  Getinfos($info_id,$user_id){
        $news = new Query();
        $info = $news
            ->select("news.news_id,keywords,title,subtitle,type,column_id,area_id, reference_id,reference_type, cover_image,abstract as news_abstract,type_id,content,create_time,update_time,special_news_id,top_status,source_id,source_name,outer_url,external_link,live_id,click_count")->from("vrnews1.news")
            ->where(['news_id'=>$info_id])
            ->one();
        if(empty($info)){
            return false;
        }
        // 分享标题加后缀 （不含直播）
        $share_title = $info['title'] . ' |法制与新闻客户端';
       
        if($info['reference_id']){$reference_id = $info['reference_id'];}
        $info['create_time'] = str_replace('-','/',substr($info['create_time'],5,11));
        $click_count = $info['click_count'] + 1;
        News::updateAll(['click_count' => $click_count],["news_id" => $info_id]);
        $info_video = array();
        
        //如果有内部引用id 则用引用的id
        if(!empty($reference_id)) {
            $info = News::find()
                ->select("news_id,title,keywords,subtitle,type,column_id,area_id, reference_id,reference_type, cover_image,abstract as news_abstract,type_id,content,create_time,update_time,special_news_id,top_status,source_id,source_name,outer_url,external_link,live_id")
                ->where(['news_id' => $reference_id])
                ->asArray()
                ->one();
            
        }
        $info['share_title'] = $share_title;
        if($info['type'] == 4){
            //点击次数加1
            News::ClickVideoNum($info_id);

            //视频  查看相关详情
            $video = new Query();
            $info_video = NewsVideo::find()
                ->select("thumbnail_url,file_id,video_url,video_url1,video_url2,duration,height,height1,height2,width,width1,width2,size,size1,size2,category,play_count")->from("vrnews1.news_video")
                ->where(["news_id" => $info_id])
                ->asArray()
                ->one();

            //如果有内部引用id 则用引用的id
            if(!empty($reference_id)){
                $create_time = $info['create_time'];
                $play_count  = !$info_video['play_count'] ? 0 : $info_video['play_count'];

//                $news_find = new Query();
//                //视频信息
//                $info = News::find()
//                    ->select("news_id,title,keywords,subtitle,type,column_id,area_id, reference_id,reference_type, cover_image,abstract as news_abstract,type_id,content,create_time,update_time,special_news_id,top_status,source_id,source_name,outer_url,external_link,live_id")
//                    //->select("vrnews1.news")
//                    ->where(['news_id'=>$reference_id])
//                    ->asArray()
//                    ->one();

                $video_find = new Query();
                //播放信息
                $info_video = NewsVideo::find()
                    ->where(['news_id'=>$reference_id])
                    ->select("thumbnail_url,file_id,video_url,video_url1,video_url2,duration,height,height1,height2,width,width1,width2,size,size1,size2,category,play_count")
                    //->from("vrnews1.news_video")
                	->asArray()
                	->one();
                $info_video['play_count'] = $play_count;
                $info_video['create_time'] = $create_time;
            }

            if($info_video['video_url']){
                unset($info_video['video_url1']);
                unset($info_video['video_url2']);
            }else if($info_video['video_url1']){
                $info_video['video_url'] = $info_video['video_url1'];
                unset($info_video['video_url1']);
                unset($info_video['video_url2']);
            }else if($info_video['video_url2']){
                $info_video['video_url'] = $info_video['video_url2'];
                unset($info_video['video_url1']);
                unset($info_video['video_url2']);
            }else{
                unset($info_video['video_url1']);
                unset($info_video['video_url2']);
            }
            if($info_video['height']){
                unset($info_video['height1']);
                unset($info_video['height2']);
            }else if($info_video['height1']){
                $info_video['height'] = $info_video['height1'];
                unset($info_video['height1']);
                unset($info_video['height2']);
            }else if($info_video['height2']){
                $info_video['height'] = $info_video['height2'];
                unset($info_video['height1']);
                unset($info_video['height2']);
            }else{
                unset($info_video['height1']);
                unset($info_video['height2']);
            }
            if($info_video['width']){
                unset($info_video['width1']);
                unset($info_video['width2']);
            }else if($info_video['width1']){
                $info_video['width'] = $info_video['width1'];
                unset($info_video['width1']);
                unset($info_video['width2']);
            }else if($info_video['width2']){
                $info_video['width'] = $info_video['width2'];
                unset($info_video['width1']);
                unset($info_video['width2']);
            }else{
                unset($info_video['width1']);
                unset($info_video['width2']);
            }
            if($info_video['size']){
                unset($info_video['size1']);
                unset($info_video['size2']);
            }else if($info_video['size1']){
                $info_video['size'] = $info_video['size1'];
                unset($info_video['size1']);
                unset($info_video['size2']);
            }else if($info_video['size2']){
                $info_video['size'] = $info_video['size2'];
                unset($info_video['size1']);
                unset($info_video['size2']);
            }else{
                unset($info_video['size1']);
                unset($info_video['size2']);
            }

        }
        if(!empty($reference_id)){
            $info_id = $reference_id;
        }
        if($user_id){
            //查看是否收藏 此新闻 并返回收藏ID
            $is_collect = NewsUserCollect::find()
                ->where(["news_id" => $info_id, "user_id" => $user_id])
                ->asArray()->one();
            if($is_collect){
                $info['collect_id'] = $is_collect['collect_id'];
            }
        }
        $info['title'] = html_entity_decode($info['title'], ENT_QUOTES);  //转义标题的实体单双引号
        
		
        //点赞数和当前用户是否可点赞
        $praise_count = NewsPraise::find()->where(['news_id'=>$info_id,'status'=>'1', 'news_type'=>1])->count();
        $user_praise_count = NewsPraise::find()->where(['news_id'=>$info_id,'status'=>'1','user_id'=>$user_id])->count();
        $info['praise_count'] =  $praise_count > 0 ? $praise_count : '0';
        $info['user_is_praise'] =  $user_praise_count > 0 ? '1' : '0';
        
        //打赏总数和打赏人员列表
        $reward_count = NewsReward::find()->select(['news_reward.id'])->innerJoin('news','news_reward.news_id = news.news_id')->where(['news_reward.news_id'=>$info_id])->count();
        $info['reward_count'] =  $reward_count > 0 ? $reward_count : '0';
        $reward_users = NewsReward::find()
        ->select(['vruser1.user.user_id','vruser1.user.nickname as nick_name','vruser1.user.avatar'])
        ->innerJoin('vrnews1.news','vrnews1.news_reward.news_id = vrnews1.news.news_id')
        ->innerJoin('vruser1.user','vrnews1.news_reward.user_id = vruser1.user.user_id')
        ->where(['vrnews1.news_reward.news_id'=>$info_id,'vruser1.user.status'=>'1'])
        ->groupBy('vrnews1.news_reward.user_id')
        ->asArray()
        ->all();
        $info['reward_users'] = !empty($reward_users) ? $reward_users : array();
        
        //用户收藏状态
        $user_collect_count = NewsUserCollect::find()->where(['news_id'=>$info_id,'status'=>'1','user_id'=>$user_id])->count();
        $info['user_is_collect'] =  $user_collect_count > 0 ? '1' : '0';
        
        $info_res = array_merge($info,$info_video);
        
//         echo '<pre>';
//         print_r($info_res);die;
        return $info_res;
    }

    //专题列表
    function GetSpecialList($special_id,$pub_type,$special_types){
        //查看专题列表新闻
        $new_arr = array();
        $pub_where = '';
        if($pub_type == 1){
            $pub_where = " and web_pub = 1 ";
        }else{
            $pub_where = " and app_pub = 1 ";
        }
        if(!$special_types){
            $new_arr['column_id']   = '';
            $new_arr['column_name'] = '';

            $query = new Query();
            $new_arr['list'] = $query
                ->select(["news.news_id,title,subtitle,content,cover_image,reference_type,reference_id,type,column_id,type_id,special_news_id,top_status,source_id,source_name,special_title,special_abstract,special_image,thumbnail_url,duration,play_count,outer_url_ishot,outer_url,external_link,year(create_time) as year1,month(create_time) as month1,day(create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time"])
                ->from("vrnews1.news")
                ->leftJoin('vrnews1.news_video','vrnews1.news.news_id = vrnews1.news_video.news_id')
                ->where("news.special_news_id = ".$special_id." and news.weight >=70 and news.status=0".$pub_where)
                ->orderBy([
		                'case  when `year` is null then `year1` else `year` end'    => SORT_DESC,
		                'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
		                'case  when `day` is null then `day1` else `day` end'       => SORT_DESC,
		                'weight'       => SORT_DESC,
		                'refresh_time' => SORT_DESC,
		                'create_time'  => SORT_DESC])
                ->createCommand()->queryAll();
            $new_arr['list'] = News::getchecklist($new_arr['list'],$pub_type);
        }else {
            foreach ($special_types as $key => $val) {
                $new_arr[$key]['column_id']   = $val['type_id'];
                $new_arr[$key]['column_name'] = $val['name'];
                $new_arr[$key]['list'] = array();

                $query = new Query();
                $info = $query
                    ->select(["news.news_id,title,subtitle,content,cover_image,reference_type,reference_id,type,column_id,type_id,special_news_id,top_status,source_id,source_name,special_title,special_abstract,special_image,thumbnail_url,duration,play_count,outer_url_ishot,outer_url,external_link,year(create_time) as year1,month(create_time) as month1,day(create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time"])
                    ->from("vrnews1.news")
                    ->leftJoin('vrnews1.news_video','vrnews1.news.news_id = vrnews1.news_video.news_id')
                    ->where("news.type_id = " . $val['type_id'] . " and news.weight >=70 and news.status=0".$pub_where)
                    ->orderBy([
			                'case  when `year` is null then `year1` else `year` end'    => SORT_DESC,
			                'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
			                'case  when `day` is null then `day1` else `day` end'       => SORT_DESC,
			                'weight'       => SORT_DESC,
			                'refresh_time' => SORT_DESC,
			                'create_time'  => SORT_DESC])
                    ->createCommand()->queryAll();
                $info = News::getchecklist($info,$pub_type);
                $new_arr[$key]['list'] = $info;
            }
        }

        return $new_arr;
    }
    
    
    //新闻收藏状态
    function getUserNewsCollectStatus($news_id = null, $user_id = null){
    	if($news_id && $user_id){
    		$collect_model = NewsUserCollect::find()->where(['news_id'=>$news_id,'user_id'=>$user_id,'status'=>1])->asArray()->one();
    		if($collect_model){
    			return $collect_model['collect_id'];
    		}
    	}
    	return 0;
    }

    //视频 点击 次数加一
    public static function ClickVideoNum($news_id){
        //查看 是否有视频信息
        $news = new Query();
        $sel_new = $news
            ->from("vrnews1.news")
            ->leftJoin("vrnews1.news_video","vrnews1.news.news_id = vrnews1.news_video.news_id")
            ->where("news.news_id = $news_id")
            ->createCommand()->queryOne();
        if($sel_new){
            $play_count = $sel_new['play_count'] + 1;
            $res = NewsVideo::updateAll(['play_count' => $play_count],["news_id" => $news_id]);
            return $res;
        }else{
            return 0;
        }

    }

    //新闻 点击 次数加一
    public function ClickNewNum($news_id){
        //查看 是否有新闻信息
        $new = News::find()->where(['news_id'=>$news_id])->asArray()->one();
        if(!empty($new)){
            $click_count = $new['click_count'] + 1;
            $res = News::updateAll(['click_count' => $click_count],["news_id" => $news_id]);
            return $res;
        }else{
            return 0;
        }

    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['source_id', 'type', 'column_id', 'type_id', 'reference_id', 'reference_type', 'outer_url_ishot', 'app_pub', 'web_pub', 'is_watermark', 'weight', 'special_news_id', 'top_status', 'full_status', 'creator_id', 'area_id', 'competitor_id', 'refresh_time', 'update_time', 'click_count', 'special_entry', 'special_id', 'special_type'], 'integer'],
            [['content'], 'string'],
            [['create_time'], 'safe'],
            [['title', 'keywords', 'source_name', 'tags', 'full_title', 'creator_name', 'special_title'], 'string', 'max' => 45],
            [['subtitle', 'full_subtitle'], 'string', 'max' => 10],
            [['abstract'], 'string', 'max' => 255],
            [['cover_image', 'full_cover_image', 'special_image'], 'string', 'max' => 200],
            [['outer_url', 'external_link'], 'string', 'max' => 300],
            [['special_abstract'], 'string', 'max' => 125],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'news_id' => 'News ID',
            'title' => 'Title',
            'subtitle' => 'Subtitle',
            'keywords' => 'Keywords',
            'source_id' => 'Source ID',
            'source_name' => 'Source Name',
            'tags' => 'Tags',
            'type' => 'Type',
            'column_id' => 'Column ID',
            'type_id' => 'Type ID',
            'abstract' => 'Abstract',
            'cover_image' => 'Cover Image',
            'content' => 'Content',
            'reference_id' => 'Reference ID',
            'reference_type' => 'Reference Type',
            'outer_url' => 'Outer Url',
            'outer_url_ishot' => 'Outer Url Ishot',
            'external_link' => 'External Link',
            'app_pub' => 'App Pub',
            'web_pub' => 'Web Pub',
            'is_watermark' => 'Is Watermark',
            'weight' => 'Weight',
            'special_news_id' => 'Special News ID',
            'top_status' => 'Top Status',
            'full_status' => 'Full Status',
            'full_title' => 'Full Title',
            'full_subtitle' => 'Full Subtitle',
            'full_cover_image' => 'Full Cover Image',
            'create_time' => 'Create Time',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'area_id' => 'Area ID',
            'competitor_id' => 'Competitor ID',
            'refresh_time' => 'Refresh Time',
            'update_time' => 'Update Time',
            'click_count' => 'Click Count',
            'special_entry' => 'Special Entry',
            'special_id' => 'Special ID',
            'special_type' => 'Special Type',
            'special_title' => 'Special Title',
            'special_abstract' => 'Special Abstract',
            'special_image' => 'Special Image',
        ];
    }

    /*
     * 获取轮播图
     * */
    public static function getBanner($is_area,$type_id, $num, $pub_type, $get_vote=0, $alias=0,$sub_alias=0,$is_pc=0){
        /*if(empty($type_id)){
            $type_id = 1;
        }*/
        $info = [];
        
        $is_areas = $is_area;
        //pc端自动判断分类是否是本地
        if ($is_pc) {
            //获取所有城市字段
            $query = new Query();
            $result = $query->select("pinyin")
                ->from("vrnews1.area")
                ->column();
            $cities = $result;
            if ($alias) {
                if (in_array($alias, $cities)) {
                    $is_areas = 1;
                    $is_area = 1;
                    
                }
            }
        }
        //判断是否本地资源
        if ($is_areas) {
            // 支持别名查询本地资源
            if ($alias && !$type_id) {
                //通过别名查询本地栏目id
                $query = new Query();
                $result = $query->where('pinyin=:status',  [':status' => $alias])
                    ->select("area_id ,name")
                    ->from("vrnews1.area")
                    ->one();
                if ($result) {
                    $type_id = $result['area_id'];
                    $type_name = $result['name'];
                } else {
                    $type_id = 1;
                    $type_name = '北京';
                }
                
            }
        } else {
            //当传入的是栏目别名且无栏目id传入时，将有效别名转换成id
            if ($alias && !$type_id) {
                
                // 一级栏目查询
                $query = new Query();
                $result = $query->where('alias=:status', [':status' => $alias])
                    ->select("column_id, name")
                    ->from("vrnews1.news_column")
                    ->one();
                
                if ($result) {
                    $type_id = $result['column_id'];
                    if(isset($result['name'])){
                        $type_name = $result['name'];
                    }
                    
                    if($sub_alias){
                        $sub_name ='';
                        // 二级栏目查询
                        $query = new Query();
                        $subColumnResult = $query->where('alias=:status', [':status' => $sub_alias])
                            ->andWhere('column_id=:first', [':first' => $type_id])
                            ->select("column_id ,name")
                            ->from("vrnews1.news_column_type")
                            ->one();
                        if ($subColumnResult) {
                            $type_id = $subColumnResult['column_id'];
                            if(isset($subColumnResult['name'])) {
                                $sub_name = $subColumnResult['name'];
                            }
                        } else {
                            //二级栏目查询不存在时，指向默认一级栏目（column_id=1）
                            $type_id = 1;
                            $type_name = '北京';
                            
                        }
                    }
                } else {
                    $type_id = 1;
                    $type_name = '北京';
                }
            }
        }
        /*if((in_array($type_id, array(3,4,10,7,8,14,6)) && $is_areas == 0) || ($type_id == 1 && $is_areas == 1)) {
            $redis = Yii::$app->cache;
            $update = Yii::$app->params['environment']."_new_list_" . $is_areas . '_' . $type_id . '_update';
            $update_time = $redis->get($update);
            $name = Yii::$app->params['environment']."_new_list_" . $is_areas . '_' . $type_id . '_' .$pub_type.'_'. $update_time;
            $redis_info = $redis->get($name);
            if ($redis_info && count($redis_info) > 0) {
                return $redis_info;
            }
        }*/
        if($is_area == 1){
            //正常栏目 ID
            $is_area = "area_id";
        }else{
            //本地栏目 ID
            $is_area = "column_id";
        }
        if($pub_type == 1){
            $pub_where = " and web_pub = 1 ";
        }else{
            $pub_where = " and app_pub = 1 ";
        }

        if(!$get_vote){
            $pub_where .= " and vote_id=0 ";
        }

        $banner_list = News::find()->where("type = 2 and weight >=70 and status=0 and ".$is_area." = $type_id".$pub_where)
            ->select("news_id,title,subtitle,source_name,cover_image,outer_url,type,outer_url_ishot,external_link,year(create_time) as year,month(create_time) as month,day(create_time) as day,reference_id,reference_type, vote_id, update_time, abstract")
	        ->orderBy([
	        		'case when `refresh_time` is null then year(create_time) else year(from_unixtime(refresh_time)) end' => SORT_DESC,
	        		'case when `refresh_time` is null then month(create_time) else month(from_unixtime(refresh_time)) end' => SORT_DESC,
	        		'case when `refresh_time` is null then day(create_time) else day(from_unixtime(refresh_time)) end' => SORT_DESC,
	        		'weight' => SORT_DESC,
	        		'refresh_time' => SORT_DESC,
	        		'create_time' => SORT_DESC
	        ])
	        ->limit($num)
            ->asArray()->all();
	    
        if(!empty($banner_list)){

            foreach ($banner_list as $key=>$val){
                if($pub_type == 1){
                    $banner_list[$key]['cover_image'] = $val['cover_image'].'?imageMogr2/thumbnail/640x310!';
                    $banner_list[$key]['small_cover_image'] = $val['cover_image'].'?imageMogr2/thumbnail/157x76!';
                    $banner_list[$key]['big_cover_image']   = $val['cover_image'];
                }else {
                    $banner_list[$key]['cover_image'] = $val['cover_image'] . '?imageMogr2/thumbnail/562.5x273!';
                }

            }
        }
        /*if((in_array($type_id, array(3,4,10,7,8,14,6)) && $is_areas == 0) || ($type_id == 1 && $is_areas == 1)) {
            $redis->set($name, $banner_list);
        }*/
        foreach ($banner_list as $key => $val) {
            $banner_list[$key]['title'] = htmlspecialchars_decode($banner_list[$key]['title']);
            //如果是投票 增加url字段跳转
            if ($val['vote_id']) {
                $banner_list[$key]['vote_url'] = yii::$app->params['vote_url'] . '?vote_id=' . $val['vote_id'];
            }
        }
        if ($is_pc) {
            if ($alias) {
                $info = ['alias' => $alias, 'name' => $type_name];
                if ($sub_alias) {
                    $sub_info = ['sub_alias' => $sub_alias, 'sub_name' => $sub_name];
                    $info = array_merge($info, $sub_info);
                };
    
                return ['info' => $info, 'list' => $banner_list];
            }
            
        }else{
            return $banner_list;
        }
    
        return $banner_list;
    }
    
    
    
    /*
     * 推荐新闻
     */
    
    public static function getFirstNewsInfoByColumnName($column_name = null){
    	$news_info = '';
    	if($column_name){
    		if($column_name == '本地'){
    			$info_data= Area::find(['area_id'])->where(['name'=>'北京'])->one();
    			$column_id = $info_data->area_id;
    			$andwhere = 'news.area_id = '.$column_id;
    		}else{
    			$info_data = NewsColumn::find(['column_id'])->where(['name'=>$column_name])->one();
    			$column_id = $info_data->column_id;
    			$andwhere = 'news.column_id = '.$column_id;
    		}
    
    		if($column_id){
    			$query = new Query();
    			$query->select([
    					"news.news_id",
    					"news.abstract as news_abstract",
    					"news.title",
    					"news.subtitle",
    					"news.content",
    					"news.cover_image",
    					"news.reference_type",
    					"news.reference_id",
    					"news.type",
    					"news.column_id",
    					"news.area_id",
    					"DATE_FORMAT(`news`.`create_time`,'%Y/%m/%d %H:%i') as create_time",
    					"news.type_id",
    					"news.special_news_id",
    					"news.top_status",
    					"news.full_status",
    					"news.full_title",
    					"news.full_subtitle",
    					"news.full_cover_image",
    					"news.source_id",
    					"news.source_name",
    					"news.special_id",
    					"news.special_type",
    					"news.special_title",
    					"news.special_abstract",
    					"news.special_image",
    					"news_video.thumbnail_url",
    					"news_video.duration",
    					"news_video.play_count",
    					"news_video.category",
    					"news.outer_url_ishot",
    					"news.outer_url",
    					"year(`news`.`create_time`) as year1",
    					"month(`news`.`create_time`) as month1",
    					"day(`news`.`create_time`) as day1",
    					"year(from_unixtime(`news`.`refresh_time`)) as year",
    					"month(from_unixtime(`news`.`refresh_time`)) as month",
    					"day(from_unixtime(`news`.`refresh_time`)) as day",
    					"from_unixtime(`news`.`refresh_time`) as refresh_time",
    					"news_video.video_url as video_url",
    					"news_video.video_url1",
    					"news_video.video_url2",
    					"news_video.width",
    					"news_video.width1",
    					"news_video.width2",
    					"news_video.height",
    					"news_video.height1",
    					"news_video.height2",
    					"news_video.size",
    					"news_video.size1",
    					"news_video.size2" ,
    					"news_video.file_id as file_id"
    			]
    			)
    			->from('news')->innerJoin("news_video","news_video.news_id = news.news_id")
    			->where("news.weight >= 70 and news.type !=2 and news.top_status = 0 and news.special_news_id = 0 and ".$andwhere)
    			->orderBy([
    					'top_status' => SORT_DESC,
    					'case  when `year` is null then `year1` else `year` end'    => SORT_DESC,
    					'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
    					'case  when `day` is null then `day1` else `day` end'       => SORT_DESC,
    					'weight'       => SORT_DESC,
    					'refresh_time' => SORT_DESC,
    					'create_time'  => SORT_DESC]);
    			$command = $query->createCommand(self::getDb());
    			$news_info = $command->queryOne();
    		}
    	}
    	return $news_info;
    }

    /**
     * 获取直播竞猜为内容的新闻详情
     */
    public static function getQuizInfo($news_info,$user_id){
        $live_info = Live::find()->where(['live_id'=>$news_info['reference_id']])->asArray()->one();
        if(!$live_info){
            $quiz_info['code'] = '0001';
            $quiz_info['message'] = '直播不存在';
            return $quiz_info;
        }
        $quiz_info = Quiz::getQuizInfo($news_info['reference_id'], $user_id);
//        $quiz_info['status'] = Live::getLiveStatus($live_info['start_time'],$live_info['status']);
        if($quiz_info){
            foreach ($quiz_info as $k=>$v){
                foreach ($v['rule'] as $key=>$val){
                    //用户投注数量+（用户投注数量/此答案总投注数量）X（此竞此题目所有投注数量-此答案总投注数量）
                    if($val['is_betting'] == 1){
                        $get_amount = $val['bett_amount']+($val['bett_amount']/$val['amount'])*($v['amount']-$val['amount']);
                        $quiz_info[$k]['get_amount'] = round($get_amount);
                    }
                }
            }
        }
        return $quiz_info;
    }

    
    /**
     * 获取热门图集
     */
    public static function hotNews(){
        $end_time   = date('Y-m-d H:i:s', time());
        $start_time = date('Y-m-d H:i:s', strtotime('-2 day'));
        $news_list = static::find()->select("news_id,title,content,type,subtitle,reference_id")
            ->where(['type'=>5,'web_pub'=>1,'status'=>0])
            ->andWhere(['>=','create_time', $start_time])->andWhere(['<=', 'create_time', $end_time])
//            ->orderBy("`click_count` desc,convert(title USING gbk)")
            ->orderBy("`click_count` desc, create_time desc")
            ->limit(4)->asArray()->all();
        if(!empty($news_list)){
            foreach ($news_list as $key=>$value){
                if ($value['reference_id']) {  //引用
                    $news_info = News::find()->where(['news_id' => $value['reference_id']])->asArray()->one();
                    $value['content']  = $news_info['content'];
                    $value['news_id']  = $news_info['news_id'];
                    $value['title']    = $news_info['title'];
                    $value['type']     = $news_info['type'];
                    $value['subtitle'] = $news_info['subtitle'];
                }
                $content = json_decode($value['content']);
                $news_list[$key]['count'] = count($content);
                $news_list[$key]['img']   = $content;
                unset($news_list[$key]['content']);
            }
        }
        return $news_list;
    }
    
    /**
     * pc右侧
     */
    public static function hotTuji(){
    	$news_list = static::find()
    	->select(["
    			news_id,title,content,type,subtitle,reference_id,reference_type,
    			year(vrnews1.news.create_time) as year1,
    			month(vrnews1.news.create_time) as month1,
    			day(vrnews1.news.create_time) as day1,
    			year(from_unixtime(refresh_time)) as year,
    			month(from_unixtime(refresh_time)) as month,
    			day(from_unixtime(refresh_time)) as day,
    			from_unixtime(refresh_time) as refresh_time
    	"])
    	->where(['type'=>5,'web_pub'=>1,'status'=>0])->andWhere('weight>=70')
//     	->groupBy("title")
    	->orderBy([
    			'case  when `year` is null then `year1` else `year` end'    => SORT_DESC,
    			'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
    			'case  when `day` is null then `day1` else `day` end'       => SORT_DESC,
    			'refresh_time' => SORT_DESC,
    			'create_time'  => SORT_DESC
    	])->limit(6)->asArray()->all();
    	if(!empty($news_list)){
    		foreach ($news_list as $key=>$value){
                if ($value['reference_id'] && $value['reference_type'] == 1) {  //引用
                    $news_info = News::find()->where(['news_id' => $value['reference_id']])->asArray()->one();
                    $value['content']  = $news_info['content'];
                    $news_list[$key]['news_id']  = $news_info['news_id'];
                    $news_list[$key]['title']    = $news_info['title'];
                    $news_list[$key]['type']     = $news_info['type'];
                    $news_list[$key]['subtitle'] = $news_info['subtitle'];
                }
    			$content = json_decode($value['content']);
    			$news_list[$key]['count'] = count($content);
    			$news_list[$key]['img']   = $content;
    			unset($news_list[$key]['content']);
    		}
    	}
    	return $news_list;
    }
    
    /**
     * 保定热门图集
     */
    public static function getHotNewsByColumnId($cid){
    	$news_list = static::find()
    			->select(["news_id,title,content,type,subtitle,year(vrnews1.news.create_time) as year1,month(vrnews1.news.create_time) as month1,day(vrnews1.news.create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time"])
    				->where(['area_id'=>$cid,'type'=>5,'web_pub'=>1, 'subtitle'=>'印象保定'])->andWhere('weight>=70')
//     				->groupBy("title")
    				->orderBy([
    						'case  when `year` is null then `year1` else `year` end'    => SORT_DESC,
    						'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
    						'case  when `day` is null then `day1` else `day` end'       => SORT_DESC,
    						'refresh_time' => SORT_DESC,
    						'create_time'  => SORT_DESC
    				])
    				->limit(6)
    				->asArray()
    				->all();
    	if(!empty($news_list)){
    		foreach ($news_list as $key=>$value){
    			$content = json_decode($value['content']);
    			$news_list[$key]['count'] = count($content);
    			$news_list[$key]['img']   = $content;
    			unset($news_list[$key]['content']);
    		}
    	}
    	return $news_list;
    }

    /**
     * 获取热门图文
     */
    public static function hotImageText(){
        $end_time   = date('Y-m-d H:i:s', time());
        $start_time = date('Y-m-d H:i:s', strtotime('-1 day'));
        $news_list = static::find()->select("news_id,title,content,type,subtitle,cover_image,source_id,source_name")
            ->where(['type'=>7,'web_pub'=>1,'status'=>0])
            ->andWhere(['>=','create_time', $start_time])->andWhere(['<=', 'create_time', $end_time])
//            ->orderBy("`click_count` desc,convert(title USING gbk)")
            ->orderBy("`click_count` desc, create_time desc")
            ->limit(4)->asArray()->all();
        return $news_list;
    }

    /**
     * 精彩视频
     */
    public static function wonderfulVideo(){
        $end_time   = date('Y-m-d H:i:s', time());
        $start_time = date('Y-m-d H:i:s', strtotime('-2 day'));
        $news_list = static::find()->select("news.news_id,title,column_id,content,type,subtitle,thumbnail_url,duration,category,play_count,
        video_url,video_url1,video_url2,news.cover_image,news_video.status")
            ->innerJoin('news_video','news.news_id=news_video.news_id')
            ->where(['type'=>4,'web_pub'=>1,'news.status'=>0])
            ->andWhere(['>=','create_time', $start_time])->andWhere(['<=', 'create_time', $end_time])
//        	->orderBy("`click_count` desc,convert(title USING gbk)")
            ->orderBy("`click_count` desc, create_time desc")
            ->limit(4)->asArray()->all();
        return $news_list;
    }
    
    
    /**
     * 精彩视频
     */
    public static function BiaoZhunVideo(){
    	$news_list = static::find()
    		->select("news.news_id,title,content,type,subtitle,thumbnail_url,duration,category,play_count,
    				video_url,video_url1,video_url2,news.cover_image,
    				year(create_time) as year1,
	    			month(create_time) as month1,
	    			day(create_time) as day1,
	    			year(from_unixtime(refresh_time)) as year,
	    			month(from_unixtime(refresh_time)) as month,
	    			day(from_unixtime(refresh_time)) as day,
	    			from_unixtime(refresh_time) as refresh_time
    				")
            ->innerJoin('news_video','news.news_id=news_video.news_id')
            ->where(['type'=>4,'web_pub'=>1,'news_video.status'=>'2'])->andWhere("(news_video.video_url<>'' or news_video.video_url1<>'' or news_video.video_url2<>'') AND news_video.file_id<>'' AND news.weight>=70")
            ->groupBy("title")
            ->orderBy([
            		'case  when `year` is null then `year1` else `year` end'    => SORT_DESC,
            		'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
            		'case  when `day` is null then `day1` else `day` end'       => SORT_DESC,
            		'refresh_time' => SORT_DESC,
            		'create_time'  => SORT_DESC
            ])
    		->limit(6)->asArray()->all();
            return $news_list;
    }
    
    /**
     * 栏目所属精彩视频
     */
    public static function columnWonderfulVideo($id, $type){
    	if($type == 0){
    		$where = "type=4 AND (news_video.video_url<>'' or news_video.video_url1<>'' or news_video.video_url2<>'') AND news_video.file_id<>'null' AND web_pub=1 AND column_id=$id AND subtitle='推荐' AND news.weight>=70";
            $news_list = static::find()->select("news.news_id,title,content,type,subtitle,thumbnail_url,duration,category,play_count,
                    video_url,video_url1,video_url2,news.cover_image,
                    year(create_time) as year1,
                    month(create_time) as month1,
                    day(create_time) as day1,
                    year(from_unixtime(refresh_time)) as year,
                    month(from_unixtime(refresh_time)) as month,
                    day(from_unixtime(refresh_time)) as day,
                    from_unixtime(refresh_time) as refresh_time
                    ")
                ->innerJoin('news_video','news.news_id=news_video.news_id')
                ->where($where)
                ->groupBy("title")
                ->orderBy([
                    'case  when `year` is null then `year1` else `year` end'    => SORT_DESC,
                    'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
                    'case  when `day` is null then `day1` else `day` end'       => SORT_DESC,
                    'refresh_time' => SORT_DESC,
                    'create_time'  => SORT_DESC
                ])->limit(6)->asArray()
                ->all();
            return $news_list;
    	}else{

            $where = "type in (4,7) AND ( case when news.type=4  then ( (video_url<>'' or video_url1<>'' or video_url2<>'') and file_id<> 'null')  when news.type=7 then (news.content LIKE '%.mp4%') else content is NULL end ) AND web_pub=1 AND area_id=$id AND subtitle='古城视角' AND news.weight>=70";
            $news_list = static::find()->select("news.news_id,title,content,type,subtitle,thumbnail_url,duration,category,play_count,
                    video_url,video_url1,video_url2,news.cover_image,
                    year(create_time) as year1,
                    month(create_time) as month1,
                    day(create_time) as day1,
                    year(from_unixtime(refresh_time)) as year,
                    month(from_unixtime(refresh_time)) as month,
                    day(from_unixtime(refresh_time)) as day,
                    from_unixtime(refresh_time) as refresh_time
                    ")
                ->leftJoin('news_video','news.news_id=news_video.news_id')
                ->where($where)
                ->groupBy("title")
                ->orderBy([
                    'case  when `year` is null then `year1` else `year` end'    => SORT_DESC,
                    'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
                    'case  when `day` is null then `day1` else `day` end'       => SORT_DESC,
                    'refresh_time' => SORT_DESC,
                    'create_time'  => SORT_DESC
                ])->limit(6)->asArray()
                ->all();
            return $news_list;
    	}
    }
    public function getchecklist($info_list,$pub_type){
        $live_type = array(0=>'9',1=>'10',2=>'11',3=>'12',4=>'13',5=>'14');
        if(!empty($info_list)){
            foreach ($info_list as $key=>$value){
                if ($pub_type != 1) {
                    if ($value['type'] == 3) { //专题
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                    } else if ($value['type'] == 4) { //视频
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/206x142!' : '';
                    } else if ($value['type'] == 5 && !empty($value['content'])) { //图集
                        if(!empty($value['reference_type']) && intval($value['reference_type']) == 1 && !empty($value['reference_id'])){
                            //查看 被引用图集信息
                            $ref_news = News::find()->where(['news_id'=>$value['reference_id']])->asArray()->one();
                            if(!empty($ref_news['content'])){
                                $ref_news['content'] = json_decode($ref_news['content']);
                                foreach ($ref_news['content'] as $re_k=>$re_v){
                                    if($re_k < 3) {
                                        if(is_object($ref_news['content'][$re_k])){
                                            $str_con = substr($ref_news['content'][$re_k]->img,-2);
                                            if($str_con == '/s'){
                                                $info_list[$key]['content'][$re_k]->img = substr($ref_news['content'][$re_k]->img,0,-2);
                                            }
                                            if($info_list[$key]['content']=='""'){
                                                $tmp = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/224x150!' : '';
                                                $info_list[$key]['content'] = array($re_k=>array('img'=>$tmp));
                                            }else{
                                                $info_list[$key]['content'][$re_k]['img'] = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/224x150!' : '';
                                            }
                                        }else {
                                            $str_con = substr($ref_news['content'][$re_k]['img'],-2);
                                            if($str_con == '/s'){
                                                $info_list[$key]['content'][$re_k]['img'] = substr($ref_news['content'][$re_k]['img'],0,-2);
                                            }
                                            $info_list[$key]['content'][$re_k]['img'] = $re_v['img'] ? $re_v['img'] . '?imageMogr2/thumbnail/224x150!' : '';
                                        }
                                    }
                                }
                            }else{
                                $info_list[$key]['content'] = array();
                            }
                        }else {
                            $info_list[$key]['content'] = json_decode($info_list[$key]['content']);

                            if (!empty($info_list[$key]['content'])) {
                                foreach ($info_list[$key]['content'] as $k => $v) {
                                    if ($k < 3) {
                                        if (is_object($info_list[$key]['content'][$k])) {
                                            $str_con = substr($info_list[$key]['content'][$k]->img, -2);
                                            if ($str_con == '/s') {
                                                $info_list[$key]['content'][$k]->img = substr($info_list[$key]['content'][$k]->img, 0, -2);
                                            }
                                            $info_list[$key]['content'][$k]->img = $info_list[$key]['content'][$k]->img ? $info_list[$key]['content'][$k]->img . '?imageMogr2/thumbnail/224x150!' : '';
                                        } else {
                                            $str_con = substr($info_list[$key]['content'][$k]['img'], -2);
                                            if ($str_con == '/s') {
                                                $info_list[$key]['content'][$k]['img'] = substr($info_list[$key]['content'][$k]['img'], 0, -2);
                                            }
                                            $info_list[$key]['content'][$k]['img'] = $info_list[$key]['content'][$k]['img'] ? $info_list[$key]['content'][$k]['img'] . '?imageMogr2/thumbnail/224x150!' : '';
                                        }

                                    }

                                }
                            }else{
                                $info_list[$key]['content'] = array();
                            }
                        }
                    } else if ($value['type'] == 7) { //图文
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/206x142!' : '';
                    } else if (in_array($value['type'], $live_type)) { //直播类型新闻
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/355x170!' : '';
                    }

                } else {
                    if ($value['type'] == 3) { //专题
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                    } else if ($value['type'] == 4) {
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/145x100!' : '';
                    } else if ($value['type'] == 5 && !empty($value['content'])) {
                        if(!empty($value['reference_type']) && intval($value['reference_type']) == 1 && !empty($value['reference_id'])){
                            //查看 被引用图集信息
                            $ref_news = News::find()->where(['news_id'=>$value['reference_id']])->asArray()->one();
                            if(!empty($ref_news['content'])){
                                $ref_news['content'] = json_decode($ref_news['content']);
                                foreach ($ref_news['content'] as $re_k=>$re_v){
                                    if($re_k < 4) {
                                        if(is_object($ref_news['content'][$re_k])){
                                            $str_con = substr($ref_news['content'][$re_k]->img,-2);
                                            if($str_con == '/s'){
                                                $info_list[$key]['content'][$re_k]->img = substr($ref_news['content'][$re_k]->img,0,-2);
                                            }
                                            if($info_list[$key]['content']=='""'){
                                                $tmp = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/150x100!' : '';
                                                $info_list[$key]['content'] = array($re_k=>array('img'=>$tmp));
                                            }else{
                                                $info_list[$key]['content'][$re_k]['img'] = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/150x100!' : '';
                                            }
                                        }else {
                                            $str_con = substr($ref_news['content'][$re_k]['img'],-2);
                                            if($str_con == '/s'){
                                                $info_list[$key]['content'][$re_k]['img'] = substr($ref_news['content'][$re_k]['img'],0,-2);
                                            }
                                            $info_list[$key]['content'][$re_k]['img'] = $re_v['img'] ? $re_v['img'] . '?imageMogr2/thumbnail/150x100!' : '';
                                        }
                                    }
                                }
                            }else{
                                $info_list[$key]['content'] = array();
                            }
                        }else {
                            $info_list[$key]['content'] = json_decode($info_list[$key]['content']);
                            foreach ($info_list[$key]['content'] as $k => $v) {
                                if ($k < 4) {
                                    if (is_object($info_list[$key]['content'][$k])) {
                                        $str_con = substr($info_list[$key]['content'][$k]->img, -2);
                                        if ($str_con == '/s') {
                                            $info_list[$key]['content'][$k]->img = substr($info_list[$key]['content'][$k]->img, 0, -2);
                                        }
                                        $info_list[$key]['content'][$k]->img = $v->img ? $v->img . '?imageMogr2/thumbnail/150x100!' : '';
                                    } else {
                                        $str_con = substr($info_list[$key]['content'][$k]['img'], -2);
                                        if ($str_con == '/s') {
                                            $info_list[$key]['content'][$k]['img'] = substr($info_list[$key]['content'][$k]['img'], 0, -2);
                                        }
                                        $info_list[$key]['content'][$k]['img'] = $v['img'] ? $v['img'] . '?imageMogr2/thumbnail/150x100!' : '';
                                    }


                                }
                            }
                        }
                    } else if ($value['type'] == 7) {
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/145x100!' : '';
                    } else if (in_array($value['type'], $live_type)) { //直播类型新闻
                        $info_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/208x100!' : '';
                    }
                }
            }
        }

        return $info_list;
    }
    
    public function getLive()
    {
        return $this->hasOne(Live::className(), ['news_id' => 'news_id']);
        
    }
    
    public function getColumn()
    {
        return $this->hasOne(NewsColumn::className(), ['column_id' => 'column_id']);
        
    }
    
    public function getArea()
    {
        return $this->hasOne(Area::className(), ['area_id' => 'area_id']);
        
    }
}
