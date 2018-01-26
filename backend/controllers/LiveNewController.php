<?php

namespace backend\controllers;

use common\models\AdminRole;
use common\models\AdminUser;
use common\models\LiveNew;
use common\models\LiveSection;
use common\models\LiveTagsRelation;
use common\models\News;
use common\models\ZLiveCameraAngle;
use common\models\ZLiveChannel;
use common\models\ZLiveManager;
use Yii;

/**
 * LiveNew controller
 * 新直播
 */
class LiveNewController extends PublicBaseController
{
    /*
     * 采集端 创建新直播
     * */
    public function actionLiveCreate()
    {
        $type    = isset($this->params['type']) ? $this->params['type'] : ''; //直播类型 1视频直播，3图文直播，4视频加图文直播
        $title   = isset($this->params['title']) ? $this->params['title'] : ''; //标题  有默认标题
        $weight  = isset($this->params['weight']) ? $this->params['weight'] : 110; //权重
        $cover_img    = isset($this->params['cover_img']) ? $this->params['cover_img'] : ''; //封面图
        $creator_id   = isset($this->params['creator_id']) ? $this->params['creator_id'] : ''; //当前管理员ID
        $operator_ids = isset($this->params['operator_ids']) ? $this->params['operator_ids'] : 0; //推流业务员ID
        $operator_id  = isset($this->params['operator_id']) ? $this->params['operator_id'] : ''; //直播推流业务员
        $start_time   = isset($this->params['start_time']) ? $this->params['start_time'] : ''; //直播开始时间
        $screen       = isset($this->params['screen']) ? $this->params['screen'] : 0; //画面方向，0横屏，1竖屏
        $admin_img    = isset($this->params['admin_img']) ? $this->params['admin_img'] : ''; //业务员头像
        $admin_cate   = isset($this->params['admin_cate']) ? $this->params['admin_cate'] : ''; //业务员类别
        $admin_alias  = isset($this->params['admin_alias']) ? $this->params['admin_alias'] : ''; //业务员别名
        $column_id    = isset($this->params['column_id']) ? $this->params['column_id'] : '';     //栏目ID
        $column_type  = isset($this->params['column_type']) ? $this->params['column_type'] : '0';    //栏目分类
        $res_live = array();
        if (!$type) {
            $this->_errorData('0121', "直播类型不能为空");
        }
        if (!$title) {
            $this->_errorData('0121', "直播标题不能为空");
        }
        if (!$cover_img) {
            $cover_img = "http://vrlive-10047449.image.myqcloud.com/lv1500016289morentu.png";
        }
        if (!$creator_id) {
            $this->_errorData('0123', "当前管理员ID 不能为空");
        }
        if ($creator_id != 1) {
            //查看 缓存数据
            $redis = Yii::$app->cache;
            $red_admin = Yii::$app->params['environment'] . '_admin_role_' . $creator_id;
            $redis_info = $redis->get($red_admin);
            if (!$redis_info) {
                //如 无缓存 查看数据
                $red_list = AdminRole::getAdminRole($creator_id);
            } else {
                $red_list = $redis_info;
            }
            $role_arr = array_column($red_list, "action_name");
            if (!in_array("快直播-直播管理-创建快直播", $role_arr) && !in_array("快直播-高级管理-创建/编辑快直播", $role_arr)) {
                $this->_errorData('0339', "暂无此权限");
            }
        }
        //查看 当前管理员 的角色 超管或高管 可以直接发布，普通人员 创建为 待审核
        $reviewed_status = 1;
        if ($creator_id == 1) {
            $reviewed_status = 0;
        } else {
            $reviewed_status = AdminRole::findRole($creator_id);
        }
        $reviewed_status = 0; //临时版本 无需审核
        if ($type != 3) {
            if (!$operator_id) {
                $this->_errorData('0125', "推流业务员不能为空");
            }
        }
        if ($start_time) {
            //判断 时间是否比 当前服务器时间晚
            $now_date = time();
            $start_str = strtotime($start_time);
            if ($start_str < $now_date) {
                $this->_errorData('0325', "开始时间异常");
            }
        }
        if ($type != 1) {
            if (!$operator_ids) {
                $this->_errorData('0125', "推流业务员不能为空");
            } else {
                $res_admin_ids = explode(',', $operator_ids);
                if (count($res_admin_ids) > 4) {
                    $this->_errorData('0131', "业务员不能多于4人");
                }
                if ($type == 4 && in_array($operator_id, $res_admin_ids)) {
                    $this->_errorData('0132', "业务员不能重复");
                }
            }
        }
        if (!preg_match('/^(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])$/', $weight)) {
            $this->_errorData('0126', "权重必须为0到255的数字！");
        }
        $section_id = $this->create_live_id();
        if (in_array($type, array(3, 4))) {
            //创建图文直播聊天室
            $code = $this->create_rcloud_chroom_pic_txt($section_id);
            if (!$code == 200) {
                $this->_errorData('0127', '创建融云图文直播聊天室失败，请联系系统管理员！');
            }
        }

        //创建系列
        $createlive = $this->createlive($column_type,$column_id,$creator_id,$title,$cover_img,'',1);
        if(!$createlive){
            $this->_errorData('1201', '创建系列失败');
        }
        $live_id = $createlive['live_id'];
        
        //创建融云聊天室
        $code = $this->create_rcloud_chroom($section_id);
        if ($code == 200) {
            $create_time = date('Y-m-d H:i:s');
            if (!$start_time || empty($start_time)) {
                $start_time = $create_time;
            }
            $param = new LiveSection();
            $param['section_id'] = $section_id;
            $param['live_id']    = $live_id;
            $param['title']      = $title;
            $param['start_time'] = $start_time;
            $param['image_url']  = $cover_img;
            $param['create_time']  = $create_time;
            $param['update_time']  = $create_time;
            $param['refresh_time'] = $create_time;
            $param['creator_id']   = $creator_id;
            $param['screen'] = $screen;
            $param['live_man_avatar_url'] = $admin_img ? $admin_img : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
            $param['live_man_cate'] = $admin_cate;
            $param['live_man_alias'] = $admin_alias;
            $param['view_ranage'] = '5-10|1-20|1-5';
            $param['reviewed_status'] = $reviewed_status;

            $result = $param->save();
            if (!$result) {
                $this->_errorData('0128', "保存直播失败");
            }
            // 快直播创建成果后，创建管理关联新闻，状态为待审核
            $news_data = new News();
            $news_data['title'] = $title;
            $news_data['cover_image'] = $cover_img ? $cover_img : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
            $news_data['app_pub'] = 0;
            $news_data['weight']  = 70;
            $news_data['type']    = 15;//新增新闻类型，17.集入口 16是集 15.普通快直播-新直播
            $news_data['create_time']  = date('Y-m-d H:i:s', time());
            $news_data['refresh_time'] = time();
            $news_data['live_id']      = $live_id;
            if($reviewed_status == 0) {
                //网管高管自动发布快直播，同时其对应的news状态为0，即已发布
                $news_data['status'] = 0;//   新闻状态，0 已发布，1草稿，2定时发布,3待审核
            }else{
                $news_data['status'] = 3;//   新闻状态，0 已发布，1草稿，2定时发布,3待审核
            }
            if (!$column_type) {
                $news_data['column_id'] = $column_id;
            } else {
                $news_data['area_id'] = $column_id;
            }
            $insert_news = $news_data->save();
            //快直播关联新闻,创建成果后更新直播关联新闻id;
            if ($insert_news) {
                $live_new['news_id'] = $news_data->news_id;
                LiveNew::updateAll($live_new, ['live_id' => $live_id]);
            }

            // 当超管/高管时,创建的快直播自带直播标签，tag_id=2时是“直播"标签
            if (!AdminRole::findRole($creator_id) || $creator_id== 1) {
                LiveTagsRelation::save_tags($section_id,2, $creator_id);
            }
            if($type != 1) {
                //添加 直播业务员
                foreach ($res_admin_ids as $key => $val) {
                    //查询管理员名称
                    $val_name = AdminUser::find()->select("real_name")->where('admin_id = ' . $val)->asArray()->one();
                    $live_manages = new ZLiveManager();
                    $live_manages['section_id'] = $section_id;
                    $live_manages['admin_id'] = $val;
                    $live_manages['admin_name'] = $val_name['real_name'];
                    $live_manages['create_time'] = date('Y-m-d H:i:s');
                    $live_manages->save();
                }
                //添加 图文标签
                LiveTagsRelation::save_tags($section_id,7,$creator_id);
            }
            if (in_array($type, array(1, 4))) {
                //创建直播码，并获取推流地址
                $channel_res = $this->getChannel($section_id,$start_time);

                //创建 直播的 直播码记录
                $live_channel = new ZLiveChannel();
                $live_channel['txy_channel_id'] = $channel_res['livecode'];
                $live_channel['channel_name'] = "直播码-" . $section_id;
                $live_channel['device_type'] = 3;
                $live_channel['manager'] = $creator_id;
                $live_channel['status'] = 1;
                $live_channel['push_url'] = $channel_res['push_url'];
                $live_channel['pull_url'] = $channel_res['pull_url'];
                $live_channel['create_time'] = date("Y-m-d H:i:s");
                $live_channel['creator_id'] = $creator_id;
                $live_channel['type'] = 1;
                $live_channel['section_id']  = $section_id;
                $live_channel->save();

                $channel_id = $live_channel->getAttributes(array(0 => 'channel_id'));
                //添加 直播的直播码形式的 推流人
                $live_camera = new ZLiveCameraAngle();
                $live_camera['section_id'] = $section_id;
                $live_camera['signal_source'] = 1;
                $live_camera['name'] = "直播码-" . $channel_id['channel_id'];
                $live_camera['source_id'] = $channel_id['channel_id'];
                $live_camera['operator_id'] = $operator_id;
                $live_camera->save();
                $res_live['push_url'] = $channel_res['push_url'];
                //添加 视频标签
                LiveTagsRelation::save_tags($section_id,5,$creator_id);
            }
            $h = intval(date("H"));
            $work_time = 1;
            if ($h >= 9 || $h <= 21) {
                $work_time = 0;
            }
            $res_live['live_id'] = $section_id;
            $res_live['work_time'] = $work_time;
            $res_live['reviewed_status'] = $reviewed_status;
            $this->_successData($res_live);
        } else {
            $this->_errorData('0129', '创建融云聊天室失败，请联系系统管理员！');
        }

    }

