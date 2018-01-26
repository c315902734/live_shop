<?php
namespace frontend\controllers;

use common\models\Area;
use common\models\Cities;
use common\models\News;
use common\models\NewsColumn;
use common\service\Pinyin;
use common\service\Record;
use yii;

class NewsColumnController extends PublicBaseController
{

    /**
     * 获取栏目类型
     */
    public function actionColumnType(){
        $area  = isset($_REQUEST['area']) ? $_REQUEST['area'] : '';
        $is_pc = isset($_REQUEST['is_pc']) ? $_REQUEST['is_pc'] : '';
        if(empty($area)) $area = '北京';
        if($area == '黔东南苗族侗族自治州')
        {
            $area = '黔东南';
        }
        $column = NewsColumn::getColumnType($area, $is_pc);
        if(count($column) > 0){
            foreach ($column as $key=>$value){
                if($value['type'] == 2){
                    $column[$key]['ename'] = Pinyin::utf8_to($value['name']);
                }
                if(count($value['columntype']) > 0 && $value['type'] == 2){
                    foreach ($value['columntype'] as $k=>$v){
                        if($v['name'] == '东莞'){
                            $column[$key]['columntype'][$k]['ename'] = 'dongguan';
                        }elseif($v['name'] == '重庆'){
                            $column[$key]['columntype'][$k]['ename'] = 'chongqing';
                        }else {
                            $column[$key]['columntype'][$k]['ename'] = Pinyin::utf8_to($v['name']);
                        }
                    }
                }
            }
        }
        $this->_successData($column);
    }

    /**
     * 获取栏目类型(pc新版新闻栏目)
     */

    public function actionColumnTypeNew(){
        $area     = isset($_REQUEST['area']) ? $_REQUEST['area'] : '';
        $py_area  = isset($_REQUEST['py_area']) ? $_REQUEST['py_area'] : '';
        $is_pc    = isset($_REQUEST['is_pc']) ? $_REQUEST['is_pc'] : '';
        $show_type = isset($_REQUEST['show_type']) ? $_REQUEST['show_type'] : '';
        if(empty($area)) $area = '北京';
        $column = NewsColumn::getColumnType($area, $is_pc, $show_type, $py_area);
        if(count($column) > 0){
            foreach ($column as $key=>$value){
                if($value['type'] == 2){
                    foreach ($value['columntype'] as $k=>$v){
                        $city = Cities::getCities($v['column_id'], $area);
                        $column[$key]['columntype'][$k]['cities'] = $city;

                    }
                }
            }
        }
        $this->_successData($column);
    }

    /**
     * 获取省内所有开通的市
     */
    public function actionGetCities(){
        $column_id  = isset($_REQUEST['column_id']) ? $_REQUEST['column_id'] : '';  //省份ID
        $name       = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';  //市名称
        $city_list  = Cities::getCities($column_id, $name);
//        if(count($city_list) > 0){
//            foreach ($city_list as $key=>$value){
//                if($value['name'] == '东莞'){
//                    $city_list[$key]['ename'] = 'dongguan';
//                }elseif($value['name'] == '重庆'){
//                    $city_list[$key]['ename'] = 'chongqing';
//                }else {
//                    $city_list[$key]['ename'] = Pinyin::utf8_to($value['name']);
//                }
//            }
//        }
        $this->_successData($city_list);
    }
    
    /**
     * 获取地区
     */
    public function actionGetArea(){

        $data = Area::getArena();
        $this->_successData($data);
    }

    /**
     * 获取轮播图
     */
    public function actionGetBanner()
    {
        $type_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $is_area = isset($_REQUEST['is_area']) ? $_REQUEST['is_area'] : 0;
        $num     = isset($_REQUEST['num']) ? $_REQUEST['num'] : 5;
        $pub_type = isset($_REQUEST['pub_type']) ? $_REQUEST['pub_type'] : 0;
        $get_vote = isset($_REQUEST['get_vote']) ? $_REQUEST['get_vote'] : 0;
        $alias = isset($_REQUEST['alias']) ? $_REQUEST['alias'] : '';
        $sub_alias = isset($_REQUEST['sub_alias']) ? $_REQUEST['sub_alias'] : '';
        $is_pc = !empty($_REQUEST['is_pc']) ? $_REQUEST['is_pc'] : '';
        
        //$data_init    = ['info'=>['alias'=>$alias,'sub_alias'=> $sub_alias],'list'=>[]];
        // 必填项判断
        $data = [];
        if (!$type_id  && !$alias) {
            // pc 端判断
            if ($is_pc) {
                $data['info']['message'] = '输入项缺失';
            } else {
                $data = $data;
            }
            $this->_successData($data);
        }
        //调用model逻辑
        $data =  News::getBanner($is_area, $type_id, $num, $pub_type, $get_vote, $alias, $sub_alias, $is_pc);
        $this->_successData($data);
    }


    /*
     * pc端 详情内 相关推荐
     *
     * */
    public function actionRecommend(){
        $column_type = isset($this->params['column_type'])?$this->params['column_type']:0; //栏目类型
        $column_id = isset($this->params['column_id'])?$this->params['column_id']:''; // 栏目/频道 ID
        $page = isset($this->params['page'])?$this->params['page']:'';
        $page = (!empty($page) && $page > 0) ? $page : 1;
        $pageSize = isset($this->params['pageSize'])?$this->params['pageSize']:'10';
        $pageStart = ($page - 1) * $pageSize;
        $pageEnd = $page * $pageSize;

        $returnData = NewsColumn::getSameList($column_type,$column_id, $pageStart, $pageEnd);
        if(!$returnData){
            $this->_errorData('1091', "频道不存在");
        }
        $this->_successData($returnData, "查选成功");
    }
    

}

