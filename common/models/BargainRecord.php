<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "bargain_record".
 *
 * @property string $record_id
 * @property string $activity_id
 * @property string $user_id
 * @property string $huiwenbi
 * @property string $bargain_huiwenbi
 * @property integer $type
 * @property integer $create_time
 */
class BargainRecord extends \yii\db\ActiveRecord
{
    public static function getDb()
    {
        return yii::$app->vrshop;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bargain_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['activity_id', 'user_id', 'type', 'create_time'], 'integer'],
            [['huiwenbi', 'bargain_huiwenbi'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'record_id' => 'Record ID',
            'activity_id' => 'Activity ID',
            'user_id' => 'User ID',
            'huiwenbi' => 'Huiwenbi',
            'bargain_huiwenbi' => 'Bargain Huiwenbi',
            'type' => 'Type',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * @param $activity_id
     * @param $page
     * @param $size
     * @return array
     */
    public static function getRecordList($activity_id, $page, $size){
        if(!$activity_id) return [];
        $offset = ($page - 1) * $size;

        $count = self::find()
            ->alias('rl')
            ->leftJoin('vruser1.user u', 'rl.user_id = u.user_id')
            ->where(['rl.activity_id'=>$activity_id])
            ->orderBy('rl.create_time DESC')
            ->asArray()
            ->count();
        $count || $count = 0;

        $list = self::find()
            ->alias('rl')
            ->leftJoin('vruser1.user u', 'rl.user_id = u.user_id')
            ->where(['rl.activity_id'=>$activity_id])
            ->orderBy('rl.create_time DESC')
            ->offset($offset)
            ->limit($size)
            ->asArray()
            ->all();
        $list || $list = [];

        return ['count'=>$count, 'list'=>$list];
    }
}
