<?php
namespace common\models;

use frontend\controllers\NewslinkController;
use frontend\controllers\PublicBaseController;
use Yii;

/**
 * This is the model class for table "news_column".
 *
 * @property integer $column_id
 * @property string $name
 * @property integer $type
 * @property integer $weight
 * @property string $create_time
 * @property integer $creator_id
 * @property string $update_time
 * @property integer $status
 */
class NewsColumnAdmin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news_column_admin';
    }
    public static function getDb()
    {
        return Yii::$app->vrnews1;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['column_id', 'admin_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'column_id' => 'Column ID',
            'admin_id' => 'Admin ID',
            'weight' => 'Weight'
        ];
    }

}
