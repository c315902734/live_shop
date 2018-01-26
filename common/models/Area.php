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
class Area extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'area';
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
            [['initial_group', 'establish_status', 'disable_status'], 'integer'],
            [['establish_time'], 'safe'],
            [['name', 'pinyin'], 'string', 'max' => 45],
            [['initial'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'area_id' => 'Area ID',
            'name' => 'Name',
            'initial' => 'Initial',
            'initial_group' => 'Initial Group',
            'pinyin' => 'Pinyin',
            'establish_status' => 'Establish Status',
            'establish_time' => 'Establish Time',
            'disable_status' => 'Disable Status',
        ];
    }

    /**
     * 获取城市
     */
    public static function getArena(){
        $list = static::find()->where(['establish_status'=>1,'disable_status'=>'0'])->select('area_id, name, initial, initial_group, pinyin')
                ->asArray()->all();
        return $list;
    }
    
    
}
