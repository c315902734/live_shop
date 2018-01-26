<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "power".
 *
 * @property integer $power_id
 * @property string $power_name
 * @property integer $create_time
 * @property string $desc
 */
class Power extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'power';
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
            [['power_name'], 'required'],
            [['create_time'], 'integer'],
            [['power_name'], 'string', 'max' => 200],
            [['desc'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'power_id' => 'Power ID',
            'power_name' => 'Power Name',
            'create_time' => 'Create Time',
            'desc' => 'Desc',
        ];
    }
    
    /**
     * 获取所有权限列表
     */
    public static function getPowerList(){
        $list = array();
        $list = static::find()->orderBy("convert(power_name USING gbk) COLLATE gbk_chinese_ci asc")->asArray()->all();
        if(count($list) > 0){
            foreach ($list as $key=>$value){
                $action_list = PowerAction::find()->where(['power_id'=>$value['power_id']])->orderBy("convert(action_name USING gbk) COLLATE gbk_chinese_ci asc")->asArray()->all();
                $list[$key]['action_list'] = $action_list;
            }
        }
        return $list;
    }

    /**
     * 获取权限下功能列表
     */
    public static function getPowerActionList($power_id){
        $list = array();
        $list = PowerAction::find()->where(['power_id'=>$power_id])->orderBy("convert(action_name USING gbk) COLLATE gbk_chinese_ci asc")->asArray()->all();
        return $list;
    }

    /**
     * 新增权限
     */
    public static function addPower($power_name, $action_list){
//        $action_list = json_decode($action_list);
        $power = new Power();
        $power->power_name  = $power_name;
        $power->create_time = time();
        if($power->save()){
            if(!is_array($action_list)){
                $action_list = array();
            }
            if(count($action_list) > 0) {
                $count = 0;
                for ($i = 0; $i < count($action_list); $i++) {
                    $power_action = new PowerAction();
                    $power_action->power_id = $power->power_id;
                    $power_action->action_name = $action_list[$i];
                    $power_action->create_time = time();
                    if ($power_action->save()) {
                        $count++;
                    }
                }
                if ($count > 0) {
                    return true;
                } else {
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
     * 获取角色管理下添加权限数据
     */
    public static function getRolePower($role_id){
        $power_list = Power::find()->select('power_id, power_name')->asArray()->all();
        if(count($power_list) > 0){
            foreach ($power_list as $key=>$value){
                $power_list[$key]['is_checked'] = 0;
                if(isset($role_id) && !empty($role_id)){
                    $role_power_action_list = RolePowerRelation::find()->where(['role_id'=>$role_id, 'power_id'=>$value['power_id']])->asArray()->one();
                    if($role_power_action_list){
                        $power_list[$key]['is_checked'] = 1;
                    }else{
                        $power_list[$key]['is_checked'] = 0;
                    }
                }
                $action_list = PowerAction::find()->where(['power_id'=>$value['power_id']])->select('action_id,action_name')->asArray()->all();
                if(count($action_list) > 0){
                    $action_ids = array();
                    foreach ($action_list as $k=>$v){
                        $action_list[$k]['is_checked'] = 0;
                        if(!empty($role_power_action_list['action_ids'])){
                            $action_ids = explode(',', $role_power_action_list['action_ids']);
                        }
                        if(count($action_ids) > 0 && in_array($v['action_id'], $action_ids)){
                            $action_list[$k]['is_checked'] = 1;
                        }else{
                            $action_list[$k]['is_checked'] = 0;
                        }
                    }
                }
                $power_list[$key]['action_list'] = $action_list;
            }
        }
        return $power_list;
    }
}
