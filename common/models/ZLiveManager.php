<?php

namespace common\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "z_live_manager".
 *
 * @property string $live_id
 * @property string $name
 * @property integer $weight
 * @property string $start_time
 * @property string $image_url
 * @property integer $introduction_status
 */
class ZLiveManager extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'z_live_manager';
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
            'section_id' => 'Section ID',
            'admin_id' => 'Admin ID',
            'admin_name' => 'Admin Name',
            'create_time' => 'Create Time',
        ];
    }

    //查看图文直播列表
    public function getLists($admin_id,$status,$page,$count){
        $offset = ($page-1)*$count;
        $now_time = date("Y-m-d H:i:s");
        if($status == 0){ //未开始
            $start_time = " and live_section.status not in(0,2,5) and live_section.start_time >'".$now_time."'";
        }else if($status == 1){ //直播中
            $start_time = " and live_section.status not in(0,2,5) and live_section.start_time < '".$now_time."'";
        }else{
            $start_time = " and live_section.status in(2,5)";
        }
        $query = new Query();
        $sort_val = 'SORT_ASC';
        if($status == 2){
            $sort_val = 'SORT_DESC';
        }
        //视频类型 查推流信息 图文类型 查图文消息
        $list = $query
            ->select("live_section.section_id as live_id , live_section.title as name,live_section.creator_id,live_section.start_time,screen,reviewed_status,amendments")
            ->from("vrlive.live_section")
            ->leftJoin('vrlive.z_live_manager','vrlive.z_live_manager.section_id = vrlive.live_section.section_id')
            ->leftJoin('vrlive.live_tags_relation','vrlive.live_section.section_id = live_tags_relation.live_id')
            ->where("(vrlive.z_live_manager.admin_id = " . $admin_id . " and live_tags_relation.tag_id = 7 ".$start_time.") or ( live_section.creator_id = ".$admin_id." and live_tags_relation.tag_id = 7 ".$start_time.")")
            ->orderBy([
                'start_time'       => $sort_val,
            ])
            ->offset($offset)
            ->limit($count)
            ->createCommand()->queryAll();
        return $list;
    }

    //直播详情 获取 图文业务员信息
    public function getPushinfo($live_id){
        $list = ZLiveManager::find()
            ->select([
                "admin_id",
                "admin_name as real_name",
            ])
            ->where(['section_id'=>$live_id])
            ->asArray()->all();

        return $list;
    }
    
}
