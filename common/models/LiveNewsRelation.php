<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_news_relation".
 *
 * @property string $relation_id
 * @property string $live_id
 * @property string $news_id
 * @property string $type
 * @property integer $weight
 */
class LiveNewsRelation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_news_relation';
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
            [['live_id', 'news_id', 'weight'], 'integer'],
            [['type'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'relation_id' => 'Relation ID',
            'live_id' => 'Live ID',
            'news_id' => 'News ID',
            'type' => 'Type',
            'weight' => 'Weight',
        ];
    }

    /**
     * 获取直播相关新闻
     * @param $live_id 直播id
     */
    public static function newsRelation($live_id){
        $live_list = array();
        $news_list = array();
        $relation_list = static::find()->where(['live_id'=>$live_id])->asArray()->all();
        if(!empty($relation_list)){
            $live = '';
            $news = '';
            foreach($relation_list as $key=>$val){
                if($val['type'] == 'live'){
                    $live .= $val['news_id'] . ',';
                }else{
                    $news .= $val['news_id'] . ',';
                }
            }
            if(!empty($live)) {
                $live = rtrim($live, ',');
                $live_list = static::find()->innerJoin("vrlive.live_resources", "live.live_id = live_resources.resource_id")
                    ->where("live_id in ($live)")->select("live.live_id as news_id, live.name as title, '' as subtitle,
                    '' as source_name,live.image_url as cover_img,live.type,'' as full_status,'' as full_title,
                    '' as full_subtitle,'' as full_cover_image, 2 as reference_type,'' as content,0 as play_count,
                    live.image_url,live.create_time,live_resources.duration,live.category")->asArray()->all();
            }

            if(!empty($news)){
                $news    = rtrim($news, ',');
                $news_list = News::find()->leftJoin("vrnews1.news_video","news_video.news_id = news.news_id")
                    ->where("news.news_id in ($news) and news.status=0")
                    ->select('news.news_id, title, subtitle, source_name,create_time, cover_image, type,
                    full_status, full_title, full_subtitle, full_cover_image, news_video.thumbnail_url as image_url, news_video.duration,
                    reference_type,news.content,news_video.play_count')->asArray()->all();
                if (!empty($news_list)){
                    foreach ($news_list as $key=>$value){
                        $news_list[$key]['category'] = 0;
                    }
                }
            }
        }

        $result = array_merge($news_list, $live_list);
        return $result;
    }
}
