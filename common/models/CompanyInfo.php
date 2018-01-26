<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "company_info".
 *
 * @property string $company_id
 * @property string $company_name
 * @property string $company_contact_person
 * @property string $company_phone
 * @property string $company_email
 * @property integer $create_time
 */
class CompanyInfo extends \yii\db\ActiveRecord
{

    public static function getDb(){
        return Yii::$app->vrshop;
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'company_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time'], 'integer'],
            [['company_name', 'company_contact_person', 'company_phone', 'company_email'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'company_id' => 'Company ID',
            'company_name' => 'Company Name',
            'company_contact_person' => 'Company Contact Person',
            'company_phone' => 'Company Phone',
            'company_email' => 'Company Email',
            'create_time' => 'Create Time',
        ];
    }

    public static function companyInfo($company_id = 1){
        $company_info = self::find()->select(['company_name', 'company_contact_person', 'company_phone', 'company_email'])->where(['company_id'=>$company_id])->asArray()->one();
        return $company_info;
    }
}
