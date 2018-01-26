<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "news_column_type".
 *
 * @property string $type_id
 * @property integer $column_id
 * @property string $name
 * @property integer $weight
 * @property string $create_time
 * @property integer $creator_id
 * @property string $update_time
 * @property integer $status
 */
class NewsColumnType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news_column_type';
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
            [['column_id', 'weight', 'creator_id', 'status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['name'], 'string', 'max' => 45, 'message' => '栏目名称过长！'],
            [['alias'], 'string', 'max' => 45, 'message' => '栏目别名过长！'],
            [['alias'], 'string', 'unique', 'message' => '栏目别名重复！'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'type_id' => 'Type ID',
            'column_id' => 'Column ID',
            'name' => 'Name',
            'alias' => 'Alias',
            'weight' => 'Weight',
            'create_time' => 'Create Time',
            'creator_id' => 'Creator ID',
            'update_time' => 'Update Time',
            'status' => 'Status',
        ];
    }
}
