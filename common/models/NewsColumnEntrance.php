<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "news_column_entrance".
 *
 * @property integer $entrance_id
 * @property integer $column_id
 * @property integer $area_id
 * @property string $title
 * @property string $subtitle
 * @property string $cover_image
 * @property string $link_url
 * @property string $link_type
 * @property integer $entrance_type_id
 * @property string $entrance_type_title
 * @property string $create_time
 * @property string $status
 * @property integer $weight
 */
class NewsColumnEntrance extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news_column_entrance';
    }
    
    public static function getDb()
    {
        return Yii::$app->vrnews1;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['column_id', 'area_id', 'link_type', 'entrance_type_id', 'status', 'weight'], 'integer'],
            [['create_time'], 'safe'],
            [['title', 'entrance_type_title'], 'string', 'max' => 45],
            [['subtitle'], 'string', 'max' => 10],
            [['cover_image', 'link_url'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'entrance_id' => 'Entrance ID',
            'column_id' => 'Column ID',
            'area_id' => 'Area ID',
            'title' => 'Title',
            'subtitle' => 'Subtitle',
            'cover_image' => 'Cover Image',
            'link_url' => 'Link Url',
            'link_type' => 'Link Type',
            'entrance_type_id' => 'Entrance Type ID',
            'entrance_type_title' => 'Entrance Type Title',
            'create_time' => 'Create Time',
            'status' => 'Status',
            'weight' => 'Weight',
        ];
    }
}
