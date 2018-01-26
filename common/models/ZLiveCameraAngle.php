<?php

namespace common\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "z_live_camera_angle".
 *
 * @property string $camera_id
 * @property string $live_id
 * @property string $name
 * @property integer $signal_source
 * @property string $source_id
 * @property integer $operator_id
 * @property integer $display_order
 * @property integer $status
 */
class ZLiveCameraAngle extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'z_live_camera_angle';
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
            [['section_id', 'signal_source', 'source_id', 'operator_id', 'display_order', 'status'], 'integer'],
            [['name'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'camera_id' => 'Camera ID',
            'section_id' => 'Section ID',
            'name' => 'Name',
            'signal_source' => 'Signal Source',
            'source_id' => 'Source ID',
            'operator_id' => 'Operator ID',
            'display_order' => 'Display Order',
            'status' => 'Status',
        ];
    }

    /**
     * 获取直播机位列表
     */
    public static function getCameraAngleList($liveId){
        $list = static::find()->where(['live_id'=>$liveId, 'status'=>1])->asArray()->all();
        return $list;
    }

    /*
     * 获取 机位的来源ID
     * */
    public static function getSourceId($camera_id){
        $channel = static::find()->where(['camera_id'=>$camera_id, 'signal_source'=>1])->asArray()->select("live_id,source_id")->one();
        return $channel;
    }
    
    /*
     * 获取 推流列表
     * */
    public static function getPushList($admin_id){
        $query = new Query();
        $list = $query
            ->select("live_section.section_id as live_id, live_section.title as name,start_time,push_url")
            ->from("vrlive.z_live_camera_angle")
            ->leftJoin('vrlive.z_live_channel','vrlive.z_live_camera_angle.signal_source = vrlive.z_live_channel.channel_id')
            ->leftJoin('vrlive.live_section','vrlive.z_live_camera_angle.section_id = vrlive.live_section.section_id')
            ->where("vrlive.z_live_camera_angle.operator_id = " . $admin_id . " and vrlive.z_live_camera_angle.signal_source = 1 and vrlive.z_live_camera_angle.status=1 and live_section.status not in(0,2,5)")
            ->orderBy([
                'start_time'       => SORT_ASC,
            ])
            ->createCommand()->queryAll();
        return $list;

    }

    /*
     * 获取视频直播列表
     *
     * */
    public function getCameraLists($admin_id,$status,$page,$count){
        $offset = ($page-1)*$count;
        $now_time = date("Y-m-d H:i:s");
        if($status == 0){ //未开始
            $start_time = " and live_section.start_time >'".$now_time."'";
        }else{ //直播中
            $start_time = " and live_section.start_time < '".$now_time."'";
        }
        $query = new Query();
        //视频类型 查推流信息 图文类型 查图文消息
        $list = $query
            ->select("live_section.section_id as live_id, live_section.title as name,live_section.creator_id,live_section.start_time,push_url,screen,reviewed_status,amendments")
            ->from("vrlive.z_live_camera_angle")
            ->leftJoin('vrlive.z_live_channel','vrlive.z_live_camera_angle.source_id = vrlive.z_live_channel.channel_id')
            ->leftJoin('vrlive.live_section','vrlive.z_live_camera_angle.section_id = vrlive.live_section.section_id')
            ->leftJoin('vrlive.live_tags_relation','vrlive.live_section.section_id = live_tags_relation.live_id')
            ->where("(vrlive.z_live_camera_angle.operator_id = " . $admin_id . " and vrlive.z_live_camera_angle.signal_source = 1 and vrlive.z_live_camera_angle.status=1 and live_tags_relation.tag_id = 5 and live_section.status not in(0,2,5) ".$start_time.") or (live_section.creator_id = ".$admin_id." and vrlive.z_live_camera_angle.signal_source = 1 and vrlive.z_live_camera_angle.status=1 and live_tags_relation.tag_id = 5 and live_section.status not in(0,2,5) ".$start_time .")")
            ->orderBy([
                'start_time' => SORT_ASC,
            ])
            ->offset($offset)
            ->limit($count)
            ->createCommand()->queryAll();
        return $list;
    }

    /*
     * 获取直播 推流业务员 只一个
     * */
    public function getPushinfo($live_id){
        $list = ZLiveCameraAngle::find()
            ->select([
                "z_live_camera_angle.operator_id as admin_id",
                "vradmin1.admin_user.real_name",
            ])
			->leftJoin('vradmin1.admin_user','z_live_camera_angle.operator_id = vradmin1.admin_user.admin_id')
            ->where(['z_live_camera_angle.section_id'=>$live_id])
            ->asArray()->one();

        return $list;
    }





}
