<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "goods".
 *
 * @property string $goods_id
 * @property integer $company_id
 * @property string $goods_name
 * @property string $tags
 * @property string $abstract
 * @property string $art_no
 * @property string $video_url
 * @property integer $pay_type
 * @property string $huiwenbi
 * @property string $rmb_price
 * @property string $market_price
 * @property integer $brand_id
 * @property string $brand_name
 * @property string $banner_image
 * @property integer $goods_stock
 * @property integer $goods_stock_warning
 * @property string $goods_introduce
 * @property integer $goods_type
 * @property string $delivery_area
 * @property integer $freight_type
 * @property string $freight
 * @property integer $recommend_status
 * @property integer $is_shelves
 * @property integer $is_recovery
 * @property integer $create_time
 */
class Goods extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'goods';
    }

    public static function getDb()
    {
        return Yii::$app->vrshop;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            [['company_id', 'pay_type', 'brand_id', 'goods_stock', 'goods_stock_warning', 'goods_type', 'freight_type', 'recommend_status', 'is_shelves', 'is_recovery', 'create_time'], 'integer'],
            [['goods_name'], 'required'],
            [['huiwenbi', 'rmb_price', 'market_price', 'freight'], 'number'],
            [['goods_introduce'], 'string'],
            [['goods_name', 'tags', 'abstract', 'video_url'], 'string', 'max' => 200],
            [['art_no'], 'string', 'max' => 50],
            [['brand_name', 'delivery_area'], 'string', 'max' => 100],
            [['banner_image'], 'string', 'max' => 300],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'goods_id' => 'Goods ID',
            'company_id' => 'Company ID',
            'goods_name' => 'Goods Name',
            'tags' => 'Tags',
            'abstract' => 'Abstract',
            'art_no' => 'Art No',
            'video_url' => 'Video Url',
            'pay_type' => 'Pay Type',
            'huiwenbi' => 'Huiwenbi',
            'rmb_price' => 'Rmb Price',
            'market_price' => 'Market Price',
            'brand_id' => 'Brand ID',
            'brand_name' => 'Brand Name',
            'banner_image' => 'Banner Image',
            'goods_stock' => 'Goods Stock',
            'goods_stock_warning' => 'Goods Stock Warning',
            'goods_introduce' => 'Goods Introduce',
            'goods_type' => 'Goods Type',
            'delivery_area' => 'Delivery Area',
            'freight_type' => 'Freight Type',
            'freight' => 'Freight',
            'recommend_status' => 'Recommend Status',
            'is_shelves' => 'Is Shelves',
            'is_recovery' => 'Is Recovery',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * @param $company_id        公司ID
     * @param $brand_id          品牌ID
     * @param $is_shelves        是否上下架
     * @param $recommend_status  推荐状态
     * @param $goods_name        商品名称
     * @param $goods_type        商品类型
     * @param $stock_warning     库存警告
     * @param $sale_type         销售类型
     * @param $pageStart
     * @param $pageEnd
     * @return array
     */
    public static function GetList($company_id = 0, $brand_id = 0, $is_shelves = 0, $recommend_status = 0, $goods_name = '', $goods_type = 0, $stock_warning = 0, $sale_type = 0, $live_verify_state = false, $tags = '', $page = 1, $pageSize = 10){
        $returnData = array();

        $offset = ($page - 1) * $pageSize;

        $andwhere = '';
        if(isset($is_shelves)) $andwhere .= ' AND is_shelves = '.$is_shelves;
        if($company_id) $andwhere .= ' and company_id = '.$company_id;
        if($brand_id) $andwhere .= ' and brand_id = '.$brand_id;

        if($recommend_status) $andwhere .= ' and recommend_status = '.$recommend_status;
        if($goods_name) $andwhere .= " and (goods_name like '%$goods_name%' or art_no like '%$goods_name%') ";
        if($goods_type) $andwhere .= ' and goods_type = '.$goods_type;
        if($stock_warning) $andwhere .= ' and goods_stock_warning > goods_stock';
        if ($sale_type) {
            $andwhere .= " and sale_type = {$sale_type}";
            if ($live_verify_state !== false) {
                $andwhere .= " and live_sale_status = {$live_verify_state}";
            }

            if ($tags !== '') {
                $andwhere .= " and FIND_IN_SET('{$live_verify_state}', tags)";
            }
        }

        $model = new self();
        $list = $model->find()
            ->where("is_recovery = 0 ".$andwhere)
            ->orderBy(['goods_id'=>SORT_DESC])
            ->offset($offset)
            ->limit($pageSize)
            ->asArray()->all();

        $count = $model->find()
            ->where("is_recovery = 0 ".$andwhere)
            ->orderBy(['goods_id'=>SORT_DESC])
            ->asArray()->count();

        if($list){
            foreach($list as $key=>$value){
                $category_model = new Category();
                $category_list = $category_model->find()
                    ->select(['category_name'])
                    ->innerJoin('category_goods_relation','category.category_id = category_goods_relation.category_id')
                    ->where(['category_goods_relation.goods_id'=>$value['goods_id']])
                    ->orderBy(['category.category_id'=>SORT_DESC])
                    ->asArray()->all();
                $list[$key]['category_list'] = $category_list;
            }
        }

        $returnData = ['count'=>$count, 'list'=>$list];
        return $returnData;
    }

    public static function liveGoodsList($company_id = 0, $section_id = 0, $goods_name = '', $brand_name = '', $freight_type = 0, $goods_tag = '', $add_status = 0, $page = 1, $size = 10){
        if (!$section_id) return [];
        $where_condition = 'g.sale_type IN (0, 2) AND g.live_sale_status = 1 AND g.is_shelves = 1 AND g.is_recovery = 0';

        $having_condition = '1';
        if ($company_id) {
            $where_condition .= ' AND g.company_id = '.$company_id;
        }
        if ($goods_name) {
            $where_condition .= " AND g.goods_name LIKE '%{$goods_name}'";
        }
        if ($brand_name) {
            $where_condition .= " AND g.brand_name LIKE '%{$brand_name}'";
        }
        if ($add_status) {
            $having_condition .= ' AND add_status = 1';
        }

        $count = self::find()
            ->alias('g')
            ->select([
                'g.company_id',
                'g.goods_id',
                'g.goods_name',
                'g.brand_name',
                'g.tags',
                'g.huiwenbi',
                'g.rmb_price',
                'g.freight',
                'g.goods_stock',
                new Expression("GROUP_CONCAT(sg.section_id) as section_ids")
            ])
            ->leftJoin('vrlive.section_goods sg', 'g.goods_id = sg.good_id')
            ->where($where_condition)
            ->groupBy('g.goods_id')
            ->having($having_condition)
            ->count();

        $list = self::find()
            ->alias('g')
            ->select([
                'g.company_id',
                'g.goods_id',
                'g.goods_name',
                'g.brand_name',
                'g.tags',
                'g.huiwenbi',
                'g.rmb_price',
                'g.freight',
                'g.goods_stock',
                new Expression("GROUP_CONCAT(sg.section_id) as section_ids")
            ])
            ->leftJoin('vrlive.section_goods sg', 'g.goods_id = sg.good_id')
            ->where($where_condition)
            ->groupBy('g.goods_id')
            ->offset(($page - 1) * $size)
            ->limit($size)
            ->having($having_condition)
            ->asArray()->all();

        foreach ($list as $k=>&$goods) {
            $goods['add_status'] = 0;
            if (strpos($goods['section_ids'], ',') !== false) {
                $_arr = explode(',', $goods['section_ids']);
                if (in_array($section_id, $_arr)) {
                    $goods['add_status'] = 1;
                }
            } else {
                if (trim($section_id) == $goods['section_ids']) {
                    $goods['add_status'] = 1;
                }
            }

            unset($goods['section_ids']);
            unset($goods);
        }

        return ['count'=>$count, 'list'=>$list];
    }

    /*
     * 添加商品
     */
    public static function AddGoods($goods_id = 0, $company_id = '0', $goods_name = NULL, $tags = NULL, $abstract, $art_no = NULL, $video_url = '', $pay_type = '0', $huiwenbi = 0, $rmb_price= 0, $market_price = 0, $brand_id = '0', $brand_name = NULL, $banner_image = NULL, $category_id = '0', $attribute_list = array(),  $goods_stock = '0', $goods_stock_warning = '0', $freight = '0', $delivery_area = '', $goods_type = '1', $card_type = '1', $virtual_goods_info = array(), $goods_introduce = NULL, $is_shelves = NULL, $freight_type, $sale_type){
        try{
            if($goods_id){
                $model = Goods::findOne($goods_id);
            }else{
                $model = new self();
                $model->create_time = time();
            }
            $model->company_id = $company_id;
            $model->goods_name = $goods_name;
            $model->tags = $tags;
            $model->abstract   = $abstract;
            $model->art_no = $art_no;
            $model->video_url = $video_url;
            $model->pay_type  = $pay_type;
            $model->huiwenbi  = $huiwenbi;
            $model->rmb_price = $rmb_price;
            $model->market_price = $market_price;
            $model->brand_id = $brand_id;
            $model->brand_name = $brand_name;
            $model->banner_image = $banner_image;
            $model->goods_stock = $goods_stock;
            $model->goods_stock_warning = $goods_stock_warning;
            $model->goods_introduce = $goods_introduce;
            $model->is_shelves = $is_shelves;
            $model->goods_type = $goods_type;
            $model->delivery_area = $delivery_area;
            $model->freight_type = $freight_type;
            $model->freight = $freight;
            $model->sale_type = $sale_type;

            if($model->save()){
                //分类
                if($category_id){
                    CategoryGoodsRelation::deleteAll(['goods_id'=>$model->goods_id]);
                    $category_goods_relation_model  = new CategoryGoodsRelation();
                    $category_goods_relation_model->category_id = $category_id;
                    $category_goods_relation_model->goods_id = $model->goods_id;
                    $category_goods_relation_model->save();
                }
                //虚拟商品
                if($goods_type == '2'){
//                    VirtualGoodsInfo::deleteAll(['goods_id'=>$model->goods_id]);
                    if(is_array($virtual_goods_info)){
                        if($card_type == '1'){
                            if ($virtual_goods_info['single']['serial_number'] && $virtual_goods_info['single']['password'] && $virtual_goods_info['single']['deadline']) {
                                self::AddVirtualGoodsInfo(
                                    $model->goods_id,
                                    $virtual_goods_info[0]['serial_number'],
                                    $virtual_goods_info[0]['password'],
                                    $virtual_goods_info[0]['deadline']
                                );
                            }
                        }else if($card_type == '2'){
                            foreach($virtual_goods_info as $key=>$value){
                                if ($value['serial_number'] && $value['password'] && $value['deadline']) {
                                    self::AddVirtualGoodsInfo(
                                        $model->goods_id,
                                        $value['serial_number'],
                                        $value['password'],
                                        $value['deadline']
                                    );
                                }
                            }
                        }
                    }
                }
                //属性
                GoodsAttributeValues::deleteAll(['goods_id'=>$model->goods_id]);

                if(is_array($attribute_list)){
                    foreach ($attribute_list as $attribute_key=>$attribute_value){
                        self::AddGoodsAttributeValues(
                            $model->goods_id,
                            $attribute_value['attribute_id'],
                            $attribute_value['values_content'],
                            isset($attribute_value['pay_type']) ? (int)$attribute_value['pay_type'] : 0,
                            isset($attribute_value['price']) ? round($attribute_value['price'], 2) : 0,
                            isset($attribute_value['rmb_price']) ? round($attribute_value['rmb_price'], 2) : 0,
                            $attribute_value['stock'],
                            $attribute_value['values_images']
                        );
                    }
                }
                return $model->goods_id;
            }
        }catch (Exception $e){
            return $e->getMessage();
        }
        return false;
    }

    /*
     * 添加虚拟商品表
     */
    public static function AddVirtualGoodsInfo($goods_id = NULL, $serial_number = NULL, $password = NULL, $deadline = NULL ){
        $virtual_goods_info_model = new VirtualGoodsInfo();
        $virtual_goods_info_model->goods_id = $goods_id;
        $virtual_goods_info_model->serial_number = $serial_number;
        $virtual_goods_info_model->password = $password;
        $virtual_goods_info_model->deadline = $deadline;
        $virtual_goods_info_model->is_sold = '0';
        $virtual_goods_info_model->create_time = date('Y-m-d H:i:s',time());
        if($virtual_goods_info_model->save()){
            return TRUE;
        }
        return false;
    }

    /*
     * 更新虚拟商品表
     */
    public static function UpdateVirtualGoodsInfo($details_id = NULL, $serial_number = NULL, $password = NULL, $deadline = NULL ){
        $virtual_goods_info_model = VirtualGoodsInfo::findOne($details_id);
        $virtual_goods_info_model->goods_id = $goods_id;
        $virtual_goods_info_model->serial_number = $serial_number;
        $virtual_goods_info_model->password = $password;
        $virtual_goods_info_model->deadline = $deadline;
        $virtual_goods_info_model->is_sold = '0';
        $virtual_goods_info_model->create_time = date('Y-m-d H:i:s',time());
        if($virtual_goods_info_model->save()){
            return TRUE;
        }
        return false;
    }

    /*
     * 添加属性值表
     */
    public static function AddGoodsAttributeValues($goods_id = NULL, $attribute_id = NULL, $values_content = NULL, $pay_type=0, $price = NULL, $rmb_price=0, $stock = NULL, $values_images = array() ){
        $goods_attribute_values_model = new GoodsAttributeValues();
        $goods_attribute_values_model->goods_id = $goods_id;
        $goods_attribute_values_model->attribute_id = $attribute_id;
        $goods_attribute_values_model->values_content = $values_content;
        $goods_attribute_values_model->pay_type = $pay_type;
        $goods_attribute_values_model->price = $price;
        $goods_attribute_values_model->rmb_price = $rmb_price;
        $goods_attribute_values_model->stock = $stock;
        $goods_attribute_values_model->values_images = json_encode($values_images);
        if($goods_attribute_values_model->save()){
            return TRUE;
        }
        return false;
    }

    /*
     * 设置审核状态
     */
    public static function SetRecommendStatus($goods_id = NULL, $recommend_status = NULL){
        $model = Goods::findOne($goods_id);
        if($model){
            $model->recommend_status = $recommend_status;
            if($model->save()) return $model->goods_id;
        }
        return false;
    }

    private  static function getRange($cnt=9){
        $numbers = range (1,$cnt);
        //播下随机数发生器种子，可有可无，测试后对结果没有影响
        srand ((float)microtime()*1000000);
        shuffle ($numbers);
        //跳过list第一个值（保存的是索引）
        $n = '';
        while (list(, $number) = each ($numbers)) {
            $n .="$number";
        }
        return $n;
    }

    /*
     * 商品下架
     */
    public static function ShelvesGoods($goods_ids_arr = array()){
        if(self::updateAll(['is_shelves'=>'1'],['in','goods_id',$goods_ids_arr])){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 后台 商品详情
     * @param $goods_id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getGoodsInfo($goods_id){
        $info = self::find()
            ->alias('g')
            ->select(['g.*', 'c.category_id', 'c.category_name'])
            ->leftJoin('vrshop.category_goods_relation cgr', 'cgr.goods_id = g.goods_id')
            ->leftJoin('vrshop.category c', 'cgr.category_id = c.category_id')
            ->where(['g.goods_id'=>$goods_id])
            ->asArray()
            ->one();
        if(!$info) return false;

        $attr_list = GoodsAttributeValues::find()
            ->alias('gav')
            ->select(['gav.attribute_id', 'gav.values_id', 'ga.attribute_name', 'gav.values_content', 'gav.pay_type', 'gav.price', 'gav.rmb_price', 'gav.stock', 'gav.values_images'])
            ->leftJoin('vrshop.goods_attribute ga', 'gav.attribute_id = ga.attribute_id')
            ->where(['gav.goods_id'=>$info['goods_id']])
            ->asArray()
            ->all();
        $info['attr_list'] = $attr_list;
        return $info;
    }

    public static function getLiveGoodsBuyState($user_id = 0, $goods_id = 0){
        if (!$user_id || !$goods_id) return 0;

        $buy_state_ret = OrderGoodsRelation::find()
            ->alias('ogr')
            ->select('so.order_id')
            ->leftJoin(ShopOrder::tableName().' so', 'ogr.order_id = so.order_id')
            ->where(['ogr.goods_id'=>$goods_id, 'so.user_id'=>$user_id])
            ->andWhere(['>', 'status', '0'])
            ->asArray()->one();
        $buy_state_ret = $buy_state_ret ? 1 : 0;
        return $buy_state_ret;
    }
}
