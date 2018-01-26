<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "garbage".
 *
 * @property integer $garbage_id
 * @property integer $company_id
 * @property string $goods_id
 * @property string $create_time
 */
class Garbage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'garbage';
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
            [['company_id', 'goods_id'], 'integer'],
            [['create_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'garbage_id' => 'Garbage ID',
            'company_id' => 'Company ID',
            'goods_id' => 'Goods ID',
            'create_time' => 'Create Time',
        ];
    }
    
    /*
     * 批量回收
     */
    public static function AddGarbage($company_id, $goods_ids_arr = array()){
    	try{
	    	foreach($goods_ids_arr as $key=>$value){
	    		$model = new self();
	    		$model->company_id = $company_id;
	    		$model->goods_id = $value;
	    		$model->create_time = date('Y-m-d H:i:s',time());
//	    		$model->is_del = '0';
	    		$model->save();
	    	}
	    	
	    	//商品批量设置被回收
	    	Goods::updateAll(['is_recovery'=>'1'],['goods_id'=>$goods_ids_arr]);
	    	
	    	return true;
    	}catch (Exception $e){
    		return false;
    	}	
    }
    
    /*
     * 商品回收站列表
     */
    public static function GetList($company_id = NULL, $keyword = NULL, $search_start_time = NULL, $search_end_time = NULL, $pageStart = '0', $pageEnd = '0'){
    	$returenData = array();
    	$model = new self();
    	#$andwhere = 'garbage.company_id = '.$company_id.' and garbage.is_del = 0';
    	$andwhere = 'garbage.company_id = '.$company_id;
    	if($keyword) $andwhere .= " and goods.goods_name like '%$keyword%' or goods.art_no like '%$keyword%'";
    	if($search_start_time) $andwhere .= " and garbage.create_time >= '{$search_start_time}'";
    	if($search_end_time) $andwhere .= " and garbage.create_time <= '{$search_end_time}'";
    	
    	$list = $model->find()
    	->select(['garbage.garbage_id','goods.goods_id','goods.goods_name','goods.art_no','goods.huiwenbi','garbage.create_time'])
    	->innerJoin('goods','goods.goods_id = garbage.goods_id')
    	->where($andwhere)
   		->orderBy(['garbage_id'=>SORT_DESC])
    	->offset($pageStart)
    	->limit($pageEnd-$pageStart)
    	->asArray()->all();
        $list || $list = [];

        $count = $model->find()
            ->select(['garbage.garbage_id','goods.goods_name','goods.art_no','goods.huiwenbi','garbage.create_time'])
            ->innerJoin('goods','goods.goods_id = garbage.goods_id')
            ->where($andwhere)
            ->orderBy(['garbage_id'=>SORT_DESC])
            ->asArray()->count();
        $count || $count = 0;

    	return ['count'=>$count, 'list'=>$list];//$returenData;
    }
    
    /*
     * 商品恢复/删除操作
     */
    public static function SetGarbages($garbage_ids_arr = array(), $type = '1'){
        try{
        	$goods_ids_arr =  array();
        	foreach($garbage_ids_arr as $key=>$value){
        		$model = self::findOne($value);
        		if($model) $goods_ids_arr[] = $model->goods_id;
        	}

        	if($type == '1'){
        		//恢复
        		Goods::updateAll(['is_recovery'=>'0','is_shelves'=>'0'],['goods_id'=>$goods_ids_arr]);
        	}else{
        		//删除
        		Goods::deleteAll(['goods_id'=>$goods_ids_arr]);
        	}
        	
        	//删除回收站id集
        	self::deleteAll(['garbage_id'=>$garbage_ids_arr]);
        	
	    	return true;
    	}catch (Exception $e){
    		return false;
    	}	
    }

}
