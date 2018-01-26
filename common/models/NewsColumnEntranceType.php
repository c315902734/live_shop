<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "news_column_entrance_type".
 *
 * @property integer $type_id
 * @property string $title
 * @property integer $num
 * @property string $type
 */
class NewsColumnEntranceType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news_column_entrance_type';
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
            [['num', 'type'], 'integer'],
            [['title'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'type_id' => 'Type ID',
            'title' => 'Title',
            'num' => 'Num',
            'type' => 'Type',
        ];
    }
}
