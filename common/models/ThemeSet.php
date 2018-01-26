<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "theme_set".
 *
 * @property integer $id
 * @property string $start_time
 * @property string $end_time
 */
class ThemeSet extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'theme_set';
    }

    public static function getDb(){
        return Yii::$app->vradmin1;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_time', 'end_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
        ];
    }
}
