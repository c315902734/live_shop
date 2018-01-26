<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "entry_type".
 *
 * @property integer $entry_type_id
 * @property integer $title
 * @property integer $parent_id
 */
class EntryType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'entry_type';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('vrnews1');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title', 'parent_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'entry_type_id' => 'Entry Type ID',
            'title' => 'Title',
            'parent_id' => 'Parent ID',
        ];
    }
}
