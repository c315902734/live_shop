<?php

namespace common\models;

use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "friend_circle".
 *
 */
class LiveFriendCircle extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    const SINGLE_PAGE = 2;

    public static function tableName() {
        return 'live_friend_circle';
    }

    public static function getDb() {
        return Yii::$app->vrlive;
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
                [['subject'], 'required'],
                [['type'], 'default', 'value' => self::SINGLE_PAGE, 'on' => 'page'],
        ];
    }

    public static function getFriendCircleList($pageSize=10,$live_id=0) {
        $data = LiveFriendCircle::find()->select(['tid'])->where(['live_id'=>$live_id,'hidden'=>'1'])->asArray()->all();
        $pages = new Pagination(['totalCount' => count($data), 'pageSize' => $pageSize]);
        $query = LiveFriendCircle::find()->select(['tid','author','avatar','dateline','subject','recommends','icon','replies','cover','url'])->where(['live_id'=>$live_id,'hidden'=>'1'])->offset($pages->offset)->limit($pages->limit)->orderBy('update_time DESC')->asArray()->all();
        return ['count' =>count($data), 'data' => $query];
    }
    
       public static function getFriendCircleDetail($tid=220) {
        $result = self::find()->select(['tid','author','avatar','dateline','subject','recommends','icon','replies','cover','url'])->where(['tid'=>$tid])->orderBy('tid DESC')->asArray()->one();
        return $result;
    }

}
