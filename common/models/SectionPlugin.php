<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "area".
 *
 * @property integer $area_id
 * @property string $name
 * @property string $initial
 * @property integer $initial_group
 * @property string $pinyin
 * @property integer $establish_status
 * @property string $establish_time
 * @property integer $disable_status
 */
class SectionPlugin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'section_plugin';
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
            [['section_id', 'plugin_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'section_id' => 'Section ID',
            'plugin_id' => 'Plugin ID'
        ];
    }
    
    //查看是否 有对应插件
    public function getPlugin($section_id,$val){
        $res = SectionPlugin::find()
            ->where(['section_id'=>$section_id,'plugin_id'=>$val])
            ->asArray()->one();
        return $res;
    }

    //查看 直播 现有插件信息
    public function getPlugins($section_id){
        $res = SectionPlugin::find()
            ->leftJoin("plugin a", "a.id = section_plugin.plugin_id")
            ->select("a.id,a.name,section_plugin.name as section_name,section_plugin.image_url,title,content,is_water")
            ->where(['section_id'=>$section_id])->asArray()->all();
        return $res;
    }
    
    //存入 插件信息
    public function insertInfo($section_id,$type,$name='',$image_url='',$title='',$content='',$is_water=0){
        $section_plugin = new SectionPlugin();
        $section_plugin['section_id'] = $section_id;
        $section_plugin['plugin_id']  = $type;
        $section_plugin['name']       = $name;
        $section_plugin['image_url']  = $image_url;
        $section_plugin['title']      = $title;
        $section_plugin['content']    = $content;
        $section_plugin['is_water']   = $is_water;
        $section_plugin->save();
        return 1;
    }
    
    
}
