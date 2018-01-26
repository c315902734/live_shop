<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_pk_competitor".
 *
 * @property string $id
 * @property string $competitor_id
 * @property string $competitor_name
 * @property string $pk_id
 * @property string $live_id
 * @property integer $group_id
 */
class LivePkCompetitor extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_pk_competitor';
    }

    public static function getDb(){
    	return yii::$app->vrlive;
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['competitor_id', 'pk_id', 'live_id', 'group_id'], 'integer'],
            [['competitor_name'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'competitor_id' => 'Competitor ID',
            'competitor_name' => 'Competitor Name',
            'pk_id' => 'Pk ID',
            'live_id' => 'Live ID',
            'group_id' => 'Group ID',
        ];
    }
}
