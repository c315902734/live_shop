<?php
namespace backend\controllers;

use backend\models\SysMenuAdmin;
use common\models\AdminRole;
use common\models\AdminUser;
use common\models\Area;
use common\models\AreaAdmin;
use common\models\Goods;
use common\models\Live;
use common\models\LiveNew;
use common\models\LiveSection;
use common\models\LiveTagsRelation;
use common\models\News;
use common\models\NewsColumn;
use common\models\NewsColumnAdmin;
use common\models\PowerAction;
use common\models\SectionGoods;
use common\models\SectionPlugin;
use common\models\ZLiveCameraAngle;
use common\models\ZLiveChannel;
use common\models\ZLiveManager;
use Faker\Provider\Company;
use Yii;
use yii\db\Command;
use yii\helpers\ArrayHelper;

/**
 * LiveSection controller
 */
class LiveSectionController extends PublicBaseController
{
    /*
     * 当前账号拥有权限的栏目 列表
     *
     * */
    public function actionAdminColumn(){
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] :  ''; //当前管理员ID
        if(!$admin_id){
            $this->_errorData('0123', "当前管理员ID 不能为空");
        }
        if($admin_id == 1){
            //超管 可以查看所有
            $news_column = NewsColumn::find()->where(['type'=>1,'status'=>1])->select("column_id,name")->asArray()->all();
            $area = Area::find()->where(['establish_status'=>1,'disable_status'=>0])->select("area_id,name")->asArray()->all();
            $columns = array_merge($news_column,$area);

        }else {
            //查看当前管理员 拥有的常规栏目信息
            $news_column = NewsColumnAdmin::find()
                ->leftJoin('vrnews1.news_column a', 'news_column_admin.column_id = a.column_id')
                ->where(['admin_id' => $admin_id,'a.type'=>1])
                ->select(["a.column_id","name","@weight:=0 as `column_type`"])
                ->asArray()->all();

            //查看是否有本地栏目
            $columns_area = NewsColumnAdmin::find()
                ->leftJoin('vrnews1.news_column a', 'news_column_admin.column_id = a.column_id')
                ->where(['admin_id' => $admin_id,'a.type'=>2])->select("a.column_id,name")->asArray()->all();

            if(!empty($columns_area)){
                //查看当前管理员 拥有的本地栏目信息
                $area = AreaAdmin::find()
                    ->leftJoin('vrnews1.area b', 'area_admin.area_id = b.area_id')
                    ->where(['admin_id' => $admin_id])
                    ->select("b.area_id,name,@initial:=1 as `column_type`")
                    ->asArray()->all();
                //合并两个 栏目信息
                $columns = array_merge($news_column, $area);
            }else{
                $columns = $news_column;

            }

        }
//        $live_list = array();
//        foreach ($columns as $key=>$val){
//            //查出栏目对应 新闻 及 系列列表
//            if(isset($val['column_id'])) {
//                $live_list = News::find()
//                    ->leftJoin('vrlive.live_new c', 'news.live_id = c.live_id')
//                    ->where(['column_id' => $val['column_id'], 'is_fast' => 1,'c.type'=>0])
//                    ->andWhere(['!=',"c.status",'0'])
//                    ->select("c.live_id,c.name")
//                    ->asArray()->all();
//            }else{
//                $live_list = News::find()
//                    ->leftJoin('vrlive.live_new c', 'news.live_id = c.live_id')
//                    ->where(['area_id' => $val['area_id'], 'is_fast' => 1,'c.type'=>0])
//                    ->andWhere(['!=',"c.status",'0'])
//                    ->select("c.live_id,c.name")
//                    ->asArray()->all();
//            }
//            $columns[$key]['live_list'] = $live_list;
//        }

