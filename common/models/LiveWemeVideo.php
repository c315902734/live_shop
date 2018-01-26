<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_weme_video".
 *
 * @property integer $id
 * @property string $video_url
 * @property string $file_id
 * @property string $txy_video_url
 * @property string $create_time
 * @property string $update_time
 */
class LiveWemeVideo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_weme_video';
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
            [['file_id'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['video_url', 'txy_video_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'video_url' => 'Video Url',
            'file_id' => 'File ID',
            'txy_video_url' => 'Txy Video Url',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
