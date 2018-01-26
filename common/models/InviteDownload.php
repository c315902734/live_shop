<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "invite_download".
 *
 */
class InviteDownload extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'invite_download';
    }

    public static function getDb()
    {
        return Yii::$app->vruser1;
    }


}
