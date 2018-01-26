<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "version".
 *
 * @property integer $id
 * @property string $version
 * @property string $url
 * @property integer $create_time
 * @property integer $status
 * @property integer $user_id
 * @property string $info
 * @property integer $update_mode
 * @property integer $system_type
 */
class Version extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'version';
    }
    
    public static function getDb()
    {
        return Yii::$app->vradmin1;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'status', 'user_id', 'update_mode', 'system_type'], 'integer'],
            [['version'], 'string', 'max' => 100],
            [['url'], 'string', 'max' => 200],
            [['info'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'version' => 'Version',
            'url' => 'Url',
            'create_time' => 'Create Time',
            'status' => 'Status',
            'user_id' => 'User ID',
            'info' => 'Info',
            'update_mode' => 'Update Mode',
            'system_type' => 'System Type',
        ];
    }
}
