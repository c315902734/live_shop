<?php

namespace common\models;

use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "api_user_action".
 *
 */
class AdminApiUserAction extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    const SINGLE_PAGE = 2;

    public static function tableName() {
        return 'api_user_action';
    }

    public static function getDb() {
        return Yii::$app->vradmin1;
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
                [['id'], 'required'],
        ];
    }

    public static function getApiUserActionList($pageSize=10) {
    }
    
       public static function getApiUserActionDetail($id=1) {
        $result = self::find()->select(['id','data','actionName'])->where(['id'=>$id])->asArray()->one();
        $result['data']= unserialize($result['data']);
        return $result;
    }

}