    /*
     * 直播详情
     *
     * */
    public function actionLiveInfo()
    {
        $section_id = isset($this->params['live_id']) ? $this->params['live_id'] : ''; //直播ID
        $admin_id  = isset($this->params['admin_id']) ? $this->params['admin_id'] : ''; //当前管理员ID
        if (!$section_id) {
            $this->_errorData('0133', "直播ID不能为空");
        }
        if (!$admin_id) {
            $this->_errorData('0123', "当前管理员ID 不能为空");
        }
        //查看直播是否存在
        $live_check = LiveSection::find()
            ->where('section_id = ' . $section_id . " and status != 0")
            ->asArray()->one();
        
        if (!$live_check) {
            $this->_errorData('0134', "直播不存在");
        }

        //查看直播详情
        $live_info = LiveSection::getNewInfo($section_id);
        unset($live_info['live_id']);
        $live_info['live_id'] = $live_info['section_id'];
        unset($live_info['section_id']);

        //标签返回 type 1视频直播，3图文直播，4视频加图文直播
        //4快直播，5视频直播，6VR直播，7图文直播，8录播，9汇友圈直播
        $section_tags = LiveTagsRelation::getSection_alltags($section_id);
        $tags = array_column($section_tags, 'tag_id');
        if(in_array(5,$tags) && !in_array(7,$tags)){
            $live_info['type'] = 1;
        }
        if(in_array(7,$tags) && !in_array(5,$tags)){
            $live_info['type'] = 3;
        }
        if(in_array(5,$tags) && in_array(7,$tags )){
            $live_info['type'] = 4;
        }

        //直播状态
        $live_info['status'] = $live_info['status'];
        $live_info['creator_id'] = $admin_id;
        $live_info['operator_id'] = array();
        $live_info['operator_ids'] = array();

        //查看直播 推流业务员
        $live_push = ZLiveCameraAngle::getPushinfo($section_id);
        if ($live_push) {
            $live_info['operator_id'] = $live_push;
        } else {
            $live_info['operator_id'] = (object)array();
        }
        //查看直播 图文业务员
        $live_mana_push = ZLiveManager::getPushinfo($section_id);
        if ($live_mana_push) {
            $live_info['operator_ids'] = $live_mana_push;
        }
        $live_info['now_time'] = time();
        $this->_successData($live_info);
    }

