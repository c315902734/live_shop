<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2017/12/18
 * Time: 14:27
 */

namespace backend\controllers;


use common\models\HwbPool;
use yii;
class HwbPoolController extends PublicBaseController
{
    public function init()
    {
        parent::init();
    }

    public function actionAddRecord(){
        $pool_type     = yii::$app->request->post('pool_type', 0);
        $children_type = yii::$app->request->post('children_type', 0);
        $hwb = yii::$app->request->post('hwb', 0);
        $remarks = yii::$app->request->post('remarks', '');
        if (!$pool_type || !$children_type || !$hwb) $this->_errorData('1000', '参数错误');

        $ret = HwbPool::addRecord($pool_type, $children_type, $hwb, $remarks);
        if ($ret) {
            $this->_successData('添加成功');
        }
        $this->_errorData('1001', '添加失败');
    }
}