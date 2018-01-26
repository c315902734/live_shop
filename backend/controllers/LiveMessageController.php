<?php

namespace backend\controllers;

use common\models\AdminRole;
use common\models\AdminUser;
use common\models\LivePanelManage;
use common\models\LiveSection;
use common\models\LiveTagsRelation;
use common\models\ResourceLibrary;
use Yii;

/**
 * LiveMessage controller
 * 新直播 聊天消息相关
 */
class LiveMessageController extends PublicBaseController
{
    /**
     * 实时获取聊天室最新信息
     */
    public function actionLiveMessages()
    {
        $pageSize = isset($_REQUEST['pageSize']) ? $_REQUEST['pageSize'] : '';
        $pageSize = (!empty($pageSize) && $pageSize > 0) ? $pageSize : 10;
        $live_id = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '0';
        $last_id = isset($_REQUEST['last_id']) ? $_REQUEST['last_id'] : '0';
        if (!$live_id) {
            $this->_errorData('0001', '参数错误');
        }
        $returnData = LivePanelManage::UserGetNewMessage($live_id, $last_id, 0, $pageSize,1);
        $this->_successData($returnData, "查选成功");
    }

    /*
    * 创建图文直播消息
    *
    * */
    public function actionCreateMessage()
    {
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : 0; //直播ID
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : 0; //当前管理员ID
        $content = isset($this->params['content']) ? $this->params['content'] : ''; //直播内容
        $img_urls = isset($this->params['img_urls']) ? $this->params['img_urls'] : ''; //直播图片
        $video_ids = isset($this->params['video_ids']) ? $this->params['video_ids'] : ''; //直播资源池视频id
        if ($admin_id != 1) {
            //查看 缓存数据
            $redis = Yii::$app->cache;
            $red_admin = Yii::$app->params['environment'] . '_admin_role_' . $admin_id;
            $redis_info = $redis->get($red_admin);
            if (!$redis_info) {
                //如 无缓存 查看数据
                $red_list = AdminRole::getAdminRole($admin_id);
            } else {
                $red_list = $redis_info;
            }
            $role_arr = array_column($red_list, "action_name");
            if (!in_array("快直播-高级管理-添加图文消息", $role_arr) && !in_array("快直播-图文直播-添加图文消息", $role_arr)) {
                $this->_errorData('0339', "暂无此权限");
            }
        }
        $compere = LiveSection::getCompere($live_id); //获取直播员信息
        if (!$compere) {
            $this->_errorData('0134', "直播不存在");
        }
        $live_tag = LiveTagsRelation::getSection_tags($live_id,7,1);
        if (!$live_tag) {
            $this->_errorData('0137', "直播类型有误");
        }
        $admin_info = AdminUser::find()->where('admin_id = ' . $admin_id)->asArray()->one();
        if ($admin_info) {
            $creator_name = $admin_info['real_name'];
            $creator_nickname = $admin_info['username'];
            $creator_avatar = 'http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png';
        } else {
            $creator_name = '';
            $creator_nickname = '';
            $creator_avatar = 'http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png';
        }
        $images = $img_urls ? explode(',', $img_urls) : array();
        //获得上榜用户信息
        $user_info = array(
            "user_username" => "",
            "user_id"       => "",
            "user_content"  => "",
            "msg_time"      => "08:00:00",
            "msg_date"      => "1970-01-01"
        );
        $date_time = date('Y-m-d H:i:s', time());
        $video_data = array();
        $video_url_arr = array();
        $video_thumbnails = array();
        //处理 视频信息
        if ($video_ids) {
            $video_id_arr = $video_ids ? explode(',', $video_ids) : array();
            if ($video_id_arr) {
                foreach ($video_id_arr as $key => $value) {
                    //查看资源池 中 视频信息
                    $video_info = ResourceLibrary::find()->where('resource_id = ' . $value)->asArray()->one();
                    if ($video_info) {
                        if ($video_info['url']) {
                            $video_url_res = $video_info['url'];
                        } else if ($video_info['url1']) {
                            $video_url_res = $video_info['url1'];
                        } else {
                            $video_url_res = $video_info['url2'];
                        }
                        if ($video_info['height']) {
                            $video_height_res = $video_info['height'];
                        } else if ($video_info['height1']) {
                            $video_height_res = $video_info['height1'];
                        } else {
                            $video_height_res = $video_info['height2'];
                        }
                        if ($video_info['width']) {
                            $video_width_res = $video_info['width'];
                        } else if ($video_info['width1']) {
                            $video_width_res = $video_info['width1'];
                        } else {
                            $video_width_res = $video_info['width2'];
                        }
                        if ($video_info['size']) {
                            $video_size_res = $video_info['size'];
                        } else if ($video_info['size1']) {
                            $video_size_res = $video_info['size1'];
                        } else {
                            $video_size_res = $video_info['size2'];
                        }
                        $video_data[$key]['video_url'] = $video_url_res;
                        $video_data[$key]['video_thumbnail'] = $video_info['thumbnail_url'];
                        $video_data[$key]['video_file_id'] = $video_info['file_id'];
                        $video_data[$key]['video_duration'] = $video_info['duration'];
                        $video_data[$key]['video_height'] = $video_height_res;
                        $video_data[$key]['video_width'] = $video_width_res;
                        $video_data[$key]['video_size'] = $video_size_res;
                        $video_thumbnails[] = $video_info['thumbnail_url'];
                        $video_url_arr[] = $video_url_res;
                    }
                }
            }
        }
        $content = '<p>' . str_replace("\t", "<p><br></p>", $content) . '</p>';

        $msg_content = array(
            "msg_id"            => 0,
            "content"           => $this->userTextEncode($content),
            "compere_id"        => $compere['creator_id'],//管理员编号
            "compere_cate"      => $compere['live_man_cate'], //直播员类别
            "compere_name"      => $compere['live_man_alias'],//"直播员别名",
            "compere_avatar"    => $compere['live_man_avatar_url'],//"直播员头像"
            "images_url"        => $images,
            "videos_url"        => $video_url_arr,
            "video_thumbnails"  => $video_thumbnails,
            "images_data"       => $images,
            "videos_data"       => $video_data,
            "on_list_user_info" => $user_info, //上榜用户信息
            "date_time"         => $date_time
        );
        $json_msg_content = json_encode($msg_content);
        $sort_number = intval(LivePanelManage::getNextSortNumber());
        $live_panel_manage = new LivePanelManage();
        $live_panel_manage['live_id'] = $live_id;
        $live_panel_manage['json_data'] = $json_msg_content;
        $live_panel_manage['creator_id'] = $admin_id;
        $live_panel_manage['creator_name'] = $creator_name;
        $live_panel_manage['creator_nickname'] = $creator_nickname;
        $live_panel_manage['creator_avatar'] = $creator_avatar;
        $live_panel_manage['create_time'] = $date_time;
        $live_panel_manage['update_time'] = $date_time;
        $live_panel_manage['sort_number'] = $sort_number;
        $live_panel_manage['content_type'] = 1; //1：原始图文消息内容；2：上榜数据内容
        $live_panel_manage['pic_txt_content'] = $this->userTextEncode($content);
        $live_panel_manage->save();
        $this->_successData("创建成功");
    }