    /*
     * 编辑直播
     *
     * */
    public function actionLiveEdit()
    {
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : ''; //直播ID
        $type = isset($this->params['type']) ? $this->params['type'] : ''; //直播类型 1视频直播，3图文直播，4视频加图文直播
        $title = isset($this->params['title']) ? $this->params['title'] : ''; //标题  有默认标题
        $weight = isset($this->params['weight']) ? $this->params['weight'] : 110; //权重
        $cover_img = isset($this->params['cover_img']) ? $this->params['cover_img'] : ''; //封面图
        $creator_id = isset($this->params['creator_id']) ? $this->params['creator_id'] : ''; //当前管理员ID
        $operator_ids = isset($this->params['operator_ids']) ? $this->params['operator_ids'] : 0; //推流业务员ID
        $operator_id = isset($this->params['operator_id']) ? $this->params['operator_id'] : ''; //直播推流业务员
        $start_time = isset($this->params['start_time']) ? $this->params['start_time'] : ''; //直播开始时间
        $screen = isset($this->params['screen']) ? $this->params['screen'] : 0; //画面方向，0横屏，1竖屏
        $admin_img = isset($this->params['admin_img']) ? $this->params['admin_img'] : ''; //业务员头像
        $admin_cate = isset($this->params['admin_cate']) ? $this->params['admin_cate'] : ''; //业务员类别
        $admin_alias = isset($this->params['admin_alias']) ? $this->params['admin_alias'] : ''; //业务员别名

        if (!$live_id) {
            $this->_errorData('0133', "直播ID不能为空");
        }
        if (!$creator_id) {
            $this->_errorData('0123', "当前管理员ID 不能为空");
        }
        if ($creator_id != 1) {
            //查看 缓存数据
            $redis = Yii::$app->cache;
            $red_admin = Yii::$app->params['environment'] . '_admin_role_' . $creator_id;
            $redis_info = $redis->get($red_admin);
            if (!$redis_info) {
                //如 无缓存 查看数据
                $red_list = AdminRole::getAdminRole($creator_id);
            } else {
                $red_list = $redis_info;
            }
            $role_arr = array_column($red_list, "action_name");
            if (!in_array("快直播-直播管理-编辑快直播", $role_arr) && !in_array("快直播-高级管理-创建/编辑快直播", $role_arr)) {
                $this->_errorData('0339', "暂无此权限");
            }
        }
        //查看 当前管理员 的角色 超管或高管 可以直接发布，普通人员 创建为 待审核
        $reviewed_status = 1;
        if ($creator_id == 1) {
            $reviewed_status = 0;
        } else {
            $reviewed_status = AdminRole::findRole($creator_id);
        }
        $reviewed_status = 0; //临时版本 无需审核
        //查看直播是否存在
        $live_info = LiveSection::find()
                ->with(['livenew'=>function($q){
                    $q->select('live_new.live_id');
                },'livenew.newsnew'])
            ->where('section_id = ' . $live_id)
            ->asArray()->one();

        if (!$live_info) {
            $this->_errorData('0134', "直播不存在");
        }

        if (!$type) {
            $this->_errorData('0121', "直播类型不能为空");
        }
        if (!$cover_img) {
            $cover_img = "http://vrlive-10047449.image.myqcloud.com/lv1500016289morentu.png";
        }
        if ($live_info['reviewed_status'] == 0) {
            $reviewed_status = 0;
        }
        $live_start_time = strtotime($live_info['start_time']);
        $now_date = time();
        $start_str = strtotime($start_time);
        if ($start_time && $live_start_time > $now_date) { //未开始
            //判断 时间是否比 当前服务器时间晚
            if ($start_str < $now_date) {
                $this->_errorData('0325', "开始时间异常");
            }
        }
        if ($type != 3) {
            if (!$operator_id) {
                $this->_errorData('0125', "推流业务员不能为空");
            }
        }
        if ($type != 1) {
            if (!$operator_ids) {
                $this->_errorData('0125', "推流业务员不能为空");
            } else {
                $res_admin_ids = explode(',', $operator_ids);
                if (count($res_admin_ids) > 4) {
                    $this->_errorData('0131', "业务员不能多于4人");
                }
                if ($type == 4 && in_array($operator_id, $res_admin_ids)) {
                    $this->_errorData('0132', "业务员不能重复");
                }
            }
        }
        if (!preg_match('/^(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])$/', $weight)) {
            $this->_errorData('0126', "权重必须为0到255的数字！");
        }
        $create_time = date('Y-m-d H:i:s');
        if (!$start_time || empty($start_time)) {
            $start_time = $create_time;
        }

        $param['title'] = $title;
        $param['start_time'] = $start_time;
        $param['image_url'] = $cover_img;
        $param['update_time'] = $create_time;
        $param['creator_id'] = $creator_id;
        $param['screen'] = $screen;
        $param['live_man_avatar_url'] = $admin_img ? $admin_img : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
        $param['live_man_cate'] = $admin_cate;
        $param['live_man_alias'] = $admin_alias;
        $param['reviewed_status'] = $reviewed_status;
        LiveSection::updateAll($param, ['section_id' => $live_id]);

        $news_data = array(
            'title'        => $title,
            'cover_image'  => $cover_img ? $cover_img : "http://vrlive-10047449.image.myqcloud.com/lv1500016289morentu.png",
            'app_pub'      => 0,
            'weight'       => 70,
            'refresh_time' => time(),
            'live_id'      => $live_info['live_id'],
        );
        News::updateAll($news_data, ['live_id' => $live_info['live_id']]);

        //编辑 图文 业务员
        if ($type != 1) {
            //查看 之前存储 业务员信息
            $live_manager_res = ZLiveManager::find()->where("section_id = " . $live_id)->asArray()->all();
            $live_manager_ids = array_column($live_manager_res, 'admin_id');
            $admin_ids_cou = count($res_admin_ids); //原有 人数
            $manager_ids_cou = count($live_manager_ids); //本次人数
            //查看 并对比是否有改动
            $sect_cou = count(array_intersect($res_admin_ids, $live_manager_ids)); //两次相同的 数量
            if (($admin_ids_cou != $manager_ids_cou) || ($admin_ids_cou != $sect_cou)) {
                //删除 并添加新的指派人
                ZLiveManager::deleteAll(["section_id" => $live_id]);
                //添加 直播业务员
                foreach ($res_admin_ids as $key => $val) {
                    //查询管理员名称
                    $val_name = AdminUser::find()->select("real_name")->where('admin_id = ' . $val)->asArray()->one();
                    $live_manages = new ZLiveManager();
                    $live_manages['section_id']  = $live_id;
                    $live_manages['admin_id']    = $val;
                    $live_manages['admin_name']  = $val_name['real_name'];
                    $live_manages['create_time'] = date('Y-m-d H:i:s');
                    $live_manages->save();
                }
            }
            //查看是否有 图文标签
            $section_videotag = LiveTagsRelation::getSection_tags($live_id,7,1
            );
            if(!$section_videotag){
                LiveTagsRelation::save_tags($live_id, 7, $creator_id);
            }
        }else{
            //删除 图文标签
            LiveTagsRelation::delSection_tag($live_id,1,7);
        }
        //编辑 直播推流业务员
        if (in_array($type, array(1, 4))) {
            //查看是否修改了原指派人
            $live_operator_info = ZLiveCameraAngle::find()
                ->where('section_id = ' . $live_id . ' and status=1')
                ->asArray()->one();
            if ($live_operator_info['operator_id'] != $operator_id || $live_info['start_time'] != $start_time) {

                //时间更改 才更换 推流中的 过期时间
                if ($live_info['start_time'] != $start_time) {
                    //创建直播码，并获取推流地址
                    $channel_res = $this->getChannel($live_id,$start_time);

                    //编辑 直播的 直播码记录
                    $live_channel['txy_channel_id'] = $channel_res['livecode'];
                    $live_channel['push_url'] = $channel_res['push_url'];
                    $live_channel['pull_url'] = $channel_res['pull_url'];
                    $live_channel['creator_id'] = $creator_id;
                    $live_channel['type'] = 1;
                    ZLiveChannel::updateAll($live_channel, ['channel_id' => $live_operator_info['source_id']]);
                    //编辑 直播的直播码形式的 推流人
                    $live_camera['section_id']  = $live_id;
                    $live_camera['operator_id'] = $operator_id;
                    ZLiveCameraAngle::updateAll($live_camera, ['camera_id' => $live_operator_info['camera_id']]);

                } else {
                    //编辑
                    $live_camera['section_id'] = $live_id;
                    $live_camera['operator_id'] = $operator_id;
                    ZLiveCameraAngle::updateAll($live_camera, ['camera_id' => $live_operator_info['camera_id']]);
                }
            }
            //查看是否有 视频标签
            $section_videotag = LiveTagsRelation::getSection_tags($live_id,5,1);
            if(!$section_videotag){
                LiveTagsRelation::save_tags($live_id, 5, $creator_id);
            }
        }else{
            //删除 视频标签
            LiveTagsRelation::delSection_tag($live_id,1,5);
        }
        $res_live = array();
        $h = intval(date("H"));
        $work_time = 1;
        if ($h >= 9 || $h <= 21) {
            $work_time = 0;
        }
        $res_live['work_time'] = $work_time;
        $res_live['reviewed_status'] = $reviewed_status;
        $this->_successData($res_live);
    }

