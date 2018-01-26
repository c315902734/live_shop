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
class AreaAdmin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'area_admin';
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
            [['area_id', 'admin_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'area_id' => 'Area ID',
            'admin_id' => 'Admin ID'
        ];
    }
    
    
    
}
