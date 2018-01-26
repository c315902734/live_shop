<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "sys_menu_admin".
 *
 * @property integer $id
 * @property integer $menu_id
 * @property integer $admin_id
 * @property string $company_id
 */
class SysMenuAdmin extends \yii\db\ActiveRecord
{
    public static function getDb()
    {
        return yii::$app->vradmin1;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sys_menu_admin';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['menu_id', 'admin_id', 'company_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'menu_id' => 'Menu ID',
            'admin_id' => 'Admin ID',
            'company_id' => 'Company ID',
        ];
    }
}
