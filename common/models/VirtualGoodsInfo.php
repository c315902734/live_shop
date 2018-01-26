<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "virtual_goods_info".
 *
 * @property integer $details_id
 * @property string $goods_id
 * @property string $serial_number
 * @property string $password
 * @property string $deadline
 * @property integer $is_sold
 * @property string $create_time
 */
class VirtualGoodsInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'virtual_goods_info';
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
            [['goods_id', 'is_sold'], 'integer'],
            [['deadline', 'create_time'], 'safe'],
            [['serial_number', 'password'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'details_id' => 'Details ID',
            'goods_id' => 'Goods ID',
            'serial_number' => 'Serial Number',
            'password' => 'Password',
            'deadline' => 'Deadline',
            'is_sold' => 'Is Sold',
            'create_time' => 'Create Time',
        ];
    }

    /*
     * 虚拟商品 卡号列表
     */
   	public static function GetList($goods_id =NULL, $keyword = NULL, $is_sold = 0, $is_activity = 0, $stock_warning, $pageStart = '0', $pageEnd = '0'){
   		$returnData = array();
   		$goods_info = Goods::find()->select(['goods_name'])->where(['goods_id'=>$goods_id])->asArray()->one();

        $where = '1 = 1';
        if($keyword){
            $where .= " AND virtual_goods_info.serial_number like '%$keyword%' or  shop_order.order_number like '%$keyword%'";
        }
        if($is_sold !== false){
            $where .= " AND virtual_goods_info.is_sold = {$is_sold} ";
        }
        if($stock_warning){
            $where .= " AND goods_stock_warning > goods_stock";
        }

   		$model = new self();
   		$list = $model->find()
   		->select(['virtual_goods_info.*','shop_order.order_number'])
   		->leftJoin('order_goods_relation','order_goods_relation.virtual_goods_id = virtual_goods_info.details_id')
   		->leftJoin('shop_order','shop_order.order_id = order_goods_relation.order_id')
   		->where(['virtual_goods_info.goods_id'=>$goods_id])
   		->andWhere($where)
   		->orderBy(['details_id'=>SORT_ASC])
    	->offset($pageStart)
    	->limit($pageEnd-$pageStart)
    	->asArray()->all();

        $count = $model->find()
            ->select(['virtual_goods_info.*','shop_order.order_number'])
            ->leftJoin('order_goods_relation','order_goods_relation.virtual_goods_id = virtual_goods_info.details_id')
            ->leftJoin('shop_order','shop_order.order_id = order_goods_relation.order_id')
            ->where(['virtual_goods_info.goods_id'=>$goods_id])
            ->andWhere($where)
            ->orderBy(['details_id'=>SORT_ASC])
            ->asArray()->count();

   		if($list){
   			foreach($list as $key=>$value){
   				$list[$key]['goods_name'] = $goods_info['goods_name'];
//    				$order_info = OrderGoodsRelation::find()
//    				->select(['order.order_number'])
//    				->leftJoin('order','order.order_id = order_goods_relation.order_id')
//    				->where(['order_goods_relation.goods_id'=>$goods_id,'order_goods_relation.virtual_goods_id'=>$value['details_id']])
//    				->asArray()->one();
//    				$list[$key]['order_number'] = isset($order_info['order_number']) ? $order_info['order_number'] : '';
   				$returnData[] = $list[$key];
   			}
   		}

   		return ['count'=>$count, 'list'=>$returnData];//$returnData;
   	}


   	/*
   	 * 补货
   	 */
   	public static function AddVirtualGoods($goods_id = NULL, $details_id = NULL, $card_type = '1', $virtual_goods_info = array()){
   		try{
   			if($details_id){
   				UpdateVirtualGoodsInfo(
	   				$details_id,
	   				$virtual_goods_info['single']['serial_number'],
	   				$virtual_goods_info['single']['password'],
	   				$virtual_goods_info['single']['deadline']
   				);
   			}else{
   				if($card_type == '1'){
                    if ($virtual_goods_info['single']['serial_number'] && $virtual_goods_info['single']['password'] && $virtual_goods_info['single']['deadline']) {
                        Goods::AddVirtualGoodsInfo(
                            $goods_id,
                            $virtual_goods_info['single']['serial_number'],
                            $virtual_goods_info['single']['password'],
                            $virtual_goods_info['single']['deadline']
                        );
                    }
   				}else if($card_type == '2'){
   					foreach($virtual_goods_info['complexes'] as $key=>$value){
   					    if ($value['serial_number'] && $value['password'] && $value['deadline']) {
                            Goods::AddVirtualGoodsInfo(
                                $goods_id,
                                $value['serial_number'],
                                $value['password'],
                                $value['deadline']
                            );
                        }
   					}
   				}
   			}
   			return $goods_id;
   		}catch (Exception $e){
   			return false;
   		}
   	}
   	
   	/*
   	 * 删除
   	 */
   	public static function DeleteVirtualGoods($details_ids_arr = array()){
   		if(self::deleteAll(['details_id'=>$details_ids_arr])){
   			return true;
   		}else{
   			return false;
   		}
   	}
   	
}
