<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_panel_user_manage".
 *
 * @property integer $id
 * @property string $live_id
 * @property string $create_time
 * @property string $update_time
 * @property string $pic_txt_content
 * @property integer $sort_number
 * @property string $creator_id
 * @property string $creator_name
 * @property string $creator_nickname
 * @property string $creator_avatar
 */
class LivePanelUserManage extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'live_panel_user_manage';
	}

	public static function getDb()
	{
		return yii::$app->vrlive;
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
				[['live_id'], 'required'],
				[['live_id', 'sort_number', 'creator_id'], 'integer'],
				[['create_time', 'update_time'], 'safe'],
				[['pic_txt_content'], 'string'],
				[['creator_name'], 'string', 'max' => 45],
				[['creator_nickname'], 'string', 'max' => 30],
				[['creator_avatar'], 'string', 'max' => 200],
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
				'create_time' => 'Create Time',
				'update_time' => 'Update Time',
				'pic_txt_content' => 'Pic Txt Content',
				'sort_number' => 'Sort Number',
				'creator_id' => 'Creator ID',
				'creator_name' => 'Creator Name',
				'creator_nickname' => 'Creator Nickname',
				'creator_avatar' => 'Creator Avatar',
		];
	}
}
?>