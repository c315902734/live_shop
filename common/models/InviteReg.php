<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "invite_reg".
 *
 * @property integer $id
 * @property string $images
 * @property string $share_titile
 * @property string $share_abstract
 * @property string $share_img
 * @property integer $create_time
 * @property integer $create_id
 */
class InviteReg extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'invite_reg';
    }

    public static function getDb()
    {
        return Yii::$app->vruser1;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'create_id'], 'integer'],
            [['images', 'share_title', 'share_abstract', 'share_img'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'images' => 'Images',
            'share_titile' => 'Share Titile',
            'share_abstract' => 'Share Abstract',
            'share_img' => 'Share Img',
            'create_time' => 'Create Time',
            'create_id' => 'Create ID',
        ];
    }

    /**
     * 新增邀请注册信息
     */
    public static function addInviteReg($images, $share_title, $share_abstract, $share_img, $create_id){
        $invite_reg = new InviteReg();
        $invite_reg->images = $images;
        $invite_reg->share_title   = $share_title;
        $invite_reg->share_abstract = $share_abstract;
        $invite_reg->share_img = $share_img;
        $invite_reg->create_id = $create_id;
        $invite_reg->create_time = time();
        if($invite_reg->save()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取邀请注册列表
     */
    public static function getInviteList($page, $size){
        $offset = ($page - 1)*$size;
        $list = InviteReg::find()->leftJoin('vradmin1.admin_user a','a.admin_id=invite_reg.create_id')->select('invite_reg.*, a.username')->orderBy('create_time desc')->offset($offset)->limit($size)->asArray()->all();
        $count = InviteReg::find()->leftJoin('vradmin1.admin_user a','a.admin_id=invite_reg.create_id')->orderBy('invite_reg.create_time desc')->count();
        if($list && count($list) > 0){
            foreach ($list as $key=>$value){
                $list[$key]['create_time'] = date('Y-h-m H:i:s', $value['create_time']);
            }
        }
        $return['list'] = $list;
        $return['totalCount'] = $count;
        return $return;
    }

    /**
     * 获取某条邀请注册详情
     */
    public static function getInviteInfo($id){
        $invite_info = static::find()->where(['id'=>$id])->asArray()->one();
        return $invite_info;
    }

    /**
     * 获取某条邀请注册详情
     */
    public static function getNewInvite(){
        $invite_info = static::find()->orderBy('create_time desc')->select("images, share_title, share_abstract, share_img")->asArray()->one();
        return $invite_info;
    }
}
