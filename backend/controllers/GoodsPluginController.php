<?php

namespace backend\controllers;

use common\models\SectionGoods;
use common\models\SectionPlugin;
use common\models\Plugin;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * LiveSection controller
 */
class GoodsPluginController extends PublicBaseController
{
    /**
     * 绑定产品插件到指定直播
     *
     * @param  integer $section_id 直播id
     * @param  integer $plugin_id  插件id
     *
     * @return boolean $result     保存是否成功
     */
    public function Bind($section_id,$plugin_id)
    {
        
        if (!$section_id) {
            $this->_errorData('1202', "无直播间ID");
        }
        if (!$plugin_id) {
            $this->_errorData('1202', "无插件ID");
        }
        $plugin = Plugin::findOne($plugin_id);
        $section_plugin = new SectionPlugin();
        $section_plugin['section_id'] = $section_id;
        $section_plugin['plugin_id'] = $plugin->id; //插件id
        $section_plugin['name'] = $plugin->name;
        $result = $section_plugin->save();
        
        return $result;
   }
    
    /**
     * 移除产品插件和指定直播的绑定关系
     *
     * @param  integer $section_id 直播id
     * @param  integer $plugin_id  插件id
     *
     * @return boolean $result     删除是否成功 成功返回id 失败返回false
     */
    public function Unbind($section_id, $plugin_id)
    {
        
        if (!$section_id) {
            $this->_errorData('1202', "无直播间ID");
        }
        if (!$plugin_id) {
            $this->_errorData('1202', "无插件ID");
        }
        $section_plugin = SectionPlugin::findOne(['section_id' => $section_id, 'plugin_id' => $plugin_id]);
        
        $result = $section_plugin->delete();
        
        return $result;
    }
    
    /**
     * 绑定产品插件到指定直播
     *
     * @param  integer $section_id 直播id
     * @param  string $goods_ids  商品id字符串
     *
     * @return array  $result     保存是否成功
     */
   
    
    public function Add($section_id, $goods_ids)
    {
        
        if (!$section_id) {
            $this->_errorData('1202', "无直播间id");
        }
        if (!$goods_ids) {
            $this->_errorData('1202', "商品id不能为空");
        }
        $goods_ids = explode(',', $goods_ids);
        foreach ($goods_ids as $key => $val) {
            $section_goods = new SectionGoods();
            $section_goods['section_id'] = $section_id;
            $section_goods['good_id'] = $val;
            
            $result = $section_goods->save();
            if(!$result){
                return ['result' => false, 'section_id' => $section_id, 'good_id' => $val];
            }
        }
        return ['result' => true, 'section_id' => $section_id, 'goods_id' => $goods_ids];
        
    }
    
    /**
     * 绑定产品插件到指定直播
     *
     * @param  integer $section_id 直播id
     * @param  string  $goods_ids  商品id字符串
     *
     * @return array  $result    保存是否成功,若不成功则返回相应的section_id和goods_id
     */
    public function Remove($section_id, $goods_ids)
    {
        
        if (!$section_id) {
            $this->_errorData('1202', "无直播间id");
        }
        if (!$goods_ids) {
            $this->_errorData('1202', "商品id不能为空");
        }
        $goods_ids = explode(',', $goods_ids);
        foreach ($goods_ids as $key => $val) {
            $section_goods = SectionGoods::findOne(['section_id'=>$section_id,'good_id'=>$val]);
            if($section_goods) {
                $result = $section_goods->delete();
                if (!$result) {
                    return ['result' => false, 'section_id' => $section_id, 'goods_id' => $val];
                }
            }
        }
        
        return ['result' => true, 'section_id' => $section_id, 'goods_id' => $goods_ids];
        
    }

    
}