    /*
     * 直播列表
     *
     * */
    public function actionLiveList()
    {
        $type = isset($this->params['type']) ? $this->params['type'] : ''; //直播类型 0视频直播，1图文直播
        $status = isset($this->params['status']) ? $this->params['status'] : ''; //直播状态 0未开始，1直播中，2结束/回顾
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : ''; //当前管理员ID
        $page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $count = !empty($_REQUEST['count']) ? $_REQUEST['count'] : 20;

        if (!$admin_id) {
            $this->_errorData('0123', "当前管理员ID 不能为空");
        }
        if (!in_array($type, array(0, 1))) {
            $this->_errorData('0001', "参数错误");
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
            if ($type == 0) { //视频
                if (!in_array("快直播-高级管理-查看全部快直播列表", $role_arr) && !in_array("快直播-直播管理-访问自己创建的快直播列表", $role_arr) && !in_array("快直播-直播推流-访问指派给自己的直播列表", $role_arr)) {
                    $this->_errorData('0339', "暂无此权限");
                }
            } else { //图文
                if (!in_array("快直播-高级管理-查看全部快直播列表", $role_arr) && !in_array("快直播-直播管理-访问自己创建的快直播列表", $role_arr) && !in_array("快直播-图文直播-访问指派给自己的直播列表", $role_arr)) {
                    $this->_errorData('0339', "暂无此权限");
                }
            }

        }
        if ($type == 0) {
            //视频直播 is_video 1
            $camera_lists = ZLiveCameraAngle::getCameraLists($admin_id, $status, $page, $count);
            foreach ($camera_lists as $key=>$val){
                //返回 category ,查看标签对应的类型值
                $live_infos = LiveTagsRelation::getSection_alltags($val['live_id']);
                $live_tags = array_column($live_infos, "tag_id");
                if(count($live_tags) > 1){
                    if(in_array(5,$live_tags ) && in_array(7,$live_tags )){
                        $camera_lists[$key]['category'] = 4;
                    }else {
                        if (in_array(5, $live_tags)) {
                            $camera_lists[$key]['category'] = 1;
                        }
                        if (in_array(7, $live_tags)) {
                            $camera_lists[$key]['category'] = 3;
                        }
                    }
                }else {
                    if (in_array(5, $live_tags)) {
                        $camera_lists[$key]['category'] = 1;
                    }
                    if (in_array(7, $live_tags)) {
                        $camera_lists[$key]['category'] = 3;
                    }
                }
            }
            $this->_successData($camera_lists);
        } else {
            //图文直播 is_textvideo 1
            $manage_lists = ZLiveManager::getLists($admin_id, $status, $page, $count);
            foreach ($manage_lists as $key=>$val){
                //返回 category ,查看标签对应的类型值
                $live_infos = LiveTagsRelation::getSection_alltags($val['live_id']);
                $live_tags = array_column($live_infos, "tag_id");
                if(count($live_tags) > 1){
                    if(in_array(5,$live_tags ) && in_array(7,$live_tags )){
                        $manage_lists[$key]['category'] = 4;
                    }else {
                        if (in_array(5, $live_tags)) {
                            $manage_lists[$key]['category'] = 1;
                        }
                        if (in_array(7, $live_tags)) {
                            $manage_lists[$key]['category'] = 3;
                        }
                    }
                }else {
                    if (in_array(5, $live_tags)) {
                        $manage_lists[$key]['category'] = 1;
                    }
                    if (in_array(7, $live_tags)) {
                        $manage_lists[$key]['category'] = 3;
                    }
                }
            }
            $this->_successData($manage_lists);
        }

    }

