<?php

namespace backend\controllers;


use common\models\Basket;
use common\models\AdminUser;
use common\models\Live;
use common\models\News;
use Yii;

/**
 * Basket controller
 */
class BasketController extends PublicBaseController
{
    
    
    /*
     * 创建篮子
     *
     * */
    public function actionCreate()
    {
        $type = isset($this->params['basket_type_id']) ? $this->params['basket_type_id'] : '1'; //篮子类型 0: 通用篮子 1:快直播篮子' ",
        $title = isset($this->params['title']) ? $this->params['title'] : ''; //篮子标题
        $description = isset($this->params['description']) ? $this->params['description'] : ''; //篮子描述
        $weight = isset($this->params['weight']) ? $this->params['weight'] : 110; //权重
        $cover_img = isset($this->params['img_url']) ? $this->params['cover_img'] : ''; //封面图
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : ''; //当前管理员ID
        $column_id = isset($this->params['column_id']) ? $this->params['column_id'] : '';     //栏目ID
        $column_type = isset($this->params['column_type']) ? $this->params['column_type'] : '0';    //栏目分类
        if (!$cover_img) {
            $cover_img = "http://vrlive-10047449.image.myqcloud.com/lv1500016289morentu.png";
//            $this->_errorData('0122', "封面图不能为空");
        }
        if (!$admin_id) {
            $this->_errorData('0123', "当前创建者员ID 不能为空");
        }
        if (!$column_id) {
            $this->_errorData('0123', "请传入栏目ID");
        }
        //创建篮子
        $param = new Basket();
        /*print_r($param);
        die();*/
        $param['basket_type_id'] = $type;
        //$param['news_id'] = 0;
        $param['title'] = $title;
        $param['weight'] = $weight;
        $param['description']=$description;
        $param['column_id'] = $column_id;
        $param['column_type'] = $column_type;
        //$param['image_url'] = $cover_img;
        $param['operater_id'] = $admin_id;
        $result = $param->save();
       // print_r($result);die;
        
        if (!$result) {
            $this->_errorData('0128', "创建篮子失败");
        }
        // 篮子创建成功后，创建管理关联新闻
        $news_data = new News();
        $news_data['title'] = $title;
        $news_data['cover_image'] = $cover_img ? $cover_img : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
        $news_data['app_pub'] = 1;
        $news_data['weight'] = $weight;
        $news_data['type'] = 16;//新增新闻类型，16.篮子（集）
        $news_data['create_time'] = date('Y-m-d H:i:s', time());
        $news_data['refresh_time'] = time();
        $news_data['status'] = 100;// 新增新闻状态 100：待开启
        if (!$column_type) {
            $news_data['column_id'] = $column_id;
        } else {
            $news_data['area_id'] = $column_id;
        }
        $insert_news = $news_data->save();
        //关联新闻创建成功后,更新篮子关联新闻id;
        $return = 0;
        if ($insert_news) {
            $param['news_id'] = $news_data->news_id;
            $return = $param->update();
        } else {
            //关联创建失败回滚
            Basket::findOne($param->id)->delete();
            $this->_errorData('0128', '创建篮子关联新闻失败');
        }
        if ($return) {
            
            $this->_successData($return);
        } else {
            //更新失败回滚
            Basket::findOne($param->id)->delete();
            News::findOne($news_data->news_id)->delete();
            $this->_errorData('0128', '更新news_id记录失败');
        }
        
    }
    
    /*
     * 查看篮子
     *
     * */
    public function actionView()
    {
        
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : ''; //当前管理员ID
        $column_id = isset($this->params['column_id']) ? $this->params['column_id'] : '';     //栏目ID
        $column_type = isset($this->params['column_type']) ? $this->params['column_type'] : '0';    //栏目分类
        
        if (!$admin_id) {
            $this->_errorData('0123', "当前创建者员ID 不能为空");
        }
        if (!$column_id) {
            $this->_errorData('0123', "请传入栏目ID");
        }
        $basket=Basket::find()->where(['column_id'=>$column_id,'column_type'=>$column_type])->asArray()->one();
        $this->_successData($basket);
        
    }
    /*
     * 查看篮子内容项列表
     *
     * */
    public function actionBasketItemList()
    {
        
        $news_id = isset($this->params['news_id']) ? $this->params['news_id'] : ''; //当前管理员ID
        $basket = Basket::find()->where(['news_id' => $news_id])->one();
        $page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $size = !empty($_REQUEST['size']) ? $_REQUEST['size'] : 20;
        if (!$news_id) {
            $this->_errorData('0123', "请传入新闻id");
        }
        $return = [];
        if ($basket->basket_type_id == 1 && $basket->is_active == 1) {
            $return = Live::fastliveList($page, $size, $user_id = 1, $is_pc = 0, $basket->column_id, $basket->column_type);
        }
        $this->_successData($return);
        
    }
    
    /*
     * 设置篮子开启/关闭
     *
     * */
    public function actionActive()
    {
        
        $news_id = isset($this->params['news_id']) ? $this->params['news_id'] : ''; //当前管理员ID
        $active = isset($this->params['is_active']) ? $this->params['is_active'] : '1'; //当前管理员ID
        $basket = Basket::find()->where(['news_id' => $news_id])->one();
        if (!$news_id) {
            $this->_errorData('0123', "请传入新闻id");
        }
        $basket->is_active = $active;
        $basket->save();
        $news = News::findOne($basket->news_id);
        if ($active) {
            $news->status = 0; //对应news状态变为已开启
        } else {
            $news->status = 100; //对应news状态变为待开启
        }
        $this->_successData($news->save());
        
        
    }
    
    
    /**
     *编辑篮子（集）
     *同时更新对应新闻的相关字段
     * @return Response
     */
    public function actionEdit($basket_id)
    {
    
    }
    
    /**
     *删除篮子（集）
     *同时删除对应新闻纪录（软删除）
     * @return Response
     */
    public function actionDelete($basket_id)
    {
    
    }
}
