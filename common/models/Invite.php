<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "invite".
 *
 */
class Invite extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'invite';
    }

    public static function getDb()
    {
        return Yii::$app->vruser1;
    }

    
}
