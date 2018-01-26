<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_video".
 *
 * @property string $file_id
 * @property string $live_id
 * @property string $thumbnail_url
 * @property string $file_name
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
 * @property string $text
 * @property string $type
 * @property integer $status
 */
class LiveVideo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_video';
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
            [['file_id', 'live_id'], 'required'],
            [['live_id', 'height', 'height1', 'height2', 'width', 'width1', 'width2', 'size', 'size1', 'size2', 'category', 'play_count', 'type', 'status'], 'integer'],
            [['update_time'], 'safe'],
            [['text'], 'string'],
            [['file_id'], 'string', 'max' => 60],
            [['thumbnail_url', 'video_url', 'video_url1', 'video_url2', 'duration'], 'string', 'max' => 200],
            [['file_name'], 'string', 'max' => 300],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'file_id' => 'File ID',
            'live_id' => 'Live ID',
            'thumbnail_url' => 'Thumbnail Url',
            'file_name' => 'File Name',
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
            'text' => 'Text',
            'type' => 'Type',
            'status' => 'Status',
        ];
    }
}
