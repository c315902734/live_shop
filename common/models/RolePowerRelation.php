<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "role_power_relation".
 *
 * @property integer $id
 * @property string $role_id
 * @property string $power_id
 * @property string $action_ids
 * @property integer $create_time
 * @property string $desc
 */
class RolePowerRelation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'role_power_relation';
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
            [['role_id', 'power_id'], 'required'],
            [['role_id', 'power_id', 'create_time'], 'integer'],
            [['action_ids'], 'string', 'max' => 200],
            [['desc'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_id' => 'Role ID',
            'power_id' => 'Power ID',
            'action_ids' => 'Action Ids',
            'create_time' => 'Create Time',
            'desc' => 'Desc',
        ];
    }

    /**
     * 新增角色下权限
     */
    public static function addRolePower($role_id, $power_list){
        if (!is_array($power_list)){
            $power_list = array();
        }

        if(count($power_list) > 0){
            $count = 0;
            $power_id_str = '';
            foreach ($power_list as $key=>$value){
                $power_id_str .= $value['power_id'].',';
                if(isset($value['action_id']) && count($value['action_id']) > 0){
                    $power_action_id = implode(',', $value['action_id']);
                    $action_ids = rtrim($power_action_id, ',');
                }else{
                    $action_ids = '';
                }
                $relation = RolePowerRelation::find()->where(['role_id'=>$role_id, 'power_id'=>$value['power_id']])->one();
                if(!$relation){
                    $relation = new RolePowerRelation();
                    $relation->create_time = time();
                }
                $relation->role_id  = $role_id;
                $relation->power_id = $value['power_id'];
                $relation->action_ids  = $action_ids;
                $relation->save();
                $count ++;
            }
            $power_ids = rtrim($power_id_str, ',');
            RolePowerRelation::deleteAll("power_id not in($power_ids) and role_id=$role_id");

            if($count > 0){
                return true;
            }else{
                return false;
            }
        }else{
            return true;
        }
    }

    /**
     * 编辑角色下权限功能
     */
    public static function addRolePowerAction($role_id, $power_id, $action_list){
        if(!is_array($action_list)){
            $action_list = array($action_list);
        }
        if(isset($action_list) && count($action_list) > 0){
            $power_action_id = implode(',', $action_list);
            $action_ids = rtrim($power_action_id, ',');
        }else{
            $action_ids = '';
        }
        $relation = RolePowerRelation::find()->where(['role_id'=>$role_id, 'power_id'=>$power_id])->one();
        if(!$relation){
            $relation = new RolePowerRelation();
            $relation->role_id  = $role_id;
            $relation->power_id = $power_id;
            $relation->create_time = time();
        }
        $relation->action_ids  = $action_ids;
        if($relation->save()){
            return true;
        }else{
            return false;
        }
    }
}
