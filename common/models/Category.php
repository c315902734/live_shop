<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "category".
 *
 * @property integer $category_id
 * @property integer $parent_id
 * @property integer $level
 * @property integer $company_id
 * @property string $category_name
 * @property string $category_describe
 * @property integer $sort
 * @property integer $is_show
 * @property string $create_time
 * @property integer $is_del
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category';
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
            [['parent_id', 'level', 'company_id', 'sort', 'is_show', 'is_del'], 'integer'],
            [['category_describe'], 'string'],
            [['create_time'], 'safe'],
            [['category_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'category_id' => 'Category ID',
            'parent_id' => 'Parent ID',
            'level' => 'Level',
            'company_id' => 'Company ID',
            'category_name' => 'Category Name',
            'category_describe' => 'Category Describe',
            'sort' => 'Sort',
            'is_show' => 'Is Show',
            'create_time' => 'Create Time',
            'is_del' => 'Is Del',
        ];
    }
    
    /*
     * 获取列表
     */
    public static function GetList($company_id = NULL, $parent_id, $category_name = NULL, $review_status, $include_children, $page, $page_size){
    	$returnData = array();

        $offset = ($page-1) * $page_size;

    	$andwhere = ' AND parent_id = '.$parent_id;
    	if($company_id) $andwhere .= ' and company_id = '.$company_id;
    	if($category_name){
    		$andwhere .= " and category_name like '%$category_name%' ";
    	}
    	/*else{
    		$andwhere .= " and level=1 ";
    	}*/

    	$model = new self();
    	$list = $model->find()
            ->where("is_del = 0".$andwhere)
            ->orderBy(['sort'=>SORT_DESC,'category_id'=>SORT_DESC])
            ->asArray()
            ->offset($offset)
            ->limit($page_size)
            ->all();

        $count = $model->find()
            ->where("is_del = 0".$andwhere)
            ->orderBy(['sort'=>SORT_DESC,'category_id'=>SORT_DESC])
            ->asArray()
            ->count();

//    	$parent_category_ids = array();
    	
    	if($list){
    	 	foreach($list as $key=>$value){
//    	 		$list[$key]['parent_category_info'] = array();
//    	 		$list[$key]['children_category_list'] = array();
    	 		/*if($value['parent_id']){
    	 			if(in_array($value['parent_id'], $parent_category_ids)){
    	 				continue;
    	 			}else{
    	 				$parent_category_info = $model->find()->where(['category_id'=>$value['parent_id']])->asArray()->one();
    	 				if($parent_category_info){
    	 					$parent_category_info['goods_count'] = self::GetGoodsCountByCatrgoryId($parent_category_info['category_id']);
    	 					$list[$key]['parent_category_info'] = $parent_category_info;
    	 					$parent_category_ids[] = $parent_category_info['category_id'];
    	 				}
    	 			}
    	 		}*/
    	 		
    	 		$list[$key]['children_count'] = $model->find()->where(['parent_id'=>$value['category_id']])->asArray()->count();
    	 		/*if($children_list){
    	 			foreach($children_list as $children_key=>$children_value){
    	 				$children_list[$children_key]['goods_count'] = self::GetGoodsCountByCatrgoryId($children_value['category_id']);
    	 			}
    	 			$list[$key]['children_category_list'] = $children_list;
    	 			$parent_category_ids[] = $value['category_id'];
    	 		}*/
//    	 		$list[$key]['goods_count'] = self::GetGoodsCountByCatrgoryId($value['category_id']);
    	 		
    	 		$returnData[] = $list[$key];
    	 	}
    	}

    	if($include_children && !empty($returnData)){
    	    foreach ($returnData as $item){
    	        if($item['children_count'] > 0){
                    $_list[] = $item;
                }
            }
        }else{
            $_list = $returnData;
        }

        $return_data = ['count'=>$count, 'list'=>$_list];
        return $return_data;
    }
    
    /*
     * 添加
     */
    public static function AddCategory($company_id = NULL, $cid = 0, $parent_id = '0', $category_name = NULL, $is_show = '0', $sort = '0', $category_describe = ''){
        if($cid){
            $model = self::findOne($cid);
            if(!$model) return false;
        }else{
            $model = new self();
            $model->create_time = date('Y-m-d H:i:s',time());
        }

    	$level = '1';
    	$model->company_id = $company_id;
    	$model->parent_id = $parent_id;
    	if($parent_id > 0 ) $level = $level+1;
    	$model->level = $level;
    	$model->category_name = $category_name;
    	$model->category_describe = $category_describe;
    	$model->sort = $sort;
    	$model->is_show = $is_show;
    	$model->is_del = '0';
    	if($model->save()){
    		return $model->category_id;
    	}else{
    		return false;
    	}
    }
    
    private static function GetGoodsCountByCatrgoryId($category_id = NULL){
    	$count = 0;
    	if($category_id){
    		$goods_model = new CategoryGoodsRelation();
    		$count = $goods_model->find()->select(['relation_id'])->where(['category_id'=>$category_id])->count();
    	}
    	return $count;
    }
    
}
?>