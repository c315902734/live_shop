<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_competitor".
 *
 * @property integer $competitor_id
 * @property string $nickname
 * @property string $real_name
 * @property integer $sex
 * @property string $native
 * @property string $birthday
 * @property string $avatar
 * @property integer $weight
 * @property string $level
 * @property string $team
 * @property integer $popular_value
 * @property string $keywords
 * @property string $record
 * @property string $introduction
 * @property string $create_time
 * @property string $update_time
 * @property integer $creator_id
 * @property integer $status
 */
class LiveCompetitor extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_competitor';
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
            [['sex', 'weight', 'popular_value', 'creator_id', 'status'], 'integer'],
            [['birthday', 'create_time', 'update_time'], 'safe'],
            [['nickname'], 'string', 'max' => 25],
            [['real_name'], 'string', 'max' => 15],
            [['native', 'level', 'team', 'keywords'], 'string', 'max' => 45],
            [['avatar'], 'string', 'max' => 200],
            [['record', 'introduction'], 'string', 'max' => 300],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'competitor_id' => 'Competitor ID',
            'nickname' => 'Nickname',
            'real_name' => 'Real Name',
            'sex' => 'Sex',
            'native' => 'Native',
            'birthday' => 'Birthday',
            'avatar' => 'Avatar',
            'weight' => 'Weight',
            'level' => 'Level',
            'team' => 'Team',
            'popular_value' => 'Popular Value',
            'keywords' => 'Keywords',
            'record' => 'Record',
            'introduction' => 'Introduction',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'creator_id' => 'Creator ID',
            'status' => 'Status',
        ];
    }

    /**
     * 获取直播选手信息
     */
    public static function getCompetitorInfo($id){
        if(empty($id)) return false;
        $info = static::findOne($id);
        return $info;
    }
}
