<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "activity_lottery".
 *
 * @property string $activity_id
 * @property string $company_id
 * @property string $base_id
 * @property string $title
 * @property integer $security
 * @property string $end_time
 * @property integer $create_time
 */
class ActivityLottery extends \yii\db\ActiveRecord
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
        return 'activity_lottery';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'base_id', 'security', 'create_time'], 'integer'],
            [['end_time'], 'safe'],
            [['title'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'activity_id' => 'Activity ID',
            'company_id' => 'Company ID',
            'base_id' => 'Base ID',
            'title' => 'Title',
            'security' => 'Security',
            'end_time' => 'End Time',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 后台添加编辑大转盘基本信息（不包括奖品
     * @param $company_id
     * @param $title
     * @param $huiwenbi
     * @param $limit_num
     * @param $free_num
     * @param $minimum_huiwenbi
     * @param $security
     * @param $end_time
     */
    public static function addActivity($company_id, $activity_id, $base_id, $title, $cover_img, $security, $status, $end_time){
        if($activity_id){
            $activity_model = self::findOne($activity_id);
        }else{
            $activity_model = new self();
            $activity_model->create_time= time();
        }
        $activity_model->company_id = $company_id;
        $activity_model->base_id    = $base_id;
        $activity_model->title      = $title;
        $activity_model->cover_img  = $cover_img;
        $activity_model->security   = $security;
        $activity_model->status     = $status;
        $activity_model->end_time   = $end_time;
        $ret = $activity_model->save();
        if($ret){
            return $activity_model->attributes['activity_id'];
        }
        return false;
    }

    public static function activityList($company_id = 0, $status, $title, $page, $size){
        $offset = ($page - 1) * $size;

        $and_where = ' 1 = 1';
        if($status) $and_where .= " AND al.status = {$status}";
        if($title) $and_where .= " AND al.title LIKE '%{$title}%'";

        $list = self::find()
            ->alias('al')
            ->select(['al.*', 'alb.base_id', 'alb.company_id', 'alb.cost_huiwenbi', 'alb.limit_num', 'alb.limit_num_type', 'alb.free_num', 'alb.free_num_type'])
            ->leftJoin('vrshop.activity_lottery_base alb', 'al.base_id = alb.base_id')
            ->where(['al.company_id'=>$company_id])
            ->andWhere($and_where)
            ->offset($offset)
            ->limit($size)
            ->orderBy('al.create_time DESC')
            ->asArray()
            ->all();
        $list || $list = [];
        if(!empty($list)){
            foreach ($list as &$item) {
                if ($item['create_time']){
                    $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                }
            }
            unset($item);
        }

        $count = self::find()
            ->alias('al')
            ->where(['company_id'=>$company_id])
            ->andWhere($and_where)
            ->asArray()
            ->count();
        $count || $count = 0;

        return ['count'=>$count, 'list'=>$list];
    }

    /**
     * 有人参加了活动则不能编辑和删除
     * @param $activity_id
     * @return bool
     */
    public static function isParticipateActivities($activity_id){
        $play_count = ActivityLotteryRecord::find()->where(['activity_id'=>$activity_id])->count();
        if ($play_count > 0) {
            return false;
        }
        return true;
    }
}
