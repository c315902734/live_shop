<?php

namespace common\models;

use Yii;
use common\models\LiveRobotChat;
use common\models\LiveRobotChatAnswer;
use common\models\LiveRobotChatUser;

/**
 * This is the model class for table "live_robot_chat_setup".
 *
 * @property integer $id
 * @property string $live_id
 * @property string $preheat_starttime
 * @property string $preheat_chat_send_rate
 * @property string $chat_send_rate
 * @property string $create_time
 */
class LiveRobotChatSetup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_robot_chat_setup';
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
            [['live_id'], 'integer'],
            [['preheat_starttime', 'create_time'], 'safe'],
            [['preheat_chat_send_rate', 'chat_send_rate'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'live_id' => 'Live ID',
            'preheat_starttime' => 'Preheat Starttime',
            'preheat_chat_send_rate' => 'Preheat Chat Send Rate',
            'chat_send_rate' => 'Chat Send Rate',
            'create_time' => 'Create Time',
        ];
    }


    /**
     * 聊天室机器人设置
     */
    public static function robot_setup($live_id, $prehead_starttime, $prehead_rate, $chat_rate, $is_open, $endchat_rate)
    {
        $setup = static::find()->where(['live_id' => $live_id])->one();
        if(!$setup)
        {
            $setup = new LiveRobotChatSetup();
        }
        $setup->live_id                = $live_id;
        $setup->is_open                = $is_open;
        $setup->preheat_starttime      = $prehead_starttime;
        $setup->preheat_chat_send_rate = $prehead_rate;
        $setup->chat_send_rate         = $chat_rate;
        $setup->create_time            = date('Y-m-d H:i:s', time());
        $setup->endchat_send_rate      = $endchat_rate;
        if($setup->save())
        {
            return true;
        }else
        {
            return false;
        }
    }
}
