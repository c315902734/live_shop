<?php
namespace backend\controllers;
use common\models\Power;
use common\models\Role;
use common\models\RolePowerRelation;
use Yii;

class RoleController extends PublicBaseController{

    /**
     * 新增角色
     */
    public function actionAddRole(){
        $role_name   = isset($this->params['role_name']) ? $this->params['role_name'] : '';  //角色名称
        $is_son_show = isset($this->params['is_son_show']) ? $this->params['is_son_show'] : '';  //子公司是否可见
        $power_list  = isset($this->params['power_list']) ? $this->params['power_list'] : '';  //权限信息
        if(!$role_name){
            $this->_errorData('0001', '参数错误');
        }
        $result = Role::addRole($role_name, $power_list, $is_son_show);
        if($result){
            $this->_successData('0000', '添加成功');
        }else{
            $this->_successData('0003', '添加失败');
        }
    }

    /**
     * 新增角色下权限
     */
    public function actionAddRolePower(){
        $role_id    = isset($this->params['role_id']) ? $this->params['role_id'] : '';  //角色id
        $power_list = isset($this->params['power_list']) ? $this->params['power_list'] : '';  //权限信息
        if(!$role_id || !$power_list){
            $this->_errorData('0001', '参数错误');
        }
        $role_info = Role::findOne($role_id);
        if(!$role_info){
            $this->_errorData('0002', '角色不存在');
        }
        $result = RolePowerRelation::addRolePower($role_id, $power_list);
        if($result){
            $this->_successData('0000', '添加成功');
        }else{
            $this->_successData('0003', '添加失败');
        }
    }

    /**
     * 编辑角色下权限功能
     */
    public function actionEditRolePowerAction(){
        $role_id   = isset($this->params['role_id']) ? $this->params['role_id'] : '';  //角色id
        $power_id  = isset($this->params['power_id']) ? $this->params['power_id'] : '';  //权限id
        $action_list = isset($this->params['action_list']) ? $this->params['action_list'] : '';  //权限功能id
        if(!$role_id || !$power_id){
            $this->_errorData('0001', '参数错误');
        }
        $role_info = Role::findOne($role_id);
        $power_info = Power::findOne($power_id);
        if(!$role_info || !$power_info){
            $this->_errorData('0002', '角色或权限不存在');
        }
        $role_power_action = RolePowerRelation::find()->where(['role_id'=>$role_id, 'power_id'=>$power_id])->count();
        if($role_power_action == 0){
            $this->_errorData('0002', '该角色下权限不存在');
        }
        $result = RolePowerRelation::addRolePowerAction($role_id, $power_id, $action_list);
        if($result){
            $this->_successData('0000', '添加成功');
        }else{
            $this->_successData('0003', '添加失败');
        }
    }

    /**
     * 编辑权限页面展示
     */
    public function actionGetRolePowerAction(){
        $role_id   = isset($this->params['role_id']) ? $this->params['role_id'] : '';  //角色id
        $power_id  = isset($this->params['power_id']) ? $this->params['power_id'] : '';  //权限id
        if(!$role_id || !$power_id){
            $this->_errorData('0001', '参数错误');
        }
        $role_info = Role::findOne($role_id);
        $power_info = Power::findOne($power_id);
        if(!$role_info || !$power_info){
            $this->_errorData('0002', '角色或权限不存在');
        }
        $list = Role::getRolePowerAction($role_id, $power_id);
        $this->_successData($list);
    }

    /**
     * 角色管理添加权限页面展示
     */
    public function actionGetPower(){
        $role_id   = isset($this->params['role_id']) ? $this->params['role_id'] : '';  //角色id
        $list = Power::getRolePower($role_id);
        $this->_successData($list);
    }

    /**
     * 修改角色名称
     */
    public function actionEditRole(){
        $role_id     = isset($this->params['role_id']) ? $this->params['role_id'] : '';  //角色id
        $role_name   = isset($this->params['role_name']) ? $this->params['role_name'] : '';  //角色名称
        $is_son_show = isset($this->params['is_son_show']) ? $this->params['is_son_show'] : '';  //子公司是否可见
        if(!$role_id || !$role_name){
            $this->_errorData('0001', '参数错误');
        }
        $role_info = Role::findOne($role_id);
        if(!$role_info){
            $this->_errorData('0002', '角色不存在');
        }
        $result = Role::editRole($role_id, $role_name, $is_son_show);
        if($result){
            $this->_successData('0000', '修改成功');
        }else{
            $this->_successData('0003', '修改失败');
        }
    }

    /**
     * 删除角色
     */
    public function actionDelRole(){
        $role_id   = isset($this->params['role_id']) ? $this->params['role_id'] : '';  //角色id
        $role_info = Role::findOne($role_id);
        if(!$role_info){
            $this->_errorData('0002', '角色不存在');
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $role_info->delete();  //删除角色
            $power_action_info = RolePowerRelation::find()->where(['role_id'=>$role_id])->count();
            if($power_action_info > 0){
                RolePowerRelation::deleteAll(['role_id'=>$role_id]); //删除该角色下所有权限
            }
            $transaction->commit();
            $this->_successData('0000','删除成功');
        } catch (Exception $e) {
            $transaction->rollBack();
            $this->_errorData('0003', '删除失败');
        }
    }

    /**
     * 删除角色下权限
     */
    public function actionDelRolePower(){
        $role_id   = isset($this->params['role_id']) ? $this->params['role_id'] : '';  //角色id
        $power_id  = isset($this->params['power_id']) ? $this->params['power_id'] : '';  //权限id
        if(!$role_id || !$power_id){
            $this->_errorData('0001', '参数错误');
        }
        $role_info  = Role::findOne($role_id);
        $power_info = Power::findOne($power_id);
        if(!$role_info || !$power_info){
            $this->_errorData('0002', '角色或权限不存在');
        }
        $role_power_info = RolePowerRelation::find()->where(['role_id'=>$role_id, 'power_id'=>$power_id])->one();
        if(!$role_power_info){
            $this->_errorData('0002', '该角色下该权限不存在');
        }
        $result = $role_power_info->delete();
        if($result){
            $this->_successData('0000', '修改成功');
        }else{
            $this->_successData('0003', '修改失败');
        }
    }
    
    /**
     * 角色列表
     */
    public function actionRoleList(){
        $list = Role::getRoleList();
        $this->_successData($list);
    }

    /**
     * 获取某角色下权限
     */
    public function actionRolePowerList(){
        $role_id   = isset($this->params['role_id']) ? $this->params['role_id'] : '';  //角色id
        if(!$role_id){
            $this->_errorData('0001', '参数错误');
        }
        $role_info = Role::findOne($role_id);
        if(!$role_info){
            $this->_errorData('0002', '角色或权限不存在');
        }
        $list = Role::getRolePowerList($role_id);
        $this->_successData($list);
    }
}