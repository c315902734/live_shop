<?php

namespace backend\controllers;

use common\models\AdminRole;
use common\models\LiveTagsRelation;
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
use Faker\Test\Provider\PaymentTest;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * LiveBack controller
 */
class LiveBackController extends PublicBaseController
{
    /*
     * 创建直播
     *
     * */
    public function actionLiveCreate()
    {
        $type = isset($this->params['type']) ? $this->params['type'] : ''; //直播类型 1视频直播，3图文直播，4视频加图文直播
        $name = isset($this->params['name']) ? $this->params['name'] : ''; //标题  有默认标题
        $weight = isset($this->params['weight']) ? $this->params['weight'] : 110; //权重
        $start_time = isset($this->params['start_time']) ? $this->params['start_time'] : ''; //直播开始时间
        $image_url = isset($this->params['image_url']) ? $this->params['image_url'] : ''; //封面图
        $introduction_status = isset($this->params['introduction_status']) ? $this->params['introduction_status'] : ''; //直播结束 	0未选中，1选中
        $title = isset($this->params['title']) ? $this->params['title'] : ''; //标题
        $introduction = isset($this->params['introduction']) ? $this->params['introduction'] : ''; //详情
        //直播未开始浏览数随机设置 - 开始
        $no_start_view_ranage_from = isset($this->params['no_start_view_ranage_from']) ? $this->params['no_start_view_ranage_from'] : 0;
        //直播未开始浏览数随机设置 - 结束
        $no_start_view_ranage_to = isset($this->params['no_start_view_ranage_to']) ? $this->params['no_start_view_ranage_to'] : 0;
        //直播进行中浏览数随机设置 - 开始
        $loading_view_ranage_from = isset($this->params['loading_view_ranage_from']) ? $this->params['loading_view_ranage_from'] : 0;
        //直播进行中浏览数随机设置 - 结束
        $loading_view_ranage_to = isset($this->params['loading_view_ranage_to']) ? $this->params['loading_view_ranage_to'] : 0;
        //直播结束浏览数随机设置 - 开始
        $end_view_ranage_from = isset($this->params['end_view_ranage_from']) ? $this->params['end_view_ranage_from'] : 0;
        //直播结束浏览数随机设置 - 结束
        $end_view_ranage_to = isset($this->params['end_view_ranage_to']) ? $this->params['end_view_ranage_to'] : 0;
        $live_man_avatar_url = isset($this->params['live_man_avatar_url']) ? $this->params['live_man_avatar_url'] : ''; //直播员属性–头像
        $live_man_cate = isset($this->params['live_man_cate']) ? $this->params['live_man_cate'] : ''; //直播员属性–类别
        $live_man_alias = isset($this->params['live_man_alias']) ? $this->params['live_man_alias'] : ''; //直播员属性–别名
        $remark = isset($this->params['remark']) ? $this->params['remark'] : 0;   //直播备忘
        $screen = isset($this->params['screen']) ? $this->params['screen'] : 0;   //画面方向，0横屏，1竖屏
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : ''; //当前管理员ID
        $operator_id = isset($this->params['operator_id']) ? $this->params['operator_id'] : '';   //直播推流业务员ID，只能一个
        $operator_ids = isset($this->params['operator_ids']) ? $this->params['operator_ids'] : ''; //图文直播 业务员ID，最多四个 多个用逗号隔开‘，’
        $column_id = isset($this->params["column_id"]) ? $this->params["column_id"] : '';
        $column_type = isset($this->params["column_type"]) ? $this->params["column_type"] : '0';
        if (!$column_id) {
            $this->_errorData('0121', '请确认所属栏目');
        }
        if (!$type) {
            $this->_errorData('0121', "直播类型不能为空");
        }
        if (!$image_url) {
            $image_url = "http://vrlive-10047449.image.myqcloud.com/lv1500016289morentu.png";
        }
        if (!$admin_id) {
            $this->_errorData('0123', "当前管理员ID 不能为空");
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
        $view_ranage = $no_start_view_ranage_from . '-' . $no_start_view_ranage_to . '|' . $loading_view_ranage_from . '-' . $loading_view_ranage_to . '|' . $end_view_ranage_from . '-' . $end_view_ranage_to;
        $live_id = $this->create_live_id();
        //查看 当前管理员 的角色 超管或高管 可以直接发布，普通人员 创建为 待审核
        $reviewed_status = 1;
        if ($admin_id == 1 ) {
            $reviewed_status = 0;
        } else {
            $reviewed_status = AdminRole::findRole($admin_id);
        }
        
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
            $param['name'] = $name;
            $param['weight'] = $weight;
            $param['start_time'] = $start_time;
            $param['image_url'] = $image_url;
            $param['create_time'] = $create_time;
            $param['update_time'] = $create_time;
            $param['refresh_time'] = $create_time;
            $param['creator_id'] = $admin_id;
            $param['introduction_status'] = $introduction_status;
            $param['title'] = $title;
            $param['introduction'] = $introduction;
            $param['is_fast'] = 1;
            $param['status'] = 1;
            $param['screen'] = $screen;
            $param['live_man_avatar_url'] = $live_man_avatar_url ? $live_man_avatar_url : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
            $param['live_man_cate'] = $live_man_cate;
            $param['live_man_alias'] = $live_man_alias;
            $param['remark'] = $remark;
            $param['view_ranage'] = $view_ranage;
            $param['reviewed_status'] = $reviewed_status;
            $result = $param->save();
            if (!$result) {
                $this->_errorData('0128', "保存直播失败");
            }
            // 快直播创建成果后，创建管理关联新闻，状态为待审核
            $news_data = new News();
            //'news_id'      => $news_id_add,
            $news_data['title'] = $name;
            $news_data['cover_image'] = $image_url ? $image_url : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
            $news_data['app_pub'] = 1;
            $news_data['weight'] = 70;
            $news_data['type'] = 15;//新增新闻类型，17.集入口 15.普通快直播
            $news_data['create_time'] = date('Y-m-d H:i:s', time());
            $news_data['refresh_time'] = time();
            $news_data['live_id'] = $live_id;
            if($reviewed_status ==0){
                $news_data['status'] = 0;//   新闻状态，0 已发布，1草稿，2定时发布,3待审核
            }else{
                $news_data['status'] = 3;//   新闻状态，0 已发布，1草稿，2定时发布,3待审核
            }
            
            if ($column_type) {
                $news_data['area_id'] = $column_id;
            } else {
                $news_data['column_id'] = $column_id;
            }
            $insert_news = $news_data->save();
            //快直播关联新闻,创建成果后更新直播关联新闻id;
            if ($insert_news) {
                $param->news_id = $news_data->news_id;
                $param->update();
            }
//            $live_tag = new LiveTagsRelation();
//            $live_tag['live_id'] = $live_id;
//            $live_tag['tag_id']  = 1;
//            $live_tag['type']    = 1;
//            $live_tag['create_time'] = date('Y-m-d H:i:s', time());
//            $live_tag['creator']     = $admin_id;
//            $live_tag->save();
           // 当超管/高管时,创建的快直播自带直播标签，tag_id=2时是“直播"标签
            if(!AdminRole::findRole($admin_id) || $admin_id == 1) {
                $live_tag = new LiveTagsRelation();
                $live_tag['live_id'] = $live_id;
                $live_tag['tag_id'] = 2;
                $live_tag['type'] = 1;
                $live_tag['create_time'] = date('Y-m-d H:i:s', time());
                $live_tag['creator'] = $admin_id;
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
            $live_channel['manager'] = $admin_id;
            $live_channel['status'] = 1;
            $live_channel['push_url'] = $push_url;
            $live_channel['pull_url'] = $pull_url;
            $live_channel['create_time'] = date("Y-m-d H:i:s");
            $live_channel['creator_id'] = $admin_id;
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
//                print_r($live_camera->getErrors());die;
        }
        $res_work = array();
        $h = intval(date("H"));
        $work_time = 1;
        if ($h >= 9 || $h <= 21) {
            $work_time = 0;
            $res_work['work_time'] = $work_time;
            $this->_successData($res_work);
        } else {
            $this->_errorData('0129', '创建融云聊天室失败，请联系系统管理员！');
        }
    }
    
    /*
     * 编辑直播
     *
     * */
    public function actionEdit()
    {
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : ''; //直播ID
        $type = isset($this->params['type']) ? $this->params['type'] : ''; //直播类型 1视频直播，3图文直播，4视频加图文直播
        $name = isset($this->params['name']) ? $this->params['name'] : ''; //标题  有默认标题
        $weight = isset($this->params['weight']) ? $this->params['weight'] : 110; //权重
        $start_time = isset($this->params['start_time']) ? $this->params['start_time'] : ''; //直播开始时间
        $image_url = isset($this->params['image_url']) ? $this->params['image_url'] : ''; //封面图
        $introduction_status = isset($this->params['introduction_status']) ? $this->params['introduction_status'] : ''; //直播结束 	0未选中，1选中
        $title = isset($this->params['title']) ? $this->params['title'] : ''; //标题
        $introduction = isset($this->params['introduction']) ? $this->params['introduction'] : ''; //详情
        //直播未开始浏览数随机设置 - 开始
        $no_start_view_ranage_from = isset($this->params['no_start_view_ranage_from']) ? $this->params['no_start_view_ranage_from'] : 0;
        //直播未开始浏览数随机设置 - 结束
        $no_start_view_ranage_to = isset($this->params['no_start_view_ranage_to']) ? $this->params['no_start_view_ranage_to'] : 0;
        //直播进行中浏览数随机设置 - 开始
        $loading_view_ranage_from = isset($this->params['loading_view_ranage_from']) ? $this->params['loading_view_ranage_from'] : 0;
        //直播进行中浏览数随机设置 - 结束
        $loading_view_ranage_to = isset($this->params['loading_view_ranage_to']) ? $this->params['loading_view_ranage_to'] : 0;
        //直播结束浏览数随机设置 - 开始
        $end_view_ranage_from = isset($this->params['end_view_ranage_from']) ? $this->params['end_view_ranage_from'] : 0;
        //直播结束浏览数随机设置 - 结束
        $end_view_ranage_to = isset($this->params['end_view_ranage_to']) ? $this->params['end_view_ranage_to'] : 0;
        $live_man_avatar_url = isset($this->params['live_man_avatar_url']) ? $this->params['live_man_avatar_url'] : ''; //直播员属性–头像
        $live_man_cate = isset($this->params['live_man_cate']) ? $this->params['live_man_cate'] : ''; //直播员属性–类别
        $live_man_alias = isset($this->params['live_man_alias']) ? $this->params['live_man_alias'] : ''; //直播员属性–别名
        $remark = isset($this->params['remark']) ? $this->params['remark'] : 0;   //直播备忘
        $screen = isset($this->params['screen']) ? $this->params['screen'] : '';   //画面方向，0横屏，1竖屏
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : ''; //当前管理员ID
        $operator_id = isset($this->params['operator_id']) ? $this->params['operator_id'] : '';   //直播推流业务员ID，只能一个
        $operator_ids = isset($this->params['operator_ids']) ? $this->params['operator_ids'] : ''; //图文直播 业务员ID，最多四个 多个用逗号隔开‘，’
        $rever_url = isset($this->params['rever_url']) ? $this->params['rever_url'] : ''; //回顾视频地址
        if (!$live_id) {
            $this->_errorData('0133', "直播ID不能为空");
        }
        if (!$admin_id) {
            $this->_errorData('0123', "当前管理员ID 不能为空");
        }
        if ($admin_id != 1) {
            //查看权限
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
            if (!in_array("快直播-直播管理-编辑快直播", $role_arr) && !in_array("快直播-高级管理-创建/编辑快直播", $role_arr)) {
                $this->_errorData('0339', "暂无此权限");
            }
        }
        //查看 当前管理员 的角色 超管或高管 可以直接发布，普通人员 创建为 待审核
        $reviewed_status = 1;
        $news_wait = 3;
        if ($admin_id == 1 ) {
            $reviewed_status = 0;
            $news_wait = $reviewed_status;
        } else {
            $reviewed_status = AdminRole::findRole($admin_id);
            if(!$reviewed_status){
                $news_wait = $reviewed_status;
            }
        }
        //查看直播是否存在
        $live_info = Live::find()->with('news')->where('live_id = ' . $live_id)->asArray()->one();
        if (!$live_info) {
            $this->_errorData('0134', "直播不存在");
        }
//        if(!in_array($admin_id ,array($live_info['creator_id'],1))){
//            $this->_errorData('0136', '只有 直播创建者或超级管理员可以编辑直播');
//        }
        if (!$type) {
            $this->_errorData('0121', "直播类型不能为空");
        }
        if (!$image_url) {
            $image_url = "http://vrlive-10047449.image.myqcloud.com/lv1500016289morentu.png";
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
        $view_ranage = $no_start_view_ranage_from . '-' . $no_start_view_ranage_to . '|' . $loading_view_ranage_from . '-' . $loading_view_ranage_to . '|' . $end_view_ranage_from . '-' . $end_view_ranage_to;
        $create_time = date('Y-m-d H:i:s');
        if (!$start_time || empty($start_time)) {
            $start_time = $create_time;
        }
        //添加视频回顾地址
        if (in_array($live_info['status'], array(2, 5)) && !empty($rever_url)) {
            if ($live_info['status'] == 2) {
                $param['status'] = 5;
            }
            $param['rever_url'] = $rever_url;
        }
        $param['category'] = $type;
        $param['name'] = $name;
        $param['weight'] = $weight;
        $param['start_time'] = $start_time;
        $param['image_url'] = $image_url;
        $param['update_time'] = $create_time;
        $param['introduction_status'] = $introduction_status;
        $param['title'] = $title;
        $param['introduction'] = $introduction;
        $param['is_fast'] = 1;
        $param['screen'] = $screen;
        $param['live_man_avatar_url'] = $live_man_avatar_url ? $live_man_avatar_url : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
        $param['live_man_cate'] = $live_man_cate;
        $param['live_man_alias'] = $live_man_alias;
        $param['remark'] = $remark;
        $param['view_ranage'] = $view_ranage;
        $param['reviewed_status'] = $reviewed_status;
        Live::updateAll($param, ['live_id' => $live_id]);
        $news_data = array(
            //'news_id'      => $news_id_add,
            'title'        => $name,
            'cover_image'  => $image_url ? $image_url : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png",
            'app_pub'      => 1,
            'weight'       => $weight,
            //'type'         => 15,//新增新闻类型，14.集入口 15.普通快直播
            //'create_time'  => date('Y-m-d H:i:s', time()),
            'refresh_time' => time(),
            'live_id'      => $live_id,
            'status'       => $news_wait
        );
        News::updateAll($news_data, ['live_id' => $live_id]);
//
//        if (!$result) {
//            $this->_errorData('0128', "修改直播失败");
//        }
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
                    $livecode = $bizId . "_" . $streamId; //直播码
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
                    $live_channel['creator_id'] = $admin_id;
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
        $res_work = array();
        $h = intval(date("H"));
        $work_time = 1;
        if ($h >= 9 || $h <= 21) {
            $work_time = 0;
        }
        $res_work['work_time'] = $work_time;
        $this->_successData($res_work);
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
    
    /**
     * 后台直播列表
     */
    public function actionLiveList()
    {
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';  //当前登录管理员ID
        $name = isset($this->params['name']) ? $this->params['name'] : '';      //直播名称
        $type = isset($this->params['type']) ? $this->params['type'] : '';      //直播类型 1视频直播，3图文直播，4视频加图文直播, 6录播, 7视频直播+视频加图文直播
        $status = isset($this->params['status']) ? $this->params['status'] : '';  //直播状态 2 已结束 3 未开始 4 直播中 5 直播回顾
        $page = isset($this->params['page']) ? $this->params['page'] : '1';     //当前页数
        $count = isset($this->params['count']) ? $this->params['count'] : '20';    //每页最多显示数量
        $column_id = isset($this->params['column_id']) ? $this->params['column_id'] : '';     //栏目ID
        $column_type = isset($this->params['column_type']) ? $this->params['column_type'] : '0';    //栏目分类
        if (!$admin_id) {
            $this->_errorData('0101', '参数错误');
        }
        if (!$column_id && $column_type) {
            $this->_errorData('0199', "请输入栏目ID和栏目类型Id");
        }
        if (!$column_id && $column_type) {
            $this->_errorData('0199', "请输入栏目ID和栏目类型Id");
        }
        //查看 缓存数据
        $redis = Yii::$app->cache;
        $red_admin = Yii::$app->params['environment'] . '_admin_role_' . $admin_id;
        $redis_info = $redis->get($red_admin);
        //$redis_info = 0;
        if (!$redis_info) {
            //如 无缓存 查看数据
            $red_list = AdminRole::getAdminRole($admin_id);
        } else {
            $red_list = $redis_info;
        }
        $role_arr = array_column($red_list, "action_name");
        $gao_admin = 0;//高管
        if ($type != 7 && !in_array("快直播-高级管理-查看全部快直播列表", $role_arr)) {
            $gao_admin = 1;
        }

        if ($type == 7 && (!in_array("快直播-监控-访问直播监控台", $role_arr) && !in_array("快直播-高级管理-查看全部快直播列表", $role_arr))) {
            $gao_admin = 1;
        }
        $list = Live::getBackLiveList($admin_id, $name, $type, $status, $page, $count, $gao_admin, $column_id, $column_type);
        $this->_successData($list);
    }
    
    /**
     * 获取后台直播详情
     */
    public function actionLiveInfo()
    {
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';  //当前登录管理员ID
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : '';  //直播ID
        if (!$admin_id || !$live_id) {
            $this->_errorData('0101', '参数错误');
        }
        $live_info = Live::find()->where(['live_id' => $live_id])->with(['news' => function ($q) {
            $q->select('news_id,column_id,area_id,type_id');
        }, 'news.column'                                                        => function ($q) {
            $q->select('column_id,name');
        }, 'news.area'                                                          => function ($q) {
            $q->select('area_id,name');
        }])->select("live_id,category,name,title,weight,image_url,start_time,introduction_status,introduction,screen,creator_id,status,view_ranage,introduction,remark,live_man_cate,live_man_alias,live_man_avatar_url,reviewed_status,amendments,rever_url")->asArray()->one();
        if (!$live_info) {
            $this->_errorData('0102', '直播不存在');
        }
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
        $gao_admin = 0;//高管
        if (!in_array("快直播-高级管理-查看全部快直播列表", $role_arr) && !in_array("快直播-审核-访问待审核的快直播列表", $role_arr)) {
            $gao_admin = 1;
        }
        if ($admin_id != 1 && $gao_admin != 0) {
            $live_info = Live::find()->where(['live_id' => $live_id])->with(['news' => function ($q) {
                $q->select('news_id,column_id,area_id,type_id');
            }, 'news.column'                                                                                     => function ($q) {
                $q->select('column_id,name');
            }, 'news.area'                                                                                       => function ($q) {
                $q->select('area_id,name');
            }])->select("live_id,category,name,title,weight,image_url,start_time,introduction_status,introduction,screen,creator_id,status,view_ranage,introduction,remark,live_man_cate,live_man_alias,live_man_avatar_url,reviewed_status,amendments,rever_url")->asArray()->one();
            if (!$live_info) {
                $this->_errorData('0102', '无权查看该直播');
            }
        }
        //返回 直播创建者 名字
        $admin_live = AdminUser::find()->where(["admin_id" => $live_info['creator_id']])->asArray()->one();
        $admin_live = $admin_info = AdminUser::find()->alias('a')->leftJoin('vrnews1.company c', 'a.company_id = c.company_id')->select("a.username, a.real_name,c.name as company_name")->where(['admin_id' => $live_info['creator_id']])->asArray()->one();
        $live_info['creator_name'] = $admin_live['real_name'];
        $live_info['company_name'] = $admin_live['company_name'];
        $live_info['operator_ids'] = LiveManager::find()->where(['live_id' => $live_id])->asArray()->all();
        $live_info['operator_id'] = LiveCameraAngle::find()->alias('c')->leftJoin('vradmin1.admin_user a', 'a.admin_id = c.operator_id')->where(['live_id' => $live_id])->select("c.*,a.username,a.real_name")->asArray()->one();
        //判断 当前管理员是什么角色
        $live_info['admin_operator'] = 0; //都未选中
        
        if ($live_info['news']['column_id']) {
            $live_info['news']['column_name'] = $live_info['news']['column']['name']; //栏目名
        }
        if ($live_info['news']['area_id']) {
            $live_info['news']['area_name'] = $live_info['news']['area']['name']; //地区栏目名
        }
        unset($live_info['news']['column']);
        unset($live_info['news']['area']);
        if ($live_info['operator_id']['operator_id'] == $live_info['creator_id']) {
            $live_info['admin_operator'] = 1; //推流业务员
        }
        //图文 业务员
        if ($live_info['operator_ids']) {
            foreach ($live_info['operator_ids'] as $key => $val) {
                if ($val['admin_id'] == $live_info['creator_id']) {
                    $live_info['admin_operator'] = 2; //图文 业务员
                    break;
                }
            }
        }
        if ($live_info['view_ranage']) {
            $range_arr = explode('|', $live_info['view_ranage']);
            if (is_array($range_arr)) {
                $no_start = explode('-', $range_arr[0]);
                if (is_array($no_start)) {
                    $live_info['no_start_view_ranage_from'] = $no_start[0];
                    $live_info['no_start_view_ranage_to'] = $no_start[1];
                }
                $loading = explode('-', $range_arr[1]);
                if (is_array($loading)) {
                    $live_info['loading_view_ranage_from'] = $loading[0];
                    $live_info['loading_view_ranage_to'] = $loading[1];
                }
                $end_view = explode('-', $range_arr[2]);
                if (is_array($end_view)) {
                    $live_info['end_view_ranage_from'] = $end_view[0];
                    $live_info['end_view_ranage_to'] = $end_view[1];
                }
            }
        }
        $this->_successData($live_info);
    }
    
    /**
     * 后台删除直播
     */
    public function actionDelLive()
    {
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';  //当前登录管理员ID
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : '';  //直播ID
        if (!$admin_id || !$live_id) {
            $this->_errorData('0101', '参数错误');
        }
        $live_info = Live::findOne($live_id);
        if (!$live_info) {
            $this->_errorData('0102', '直播不存在');
        }
        if ($admin_id != 1) {
            //查看权限
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
            if (!in_array("快直播-高级管理-删除直播", $role_arr) && !in_array("快直播-录播管理-删除录播", $role_arr) && !in_array("快直播-直播管理-删除快直播", $role_arr)) {
                $this->_errorData('0339', "暂无此权限");
            }
            
        }
        if ($live_info->status == '4') {
            $this->_errorData('0103', '直播正在进行中，不可以删除');
        }
        $live_info->status = 0;
        if ($live_info->save()) {
            //$news状态改为草稿状态
            $news_data = News::find()->where("live_id = " . $live_id)->one();
            $news_data->status = 1; //草稿状态
            $news_data->update();
            $this->_successData('删除成功');
        } else {
            $this->_errorData('0113', '删除失败');
        }
    }
    
    /*
     * 快直播 待审核列表
     *
     * */
    public function actionWaitList()
    {
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';  //当前登录管理员ID
        $name = isset($this->params['name']) ? $this->params['name'] : '';      //直播名称
        $type = isset($this->params['type']) ? $this->params['type'] : '';      //直播类型 1视频直播，3图文直播，4视频加图文直播, 6录播
        $page = isset($this->params['page']) ? $this->params['page'] : '1';     //当前页数
        $count = isset($this->params['count']) ? $this->params['count'] : '20';    //每页最多显示数量
        $column_id = isset($this->params['column_id']) ? $this->params['column_id'] : '';     //栏目ID
        $column_type = isset($this->params['column_type']) ? $this->params['column_type'] : '0';    //普通栏目为0，本地栏目为1，默认为0
        /*if(!$column_id){
            $this->_errorData('8001', '栏目ID不能为空');
        }*/
        if (!$admin_id) {
            $this->_errorData('0101', '参数错误');
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
            $admin_wait = 1;//高管
            if (!in_array("快直播-高级管理-查看全部快直播列表", $role_arr) && !in_array("快直播-审核-审核快直播", $role_arr)) {
                $admin_wait = 0;
            }
        } else {
            $admin_wait = 1;
        }
        $list = Live::getBackWaitList($admin_id, $name, $type, $page, $count, $admin_wait, $column_id, $column_type);
        $this->_successData($list);
    }
    
    /*
     * 快直播 审核未通过列表
     *
     * */
    public function actionNopassList()
    {
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';  //当前登录管理员ID
        $name = isset($this->params['name']) ? $this->params['name'] : '';      //直播名称
        $type = isset($this->params['type']) ? $this->params['type'] : '';      //直播类型 1视频直播，3图文直播，4视频加图文直播, 6录播
        $page = isset($this->params['page']) ? $this->params['page'] : '1';     //当前页数
        $count = isset($this->params['count']) ? $this->params['count'] : '20';    //每页最多显示数量
        $column_id = isset($this->params['column_id']) ? $this->params['column_id'] : '';     //栏目ID
        $column_type = isset($this->params['column_type']) ? $this->params['column_type'] : '0';    //普通栏目为0，本地栏目为1，默认为0
        if (!$admin_id) {
            $this->_errorData('0101', '参数错误');
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
            $admin_wait = 1;//高管
            if (!in_array("快直播-高级管理-查看全部快直播列表", $role_arr) && !in_array("快直播-审核-审核快直播", $role_arr)) {
                $admin_wait = 0;
            }
        } else {
            $admin_wait = 1;
        }
        $list = Live::getBackNopassList($admin_id, $name, $type, $page, $count, $admin_wait, $column_id, $column_type);
        $this->_successData($list);
    }
    
    /*
     * 审核
     *
     * */
    public function actionReviewed()
    {
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : ''; //当前登录管理员ID
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : '';  //直播ID
        $status = isset($this->params['status']) ? $this->params['status'] : '';    //审核结果，1通过 2审核不通过
        $amendments = isset($this->params['amendments']) ? $this->params['amendments'] : ''; //修改意见
        if (!$admin_id || !$live_id || !in_array($status, array(1, 2))) {
            $this->_errorData('0101', '参数错误');
        }
        $live_info = Live::findOne($live_id);
        if (!$live_info) {
            $this->_errorData('0102', '直播不存在');
        }
        //查看 直播创建者 推送ID
        $admin_regid = AdminUser::find()
            ->where(['admin_id' => $live_info['creator_id']])
            ->select("registration_id")
            ->asArray()->one();
//        $redis = Yii::$app->cache;
//        $redis->delete('live_reviewed_list');die;
//        $up_redis = $redis->get('live_reviewed_list');
//print_r($up_redis);die;
        if ($status == 1) {
            $re_status = 0;
        } else {
            $re_status = $status;
        }
        if ($status == 2 || !empty($live_info->amendments)) {
            $live_info->amendments = $amendments;
        }
        $live_info->reviewed_status = $re_status;
        if ($live_info->save()) {
            $redis = Yii::$app->cache;
            $redis_res = array();
            $category = ($live_info['category'] == 6) ? 1 : 0;
            if ($status == 1) {
                //查看 指派的业务员
                $operator_ids = LiveManager::find()->alias('b')
                    ->leftJoin('vradmin1.admin_user a', 'a.admin_id = b.admin_id')
                    ->where(['live_id' => $live_id])
                    ->andWhere("a.admin_id != " . $admin_id)
                    ->andWhere("a.registration_id is not null and registration_id <> ''")
                    ->select("a.admin_id,a.registration_id")
                    ->asArray()->all();
                $operator_id = LiveCameraAngle::find()->alias('c')
                    ->leftJoin('vradmin1.admin_user a', 'a.admin_id = c.operator_id')
                    ->where(['live_id' => $live_id])
                    ->andWhere("admin_id != " . $admin_id)
                    ->andWhere("a.registration_id != null and registration_id <> ''")
                    ->select("a.admin_id,a.registration_id")
                    ->asArray()->one();
                //审核通过 推送消息
                $start_date = date("Y年m月d日 H时", strtotime($live_info['start_time']));
                $operator_title = "指派给您的快直播《" . $live_info['name'] . "》将于" . $start_date . "开始，请按时进入任务";
                $k = 0;
                if (!empty($admin_regid['registration_id'])) {
                    $redis_res[$live_id][0]['create_title'] = "《" . $live_info['name'] . "》" . "已经审核通过，立即查看";
                    //记录 创建者推送信息
                    $redis_res[$live_id][0]['admin_id'] = $live_info['creator_id'];
                    $redis_res[$live_id][0]['registration_id'] = $admin_regid['registration_id'];
                    $redis_res[$live_id][0]['category'] = $category;
                    $k = 1;
                }
                if (!empty($operator_ids)) {
                    foreach ($operator_ids as $key => $val) {
                        if ($admin_regid['registration_id']) {
                            $key = $key + 1;
                        }
                        $redis_res[$live_id][$key]['admin_id'] = $val['admin_id'];
                        $redis_res[$live_id][$key]['create_title'] = $operator_title;
                        $redis_res[$live_id][$key]['registration_id'] = $val['registration_id'];
                        $redis_res[$live_id][$key]['category'] = $category;
                        ++$k;
                    }
                }
                if (!empty($operator_id) && !empty($operator_id['registration_id'])) {
                    $redis_res[$live_id][$k]['admin_id'] = $operator_id['admin_id'];
                    $redis_res[$live_id][$k]['create_title'] = $operator_title;
                    $redis_res[$live_id][$k]['registration_id'] = $operator_id['registration_id'];
                    $redis_res[$live_id][$k]['category'] = $category;
                }
                $up_redis = $redis->get(Yii::$app->params['environment'] . 'live_reviewed_list');
                if ($up_redis) {
                    $up_redis[] = $redis_res;
                    $redis_res = $up_redis;
//                   $redis_res = array_merge($up_redis,$redis_res);
                } else {
                    $now_redis[] = $redis_res;
                    $redis_res = $now_redis;
                }
                if (!empty($redis_res)) {
                    $redis->set(Yii::$app->params['environment'] . 'live_reviewed_list', $redis_res);
                }
                $red_redis = $redis->get(Yii::$app->params['environment'] . 'live_reviewed_list');
//                print_r($red_redis);die;
                // 快直播审核通过后，对应新闻状态更新为发布
                $news_data = News::find()->where(['live_id' => $live_id])->one();
                $news_data->status = 0;//   新闻状态，0 已发布，1草稿，2定时发布,3待审核4.审核未通过
                $news_data->save();
                
            } else {
                $create_title = "《" . $live_info['name'] . "》" . "未审核通过，立即查看";
                if ($admin_regid['registration_id']) {
                    $redis_res[$live_id][0]['create_title'] = $create_title;
                    //记录 创建者推送信息
                    $redis_res[$live_id][0]['admin_id'] = $live_info['creator_id'];
                    $redis_res[$live_id][0]['registration_id'] = $admin_regid['registration_id'];
                    $redis_res[$live_id][0]['category'] = $category;
                }
                $up_redis = $redis->get(Yii::$app->params['environment'] . 'live_reviewed_list');
                if ($up_redis) {
                    $up_redis[] = $redis_res;
                    $redis_res = $up_redis;
//                    $redis_res = array_merge($redis_res,$up_redis);
                } else {
                    $now_redis[] = $redis_res;
                    $redis_res = $now_redis;
                }
                if (!empty($redis_res)) {
                    $redis->set(Yii::$app->params['environment'] . 'live_reviewed_list', $redis_res);
                }
//                $red_redis = $redis->get('live_reviewed_list');
//                print_r($red_redis);die;
            }
            if ($status == 2) {
                $news_data = News::find()->where(['live_id' => $live_id])->one();
                $news_data->status = 4;//审核未通过
                $news_data->save();
                
            }
            $this->_successData('审核成功');
        } else {
            $this->_errorData('0350', '审核失败');
        }
        
    }
    
    
    /**
     * 后台删除直播 -- 待审核/未通过
     * 只有 创建者、超管、高管 能操作
     */
    public function actionDelReviewed()
    {
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';  //当前登录管理员ID
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : '';  //直播ID
        if (!$admin_id || !$live_id) {
            $this->_errorData('0101', '参数错误');
        }
        $live_info = Live::findOne($live_id);
        if (!$live_info) {
            $this->_errorData('0102', '直播不存在');
        }
        if ($admin_id != 1 && $admin_id != $live_info['creator_id']) {
            //查看角色
            $reviewed_status = AdminRole::findRole($admin_id);
            if ($reviewed_status == 1) {
                $this->_errorData('0339', "暂无此权限");
            }
            
        }
        Live::deleteAll("live_id = " . $live_id);
        //删除直播对应新闻
        News::deleteAll("live_id = " . $live_id);
        $this->_successData('删除成功');
    }
    
    /**
     * 后台更改直播权重
     */
    public function actionEditWeight()
    {
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';  //当前登录管理员ID
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : '';  //直播ID
        $weight = isset($this->params['weight']) ? $this->params['weight'] : '';  //直播权重
        if (!$admin_id || !$live_id || !$weight) {
            $this->_errorData('0101', '参数错误');
        }
        $live_info = Live::findOne($live_id);
        if (!$live_info) {
            $this->_errorData('0102', '直播不存在');
        }
        if ($admin_id != 1) {
            //查看权限
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
            if (!in_array("快直播-高级管理-修改权重", $role_arr) && !in_array("快直播-录播管理-修改权重", $role_arr) && !in_array("快直播-直播管理-修改权重", $role_arr)) {
                $this->_errorData('0339', "暂无此权限");
            }
            
        }
        $live_info->weight = $weight;
        if ($live_info->save()) {
            $this->_successData('修改成功');
        } else {
            $this->_errorData('0114', '修改失败');
        }
    }
    
    /**
     * 后台更改直播观看人数
     */
    public function actionEditPlaycount()
    {
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';  //当前登录管理员ID
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : '';  //直播ID
        $play_count = isset($this->params['play_count']) ? $this->params['play_count'] : '';  //直播观看人数
        if (!$admin_id || !$live_id || !$play_count) {
            $this->_errorData('0101', '参数错误');
        }
        $live_info = Live::findOne($live_id);
        if (!$live_info) {
            $this->_errorData('0102', '直播不存在');
        }
        if ($admin_id != 1) {
            //查看权限
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
            if (!in_array("快直播-高级管理-修改观看人数", $role_arr)) {
                $this->_errorData('0339', "暂无此权限");
            }
            
        }
        $live_info->play_count = $play_count;
        if ($live_info->save()) {
            $this->_successData('修改成功');
        } else {
            $this->_errorData('0114', '修改失败');
        }
    }
    
    /**
     * 后台刷新直播排序时间
     */
    public function actionRefresh()
    {
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';  //当前登录管理员ID
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : '';  //直播ID
        if (!$admin_id || !$live_id) {
            $this->_errorData('0101', '参数错误');
        }
        $live_info = Live::findOne($live_id);
        if (!$live_info) {
            $this->_errorData('0102', '直播不存在');
        }
        if ($admin_id != 1) {
            //查看权限
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
            if (!in_array("快直播-高级管理-刷新排序时间", $role_arr)) {
                $this->_errorData('0339', "暂无此权限");
            }
//            $live_info = Live::find()->where(['live_id' => $live_id, 'creator_id' => $admin_id])->one();
//            if(!$live_info){
//                $this->_errorData('0102', '无权刷新该直播排序时间');
//            }
        }
        $live_info->refresh_time = date('Y-m-d H:i:s', time());;
        if ($live_info->save()) {
            $this->_successData('刷新成功');
        } else {
            $this->_errorData('0114', '刷新失败');
        }
    }
    
    /**
     * 后台修改图文直播状态为已结束
     */
    public function actionEditStatus()
    {
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';  //当前登录管理员ID
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : '';  //直播ID
        if (!$admin_id || !$live_id) {
            $this->_errorData('0101', '参数错误');
        }
        $live_info = Live::findOne($live_id);
        if (!$live_info) {
            $this->_errorData('0102', '直播不存在');
        }
        if ($admin_id != 1) {
            $live_info = Live::find()->where(['live_id' => $live_id, 'creator_id' => $admin_id])->one();
            if (!$live_info) {
                $this->_errorData('0103', '无权修改该直播状态');
            }
        }
        if ($live_info->category != 3) {
            $this->_errorData('0104', '直播类型异常');
        }
        $status = Live::getLiveStatus($live_info['start_time'], $live_info['status']);
        if ($status != 4) {
            $this->_errorData('0105', '直播状态不可结束');
        }
        $live_info->status = 2;
        if ($live_info->save()) {
            $this->_successData('更改成功');
        } else {
            $this->_errorData('0114', '更改失败');
        }
    }
    
    /**
     * @param $admin_id int //管理员id (//暂不支持超级管理员)
     *
     *
     */
    public function actionMyChannelList()
    {
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : ''; //直播ID
        $lists = Live::getBackAdminColumnsList($admin_id,$view=1);
        $this->_successData($lists);
        
    }
    
}
