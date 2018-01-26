<?php

namespace common\models;

use common\behavior\AttachmentBehavior;
use Yii;

/**
 * This is the model class for table "ad_startup_images".
 *
 * @property integer $id
 * @property integer $weight
 * @property integer $term_id
 * @property integer $ad_startup_id
 * @property string $file_url
 * @property string $create_time
 * @property string $update_time
 */
class AdStartupImages extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ad_startup_images';
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
            [['weight', 'term_id', 'ad_startup_id'], 'integer'],
            [['file_url'], 'required'],
            [['create_time', 'update_time'], 'safe'],
            [['file_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'weight' => 'Weight',
            'term_id' => 'Term ID',
            'ad_startup_id' => 'Ad Startup ID',
            'file_url' => 'File Url',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    /**
     * every startup image belongs to a startup page via AdStartupAdmin.admin_id' -> 'id'
     * @return Response
     */
    public function getAdStartupAdmin()
    {
        return $this->hasOne(AdStartupAdmin::classname(), ['id'=>'ad_startup_id']);
    }

//    public function behaviors()
//    {
//        return [
//            // anonymous behavior, behavior class name only
//            'class'         => AttachmentBehavior::className(),
//            //'uploadFiles'   => 'attachmentsToBe',
//            //'uploadedFiles' => 'attachments',
//        ];
//    }

    /**
     * 获取最新一条启动页广告
     */
    public static function getNewAdStartup()
    {
        date_default_timezone_set('PRC');
        $list = AdStartupAdmin::find()->where(['is_active'=>1])->with('adStartupImages')->orderBy('create_time desc')
                ->limit(1)->asArray()->one();
        if($list['adStartupImages'] && count($list['adStartupImages']) > 0)
        {
            $rndKey = array_rand($list['adStartupImages']);
            $theme_set = ThemeSet::find()->asArray()->one();
            $start = strtotime($theme_set['start_time']);
            $end   = strtotime($theme_set['end_time']);
            if($start <= time() && $end > time())
            {
                $list['adStartupImages'][$rndKey]['is_start'] = 1;
            }else{
                $list['adStartupImages'][$rndKey]['is_start'] = 0;
            }
            return $list['adStartupImages'][$rndKey];
        }else{
            return array();
        }
    }
}
