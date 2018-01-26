<?php
namespace frontend\controllers;

use common\models\ApiResponse;
use common\models\News;
use common\models\NewsColumn;
use common\models\NewsColumnType;
use common\models\SpecialColumnType;
use OAuth2\Request;
use Yii;
use yii\db\Query;
use yii\rest\Controller;


/**
 * 新闻相关接口
 */
class InformationController extends PublicBaseController
{
    /*
   * 新闻列表
   * id  栏目ID
   * is_area 是否本地 0 正常栏目,1本地栏目
   * pub_type  发布类型，0 app发布，1门户网站 发布
   * keyword 关键字搜索 只针对 标题
   * page 当前页
   * count 最多显示数量
   * */
    function actionInfoList(){
        $column_id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 1;
        $is_area   = !empty($_REQUEST['is_area']) ? $_REQUEST['is_area'] : 0;
        $pub_type  = !empty($_REQUEST['pub_type']) ? $_REQUEST['pub_type'] : 0;
        $key_word  = !empty($_REQUEST['key_word']) ? $_REQUEST['key_word'] : '';
        $page      = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $count     = !empty($_REQUEST['count']) ? $_REQUEST['count'] : 20;
        $is_pc     = !empty($_REQUEST['is_pc']) ? $_REQUEST['is_pc'] : '';
        $type_id   = !empty($_REQUEST['type_id']) ? $_REQUEST['type_id'] : '';
        $get_vote   = !empty($_REQUEST['get_vote']) ? $_REQUEST['get_vote'] : '';
        
        $info_list = News::GetList($column_id,$is_area,$pub_type,$key_word,$page,$count,$is_pc,$type_id, $get_vote);
        if($is_pc){
            foreach($info_list['list'] as $key=>$val){
                $info_list['list'][$key]['title'] = htmlspecialchars_decode($info_list['list'][$key]['title']);
                if($val['type'] == 5){ //图集 对内容进行处理
                    $content = $val['content'];
                    if($val['reference_id']){
                        $content_re = News::findOne($val['reference_id']);
                        $content = $content_re['content'];
                        $news_content = json_decode($content);
                        if(!empty($news_content)){
                            foreach ($news_content as $k=>$v) {
                                if ($k < 4) {
                                    if (is_object($news_content[$k])) {
                                        $str_con = substr($news_content[$k]->img,-2);
                                        if($str_con == '/s'){
                                            $news_content[$k]->img = substr($news_content[$k]->img,0,-2);
                                        }
                                        $news_content[$k]->img = $news_content[$k]->img . '?imageMogr2/thumbnail/150x100!';
                                    }else{
                                        $str_con = substr($news_content[$k]['img'],-2);
                                        if($str_con == '/s'){
                                            $news_content[$k]['img'] = substr($news_content[$k]['img'],0,-2);
                                        }
                                        $news_content[$k]['img'] = $news_content[$k]['img'] . '?imageMogr2/thumbnail/150x100!';
                                    }
                                    
                                }
                            }
                        }
                    }else{
                        $news_content = $content;
                    }
                    $info_list['list'][$key]['content'] = $news_content;
                }else{
                    $info_list['list'][$key]['content'] = array();
                }
                if($val['special_type'] == 5){
                    $info_list['list'][$key]['special_image'] = json_decode($val['special_image']);
                }else{
                    $info_list['list'][$key]['special_image'] = array();
                }
            }
        }else{
            foreach($info_list as $key=>$val){
//                if(!$is_pc){
//                    $info_list[$key]['cover_image'] = $val['cover_image'] ? $val['cover_image'].'/s' : '';
//                    $info_list[$key]['full_cover_image'] = $val['full_cover_image'] ? $val['full_cover_image'].'/y' : '';
//                    if($val['type'] == 3){
//                        $info_list[$key]['cover_image'] = $val['cover_image'] ? $val['cover_image'].'/y' : '';
//                    }
//                }
                $info_list[$key]['title'] = htmlspecialchars_decode($info_list[$key]['title']);
                if($val['type'] == 5){ //图集 对内容进行处理
                    $content = $val['content'];
                    if($val['reference_id']){
                        $content_re = News::findOne($val['reference_id']);
                        $content = $content_re['content'];
                        $news_content = json_decode($content);
                        if(!empty($news_content)){
                            foreach ($news_content as $k=>$v){
                                if($k < 3){
                                    if (is_object($news_content[$k])) {
                                        $str_con = substr($news_content[$k]->img,-2);
                                        if($str_con == '/s'){
                                            $news_content[$k]->img = substr($news_content[$k]->img,0,-2);
                                        }

                                        $news_content[$k]->img = $v->img. '?imageMogr2/thumbnail/224x150!';
                                    }else{
                                        $str_con = substr($news_content[$k]['img'],-2);
                                        if($str_con == '/s'){
                                            $news_content[$k]['img'] = substr($news_content[$k]['img'],0,-2);
                                        }
                                        $news_content[$k]['img'] = $v['img']. '?imageMogr2/thumbnail/224x150!';
                                    }
                                    
                                }
                            }
                        }
                    }else{
                        $news_content = $content;
                    }

                    $info_list[$key]['content'] = $news_content;
                }else{
                    $info_list[$key]['content'] = array();
                }
                if($val['special_type'] == 5){
                    $info_list[$key]['special_image'] = json_decode($val['special_image']);
                }else{
                    $info_list[$key]['special_image'] = array();
                }
            }
        }
        $this->_successData($info_list);
    }

