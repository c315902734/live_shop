<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crowd_funding_gear".
 *
 * @property string $gear_id
 * @property integer $activity_id
 * @property string $title
 * @property string $huiwenbi
 * @property string $description
 * @property string $img
 * @property integer $quota_limit
 * @property integer $quota_num
 * @property integer $support_limit
 * @property integer $support_num
 * @property integer $mail_type
 * @property integer $package_mail
 * @property integer $create_time
 */
class CrowdFundingGear extends \yii\db\ActiveRecord
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
        return 'crowd_funding_gear';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['activity_id', 'quota_limit', 'quota_num', 'support_limit', 'support_num', 'mail_type', 'package_mail', 'create_time'], 'integer'],
            [['huiwenbi'], 'number'],
            [['title', 'img'], 'string', 'max' => 200],
            [['description'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'gear_id' => 'Gear ID',
            'activity_id' => 'Activity ID',
            'title' => 'Title',
            'huiwenbi' => 'Huiwenbi',
            'description' => 'Description',
            'img' => 'Img',
            'quota_limit' => 'Quota Limit',
            'quota_num' => 'Quota Num',
            'support_limit' => 'Support Limit',
            'support_num' => 'Support Num',
            'mail_type' => 'Mail Type',
            'package_mail' => 'Package Mail',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 档位列表
     * @param $activity_id
     * @param $page
     * @param $size
     * @return array|bool
     */
    public static function getGearList($activity_id, $page, $size){
        if(!$activity_id) return false;

        $count = self::find()
            ->where(['activity_id'=>$activity_id])
            ->orderBy('create_time ASC')
            ->asArray()
            ->count();
        $count || $count = 0;

        $offset = ($page - 1) * $size;
        $list = self::find()
            ->where(['activity_id'=>$activity_id])
            ->orderBy('create_time ASC')
            ->offset($offset)
            ->limit($size)
            ->asArray()
            ->all();
        $list || $list = [];

        return ['count'=>$count, 'list'=>$list];
    }
}
