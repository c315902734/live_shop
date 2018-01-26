<?php

namespace common\models;

use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "friend_comment".
 *
 */
class LiveFriendComment extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    const SINGLE_PAGE = 2;

    public static function tableName() {
        return 'live_friend_comment';
    }

    public static function getDb() {
        return Yii::$app->vrlive;
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
                [['author'], 'required'],
                [['type'], 'default', 'value' => self::SINGLE_PAGE, 'on' => 'page'],
        ];
    }

      public static function getFriendCommentList($pageSize=10,$tid=218) {
        $data = self::find()->select(['pid'])->where(['tid'=>$tid])->asArray()->all();
        $pages = new Pagination(['totalCount' => count($data), 'pageSize' => $pageSize]);
        $query = self::find()->select(['pid','author','dateline','message','avatar'])->where(['tid'=>$tid])->offset($pages->offset)->limit($pages->limit)->orderBy('pid DESC')->asArray()->all();
        foreach ($query as $key => $val) {
                $query[$key]['author'] = self::userTextDecode($val['author']);
                $query[$key]['message'] = self::userTextDecode($val['message']);
        }
        return ['count' =>count($data), 'data' => $query];
    }
    
       public static function getFriendCommentDetail($pid=1) {
        $result = self::find()->select(['pid','author','dateline','message','avatar'])->where(['pid'=>$pid])->orderBy('pid DESC')->asArray()->one();
        return $result;
    }


     public static  function userTextDecode($str){
        $text = json_encode($str);
        $text = preg_replace_callback('/\\\\\\\\/i',function($str){
            return '\\';
        },$text);
        return json_decode($text);
    }

}