    /*
     * 删除直播
     *
     * */
    public function actionLiveDel()
    {
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : ''; //直播ID
        $creator_id = isset($this->params['creator_id']) ? $this->params['creator_id'] : ''; //直播推流业务员
        if (!$live_id || !$creator_id) {
            $this->_errorData('0133', "参数错误");
        }
        //查看直播是否存在
        $live_info = LiveSection::find()->where('section_id = ' . $live_id . ' and status != 0')->asArray()->one();
        if (!$live_info) {
            $this->_errorData('0134', "直播不存在");
        }

        if ($creator_id != 1) {
            //查看 缓存数据
            $redis = Yii::$app->cache;
            $red_admin = Yii::$app->params['environment'] . '_admin_role_' . $creator_id;
            $redis_info = $redis->get($red_admin);
            if (!$redis_info) {
                //如 无缓存 查看数据
                $red_list = AdminRole::getAdminRole($creator_id);
            } else {
                $red_list = $redis_info;
            }
            $role_arr = array_column($red_list, "action_name");
            if (!in_array("快直播-高级管理-删除直播", $role_arr) && !in_array("快直播-直播管理-删除快直播", $role_arr)) {
                $this->_errorData('0339', "暂无此权限");
            }
        }
        LiveSection::updateAll(["status" => 0], ['section_id' => $live_id]);
        //关联新闻状态下线
        News::updateAll(["status" => 1], ['live_id' => $live_info['live_id']]);
        $this->_successData("删除成功");
    }

    /*
     * 推流中 定时更新直播状态 和查看时间
     *
     * */
    public function actionNowStatus()
    {
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : ''; //直播ID
        if (!$live_id) {
            $this->_errorData('0133', "直播ID不能为空");
        }
        //查看直播是否存在
        $live_info = LiveSection::find()->where('section_id = ' . $live_id . " and status != 0")->asArray()->one();
        if (!$live_info) {
            $this->_errorData('0134', "直播不存在");
        }
        //直播状态
        $live_res['status'] = $live_info['status'];
        //push_time  修改为 断流时间
        //推流中 push_time 改为0 表示未断流
        $param['push_time'] = 0;
        LiveSection::updateAll($param, ['section_id' => $live_id]);
        $this->_successData($live_res, '查询成功');
    }

    /**
     * 管理员的直播推流列表
     * @return string
     */
    public function actionPushList()
    {
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';
        if (!$admin_id) {
            $this->_errorData("0101", "参数错误");
        }
        //查看管理员对应的  推流信息
        $res_list = ZLiveCameraAngle::getPushList($admin_id);
        //返回 正常的直播状态
        foreach ($res_list as $key => $val) {
            $start_time = strtotime($val['start_time']);
            if ($start_time > time()) { //未开始
                $res_list[$key]['status'] = 3;
            } else { //直播中
                $res_list[$key]['status'] = 4;
            }
        }
        $this->_successData($res_list);

    }

    /*
     * 图文直播 直播中 更新状态 为直播结束
     * */
    public function actionLiveStatus()
    {
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : 0; //直播ID
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : ''; //当前管理员ID
        if (!$live_id || !$admin_id) {
            $this->_errorData('0133', "参数错误");
        }
        $compere = LiveSection::getCompere($live_id); //获取直播员信息
        if (!$compere) {
            $this->_errorData('0134', "直播不存在");
        }

        $live_data['status'] = 2;
        LiveSection::updateAll($live_data, ['section_id' => $live_id]);
        $this->_successData("更新成功");
    }


    /*
    * 点击创建 新 录播 选择：实时录制视频--创建直播码
    *
    * */
    public function actionRecord()
    {
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : 0; //当前管理员ID
        if (!$admin_id) {
            $this->_errorData('0123', "当前管理员ID 不能为空");
        }
        //创建 录播
        $live_id = $this->create_live_id();
        //创建 直播临时 信息
        $live_sql = new LiveSection();
        $live_sql['section_id'] = $live_id;
        $live_sql['status'] = 0;
        $live_sql['creator_id'] = $admin_id;
        $live_sql->save();

        $bizId = Yii::$app->params['API_LiveCode'];
        $streamId = $live_id . '_' . time();
        $time = date("Y-m-d H:i:s", time() + 60 * 60 * 48); //直播开始后的48小时 过期
        $live_key = Yii::$app->params['API_LiveKey'];
        $txTime = strtoupper(base_convert(strtotime($time), 10, 16));
        $livecode = $bizId . "_" . $streamId; //直播码
        $txSecret = md5($live_key . $livecode . $txTime);
        $ext_str = "?" . http_build_query(array(
                "bizid"    => $bizId,
                "txSecret" => $txSecret,
                "txTime"   => $txTime
            ));
        $push_url = "rtmp://" . $bizId . ".livepush.myqcloud.com/live/" . $livecode . (isset($ext_str) ? $ext_str : "");
        $pull_url = "http://" . $bizId . ".liveplay.myqcloud.com/live/" . $livecode . ".m3u8";
        $live_channel = new ZLiveChannel();
        $live_channel['section_id'] = $live_id;
        $live_channel['txy_channel_id'] = $livecode;
        $live_channel['channel_name'] = "直播码-" . $live_id;
        $live_channel['device_type'] = 3;
        $live_channel['manager'] = $admin_id;
        $live_channel['status'] = 1;
        $live_channel['push_url'] = $push_url;
        $live_channel['pull_url'] = $pull_url;
        $live_channel['create_time'] = date("Y-m-d H:i:s");
        $live_channel['creator_id'] = $admin_id;
        $live_channel['type'] = 1;
        $result = $live_channel->save();
        //查看直播码
        $channel_name = "直播码-" . $live_id;
        $channel_id = ZLiveChannel::find()->where(['channel_name' => $channel_name])
            ->orderBy("create_time desc")->asArray()->one();
        //添加 直播的直播码形式的 推流人
        $live_camera = new ZLiveCameraAngle();
        $live_camera['section_id'] = $live_id;
        $live_camera['signal_source'] = 1;
        $live_camera['name'] = "直播码-" . $channel_id['channel_id'];
        $live_camera['source_id'] = $channel_id['channel_id'];
        $live_camera['operator_id'] = $admin_id;
        $live_camera->save();
        $res = array();
        if (!$result) {
            $this->_errorData('0128', "保存失败");
        }
        $res['live_id'] = $live_id;
        $res['push_url'] = $push_url;
        $this->_successData($res);
    }

