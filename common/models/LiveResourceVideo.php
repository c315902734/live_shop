<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_resource_video".
 *
 * @property string $video_id
 * @property string $live_id
 * @property string $video_name
 * @property string $thumbnail_url
 * @property string $file_name
 * @property string $file_id
 * @property string $input_file_id
 * @property string $video_url
 * @property string $video_url1
 * @property string $video_url2
 * @property string $duration
 * @property integer $height
 * @property integer $height1
 * @property integer $height2
 * @property integer $width
 * @property integer $width1
 * @property integer $width2
 * @property integer $size
 * @property integer $size1
 * @property integer $size2
 * @property integer $category
 * @property integer $play_count
 * @property string $update_time
 * @property integer $status
 * @property string $text
 */
class LiveResourceVideo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_resource_video';
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
            [['video_id', 'live_id'], 'required'],
            [['video_id', 'live_id', 'height', 'height1', 'height2', 'width', 'width1', 'width2', 'size', 'size1', 'size2', 'category', 'play_count', 'status'], 'integer'],
            [['update_time'], 'safe'],
            [['text'], 'string'],
            [['video_name'], 'string', 'max' => 100],
            [['thumbnail_url', 'video_url', 'video_url1', 'video_url2', 'duration'], 'string', 'max' => 200],
            [['file_name'], 'string', 'max' => 300],
            [['file_id', 'input_file_id'], 'string', 'max' => 60],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'video_id' => 'Video ID',
            'live_id' => 'Live ID',
            'video_name' => 'Video Name',
            'thumbnail_url' => 'Thumbnail Url',
            'file_name' => 'File Name',
            'file_id' => 'File ID',
            'input_file_id' => 'Input File ID',
            'video_url' => 'Video Url',
            'video_url1' => 'Video Url1',
            'video_url2' => 'Video Url2',
            'duration' => 'Duration',
            'height' => 'Height',
            'height1' => 'Height1',
            'height2' => 'Height2',
            'width' => 'Width',
            'width1' => 'Width1',
            'width2' => 'Width2',
            'size' => 'Size',
            'size1' => 'Size1',
            'size2' => 'Size2',
            'category' => 'Category',
            'play_count' => 'Play Count',
            'update_time' => 'Update Time',
            'status' => 'Status',
            'text' => 'Text',
        ];
    }

    public static function get_list($live_id){
        $list = static::find()->where(['live_id'=>$live_id, 'status'=>2])->select("video_id,video_name,ifnull(`input_file_id`,`file_id`) as file_id,category,duration,video_url,video_url1,video_url2")->asArray()->all();
        return $list;
    }
}
