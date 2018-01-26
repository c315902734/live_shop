<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "brand".
 *
 * @property integer $brand_id
 * @property integer $company_id
 * @property string $brand_name
 * @property string $logo
 * @property integer $is_show
 * @property string $brand_url
 * @property string $brand_introduce
 * @property string $create_time
 * @property integer $is_del
 */
class Brand extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'brand';
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
            [['company_id', 'is_show', 'is_del'], 'integer'],
            [['brand_introduce'], 'string'],
            [['create_time'], 'safe'],
            [['brand_name', 'logo', 'brand_url'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'brand_id' => 'Brand ID',
            'company_id' => 'Company ID',
            'brand_name' => 'Brand Name',
            'logo' => 'Logo',
            'is_show' => 'Is Show',
            'brand_url' => 'Brand Url',
            'brand_introduce' => 'Brand Introduce',
            'create_time' => 'Create Time',
            'is_del' => 'Is Del',
        ];
    }
    
    /*
     * 获取列表
     */
    public static function GetList($company_id = NULL, $brand_name = NULL, $is_show, $pageStart = 0, $pageEnd = null){
    	$returnData = array();
    	 
    	$andwhere = '';
    	if($company_id) $andwhere .= ' and company_id = '.$company_id;
    	if($brand_name) $andwhere .= " and brand_name like '%$brand_name%' ";
    	if($is_show !== false) $andwhere .= " and is_show = {$is_show} ";

    	$model = new self();
    	$list = $model->find()
    	->where("is_del = 0".$andwhere)
    	->orderBy(['brand_id'=>SORT_DESC])
    	->offset($pageStart)
    	->limit($pageEnd-$pageStart)
    	->asArray()->all();

        $count = $model->find()
            ->where("is_del = 0".$andwhere)
            ->orderBy(['brand_id'=>SORT_DESC])
            ->asArray()->count();

    	
    	if($list){
    		foreach($list as $key=>$value){
    			$goods_count = Goods::find()->where(['brand_id'=>$value['brand_id']])->count();
    			$list[$key]['goods_count'] = $goods_count;
    		}
    	} 
    	
    	$returnData = ['count'=>$count ,'list'=>$list];
    	return $returnData;
    }
    
    /*
     * 添加品牌
     */
    public static function AddBrand($brand_id = NULL, $company_id = NULL, $brand_name = NULL, $logo = NULL, $is_show = '0', $brand_url = NULL, $brand_introduce = NULL){
    	if($brand_id){
    		$model = Brand::findOne($brand_id);
    	}else{
    		$model = new self();
            $model->create_time = date('Y-m-d H:i:s',time());
    	}
    	$model->company_id = $company_id;
    	$model->brand_name = $brand_name;
    	$model->logo = $logo;
    	$model->is_show = $is_show;
    	$model->brand_url = $brand_url;
    	$model->brand_introduce = $brand_introduce;
    	$model->is_del = '0';

    	if($model->save()){
    		return $model->brand_id;
    	}else{
    		return false;
    	}
    }
}
