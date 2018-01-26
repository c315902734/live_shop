<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use common\models\OauthAccessTokens;

/**
 * This is the model class for table "area_city".
 *
 * @property integer $area_id
 * @property integer $online
 * @property string $zone_code
 * @property string $parent_code
 * @property integer $city_level
 * @property string $city_name
 * @property string $city_code
 * @property string $create_time
 * @property string $update_time
 * @property integer $status
 */
class AreaCity extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'area_city';
    }
    
    public static function getDb(){
    	return Yii::$app->vruser1;
    }

    public static function getCityName($provinceId)
    {
        if ($provinceId <= 0) return "";
        $province = AreaCity::find()->where(["area_id"=>$provinceId ])->asArray()->one();
        $provinceName = (empty($province) || $province['area_id'] <= 0) ? "" : $province['city_name'] . "";
        return $provinceName;
    }

    public static function getProvinceName($provinceId)
    {
        if ($provinceId <= 0) return "";
        $province = self::find()->where(['area_id'=>$provinceId,'city_level'=>'1'])->asArray()->one();
        $provinceName = (empty($province) || $province['area_id'] <= 0) ? "" : $province['city_name'] . "";
        return $provinceName;
    }

    public static function getProvinceList()
    {
        $returnArr=array();
        $province = static::find()->where(['city_level'=>1,'online'=>1,'status'=>1])->asArray()->all();
        foreach($province as $k=>$v){
            $returnArr[]=array("area_id"=>$v['area_id'],"area_name"=>$v['city_name'],"area_code"=>$v['city_code']);
        }
        return $returnArr;
    }

    public static function getCityList($provinceCode)
    {
        $returnArr=array();
        $cityList = static::find()->where(['parent_code'=>$provinceCode,'online'=>1,'status'=>1])->asArray()->all();
        foreach($cityList as $k=>$v){
            $returnArr[]=array("area_id"=>$v['area_id'],"area_name"=>$v['city_name'],"area_code"=>$v['city_code']);
        }
        return $returnArr;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['online', 'city_level', 'status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['zone_code', 'parent_code', 'city_code'], 'string', 'max' => 32],
            [['city_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'area_id' => 'Area ID',
            'online' => 'Online',
            'zone_code' => 'Zone Code',
            'parent_code' => 'Parent Code',
            'city_level' => 'City Level',
            'city_name' => 'City Name',
            'city_code' => 'City Code',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'status' => 'Status',
        ];
    }
    

}
