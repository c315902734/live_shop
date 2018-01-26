<?php
namespace frontend\controllers;

use common\models\AdminUser;
use common\models\Goods;
use common\models\LiveSection;
use common\models\LiveTagsRelation;
use common\models\NewsPraise;
use common\models\NewsUserCollect;
use common\models\SectionGoods;
use common\models\SectionPlugin;
use common\models\SectionUserVerify;
use common\models\ShopOrder;
use common\models\ZLiveChannel;
use common\models\ZLiveUserSubscribe;
use Yii;
use common\models\LiveUserSubscribe;
include_once Yii::$app->basePath."/../QcloudApi/QcloudApi.php";
class LiveSectionController extends PublicBaseController{
    /**
     * 系列：直播列表 (直播中、已结束、回顾)
     * @param $page 页码
     * @param $size 条数
     */
    public function actionLiveList(){
        $column_type = isset($this->params['column_type']) ? $this->params['column_type'] :  0;
        $column_id   = isset($this->params['column_id']) ? $this->params['column_id'] : '';	//栏目id
        $user_id  = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
        $is_pc    = isset($_REQUEST['type']) ? $_REQUEST['type'] : 0;
        $page     = isset($_REQUEST['page']) ? $_REQUEST['page'] : '1';
        $size     = isset($_REQUEST['size']) ? $_REQUEST['size'] : '20';

        $result  = LiveSection::LiveList($column_type,$column_id, $user_id,$is_pc,$page, $size);
        if($is_pc == 1){
            $list = $result;
        }else{
            $list = $result['list'];
        }
        $this->_successData($list);




    }
    
    /**
     * 系列：直播列表 (未开始)
     * @param $page 页码
     * @param $size 条数
     */
    public function actionLiveWaitList(){
        $column_type = isset($this->params['column_type']) ? $this->params['column_type'] :  0;
        $column_id   = isset($this->params['column_id']) ? $this->params['column_id'] : '';	//栏目id
        $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
        $is_pc    = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
        $page    = isset($_REQUEST['page']) ? $_REQUEST['page'] : '1';
        $size    = isset($_REQUEST['size']) ? $_REQUEST['size'] : '20';
        
        $result = LiveSection::WaitList($column_type,$column_id, $user_id,$is_pc,$page, $size);
        if($is_pc == 1){
            $list = $result;
        }else{
            $list = $result['list'];
        }
        $this->_successData($list);
    }


