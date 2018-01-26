<?php
namespace backend\controllers;

use common\models\NewsColumn;
use common\models\NewsColumnType;
use common\models\SpecialColumnType;
use Yii;


/**
 * Column controller
 */
class ColumnController extends PublicBaseController
{

    /**
     * 后台接口：栏目类型
     * type 类型 0栏目类型，1专题分栏
     * type_id 栏目ID 或 专题ID
     * @return string
     */
    public function actionIndex()
    {
        $type     = $this->params['type'] ? $this->params['type'] : 0;
        $type_id  = $this->params['type_id'];
        if(empty($type) && empty($type_id)){
            $this->_errorData("0101", "参数错误");
        }

        if($type == 1){
            //专题分栏
            $special = SpecialColumnType::find()->select("type_id,name")
                ->where(['news_id'=>$type_id,'status'=>1])->orderBy("weight desc,create_time desc")
                ->asArray()->all();
            $this->_successData($special);
        }

        //栏目类型
        $columns = NewsColumnType::find()->select("type_id,name")
            ->where(['column_id'=>$type_id,'status'=>1])
            ->orderBy("weight desc")
            ->asArray()->all();
        $this->_successData($columns);

    }

    /*
     * 采集端 接口  新闻栏目、栏目类型
     *
     * */
    public function actionColumnType(){
        $column = NewsColumn::getAdminColumn();
        $this->_successData($column);
    }



}