    /*
    * 直播图文消息 置顶和上榜列表
    *
    * */
    public function actionTopAndOnList()
    {
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : 0; //直播ID
        if (!$live_id) {
            $this->_errorData('0133', "参数错误");
        }
        //查看直播是否存在
        $live_info = LiveSection::find()->where('section_id = ' . $live_id . ' and status != 0')->asArray()->one();
        if (!$live_info) {
            $this->_errorData('0134', "直播不存在");
        }
        $live_tag = LiveTagsRelation::getSection_tags($live_id,7,1);
        if (!$live_tag) {
            $this->_errorData('0137', "直播类型有误");
        }
        $returnData = LivePanelManage::Get_TopAndOnList($live_id);
        $this->_successData($returnData, "查选成功");
        die;

    }

    /*
     * 图文直播消息列表
     *
     * */
    public function actionMessageList()
    {
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : 0; //直播ID
        $pageSize = isset($_REQUEST['pageSize']) ? $_REQUEST['pageSize'] : 10;
        $last_id = isset($_REQUEST['last_id']) ? $_REQUEST['last_id'] : '0';
        if (!$live_id) {
            $this->_errorData('0133', "参数错误");
        }
        //查看直播是否存在
        $live_info = LiveSection::find()->where('section_id = ' . $live_id . ' and status != 0')->asArray()->one();
        if (!$live_info) {
            $this->_errorData('0134', "直播不存在");
        }
        $live_tag = LiveTagsRelation::getSection_tags($live_id,7,1);
        if (!$live_tag) {
            $this->_errorData('0137', "直播类型有误");
        }
        //查看 图文消息列表
        $res_list = array();
        $res_list = LivePanelManage::get_MessageList($live_id, $last_id, $pageSize);
        if (!$res_list) {
            $this->_successData($res_list);
        }
        foreach ($res_list as $key => $val) {
            $json_data = json_decode($val['json_data'], TRUE);
            if (isset($json_data['images_url'])) {
                if ($json_data['images_url']) {
                    $new_image_data = array();
                    foreach ($json_data['images_url'] as $key1 => $value1) {
                        $new_image_data[$key1]['thumbnail_image_url'] = $value1 . '/e';
                        $new_image_data[$key1]['original_image_url'] = $value1;
                    }
                    $json_data['images_url'] = $new_image_data;
                    $json_data['images_data'] = $new_image_data;
                }
            }
            $returnData[$key]['msg_id'] = $val['id'];
            $returnData[$key]['content'] = self::userTextDecode($json_data['content']);
            $returnData[$key]['compere_id'] = $json_data['compere_id'];
            $returnData[$key]['compere_cate'] = isset($json_data['compere_cate']) ? $json_data['compere_cate'] : "主持人"; //直播员类别
            $returnData[$key]['compere_name'] = $json_data['compere_name'];
            $returnData[$key]['compere_avatar'] = $json_data['compere_avatar'] ? $json_data['compere_avatar'] : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
            $returnData[$key]['images_data'] = isset($json_data['images_data']) ? $json_data['images_data'] : array();
            $returnData[$key]['videos_data'] = isset($json_data['videos_data']) ? $json_data['videos_data'] : array();
            $returnData[$key]['images_url'] = isset($json_data['images_url']) ? $json_data['images_url'] : array();
            $returnData[$key]['videos_url'] = isset($json_data['videos_url']) ? $json_data['videos_url'] : array();
            $returnData[$key]['video_thumbnails'] = isset($json_data['video_thumbnails']) ? $json_data['video_thumbnails'] : array();
            $returnData[$key]['on_list_user_info'] = $json_data['on_list_user_info'] ? $json_data['on_list_user_info'] : array();
            $returnData[$key]['date_time'] = $json_data['date_time'];

        }
        $this->_successData($returnData);
    }