    /**
     * 系列内直播详情
     * @param $live_id 直播id
     * @param $token 用户登录token 可不传
     * @param $user_id 用户id 可不传
     */
    public function actionGetLiveById(){
        $section_id = isset($_REQUEST['section_id']) ? $_REQUEST['section_id'] : '';
        $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';

        //查询直播主信息
        $live = LiveSection::getLiveById($section_id);
        if($live){
            //点击次数 加1
            $live_cou = $live['play_count'];
            $true_live_cou = $live['true_play_count'];
            $live['play_count'] = LiveSection::countAdd($section_id, $live_cou, $true_live_cou);
            //是否付费，付费金额返回
            $live['pay_type'] = '';
            $live['huiwenbi'] = '';
            $live['rmb_price'] = '';
            if($live['show_type'] == 1){
                $live_goods = Goods::find()->where(['goods_id'=>$live['good_id']])->asArray()->one();
                if($live_goods){
                    $live['pay_type'] = $live_goods['pay_type'];
                    $live['huiwenbi'] = $live_goods['huiwenbi'];
                    $live['rmb_price'] = $live_goods['rmb_price'];
                }
            }

            //查看开通的 插件
            $plugin = SectionPlugin::getPlugins($section_id);
            if(!$plugin){
                $live['plugins'] = array();
            }else{
                $live['plugins'] = $plugin;
            }

            //是否 视频直播
            $live['is_video'] = 0;
            $is_video = LiveTagsRelation::getSection_tags($section_id,5,1);
            if($is_video){
                $live['is_video'] = 1;
            }
            //是否 图文直播
            $live['is_textvideo'] = 0;
            $is_textvideo = LiveTagsRelation::getSection_tags($section_id,7,1);
            if($is_textvideo){
                $live['is_textvideo'] = 1;
            }

            //返回所有 标签
            $all_tags = LiveTagsRelation::getSection_alltags($section_id);
            $live['all_tags'] = $all_tags;

            //返回当前用户是否验证通过
            $live['user_verify'] = 1; //默认验证过
            if($user_id){
                $user_verify = SectionUserVerify::getUser_verify($section_id,$user_id);
                if(!$user_verify){
                    $live['user_verify'] = 0; //未验证过
                }
            }

            //返回直播间所属公司
            $admin_info = AdminUser::find()->alias('a')
                ->leftJoin('vrnews1.company c','a.company_id = c.company_id')
                ->select("c.name")->where(['admin_id'=>$live['creator_id']])->asArray()->one();
            $live['source'] = '';
            if(!empty($admin_info['name'])){
                $live['source'] = $admin_info['name'];
            }
            //查询是否收藏
            if($user_id){
                $collect_model = NewsUserCollect::find()->where(['news_id'=>$section_id, 'user_id'=>$user_id, 'type'=>4, 'status'=>1])
                    ->asArray()->one();
                if($collect_model){
                    $live['subscribe_status'] = 1;
                    $live['subscribe']        = $collect_model['collect_id'];
                }else{
                    $live['subscribe_status'] = 0;
                    $live['subscribe']        = '';
                }
            }else {
                $live['subscribe_status'] = 0;
                $live['subscribe']        = '';
            }

            $live['is_subscribe'] = 0;
            if(!empty($user_id)){
                $is_subscribe = ZLiveUserSubscribe::find()->where(['user_id'=>$user_id, 'live_id'=>$section_id, 'status'=>1])->count();
                $live['is_subscribe'] = $is_subscribe;
            }

            $live['chatroom_id'] = 'room_'.$section_id;

            //点赞数量
            $praise_count = NewsPraise::find()->where(['news_id'=>$section_id, 'news_type'=>0, 'status'=>1])->count();
            $live['praise_count'] = $praise_count;

            //判断是否点赞
            if($user_id){
                $is_praise = NewsPraise::find()->where(['news_id'=>$section_id, 'user_id'=>$user_id, 'news_type'=>0, 'status'=>1])->count();
                $live['is_praise'] = 0;
                if($is_praise){
                    $live['is_praise'] = 1;
                }
            }
            
            $this->_successData($live);
        }else{
            $this->_errorData('1001', '直播或用户不存在！');
        }

    }

    /*
     * 直播推流状态
     * */
    public function actionChannelStatus(){
        $section_id = isset($_REQUEST['section_id']) ? $_REQUEST['section_id'] : '';
        if(!$section_id){
            $this->_errorData(0001,'参数错误' );
        }
        //查看直播状态
        $section_info = LiveSection::find()->where(['section_id'=>$section_id])->asArray()->one();
        $res_status['section_status'] = $section_info['status'];

        $res_status['channel_status'] = 1;
        //查看直播码
        $channel = ZLiveChannel::find()->where(['channel_name' => '直播码-' . $section_id])->orderBy('create_time desc')->asArray()->one();
        if (!$channel) {
            $this->_errorData(0301, '无直播码信息');
        }
        //直播码
        $channel_code = $channel['txy_channel_id'];

        $appid = Yii::$app->params['appid'];//'1253999690';
        $zhiboma_key = Yii::$app->params['txy_zbm_key'];//'3ccce1558b5d4430135582a5f11582e9';
        $t = time() + 60;
        $sign = md5($zhiboma_key . $t);
        //调用腾讯云视频详情接口
        $interface = "Live_Channel_GetStatus";
        $config = array(
            'SecretId' => Yii::$app->params['API_SecretId'],
            'SecretKey' => Yii::$app->params['API_SecretKey'],
            'RequestMethod' => 'GET',
            'DefaultRegion' => Yii::$app->params['API_DefaultRegion']);

        $service = \QcloudApi::load(\QcloudApi::MODULE_FCGI, $config);
        $package = array(
            'appid' => $appid,
            'interface' => $interface,
            'Param.s.channel_id' => $channel_code,
            't' => $t,
            'sign' => $sign,
        );

        $res = $service->Send($package);
        $res_json = json_decode($res, true);

        //流状态 0异常，1正常
        $res_status['channel_status'] = 0;
        //从未 推过流
        if ($res_json['ret'] == 20601) {
            $res_status['channel_status'] = 0;
        }

        if (!empty($res_json['output'][0]['status'])) {
            //断流  更新断流时间 push_time
            if ($res_json['output'][0]['status'] == 0) {
                $res_status['channel_status'] = 0;
            }
            //正常推流 跳过
            if ($res_json['output'][0]['status'] == 1) {
                $res_status['channel_status'] = 1;
            }

            //流关闭状态 关闭直播
            if ($res_json['output'][0]['status'] == 3) {
                $res_status['channel_status'] = 0;
            }
        }

        $this->_successData($res_status);
    }

