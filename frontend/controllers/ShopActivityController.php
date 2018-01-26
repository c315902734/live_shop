<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2017/6/21
 * Time: 14:42
 */

namespace frontend\controllers;

use common\models\ActivityBargain;
use common\models\BargainRecode;
use common\models\BargainRecord;
use yii;

class ShopActivityController extends PublicBaseController
{
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
    }

    /**
     * 杀价帮 活动详情
     * @activity_id 活动id
     */
    public function actionBargainInfo(){
        $activity_id = yii::$app->request->post('activity_id', 0);
        if(!$activity_id) $this->_errorData('1000', '参数错误');

        $activity_info = ActivityBargain::findOne($activity_id)->toArray();
        if(!$activity_info) $this->_errorData('1001', '活动信息错误');

        $this->_successData($activity_info);
    }

    /**
     * 杀价帮砍价列表
     * @param activity_id
     * @return array
     */
    public function actionBargainRecordList(){
        $activity_id = yii::$app->request->post('activity_id', 0);
        $page = yii::$app->request->post('page', 1);
        $size = yii::$app->request->post('size', 10);
        if(!$activity_id) $this->_errorData('2000', '参数错误');
//        $record_list = BargainRecord::findOne('84');
        $record_list = BargainRecord::getRecordList($activity_id, $page, $size);
        $this->_successData($record_list);
    }

}