<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "section_user_verify".
 *
 * @property integer $id
 * @property string $section_id
 * @property integer $user_id
 */
class SectionUserVerify extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'section_user_verify';
    }

    public static function getDb()
    {
        return Yii::$app->vrlive;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['section_id', 'user_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'section_id' => 'Section ID',
            'user_id' => 'User ID',
        ];
    }

    /*
     * 查找 用户是否有验证记录
     * */
    public function getUser_verify($section_id,$user_id){
        $user_verify = SectionUserVerify::find()
            ->where(['section_id' => $section_id,'user_id'=>$user_id])
            ->asArray()->one();
        return $user_verify;
    }
    
    /*
     *  存入用户验证 表
     * */
    public function save_verify($section_id,$user_id){
        $user_verify = new SectionUserVerify();
        $user_verify['section_id'] = $section_id;
        $user_verify['user_id']  = $user_id;
        $res = $user_verify->save();
        return $res;
    }
    

}
