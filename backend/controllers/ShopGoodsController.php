<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2017/11/6
 * Time: 14:29
 */

namespace backend\controllers;

use common\models\Goods;
use common\models\ShopOrder;
use yii;

class ShopGoodsController extends PublicBaseController
{
    protected $live_goods_type;
    protected $live_goods_verified_code;

    public function init()
    {
        parent::init();

        $this->live_goods_type = 2;
        $this->live_goods_verified_code = 1;
    }

    /**
     * 直播间售卖商品列表
     */
    public function actionLiveGoodsList()
    {
        $verify_state = yii::$app->request->post('verify_state', 0);
        $tags = yii::$app->request->post('tags', '');
        $page = yii::$app->request->post('page', 1);
        $size = yii::$app->request->post('size', 10);

        $list = Goods::GetList(
            0,
            0,
            1,
            0,
            '',
            0,
            0,
            $this->live_goods_type,
            $verify_state,
            $tags,
            $page,
            $size
        );

        $this->_successData($list);
    }

    /**
     * 直播间商品 审核
     */
    public function actionLiveGoodsVerify()
    {
        $goods_id = yii::$app->request->post('goods_id', 0);
        if (intval($goods_id) < 1) $this->_errorData(1000,'参数错误');

        $goods_info = Goods::findOne($goods_id);
        if (!$goods_info) $this->_errorData(1001, '未找到该商品');
        if ($goods_info->sale_type == 1) $this->_errorData(1002, '不支持此类型商品的审核');
        if ($goods_info->live_sale_status == $this->live_goods_verified_code) $this->_errorData(1003, '此商品已通过审核');

        $goods_info->live_sale_status = $this->live_goods_verified_code;
        if ($goods_info->save()) {
            $this->_successData('审核已通过');
        }
        $this->_errorData(1004, '审核失败，请稍后再试');
    }
}