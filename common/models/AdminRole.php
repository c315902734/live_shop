<?php

namespace common\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "admin_role".
 *
 * @property integer $id
 * @property integer $admin_id
 * @property integer $role_id
 * @property integer $create_time
 */
class AdminRole extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_role';
    }
    
    public static function getDb()
    {
        return Yii::$app->vradmin1;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['admin_id', 'role_id'], 'required'],
            [['admin_id', 'role_id', 'create_time'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'admin_id' => 'Admin ID',
            'role_id' => 'Role ID',
            'create_time' => 'Create Time',
        ];
    }
    
    /**
     * 获取管理员权限
     */
    public static function getAdminRole($admin_id){
        $action_list = array();
        $admin_role = static::find()->where(['admin_id'=>$admin_id])->select("admin_id,role_id")->asArray()->all();
        if($admin_role && count($admin_role) > 0){
            $role_ids = array_column($admin_role, 'role_id');
            if(count($role_ids) > 0){
                $role_power_relation = RolePowerRelation::find()->where(['in', 'role_id', $role_ids])->asArray()->all(); //角色权限关系数据
                $action_ids_arr = array_filter(array_column($role_power_relation, 'action_ids'));
                if(count($action_ids_arr) > 0){
                    $action_ids = implode(',', $action_ids_arr);
                    $action_list = PowerAction::find()->where("action_id in (".$action_ids.")")->select("action_id, action_name")->asArray()->all();
                }
            }
        }
        return $action_list;
//        if($admin_role && count($admin_role) > 0){
//            foreach($admin_role as $key=>$value){
//                $role_power_list = array();
//                $role_power_relation = RolePowerRelation::find()->where(['role_id'=>$value['role_id']])->asArray()->all(); //角色权限关系数据
//                if($role_power_relation && count($role_power_relation) > 0){
//                    foreach($role_power_relation as $k=>$val){
//                        $power_list = Power::find()->where(['power_id'=>$val['power_id']])->select("power_id, power_name")->asArray()->all(); //权限信息
//                        if($power_list && count($power_list) > 0) {
//                            foreach ($power_list as $action_key => $action_val) {
//                                if(!empty($val['action_ids'])){
//                                    $action_list = PowerAction::find()->where(['power_id'=>$action_val['power_id']])->andWhere("action_id in (".$val['action_ids'].")")->select("action_id, action_name")->asArray()->all();
//                                }else{
//                                    $action_list = array();
//                                }
//                                $power_list[$action_key]['action_list'] = $action_list;
//                            }
//                        }
//                        $role_power_list[$k] = $power_list;
//                    }
//                    $admin_role[$key]['power_list'] = $role_power_list;
//                }
//            }
//        }
//        return $admin_role;
    }

    /*
     * 查看 账号 创建直播 是否需要审核
     *
     * */
    public static function findRole($creator_id){
        $query = new Query();
        $query->select("role.role_name");
        $query->from('vradmin1.admin_role');
        $query->leftJoin('vradmin1.role','vradmin1.admin_role.role_id = vradmin1.role.role_id');
        $query->where(" admin_role.admin_id =  ".$creator_id);
        $command   = $query->createCommand();
        $info_list = $command->queryAll();
        if($info_list && count($info_list) > 0){
            $role_names = array_column($info_list, 'role_name');
            if(in_array("快直播-高级管理员",$role_names )){
                return 0;
            }else{
                return 1;
            }
        }else{
            return 1;
        }

    }

}
