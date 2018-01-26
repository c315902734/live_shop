<?php
namespace common\models;

use Yii;
use common\models\OauthAccessTokens;
use yii\db\Query;

/**
 * user_merssage model
 */
class UserMerssage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_merssage';
    }

    public static function getDb()
    {
        return Yii::$app->vruser1;
    }
}