    /*
   * 新闻列表--新版 含 栏目直播新闻
   * id  栏目ID
   * is_area 是否本地 0 正常栏目,1本地栏目
   * pub_type  发布类型，0 app发布，1门户网站 发布
   * keyword 关键字搜索 只针对 标题
   * page 当前页
   * count 最多显示数量
   * user_id 用户ID
   * */
    public function actionInfoListNew()
    {
        $column_id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
        $is_area   = !empty($_REQUEST['is_area']) ? $_REQUEST['is_area'] : 0;
        $pub_type  = !empty($_REQUEST['pub_type']) ? $_REQUEST['pub_type'] : 0;
        $key_word  = !empty($_REQUEST['key_word']) ? $_REQUEST['key_word'] : '';
        $page      = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $count     = !empty($_REQUEST['count']) ? $_REQUEST['count'] : 20;
        $type_id   = !empty($_REQUEST['type_id']) ? $_REQUEST['type_id'] : '';
        $source = !empty($_REQUEST['source']) ? $_REQUEST['source'] : '';
        $get_vote   = !empty($_REQUEST['get_vote']) ? $_REQUEST['get_vote'] : '';
        $alias = !empty($_REQUEST['alias']) ? $_REQUEST['alias'] : '';
        $sub_alias = !empty($_REQUEST['sub_alias']) ? $_REQUEST['sub_alias'] : '';
        $is_pc = !empty($_REQUEST['is_pc']) ? $_REQUEST['is_pc'] : 0;

        $new_info_list = News::GetListNew($column_id,$is_area,$pub_type,$key_word,$page,$count,$user_id,$type_id, $get_vote, $source, $alias, $sub_alias,$is_pc);

        /* 微信小程序审核 */
        /*if (isset($_REQUEST['wechat'])) {
            $new_info_list['mini_app'] = 1;
        }*/

        $this->_successData($new_info_list);
    }

    /*
     * 专题列表
     * special_id 专题ID 即表里的special_news_id
     * pub_type  发布类型，0 app发布，1门户网站 发布
     * */
    function actionSpecialList(){
        $special_id = $_REQUEST['special_id'];
        $pub_type   = !empty($_REQUEST['pub_type']) ? $_REQUEST['pub_type'] : 0;

        $result = array();

        //查看专题 详情
        $special_info = News::GetSinfo($special_id);
        if($pub_type == 1){
            $special_info['cover_image'] = $special_info['cover_image'] ?  $special_info['cover_image']. '?imageMogr2/thumbnail/640x213!' : '';
        }else{
            $special_info['cover_image'] = $special_info['cover_image'] ?  $special_info['cover_image']. '?imageMogr2/thumbnail/710x236!' : '';
        }


        //查看所有分栏
        $special_types = SpecialColumnType::GetType($special_id);
        
        //查看列表
        $info_list = News::GetSpecialList($special_id,$pub_type,$special_types);

        $result['special'] = $special_info;
        $result['alllist'] = $info_list;

//        print_r($result);die;

        $this->_successData($result);
    }

