<?php
namespace backend\controllers;

use common\models\AdminRole;
use common\models\AdminUser;
use common\models\NewsColumnType;
use common\models\SpecialColumnType;
use Yii;


/**
 * Column controller
 */
class AdminRoleController extends PublicBaseController
{

    /**
     * 获取管理员权限
     */
    public function actionIndex(){
        $admin_id = isset($this->params['admin_id']) ? $this->params['admin_id'] : '';
        if(!$admin_id){
            $this->_errorData('1001', '参数错误');
        }
        $admin_info = AdminUser::find()->where(['admin_id'=>$admin_id, 'status'=>1]);
        if(!$admin_info){
            $this->_errorData('1002', '管理员不存在');
        }
        $admin_role_info = AdminRole::getAdminRole($admin_id);
        $this->_successData($admin_role_info);
    }

}
