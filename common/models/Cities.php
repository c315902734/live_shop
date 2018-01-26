<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "cities".
 *
 * @property integer $id
 * @property string $cityid
 * @property string $city
 * @property string $provinceid
 */
class Cities extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cities';
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
            [['cityid', 'city', 'provinceid'], 'required'],
            [['cityid', 'provinceid'], 'string', 'max' => 20],
            [['city'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cityid' => 'Cityid',
            'city' => 'City',
            'provinceid' => 'Provinceid',
        ];
    }

    public static function getCities($province_id, $name){
        $province_info = Provinces::find()->where(['provinceid' => $province_id])->asArray()->one();
        $city_name = Cities::find()->where(['provinceid' => $province_id])->asArray()->all();
        $name_list = array_column($city_name, 'city');
        $city_list = Area::find()->where(['in', 'name', $name_list])->andWhere(['establish_status'=>1,'disable_status'=>'0'])->asArray()->all();//获取已开通的地区信息
        $news_list = array();
        if($city_list && count($city_list) > 0){
            foreach ($city_list as $k=>$v){
                if($province_info['province'] == $v['name']&& $v['name']!='吉林'){
                    continue;
                }
                if($v['name'] == $name){
                    $news_list[$k]['is_light'] = '1';
                }else{
                    $news_list[$k]['is_light'] = '0';
                }
                $news_list[$k]['column_id'] = $v['area_id'];
                $news_list[$k]['name']  = $v['name'];
                $news_list[$k]['type']  = 2;
                $news_list[$k]['ename'] = $v['pinyin'];
            }
        }
        return $news_list;
    }
}
