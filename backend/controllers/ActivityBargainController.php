<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2017/6/1
 * Time: 15:07
 */
namespace backend\controllers;

use common\models\ActivityBargain;
use common\models\ActivityGoodsRelation;
use common\models\Goods;
use common\models\VirtualGoodsInfo;
use yii;
class ActivityBargainController extends PublicBaseController
{
    protected $virtual_goods_code;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->virtual_goods_code = 2;
    }

    /**
     *  创建活动
     * @param name              活动名称
     * @param company_id        公司ID
     * @param huiwenbi          费用
     * @param reserve_price     保底价  最高杀价（商品总价-保底价）*10%*50%
     * @param pay_num           付费参加活动次数
     * @param pay_num_type      付费参加类型     0：人/每天    1：人/次数
     * @param free_num          免费参加活动次数
     * @param free_num_type     免费参加类型     0：人/每天    1：人/次数
     * @param goods_id          商品ID
     * @param goods_img         商品图片
     * @param prize_info        奖品信息
     * @param prize_desc        奖品说明
     * @return array
     */
    public function actionAddActivity(){
        if(yii::$app->request->isPost){
            $activity_id = isset($this->params['activity_id']) ? $this->params['activity_id'] : 0;
            $goods_type = yii::$app->request->post('goods_type', 1);
            $goods_id   = yii::$app->request->post('goods_id', 0);
            if($activity_id){
                $activity_model = ActivityBargain::findOne($activity_id);
                /* 编辑 如果重新选择了商品 归还库存 */
                if($goods_type == 1 && $activity_model->goods_id != $goods_id){
                    $return_goods_stock_ret = yii::$app->db->createCommand()->update('vrshop_goods', [
                        'goods_stock' => new yii\db\Expression('goods_stock + 1')
                    ], "goods_id = {$goods_id}")->execute();
                    if($return_goods_stock_ret) $this->_errorData('100000', '归还商品库存失败');
                }
            }else{
                $activity_model = new ActivityBargain();
                $activity_model->create_time   = time();

                if(yii::$app->request->post('goods_type', 1) == 1){
                    $goods_id = yii::$app->request->post('goods_id', 0);
                    if(!$goods_id) $this->_errorData('10000', '参数错误');
                    $goods_info = Goods::findOne($goods_id)->toArray();
                    $activity_model->bargain_price = $goods_info['huiwenbi'];
                }else{
                    $virtual_id = yii::$app->request->post('virtual_goods_id', 0);
                    $goods_info = VirtualGoodsInfo::find()->alias('vgi')->select(['g.huiwenbi'])->leftJoin('vrshop.goods g', 'vgi.goods_id = g.goods_id')->where(['vgi.details_id'=>$virtual_id])->asArray()->one();
                    $activity_model->bargain_price = $goods_info['huiwenbi'];
                }
            }

            $activity_model->activity_name = isset($this->params['name']) ? $this->params['name'] : '';
            $activity_model->company_id    = isset($this->params['company_id']) ? $this->params['company_id'] : '';
            $activity_model->huiwenbi      = isset($this->params['huiwenbi']) ? $this->params['huiwenbi'] : 0;
            $activity_model->reserve_price = isset($this->params['reserve_price']) ? $this->params['reserve_price'] : 0;
            $activity_model->pay_num       = isset($this->params['pay_num']) ? $this->params['pay_num'] : 0;
            $activity_model->pay_num_type  = isset($this->params['pay_num_type']) ? $this->params['pay_num_type'] : 0;
            $activity_model->free_num      = isset($this->params['free_num']) ? $this->params['free_num'] : 0;
            $activity_model->free_num_type = isset($this->params['free_num_type']) ? $this->params['free_num_type'] : 0;
            $activity_model->goods_id      = isset($this->params['goods_id']) ? $this->params['goods_id'] : 0;
            $activity_model->goods_type    = isset($this->params['goods_type']) ? $this->params['goods_type'] : 1;
            $activity_model->virtual_goods_id = isset($this->params['virtual_goods_id']) ? $this->params['virtual_goods_id'] : 0;
            $activity_model->goods_img     = isset($this->params['goods_img']) ? $this->params['goods_img'] : '';
            $activity_model->prize_info    = isset($this->params['prize_info']) ? $this->params['prize_info'] : '';
            $activity_model->prize_desc    = isset($this->params['prize_desc']) ? $this->params['prize_desc'] : '';
            $activity_model->status        = 0;
            $activity_model->end_time      = isset($this->params['end_time']) ? $this->params['end_time'] : '';

            if(!$activity_model->activity_name || !$activity_model->company_id || !$activity_model->huiwenbi){
                $this->_errorData('1000', '参数错误');
            }

            $ret = $activity_model->save();
            if($ret){

                $virtual_goods_id = yii::$app->request->post('virtual_goods_id', 0);
                $activity_id = $activity_id ? $activity_id : $activity_model->attributes['activity_id'];

                if($goods_type == 1 && $goods_id){
                    /* 如果实体商品 直接减去库存 */
                    $up_goods_ret = yii::$app->db->createCommand()->update('vrshop.goods', ['goods_stock' => new yii\db\Expression('goods_stock - 1')], "goods_id = {$goods_id} AND goods_stock > 0")->execute();
                    if(!$up_goods_ret) $this->_errorData('10002', '所选商品库存不足');
                }elseif ($goods_type == 2 && $virtual_goods_id){
                    if($activity_id){
                        /* 如果是编辑  解除绑定活动跟卡号的绑定 */
                        $validity = date('Y-m-d', time());
                        $del_relation_ret = yii::$app->db->createCommand()->update('vrshop.virtual_goods_info', [
                            'activity_id' => 0,
                        ], "activity_id = {$activity_id} AND is_sold = 0 AND deadline > {$validity}")->execute();
                        if(!$del_relation_ret) $this->_errorData('100031', '解除绑定失败');
                    }
                    /* 如果虚拟商品 绑定卡号和活动 */
                    $up_virtual_goods_ret = yii::$app->db->createCommand()->update('vrshop.virtual_goods_info', ['activity_id'=>$activity_id], "details_id = {$virtual_goods_id}")->execute();
                    if(!$up_virtual_goods_ret) $this->_errorData('10003', '数据更新失败');
                }else{
                    $this->_errorData('10000', 'error');
                }

                $this->_successData('成功');
            }

            $this->_errorData('10001', '失败');
        }
        $this->_errorData('1001', '请求错误');
    }

    public function actionActivityInfo(){
        $company_id    = yii::$app->request->post('company_id', 0);
        $activity_id   = yii::$app->request->post('activity_id', 0);
        if(!$activity_id) $this->_errorData('6000', '参数错误');

        $info = ActivityBargain::find()
            ->alias('ab')
            ->select('ab.*, g.goods_name, g.huiwenbi, g.banner_image, g.goods_type')
            ->leftJoin('vrshop.goods g', 'ab.goods_id = g.goods_id')
            ->where(['ab.activity_id'=>$activity_id])
            ->asArray()
            ->one();

        $info || $info = [];

        if($info['goods_type'] == $this->virtual_goods_code){
            $info['virtual_info'] = VirtualGoodsInfo::find()->where(['activity_id'=>$activity_id])->asArray()->one();
        }

        $this->_successData($info);
    }

    /**
     *  活动列表
     * @param company_id
     * @param activity_name     活动标题
     * @param status            活动状态
     * @param page
     * @param size
     * @return array
     */
    public function actionActivityList(){
        $company_id    = isset($this->params['company_id']) ? $this->params['company_id'] : 0;
        $activity_name = isset($this->params['activity_name']) ? $this->params['activity_name'] : 0;
        $status        = isset($this->params['status']) ? $this->params['status'] : 0;
        $page          = isset($this->params['page']) ? $this->params['page'] : 1;
        $size          = isset($this->params['size']) ? $this->params['size'] : 10;

        $order = 'create_time DESC';

        $list = ActivityBargain::getList($company_id, $activity_name, $status, $order, $page, $size);

        $this->_successData($list);
    }

    /**
     *  删除活动
     * @param company_id
     * @param activity_id
     * @return array
     */
    public function actionDelActivity(){
        if(yii::$app->request->isPost){
            $company_id  = yii::$app->request->post('company_id', 0);
            $activity_id = yii::$app->request->post('activity_id', 0);

            if(!$activity_id){
                $this->_errorData('1003', '参数错误');
            }

            $ret = ActivityBargain::deleteAll(['activity_id'=>$activity_id]);

            if($ret){
                $this->_successData('删除成功');
            }
            $this->_errorData('1004', '删除失败');
        }
        $this->_errorData('1002', '请求错误');
    }

    /**
     *  更改活动状态
     * @param company_id
     * @param activity_id
     * @param status
     * @return array
     */
    public function actionChangeActivityStatus(){
        if(yii::$app->request->isPost){
            $company_id  = yii::$app->request->post('company_id', 0);
            $activity_id = yii::$app->request->post('activity_id', 0);
            $status      = yii::$app->request->post('status', 0);


            if(!$activity_id){
                $this->_errorData('1007', '参数错误');
            }

            $ret = ActivityBargain::updateAll(['status'=>$status], ['activity_id'=>$activity_id]);

            if($ret != 0){
                $this->_successData('修改成功');
            }
            $this->_errorData('1008', '修改失败');
        }
        $this->_errorData('1009', '请求错误');
    }


}