        $this->_successData($columns);
    }

    /*
     * 创建直播中 某栏目下直播系列 列表
     * 后台 栏目系列列表
     * */
    public function actionColumnLive(){
        $column_type = isset($this->params['column_type']) ? $this->params['column_type'] :  0;
        $column_id = isset($this->params['column_id']) ? $this->params['column_id'] :  '';

        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';  //当前登录管理员ID
        $name     = isset($this->params['name']) ? $this->params['name'] : '';      //直播名称
        $page     = isset($this->params['page']) ? $this->params['page'] : '1';     //当前页数
        $count    = isset($this->params['count']) ? $this->params['count'] : '20';    //每页最多显示数量
        if(!isset($column_type) || !$column_id || !$admin_id){
            $this->_errorData('0101', "参数错误");
        }
        //查看 管理员 权限 缓存数据
        $role_arr = $this->getredis_admin($admin_id);

        $gao_admin = 0;//高管
        if(!in_array("快直播-高级管理-查看全部快直播列表",$role_arr)){
            $gao_admin = 1;
        }

        if(!in_array("快直播-监控-访问直播监控台",$role_arr)){
            $gao_admin = 1;
        }
        $list = LiveNew::getLiveList($column_type,$column_id,$admin_id, $name, $page, $count,$gao_admin);
        $this->_successData($list);

//        //查出栏目对应 新闻 及 系列列表
//        if($column_type == 0){
//            $live_list = News::find()
//                ->leftJoin('vrlive.live_new c', 'news.live_id = c.live_id')
//                ->where(['column_id' => $column_id, 'is_fast' => 1,'c.type'=>0])
//                ->andWhere(['!=',"c.status",'0'])
//                ->select("c.live_id,c.name")
//                ->asArray()->all();
//        }else{
//            $live_list = News::find()
//                ->leftJoin('vrlive.live_new c', 'news.live_id = c.live_id')
//                ->where(['area_id' => $column_id, 'is_fast' => 1,'c.type'=>0])
//                ->andWhere(['!=',"c.status",'0'])
//                ->select("c.live_id,c.name")
//                ->asArray()->all();
//        }
//
//        $this->_successData($live_list);
    }


    /*
     * 推流人员 列表
     * type 0除admin外所有的管理员，1推流业务员，2图文业务员
     *
     * */
    public function actionOperatorList(){
        $type = isset($this->params['type']) ? $this->params['type'] : 0;

        if($type == 0) {
            //查看 除admin 外全部管理员
            $manager_all = AdminUser::find()
                ->select("admin_id,real_name")
                ->where("status=1 and admin_id != 1")
                ->asArray()->all();
            $this->_successData($manager_all,'查询成功');
        }

        $search = PowerAction::getSearchList($type);
        $this->_successData($search,'查询成功');
    }

    /*
     * 创建直播
     *
     * */
    public function actionLiveCreate(){
        $type   = isset($this->params['type'])   ? $this->params['type']   : ''; //是否系列 0系列, 1直播
        $column_type = isset($this->params['column_type']) ? $this->params['column_type'] :  0;
        $column_id   = isset($this->params['column_id']) ? $this->params['column_id'] : '';	//栏目id
        $live_type   = isset($this->params['live_type']) ? $this->params['live_type'] : 0; //选择已有系列0,创建新系列 1
        $live_id     = isset($this->params['live_id']) ? $this->params['live_id'] : ''; //系列ID
        $live_name   = isset($this->params['live_name']) ? $this->params['live_name'] : ''; //创建新系列时,系列的名称
        $live_imgurl = isset($this->params['live_imgurl']) ? $this->params['live_imgurl'] : ''; //创建新系列时,系列的封面图
        $live_intro  = isset($this->params['live_intro']) ? $this->params['live_intro'] : ''; //创建新系列时,系列的介绍

        $name   = isset($this->params['name'])  ? $this->params['name']  : ''; //直播名称
        $image_url    = isset($this->params['image_url'])    ? $this->params['image_url']    : ''; //封面图
        $start_time   = isset($this->params['start_time'])   ? $this->params['start_time']   : ''; //直播开始时间

        $title = isset($this->params['title'])  ? $this->params['title']  : ''; //标题
        $introduction  = isset($this->params['introduction'])  ? $this->params['introduction']  : ''; //详情
        $watermark = isset($this->params['watermark']) ? $this->params['watermark'] : 0; //是否水印 0否 1是
        $notice  = isset($this->params['notice'])  ? $this->params['notice']  : ''; //公告

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

        $is_video = isset($this->params['is_video']) ? $this->params['is_video'] :  0;  //是否视频直播 0否，1是
        $operator_id = isset($this->params['operator_id']) ? $this->params['operator_id'] : ''; //直播推流业务员ID，只能一个

        $is_textvideo = isset($this->params['is_textvideo']) ? $this->params['is_textvideo'] : 0;//是否图文直播 0否，1是
        $live_man_avatar_url = isset($this->params['live_man_avatar_url']) ? $this->params['live_man_avatar_url'] : ''; //直播员属性–头像
        $live_man_cate  = isset($this->params['live_man_cate']) ? $this->params['live_man_cate'] :  ''; //直播员属性–类别
        $live_man_alias = isset($this->params['live_man_alias'])  ? $this->params['live_man_alias']  : ''; //直播员属性–别名
        $operator_ids = isset($this->params['operator_ids']) ? $this->params['operator_ids'] : ''; //图文直播 业务员ID，最多四个 多个用逗号隔开‘，’

        

        //授权类型 0免费，1付费，2密码观看，3手机认证观看
        $show_type = isset($this->params['show_type']) ? $this->params['show_type'] :  0;
        $price     = isset($this->params['price']) ? $this->params['price'] :  0;   //需付费金额
        $password = isset($this->params['password']) ? $this->params['password'] :  ''; //密码
        $phones = isset($this->params['phones']) ? $this->params['phones'] :  ''; //白名单限制 每个手机号用英文逗号（,）隔开
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] :  ''; //当前管理员ID

        if(!isset($type)){
            $this->_errorData('0121', "直播类型不能为空");
        }
        if(!$image_url){
            $image_url = "http://vrlive-10047449.image.myqcloud.com/lv1500016289morentu.png";
        }
        if(!$admin_id){
            $this->_errorData('0123', "当前管理员ID 不能为空");
        }
        if($is_video == 1 && !$operator_id){
            $this->_errorData('0125', "推流业务员不能为空");
        }
        if($start_time){
            //判断 时间是否比 当前服务器时间晚
            $now_date = time();
            $start_str = strtotime($start_time);
            if($start_str < $now_date){
                $this->_errorData('0325', "开始时间异常");
            }
        }

        //图文类型 验证推流业务员
        if($is_textvideo == 1){
            if(!$operator_ids){
                $this->_errorData('0125', "推流业务员不能为空");
            }else {
                $res_admin_ids = explode(',', $operator_ids);
                if (count($res_admin_ids) > 4) {
                    $this->_errorData('0131', "业务员不能多于4人");
                }
                if(in_array($operator_id, $res_admin_ids)){
                    $this->_errorData('0132', "业务员不能重复");
                }
            }
        }

        $view_ranage = $no_start_view_ranage_from.'-'.$no_start_view_ranage_to.'|'.$loading_view_ranage_from.'-'.$loading_view_ranage_to.'|'.$end_view_ranage_from.'-'.$end_view_ranage_to;

        $section_id = $this->create_live_id();

        //查看 当前管理员 的角色 超管或高管 可以直接发布，普通人员 创建为 待审核
        $reviewed_status = 1;
        if($admin_id == 1){
            $reviewed_status = 0;
        }else {
            $reviewed_status = AdminRole::findRole($admin_id);
        }

        if($is_textvideo == 1){
            //创建图文直播聊天室
            $code = $this->create_rcloud_chroom_pic_txt($section_id);
            if (!$code==200){
                $this->_errorData('0127','创建融云图文直播聊天室失败，请联系系统管理员！');
            }
        }
        //创建融云聊天室
        $code = $this->create_rcloud_chroom($section_id);

        //创建系列 或 直播类型创建跟直播名相同的系列名称
        if($live_type == 1 || $type == 1){
            if($type == 1){ //直播类型 创建跟直播名相同的 系列名称
                $create_live_name = $name;
                $live_imgurl = $image_url;
                $live_intro = $introduction;
            }else{
                $create_live_name = $live_name;
            }
            //创建系列
            $createlive = $this->createlive($column_type,$column_id,$admin_id,$create_live_name,$live_imgurl,$live_intro,$type);
            if(!$createlive){
                $this->_errorData('1201', '创建系列失败');
            }
            $live_id = $createlive['live_id'];
            $news_id = $createlive['news_id'];
        }

        if($live_type == 0){
            //查看 对应的新闻ID
            $news_info = News::find()->where(['live_id'=>$live_id])->asArray()->one();
            $news_id = $news_info['news_id'];
        }

        if($code == 200) {
            $create_time = date('Y-m-d H:i:s');
            if(!$start_time || empty($start_time)){
                $start_time = $create_time;
            }

            $goods_id = '';
            if($show_type == 1){
                //创建关联商品
                $company_id = $this->adminCompany($admin_id);
                if(!$company_id){
                    $company_id = 0;
                }
                $goods_id = Goods::AddGoods('', $company_id, $name, '', '', '', '', 2, 0, $price, '', '', '', '', '', '',  9999999, '', '', '', 2, 1, '', '', 1, '', 4);
            }

            $param = new LiveSection();
            $param['section_id']   = $section_id;
            $param['live_id']      = $live_id;
            $param['title']        = $name;
            $param['start_time']   = $start_time;
            $param['image_url']    = $image_url;
            $param['create_time']  = $create_time;
            $param['update_time']  = $create_time;
            $param['refresh_time'] = $create_time;
            $param['creator_id']   = $admin_id;
            $param['intro_title']  = $title;
            $param['introduction'] = $introduction;
            $param['notice']       = $notice;
            $param['watermark']    = $watermark;
            $param['is_video']     = $is_video;
            $param['is_textvideo'] = $is_textvideo;
            $param['live_man_avatar_url'] = $live_man_avatar_url ? $live_man_avatar_url : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
            $param['live_man_cate']   = $live_man_cate;
            $param['live_man_alias']  = $live_man_alias;
            $param['view_ranage']     = $view_ranage;
            $param['reviewed_status'] = 0;
            $param['good_id']   = $goods_id;
            $param['show_type'] = $show_type;
            $param['price']     = $price;
            $param['password']  = $password;
            $param['phones']    = $phones;

            $result = $param->save();
            if (!$result) {
                $this->_errorData('0128', "保存直播失败");
            }

//            //修改直播对应的系列 type值
//            $up_livenew['type'] = $type;
//            LiveNew::updateAll($up_livenew,['live_id'=>$live_id]);
            if($type == 0 && $live_type == 0){
                //系列内直播数量 加1
                $live_section_count = LiveNew::find()
                    ->where(['live_id'=>$live_id])
                    ->select("live_count")->column();
                if(!$live_section_count){ //处理数据问题
                    $live_section_count[0] = 0;
                }
                $section_count_add['live_count'] = $live_section_count[0] + 1;
                LiveNew::updateAll($section_count_add,['live_id'=>$live_id]);
            }

            if($is_textvideo == 1) {
                //添加 直播业务员
                foreach ($res_admin_ids as $key => $val) {
                    //查询管理员名称
                    $val_name = AdminUser::find()->select("real_name")->where('admin_id = ' . $val)->asArray()->one();
                    $live_manages = new ZLiveManager();
                    $live_manages['section_id']  = $section_id;
                    $live_manages['admin_id']    = $val;
                    $live_manages['admin_name']  = $val_name['real_name'];
                    $live_manages['create_time'] = date('Y-m-d H:i:s');
                    $live_manages->save();
                }
                //添加 图文标签
                LiveTagsRelation::save_tags($section_id,7,$admin_id);
            }
            if ($is_video == 1) {
                //创建直播码，并获取推流地址
                $channel_res = $this->getChannel($section_id,$start_time);

                //创建 直播的 直播码记录
                $live_channel = new ZLiveChannel();
                $live_channel['txy_channel_id'] = $channel_res['livecode'];
                $live_channel['channel_name']   = "直播码-".$section_id;
                $live_channel['device_type']    = 3;
                $live_channel['manager']  = $admin_id;
                $live_channel['status']   = 1;
                $live_channel['push_url'] = $channel_res['push_url'];
                $live_channel['pull_url'] = $channel_res['pull_url'];
                $live_channel['create_time'] = date("Y-m-d H:i:s");
                $live_channel['creator_id']  = $admin_id;
                $live_channel['type']        = 1;
                $live_channel['section_id']  = $section_id;

                $live_channel->save();
                $live_channel->getErrors();
                $channel_id = $live_channel->getAttributes(array(0=>'channel_id'));

                //添加 直播的直播码形式的 推流人
                $live_camera = new ZLiveCameraAngle();
                $live_camera['section_id']    = $section_id;
                $live_camera['signal_source'] = 1;
                $live_camera['name']          = "直播码-".$channel_id['channel_id'];
                $live_camera['source_id']     = $channel_id['channel_id'];
                $live_camera['operator_id']   = $operator_id;
                $live_camera->save();
//                print_r($live_camera->getErrors());die;

                //添加 视频标签
                LiveTagsRelation::save_tags($section_id,5,$admin_id);
            }

            // 当超管/高管时,创建的快直播自带直播标签，tag_id=2时是“直播"标签
            if($reviewed_status == 0) {
                LiveTagsRelation::save_tags($section_id,2, $admin_id);
//                $live_tag = new LiveTagsRelation();
//                $live_tag['live_id'] = $section_id;
//                $live_tag['tag_id']  = 2;
//                $live_tag['type']    = 1;
//                $live_tag['create_time'] = date('Y-m-d H:i:s', time());
//                $live_tag['creator']     = $admin_id;
//                $live_tag->save();
            }

            $h = intval(date("H"));
            $work_time = 1;
            if($h >= 9 || $h <= 21){
                $work_time = 0;
            }
//目前过滤 审核
            //admin 或 高管 在创建系列 情况下，直播不需审核，对应系列、新闻 改为显示状态
//            if($reviewed_status == 0){ // && $live_type == 1
                $live_param['status'] = 1;
                LiveNew::updateAll($live_param,['live_id'=>$live_id]);
//                $news_param['status'] = 0;
//                News::updateAll($live_param,['news_id'=>$news_id]);
//            }
            $res_work = array();
//            //返回当前管理员的 公司ID
//            if($admin_id != 1){
//                $company_admin_info = SysMenuAdmin::find()
//                    ->where("admin_id={$admin_id} AND company_id <> 0")
//                    ->asArray()
//                    ->one();
//                if(!$company_admin_info) $this->_errorData('1304','此账号无商城权限');
//
//                $company_info = Company::find()->where(['company_id'=>$company_admin_info['company_id']])->asArray()->one();
//                if(!$company_info) $this->_errorData('1305', '此账号无商城权限');
//                if($company_info['status'] != 1) $this->_errorData('1306', '该地区已经暂时关闭');
//                $res_work['company_id'] = $company_admin_info['company_id'];
//            }else{
//                $res_work['company_id'] = 1;
//            }

            $res_work['section_id'] = $section_id;
            $res_work['work_time'] = $work_time;
            $this->_successData($res_work);
        }else{
            $this->_errorData('0129','创建融云聊天室失败，请联系系统管理员！');
        }
    }

    //返回当前管理员的 公司ID
    public function adminCompany($admin_id){
        if($admin_id != 1){
            $company_admin_info = SysMenuAdmin::find()
                ->where("admin_id={$admin_id} AND company_id <> 0")
                ->asArray()
                ->one();
            if(!$company_admin_info) return false; //$this->_errorData('1304','此账号无商城权限');

            $company_info = Company::find()->where(['company_id'=>$company_admin_info['company_id']])->asArray()->one();
            if(!$company_info) return false; //$this->_errorData('1305', '此账号无商城权限');
            if($company_info['status'] != 1) return false; //$this->_errorData('1306', '该地区已经暂时关闭');
            return $company_admin_info['company_id'];
        }else{
            return 1;
        }
    }
    
    /*
     * 编辑直播
     *
     * */
    public function actionEdit(){
        $section_id  = isset($this->params['section_id']) ? $this->params['section_id'] : ''; //直播ID
        $column_type = isset($this->params['column_type']) ? $this->params['column_type'] :  0;
        $column_id   = isset($this->params['column_id']) ? $this->params['column_id'] : '';	//栏目id
        $type        = isset($this->params['type'])   ? $this->params['type']   : ''; //是否系列 0系列, 1直播
        $live_type   = isset($this->params['live_type']) ? $this->params['live_type'] : 0; //选择已有系列0,创建新系列 1
        $live_id     = isset($this->params['live_id']) ? $this->params['live_id'] : ''; //系列ID
        $live_name   = isset($this->params['live_name']) ? $this->params['live_name'] : ''; //创建新系列时,系列的名称
        $live_imgurl = isset($this->params['live_imgurl']) ? $this->params['live_imgurl'] : ''; //创建新系列时,系列的封面图
        $live_intro  = isset($this->params['live_intro']) ? $this->params['live_intro'] : ''; //创建新系列时,系列的介绍

        $name   = isset($this->params['name'])  ? $this->params['name']  : ''; //直播名称
        $image_url    = isset($this->params['image_url'])    ? $this->params['image_url']    : ''; //封面图
        $start_time   = isset($this->params['start_time'])   ? $this->params['start_time']   : ''; //直播开始时间

        $title = isset($this->params['title'])  ? $this->params['title']  : ''; //标题
        $introduction  = isset($this->params['introduction'])  ? $this->params['introduction']  : ''; //详情
        $watermark = isset($this->params['watermark']) ? $this->params['watermark'] : 0; //是否水印 0否 1是
        $notice  = isset($this->params['notice'])  ? $this->params['notice']  : ''; //公告

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

        $is_video = isset($this->params['is_video']) ? $this->params['is_video'] :  0;  //是否视频直播 0否，1是
        $operator_id = isset($this->params['operator_id']) ? $this->params['operator_id'] : ''; //直播推流业务员ID，只能一个

        $is_textvideo = isset($this->params['is_textvideo']) ? $this->params['is_textvideo'] : 0;//是否图文直播 0否，1是
        $live_man_avatar_url = isset($this->params['live_man_avatar_url']) ? $this->params['live_man_avatar_url'] : ''; //直播员属性–头像
        $live_man_cate  = isset($this->params['live_man_cate']) ? $this->params['live_man_cate'] :  ''; //直播员属性–类别
        $live_man_alias = isset($this->params['live_man_alias'])  ? $this->params['live_man_alias']  : ''; //直播员属性–别名
        $operator_ids = isset($this->params['operator_ids']) ? $this->params['operator_ids'] : ''; //图文直播 业务员ID，最多四个 多个用逗号隔开‘，’

        //授权类型 0免费，1付费，2密码观看，3手机认证观看
        $show_type = isset($this->params['show_type']) ? $this->params['show_type'] :  0;
        $price     = isset($this->params['price']) ? $this->params['price'] :  0;   //需付费金额
        $password = isset($this->params['password']) ? $this->params['password'] :  ''; //密码
        $phones = isset($this->params['phones']) ? $this->params['phones'] :  ''; //白名单限制 每个手机号用英文逗号（,）隔开
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] :  ''; //当前管理员ID
        $rever_url = isset($this->params['rever_url']) ? $this->params['rever_url'] : ''; //回顾视频地址

        if(!$section_id){
            $this->_errorData('0133', "直播ID不能为空");
        }
        if(!$admin_id){
            $this->_errorData('0123', "当前管理员ID 不能为空");
        }
        if($admin_id != 1){
            //查看权限
            $role_arr = $this->getredis_admin($admin_id);
            
            if(!in_array("快直播-直播管理-编辑快直播",$role_arr) && !in_array("快直播-高级管理-创建/编辑快直播", $role_arr)){
                $this->_errorData('0339',"暂无此权限");
            }
        }

        //查看 当前管理员 的角色 超管或高管 可以直接发布，普通人员 创建为 待审核
        $reviewed_status = 1;
        if($admin_id == 1){
            $reviewed_status = 0;
        }else {
            $reviewed_status = AdminRole::findRole($admin_id);
        }
        //查看直播是否存在
        $section_info = LiveSection::find()->where('section_id = '.$section_id)->asArray()->one();
        if(!$section_info){
            $this->_errorData('0134', "直播不存在");
        }
        //查看对应的系列
        $live_info = LiveNew::GetInfo($section_info['live_id']);
        if(!$live_info){
            $this->_errorData('0900', '数据异常');
        }

        if(!$image_url){
            $image_url = "http://vrlive-10047449.image.myqcloud.com/lv1500016289morentu.png";
        }
        if($section_info['reviewed_status'] == 0){
            $reviewed_status = 0;
        }

        $section_start_time = strtotime($section_info['start_time']);
        $now_date = time();
        $start_str = strtotime($start_time);

        if($start_time && $section_start_time > $now_date){ //未开始
            //判断 时间是否比 当前服务器时间晚
            if($start_str < $now_date){
                $this->_errorData('0325', "开始时间异常");
            }
        }
        if($is_video  == 1){
            if(!$operator_id){
                $this->_errorData('0125', "推流业务员不能为空");
            }
        }
        if($is_textvideo == 1){
            if(!$operator_ids){
                $this->_errorData('0125', "推流业务员不能为空");
            }else {
                $res_admin_ids = explode(',', $operator_ids);
                if (count($res_admin_ids) > 4) {
                    $this->_errorData('0131', "业务员不能多于4人");
                }
                if($is_video == 1 && in_array($operator_id, $res_admin_ids)){
                    $this->_errorData('0132', "业务员不能重复");
                }
            }
        }

        $view_ranage = $no_start_view_ranage_from.'-'.$no_start_view_ranage_to.'|'.$loading_view_ranage_from.'-'.$loading_view_ranage_to.'|'.$end_view_ranage_from.'-'.$end_view_ranage_to;

        $create_time = date('Y-m-d H:i:s');
        if(!$start_time || empty($start_time)){
            $start_time = $create_time;
        }

        //添加视频回顾地址
        if(in_array($section_info['status'],array(2,5)) && !empty($rever_url)){
            if($section_info['status'] == 2){
                $param['status'] = 5;
            }
            $param['rever_url'] = $rever_url;
        }

        //是否修改 直播分类
        if($live_info['type'] != $type){
            //修改类型
            if($type == 0){ //原类型为直播，现修改为系列
                if($live_type == 0){
                    //选择已有系列 判断是否选择原系列
                    if($live_id == $section_info['live_id']){
                        //选择 原直播对应的系列 直接改变 live 中的 类型
                        $type_param['type'] = 0;
                        LiveNew::updateAll($type_param,['live_id'=>$section_info['live_id']]);
                    }else{
                        //删除 原直播 对应的系列 对应的新闻
                        LiveNew::deleteAll(["live_id"=>$section_info['live_id']]);
                        News::deleteAll(["news_id"=>$live_info['news_id']]);
                        //更改对应系列ID
                        $type_params['live_id'] = $live_id;
                        LiveSection::updateAll($type_params,['section_id'=>$section_id]);
                        // 归属新系列内
                        $type_live['live_count'] = $live_info['live_count'] + 1;
                        LiveNew::updateAll($type_live,['live_id'=>$section_info['live_id']]);
                    }
                }else{
                    //创建新系列  删除原直播对应的 系列
                    LiveNew::deleteAll(["live_id"=>$section_info['live_id']]);
                    News::deleteAll(["news_id"=>$live_info['news_id']]);
                    $ret_cre = $this->createlive($column_type,$column_id,$admin_id,$live_name,$live_imgurl,$live_intro,$type);
                    //更改对应系列ID
                    $type_params['live_id'] = $ret_cre['live_id'];
                    LiveSection::updateAll($type_params,['section_id'=>$section_id]);
                }
            }else{ //原类型为系列，现修改为直播
                //查看 原系列内是否只有一条直播，如只有一条则删除系列 和新闻；多条 只将系列内直播数量 减1
                if($live_info['live_count'] > 1){
                    $type_live['live_count'] = $live_info['live_count'] - 1;
                    LiveNew::updateAll($type_live,['live_id'=>$section_info['live_id']]);
                }else{
                    LiveNew::deleteAll(["live_id"=>$section_info['live_id']]);
                    News::deleteAll(["news_id"=>$live_info['news_id']]);
                    //更改对应系列ID
                    $type_params['live_id'] = $live_id;
                    LiveSection::updateAll($type_params,['section_id'=>$section_id]);
                    //直播 自动创建系列 只能包含一条直播， 此处不需要更改 对应直播数量
                }
            }
        }

        //授权类型为付费时 修改关联商品
        $goods_id = '';
        if($show_type == 1){
            if(!$section_info['good_id']) {
                //创建关联商品
                $company_id = $this->adminCompany($admin_id);
                if(!$company_id){
                    $company_id = 0;
                }
                $goods_id = Goods::AddGoods('', $company_id, $name, '', '', '', '', 2, 0, $price, '', '', '', '', '', '',  9999999, '', '', '', 2, 1, '', '', 1, '', 4);
            }else{
                $goods_id = $section_info['good_id'];
                //查看价格是否更改
                if($price != $section_info['price']){
                    //更改关联的商品价格
                    $goods_params['price'] = $price;
                    Goods::updateAll($goods_params,['goods_id'=>$section_info['good_id']]);
                }
            }
        }

