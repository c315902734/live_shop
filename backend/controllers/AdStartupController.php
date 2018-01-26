<?php

namespace backend\controllers;
use  common\models\AdStartupAdmin;
use  common\models\AdStartupImages;
use yii\web\UploadedFile;
use yii;

class AdStartupController extends PublicBaseController
{
    /**
     * 启动页创建广告
     * @param string $title 创建名称
     * @param array  $image_list  = array('image_url','term_id','weight')
     * @param int  $admin_id 当前发布广告管理员ID
     */
    public function actionAdd()
    {
        $title  = isset($this->params['title']) ? $this->params['title'] : '';
        $image_list = isset($this->params['image_list']) ? $this->params['image_list'] : '';
        $admin_id   = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';
        if(!$title || !$image_list)
        {
            $this->_errorData('0001', '参数错误');
        }
        $result = AdStartupAdmin::addAdStartupInfo($title, $image_list, $admin_id);
        if($result){
            $this->_successData('新增成功');
        }else{
            $this->_errorData('0004', '新增失败');
        }
    }

    /**
     * 启动页设置列表
     * @param string $title 创建名称
     * @param string $admin_name 创建人姓名
     * @param string $create_time 创建时间
     * @return string
     */
    public function actionList()
    {
        $title  = isset($this->params['title']) ? $this->params['title'] : '';
        $admin_name  = isset($this->params['admin_name']) ? $this->params['admin_name'] : '';
        $create_time = isset($this->params['create_time']) ? $this->params['create_time'] : '';
        $page        = isset($this->params['page']) ? $this->params['page'] : '1';
        $size        = isset($this->params['size']) ? $this->params['size'] : '20';
        $result = AdStartupAdmin::getList($title, $admin_name, $create_time, $page, $size);
        $this->_successData($result);
    }

    /**
     * 删除启动页广告
     * @param $id
     * @return string
     */
    public function actionDelete()
    {
        $id = isset($this->params['id']) ? $this->params['id'] : '';
        if(!$id)
        {
            $this->_errorData('0001', '参数错误');
        }
        AdStartupAdmin::findOne($id)->delete();
        AdStartupImages::deleteAll("ad_startup_id = $id");
        $this->_successData('删除成功');
    }

    /**
     * 编辑启动页广告
     * @param $id
     * @return string
     */
    public function actionEdit()
    {
        $id = isset($this->params['id']) ? $this->params['id'] : '';
        if(!$id)
        {
            $this->_errorData('0001', '参数错误');
        }
        $result = AdStartupAdmin::getAdInfo($id);
        $this->_successData($result);
    }

    /**
     * 更新操作
     * @param string $title 创建名称
     * @param array  $image_list  = array(array('image_url','term_id','weight'))
     * @return string
     */
    public function actionDoEdit()
    {
        $id    = isset($this->params['id']) ? $this->params['id'] : '';
        $title = isset($this->params['title']) ? $this->params['title'] : '';
        $image_list = isset($this->params['image_list']) ? $this->params['image_list'] : array();
        $admin_id   = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';
        if(!$id || !$title)
        {
            $this->_errorData('0001', '参数错误');
        }
        $ad_startup_info = AdStartupAdmin::findOne($id);
        if(!$ad_startup_info)
        {
            $this->_errorData('0002', '启动页广告不存在');
        }
        $result = AdStartupAdmin::updateAdStartupInfo($id, $title, $image_list, $admin_id);
        $this->_successData($result);
    }


    /**
     * 设置启动页广告状态
     * @param int $id
     * @param int $is_active 是否启用：0:禁用；1：启用
     */
    public function actionSetActive()
    {
        $id        = isset($this->params['id']) ? $this->params['id'] : '';
        $is_active = isset($this->params['is_active']) ? $this->params['is_active'] : '1';
        if(!$id) 
        {
            $this->_errorData('0001', '参数错误');
        }
        $ad_startup_info = AdStartupAdmin::findOne($id);
        if(!$ad_startup_info)
        {
            $this->_errorData('0002', '启动页广告不存在');
        }
        if($is_active == 1)
        {
            AdStartupAdmin::updateAll(['is_active'=>0]);
        }
        $ad_startup_info->is_active = $is_active;
        if($ad_startup_info->save())
        {
            $this->_successData('设置成功');
        }else
        {
            $this->_errorData('0003', '设置失败');
        }
    }

}
