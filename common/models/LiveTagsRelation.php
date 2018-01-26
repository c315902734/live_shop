<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_tags_relation".
 *
 * @property integer $id
 * @property string $live_id
 * @property integer $tag_id
 * @property integer $type
 * @property string $create_time
 * @property string $creator
 */
class LiveTagsRelation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_tags_relation';
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
            [['live_id', 'tag_id', 'type', 'creator'], 'integer'],
            [['create_time'], 'safe'],
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
            'tag_id' => 'Tag ID',
            'type' => 'Type',
            'create_time' => 'Create Time',
            'creator' => 'Creator',
        ];
    }

    /*
     * 查找 是否有标签记录
     * */
    public function getSection_tags($section_id,$tag_id,$type){
        $section_tag = LiveTagsRelation::find()
            ->where(['live_id' => $section_id,'type'=>$type,'tag_id'=>$tag_id])
            ->asArray()->one();
        return $section_tag;
    }

    /*
     * 查看 直播拥有的 全部标签
     * */
    public function getSection_alltags($section_id){
        $section_tags = LiveTagsRelation::find()
            ->leftJoin("vrlive.live_tags",'live_tags_relation.tag_id = live_tags.id')
            ->where(['live_id' => $section_id,'live_tags_relation.type'=>1])
            ->select("tag_id,tag_name")
            ->asArray()->all();
        return $section_tags;
    }

    /*
     *  存入标签表
     * */
    public function save_tags($section_id,$type_id,$admin_id){
        $live_tag = new LiveTagsRelation();
        $live_tag['live_id'] = $section_id;
        $live_tag['tag_id']  = $type_id;
        $live_tag['type']    = 1;
        $live_tag['create_time'] = date('Y-m-d H:i:s', time());
        $live_tag['creator']     = $admin_id;
        $res = $live_tag->save();
        return $res;
    }
    
    /*
     * 删除 直播的标签
     * */
    public function delSection_tag($section_id,$type,$tag_id)
    {
        $tag_del = LiveTagsRelation::deleteAll(["live_id" => $section_id,"type"=>$type,"tag_id"=>$tag_id]);
        return $tag_del;
    }

    public function getSectiontags()
    {
        return $this->hasOne(LiveSection::className(), ['section_id' => 'live_id']);
    }
    
    

}
