<?php

namespace common\models;

use Yii;
use common\behavior\AttachmentBehavior;

/**
 * This is the model class for table "ad_startup_admin".
 *
 * @property integer $id
 * @property string $admin_id
 * @property string $title
 * @property integer $is_active
 * @property string $create_time
 * @property string $update_time
 */
class AdStartupAdmin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ad_startup_admin';
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
            [['admin_id', 'title'], 'required'],
            [['admin_id', 'is_active'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'admin_id' => 'Admin ID',
            'title' => 'Title',
            'is_active' => 'Is Active',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    
    /**
     * 新增广告启动数据
     */
    public static function addAdStartupInfo($title, $image_list, $admin_id)
    {
        AdStartupAdmin::updateAll(['is_active'=>0]);
        $ad_startup = new AdStartupAdmin();
        $ad_startup->title = $title;
        $ad_startup->is_active   = 1;
        $ad_startup->create_time = date('Y-m-d H:i:s', time());
        $ad_startup->admin_id    = $admin_id;
        if($ad_startup->save()){
            $count = count($image_list);
            $num = 0;
            foreach ($image_list as $key=>$value){
                $startup_images = new AdStartupImages();
                $startup_images->weight  = isset($value['weight']) ? $value['weight'] : 0;
                $startup_images->term_id = $value['term_id'] ? $value['term_id'] : $key;
                $startup_images->ad_startup_id = $ad_startup->id;
                $startup_images->file_url      = $value['image_url'];
                $startup_images->link          = $value['link'];
                $startup_images->create_time   = date('Y-m-d H:i:s', time());
                if($startup_images->save()){
                    $num++;
                }
            }
            if($num == $count){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 启动页列表
     */
    public static function getList($title, $admin_name, $create_time, $page, $size)
    {
        $where = ' 1=1';
        if(!empty($title)){
            $where .= " and title like '%$title%'";
        }
        if(!empty($admin_name)){
            $where .= " and username like '%$admin_name%'";
        }
        if(!empty($create_time)){
            $start_time = $create_time.' 00:00:00';
            $end_time   = $create_time.' 23:59:59';
            $where .= " and ad_startup_admin.create_time >= '$start_time' 
            and ad_startup_admin.create_time <= '$end_time'";
        }
        $offset = ($page - 1) * $size;
        $list = static::find()->where($where)
                ->innerJoin('vradmin1.admin_user', 'ad_startup_admin.admin_id = vradmin1.admin_user.admin_id')
                ->select('ad_startup_admin.*, vradmin1.admin_user.username')
                ->orderBy("ad_startup_admin.create_time desc")
                ->offset($offset)
                ->limit($size)->asArray()->all();
        $count = static::find()->where($where)
                ->innerJoin('vradmin1.admin_user', 'ad_startup_admin.admin_id = vradmin1.admin_user.admin_id')
                ->count();
        $result['totalCount'] = $count;
        $result['list']       = $list;
        return $result;
    }

    public static function getAdInfo($id){
        $info = static::find()->where(['ad_startup_admin.id'=>$id])
                ->joinWith('adStartupImages')
                ->select('ad_startup_admin.id, title, admin_id')
                ->asArray()->one();
        return $info;
    }

    /**
     * 更新操作
     */
    public static function updateAdStartupInfo($id, $title, $image_list, $admin_id)
    {
        $ad_startup = AdStartupAdmin::findOne($id);
        $ad_startup->title = $title;
        $ad_startup->is_active   = 1;
        $ad_startup->update_time = date('Y-m-d H:i:s', time());
        $ad_startup->admin_id    = $admin_id;
        if($ad_startup->save()){
            $count = count($image_list);
            if($count > 0){
                AdStartupImages::deleteAll("ad_startup_id = $id");
                $count = count($image_list);
                $num = 0;
                foreach ($image_list as $key=>$value){
                    $startup_images = new AdStartupImages();
                    $startup_images->weight  = isset($value['weight']) ? $value['weight'] : 0;
                    $startup_images->term_id = $value['term_id'] ? $value['term_id'] : $key;
                    $startup_images->ad_startup_id = $ad_startup->id;
                    $startup_images->file_url      = $value['image_url'];
                    $startup_images->link          = $value['link'];
                    $startup_images->update_time   = date('Y-m-d H:i:s', time());
                    if($startup_images->save()){
                        $num++;
                    }
                }
                if($num == $count){
                    return true;
                }else{
                    return false;
                }
            }else{
                return true;
            }
        }else{
            return false;
        }
    }

    /**
     * Ad startup page  has_many images via AdStartupImages.ad_startup_id' -> 'id'
     * @return Response
     */
    public function getAdStartupImages()
    {
        return $this->hasMany(AdStartupImages::className(), ['ad_startup_id' => 'id']);
    }

    /**
     * every startup page belongs to a Admin use via AdStartupAdmin.admin_id' -> 'id'
     * @return Response
     */
    public function getAdminUser()
    {
        return $this->hasOne(AdminUser::className(), ['admin_id' => 'admin_id']);
    }
}
