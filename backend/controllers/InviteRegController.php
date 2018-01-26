<?php
namespace backend\controllers;

use common\models\InviteReg;

class InviteRegController extends PublicBaseController{

    /**
     * 邀请注册列表
     */
    public function actionList(){
        $page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : '1';
        $size = !empty($_REQUEST['size']) ? $_REQUEST['size'] : '10';
        $list = InviteReg::getInviteList($page, $size);
        $this->_successData($list);
    }

    /**
     * 新增邀请注册信息
     */
    public function actionAdd(){
        $images = !empty($_REQUEST['images']) ? $_REQUEST['images'] : '';
        $share_title    = !empty($_REQUEST['share_title']) ? $_REQUEST['share_title'] : '';
        $share_abstract = !empty($_REQUEST['share_abstract']) ? $_REQUEST['share_abstract'] : '';
        $share_img      = !empty($_REQUEST['share_img']) ? $_REQUEST['share_img'] : '';
        $create_id      = !empty($_REQUEST['create_id']) ? $_REQUEST['create_id'] : '';
        if(!$images || !$share_title || !$share_abstract || !$share_img || !$create_id){
            $this->_errorData('0001', '参数错误');
        }
        $result = InviteReg::addInviteReg($images, $share_title, $share_abstract, $share_img, $create_id);
        $this->_successData($result);
    }

    /**
     * 获取某一条邀请注册详情
     */
    public function actionInviteDetail(){
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
        if(!$id){
            $this->_errorData('0001', '参数错误');
        }
        $info = InviteReg::getInviteInfo($id);
        $this->_successData($info);
    }

    /**
     * 获取最新邀请注册信息
     */
    public function actionNewInvite(){
        $info = InviteReg::getNewInvite();
        $this->_successData($info);
    }
}