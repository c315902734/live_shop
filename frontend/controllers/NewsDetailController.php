<?php
namespace frontend\controllers;

use common\models\LiveUserSubscribe;
use common\models\NewsPraise;
use common\models\NewsReward;
use common\models\NewsUserCollect;
use Yii;
use common\models\Live;
use common\models\News;
use common\models\NewsColumnType;
use common\models\Quiz;
use common\models\SpecialColumnType;
use common\models\UserAmount;

class NewsDetailController extends PublicBaseController{

    /**
     * 详情页面
     */
    public function actionNewsDetail(){
        $news_id = isset($this->params['id']) ? $this->params['id'] : '';
        $type    = isset($this->params['type']) ? $this->params['type'] : '';
        $user_id = isset($this->params['user_id']) ? $this->params['user_id'] : '';
        $is_pc   = isset($this->params['is_pc']) ? $this->params['is_pc'] : '';

        $live_types = array(0=>'9',1=>'10',2=>'11',3=>'12',4=>'13',5=>'14');
        $news_info = News::find()->leftJoin('news_video','news_video.news_id = news.news_id')
            ->where(['news.news_id'=>$news_id,'news.status'=>0])->asArray()->one();

        $click_count = $news_info['click_count'] + 1;
        News::updateAll(['click_count' => $click_count],["news_id" => $news_id]);

        if($news_info['reference_id'] && $news_info['reference_type'] != 5){
            $news_id = $news_info['reference_id'];
            $news_info = News::find()->leftJoin('news_video','news_video.news_id = news.news_id')
            ->where(['news.news_id'=>$news_id,'news.status'=>0])->asArray()->one();
        }

        if($user_id){
            $param['user_id']     = $user_id;
            $param['operate_cnt'] = 5;
            $param['operate']     = '1';
            $param['operate_name'] = '阅读新闻';
            $param['task_id']      = '2';
            UserAmount::addUserAmount($param);
        }
        if(!empty($news_info)){
            if($news_info['reference_type'] == 5){ //直播竞猜
                $res = News::getQuizInfo($news_info, $user_id);
                if(isset($res['code'])){
                    $this->_errorData($res['code'], $res['message']);
                }
                $news_info['quiz_info'] = $res;
                $live_info = Live::find()->where(['live_id'=>$news_info['reference_id']])->asArray()->one();
                $news_info['status']    = Live::getLiveStatus($live_info['start_time'],$live_info['status']);
                $news_info['token']     = $this->token;
            }else{
                if($news_info['type'] == 3){ //专题
                	$new_arr = array();
                    if($news_info['special_news_id'] == '0'){
                        $redis = Yii::$app->cache;
                        $update = Yii::$app->params['environment']."_special_list_" . $news_id . '_update';
                        $update_time = $redis->get($update);
                        $name = Yii::$app->params['environment']."_special_list_" . $news_id . '_'.$is_pc . '_' . $update_time;
                        $redis_info = $redis->get($name);
                        if ($redis_info && count($redis_info) > 0) {
                            foreach ($redis_info['type_news'] as $list_key=>$live_val){
                                if(count($live_val['list']) > 0){
                                    foreach ($live_val['list'] as $val_key=>$val_val){
                                        // 9普通直播/视频直播 10VR直播 11图文直播 12视频加图文直播 13 VR加图文直播
                                        if (in_array($val_val['type'],array(9,10,11,12,13,14))) {
                                            $live_info = Live::find()->where(['live_id' => $val_val['live_id']])->asArray()->one();
                                            $redis_info['type_news'][$list_key]['list'][$val_key]['status'] = Live::getLiveStatus($live_info['start_time'], $live_info['status']);                                                 //直播状态
                                            $redis_info['type_news'][$list_key]['list'][$val_key]['live_is_subscribe'] = 0; //是否预约
                                            if (!empty($user_id)) {
                                                $is_subscribe = LiveUserSubscribe::find()->where(['user_id' => $user_id, 'live_id' => $val_val['live_id'], 'status' => 1])->count();
                                                $redis_info['type_news'][$list_key]['list'][$val_key]['live_is_subscribe'] = $is_subscribe;
                                            }
                                            $redis_info['type_news'][$list_key]['list'][$val_key]['chatroom_id'] = 'room_' . $val_val['live_id']; //聊天室ID
                                            $redis_info['type_news'][$list_key]['list'][$val_key]['live_play_count'] = $live_info['play_count']; //直播点击数量
                                        }
                                    }
                                }
                            }

                            return $this->_successData($redis_info);
                        }
                        $type_list = SpecialColumnType::find()
                            ->where(['news_id'=>$news_id,'status'=>1])->andWhere(['>=','weight','70'])
                            ->orderBy("weight desc,create_time desc")
                            ->asArray()->all();
                        /* 区分手机端和app端 */
                        if($is_pc){
                        	$trans_where = ' and web_pub=1 ';
                        }else{
                        	$trans_where = ' and app_pub=1 ';
                        }
                            
                        foreach ($type_list as $key => $val) {
                            if($is_pc == 1){
                                $new_arr[$val['type_id']]['column_id']   = $val['type_id'];
                                $new_arr[$val['type_id']]['column_name'] = $val['name'];
                                $new_arr[$val['type_id']]['list'] = array();
                            }else{
                                $new_arr[$key]['column_id']   = $val['type_id'];
                                $new_arr[$key]['column_name'] = $val['name'];
                                $new_arr[$key]['list'] = array();
                            }
                            $info = array();
                            //是否 有视频 显示
                            $trans_where .= " and (file_id is null or  ( (video_url<>'' or video_url1<>'' or video_url2<>'') and file_id<> 'null') )";
                            $info = News::find()
                                ->leftJoin('vrnews1.news_video','vrnews1.news.news_id = vrnews1.news_video.news_id')
                                ->where("news.type_id = " . $val['type_id'] . "  and special_news_id = ".$news_id." and news.status=0 and news.weight >=70 ".$trans_where)
                                ->select(["news.news_id,abstract as news_abstract,title,subtitle,content,cover_image,reference_type,reference_id,type,column_id,vote_id,area_id,DATE_FORMAT(`create_time`,'%Y/%m/%d %H:%i') as create_time,type_id,special_news_id,top_status,full_status,full_title,full_subtitle,full_cover_image,source_id,source_name,special_id,special_type,special_title,special_abstract,special_image,thumbnail_url,duration,play_count,category,outer_url_ishot,outer_url,external_link,live_id,year(create_time) as year1,month(create_time) as month1,day(create_time) as day1,year(from_unixtime(refresh_time)) as year,month(from_unixtime(refresh_time)) as month,day(from_unixtime(refresh_time)) as day,from_unixtime(refresh_time) as refresh_time"])
                                ->orderBy([
                                    'case  when `year` is null then `year1` else `year` end'    => SORT_DESC,
                                    'case  when `month` is null then `month1` else `month` end' => SORT_DESC,
                                    'case  when `day` is null then `day1` else `day` end'       => SORT_DESC,
                                    'weight'       => SORT_DESC,
                                    'refresh_time' => SORT_DESC,
                                    'create_time'  => SORT_DESC])
                                ->asArray()->all();

                            if($info) {
                                foreach ($info as $k => &$v) {
//                                    if($is_pc && $v['type'] == 5){
//                                        $info[$k]['content'] = json_decode($v['content']);
//                                    }
                                    // 9普通直播/视频直播 10VR直播 11图文直播 12视频加图文直播 13 VR加图文直播
                                    if(in_array($v['type'],array(9,10,11,12,13,14))){
                                        $live_info = Live::find()->where(['live_id'=>$v['live_id']])->asArray()->one();
                                        $info[$k]['status'] = Live::getLiveStatus($live_info['start_time'], $live_info['status']); //直播状态
                                        $info[$k]['live_is_subscribe'] = 0; //是否预约
                                        if(!empty($user_id)){
                                            $is_subscribe = LiveUserSubscribe::find()->where(['user_id'=>$user_id, 'live_id'=>$v['live_id'], 'status'=>1])->count();
                                            $info[$k]['live_is_subscribe'] = $is_subscribe;
                                        }
                                        $info[$k]['chatroom_id']      = 'room_'.$v['live_id']; //聊天室ID
                                        $info[$k]['live_play_count']  = $live_info['play_count']; //直播点击数量
                                    }
                                    if(!$is_pc) {
                                        if ($v['type'] == 3) { //专题
                                            $info[$k]['cover_image'] = $v['cover_image'] ? $v['cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                                            $info[$k]['full_cover_image'] = $v['full_cover_image'] ? $v['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                                            $info[$k]['content'] = array();
                                        } else if ($v['type'] == 4) { //视频
                                            $info[$k]['cover_image'] = $v['cover_image'] ? $v['cover_image'] . '?imageMogr2/thumbnail/206x142!' : '';
                                            $info[$k]['full_cover_image'] = $v['full_cover_image'] ? $v['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                                            $info[$k]['content'] = array();
                                        } else if ($v['type'] == 5 && !empty($v['content'])) { //图集
                                            $info[$k]['content'] = json_decode($info[$k]['content']);
                                            if(!empty($v['reference_type']) && intval($v['reference_type']) == 1 && !empty($v['reference_id'])){
                                                //查看 被引用图集信息
                                                $ref_news = News::find()->where(['news_id'=>$v['reference_id']])->asArray()->one();
                                                if(!empty($ref_news['content'])){
                                                    $ref_news['content'] = json_decode($ref_news['content']);
                                                    foreach ($ref_news['content'] as $re_k=>$re_v){
                                                        if($re_k < 3) {
                                                            if(is_object($ref_news['content'][$re_k])){
                                                                $str_con = substr($ref_news['content'][$re_k]->img,-2);
                                                                if($str_con == '/s'){
                                                                    $info[$k]['content'][$re_k]->img = substr($ref_news['content'][$re_k]->img,0,-2);
                                                                }
                                                                if($info[$k]['content']=='""'){
                                                                    $tmp = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/224x150!' : '';
                                                                    $info[$k]['content'] = array($re_k=>array('img'=>$tmp));
                                                                }else{
                                                                    $info[$k]['content'][$re_k]['img'] = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/224x150!' : '';
                                                                }
                                                                
                                                            }else {
                                                                $str_con = substr($ref_news['content'][$re_k]['img'],-2);
                                                                if($str_con == '/s'){
                                                                    $info[$k]['content'][$re_k]['img'] = substr($ref_news['content'][$re_k]['img'],0,-2);
                                                                }
                                                                $info[$k]['content'][$re_k]['img'] = $re_v['img'] ? $re_v['img'] . '?imageMogr2/thumbnail/224x150!' : '';
                                                            }
                                                        }
                                                    }
                                                }else{
                                                    $info[$k]['content'] = array();
                                                }
                                            }else {
                                                if (!empty($info[$k]['content'])) {
                                                    foreach ($info[$k]['content'] as $kk => $vv) {
                                                        if ($kk < 3) {
                                                            if (is_object($info[$k]['content'][$kk])) {
                                                                $str_con = substr($info[$k]['content'][$kk]->img, -2);
                                                                if ($str_con == '/s') {
                                                                    $info[$k]['content'][$kk]->img = substr($info[$k]['content'][$kk]->img, 0, -2);
                                                                }
                                                                $info[$k]['content'][$kk]->img = $info[$k]['content'][$kk]->img ? $info[$k]['content'][$kk]->img . '?imageMogr2/thumbnail/224x150!' : '';
                                                            } else {
                                                                $str_con = substr($info[$k]['content'][$kk]['img'], -2);
                                                                if ($str_con == '/s') {
                                                                    $info[$k]['content'][$kk]['img'] = substr($info[$k]['content'][$kk]['img'], 0, -2);
                                                                }
                                                                $info[$k]['content'][$kk]['img'] = $info[$k]['content'][$kk]['img'] ? $info[$k]['content'][$kk]['img'] . '?imageMogr2/thumbnail/224x150!' : '';

                                                            }
                                                        }
                                                    }
                                                }else{
                                                    $info[$k]['content'] = array();
                                                }
                                            }
                                            $info[$k]['full_cover_image'] = $v['full_cover_image'] ? $v['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                                        } else if ($v['type'] == 7) { //图文
                                            $info[$k]['cover_image'] = $v['cover_image'] ? $v['cover_image'] . '?imageMogr2/thumbnail/206x142!' : '';
                                            $info[$k]['full_cover_image'] = $v['full_cover_image'] ? $v['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                                            $info[$k]['content'] = array();
                                        } else if (in_array($v['type'], $live_types)) { //直播类型新闻
                                            $info[$k]['cover_image'] = $v['cover_image'] ? $v['cover_image'] . '?imageMogr2/thumbnail/710x340!' : '';
                                            $info[$k]['full_cover_image'] = $v['full_cover_image'] ? $v['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                                            $info[$k]['content'] = array();
                                        }
                                    }else{
                                        if ($v['type'] == 3) { //专题
                                            $info[$k]['cover_image'] = $v['cover_image'] ? $v['cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                                            $info[$k]['full_cover_image'] = $v['full_cover_image'] ? $v['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                                        } else if ($v['type'] == 4) {
                                            $info[$k]['cover_image'] = $v['cover_image'] ? $v['cover_image'] . '?imageMogr2/thumbnail/145x100!' : '';
                                            $info[$k]['full_cover_image'] = $v['full_cover_image'] ? $v['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                                        } else if ($v['type'] == 5 && !empty($v['content'])) {
                                            if(!empty($v['reference_type']) && intval($v['reference_type']) == 1 && !empty($v['reference_id'])){
                                                //查看 被引用图集信息
                                                $info[$k]['content'] = '';
                                                $ref_news = News::find()->where(['news_id'=>$v['reference_id']])->asArray()->one();
                                                if(!empty($ref_news['content'])){
                                                    $ref_news['content'] = json_decode($ref_news['content']);
                                                    foreach ($ref_news['content'] as $re_k=>$re_v){
                                                        if($re_k < 4) {
                                                            if(is_object($ref_news['content'][$re_k])){
                                                                $str_con = substr($ref_news['content'][$re_k]->img,-2);
                                                                if($str_con == '/s'){
                                                                    $info[$k]['content'][$re_k]->img = substr($ref_news['content'][$re_k]->img,0,-2);
                                                                }
                                                                if($info[$k]['content']=='""'){
                                                                    $tmp = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/150x100!' : '';
                                                                    $info[$k]['content'] = array($re_k=>array('img'=>$tmp));
                                                                }else{
                                                                    $tmp = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/150x100!' : '';
                                                                    $info[$k]['content'][$re_k] = array('img'=>$tmp);
                                                                }
                                                            }else {
                                                                $str_con = substr($ref_news['content'][$re_k]['img'],-2);
                                                                if($str_con == '/s'){
                                                                    $info[$k]['content'][$re_k]['img'] = substr($ref_news['content'][$re_k]['img'],0,-2);
                                                                }
                                                                $info[$k]['content'][$re_k]['img'] = $re_v['img'] ? $re_v['img'] . '?imageMogr2/thumbnail/150x100!' : '';
                                                            }
                                                        }
                                                    }
                                                    unset($tmp);
                                                }else{
                                                    $info[$k]['content'] = array();
                                                }
                                            }else {
                                                $info[$k]['content'] = json_decode($info[$k]['content']);
                                                foreach ($info[$k]['content'] as $kk => $vv) {
                                                    if ($k < 4) {
                                                        if (is_object($info[$k]['content'][$kk])) {
                                                            $str_con = substr($info[$k]['content'][$kk]->img, -2);
                                                            if ($str_con == '/s') {
                                                                $info[$k]['content'][$kk]->img = substr($info[$k]['content'][$kk]->img, 0, -2);
                                                            }
                                                            $info[$k]['content'][$kk]->img = $info[$k]['content'][$kk]->img ? $info[$k]['content'][$kk]->img . '?imageMogr2/thumbnail/150x100!' : '';
                                                        } else {
                                                            $str_con = substr($info[$k]['content'][$kk]['img'], -2);
                                                            if ($str_con == '/s') {
                                                                $info[$k]['content'][$kk]['img'] = substr($info[$k]['content'][$kk]['img'], 0, -2);
                                                            }
                                                            $info[$k]['content'][$kk]['img'] = $info[$k]['content'][$kk]['img'] ? $info[$k]['content'][$kk]['img'] . '?imageMogr2/thumbnail/150x100!' : '';

                                                        }

                                                    }
                                                }
                                            }
                                            $info[$k]['full_cover_image'] = $v['full_cover_image'] ? $v['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                                        } else if ($v['type'] == 7) {
                                            $info[$k]['cover_image'] = $v['cover_image'] ? $v['cover_image'] . '?imageMogr2/thumbnail/145x100!' : '';
                                            $info[$k]['full_cover_image'] = $v['full_cover_image'] ? $v['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                                        } else if (in_array($v['type'], $live_types)) { //直播类型新闻
                                            $info[$k]['cover_image'] = $v['cover_image'] ? $v['cover_image'] . '?imageMogr2/thumbnail/208x100!' : '';
                                            $info[$k]['full_cover_image'] = $v['full_cover_image'] ? $v['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                                        }
                                    }
                                    if($v['reference_id']){
                                        $quote_info = News::find()
                                            ->leftJoin('vrnews1.news_video','vrnews1.news.news_id = vrnews1.news_video.news_id')
                                            ->where("news.news_id = ".$v['reference_id'])
                                            ->select("news.news_id,title,subtitle,weight,content,cover_image,reference_type,reference_id,type,column_id,type_id,special_news_id,top_status,source_id,source_name,full_status,full_title,full_subtitle,full_cover_image,special_title,special_abstract,special_image,thumbnail_url,duration,play_count,outer_url_ishot,outer_url,year(create_time) as year,month(create_time) as month,day(create_time) as day,news_video.category")
                                            ->orderBy("year desc,month desc,day desc,weight desc,refresh_time desc")
                                            ->asArray()->one();

                                        $v['category'] = $quote_info['category'];
                                        $v['duration'] = $quote_info['duration'];
                                        $v['play_count'] = !$v['play_count'] ? 0 : $v['play_count'];
                                    }

                                    //投票
                                    if($v['vote_id']){
                                        $v['vote_url'] = yii::$app->params['vote_url'].'?vote_id='.$v['vote_id'];
                                    }

                                    if($v['type']!='5'){
                                        if($v['type'] == 3){
                                            $out_info = News::find()->where(['news_id'=>$v['reference_id']])->asArray()->one();
                                            $info[$k]['news_title'] = $out_info['title'];
                                        }
                                        if(mb_strlen($v['title'],'utf8') >= 25){
                                            $info[$k]['title'] = $this->strsub_utf8(htmlspecialchars_decode($v['title']),0,25,'UTF8')."...";
                                        }else{
                                            $info[$k]['title'] = $info[$k]['title'];
                                        }
                                    }else{
                                        if(mb_strlen($v['title'],'utf8') >= 21){
                                            $info[$k]['title'] = $this->strsub_utf8(htmlspecialchars_decode($v['title']),0,21,'UTF8')."...";
                                        }else{
                                            $info[$k]['title'] = htmlspecialchars_decode($info[$k]['title']);
                                        }
                                    }
                                }
                                unset($v);
                            }
                            if($is_pc == 1){
                                $new_arr[$val['type_id']]['list'] = $info;
                            }else{
                                $new_arr[$key]['list'] = $info;
                            }
                        }

                        $type_news = News::find()->leftJoin('news_column_type t','t.type_id = news.type_id')
                            ->leftJoin('news_video v','v.news_id = news.news_id')
                            ->where(['special_news_id'=>$news_id])->select("news.*,year(news.create_time) as year,month(news.create_time) as month,day(news.create_time) as day")
                            ->orderBy("year desc,month desc,day desc,news.weight desc,news.refresh_time desc")
                            ->asArray()->all();
                        if($type_news){
                            $result = array();
                            foreach($type_news as $key=>$val){
                                $result[$val['type_id']][] = $val;
                            }
                        }
                        $news_info['type_list'] = $type_list;
                        $news_info['type_news'] = $new_arr;
                        $redis->set($name, $news_info, 86400);
                    }else{
                        if($news_info['type'] == 3) { //专题
                            if ($news_info['special_news_id'] == '0') {
                                $redis = Yii::$app->cache;
                                $update = Yii::$app->params['environment']."_special_list_" . $news_id . '_update';
                                $update_time = $redis->get($update);
                                $name = Yii::$app->params['environment']."_special_list_" . $news_id . '_'.$is_pc . '_' .$update_time;
                                $redis_info = $redis->get($name);
                                if ($redis_info && count($redis_info) > 0) {
                                    return $this->_successData($redis_info);
                                }
                                $type_list = NewsColumnType::find()->where(['news_id' => $news_id, 'status' => 1])
                                    ->andWhere(['>=', 'weight', '70'])->orderBy("weight desc,create_time desc")
                                    ->asArray()->all();
                                foreach ($type_list as $key=>$value){
                                    if(!$is_pc) {
                                        if ($value['type'] == 3) { //专题
                                            $type_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                                            $type_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                                            $type_list[$key]['content'] = array();
                                        } else if ($value['type'] == 4) { //视频
                                            $type_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/206x142!' : '';
                                            $type_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                                            $type_list[$key]['content'] = array();
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
                                                                    $type_list[$key]['content'][$re_k]->img = substr($ref_news['content'][$re_k]->img,0,-2);
                                                                }
                                                                if($type_list[$key]['content']=='""'){
                                                                    $tmp = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/224x150!' : '';
                                                                    $type_list[$key]['content'] = array($re_k=>array('img'=>$tmp));
                                                                }else{
                                                                    $type_list[$key]['content'][$re_k]['img'] = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/224x150!' : '';
                                                                }
                                                            }else {
                                                                $str_con = substr($ref_news['content'][$re_k]['img'],-2);
                                                                if($str_con == '/s'){
                                                                    $type_list[$key]['content'][$re_k]['img'] = substr($ref_news['content'][$re_k]['img'],0,-2);
                                                                }
                                                                $type_list[$key]['content'][$re_k]['img'] = $re_v['img'] ? $re_v['img'] . '?imageMogr2/thumbnail/224x150!' : '';
                                                            }
                                                        }
                                                    }
                                                }else{
                                                    $type_list[$key]['content'] = array();
                                                }
                                            }else {
                                                $type_list[$key]['content'] = json_decode($type_list[$key]['content']);
                                                if (!empty($type_list[$key]['content'])) {
                                                    foreach ($type_list[$key]['content'] as $k => $v) {
                                                        if ($k < 3) {
                                                            if (is_object($type_list[$key]['content'][$k])) {
                                                                $str_con = substr($type_list[$key]['content'][$k]->img, -2);
                                                                if ($str_con == '/s') {
                                                                    $type_list[$key]['content'][$k]->img = substr($type_list[$key]['content'][$k]->img, 0, -2);
                                                                }
                                                                $type_list[$key]['content'][$k]->img = $type_list[$key]['content'][$k]->img ? $type_list[$key]['content'][$k]->img . '?imageMogr2/thumbnail/224x150!' : '';
                                                            } else {
                                                                $str_con = substr($type_list[$key]['content'][$k]['img'], -2);
                                                                if ($str_con == '/s') {
                                                                    $type_list[$key]['content'][$k]['img'] = substr($type_list[$key]['content'][$k]['img'], 0, -2);
                                                                }
                                                                $type_list[$key]['content'][$k]['img'] = $type_list[$key]['content'][$k]['img'] ? $type_list[$key]['content'][$k]['img'] . '?imageMogr2/thumbnail/224x150!' : '';

                                                            }

                                                        }
                                                    }
                                                }else{
                                                    $type_list[$key]['content'] = array();
                                                }
                                            }
                                            $type_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                                        } else if ($value['type'] == 7) { //图文
                                            $type_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/206x142!' : '';
                                            $type_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                                            $type_list[$key]['content'] = array();
                                        } else if (in_array($value['type'], $live_types)) { //直播类型新闻
                                            $type_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/710x340!' : '';
                                            $type_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
                                            $type_list[$key]['content'] = array();
                                        }
                                    }else{
                                        if ($value['type'] == 3) { //专题
                                            $type_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                                            $type_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                                        } else if ($value['type'] == 4) {
                                            $type_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/145x100!' : '';
                                            $type_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
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
                                                                    $type_list[$key]['content'][$re_k]->img = substr($ref_news['content'][$re_k]->img,0,-2);
                                                                }
                                                                if($type_list[$key]['content']=='""'){
                                                                    $tmp = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/150x100!' : '';
                                                                    $type_list[$key]['content'] = array($re_k=>array('img'=>$tmp));
                                                                }else{
                                                                    $type_list[$key]['content'][$re_k]['img'] = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/150x100!' : '';
                                                                }
                                                            }else {
                                                                $str_con = substr($ref_news['content'][$re_k]['img'],-2);
                                                                if($str_con == '/s'){
                                                                    $type_list[$key]['content'][$re_k]['img'] = substr($ref_news['content'][$re_k]['img'],0,-2);
                                                                }
                                                                $type_list[$key]['content'][$re_k]['img'] = $re_v['img'] ? $re_v['img'] . '?imageMogr2/thumbnail/150x100!' : '';
                                                            }
                                                        }
                                                    }
                                                }else{
                                                    $type_list[$key]['content'] = array();
                                                }
                                            }else {
                                                $type_list[$key]['content'] = json_decode($type_list[$key]['content']);
                                                foreach ($type_list[$key]['content'] as $k => $v) {
                                                    if ($k < 4) {
                                                        if (is_object($type_list[$key]['content'][$k])) {
                                                            $str_con = substr($type_list[$key]['content'][$k]->img, -2);
                                                            if ($str_con == '/s') {
                                                                $type_list[$key]['content'][$k]->img = substr($type_list[$key]['content'][$k]->img, 0, -2);
                                                            }
                                                            $type_list[$key]['content'][$k]->img = $type_list[$key]['content'][$k]->img ? $type_list[$key]['content'][$k]->img . '?imageMogr2/thumbnail/150x100!' : '';
                                                        } else {
                                                            $str_con = substr($type_list[$key]['content'][$k]['img'], -2);
                                                            if ($str_con == '/s') {
                                                                $type_list[$key]['content'][$k]['img'] = substr($type_list[$key]['content'][$k]['img'], 0, -2);
                                                            }
                                                            $type_list[$key]['content'][$k]['img'] = $type_list[$key]['content'][$k]['img'] ? $type_list[$key]['content'][$k]['img'] . '?imageMogr2/thumbnail/150x100!' : '';
                                                        }

                                                    }
                                                }
                                            }
                                            $type_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                                        } else if ($value['type'] == 7) {
                                            $type_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/145x100!' : '';
                                            $type_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                                        } else if (in_array($value['type'], $live_types)) { //直播类型新闻
                                            $type_list[$key]['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/208x100!' : '';
                                            $type_list[$key]['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/640x213!' : '';
                                        }
                                    }

                                }

                                $type_news = News::find()->leftJoin('news_column_type t', 't.type_id = news.type_id')
                                    ->where(['special_news_id' => $news_id])->select("news.*,year(news.create_time) as year,month(news.create_time) as month,day(news.create_time) as day")
                                    ->orderBy("year desc,month desc,day desc,news.weight desc,news.refresh_time desc")
                                    ->asArray()->all();
                                if ($type_news) {
                                    $result = array();
                                    foreach ($type_news as $key => $val) {
                                        $result[$val['type']] = $val;
                                    }
                                }
                                $news_info['type_list'] = $type_list;
                                $redis->set($name, $news_info, 86400);
                            }
                        }
                    }
                }elseif($news_info['type'] == 5){ //图集
                    if($news_info['reference_id']){
                        $ret = $this->_getContentById($news_info['reference_id']);

                        $ret['news_id']       	= $news_info['reference_id'] ? $news_info['reference_id'] :$news_info['news_id'];
                        $ret['new_title']    	= $news_info['title'];
                        $ret['new_subtitle'] 	= $news_info['subtitle'];
                        $ret['new_abstract']    = $news_info['abstract'];
                        $ret['new_cover_image'] = $news_info['cover_image'];
                        $news_info = $ret;
                    }
                    $news_info['img'] = json_decode($news_info['content']);
                    $news_info['count'] = json_decode($news_info['content']);
                }elseif($news_info['type'] == 6){ //文本
                    if($news_info['reference_id']){
                        $ret = $this->_getContentById($news_info['reference_id']);
                        $ret['news_id']       	= $news_info['news_id'];
                        $ret['new_title']    	= $news_info['title'];
                        $ret['create_time'] = $news_info['create_time'];
                        $ret['new_subtitle'] 	= $news_info['subtitle'];
                        $ret['new_abstract']    = $news_info['abstract'];
                        $ret['new_cover_image'] = $news_info['cover_image'];
                        $news_info = $ret;
                    }
                }elseif($news_info['type'] == 7 || $news_info['type'] == 2 || $news_info['type'] == 1){ //图文
                    if($news_info['reference_id']){
                        $ret = $this->_getContentById($news_info['reference_id']);
                        //引用  取新ID
                        $ret['news_id']       	= $news_info['news_id'];

                        //二次分享参数
                        $ret['new_title']    	= $news_info['title'];
                        $ret['create_time']     = $news_info['create_time'];
                        $ret['new_subtitle'] 	= $news_info['subtitle'];
                        $ret['new_abstract']    = $news_info['abstract'];
                        $ret['new_cover_image'] = $news_info['cover_image'];
                        $news_info = $ret;
                    }
                    //投票
                    if($news_info['vote_id']){
                        $news_info['vote_url'] = yii::$app->params['vote_url'].'?vote_id='.$news_info['vote_id'];
                    }
                }
            }

//             if(!empty($user_id)){
                //点赞数和当前用户是否可点赞
                $praise_count = NewsPraise::find()->where(['news_id'=>$news_id,'status'=>'1', 'news_type'=>1])->count();
                $user_praise_count = NewsPraise::find()->where(['news_id'=>$news_id,'status'=>'1','user_id'=>$user_id])->count();
                $news_info['praise_count'] =  $praise_count > 0 ? $praise_count : '0';
                $news_info['user_is_praise'] =  $user_praise_count > 0 ? '1' : '0';

                //打赏总数和打赏人员列表
                $reward_count = NewsReward::find()->select(['news_reward.id'])->innerJoin('news','news_reward.news_id = news.news_id')->where(['news_reward.news_id'=>$news_id])->count();
                $news_info['reward_count'] =  $reward_count > 0 ? $reward_count : '0';
                $reward_users = NewsReward::find()
                    ->select(['vruser1.user.user_id','vruser1.user.nickname as nick_name','vruser1.user.avatar'])
                    ->innerJoin('vrnews1.news','vrnews1.news_reward.news_id = vrnews1.news.news_id')
                    ->innerJoin('vruser1.user','vrnews1.news_reward.user_id = vruser1.user.user_id')
                    ->where(['vrnews1.news_reward.news_id'=>$news_id,'vruser1.user.status'=>'1'])
                    ->groupBy('vrnews1.news_reward.user_id')
                    ->asArray()
                    ->all();
                $news_info['reward_users'] = !empty($reward_users) ? $reward_users : array();

                //用户收藏状态
                $user_collect_count = NewsUserCollect::find()->where(['news_id'=>$news_id,'status'=>'1','user_id'=>$user_id])->select("count(*) as num, collect_id")->asArray()->one();
                $news_info['user_is_collect'] =  $user_collect_count['num'] > 0 ? '1' : '0';
                $news_info['collect_id']      =  $user_collect_count['collect_id'];
            }
//         }
        $this->_successData($news_info);
    }

    /* 获取引用的内容 content */
    private function _getContentById($id){
        $content = News::find()->leftJoin('news_video nv','nv.news_id = news.news_id')->where(['news.news_id'=>$id])->select('news.*')->asArray()->one();
        return $content;
    }
}