//        $param['live_id']      = $live_id;
        $param['title']        = $name;
        $param['start_time']   = $start_time;
        $param['image_url']    = $image_url;
        $param['update_time']  = $create_time;
        $param['refresh_time'] = $create_time;
        $param['intro_title']  = $title;
        $param['introduction'] = $introduction;
        $param['notice']       = $notice;
        $param['watermark']    = $watermark;
        $param['is_video']     = $is_video;
        $param['is_textvideo'] = $is_textvideo;
        $param['live_man_avatar_url'] = $live_man_avatar_url ? $live_man_avatar_url : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
        $param['live_man_cate']   = $live_man_cate;
        $param['live_man_alias']  = $live_man_alias;
        $param['view_ranage']     = $view_ranage;
        $param['reviewed_status'] = $reviewed_status;
        $param['good_id']   = $goods_id;
        $param['show_type'] = $show_type;
        $param['price']     = $price;
        $param['password']  = $password;
        $param['phones']    = $phones;

        LiveSection::updateAll($param,['section_id'=>$section_id]);

        //查看是否有 图文标签
        $section_videotag = LiveTagsRelation::getSection_tags($section_id,7,1);
        //编辑 图文 业务员
        if($is_textvideo == 1) {
            //查看 之前存储 业务员信息
            $live_manager_res = ZLiveManager::find()->where("section_id = ".$section_id)->asArray()->all();
            $live_manager_ids = array_column($live_manager_res, 'admin_id');
            $admin_ids_cou   = count($res_admin_ids); //原有 人数
            $manager_ids_cou = count($live_manager_ids); //本次人数
            //查看 并对比是否有改动
            $sect_cou = count(array_intersect($res_admin_ids, $live_manager_ids)); //两次相同的 数量

            if(($admin_ids_cou != $manager_ids_cou) || ($admin_ids_cou != $sect_cou)){
                //删除 并添加新的指派人
                ZLiveManager::deleteAll(["section_id"=>$section_id]);

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
            }
            //查看是否有 图文标签
            if(!$section_videotag){
                LiveTagsRelation::save_tags($section_id, 7, $admin_id);
            }
        }else{
            //删除 图文标签
            if($section_videotag) {
                LiveTagsRelation::delSection_tag($section_id, 1, 7);
            }
        }

        //查看是否有 视频标签
        $section_videotag = LiveTagsRelation::getSection_tags($section_id,5,1);
        //编辑 直播推流业务员
        if ($is_video == 1) {
            //查看是否修改了原指派人
            $live_operator_info = ZLiveCameraAngle::find()->where('section_id = '.$section_id.' and status=1')->asArray()->one();
            if($live_operator_info['operator_id'] != $operator_id || $section_info['start_time'] != $start_time) {

                //时间更改 才更换 推流中的 过期时间
                if($section_info['start_time'] != $start_time){
                    //创建直播码，并获取推流地址
                    $channel_res = $this->getChannel($section_id,$start_time);

                    //编辑 直播的 直播码记录
                    $live_channel['txy_channel_id'] = $channel_res['livecode'];
                    $live_channel['push_url']   = $channel_res['push_url'];
                    $live_channel['pull_url']   = $channel_res['pull_url'];
                    $live_channel['creator_id'] = $admin_id;
                    $live_channel['type']       = 1;

                    ZLiveChannel::updateAll($live_channel, ['channel_id' => $live_operator_info['source_id']]);

                    //编辑 直播的直播码形式的 推流人
                    $live_camera['section_id']    = $section_id;
                    $live_camera['operator_id']   = $operator_id;
                    ZLiveCameraAngle::updateAll($live_camera, ['camera_id' => $live_operator_info['camera_id']]);
                }else{
                    //编辑
                    $live_camera['section_id']  = $section_id;
                    $live_camera['operator_id'] = $operator_id;
                    ZLiveCameraAngle::updateAll($live_camera, ['camera_id' => $live_operator_info['camera_id']]);
                }
            }
            //查看是否有 视频标签
            if(!$section_videotag){
                LiveTagsRelation::save_tags($section_id, 5, $admin_id);
            }
        }else{
            //删除 视频标签
            if($section_videotag) {
                LiveTagsRelation::delSection_tag($section_id, 1, 5);
            }
        }
        //更新 新建系列和新闻状态 为 正常
        //目前过滤 审核
        //admin 或 高管 在创建系列 情况下，直播不需审核，对应系列、新闻 改为显示状态