    /**
     * 在直播详情内 显示的此系列内的直播列表 （全部状态）
     * @param $page 页码
     * @param $size 条数
     */
    public function actionSectionList(){
        $page    = isset($_REQUEST['page']) ? $_REQUEST['page'] : '1';
        $size    = isset($_REQUEST['size']) ? $_REQUEST['size'] : '20';
        $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
        $section_id = isset($_REQUEST['section_id']) ? $_REQUEST['section_id'] : '';
        $result = LiveSection::SectionList($page, $size, $user_id,$section_id);
        
        $this->_successData($result);
    }

    /*
     * 直播详情内 关联 商品列表
     * live_id  直播ID
     * user_id  用户ID
     * */
    public function actionSectionGoods(){
        $page    = isset($_REQUEST['page']) ? $_REQUEST['page'] : '1';
        $size    = isset($_REQUEST['size']) ? $_REQUEST['size'] : '20';
        $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
        $section_id = isset($_REQUEST['section_id']) ? $_REQUEST['section_id'] : '';
        $result = ShopOrder::hotGoodsSort($section_id,$page,$size);
        if ($result) {
            foreach ($result as $key => $val) {
                $result[$key]['huiwenbi'] = substr($val['huiwenbi'], 0,stripos($val['huiwenbi'],'.'));
                //查看 当前用户是否 支付此商品
                $user_pay = ShopOrder::getGoodsPayStatusByUser($user_id, $val['goods_id']);
                if ($user_pay) {
                    $result[$key]['user_pay'] = 1;
                } else {
                    $result[$key]['user_pay'] = 0;
                }
            }
        }
//        $result = SectionGoods::SectionGoods($page, $size, $user_id,$section_id);

        $this->_successData($result);
    }

    /*
   * 用户验证直播成功 记录
   * section_id  直播ID
   * user_id  用户ID
   * */
    public function actionUserVerify(){
        $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
        $section_id = isset($_REQUEST['section_id']) ? $_REQUEST['section_id'] : '';
        $result = SectionUserVerify::getUser_verify($section_id,$user_id);
        if (!$result) {
            SectionUserVerify::save_verify($section_id,$user_id);
            $this->_successData(1);
        }else{
            $this->_errorData('20001', "已验证通过");
        }
    }

    //表数据处理
    public function actionSetNum(){
        //查看 z_live_channel 中live_id channel_id
        //select live_section.section_id,live_id,channel_id,z_live_channel.section_id,txy_channel_id from live_section left join z_live_channel on live_section.live_id = substring(substring_index(z_live_channel.txy_channel_id, '_',2),7)
//where length(z_live_channel.txy_channel_id) >  20
 //limit 20

        $channel_have = LiveSection::find()
            ->leftJoin('vrlive.z_live_channel', "live_section.live_id = substring(substring_index(z_live_channel.txy_channel_id, '_',2),7)")
            ->where("length(z_live_channel.txy_channel_id) >  20 and live_section.section_id != z_live_channel.section_id")
            ->select([
                    "substring(substring_index(txy_channel_id, '_',2),7)",
                    "live_section.rever_url",
                    "z_live_channel.rever_url",
                    "txy_channel_id",
                    "z_live_channel.section_id",
                    "channel_id",
                    "live_section.section_id",
                    "live_id"
                ]
            )
            ->asArray()->one();
        //对应 live_new 表中  section_id rever_url 放入 z_live_channel 表对应字段内
        if($channel_have){
            $param['section_id'] = $channel_have['section_id'];
            $param['rever_url'] = $channel_have['rever_url'];
            ZLiveChannel::updateAll($param,['channel_id'=>$channel_have['channel_id']]);
        }


    }



}