    /*
    * 新闻详情
    * id 新闻ID
    * */
    function actionNewInfo(){
        $id      = $_REQUEST['id'];
        $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : "";
        $use_content = !empty($_REQUEST['use_content']) ? $_REQUEST['use_content'] : 0;

        $info = News::Getinfos($id,$user_id);

        if($info == false){
            $this->_errorData(0101,"新闻不存在" );
        }

        if(!$use_content){
            if(!empty($info) && $info['type'] == 5){
                $info['content'] = json_decode($info['content']);
            }else{
                $info['content'] = array();
            }
        }

//        foreach($info as $key=>$val){
//            var_dump($val['content']);exit();
//            if($val['type'] == 5){ //图集 对内容进行处理
//                $info[$key]['content'] = json_decode($val['content']);
//            }
//        }
//        print_r($info);die;
        $this->_successData($info);

    }

    //视频 点击 次数加一
    public function actionClickVideoNum(){
        $news_id   = $_REQUEST['news_id'];
        if(!$news_id){
            $this->_errorData('0001',"参数错误");
        }
        $click_num = News::ClickVideoNum($news_id);
        if($click_num){
            $this->_successData($click_num);
        }else{
            $this->_errorData('0001',$click_num);
        }
    }
    
    /**
     * 新闻收藏状态
     */
    function actionUserNewsCollectStatus(){
    	$news_id = isset($this->params['news_id'])?$this->params['news_id']:'';
    	$user_id = isset($this->params['user_id'])?$this->params['user_id']:'';
    	$status = News::getUserNewsCollectStatus($news_id,$user_id);
    	$this->_successData($status);
    }

    /*
     * 点击新闻  点击数加1
     * */
    function actionClickNewNum(){
        $news_id   = $this->params['news_id'];
        if(!$news_id){
            $this->_errorData('0001',"参数错误");
        }
        $click_num = News::ClickNewNum($news_id);
        if(!empty($click_num)){
            $this->_successData($click_num);
        }else{
            $this->_errorData('0001',$click_num);
        }
    }

    /**
     * 获取热门图集新闻
     */
    public function actionGetHotNews(){
        $list = News::hotNews();
        $this->_successData($list);
    }
    
    /**
     * 获取保定热门图集新闻
     */
    public function actionGetHotNewsByColumnId(){
    	$list = News::getHotNewsByColumnId(3);
    	$this->_successData($list);
    }
    
    /**
     * 获取热门图集新闻  跤坛 标准
     */
    public function actionGetHotTuji(){
    	$list = News::hotTuji();
    	$this->_successData($list);
    }
    
    /**
     * 获取24小时热闻
     */
    public function actionHotImagesText(){
        $list = News::hotImageText();
        $this->_successData($list);
    }

    /**
     * 精彩视频新闻
     */
    public function actionWonderfulVideo(){
        $video_list = News::wonderfulVideo();
        $this->_successData($video_list);
    }
    
    /**
     * 标准6个视频
     */
    public function actionBiaoZhunVideo(){
    	$video_list = News::BiaoZhunVideo();
    	$this->_successData($video_list);
    }
    
    /**
     * jiao_tan_wonderful-video
     * param id 地区ID 或者  栏目ID
     * return   地区或栏目下的竞猜视频
     */
    public function actionGetWonderfulVideoById(){
    	$id = yii::$app->request->post('id', 0);
    	$type = yii::$app->request->post('type', 0); // 0栏目  1地区
    	$video_list = News::columnWonderfulVideo($id, $type);
    	$this->_successData($video_list);
    }
}