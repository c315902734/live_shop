<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_panel_files".
 *
 * @property integer $id
 * @property string $live_id
 * @property string $msg_id
 * @property string $file_tag_id
 * @property string $remote_file_url
 * @property string $client_file_dir
 * @property string $client_file_name
 * @property string $local_file_rece_dir
 * @property string $local_file_save_dir
 * @property string $local_file_name
 * @property integer $file_flag
 */
class LivePanelFiles extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_panel_files';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['live_id', 'msg_id'], 'required'],
            [['live_id', 'msg_id', 'file_flag'], 'integer'],
            [['file_tag_id'], 'string', 'max' => 64],
            [['remote_file_url', 'client_file_dir', 'client_file_name', 'local_file_rece_dir', 'local_file_save_dir', 'local_file_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'live_id' => 'Live ID',
            'msg_id' => 'Msg ID',
            'file_tag_id' => 'File Tag ID',
            'remote_file_url' => 'Remote File Url',
            'client_file_dir' => 'Client File Dir',
            'client_file_name' => 'Client File Name',
            'local_file_rece_dir' => 'Local File Rece Dir',
            'local_file_save_dir' => 'Local File Save Dir',
            'local_file_name' => 'Local File Name',
            'file_flag' => 'File Flag',
        ];
    }
}
