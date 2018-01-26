<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_user_subscribe".
 *
 * @property string $subscribe_id
 * @property string $user_id
 * @property string $live_id
 * @property string $create_time
 * @property string $update_time
 * @property integer $status
 */
class LiveUserSubscribe extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_user_subscribe';
    }
    
    public static function getDb()
    {
        return Yii::$app->vrlive;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'live_id', 'status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'subscribe_id' => 'Subscribe ID',
            'user_id' => 'User ID',
            'live_id' => 'Live ID',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'status' => 'Status',
        ];
    }

    /**
     * 新增直播预约
     * @param $user_id
     * @param $live_id
     */
    public static function addSubscribe($live_id, $user_id, $status){
        $live_info = Live::findOne($live_id);
        $user_info = User1::findOne($user_id);
        if(!$live_info || !$user_info){
            return false;
        }
        $subscribe_live = new LiveUserSubscribe();
        $is_subscribe = static::find()->where(['live_id'=>$live_id, 'user_id'=>$user_id])->one();
        if(!empty($is_subscribe)){
            if($is_subscribe->status == $status){
                return false;
            }else{
                $is_subscribe->status = $status;
                $is_subscribe->save(); // 根据条件保存修改的数据
                return true;
            }
        }else{
            $subscribe = new LiveUserSubscribe();
            $subscribe->live_id = $live_id;
            $subscribe->user_id = $user_id;
            $subscribe->push_status = 0;
            $subscribe->create_time = date('Y-m-d H:i:s', time());
            $subscribe->save();
            return true;
        }
    }
}