    /*
     * 获取图文直播新消息
     *
     * */
    public function actionNewMessage()
    {
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : 0; //直播ID
        $pageSize = isset($_REQUEST['pageSize']) ? $_REQUEST['pageSize'] : 10;
        $first_id = isset($_REQUEST['first_id']) ? $_REQUEST['first_id'] : '0';
        if (!$live_id) {
            $this->_errorData('0133', "参数错误");
        }
        //查看直播是否存在
        $live_info = LiveSection::find()->where('section_id = ' . $live_id . ' and status != 0')->asArray()->one();
        if (!$live_info) {
            $this->_errorData('0134', "直播不存在");
        }
        $live_tag = LiveTagsRelation::getSection_tags($live_id,7,1);
        if (!$live_tag) {
            $this->_errorData('0137', "直播类型有误");
        }
        //查看 图文消息列表
        $res_list = array();
        $res_list = LivePanelManage::get_NewMessage($live_id, $first_id, $pageSize);
        if (!$res_list) {
            $this->_successData($res_list);
        }
        foreach ($res_list as $key => $val) {
            $json_data = json_decode($val['json_data'], TRUE);
            if (isset($json_data['images_url'])) {
                if ($json_data['images_url']) {
                    $new_image_data = array();
                    foreach ($json_data['images_url'] as $key1 => $value1) {
                        $new_image_data[$key1]['thumbnail_image_url'] = $value1 . '/e';
                        $new_image_data[$key1]['original_image_url'] = $value1;
                    }
                    $json_data['images_url'] = $new_image_data;
                    $json_data['images_data'] = $new_image_data;
                }
            }
            $returnData[$key]['msg_id'] = $val['id'];
            $returnData[$key]['content'] = self::userTextDecode($json_data['content']);
            $returnData[$key]['compere_id'] = $json_data['compere_id'];
            $returnData[$key]['compere_cate'] = isset($json_data['compere_cate']) ? $json_data['compere_cate'] : "主持人"; //直播员类别
            $returnData[$key]['compere_name'] = $json_data['compere_name'];
            $returnData[$key]['compere_avatar'] = $json_data['compere_avatar'] ? $json_data['compere_avatar'] : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
            $returnData[$key]['images_data'] = isset($json_data['images_data']) ? $json_data['images_data'] : array();
            $returnData[$key]['videos_data'] = isset($json_data['videos_data']) ? $json_data['videos_data'] : array();
            $returnData[$key]['images_url'] = isset($json_data['images_url']) ? $json_data['images_url'] : array();
            $returnData[$key]['videos_url'] = isset($json_data['videos_url']) ? $json_data['videos_url'] : array();
            $returnData[$key]['video_thumbnails'] = isset($json_data['video_thumbnails']) ? $json_data['video_thumbnails'] : array();
            $returnData[$key]['on_list_user_info'] = $json_data['on_list_user_info'] ? $json_data['on_list_user_info'] : array();
            $returnData[$key]['date_time'] = $json_data['date_time'];

        }
        $this->_successData($returnData);
    }