    /*
     * 新 创建录播 编辑录播
     *
     * */
    public function actionRecordCreate()
    {
        $save_type  = isset($this->params['save_type']) ? $this->params['save_type'] : 0; //操作类型，0创建录播，1 编辑录播
        $type = isset($this->params['type']) ? $this->params['type'] : 0; //直播类型，0选用已有视频，1 实时录制视频
        $section_id = isset($this->params['live_id']) ? $this->params['live_id'] : 0; //直播ID
        $title      = isset($this->params['title']) ? $this->params['title'] : ''; //标题
        $cover_img  = isset($this->params['cover_img']) ? $this->params['cover_img'] : ''; //封面图
        $rever_url  = isset($this->params['rever_url']) ? $this->params['rever_url'] : ''; //视频地址
        $rever_img_url = isset($this->params['rever_img_url']) ? $this->params['rever_img_url'] : ''; //视频地址缩略图
        $start_time    = isset($this->params['start_time']) ? $this->params['start_time'] : ''; //直播开始时间
        $admin_id    = isset($this->params['admin_id']) ? $this->params['admin_id'] : 0; //当前管理员ID
        $weight      = isset($this->params['weight']) ? $this->params['weight'] : 110; //权重
        $screen      = isset($this->params['screen']) ? $this->params['screen'] : 0; //横竖屏
        $column_id   = isset($this->params['column_id']) ? $this->params['column_id'] : '';     //栏目ID
        $column_type = isset($this->params['column_type']) ? $this->params['column_type'] : '0';    //栏目分类


        if (!$cover_img) {
            $cover_img = "http://vrlive-10047449.image.myqcloud.com/lv1500016289morentu.png";
        }
        if (!$admin_id) {
            $this->_errorData('0123', "当前管理员ID 不能为空");
        }
        $res_live = array();
        $h = intval(date("H"));
        $work_time = 1;
        if ($h >= 9 || $h <= 21) {
            $work_time = 0;
        }
        $res_live['work_time'] = $work_time;
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

        }
        //查看 当前管理员 的角色 超管或高管 可以直接发布，普通人员 创建为 待审核
        $reviewed_status = 1;
        if ($admin_id == 1) {
            $reviewed_status = 0;
        } else {
            $reviewed_status = AdminRole::findRole($admin_id);
        }
        $reviewed_status = 0; //临时版本 无需审核
        $res_live['reviewed_status'] = $reviewed_status;
        if (!$start_time) {
            $this->_errorData('0124', "直播开始时间不能为空");
        }
        $create_time = date('Y-m-d H:i:s');
        //编辑录播
        if ($save_type == 1 && $section_id) {
            if (!in_array("快直播-高级管理-创建/编辑快直播", $role_arr) && !in_array("快直播-录播管理-编辑录播", $role_arr)) {
                $this->_errorData('0339', "暂无此权限");
            }
            $live_check = LiveSection::find()
                ->where('section_id = ' . $section_id)->asArray()->one();
            if (!$live_check) {
                $this->_errorData('0134', "录播不存在");
            }
            if ($live_check['reviewed_status'] == 0) {
                $reviewed_status = 0;
            }
            $param['title'] = $title;
            $param['start_time'] = $start_time;
            $param['image_url'] = $cover_img;
            $param['rever_url'] = $rever_url;
            $param['rever_img_url'] = $rever_img_url;
            $param['update_time'] = $create_time;
            $param['creator_id'] = $admin_id;
            $param['screen'] = $screen;
            $param['reviewed_status'] = $reviewed_status;
            $result = LiveSection::updateAll($param, ['section_id' => $section_id]);
            if (!$result) {
                $this->_errorData('0128', "修改直播失败");
            }
            //更新创建关联新闻
            $news_data = array(
                'title'        => $title,
                'image_url'  => $cover_img ? $cover_img : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png",
                'refresh_time' => time(),
            );
            if ($reviewed_status == 0) {
                $news_data['status'] = 0;
            } else {
                $news_data['status'] = 1;
            }
            //更新 系列信息
            LiveNew::updateAll($news_data,['live_id'=>$live_check['live_id']]);
            $news_data['cover_image'] = $news_data['image_url'];
            unset($news_data['image_url']);
            if ($reviewed_status != 0) {
                $news_data['status'] = 3;
            }
            News::updateAll($news_data, ['live_id' => $live_check['live_id']]);
            $this->_successData($res_live);
        }
        if (!in_array("快直播-高级管理-创建/编辑快直播", $role_arr) && !in_array("快直播-录播管理-创建录播", $role_arr)) {
            $this->_errorData('0339'
                , "暂无此权限");
        }
        //创建 录播
        //创建系列
        $createlive = $this->createlive($column_type,$column_id,$admin_id,$title,$cover_img,'',1);
        if(!$createlive){
            $this->_errorData('1201', '创建系列失败');
        }
        $live_id = $createlive['live_id'];

