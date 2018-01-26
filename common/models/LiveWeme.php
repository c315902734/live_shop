<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_weme".
 *
 */
class LiveWeme extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_weme';
    }

    public static function getDb(){
        return Yii::$app->vrlive;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['accountID'], 'required'],
        ];
    }

  
}
