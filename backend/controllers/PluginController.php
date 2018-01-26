<?php

namespace backend\controllers;

use common\models\Plugin;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * LiveSection controller
 */
class PluginController extends PublicBaseController
{
    /*
     * 创建插件（系统级别）
     *
     * */
    public function actionAdd()
    {
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : ''; //当前管理员ID
        $name = isset($this->params['name']) ? $this->params['name'] : ''; //当前插件名称
        $status = isset($this->params['stauts']) ? $this->params['stauts'] : '1'; //当前插件状态 0：禁用 1：启用
        if (!$admin_id) {
            $this->_errorData('0123', "当前管理员ID 不能为空");
        }
        if ($admin_id != 1) {
            $this->_errorData('0123', "非系统管理员 不能操作插件库");
            
        } else {
            $values = [
                'name'   => $name,
                'status' => $status
            ];
            $plugin = new Plugin();
            $plugin->name = $values['name'];
            $plugin->status = $values['status'];
            $result = $plugin->save();
            if ($result) {
                $this->_successData('创建插件成功');
            } else {
                $this->_errorData('0123', '创建插件失败');
            }
        }
    }
    
    /*
     * 插件列表
     *
     * */
    public function actionList()
    {
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : ''; //当前管理员ID
        if (!$admin_id) {
            $this->_errorData('0123', "当前用户ID 不能为空");
        }
        $plugins = Plugin::find()->asArray()->all();
        $this->_successData($plugins);
        
    }
    
    /*
     * 启用、禁用插件（系统级别）
     *
     * */
    public function actionActive()
    {
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : ''; //当前管理员ID
        $plugin_id = isset($this->params['plugin_id']) ? $this->params['plugin_id'] : ''; //当前插件ID
        $status = isset($this->params['stauts']) ? $this->params['stauts'] : ''; //当前插件状态 0：禁用 1：启用
        if (!$admin_id) {
            $this->_errorData('0123', "当前用户ID 不能为空");
        }
        if (!$plugin_id) {
            $this->_errorData('0123', "请输入插件ID");
        }
        if (!$status) {
            $this->_errorData('0123', "请输入插件插件状态");
        }
        $plugin = Plugin::findOne($plugin_id);
        $plugin->status = $status;
        $result = $plugin->save();
        $value = ['禁用', '启用'];
        if ($result) {
            $this->_successData($value[$status] . '插件成功');
        } else {
            $this->_errorData('0123', $value[$status] . '插件失败');
        }
    }
    
    /*
     * 删除插件（系统级别）
     *
     * */
    public function actionDelete()
    {
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : ''; //当前管理员ID
        $plugin_id = isset($this->params['plugin_id']) ? $this->params['plugin_id'] : ''; //当前插件名称
        $status = isset($this->params['stauts']) ? $this->params['stauts'] : ''; //当前插件状态 0：禁用 1：启用
        if (!$admin_id) {
            $this->_errorData('0123', "当前管理员ID 不能为空");
        }
        if (!$plugin_id) {
            $this->_errorData('0123', "请输入插件ID");
        }
        if ($admin_id != 1) {
            $this->_errorData('0123', "非系统管理员 不能操作插件库");
        }
        $plugin = Plugin::findOne($plugin_id);
        $name = $plugin->name;
        $result = $plugin->delete();
        if ($result) {
            $this->_successData('删除插件"' . $name . '"成功');
        } else {
            $this->_errorData('0123', '删除插件"' . $name . '"失败');
        }
    }
    
}
