<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_address".
 *
 * @property string $address_id
 * @property string $company_id
 * @property string $user_id
 * @property integer $create_time
 * @property integer $is_default
 * @property string $id_number
 * @property string $phone
 * @property string $zipcode
 * @property string $address
 * @property string $county
 * @property string $city
 * @property string $prov
 * @property string $consignee
 */
class UserAddress extends \yii\db\ActiveRecord
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
        return 'user_address';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'user_id', 'create_time', 'is_default'], 'integer'],
            [['id_number'], 'string', 'max' => 50],
            [['phone', 'zipcode', 'city', 'prov'], 'string', 'max' => 20],
            [['address'], 'string', 'max' => 200],
            [['county'], 'string', 'max' => 30],
            [['consignee'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'address_id' => 'Address ID',
            'company_id' => 'Company ID',
            'user_id' => 'User ID',
            'create_time' => 'Create Time',
            'is_default' => 'Is Default',
            'id_number' => 'Id Number',
            'phone' => 'Phone',
            'zipcode' => 'Zipcode',
            'address' => 'Address',
            'county' => 'County',
            'city' => 'City',
            'prov' => 'Prov',
            'consignee' => 'Consignee',
        ];
    }
}
