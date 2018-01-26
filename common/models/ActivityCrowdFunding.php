<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "activity_crowd_funding".
 *
 * @property string $activity_id
 * @property integer $company_id
 * @property string $title
 * @property string $category
 * @property string $tag
 * @property string $abstract
 * @property string $cover_img
 * @property string $introduce
 * @property integer $day
 * @property string $huiwenbi
 * @property integer $return_time
 * @property integer $create_time
 */
class ActivityCrowdFunding extends \yii\db\ActiveRecord
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
        return 'activity_crowd_funding';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'day', 'return_time', 'create_time'], 'integer'],
            [['introduce'], 'string'],
            [['huiwenbi'], 'number'],
            [['title', 'tag', 'cover_img'], 'string', 'max' => 200],
            [['category'], 'string', 'max' => 10],
            [['abstract'], 'string', 'max' => 255],
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
            'title' => 'Title',
            'category' => 'Category',
            'tag' => 'Tag',
            'abstract' => 'Abstract',
            'cover_img' => 'Cover Img',
            'introduce' => 'Introduce',
            'day' => 'Day',
            'huiwenbi' => 'Huiwenbi',
            'return_time' => 'Return Time',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 后台创建活动   第一步
     * @param $company_id
     * @param $activity_id
     * @param $title
     * @param $category
     * @param $tag
     * @param $abstract
     * @param $cover_img
     * @return bool
     */
    public static function addActivitySetupOne($company_id, $activity_id, $title, $category, $tag, $abstract, $cover_img){
        $activity_model = new self();
        if($activity_id){
            $activity_model = self::findOne($activity_id);
        }
        $activity_model->company_id = $company_id;
        $activity_model->title      = $title;
        $activity_model->category   = $category;
        $activity_model->tag        = $tag;
        $activity_model->abstract   = $abstract;
        $activity_model->cover_img  = $cover_img;
        $activity_model->create_time= time();

        $ret = $activity_model->save();
        if($ret){
            return $activity_model->attributes['activity_id'];
        }
        return false;
    }

    /**
     * 后台创建活动   第er步
     * @param $activity_id
     * @param $introduce
     * @return bool
     */
    public static function addActivitySetupTwo($activity_id, $introduce){
        $activity_info = self::findOne($activity_id);
        if(!$activity_info) return false;

        $activity_info->introduce = $introduce;
        return $activity_info->save();
    }

    /**
     * 后台创建活动 第三步
     * @param $activity_id
     * @param $title
     * @param $huiwenbi
     * @param $description
     * @param $img
     * @param $quota_limit
     * @param $quota_num
     * @param $support_limit
     * @param $support_num
     * @param $mail_type
     * @param $package_mail
     * @return bool
     */
    public static function addActivitySetupThree($activity_id, $gear_id, $title, $huiwenbi, $description, $img, $quota_limit, $quota_num, $support_limit, $support_num, $mail_type, $package_mail){
        if($gear_id){
            $crowd_funding_model = CrowdFundingGear::findOne($gear_id);
        }else{
            $crowd_funding_model = new CrowdFundingGear();
            $crowd_funding_model->create_time = time();
        }

        $crowd_funding_model->activity_id = $activity_id;
        $crowd_funding_model->title = $title;
        $crowd_funding_model->huiwenbi = $huiwenbi;
        $crowd_funding_model->description = $description;
        $crowd_funding_model->img = $img;
        $crowd_funding_model->quota_limit = $quota_limit;
        $crowd_funding_model->quota_num = $quota_num;
        $crowd_funding_model->support_limit = $support_limit;
        $crowd_funding_model->support_num = $support_num;
        $crowd_funding_model->mail_type = $mail_type;
        $crowd_funding_model->package_mail = $package_mail;

        $ret = $crowd_funding_model->save();
        if(!$ret){
            $err = $crowd_funding_model->getErrors();
            var_dump($err);die;
        }
        return true;
    }

    /**
     * @param $activity_id
     * @param $day
     * @param $huiwenbi
     * @param $return_time
     * @return bool
     */
    public static function addActivitySetupFour($activity_id, $day, $huiwenbi, $return_time){
        $crowd_funding_model = self::findOne($activity_id);
        if(!$crowd_funding_model) return false;

        $crowd_funding_model->day = $day;
        $crowd_funding_model->huiwenbi = $huiwenbi;
        $crowd_funding_model->return_time = $return_time;
        return $crowd_funding_model->save();
    }

    public static function activityList($company_id, $page, $size){
        $offset = ($page - 1) * $size;
        $count = ActivityCrowdFunding::find()
            ->where(['company_id'=>$company_id])
            ->orderBy('create_time DESC')
            ->asArray()
            ->count();
        $count || $count = 0;

        $list = ActivityCrowdFunding::find()
            ->where(['company_id'=>$company_id])
            ->orderBy('create_time DESC')
            ->offset($offset)
            ->limit($size)
            ->asArray()
            ->all();
        $list || $list = [];

        return ['count'=>$count, 'list'=>$list];
    }
}
