<?php

namespace backend\controllers;

use common\models\AdminRole;
use common\models\LiveTagsRelation;
use common\models\SectionGoods;
use common\models\SectionPlugin;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * SetPlugin controller
 */
class SetPluginController extends PublicBaseController
{
    /*
     * 给直播 开通商品插件
     *
     * */
    public function actionAddGoods()
    {
        $section_id  = isset($this->params['section_id']) ? $this->params['section_id'] : ''; //直播ID
        $goods_info  = isset($this->params['goods_info']) ? $this->params['goods_info'] : ''; //关联商品信息
        $admin_id    = isset($this->params['admin_id']) ? $this->params['admin_id'] :  ''; //当前管理员ID

        if(!isset($section_id)){
            $this->_errorData('0121', "直播id不能为空");
        }

        if(!$admin_id){
            $this->_errorData('0123', "当前管理员ID 不能为空");
        }
        $have_plugin = SectionPlugin::findOne(['section_id' => $section_id, 'plugin_id' => 1]);
        if($have_plugin){
            $this->_errorData('0190','已开通' );
        }

        $goods_info = json_decode($goods_info);
        SectionPlugin::insertInfo($section_id,1,$goods_info->name);
//        $section_plugin = new SectionPlugin();
//        $section_plugin['section_id'] = $section_id;
//        $section_plugin['plugin_id']  = 1; //查看对应插件ID
//        $section_plugin['name']  = $goods_info->name;
//        $section_plugin->save();
        //调用商品插件
        SectionGoods::Add($section_id,$goods_info->goods_ids,0);
        
        $have_plugin = LiveTagsRelation::getSection_tags($section_id,3,1);
        if(!$have_plugin){
            //给直播 添加 ‘购物’ 标签
            $live_tag = new LiveTagsRelation();
            $live_tag['live_id'] = $section_id;
            $live_tag['tag_id'] = 3;
            $live_tag['type']   = 1;
            $live_tag['create_time'] = date('Y-m-d H:i:s', time());
            $live_tag['creator'] = $admin_id;
            $live_tag->save();
        }

        $this->_successData(1);

    }

    /*
     * 直播 关联商品插件 编辑 删除
     * */
    public function actionEditGoods(){
        $section_id   = isset($this->params['section_id']) ? $this->params['section_id'] : ''; //直播ID
        $live_goods   = isset($this->params['live_goods']) ? $this->params['live_goods'] : 0; //商品推荐插件，0无，1有(编辑)
        $goods_info   = isset($this->params['goods_info']) ? $this->params['goods_info'] : 0; //关联商品信息
        if(!isset($section_id)){
            $this->_errorData('0121', "直播id不能为空");
        }
        $have_plugin = SectionPlugin::findOne(['section_id' => $section_id, 'plugin_id' => 1]);
        if(!$have_plugin){
            $this->_errorData('0190','直播与商品插件 无关联' );
        }
        $goods_info = json_decode($goods_info);
        if($live_goods == 0){
            //删除
            SectionGoods::Unbind($section_id,1);
            //取消 此直播 ‘购物’标签
            LiveTagsRelation::deleteAll(["live_id"=>$section_id,"tag_id"=>3,'type'=>1]);

        }else{
            //编辑 关联关系
            $up_params['name'] = $goods_info->name;
            SectionPlugin::updateAll($up_params,['id'=>$have_plugin->id]);
            //编辑 关联商品信息
            SectionGoods::Add($section_id,$goods_info->goods_ids,1);

        }
        $this->_successData(1);
    }

    /*
    * 给直播 开通关注插件
    *
    * */
    public function actionAddFollow()
    {
        $section_id   = isset($this->params['section_id']) ? $this->params['section_id'] : ''; //直播ID
        $follow_info  = isset($this->params['follow_info']) ? $this->params['follow_info'] : 0; //关注相关信息
        $type         = isset($this->params['type']) ? $this->params['type'] : 0; //0创建，1编辑

        if(!isset($section_id)){
            $this->_errorData('0121', "直播id不能为空");
        }
        if(!$follow_info){
            $this->_errorData('1203', "已选关注插件，必须添加关注信息");
        }
        if($type == 1){
            $have_plugin = SectionPlugin::findOne(['section_id' => $section_id, 'plugin_id' => 2]);
            if(!$have_plugin){
                $this->_errorData('0191','直播与关注插件 无关联' );
            }
        }

        $follow_info = json_decode($follow_info);
        if($type == 0) {
            //开通 关注插件
            SectionPlugin::insertInfo($section_id, 2, $follow_info->name, $follow_info->image_url);
        }else{
            //编辑 关联关系
            $up_params['name'] = $follow_info->name;
            $up_params['image_url'] = $follow_info->image_url;
            SectionPlugin::updateAll($up_params,['id'=>$have_plugin->id]);
        }

        $this->_successData(1);
    }

    /*
     * 给直播 开通图文插件
     *
     * */
    public function actionAddPictext()
    {
        $section_id   = isset($this->params['section_id']) ? $this->params['section_id'] : ''; //直播ID
        $pictext_info = isset($this->params['pictext_info']) ? $this->params['pictext_info'] : 0; //图文相关信息
        $type         = isset($this->params['type']) ? $this->params['type'] : 0; //0创建，1编辑

        if(!isset($section_id)){
            $this->_errorData('0121', "直播id不能为空");
        }
        if(!$pictext_info){
            $this->_errorData('1204', "已选图文插件，必须添加图文信息");
        }
        if($type == 1){
            $have_plugin = SectionPlugin::findOne(['section_id' => $section_id, 'plugin_id' => 3]);
            if(!$have_plugin){
                $this->_errorData('0192','直播与图文插件 无关联' );
            }
        }
        $pictext_info = json_decode($pictext_info);
        if($type == 0) {
            //开通 图文插件
            SectionPlugin::insertInfo($section_id,3,$pictext_info->name,'',$pictext_info->title,$pictext_info->content,$pictext_info->is_water);
        }else{
            //编辑 关联关系
            $up_params['name']     = $pictext_info->name;
            $up_params['title']    = $pictext_info->title;
            $up_params['content']  = $pictext_info->content;
            $up_params['is_water'] = $pictext_info->is_water;
            SectionPlugin::updateAll($up_params,['id'=>$have_plugin->id]);
        }

        $this->_successData(1);
    }


    /*
     * 直播 关联关注、图文 插件 删除
     * */
    public function actionDelPlugin(){
        $section_id  = isset($this->params['section_id']) ? $this->params['section_id'] : ''; //直播ID
        $plugin_id   = isset($this->params['plugin_id']) ? $this->params['plugin_id'] : 0; //插件 ID

        if(!isset($section_id)){
            $this->_errorData('0121', "直播id不能为空");
        }
        $have_plugin = SectionPlugin::findOne(['section_id' => $section_id, 'plugin_id' => $plugin_id]);

        if(!$have_plugin){
            $this->_errorData('0190','直播与此插件 无关联' );
        }

        //删除
        SectionGoods::Unbind($section_id,$plugin_id);

        $this->_successData(1);
    }


}
