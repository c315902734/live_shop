<?php
namespace common\models;

use Yii;
use common\models\OauthAccessTokens;
use yii\db\Query;

/**
 * News model
 */
class SpecialColumnType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'special_column_type';
    }


    public static function getDb(){
        return Yii::$app->vrnews1;
    }

    //查看所有分栏
    function GetType($special_id){
        $special_type = new Query();
        $special_all  = $special_type
            ->select("*")->from("vrnews1.special_column_type")
            ->where("weight >=70 and news_id = $special_id")
            ->orderBy("weight desc,create_time desc")
            ->all();
        return $special_all;
    }


}