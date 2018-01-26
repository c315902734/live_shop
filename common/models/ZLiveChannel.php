<?php

namespace common\models;

use Yii;
include_once Yii::$app->basePath."/../QcloudApi/QcloudApi.php";
/**
 * This is the model class for table "live_channel".
 *
 * @property integer $channel_id
 * @property string $txy_channel_id
 * @property string $channel_name
 * @property string $channel_describe
 * @property string $manager
 * @property string $manager_phone
 * @property integer $device_type
 * @property string $push_url
 * @property string $pull_url
 * @property string $task_id
 * @property string $create_time
 * @property integer $creator_id
 * @property integer $status
 */
class ZLiveChannel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'z_live_channel';
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
            [['txy_channel_id'], 'required'],
            [['device_type', 'creator_id', 'status'], 'integer'],
            [['create_time'], 'safe'],
            [['txy_channel_id', 'task_id'], 'string', 'max' => 50],
            [['channel_name', 'manager', 'manager_phone'], 'string', 'max' => 45],
            [['channel_describe', 'push_url', 'pull_url'], 'string', 'max' => 200],
            [['txy_channel_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'channel_id' => 'Channel ID',
            'txy_channel_id' => 'Txy Channel ID',
            'channel_name' => 'Channel Name',
            'channel_describe' => 'Channel Describe',
            'manager' => 'Manager',
            'manager_phone' => 'Manager Phone',
            'device_type' => 'Device Type',
            'push_url' => 'Push Url',
            'pull_url' => 'Pull Url',
            'task_id' => 'Task ID',
            'create_time' => 'Create Time',
            'creator_id' => 'Creator ID',
            'status' => 'Status',
        ];
    }

    public static function getTxyId($camera_source_id){
        $channel = static::find()->where(['channel_id'=>$camera_source_id, 'status'=>1])->select("txy_channel_id")->asArray()->one();
        return $channel['txy_channel_id'];
    }

    public static function getLiveChannelList(){
        $list = static::find()->where(['status'=>1])->asArray()->all();
        return $list;
    }

    //获取所以 无输入流的 频道
    public function getLiveChannelNoneList($start_time = ''){
        $config = array(
            'SecretId'       => Yii::$app->params['API_SecretId'],
            'SecretKey'      => Yii::$app->params['API_SecretKey'],
            'RequestMethod'  => 'GET',
            'DefaultRegion'  => Yii::$app->params['API_DefaultRegion']);

        $service = \QcloudApi::load(\QcloudApi::MODULE_LIVE, $config);

        $package = array(
            'pageSize' => 100,
        );
        $a = $service->DescribeLVBChannelList($package);
        if($a == false){
            return "调用腾讯云接口错误：".$service->getError()->getMessage();
        }else{
            $channelSet = $a['channelSet'];
            $result = array();
            if($channelSet && !empty($channelSet)){
                foreach($channelSet as $key=>$value){
                    if($value['channel_status'] != '1'){
                        //无输入流
                        $channel = LiveChannel::find()->where(['txy_channel_id'=>$value['channel_id'],'type'=>1])->asArray()->one();
                        if($channel){
                            $channel['push_url'] = str_replace('MP4','mp4' , $channel['push_url']);
                            //开始时间的前后三小时内 未被占用
                            $ago_three   = date('Y-m-d H:i:s',strtotime($start_time) - 3*60*60); //三小时前
                            $after_three = date('Y-m-d H:i:s',strtotime($start_time) + 3*60*60); //三小时后
                            //查看 使用此频道的直播ID， 直播未开始 或直播中的时间是否 在前后三小时内
                            $list = LiveCameraAngle::find()
                                ->leftJoin('vrlive.live','live.live_id = live_camera_angle.live_id')
                                ->where("signal_source =1 and source_id = ".$channel['channel_id']." and live.status not in(0,2,5) and start_time > '".$ago_three."' and start_time < '".$after_three."'")
                                ->asArray()->one();
                            if($list){
                                continue;
                            }

                            $result[] = $channel;
                            break;
                        }
                    }
                }
            }

            return $result;
        }
    }
    
}