        //创建 录播
        if ($type == 0) {
            $section_id = $this->create_live_id();
            //添加 录播标签
            LiveTagsRelation::save_tags($section_id,8, $admin_id);
        } else {
            //创建 实时 真实直播信息
            $param['live_id'] = $live_id;
            $param['title']  = $title;
            $param['status'] = 5;
            $param['start_time'] = $start_time;
            $param['image_url'] = $cover_img;
            $param['create_time'] = $create_time;
            $param['update_time'] = $create_time;
            $param['refresh_time'] = $create_time;
            $param['rever_video_category'] = 1;
            $param['screen'] = $screen;
            $param['reviewed_status'] = $reviewed_status;
            $result_create = LiveSection::updateAll($param, ['section_id' => $section_id]);

            if (!$result_create) {
                $this->_errorData('0128', "保存直播失败");
            }
            // 快直播录播创建成成功后，创建管理关联新闻
            $news_data = new News();
            //判定状态如果开直播审核通过则相关新闻设置状态为已发布
            if ($reviewed_status == 0) {
                $news_data['status'] = 0;
            } else {
                $news_data['status'] = 3;
            }

            $news_data['title'] = $title;
            $news_data['cover_image'] = $cover_img ? $cover_img : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
            $news_data['app_pub'] = 0;
            $news_data['weight'] = 70;
            $news_data['type'] = 15;//新增新闻类型，14.集入口 15.普通快直播
            $news_data['create_time'] = date('Y-m-d H:i:s', time());
            $news_data['refresh_time'] = time();
            $news_data['live_id'] = $live_id;
            if (!$column_type) {
                $news_data['column_id'] = $column_id;
            } else {
                $news_data['area_id'] = $column_id;
            }
            $insert_news = $news_data->save();
            if ($insert_news) {
                $param_new['news_id'] = $news_data['news_id'];
                LiveNew::updateAll($param_new, ['live_id' => $live_id]);
            }

            //添加 录播标签
            LiveTagsRelation::save_tags($section_id,8, $admin_id);

            if (!AdminRole::findRole($admin_id) || $admin_id == 1) {
                LiveTagsRelation::save_tags($section_id,2, $admin_id);
            }
            $this->_successData($res_live);
        }

        //添加 录播标签
        LiveTagsRelation::save_tags($section_id,8, $admin_id);

        $param = new LiveSection();
        $param['status'] = 5;
        $param['live_id'] = $live_id;
        $param['section_id'] = $section_id;
        $param['title'] = $title;
        $param['start_time'] = $start_time;
        $param['image_url'] = $cover_img;
        $param['rever_url'] = $rever_url;
        $param['rever_img_url'] = $rever_img_url;
        $param['create_time'] = $create_time;
        $param['update_time'] = $create_time;
        $param['refresh_time'] = $create_time;
        $param['creator_id'] = $admin_id;
        $param['screen'] = $screen;
        $param['rever_video_category'] = 1;
        $param['reviewed_status'] = $reviewed_status;
        $result = $param->save();
        if (!$result) {
            $this->_errorData('0128', "保存直播失败");
        }
        // 快直播录播创建成成功后，创建管理关联新闻
        $news_data = new News();
        //判定状态如果开直播审核通过则相关新闻设置状态为已发布
        if ($reviewed_status == 0) {
            $news_data['status'] = 0;
        } else {
            $news_data['status'] = 3;
        }
        $news_data['title'] = $title;
        $news_data['cover_image'] = $cover_img ? $cover_img : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
        $news_data['app_pub'] = 0;
        $news_data['weight'] = 70;
        $news_data['type'] = 15;//新增新闻类型，14.集入口 15.普通快直播
        $news_data['create_time'] = date('Y-m-d H:i:s', time());
        $news_data['refresh_time'] = time();
        $news_data['live_id'] = $live_id;
        if (!$column_type) {
            $news_data['column_id'] = $column_id;
        } else {
            $news_data['area_id'] = $column_id;
        }
        $insert_news = $news_data->save();
        if ($insert_news) {
            $param_new['news_id'] = $news_data['news_id'];
            LiveNew::updateAll($param_new, ['live_id' => $live_id]);
        }

