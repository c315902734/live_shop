<?php
namespace backend\controllers;
use common\models\Power;
use common\models\PowerAction;
use common\models\RolePowerRelation;

class PowerController extends PublicBaseController{

    /**
     * 获取权限列表
     */
    public function actionPowerList(){
        $list = Power::getPowerList();
        $this->_successData($list);
    }

    /**
     * 获取权限下功能列表
     */
    public function actionPowerActionList(){
        $power_id    = isset($this->params['power_id']) ? $this->params['power_id'] : '';  //权限id
        $list = Power::getPowerActionList($power_id);
        $this->_successData($list);
    }

    /**
     * 新增权限
     */
    public function actionAddPower(){
        $power_name  = isset($this->params['power_name']) ? $this->params['power_name'] : '';  //权限名称
        $action_list = isset($this->params['action_list']) ? $this->params['action_list'] : ''; //功能列表
        if(!$power_name){
            $this->_errorData('0001', '参数错误');
        }
        $result = Power::addPower($power_name, $action_list);
        if($result){
            $this->_successData('0000', '添加成功');
        }else{
            $this->_successData('0003', '添加失败');
        }
    }
    
    /**
     * 新增权限功能
     */
    public function actionAddAction(){
        $power_id    = isset($this->params['power_id']) ? $this->params['power_id'] : '';  //权限id
        $action_list = isset($this->params['action_list']) ? $this->params['action_list'] : ''; //功能列表
        if(!$power_id){
            $this->_errorData('0001', '参数错误');
        }
        $power_info = Power::findOne($power_id);
        if(!$power_info){
            $this->_errorData('0002', '权限不存在');
        }
        $result = PowerAction::addAction($power_id, $action_list);
        if($result){
            $this->_successData('0000', '添加成功');
        }else{
            $this->_successData('0003', '添加失败');
        }
    }
    
    /**
     * 修改权限名称
     */
    public function actionUpdatePower(){
        $power_name  = isset($this->params['power_name']) ? $this->params['power_name'] : '';  //权限名称
        $power_id    = isset($this->params['power_id']) ? $this->params['power_id'] : '';  //权限id
        if(!$power_id || !$power_name){
            $this->_errorData('0001', '参数错误');
        }
        $power_info = Power::findOne($power_id);
        if(!$power_info){
            $this->_errorData('0002', '权限不存在');
        }
        $power_info->power_name = $power_name;
        if($power_info->save()){
            $this->_successData('0000', '修改成功');
        }else{
            $this->_errorData('0003', '修改失败');
        }
    }

    /**
     * 修改权限功能名称
     */
    public function actionUpdatePowerAction(){
        $action_id   = isset($this->params['action_id']) ? $this->params['action_id'] : '';  //权限功能id
        $action_name = isset($this->params['action_name']) ? $this->params['action_name'] : '';  //权限功能名称
        $power_id    = isset($this->params['power_id']) ? $this->params['power_id'] : '';  //权限id
        if(!$power_id || !$action_name || !$action_id){
            $this->_errorData('0001', '参数错误');
        }
        $power_action_info = PowerAction::find()->where(['action_id'=>$action_id, 'power_id'=>$power_id])->one();
        if(!$power_action_info){
            $this->_errorData('0002', '该权限下该功能不存在');
        }
        $power_action_info->action_name = $action_name;
        if($power_action_info->save()){
            $this->_successData('0000','修改成功');
        }else{
            $this->_errorData('0003', '修改失败');
        }
    }

    /**
     * 删除权限
     */
    public function actionDelPower(){
        $power_id    = isset($this->params['power_id']) ? $this->params['power_id'] : '';  //权限id
        if(!$power_id){
            $this->_errorData('0001', '参数错误');
        }
        $power_info = Power::findOne($power_id);
        if(!$power_info){
            $this->_errorData('0002', '权限不存在');
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $power_info->delete();  //删除权限
            $power_action_info = PowerAction::find()->where(['power_id'=>$power_id])->count();
            if($power_action_info > 0){
                PowerAction::deleteAll(['power_id'=>$power_id]); //删除该权限下的所有功能
                RolePowerRelation::deleteAll(['power_id'=>$power_id]); //删除角色下的该权限
            }
            $transaction->commit();
            $this->_successData('0000','删除成功');
        } catch (Exception $e) {
            $transaction->rollBack();
            $this->_errorData('0003', '删除失败');
        }
    }

    /**
     * 删除权限下功能
     */
    public function actionDelPowerAction(){
        $action_id   = isset($this->params['action_id']) ? $this->params['action_id'] : '';  //权限功能id
        $power_id    = isset($this->params['power_id']) ? $this->params['power_id'] : '';  //权限id
        if(!$action_id){
            $this->_errorData('0001', '参数错误');
        }
        $power_action_info = PowerAction::find()->where(['action_id'=>$action_id, 'power_id'=>$power_id])->one();
        if(!$power_action_info){
            $this->_errorData('0002', '该权限下该功能不存在');
        }
        $power_action_info = PowerAction::findOne($action_id);
        if(!$power_action_info){
            $this->_errorData('0002', '权限功能不存在');
        }
        $result = PowerAction::findOne($action_id)->delete();
        if($result){
            $relation = RolePowerRelation::find()->where("action_ids like '%$action_id%' ")->asArray()->all();
            if(count($relation) > 0){
                foreach ($relation as $key=>$value){
                    if(strstr($value['action_ids'], ",".$action_id)){
                        $action_id_str = str_replace(",".$action_id, '', $value['action_ids']);
                    }elseif (strstr($value['action_ids'], $action_id.",")){
                        $action_id_str = str_replace($action_id.",", '', $value['action_ids']);
                    }else if(strstr($value['action_ids'], $action_id)){
                        $action_id_str = str_replace($action_id, '', $value['action_ids']);
                    }
                    RolePowerRelation::updateAll(['action_ids'=>$action_id_str], "id=".$value['id']);
                }
            }
        }
        if($result){
            $this->_successData('0000','删除成功');
        }else{
            $this->_errorData('0003', '删除失败');
        }
    }

}