<?php

namespace common\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "power_action".
 *
 * @property integer $action_id
 * @property string $power_id
 * @property string $action_name
 * @property integer $create_time
 * @property string $desc
 */
class PowerAction extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'power_action';
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
            [['power_id', 'action_name'], 'required'],
            [['power_id', 'create_time'], 'integer'],
            [['action_name'], 'string', 'max' => 200],
            [['desc'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'action_id' => 'Action ID',
            'power_id' => 'Power ID',
            'action_name' => 'Action Name',
            'create_time' => 'Create Time',
            'desc' => 'Desc',
        ];
    }

    /**
     * 新增权限功能
     */
    public static function addAction($power_id,$action_list){
        if(!is_array($action_list)){
            $action_list = array();
        }
        if(count($action_list) > 0){
            $count = 0;
            for ($i=0;$i<count($action_list);$i++){
                $power_action = new PowerAction();
                $power_action->power_id = $power_id;
                $power_action->action_name = $action_list[$i];
                $power_action->create_time = time();
                if ($power_action->save()) {
                    $count++;
                }
            }
            if($count == count($action_list)){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /*
     * 根据权限 查看推流人员
     *
     * */
    public static function getSearchList($type){
        if($type == 1){
            $where = "快直播-直播推流-访问指派给自己的直播列表";
            $and_where = "快直播-高级管理-直播推流";
//            $role_where = "快直播-直播推流管理员";
        }else if($type == 2){
            $where = "快直播-图文直播-访问指派给自己的直播列表";
            $and_where = "快直播-高级管理-添加图文消息";
//            $role_where = "快直播-图文直播管理员";
        }
//        $role_and   = "快直播-高级管理员";

        //查看 权限ID
//        $role_ids = \common\models\Role::find()->where("role_name = '".$role_where."' or role_name = '".$role_and."'")->column();

        //查看 对应的权限
        $powers = static::find()->where("action_name = '".$where."' or action_name = '".$and_where."'")->column();

        $res_role = '';
        //查看 所有角色和权限 关联信息
        $role_powers = RolePowerRelation::find()->asArray()->all();
        //匹配 对应的角色id
        foreach ($role_powers as $key=>$val) {
//            if(in_array($val['role_id'], $role_ids)){
                $action_arr = explode(',', $val['action_ids']);
                foreach ($powers as $k => $power_id) {
                    if (in_array($power_id, $action_arr)) {
                        $res_role .= $val['role_id'] . ',';
                    }
                }
//            }
        }
        if(strlen($res_role) > 2) {
            $res_role = substr($res_role, 0,-1);
        }else{
            return false;
        }

        //查看对应的用户信息
        $query = new Query();
        $list = $query
            ->select(["b.admin_id","b.real_name"])
            ->distinct("b.admin_id")
            ->from("vradmin1.admin_role as a")
            ->leftJoin('vradmin1.admin_user as b','b.admin_id = a.admin_id')
            ->where("a.role_id in (".$res_role.") and b.admin_id != ''")
            ->orderBy(['b.admin_id' => SORT_ASC])
            ->createCommand()->queryAll();

        return $list;
    }
    
}
