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
class AdminVersion extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_version';
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
            [['type'], 'integer'],
            [['version'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'version' => 'Version',
            'create_time' => 'Create Time',
        ];
    }
}
