<?php

namespace common\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "live_manager".
 *
 * @property string $live_id
 * @property string $name
 * @property integer $weight
 * @property string $start_time
 * @property string $image_url
 * @property integer $introduction_status
 */
class LiveManager extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_manager';
    }

    public static  function getDb()
    {
        return Yii::$app->vrlive;
    }
    

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
			'id' => 'ID',
            'live_id' => 'Live ID',
            'admin_id' => 'Admin ID',
            'admin_name' => 'Admin Name',
            'create_time' => 'Create Time',
        ];
    }

    //查看图文直播列表
    public static function getLists($admin_id,$status,$page,$count){
        $offset = ($page-1)*$count;
        $now_time = date("Y-m-d H:i:s");
        //$fast = "and live.is_fast=0";
        if($status == 0){ //未开始
            $start_time = " and live.status not in(0,2,5) and live.start_time >'".$now_time."'";
        }else if($status == 1){ //直播中
            $start_time = " and live.status not in(0,2,5) and live.start_time < '".$now_time."'";
        }else{
            $start_time = " and live.status in(2,5)";
        }
        $query = new Query();
        $sort_val = 'SORT_ASC';
        if($status == 2){
            $sort_val = 'SORT_DESC';
        }
        //视频类型 查推流信息 图文类型 查图文消息
        $list = $query
            ->select("live.live_id , live.name,live.category,live.creator_id,live.start_time,screen,reviewed_status,amendments")
            ->groupBy(['live.live_id']) //加入去重逻辑，针对多个直播业务员
            ->from("vrlive.live")
            ->leftJoin('vrlive.live_manager','vrlive.live_manager.live_id = vrlive.live.live_id')
            ->where("(vrlive.live_manager.admin_id = " . $admin_id . " and live.reviewed_status = 0 and category in(3,4) ".$start_time.") or (live.reviewed_status != 0 and live.creator_id = ".$admin_id." and category in(3,4) ".$start_time.")")
            ->orderBy([
                'start_time'       => $sort_val,
            ])
            ->offset($offset)
            ->limit($count)
            ->createCommand()->queryAll();
        return $list;
    }

    //直播详情 获取 图文业务员信息
    public static function getPushinfo($live_id){
        $list = LiveManager::find()
            ->select([
                "admin_id",
                "admin_name as real_name",
            ])
            ->where(['live_id'=>$live_id])
            ->asArray()->all();

        return $list;
    }
    
}
