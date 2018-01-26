<?php
namespace frontend\controllers;

use common\models\UserAmount;

class UserAmountController extends BaseApiController{

    /**
     * 增加记录
     */
    public function actionAddAmount(){
        $param['user_id']     = isset($this->params['user_id']) ? $this->params['user_id'] : '';
        $param['operate_cnt'] = isset($this->params['operate_cnt']) ? $this->params['operate_cnt'] : '';
        $param['operate']     = isset($this->params['operate']) ? $this->params['operate'] : '';
        $param['operate_name'] = isset($this->params['operate_name']) ? $this->params['operate_name'] : '';
        $param['task_id']      = isset($this->params['task_id']) ? $this->params['task_id'] : '';
        if(!$param['user_id']){
            $this->_errorData(0001, '参数错误');
        }
        $result = UserAmount::addUserAmount($param);
        if(is_array($result)){
            $this->_successData($result);
        }else{
            $this->_errorData(0001, '添加失败！');
        }
    }

    /**
     * 获取用户汇闻币记录
     */
    public function actionUserAmountList(){
        $user = $this->_getUserModel();
        $user_id = $user['user_id'];
        $page    = isset($this->params['page']) ? $this->params['page'] : '1';
        $size    = isset($this->params['size']) ? $this->params['size'] : '20';
        $result  = UserAmount::getUserAmountList($user_id, $page, $size);
        $result['amount'] = $user['amount'];
        $this->_successData($result);
    }
}