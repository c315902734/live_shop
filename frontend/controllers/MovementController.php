<?php
namespace frontend\controllers;

use common\models\ApiResponse;
use OAuth2\Request;
use Yii;
use yii\db\Query;

/**
 * 用户消息相关接口
 */
class MovementController extends PublicBaseController
{
    //用户推送消息 展示
    //by zhaobo
    function actionMessageAll(){
        $page      = !empty($this->params['page']) ? $this->params['page'] : 1;
        $count     = !empty($this->params['pageSize']) ? $this->params['pageSize'] : 20;
        $is_pc     = !empty($this->params['is_pc']) ? $this->params['is_pc'] : '';
        $user_id   = !empty($this->params['user_id']) ? $this->params['user_id'] : '201715078620205404';
        $offset   = ($page-1)*$count;
        $data     = array();
        $query    = new Query();
        $query->select("*");
        $query->from("vruser1.user_merssage");
        $query->where("(status=2 and user_id={$user_id} and c_type=1) or (status=2  and c_type=0) ");
        $totalCount = $query->count();
        $query->orderBy(['send_date' => SORT_DESC]);
        $query->offset($offset);
        $query->limit($count);
        $command = $query->createCommand();
        $data_sel = $command->queryAll();
        if($data_sel){
            foreach($data_sel as $key=>$val){
                $data[$key]['messige_id']  = $val['id'];
                $data[$key]['title']       = $val['title'];
                $data[$key]['content']     = $val['content'];
                $data[$key]['news_type']   = $val['news_type'];//新闻类型
                $data[$key]['url']         = $val['url'];//新闻URL
                $data[$key]['send_date']   = $val['send_date'];
                $news_out_pos = '';
                $news_id_str  = '';

                if($val['news_type'] == 8){
                    //直播类型 获取URL中的id 值 放入news_id 内
                    $news_id_pos = strrpos($val['url'],'id=') + 3;
                    $news_id_str = substr($val['url'],$news_id_pos);
                    $news_out_pos = strpos($news_id_str, '&');
                }else {
                    if (!strrpos($val['url'], 'news_id=')) {
                        //URL 内没有 news_id
                        $data[$key]['news_id'] = '';
                    } else {
                        //找到 news_id= 位置
                        $news_id_pos = strrpos($val['url'], 'news_id=') + 8;
                        //截取 news_id  后内容
                        $news_id_str = substr($val['url'], $news_id_pos);
                        //截取到 & 首次出现位置  若没有 截取后面的全部
                        $news_out_pos = strpos($news_id_str, '&');
                    }
                }
                if ($news_out_pos) {
                    $data[$key]['news_id'] = substr($news_id_str, 0, $news_out_pos);
                } else {
                    $data[$key]['news_id'] = $news_id_str;
                }

            }
        }
        if($is_pc){
            $return['totalCount'] = $totalCount;
            $return['list'] = $data;
        }else{
            $return = $data;
        }
        // return print_r($return);
        $this->_successData($return);
    }
}