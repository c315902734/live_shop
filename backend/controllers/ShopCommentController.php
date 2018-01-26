<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2017/6/13
 * Time: 14:13
 */

namespace backend\controllers;

use common\models\OrderComment;
use yii;

class ShopCommentController extends PublicBaseController
{
    protected $comment_pass_code;
    protected $comment_not_pass_code;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub

        $this->comment_pass_code = 1;
        $this->comment_not_pass_code = 2;
    }

    /**
     * 后台-评论列表
     */
    public function actionCommentList(){
        $company_id     = yii::$app->request->post('company_id', 0);
        $comment_status = yii::$app->request->post('comment_status', 0);
        $order_id   = yii::$app->request->post('order_id', 0);
        $consignee  = yii::$app->request->post('consignee', '');
        $goods_name = yii::$app->request->post('goods_name', '');
        $start_time = yii::$app->request->post('start_time', 0);
        $end_time   = yii::$app->request->post('end_time', 0);
        $page = yii::$app->request->post('page', 1);
        $size = yii::$app->request->post('size', 10);

        if(!$company_id) $this->_errorData('1000', '参数错误');

        $return_data = OrderComment::CommentList($company_id, $comment_status, $order_id, $consignee, $goods_name, $start_time, $end_time, $page, $size);
        $this->_successData($return_data);
    }

    /**
     * 后台-评论审核操作
     * @param status   1:通过审核  2：不通过审核
     */
    public function actionCommentOperate(){
        $comment_id = yii::$app->request->post('comment_id', 0);
        $status     = yii::$app->request->post('status', 0);
        $reason     = yii::$app->request->post('reason', '');
        if(!$comment_id) $this->_errorData('2000', '评论ID错误');

        $comment_data = OrderComment::findOne(['comment_id'=>$comment_id]);
        if(!$comment_data) $this->_errorData('2003', '没有此评论');

        if($status == $this->comment_pass_code){
            $comment_data->examine_status = 1;
            $ret = $comment_data->save();

            if($ret) $this->_successData('已通过审核');
            $this->_errorData('2001', '未通过审核');
        }else if($status == $this->comment_not_pass_code){
            if($reason == '') $this->_errorData('2002', '填写未通过审核理由');

            $comment_data->examine_status = 2;
            $comment_data->examine_reason = $reason;
            $ret = $comment_data->save();
            if($ret) $this->_successData('评论处理成功');
            $this->_errorData('2001', '评论处理失败');
        }
    }

    /**
     * 后台-批量通过审核
     */
    public function actionBatchPass(){
        $comment_ids = yii::$app->request->post('comment_ids', 0);
        if(!$comment_ids) $this->_errorData('3000', '参数错误');

        $comment_ids = json_decode($comment_ids, true);
        if(!is_array($comment_ids)) $this->_errorData('3001', '传个json数组');

        $ret = OrderComment::updateAll(['examine_status'=>1], ['comment_id'=>$comment_ids]);
        if($ret) $this->_successData('批量操作成功');
        $this->_errorData('3002', '批量操作失败');
    }
}