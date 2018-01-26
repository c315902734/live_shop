<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "qrcode_download".
 *
 * @property integer $id
 * @property integer $type
 * @property string $create_time
 */
class QrcodeDownload extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'qrcode_download';
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
            [['type'], 'integer'],
            [['create_time'], 'safe'],
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
            'create_time' => 'Create Time',
        ];
    }
}
