<?php

namespace backend\controllers;

use common\models\AdminRole;
use common\models\LiveSection;
use common\models\LiveTagsRelation;
use common\models\PowerAction;
use common\models\User;
use common\models\AdminUser;
use common\models\Live;
use common\models\News;
use common\models\LiveCameraAngle;
use common\models\LiveChannel;
use common\models\LiveManager;
use common\models\LivePanelManage;
use common\models\NewsPraise;
use common\models\ResourceLibrary;
use common\models\ZLiveCameraAngle;
use common\models\ZLiveManager;
use Faker\Test\Provider\PaymentTest;
use Yii;

/**
 * Live controller
 */
class LiveController extends PublicBaseController
{
    
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
        $res_list = LiveCameraAngle::getPushList($admin_id);
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
        $returnData = LivePanelManage::UserGetNewMessage($live_id, $last_id, 0, $pageSize);
        $this->_successData($returnData, "查选成功");
    }
    
    /*
     * 推流人员 列表
     * type 0除admin外所有的管理员，1推流业务员，2图文业务员
     *
     * */
    public function actionOperatorList()
    {
        $type = isset($this->params['type']) ? $this->params['type'] : 0;
        if ($type == 0) {
            //查看 除admin 外全部管理员
            $manager_all = AdminUser::find()
                ->select("admin_id,real_name")
                ->where("status=1 and admin_id != 1")
                ->asArray()->all();
            $this->_successData($manager_all, '查询成功');
        }
        $search = PowerAction::getSearchList($type);
        $this->_successData($search, '查询成功');
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
        $live_info = Live::find()->where('live_id = ' . $live_id . " and status != 0")->asArray()->one();
        if (!$live_info) {
            $this->_errorData('0134', "直播不存在");
        }
        //直播状态
        $live_res['status'] = Live::getLiveStatus($live_info['start_time'], $live_info['status']);
        //push_time  修改为 断流时间
        //推流中 push_time 改为0 表示未断流
        $param['push_time'] = 0;
        Live::updateAll($param, ['live_id' => $live_id]);
        $this->_successData($live_res, '查询成功');
    }
    
    /*
     * 创建直播
     *
     * */
    public function actionLiveCreate()
    {
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
        $column_id = isset($this->params['column_id']) ? $this->params['column_id'] : '';     //栏目ID
        $column_type = isset($this->params['column_type']) ? $this->params['column_type'] : '0';    //栏目分类
        $res_live = array();
        if (!$type) {
            $this->_errorData('0121', "直播类型不能为空");
        }
        if (!$title) {
            $this->_errorData('0121', "直播标题不能为空");
        }
        if (!$cover_img) {
            $cover_img = "http://vrlive-10047449.image.myqcloud.com/lv1500016289morentu.png";
//            $this->_errorData('0122', "封面图不能为空");
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
        $live_id = $this->create_live_id();
        if (in_array($type, array(3, 4))) {
            //创建图文直播聊天室
            $code = $this->create_rcloud_chroom_pic_txt($live_id);
            if (!$code == 200) {
                $this->_errorData('0127', '创建融云图文直播聊天室失败，请联系系统管理员！');
            }
        }
        //创建融云聊天室
        $code = $this->create_rcloud_chroom($live_id);
        if ($code == 200) {
            $create_time = date('Y-m-d H:i:s');
            if (!$start_time || empty($start_time)) {
                $start_time = $create_time;
            }
            $param = new Live();
            $param['category'] = $type;
            $param['live_id'] = $live_id;
            $param['name'] = $title;
            $param['weight'] = $weight;
            $param['start_time'] = $start_time;
            $param['image_url'] = $cover_img;
            $param['create_time'] = $create_time;
            $param['update_time'] = $create_time;
            $param['refresh_time'] = $create_time;
            $param['creator_id'] = $creator_id;
            $param['is_fast'] = 1;
            $param['screen'] = $screen;
            $param['live_man_avatar_url'] = $admin_img ? $admin_img : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
            $param['live_man_cate'] = $admin_cate;
            $param['live_man_alias'] = $admin_alias;
            $param['view_ranage'] = '5-10|1-20|1-5';
            $param['reviewed_status'] = $reviewed_status;
            $param['live_tag']        = 1;
            
            $result = $param->save();
            if (!$result) {
                $this->_errorData('0128', "保存直播失败");
            }
            // 快直播创建成果后，创建管理关联新闻，状态为待审核
            $news_data = new News();
            //'news_id'      => $news_id_add,
            $news_data['title'] = $title;
            $news_data['cover_image'] = $cover_img ? $cover_img : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
            $news_data['app_pub'] = 1;
            $news_data['weight'] = 70;
            $news_data['type'] = 15;//新增新闻类型，17.集入口 15.普通快直播
            $news_data['create_time'] = date('Y-m-d H:i:s', time());
            $news_data['refresh_time'] = time();
            $news_data['live_id'] = $live_id;
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
                $param['news_id'] = $news_data->news_id;
                $param->update();
            }

//            $live_tag = new LiveTagsRelation();
//            $live_tag['live_id'] = $live_id;
//            $live_tag['tag_id']  = 1;
//            $live_tag['type']    = 1;
//            $live_tag['create_time'] = date('Y-m-d H:i:s', time());
//            $live_tag['creator']     = $creator_id;
//            $live_tag->save();
            // 当超管/高管,或当前用户未配置栏目时,创建的快直播,录播自带直播标签，tag_id=2时是“直播"标签
            $without_column= Live::getBackAdminColumnsList($creator_id);
            $user_without_column = 1;
            if(isset($without_column[0]['column_id'])){
                if($without_column[0]['column_id']==-200){
                    $user_without_column = 0;
                }
            }
            if (!AdminRole::findRole($creator_id) || $creator_id== 1 ||!$user_without_column) {
                $live_tag = new LiveTagsRelation();
                $live_tag['live_id'] = $live_id;
                $live_tag['tag_id'] = 2;
                $live_tag['type'] = 1;
                $live_tag['create_time'] = date('Y-m-d H:i:s', time());
                $live_tag['creator'] = $creator_id;
                $live_tag->save();
            }
            if($type != 1) {
                //添加 直播业务员
                foreach ($res_admin_ids as $key => $val) {
                    //查询管理员名称
                    $val_name = AdminUser::find()->select("real_name")->where('admin_id = ' . $val)->asArray()->one();
                    $live_manages = new LiveManager();
                    $live_manages['live_id'] = $live_id;
                    $live_manages['admin_id'] = $val;
                    $live_manages['admin_name'] = $val_name['real_name'];
                    $live_manages['create_time'] = date('Y-m-d H:i:s');
                    $live_manages->save();
                }
            }
            if (in_array($type, array(1, 4))) {
                //创建直播码，并获取推流地址
                $bizId = Yii::$app->params['API_LiveCode'];
                $streamId = $live_id . '_' . time();
                $time = date("Y-m-d H:i:s", strtotime($start_time) + 60 * 60 * 24); //直播开始后的24小时 过期
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
                //创建 直播的 直播码记录
                $live_channel = new LiveChannel();
                $live_channel['txy_channel_id'] = $livecode;
                $live_channel['channel_name'] = "直播码-" . $live_id;
                $live_channel['device_type'] = 3;
                $live_channel['manager'] = $creator_id;
                $live_channel['status'] = 1;
                $live_channel['push_url'] = $push_url;
                $live_channel['pull_url'] = $pull_url;
                $live_channel['create_time'] = date("Y-m-d H:i:s");
                $live_channel['creator_id'] = $creator_id;
                $live_channel['type'] = 1;
                $live_channel->save();
                $channel_id = $live_channel->getAttributes(array(0 => 'channel_id'));
                //添加 直播的直播码形式的 推流人
                $live_camera = new LiveCameraAngle();
                $live_camera['live_id'] = $live_id;
                $live_camera['signal_source'] = 1;
                $live_camera['name'] = "直播码-" . $channel_id['channel_id'];
                $live_camera['source_id'] = $channel_id['channel_id'];
                $live_camera['operator_id'] = $operator_id;
                $live_camera->save();
                $res_live['push_url'] = $push_url;
            }
            $h = intval(date("H"));
            $work_time = 1;
            if ($h >= 9 || $h <= 21) {
                $work_time = 0;
            }
            $res_live['live_id'] = $live_id;
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
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : ''; //直播ID
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : ''; //当前管理员ID
        if (!$live_id) {
            $this->_errorData('0133', "直播ID不能为空");
        }
        if (!$admin_id) {
            $this->_errorData('0123', "当前管理员ID 不能为空");
        }
        //查看直播是否存在
        $live_check = Live::find()->where('live_id = ' . $live_id . " and status != 0")->asArray()->one();
        if (!$live_check) {
            $this->_errorData('0134', "直播不存在");
        }
//        if($admin_id != $live_check['creator_id']){
//            $this->_errorData('0136', '只有 直播创建者可以编辑直播');
//        }
        //查看直播详情
        $live_info = Live::getLiveInfo($live_id);
        //直播状态
        $live_info['status'] = Live::getLiveStatus($live_info['start_time'], $live_info['status']);
        $live_info['creator_id'] = $admin_id;
        $live_info['operator_id'] = array();
        $live_info['operator_ids'] = array();
        //查看直播 推流业务员
        $live_push = LiveCameraAngle::getPushinfo($live_id);
        if ($live_push) {
            $live_info['operator_id'] = $live_push;
        } else {
            $live_info['operator_id'] = (object)array();
        }
        //查看直播 图文业务员
        $live_mana_push = LiveManager::getPushinfo($live_id);
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
        //$is_cover = isset($this->params['is_cover']) ? $this->params['is_cover'] : 0; //入口设置
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
        //查看直播是否存在
        $live_info = Live::find()->with('news')->where('live_id = ' . $live_id)->asArray()->one();
        if (!$live_info) {
            $this->_errorData('0134', "直播不存在");
        }
//        if($creator_id != $live_info['creator_id'] || $creator_id != 1){
//            $this->_errorData('0136', '只有 直播创建者和超管可以编辑直播');
//        }
        if (!$type) {
            $this->_errorData('0121', "直播类型不能为空");
        }
        if (!$cover_img) {
            $cover_img = "http://vrlive-10047449.image.myqcloud.com/lv1500016289morentu.png";
//            $this->_errorData('0122', "封面图不能为空");
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
        $param['category'] = $type;
        $param['name'] = $title;
        $param['weight'] = $weight;
        $param['start_time'] = $start_time;
        $param['image_url'] = $cover_img;
        $param['update_time'] = $create_time;
        $param['creator_id'] = $creator_id;
        $param['is_fast'] = 1;
        $param['screen'] = $screen;
        $param['live_man_avatar_url'] = $admin_img ? $admin_img : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
        $param['live_man_cate'] = $admin_cate;
        $param['live_man_alias'] = $admin_alias;
        $param['reviewed_status'] = $reviewed_status;
        $result = Live::updateAll($param, ['live_id' => $live_id]);
        if (!$result) {
            $this->_errorData('0128', "修改直播失败");
        }
        $news_data = array(
            //'news_id'      => $news_id_add,
            'title'        => $title,
            'cover_image'  => $cover_img ? $cover_img : "http://vrlive-10047449.image.myqcloud.com/lv1500016289morentu.png",
            'app_pub'      => 1,
            'weight'       => 70,
            //'type'         => 15,//新增新闻类型，14.集入口 15.普通快直播
            //'create_time'  => date('Y-m-d H:i:s', time()),
            'refresh_time' => time(),
            'live_id'      => $live_id,
        );
        News::updateAll($news_data, ['live_id' => $live_id]);
        //编辑 图文 业务员
        if ($type != 1) {
            //查看 之前存储 业务员信息
            $live_manager_res = LiveManager::find()->where("live_id = " . $live_id)->asArray()->all();
            $live_manager_ids = array_column($live_manager_res, 'admin_id');
            $admin_ids_cou = count($res_admin_ids); //原有 人数
            $manager_ids_cou = count($live_manager_ids); //本次人数
            //查看 并对比是否有改动
            $sect_cou = count(array_intersect($res_admin_ids, $live_manager_ids)); //两次相同的 数量
            if (($admin_ids_cou != $manager_ids_cou) || ($admin_ids_cou != $sect_cou)) {
                //删除 并添加新的指派人
                LiveManager::deleteAll(["live_id" => $live_id]);
                //添加 直播业务员
                foreach ($res_admin_ids as $key => $val) {
                    //查询管理员名称
                    $val_name = AdminUser::find()->select("real_name")->where('admin_id = ' . $val)->asArray()->one();
                    $live_manages = new LiveManager();
                    $live_manages['live_id'] = $live_id;
                    $live_manages['admin_id'] = $val;
                    $live_manages['admin_name'] = $val_name['real_name'];
                    $live_manages['create_time'] = date('Y-m-d H:i:s');
                    $live_manages->save();
                }
            }
        }
        //编辑 直播推流业务员
        if (in_array($type, array(1, 4))) {
            //查看是否修改了原指派人
            $live_operator_info = LiveCameraAngle::find()->where('live_id = ' . $live_id . ' and status=1')->asArray()->one();
            if ($live_operator_info['operator_id'] != $operator_id || $live_info['start_time'] != $start_time) {
                
                //时间更改 才更换 推流中的 过期时间
                if ($live_info['start_time'] != $start_time) {
                    //创建直播码，并获取推流地址
                    $bizId = Yii::$app->params['API_LiveCode'];
                    $streamId = $live_id . '_' . time();
                    $time = date("Y-m-d H:i:s", strtotime($start_time) + 60 * 60 * 24); //直播开始后的24小时 过期
                    $live_key = Yii::$app->params['API_LiveKey'];
                    $txTime = strtoupper(base_convert(strtotime($time), 10, 16));
                    $livecode = $bizId . "_" . $streamId;  //直播码
                    $txSecret = md5($live_key . $livecode . $txTime);
                    $ext_str = "?" . http_build_query(array(
                            "bizid"    => $bizId,
                            "txSecret" => $txSecret,
                            "txTime"   => $txTime
                        ));
                    $push_url = "rtmp://" . $bizId . ".livepush.myqcloud.com/live/" . $livecode . (isset($ext_str) ? $ext_str : "");
                    $pull_url = "http://" . $bizId . ".liveplay.myqcloud.com/live/" . $livecode . ".m3u8";
                    //编辑 直播的 直播码记录
                    $live_channel['txy_channel_id'] = $livecode;
                    $live_channel['push_url'] = $push_url;
                    $live_channel['pull_url'] = $pull_url;
                    $live_channel['creator_id'] = $creator_id;
                    $live_channel['type'] = 1;
                    LiveChannel::updateAll($live_channel, ['channel_id' => $live_operator_info['source_id']]);
                    //编辑 直播的直播码形式的 推流人
                    $live_camera['live_id'] = $live_id;
                    $live_camera['operator_id'] = $operator_id;
                    LiveCameraAngle::updateAll($live_camera, ['camera_id' => $live_operator_info['camera_id']]);
                    
                } else {
                    //编辑
                    $live_camera['live_id'] = $live_id;
                    $live_camera['operator_id'] = $operator_id;
                    LiveCameraAngle::updateAll($live_camera, ['camera_id' => $live_operator_info['camera_id']]);
                }
            }
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
        $live_info = Live::find()->where('live_id = ' . $live_id . ' and status != 0')->asArray()->one();
        if (!$live_info) {
            $this->_errorData('0134', "直播不存在");
        }
//        if($creator_id != $live_info['creator_id'] || $creator_id != 1){
//            $this->_errorData('0136', '只有 直播创建者和超管可以编辑直播');
//        }
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
        Live::updateAll(["status" => 0], ['live_id' => $live_id]);
        //关联新闻状态下线
        News::updateAll(["status" => 1], ['live_id' => $live_id]);
        $this->_successData("删除成功");
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
        $column_id = isset($this->params['column_id']) ? $this->params['column_id'] : '';     //栏目ID
        $column_type = isset($this->params['column_type']) ? $this->params['column_type'] : '0';    //栏目分类
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
            //视频直播1 和 图文加视频直播4
            $camera_lists = LiveCameraAngle::getCameraLists($admin_id, $status, $page, $count);
            $this->_successData($camera_lists);
        } else {
            //图文直播3 和图文加视频直播4
            $manage_lists = LiveManager::getLists($admin_id, $status, $page, $count);
            $this->_successData($manage_lists);
        }

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
        $compere = Live::getCompere($live_id); //获取直播员信息
        if (!$compere) {
            $this->_errorData('0134', "直播不存在");
        }
        if (!in_array($compere['category'], array(3, 4))) {
            $this->_errorData('0137', "直播类型有误");
        }
        if ($compere['creator_id'] != $admin_id) {
            $this->_errorData('0136', '只有 直播创建者可以编辑直播');
        }
        $live_data['status'] = 2;
        Live::updateAll($live_data, ['live_id' => $live_id]);
        $this->_successData("更新成功");
    }
    
    /*
     * 推流结束 更新直播状态 为直播 结束
     * */
    public function actionPushStatus()
    {
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : 0; //直播ID
        if (!$live_id) {
            $this->_errorData('0133', "参数错误");
        }
        $compere = Live::getCompere($live_id); //获取直播员信息
        if (!$compere) {
            $this->_errorData('0134', "直播不存在");
        }
        if (!in_array($compere['category'], array(1, 4))) {
            $this->_errorData('0137', "直播类型有误");
        }
        $live_data['status'] = 2;
        Live::updateAll($live_data, ['live_id' => $live_id]);
        $this->_successData("更新成功");
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
        $compere = Live::getCompere($live_id); //获取直播员信息
        if (!$compere) {
            $this->_errorData('0134', "直播不存在");
        }
        if (!in_array($compere['category'], array(3, 4))) {
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
//            "videos_url"=>isset($video_id_arr) ? $video_id_arr : array(),
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
        $live_info = Live::find()->where('live_id = ' . $live_id . ' and status != 0')->asArray()->one();
        if (!$live_info) {
            $this->_errorData('0134', "直播不存在");
        }
        if (!in_array($live_info['category'], array(3, 4))) {
            $this->_errorData('0137', "直播类型有误");
        }
        $returnData = LivePanelManage::Get_TopAndOnList($live_id);
        $this->_successData($returnData, "查选成功");
        die;
//        //查看 图文消息列表
//        $res_list = array();
//        $res_list = LivePanelManage::get_TopMessageList($live_id);
//
//        if(!$res_list){
//            $this->_successData($res_list);
//        }
//        foreach ($res_list as $key=>$val){
//            $res_list[$key]['content'] = stripslashes($this->userTextDecode($val['content']));
//            $json_data = json_decode(stripslashes($val['json_data']));
//            $res_list[$key]['compere_cate']   = isset($json_data->compere_cate) ? $json_data->compere_cate : "主持人"; //直播员类别
//            $res_list[$key]['compere_name']   = isset($json_data->compere_name) ? $json_data->compere_name : "小汇"; //直播员别名
//            $res_list[$key]['compere_avatar'] = isset($json_data->compere_avatar) ? $json_data->compere_avatar : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png"; //直播员头像
//            $res_list[$key]['images_url'] = $json_data->images_url;
//            $res_list[$key]['videos_url'] = $json_data->videos_data;
//            $res_list[$key]['on_list_user_info'] = $json_data->on_list_user_info;
//            unset($res_list[$key]['json_data']);
//        }
//
//        $this->_successData($res_list);
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
        $live_info = Live::find()->where('live_id = ' . $live_id . ' and status != 0')->asArray()->one();
        if (!$live_info) {
            $this->_errorData('0134', "直播不存在");
        }
        if (!in_array($live_info['category'], array(3, 4))) {
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
//            $res_list[$key]['content'] = stripslashes($this->userTextDecode($val['content']));
//            $res_list[$key]['compere_cate']   = isset($json_data->compere_cate) ? $json_data->compere_cate : "主持人"; //直播员类别
//            $res_list[$key]['compere_name']   = isset($json_data->compere_name) ? $json_data->compere_name : "小汇"; //直播员别名
//            $res_list[$key]['compere_avatar'] = isset($json_data->compere_avatar) ? $json_data->compere_avatar : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png"; //直播员头像
//            $res_list[$key]['images_url'] = $json_data->images_url;
//            $res_list[$key]['videos_url'] = $json_data->videos_data;
//            $res_list[$key]['on_list_user_info'] = $json_data->on_list_user_info;
//            unset($res_list[$key]['json_data']);
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
        $live_info = Live::find()->where('live_id = ' . $live_id . ' and status != 0')->asArray()->one();
        if (!$live_info) {
            $this->_errorData('0134', "直播不存在");
        }
        if (!in_array($live_info['category'], array(3, 4))) {
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
//            $res_list[$key]['content'] = stripslashes($this->userTextDecode($val['content']));
//            $json_data = json_decode(stripslashes($val['json_data']));
//            $res_list[$key]['compere_cate']   = isset($json_data->compere_cate) ? $json_data->compere_cate : "主持人"; //直播员类别
//            $res_list[$key]['compere_name']   = isset($json_data->compere_name) ? $json_data->compere_name : "小汇"; //直播员别名
//            $res_list[$key]['compere_avatar'] = isset($json_data->compere_avatar) ? $json_data->compere_avatar : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png"; //直播员头像
//            $res_list[$key]['images_url'] = $json_data->images_url;
//            $res_list[$key]['videos_url'] = $json_data->videos_data;
//            $res_list[$key]['on_list_user_info'] = $json_data->on_list_user_info;
//            unset($res_list[$key]['json_data']);
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
    
    /*
    * 点击创建 录播 选择：实时录制视频--创建直播码
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
        $live_sql = new Live();
        $live_sql['live_id'] = $live_id;
        $live_sql['status'] = 0;
        $live_sql['weight'] = 110;
        $live_sql['category'] = 6;
        $live_sql['is_fast'] = 1;
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
        $live_channel = new LiveChannel();
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
        $channel_id = LiveChannel::find()->where(['channel_name' => $channel_name])
            ->orderBy("create_time desc")->asArray()->one();
        //添加 直播的直播码形式的 推流人
        $live_camera = new LiveCameraAngle();
        $live_camera['live_id'] = $live_id;
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
     * 创建录播 编辑录播
     *
     * */
    public function actionRecordCreate()
    {
        $save_type = isset($this->params['save_type']) ? $this->params['save_type'] : 0; //操作类型，0创建录播，1 编辑录播
        $type = isset($this->params['type']) ? $this->params['type'] : 0; //直播类型，0选用已有视频，1 实时录制视频
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : 0; //直播ID
        $title = isset($this->params['title']) ? $this->params['title'] : ''; //标题
        $cover_img = isset($this->params['cover_img']) ? $this->params['cover_img'] : ''; //封面图
        $rever_url = isset($this->params['rever_url']) ? $this->params['rever_url'] : ''; //视频地址
        $rever_img_url = isset($this->params['rever_img_url']) ? $this->params['rever_img_url'] : ''; //视频地址缩略图
        $start_time = isset($this->params['start_time']) ? $this->params['start_time'] : ''; //直播开始时间
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : 0; //当前管理员ID
        $weight = isset($this->params['weight']) ? $this->params['weight'] : 110; //权重
        $screen = isset($this->params['screen']) ? $this->params['screen'] : 0; //横竖屏
        $column_id = isset($this->params['column_id']) ? $this->params['column_id'] : '';     //栏目ID
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
        $res_live['reviewed_status'] = $reviewed_status;
        if (!$start_time) {
            $this->_errorData('0124', "直播开始时间不能为空");
        }
        $create_time = date('Y-m-d H:i:s');
        //编辑录播
        if ($save_type == 1 && $live_id) {
            if (!in_array("快直播-高级管理-创建/编辑快直播", $role_arr) && !in_array("快直播-录播管理-编辑录播", $role_arr)) {
                $this->_errorData('0339', "暂无此权限");
            }
            $live_check = Live::find()
                ->where('live_id = ' . $live_id)->asArray()->one();
            if (!$live_check) {
                $this->_errorData('0134', "录播不存在");
            }
            if ($live_check['category'] != 6) {
                $this->_errorData('0137', "直播类型有误");
            }
            if ($live_check['reviewed_status'] == 0) {
                $reviewed_status = 0;
            }
            $param['name'] = $title;
            $param['start_time'] = $start_time;
            $param['image_url'] = $cover_img;
            $param['rever_url'] = $rever_url;
            $param['rever_img_url'] = $rever_img_url;
            $param['update_time'] = $create_time;
            $param['creator_id'] = $admin_id;
            $param['screen'] = $screen;
            $param['reviewed_status'] = $reviewed_status;
            $result = Live::updateAll($param, ['live_id' => $live_id]);
            if (!$result) {
                $this->_errorData('0128', "修改直播失败");
            }
            //更新创建关联新闻
            
            $news_data = array(
                //'news_id'      => $news_id_add,
                'title'        => $title,
                'cover_image'  => $cover_img ? $cover_img : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png",
                'app_pub'      => 1,
                'weight'       => 70,
                //'type'         => 15,//新增新闻类型，14.集入口 15.普通快直播
                //'create_time'  => date('Y-m-d H:i:s', time()),
                //'status'       =>
                'refresh_time' => time(),
                'live_id'      => $live_id,
            );
            if ($reviewed_status == 0) {
                $news_data['status'] = 0;
            } else {
                $news_data['status'] = 3;
            }
            News::updateAll($news_data, ['live_id' => $live_id]);
            $this->_successData($res_live);
        }
        if (!in_array("快直播-高级管理-创建/编辑快直播", $role_arr) && !in_array("快直播-录播管理-创建录播", $role_arr)) {
            $this->_errorData('0339', "暂无此权限");
        }
        //创建 录播
        if ($type == 0) {
            $live_id = $this->create_live_id();
        } else {
//            //查看直播码
//            $channel_name = "直播码-".$live_id;
//            $channel_id = LiveChannel::find()->where(['channel_name'=>$channel_name])
//                ->orderBy("create_time desc")->asArray()->one();
//            //添加 直播的直播码形式的 推流人
//            $live_camera = new LiveCameraAngle();
//            $live_camera['live_id']       = $live_id;
//            $live_camera['signal_source'] = 1;
//            $live_camera['name']          = "直播码-".$channel_id['channel_id'];
//            $live_camera['source_id']     = $channel_id['channel_id'];
//            $live_camera['operator_id']   = $admin_id;
//            $live_camera->save();
            //创建 真实直播信息
            $param['name'] = $title;
            $param['status'] = 5;
            $param['start_time'] = $start_time;
            $param['image_url'] = $cover_img;
            $param['create_time'] = $create_time;
            $param['update_time'] = $create_time;
            $param['refresh_time'] = $create_time;
            $param['rever_video_category'] = 1;
            $param['screen'] = $screen;
            $param['reviewed_status'] = $reviewed_status;
            $result_create = Live::updateAll($param, ['live_id' => $live_id]);
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
            //'news_id'      => $news_id_add,
            $news_data['title'] = $title;
            $news_data['cover_image'] = $cover_img ? $cover_img : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
            $news_data['app_pub'] = 1;
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
                $param['news_id'] = $news_data['news_id'];
                Live::updateAll($param, ['live_id' => $live_id]);
            }
//            $live_tag = new LiveTagsRelation();
//            $live_tag['live_id'] = $live_id;
//            $live_tag['tag_id']  = 1;
//            $live_tag['type']    = 1;
//            $live_tag['create_time'] = date('Y-m-d H:i:s', time());
//            $live_tag['creator']     = $admin_id;
//            $live_tag->save();
            // 当超管/高管,或当前用户未配置栏目时,创建的快直播录播自带直播标签，tag_id=2时是“直播"标签
            $without_column = Live::getBackAdminColumnsList($admin_id);
            $user_without_column = 1;
            if (isset($without_column[0]['column_id'])) {
                if ($without_column[0]['column_id']==-200) {
                    $user_without_column = 0;
                }
            }
            if (!AdminRole::findRole($admin_id) || $admin_id == 1 || !$user_without_column) {
                $live_tag = new LiveTagsRelation();
                $live_tag['live_id'] = $live_id;
                $live_tag['tag_id'] = 2;
                $live_tag['type'] = 1;
                $live_tag['create_time'] = date('Y-m-d H:i:s', time());
                $live_tag['creator'] = $admin_id;
                $live_tag->save();
            }
            $this->_successData($res_live);
        }
        $param = new Live();
        $param['category'] = 6;
        $param['status'] = 5;
        $param['is_fast'] = 1;
        $param['live_id'] = $live_id;
        $param['name'] = $title;
        $param['weight'] = $weight;
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
        //'news_id'      => $news_id_add,
        $news_data['title'] = $title;
        $news_data['cover_image'] = $cover_img ? $cover_img : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
        $news_data['app_pub'] = 1;
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
            $param['news_id'] = $news_data['news_id'];
            $param->save();
        }
        
//        $res_live['work_time'] = $work_time;
//        $live_tag = new LiveTagsRelation();
//        $live_tag['live_id'] = $live_id;
//        $live_tag['tag_id']  = 1;
//        $live_tag['type']    = 1;
//        $live_tag['create_time'] = date('Y-m-d H:i:s', time());
//        $live_tag['creator']     = $admin_id;
//        $live_tag-
        // 当超管/高管,或当前用户未配置栏目时,创建的快直播录播自带直播标签，tag_id=2时是“直播"标签
        $without_column = Live::getBackAdminColumnsList($admin_id);
        $user_without_column = 1;
        if (isset($without_column[0]['column_id'])) {
            if ($without_column[0]['column_id']==-200) {
                $user_without_column = 0;
            }
        }
       
        if (!AdminRole::findRole($admin_id) || $admin_id == 1 || !$user_without_column) {
            $live_tag = new LiveTagsRelation();
            $live_tag['live_id'] = $live_id;
            $live_tag['tag_id'] = 2;
            $live_tag['type'] = 1;
            $live_tag['create_time'] = date('Y-m-d H:i:s', time());
            $live_tag['creator'] = $admin_id;
            $live_tag->save();
        }
        $this->_successData($res_live);
    }
    
    /*
     * 录播详情
     *
     * */
    public function actionRecordInfo()
    {
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : 0; //直播ID
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : 0; //当前管理员ID
        if (!$live_id) {
            $this->_errorData('0133', "直播ID不能为空");
        }
        if (!$admin_id) {
            $this->_errorData('0123', "当前管理员ID 不能为空");
        }
        //查看录播是否存在
        $live_check = Live::find()->with(['news' => function ($q) {  $q->select('column_id,area_id'); },
                                          'news.column' => function ($q) { $q->select('column_id,name');},
                                          'news.area'   => function ($q) { $q->select('area_id,name'); }])
            ->select("live_id,name,image_url,rever_url,rever_img_url,start_time,creator_id,screen,reviewed_status,amendments")
            ->where('live_id = ' . $live_id)->asArray()->one();
        if (!$live_check) {
            $this->_errorData('0134', "录播不存在");
        }
        if ($admin_id != $live_check['creator_id']) {
            $this->_errorData('0136', '只有 直播创建者可以编辑直播');
        }
        //$live_check['news_column'] = [];
        if ($live_check['news']['column_id']) {
            $live_check['column_id'] = $live_check['news']['column_id'];
            $live_check['column_type'] = '0';
        }
        if ($live_check['news']['area']) {
            $live_check['column_id'] = $live_check['news']['area_id'];
            $live_check['column_type'] = '1';
        
        }
        unset($live_check['news']);
        //查看是否有推流信息 返回类型，已有视频0/实时录制1
        $live_check['live_type'] = 0;
        $live_creator = LiveCameraAngle::find()->where(['live_id' => $live_id])->one();
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
        $record_lists = Live::getRecordList($admin_id, $page, $count);
        foreach ($record_lists as $key => $val) {
            //查看是否有推流信息 返回类型，已有视频0/实时录制1
            $record_lists[$key]['live_type'] = 0;
            $live_creator = LiveCameraAngle::find()->where(['live_id' => $val['live_id']])->one();
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
        $live_info = Live::find()->where('live_id = ' . $live_id)->asArray()->one();
        if (!$live_info) {
            $this->_errorData('0134', "录播不存在");
        }
        $result = Live::deleteAll(['live_id' => $live_id]);
        if (!$result) {
            $this->_errorData('0138', "删除失败");
        }
        $this->_successData("删除成功");
    }
    
    
    /*
     * 直播（视频、录播 快直播类型） 点赞
     *
     * */
    public function actionLivePraise()
    {
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : 0; //直播ID
        $user_id = isset($this->params['user_id']) ? $this->params['user_id'] : 0; //用户ID
        $praises = isset($this->params['praises']) ? $this->params['praises'] : 0; //点赞次数
        if (!$live_id || !$user_id || !$praises) {
            $this->_errorData('0133', "参数错误");
        }
        //查看直播是否存在
        $live_info = Live::find()->where('live_id = ' . $live_id)->asArray()->one();
        if (!$live_info) {
            $this->_errorData('0134', "直播不存在");
        }
        $user_info = User::find()->where('user_id=' . $user_id . ' and status = 1')->one();
        if (!$user_info) {
            $this->_errorData('0011', "用户不存在");
        }
        if ($live_info['is_fast'] != 1 || !in_array($live_info['category'], array(1, 6))) {
            $this->_errorData('0137', "直播类型有误");
        }
        $praise_info = NewsPraise::find()->where(array('news_id' => $live_id, 'user_id' => $user_id, 'news_type' => 0))->one();
        if ($praise_info) {
            $praise_info->live_count = $praise_info->live_count + $praises;
            $ret = $praise_info->update();
            if ($ret <= 0 || $ret === false) $this->_errorData(1007, '点赞失败');
            $this->_successData('点赞成功');
        } else {
            $praise_model = new NewsPraise();
            $praise_model->news_id = $live_id;
            $praise_model->user_id = $user_id;
            $praise_model->news_type = 0;
            $praise_model->status = 1;
            $praise_model->create_time = time();
            $praise_model->live_count = $praises;
            $ret = $praise_model->save();
            if (!$ret) $this->_errorData(1003, '点赞失败');
            $this->_successData('点赞成功');
        }
        
    }
    
    
    //创建 直播ID
    private function create_live_id()
    {
        return date('Y') . time() . rand(0000, 9999);
    }
    
    /**
     * 创建融云图文直播聊天室
     *
     * @param $live_id
     *
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
    
    /**
     * 创建融云聊天室
     *
     * @param $live_id
     *
     * @return mixed
     */
    public function create_rcloud_chroom($live_id)
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
        $data = 'chatroom[room_' . $live_id . ']=n' . $live_id;
        $result = $this->curl_http(Yii::$app->params['ryApiUrl'] . '/chatroom/create.json', $data, $header);
        
        return $result['code'];
    }
    
    
}