    /*
  * 直播 图文消息 置顶
  *
  * */
    public function actionMessageTop()
    {
        $message_id = isset($this->params['message_id']) ? $this->params['message_id'] : 0; //消息ID
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : 0; //当前管理员ID
        if (!$message_id || !$admin_id) {
            $this->_errorData('0133', "参数错误");
        }
        if ($admin_id != 1) {
            //查看 缓存数据
            $redis = Yii::$app->cache;
            $red_admin = Yii::$app->params['environment'] . '_admin_role_' . $admin_id;
            $redis_info = $redis->get($red_admin);
            if (!$redis_info) {
                //如 无缓存 查看数据
                $red_list = AdminRole::getAdminRole($admin_id);
            } else {
                $red_list = $redis_info;
            }
            $role_arr = array_column($red_list, "action_name");
            if (!in_array("快直播-高级管理-置顶图文消息", $role_arr) && !in_array("快直播-图文直播-置顶图文消息", $role_arr)) {
                $this->_errorData('0339', "暂无此权限");
            }
        }
        //查看直播是否存在
        $live_info = LivePanelManage::find()->where('id = ' . $message_id)->asArray()->one();
        if (!$live_info) {
            $this->_errorData('0134', "直播图文消息不存在");
        }
        //查看当前置顶直播
        $data['is_top'] = 0;
        $live_top = LivePanelManage::find()->where("live_id = " . $live_info['live_id'] . " and is_top = 1")->asArray()->one();
        if ($live_top) {
            LivePanelManage::updateAll($data, ['id' => $live_top['id']]);
        }
        $param['is_top'] = 1;
        $result = LivePanelManage::updateAll($param, ['id' => $message_id]);
        if (!$result) {
            $this->_errorData('0138', "置顶失败");
        }
        $this->_successData("置顶成功");
    }

    /*
     * 直播 图文消息 删除
     *
     * */
    public function actionMessageDel()
    {
        $message_id = isset($this->params['message_id']) ? $this->params['message_id'] : 0; //消息ID
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : 0; //当前管理员ID
        if (!$message_id || !$admin_id) {
            $this->_errorData('0133', "参数错误");
        }
        if ($admin_id != 1) {
            //查看 缓存数据
            $redis = Yii::$app->cache;
            $red_admin = Yii::$app->params['environment'] . '_admin_role_' . $admin_id;
            $redis_info = $redis->get($red_admin);
            if (!$redis_info) {
                //如 无缓存 查看数据
                $red_list = AdminRole::getAdminRole($admin_id);
            } else {
                $red_list = $redis_info;
            }
            $role_arr = array_column($red_list, "action_name");
            if (!in_array("快直播-高级管理-删除图文消息", $role_arr) && !in_array("快直播-图文直播-删除图文消息", $role_arr)) {
                $this->_errorData('0339', "暂无此权限");
            }
        }
        //查看直播是否存在
        $live_info = LivePanelManage::find()->where('id = ' . $message_id)->asArray()->one();
        if (!$live_info) {
            $this->_errorData('0134', "直播图文消息不存在");
        }
        $result = LivePanelManage::deleteAll(['id' => $message_id]);
        if (!$result) {
            $this->_errorData('0138', "删除失败");
        }
        $this->_successData("删除成功");
    }
    
}