//            if($reviewed_status == 0){ // && $live_type == 1
        $live_param['status'] = 1;
        LiveNew::updateAll($live_param,['live_id'=>$live_id]);
//                $news_param['status'] = 0;
//                News::updateAll($live_param,['news_id'=>$news_id]);
//            }

        $res_work = array();
        $h = intval(date("H"));
        $work_time = 1;
        if($h >= 9 || $h <= 21){
            $work_time = 0;
        }

        $res_work['section_id'] = $section_id;
        $res_work['work_time'] = $work_time;
        $this->_successData($res_work);
    }

    /*
     * 删除直播
     *
     * */
    public function actionDel()
    {
        $admin_id   = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';  //当前登录管理员ID
        $section_id = isset($this->params['section_id']) ? $this->params['section_id'] : '';  //直播ID
        if(!$admin_id || !$section_id){
            $this->_errorData('0101', '参数错误');
        }
        $section_info = LiveSection::findOne($section_id);
        if(!$section_info){
            $this->_errorData('0102', '直播不存在');
        }
        if($admin_id != 1){
            //查看权限
            $role_arr = $this->getredis_admin($admin_id);
            if(!in_array("快直播-高级管理-删除直播",$role_arr) && !in_array("快直播-录播管理-删除录播", $role_arr) && !in_array("快直播-直播管理-删除快直播", $role_arr)){
                $this->_errorData('0339',"暂无此权限");
            }

        }
        if($section_info->status == '4'){
            $this->_errorData('0103', '直播正在进行中，不可以删除');
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

        if($live_info->type == 0 && $live_info->live_count > 1){
            //删除直播  此系列 直播总数 减 1
            $section_info->status = 0;
            if($section_info->save()){
                $live_info->live_count = $live_info->live_count - 1;
                $live_info->save();
                $this->_successData('删除成功');
            }else{
                $this->_errorData('0113', '删除失败');
            }
        }
    }

    /**
     * 后台更改直播观看人数
     */
    public function actionEditPlaycount(){
        $admin_id   = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';  //当前登录管理员ID
        $section_id = isset($this->params['section_id']) ? $this->params['section_id'] : '';  //直播ID
        $play_count = isset($this->params['play_count']) ? $this->params['play_count'] : '';  //直播观看人数
        if(!$admin_id || !$section_id || !$play_count){
            $this->_errorData('0101', '参数错误');
        }
        $section_info = LiveSection::findOne($section_id);
        if(!$section_info){
            $this->_errorData('0102', '直播不存在');
        }
        if($admin_id != 1){
            //查看权限
            $role_arr = $this->getredis_admin($admin_id);
            if(!in_array("快直播-高级管理-修改观看人数",$role_arr)){
                $this->_errorData('0339',"暂无此权限");
            }

        }
        $section_info->play_count = $play_count;
        if($section_info->save()){
            $this->_successData('修改成功');
        }else{
            $this->_errorData('0114', '修改失败');
        }
    }

    /**
     * 后台刷新直播排序时间
     */
    public function actionRefresh(){
        $admin_id   = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';  //当前登录管理员ID
        $section_id = isset($this->params['section_id']) ? $this->params['section_id'] : '';  //直播ID
        if(!$admin_id || !$section_id){
            $this->_errorData('0101', '参数错误');
        }
        $section_info = LiveSection::findOne($section_id);
        if(!$section_info){
            $this->_errorData('0102', '直播不存在');
        }
        if($admin_id != 1){
            //查看权限
            $role_arr = $this->getredis_admin($admin_id);
            if(!in_array("快直播-高级管理-刷新排序时间",$role_arr)){
                $this->_errorData('0339',"暂无此权限");
            }
        }
        $section_info->refresh_time = date('Y-m-d H:i:s', time());;
        if($section_info->save()){
            $this->_successData('刷新成功');
        }else{
            $this->_errorData('0114', '刷新失败');
        }
    }

    /*
     * 创建系列
     *
     * */
    public function actionCreatelive(){
        $column_type = isset($this->params['column_type']) ? $this->params['column_type'] :  0;
        $column_id   = isset($this->params['column_id']) ? $this->params['column_id'] : '';	//栏目id
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] :  ''; //当前管理员ID
        $live_name   = isset($this->params['live_name']) ? $this->params['live_name'] : ''; //创建新系列时,系列的名称
        $live_imgurl = isset($this->params['live_imgurl']) ? $this->params['live_imgurl'] : ''; //创建新系列时,系列的封面图
        $live_intro  = isset($this->params['live_intro']) ? $this->params['live_intro'] : ''; //创建新系列时,系列的介绍

        $create_live = $this->createlive($column_type,$column_id,$admin_id,$live_name,$live_imgurl,$live_intro);
        if(!$create_live){
            $this->_errorData('1201','创建系列失败' );
        }

        $this->_successData(1);
    }


    /**
     * 后台 未开始直播列表
     */
    public function actionSectionList(){
        $column_type = isset($this->params['column_type']) ? $this->params['column_type'] :  0;
        $column_id = isset($this->params['column_id']) ? $this->params['column_id'] :  '';

        $admin_id  = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';  //当前登录管理员ID
        $name      = isset($this->params['name']) ? $this->params['name'] : '';      //直播名称
        $type      = isset($this->params['type']) ? $this->params['type'] : '';      //直播类型
        $live_name = isset($this->params['live_name']) ? $this->params['live_name'] : ''; //直播系列名称
        $page      = isset($this->params['page']) ? $this->params['page'] : '1';     //当前页数
        $count     = isset($this->params['count']) ? $this->params['count'] : '20';    //每页最多显示数量
        if(!isset($column_type) || !$column_id || !$admin_id){
            $this->_errorData('0101', "参数错误");
        }
        //查看 管理员 权限 缓存数据
        $role_arr = $this->getredis_admin($admin_id);

        $gao_admin = 0;//高管
        if(!in_array("快直播-高级管理-查看全部快直播列表",$role_arr) && !in_array("快直播-监控-访问直播监控台",$role_arr)){
            $gao_admin = 1;
        }
        
        $list = LiveSection::BackWaitList($column_type,$column_id,$admin_id, $name,$type,$live_name, $page, $count,$gao_admin);
        $this->_successData($list);
    }
    /**
     * 后台 (直播中、已结束、回顾)直播列表
     */
    public function actionSectionListOther(){
        $column_type = isset($this->params['column_type']) ? $this->params['column_type'] :  0;
        $column_id = isset($this->params['column_id']) ? $this->params['column_id'] :  '';

        $admin_id  = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';  //当前登录管理员ID
        $name      = isset($this->params['name']) ? $this->params['name'] : '';      //直播名称
        $type      = isset($this->params['type']) ? $this->params['type'] : '';      //直播类型
        $live_name = isset($this->params['live_name']) ? $this->params['live_name'] : ''; //直播系列名称
        $page      = isset($this->params['page']) ? $this->params['page'] : '1';     //当前页数
        $count     = isset($this->params['count']) ? $this->params['count'] : '20';    //每页最多显示数量
        if(!isset($column_type) || !$column_id || !$admin_id){
            $this->_errorData('0101', "参数错误");
        }
        //查看 管理员 权限 缓存数据
        $role_arr = $this->getredis_admin($admin_id);

        $gao_admin = 0;//高管
        if(!in_array("快直播-高级管理-查看全部快直播列表",$role_arr)){
            $gao_admin = 1;
        }

        if(!in_array("快直播-监控-访问直播监控台",$role_arr)){
            $gao_admin = 1;
        }
        $list = LiveSection::BackLiveList($column_type,$column_id,$admin_id, $name,$type,$live_name, $page, $count,$gao_admin);
        $this->_successData($list);
    }


    /**
     * 获取 后台直播详情
     */
    public function actionLiveInfo(){
        $admin_id  = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';  //当前登录管理员ID
        $section_id = isset($this->params['section_id']) ? $this->params['section_id'] : '';  //直播ID
        if(!$admin_id || !$section_id){
            $this->_errorData('0101', '参数错误');
        }
        $live_info = LiveSection::getLiveInfo($section_id);

        //是否 视频直播
        $live_info['is_video'] = 0;
        $is_video = LiveTagsRelation::getSection_tags($section_id,5,1);
        if($is_video){
            $live_info['is_video'] = 1;
        }
        //是否 图文直播
        $live_info['is_textvideo'] = 0;
        $is_textvideo = LiveTagsRelation::getSection_tags($section_id,7,1);
        if($is_textvideo){
            $live_info['is_textvideo'] = 1;
        }

        //返回所有 标签
        $all_tags = LiveTagsRelation::getSection_alltags($section_id);
        $live_info['all_tags'] = $all_tags;
        
        if(!$live_info){
            $this->_errorData('0102', '直播不存在');
        }

        //查看 缓存数据
        $role_arr = $this->getredis_admin($admin_id);
        
        $gao_admin = 0; //高管
        if(!in_array("快直播-高级管理-查看全部快直播列表",$role_arr) && !in_array("快直播-审核-访问待审核的快直播列表",$role_arr)){
            $gao_admin = 1;
        }
        if($admin_id != 1 && $gao_admin != 0){
            $live_info = LiveSection::getLiveInfo($section_id,$admin_id);
            if(!$live_info){
                $this->_errorData('0102', '无权查看该直播');
            }
        }
        $live_info['column_name'] = '';
        if($live_info['column_id']){
            //常规栏目 查看栏目名称
            $column_name = NewsColumn::find()
                ->where(['column_id'=>$live_info['column_id']])
                ->select('name')->column();
            $live_info['column_name'] = $column_name[0];
        }else{
            //本地栏目 返回栏目名称
            $column_name = Area::find()
                ->where(['area_id'=>$live_info['area_id']])
                ->select("name")->column();
            $live_info['column_name'] = $column_name[0];
        }
        //返回 直播创建者 名字
        $admin_live = AdminUser::find()->where(["admin_id"=>$live_info['creator_id']])->asArray()->one();
        $live_info['creator_name'] = $admin_live['real_name'];

        $live_info['operator_ids'] = ZLiveManager::find()->where(['section_id'=>$section_id])->asArray()->all();
        $live_info['operator_id']  = ZLiveCameraAngle::find()->alias('c')->leftJoin('vradmin1.admin_user a', 'a.admin_id = c.operator_id')->where(['section_id'=>$section_id])->select("c.*,a.username,a.real_name")->asArray()->one();
        //判断 当前管理员是什么角色
        $live_info['admin_operator'] = 0; //都未选中
        if($live_info['operator_id']['operator_id'] == $live_info['creator_id']){
            $live_info['admin_operator'] = 1; //推流业务员
        }
        //图文 业务员
        if($live_info['operator_ids']){
            foreach ($live_info['operator_ids'] as $key=>$val){
                if($val['admin_id'] == $live_info['creator_id']){
                    $live_info['admin_operator'] = 2; //图文 业务员
                    break;
                }
            }
        }

        if($live_info['view_ranage']) {
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
        //查看开通的 插件
        $plugin = SectionPlugin::getPlugins($section_id);
        if(!$plugin){
            $live_info['plugins'] = array();
        }else{
            $live_info['plugins'] = $plugin;
        }
        $this->_successData($live_info);
    }

    //创建 直播ID
    private function create_live_id(){
        return date('Y') . time() . rand(0000, 9999);
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
        $param['status']    = 0; //默认删除状态，直播创建成功 更改为正常状态
        $param['type']      = $type;
        $result = $param->save();
        if(!$result){
            return false;
        }

        // 快直播创建成果后，创建管理关联新闻，状态为待审核
        $news_data = new News();
        $news_data['title'] = $name;
        $news_data['cover_image'] = "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
        $news_data['weight']  = 70;
        $news_data['type']    = 15; //新增新闻类型，14.集入口 15.普通快直播
        $news_data['create_time']  = date('Y-m-d H:i:s', time());
        $news_data['refresh_time'] = time();
        $news_data['app_pub'] = 0;
        $news_data['type'] = 18;
        $news_data['live_id'] = $live_id;
        //新闻状态，0 已发布，1草稿，2定时发布,3待审核
        $news_data['status'] = 1;

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
        $createlive['live_id'] = $live_id;
        $createlive['news_id'] = $news_data->news_id;
        return $createlive;
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

    /**
     * 创建融云图文直播聊天室
     * @param $live_id
     * @return mixed
     */
    private function create_rcloud_chroom_pic_txt($live_id){
        $nonce = mt_rand();
        $timeStamp = time();
        $sign = sha1(Yii::$app->params['ryAppSecret'] . $nonce . $timeStamp);
        $header = array(
            'RC-App-Key:' . Yii::$app->params['ryAppKey'],
            'RC-Nonce:' . $nonce,
            'RC-Timestamp:' . $timeStamp,
            'RC-Signature:' . $sign,
        );
        $data = 'chatroom[room_' . $live_id . ']=ptn'.$live_id;
        $result = $this->curl_http(Yii::$app->params['ryApiUrl'] . '/chatroom/create.json', $data, $header);
        return $result['code'];
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



    //获取 管理员权限
    public function getredis_admin($admin_id){
        $redis = Yii::$app->cache;
        $red_admin = Yii::$app->params['environment'].'_admin_role_'.$admin_id;
        $redis_info = $redis->get($red_admin);
        if(!$redis_info){
            //如 无缓存 查看数据
            $red_list = AdminRole::getAdminRole($admin_id);
        }else{
            $red_list = $redis_info;
        }

        $role_arr = array_column($red_list,"action_name");
        return $role_arr;
    }


    //获取当前section相关插件
    public  function actionPlugins()
    {
        $section_id = isset($this->params['section_id']) ? $this->params['section_id'] : '';  //当前登录管理员ID
        $plugins= LiveSection::findOne(['section_id'=>$section_id])->plugin;
        if($plugins){
            $this->_successData(ArrayHelper::toArray($plugins));
        }else{
            $this->_successData('','当前直播无关联插件');
        }
    }

    //---------------------------------------------------------------------
        //采集端
    //---------------------------------------------------------------------
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
            $this->_successData($camera_lists);
        } else {
            //图文直播 is_textvideo 1
            $manage_lists = ZLiveManager::getLists($admin_id, $status, $page, $count);
            $this->_successData($manage_lists);
        }

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
        $compere = LiveSection::find()->where(['section_id'=>$live_id])->one(); //获取直播员信息
        if (!$compere) {
            $this->_errorData('0134', "直播不存在");
        }
//        if (!in_array($compere['category'], array(1, 4))) {
//            $this->_errorData('0137', "直播类型有误");
//        }
        $live_data['status'] = 2;
        LiveSection::updateAll($live_data, ['section_id' => $live_id]);
        $this->_successData("更新成功");
    }


}
