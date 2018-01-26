<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "company_banner".
 *
 * @property string $banner_id
 * @property string $company_id
 * @property string $banner_desc
 * @property string $banner_link
 * @property string $banner_img_url
 * @property integer $banner_sort
 * @property integer $create_time
 */
class CompanyBanner extends \yii\db\ActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->vrshop;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'company_banner';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'banner_sort', 'create_time'], 'integer'],
            [['banner_desc', 'banner_link', 'banner_img_url'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'banner_id' => 'Banner ID',
            'company_id' => 'Company ID',
            'banner_desc' => 'Banner Desc',
            'banner_link' => 'Banner Link',
            'banner_img_url' => 'Banner Img Url',
            'banner_sort' => 'Banner Sort',
            'create_time' => 'Create Time',
        ];
    }
}