        if (!AdminRole::findRole($admin_id) || $admin_id == 1) {
            LiveTagsRelation::save_tags($section_id,2, $admin_id);
        }
        $this->_successData($res_live);
    }

    /*
     * 录播详情
     *
     * */
    public function actionRecordInfo()
    {
        $section_id = isset($this->params['live_id']) ? $this->params['live_id'] : 0; //直播ID
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : 0; //当前管理员ID
        if (!$section_id) {
            $this->_errorData('0133', "直播ID不能为空");
        }
        if (!$admin_id) {
            $this->_errorData('0123', "当前管理员ID 不能为空");
        }
        //查看录播是否存在
        $live_check = LiveSection::find()
            ->with(['livenew'=>function($q){
                $q->select('live_new.live_id');
            },'livenew.newsnew'   => function ($q) {
                $q->select('news_id,column_id,area_id');
            }, 'livenew.newsnew.column' => function ($q) {
                $q->select('column_id,name');
            }, 'livenew.newsnew.area'   => function ($q) {
                $q->select('area_id,name');
            }])
            ->select("
                live_id,
                section_id,
                title as name,
                image_url,
                rever_url,
                rever_img_url,
                start_time,
                creator_id,
                screen,
                reviewed_status,
                amendments")
            ->where('section_id = ' . $section_id)
            ->asArray()->one();
        if (!$live_check) {
            $this->_errorData('0134', "录播不存在");
        }
        unset($live_check['live_id']);
        $live_check['live_id'] = $live_check['section_id'];
        unset($live_check['section_id']);

        if ($live_check['livenew']['newsnew']['column_id']) {
            $live_check['column_id'] = $live_check['livenew']['newsnew']['column_id'];
            $live_check['column_type'] = '0';
        }
        if ($live_check['livenew']['newsnew']['area']) {
            $live_check['column_id'] = $live_check['news']['area_id'];
            $live_check['column_type'] = '1';

        }
        unset($live_check['livenew']);
        //查看是否有推流信息 返回类型，已有视频0/实时录制1
        $live_check['live_type'] = 0;
        $live_creator = ZLiveCameraAngle::find()->where(['section_id' => $section_id])->one();
        if ($live_creator) {
            $live_check['live_type'] = 1;
        }
        unset($live_check['creator_id']);
        $this->_successData($live_check);
    }

    /*
     * 录播列表
     *
     * */
    public function actionRecordList()
    {
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : ''; //当前管理员ID
        $page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $count = !empty($_REQUEST['count']) ? $_REQUEST['count'] : 20;
        if (!$admin_id) {
            $this->_errorData('0123', "当前管理员ID 不能为空");
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
            if (!in_array("快直播-高级管理-查看全部快直播列表", $role_arr) && !in_array("快直播-录播管理-访问自己创建的录播列表", $role_arr)) {
                $this->_errorData('0339', "暂无此权限");
            }
        }
        //查看 录播列表
        $record_lists = LiveSection::getRecordList($admin_id, $page, $count);
        foreach ($record_lists as $key => $val) {
            //查看是否有推流信息 返回类型，已有视频0/实时录制1
            $record_lists[$key]['live_type'] = 0;
            $live_creator = ZLiveCameraAngle::find()->where(['section_id' => $val['live_id']])->one();
            if ($live_creator) {
                $record_lists[$key]['live_type'] = 1;
            }
        }
        $this->_successData($record_lists);
    }

    /*
    * 录播 删除
    *
    * */
    public function actionRecordDel()
    {
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : 0; //录播ID
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : 0; //当前管理员ID
        if (!$live_id || !$admin_id) {
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
            if (!in_array("快直播-高级管理-删除直播", $role_arr) && !in_array("快直播-录播管理-删除录播", $role_arr)) {
                $this->_errorData('0339', "暂无此权限");
            }
        }
        //查看直播是否存在
        $section_info = LiveSection::find()->where('section_id = ' . $live_id)->one();
        if (!$section_info) {
            $this->_errorData('0134', "录播不存在");

        }
        $live_info = LiveNew::findOne($section_info->live_id);
        if(!$live_info){
            $this->_errorData('0191','无对应 系列信息' );
        }

        //类型为 直播，或 系列内只有一条直播，删除 直播 同时删除系列
        if(($live_info->type == 0 && $live_info->live_count == 1) || $live_info->type == 1){
            //此系列 只有一条直播，删除直播 同时删除 系列    ---是否删新闻？
            $section_info->status = 0;
            if($section_info->save()){
                $live_info->status = 0;
                $live_info->save();
                $this->_successData('删除成功');
            }else{
                $this->_errorData('0113', '删除失败');
            }
        }

        $this->_successData("删除成功");
    }


    //创建 直播ID
    private function create_live_id(){
        return date('Y') . time() . rand(0000, 9999);
    }

    /**
     * 创建融云图文直播聊天室
     *
     * @param $live_id
     * @return mixed
     */
    private function create_rcloud_chroom_pic_txt($live_id)
    {
        $nonce = mt_rand();
        $timeStamp = time();
        $sign = sha1(Yii::$app->params['ryAppSecret'] . $nonce . $timeStamp);
        $header = array(
            'RC-App-Key:' . Yii::$app->params['ryAppKey'],
            'RC-Nonce:' . $nonce,
            'RC-Timestamp:' . $timeStamp,
            'RC-Signature:' . $sign,
        );
        $data = 'chatroom[room_' . $live_id . ']=ptn' . $live_id;
        $result = $this->curl_http(Yii::$app->params['ryApiUrl'] . '/chatroom/create.json', $data, $header);

        return $result['code'];
    }
    /*
        * 创建系列
        * column_type 栏目类别，0:是普通栏目 1：本地栏目
        * column_id 常规栏目id/本地栏目ID
        * name 系列名称
        * */
    public function createlive($column_type = 0,$column_id = '',$admin_id = '',$name = '',$live_imgurl='',$live_intro='',$type = 0){
        if(!isset($column_type) || !$column_id || !$admin_id || !$name){
            return false;
        }
        //创建系列
        $param = new LiveNew();
        $live_id = $this->create_live_id();
        $param['live_id']   = $live_id;
        $param['name']      = $name;
        $param['image_url'] = $live_imgurl ? $live_imgurl : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
        $param['create_time']  = date("Y-m-d H:i:s");
        $param['creator_id']   = $admin_id;
        $param['introduction'] = $live_intro;
        $param['is_fast']   = 1;
        $param['show_type'] = 1;
        $param['status']    = 1; //默认删除状态，直播创建成功 更改为正常状态
        $param['type']      = $type;
        $result = $param->save();
        if(!$result){
            return false;
        }

        $createlive['live_id'] = $live_id;
        return $createlive;
    }

    /**
     * 创建融云聊天室
     * @param $live_id
     * @return mixed
     */
    public function create_rcloud_chroom($live_id){
        $nonce = mt_rand();
        $timeStamp = time();
        $sign = sha1(Yii::$app->params['ryAppSecret'] . $nonce . $timeStamp);
        $header = array(
            'RC-App-Key:' . Yii::$app->params['ryAppKey'],
            'RC-Nonce:' . $nonce,
            'RC-Timestamp:' . $timeStamp,
            'RC-Signature:' . $sign,
        );
        $data = 'chatroom[room_' . $live_id . ']=n'.$live_id;
        $result = $this->curl_http(Yii::$app->params['ryApiUrl'] . '/chatroom/create.json', $data, $header);
        return $result['code'];
    }

    //创建直播码
    public function getChannel($section_id,$start_time){
        $bizId = Yii::$app->params['API_LiveCode'];
        $streamId = $section_id.'_'.time();
        $time     = date("Y-m-d H:i:s",strtotime($start_time) + 60*60*24); //直播开始后的24小时 过期
        $live_key = Yii::$app->params['API_LiveKey'];

        $txTime = strtoupper(base_convert(strtotime($time),10,16));
        $livecode = $bizId."_".$streamId; //直播码
        $txSecret = md5($live_key.$livecode.$txTime);
        $ext_str = "?".http_build_query(array(
                "bizid"=> $bizId,
                "txSecret"=> $txSecret,
                "txTime"=> $txTime
            ));
        $push_url = "rtmp://".$bizId.".livepush.myqcloud.com/live/".$livecode.(isset($ext_str) ? $ext_str : "");
        $pull_url = "http://".$bizId.".liveplay.myqcloud.com/live/".$livecode.".m3u8";
        $res['livecode']  = $livecode;
        $res['push_url']  = $push_url;
        $res['pull_url']  = $pull_url;
        return $res;
    }
    
}
