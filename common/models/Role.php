<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "role".
 *
 * @property integer $role_id
 * @property string $role_name
 * @property integer $create_time
 * @property string $desc
 */
class Role extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'role';
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
            [['role_name'], 'required'],
            [['create_time'], 'integer'],
            [['role_name'], 'string', 'max' => 200],
            [['desc'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'role_id' => 'Role ID',
            'role_name' => 'Role Name',
            'create_time' => 'Create Time',
            'desc' => 'Desc',
        ];
    }

    /**
     * 新增角色
     */
    public static function addRole($role_name, $power_list, $is_son_show){
        $role = new Role();
        $role->role_name   = $role_name;
        $role->create_time = time();
        $role->is_son_show = $is_son_show;
        if($role->save()){
            if(!is_array($power_list)){
                $power_list = array();
            }
            if(count($power_list) > 0){
                $count = 0;
                foreach ($power_list as $key=>$value){
                    if(isset($value['action_id']) && count($value['action_id']) > 0){
                        $power_action_id = implode(',', $value['action_id']);
                        $action_ids = rtrim($power_action_id, ',');
                    }else{
                        $action_ids = '';
                    }
                    $relation = new RolePowerRelation();
                    $relation->role_id  = $role->role_id;
                    $relation->power_id = $value['power_id'];
                    $relation->action_ids  = $action_ids;
                    $relation->create_time = time();
                    $relation->save();
                    $count ++;
                }
                if($count > 0){
                    return true;
                }else{
                    return false;
                }
            }else{
                return true;
            }
        }else{
            return false;
        }
    }
    
    /**
     * 获取角色下权限功能列表
     */
    public static function getRolePowerAction($role_id, $power_id){
        //获取该权限下所有功能
        $power_action_list = PowerAction::find()->where(['power_id'=>$power_id])->asArray()->all();
        //获取该角色下权限功能
        $role_power_action_list = RolePowerRelation::find()->where(['role_id'=>$role_id, 'power_id'=>$power_id])->asArray()->one();
        $action_list = array();
        if(!empty($role_power_action_list['action_ids'])){
            $action_list = explode(',', $role_power_action_list['action_ids']);
        }
        if(count($power_action_list) > 0){
            foreach ($power_action_list as $key=>$value){
                if(count($action_list) > 0 && in_array($value['action_id'], $action_list)){
                    $power_action_list[$key]['is_checked'] = 1;
                }else{
                    $power_action_list[$key]['is_checked'] = 0;
                }
            }
        }
        return $power_action_list;
    }
    
    /**
     * 修改角色名称
     */
    public static function editRole($role_id, $role_name, $is_son_show){
        $role_info = Role::findOne($role_id);
        $role_info->role_name   = $role_name;
        $role_info->is_son_show = $is_son_show;
        if($role_info->save()){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 获取角色列表
     */
    public static function getRoleList(){
        $list = Role::find()->orderBy("convert(role_name USING gbk) COLLATE gbk_chinese_ci asc")->asArray()->all();
        if(count($list) > 0){
            foreach ($list as $key=>$value){
                $power_list = RolePowerRelation::find()->alias('r')->leftJoin('vradmin1.power p','r.power_id = p.power_id')->where(['role_id'=>$value['role_id']])->select('p.power_id, p.power_name, r.role_id')->orderBy("convert(power_name USING gbk) COLLATE gbk_chinese_ci asc")->asArray()->all();
                $list[$key]['power_list'] = $power_list;
            }
        }
        return $list;
    }

    /**
     * 获取某角色下权限列表
     */
    public static function getRolePowerList($role_id){
        $power_list = RolePowerRelation::find()->alias('r')->leftJoin('vradmin1.power p','r.power_id = p.power_id')->where(['role_id'=>$role_id])->select('p.power_id, p.power_name,r.role_id')->orderBy("convert(power_name USING gbk) COLLATE gbk_chinese_ci asc")->asArray()->all();
        return $power_list;
    }
}
