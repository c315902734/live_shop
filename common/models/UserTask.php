<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_task".
 *
 * @property string $user_task
 * @property string $user_id
 * @property string $task_id
 * @property string $created_at
 * @property integer $amount
 */
class UserTask extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_task';
    }

    public static function getDb(){
        return Yii::$app->vruser1;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_task'], 'required'],
            [['user_task', 'user_id', 'task_id', 'amount'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_task' => 'User Task',
            'user_id' => 'User ID',
            'task_id' => 'Task ID',
            'created_at' => 'Created At',
            'amount' => 'Amount',
        ];
    }

    /**
     * 判断用户是否已签到
     */
    public static function isSign($user_id){
        $task_id = 1;
        $start_time = date('Y-m-d',time()).' 00:00:00';
        $end_time   = date('Y-m-d',time()).' 23:59:59';
        $sign_info  = static::find()->where(['user_id' => $user_id, 'task_id' => $task_id])
                    ->andWhere(['>=', 'created_at', $start_time])
                    ->andWhere(['<', 'created_at', $end_time])->count();
        return $sign_info;
    }
}
