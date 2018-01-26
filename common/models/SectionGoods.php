<?php

namespace common\models;

use common\models\SectionPlugin;
use Yii;

/**
 * This is the model class for table "section_goods".
 *
 * @property string $_id
 * @property string $section_id
 * @property integer $good_id
 */
class SectionGoods extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'section_goods';
    }

    public static  function getDb()
    {
        return Yii::$app->vrlive;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['section_id', 'required', 'message' => '直播id不能为空'],
            ['good_id', 'required', 'message' => '关联商品ID不能为空'],
            [['section_id', 'good_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'section_id' => 'Section Id',
            'good_id' => 'Good Id'
        ];
    }

	/*
	 *直播详情内 关联 商品列表
	 * */
	public static function SectionGoods($page, $size, $user_id,$section_id){
		$offset = ($page - 1) * $size;
		$good_list = array();
		//查看对应的商品ID
		$good_all = SectionGoods::find()
			->where(['section_id'=>$section_id])
			->select(['good_id'])
			->orderBy(['good_id'=>SORT_DESC])
			->limit($size)->offset($offset)
			->column();

		//需要返回的参数 购买人数
		$return_same = "goods_id,company_id,goods_name,banner_image,abstract,huiwenbi,rmb_price,paynum";

		if($good_all) {
			$good_list = ShopOrder::hotGoodsSort($good_all ,$return_same );

			if ($good_list) {
				foreach ($good_list as $key => $val) {
					//查看 当前用户是否 支付此商品
					$user_pay = ShopOrder::getGoodsPayStatusByUser($user_id, $val['goods_id']);
					if ($user_pay) {
						$good_list[$key]['user_pay'] = 1;
					} else {
						$good_list[$key]['user_pay'] = 0;
					}
				}
			}
		}
		return $good_list;
	}
    
    public function Bind($section_id, $plugin_id)
    {
        
       
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
        
        $section_plugin = SectionPlugin::findOne(['section_id' => $section_id, 'plugin_id' => $plugin_id]);
        $result = $section_plugin->delete();
        //删除 关联商品
        SectionGoods::deleteAll(["section_id"=>$section_id]);
        
        return $result;
    }
    
    /**
     * 绑定产品插件到指定直播
     *
     * @param  integer $section_id 直播id
     * @param  string  $goods_ids  商品id字符串
     * @param  integer $is_edit     0 添加，1 修改
     *
     * @return array  $result     保存是否成功
     */
    public static function Add($section_id, $goods_ids,$is_edit)
    {
    
        $goods_ids = explode(',', $goods_ids);
        if($is_edit){
            $good_all = SectionGoods::find()
                ->where(['section_id' => $section_id])
                ->select(['good_id'])
                ->column();
            //编辑状态下，如果当前直播有商品，则调用移除商品方法移除当前直播所有的关联商品
            if(count($good_all)>0){
                //商品id数组转成以逗号分隔的字符串
                $good_all_string = implode(',', $good_all);
                $info = self::Remove($section_id, $good_all_string);
                //删除操作如果出现失败，返回结果中包含操作失败的商品id
                if (!$info['result']) {
                    return $info;
                }
            }
        }
        foreach ($goods_ids as $key => $val) {
            $section_goods = new SectionGoods();
            $section_goods['section_id'] = $section_id;
            $section_goods['good_id'] = $val;
            $result = $section_goods->save();
            if (!$result) {
                return ['result' => false, 'section_id' => $section_id, 'good_id' => $val];
            }
        }
        
        return ['result' => true, 'section_id' => $section_id, 'goods_id' => $goods_ids];
        
    }
    
    /**
     * 解除直播绑定的产品
     *
     * @param  integer $section_id 直播id
     * @param  string  $goods_ids  商品id字符串
     *
     * @return array  $result    保存是否成功,若不成功则返回相应的section_id和goods_id
     */
    public static function Remove($section_id, $goods_ids)
    {
       
        $goods_ids = explode(',', $goods_ids);
        foreach ($goods_ids as $key => $val) {
            $section_goods = SectionGoods::findOne(['section_id' => $section_id, 'good_id' => $val]);
            if ($section_goods) {
                $result = $section_goods->delete();
                if (!$result) {
                    return ['result' => false, 'section_id' => $section_id, 'goods_id' => $val];
                }
            }
        }
        
        return ['result' => true, 'section_id' => $section_id, 'goods_id' => $goods_ids];
        
    }
	
}
