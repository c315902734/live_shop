<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2017/11/10
 * Time: 15:50
 */

namespace backend\controllers;

use common\models\OrderGoodsRelation;
use yii;

class ShopLiveOrderStatisticsController extends PublicBaseController
{
    protected $objPHPExcel;

    public function init()
    {
        parent::init();
        $this->objPHPExcel = New \PHPExcel();
    }

    /**
     * 直播间购买的商品销售统计
     * @param $company_id
     * @param $goods_name
     * @param $display_type            0 全部显示  1 只显示汇闻币订单  2 人民币+汇闻币  3 人民币支付
     * @param $order_status            11-21 弃用
     * @param $start_time
     * @param $end_time
     * @param $rmb_sales_sort_rules    1 正序  2 倒序
     * @param $hmb_sales_sort_rules    1 正序  2 倒序
     * @param $export_data
     * @param $page
     * @param $size
     * @return array
     */
    public function actionGoodsSellOutStatistic()
    {
        $company_id = yii::$app->request->post('company_id', 0);
        $goods_name = yii::$app->request->post('goods_name', '');
        $display_currency_type = yii::$app->request->post('display_currency_type', false);
        $order_status = yii::$app->request->post('order_status', '');
        $start_time = yii::$app->request->post('start_time', 0);
        $end_time   = yii::$app->request->post('end_time', 0);
        $rmb_turnover_sort_rules = yii::$app->request->post('rmb_turnover_sort_rules', 0);
        $hmb_turnover_sort_rules = yii::$app->request->post('hmb_turnover_sort_rules', 0);
        $page = yii::$app->request->post('page', 1);
        $size = yii::$app->request->post('size', 10);

        // 导出数据
        $export_data = yii::$app->request->post('export_data', 0);

        // 直播间商品销售统计和列表
        $live_goods_sales_data = OrderGoodsRelation::getSellOutStatistic($company_id, $goods_name, $display_currency_type, $order_status, $start_time, $end_time, $rmb_turnover_sort_rules, $hmb_turnover_sort_rules, $export_data, $page, $size);

        // 导出csv
        if ($export_data) {
            $this->objPHPExcel->getProperties()->setCreator("xinhuiwen")
                ->setLastModifiedBy("销售统计")
                ->setTitle("销售统计")
                ->setSubject("销售统计")
                ->setDescription("销售统计")
                ->setKeywords("销售统计");

            $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '序号')
                ->setCellValue('B1', '订单号')
                ->setCellValue('C1', '商品名称')
                ->setCellValue('D1', '商品价格')
                ->setCellValue('E1', '汇闻币')
                ->setCellValue('F1', '销量')
                ->setCellValue('G1', '人民币交易额')
                ->setCellValue('H1', '汇闻币交易额');

            foreach ($live_goods_sales_data['sales_goods_list']['list'] as $key=>$item) {
                $i = $key + 2;
                $this->objPHPExcel->getActiveSheet()->setCellValue('A'.$i,  $key + 1);
                $this->objPHPExcel->getActiveSheet()->setCellValue('B'.$i,  $item['order_id']);
                $this->objPHPExcel->getActiveSheet()->setCellValue('C'.$i,  $item['goods_name']);
                $this->objPHPExcel->getActiveSheet()->setCellValue('D'.$i,  $item['goods_rwb_pirce']);
                $this->objPHPExcel->getActiveSheet()->setCellValue('E'.$i,  $item['goods_hwb_pirce']);
                $this->objPHPExcel->getActiveSheet()->setCellValue('F'.$i,  $item['sell_out_total']);
                $this->objPHPExcel->getActiveSheet()->setCellValue('G'.$i,  $item['sales_rmb_total']);
                $this->objPHPExcel->getActiveSheet()->setCellValue('H'.$i,  $item['sales_hwb_total']);
            }

            $objWriter = \PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename=销售统计.xlsx');
            header('Cache-Control: max-age=0');
            $objWriter->save('php://output');
            exit();
        }

        $this->_successData($live_goods_sales_data);
    }

    /**
     * 商品出售详情
     * @param int $company_id
     * @param int $goods_id
     * @param bool $order_status
     * @param int $transaction_type
     * @param int $display_currency_type
     * @param int $start_time
     * @param int $end_time
     * @param int $rmb_turnover_sort_rules
     * @param int $hmb_turnover_sort_rules
     * @param int $page
     * @param int $size
     * @return array
     */
    public function actionSalesGoodsStatistic(){
        $company_id   = yii::$app->request->post('company_id', 0);
        $goods_id     = yii::$app->request->post('goods_id', 0);
        $display_currency_type = yii::$app->request->post('display_currency_type', false);  // 币种 0 汇闻币 1 汇闻币+人民币 2 人民币
        $order_status = yii::$app->request->post('order_status', false);
        $transaction_type = yii::$app->request->post('transaction_type', 0);        // 交易类型 0 全部 1 已完成 2 未完成
        $start_time   = yii::$app->request->post('start_time', 0);
        $end_time     = yii::$app->request->post('end_time', 0);
        $rmb_turnover_sort_rules = yii::$app->request->post('rmb_turnover_sort_rules', 0);
        $hmb_turnover_sort_rules = yii::$app->request->post('hmb_turnover_sort_rules', 0);
        $page = yii::$app->request->post('page', 1);
        $size = yii::$app->request->post('size', 10);
        if (!$goods_id) $this->_successData([]);

        // 导出数据
        $export_data = yii::$app->request->post('export_data', 0);

        $ret = OrderGoodsRelation::getSalesGoodsStatistic($company_id, $goods_id, $order_status, $transaction_type, $display_currency_type, $start_time, $end_time, $rmb_turnover_sort_rules, $hmb_turnover_sort_rules, $export_data, $page, $size);
        if ($export_data) {
            $this->objPHPExcel->getProperties()->setCreator("xinhuiwen")
                ->setLastModifiedBy("商品销售详情")
                ->setTitle("商品销售详情")
                ->setSubject("商品销售详情")
                ->setDescription("商品销售详情")
                ->setKeywords("商品销售详情");

            $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '序号')
                ->setCellValue('B1', '商品名称')
                ->setCellValue('C1', '商品价格')
                ->setCellValue('D1', '汇闻币价格')
                ->setCellValue('E1', '销量')
                ->setCellValue('F1', '人民币交易额')
                ->setCellValue('G1', '汇闻币交易额')
                ->setCellValue('H1', '订单号')
                ->setCellValue('I1', '订单状态')
                ->setCellValue('J1', '直播间名称');

            foreach ($ret['order_list']['list'] as $key=>$item) {
                $i = $key + 2;
                $this->objPHPExcel->getActiveSheet()->setCellValue('A'.$i,  $key + 1);
                $this->objPHPExcel->getActiveSheet()->setCellValue('B'.$i,  $item['goods_name']);
                $this->objPHPExcel->getActiveSheet()->setCellValue('C'.$i,  $item['goods_hwb_price']);
                $this->objPHPExcel->getActiveSheet()->setCellValue('D'.$i,  $item['goods_rmb_price']);
                $this->objPHPExcel->getActiveSheet()->setCellValue('E'.$i,  $item['goods_num']);
                $this->objPHPExcel->getActiveSheet()->setCellValue('F'.$i,  $item['hwb_total']);
                $this->objPHPExcel->getActiveSheet()->setCellValue('G'.$i,  $item['rmb_total']);
                $this->objPHPExcel->getActiveSheet()->setCellValue('H'.$i,  $item['order_id']);
                $this->objPHPExcel->getActiveSheet()->setCellValue('I'.$i,  $item['status']);
                $this->objPHPExcel->getActiveSheet()->setCellValue('J'.$i,  $item['order_id']);
            }

            $objWriter = \PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename=销售统计.xlsx');
            header('Cache-Control: max-age=0');
            $objWriter->save('php://output');
            exit();
        }

        $this->_successData($ret);
    }

    /**
     * 所有直播间销售商品统计
     * @param int $company_id
     * @param string $live_name
     * @param int $display_currency_type         0 全部  1 汇闻币 2 混合支付
     * @param int $order_status                  0 全部  1 已完成 2 未完成
     * @param int $start_time
     * @param int $end_time
     * @param int $page
     * @param int $size
     * @return array
     */
    public function actionLiveSalesGoodsStatistic(){
        $company_id = yii::$app->request->post('company_id', 0);
        $live_name  = yii::$app->request->post('live_name', '');
        $display_currency_type = yii::$app->request->post('display_currency_type', false);
        $order_status = yii::$app->request->post('order_status', '');
        $start_time = yii::$app->request->post('start_time', 0);
        $end_time   = yii::$app->request->post('end_time', 0);
//        $rmb_turnover_sort_rules = yii::$app->request->post('rmb_turnover_sort_rules', 0);
//        $hmb_turnover_sort_rules = yii::$app->request->post('hmb_turnover_sort_rules', 0);
        $page = yii::$app->request->post('page', 1);
        $size = yii::$app->request->post('size', 10);

        // 导出数据
        $export_data = yii::$app->request->post('export_data', 0);

        $data = OrderGoodsRelation::getLiveSalesGoodsStatistic($company_id, $live_name, $display_currency_type, $order_status, $start_time, $end_time, $page, $size);
        $this->_successData($data);
    }

    /**
     * 某直播间销售商品统计
     */
    public function actionLiveSalesGoodsInfo(){
        $section_id = yii::$app->request->post('section_id', 0);
        $display_currency_type = yii::$app->request->post('display_currency_type', false);
        $goods_name = yii::$app->request->post('goods_name', '');
        $start_time = yii::$app->request->post('start_time', 0);
        $end_time = yii::$app->request->post('end_time', 0);
        $page = yii::$app->request->post('page', 1);
        $size = yii::$app->request->post('size', 10);
        if (!$section_id) $this->_successData(['']);

        $data = OrderGoodsRelation::getLiveSalesGoodsInfo($section_id, $display_currency_type, $goods_name, $start_time, $end_time, $page, $size);
        $this->_successData($data);
    }

    /**
     * 所有推流业务员 直播销售统计
     */
    public function actionPusherClerkGoodsSaleStatistic(){
        $company_id = yii::$app->request->post('company_id', 0);
        $pusher_clerk_name = yii::$app->request->post('pusher_clerk_name', '');
        $rmb_turnover_sort_rules = yii::$app->request->post('rmb_turnover_sort_rules', false);
        $hwb_turnover_sort_rules = yii::$app->request->post('hwb_turnover_sort_rules', false);
        $page = yii::$app->request->post('page', 1);
        $size = yii::$app->request->post('size', 10);

        $ret = OrderGoodsRelation::pusherClerkGoodsSaleStatistic($company_id, $pusher_clerk_name, $rmb_turnover_sort_rules, $hwb_turnover_sort_rules, $page, $size);
        $this->_successData($ret);
    }

    /**
     * 推流业务员直播下商品销售详情
     */
    public function actionPusherClerkSaleInfo(){
        $pusher_clerk_id = yii::$app->request->post('pusher_clerk_id', 0);
        $live_name = yii::$app->request->post('live_name', '');
        $page = yii::$app->request->post('page', 1);
        $size = yii::$app->request->post('size', 10);
        // 导出数据
        $export_data = yii::$app->request->post('export_data', 0);

        $ret = OrderGoodsRelation::pusherClerkSaleInfo($pusher_clerk_id, $live_name, $page, $size);

        if ($export_data) {
            $this->objPHPExcel->getProperties()->setCreator("xinhuiwen")
                ->setLastModifiedBy("直播员销量统计")
                ->setTitle("直播员销量统计")
                ->setSubject("直播员销量统计")
                ->setDescription("直播员销量统计")
                ->setKeywords("直播员销量统计");

            $this->objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '序号')
                ->setCellValue('B1', '直播间名称')
                ->setCellValue('C1', '直播员')
                ->setCellValue('D1', '直播员邮箱')
                ->setCellValue('E1', '人民币交易额')
                ->setCellValue('F1', '汇闻币交易额');

            foreach ($ret['list']['list'] as $key=>$item) {
                $i = $key + 2;
                $this->objPHPExcel->getActiveSheet()->setCellValue('A'.$i,  $key + 1);
                $this->objPHPExcel->getActiveSheet()->setCellValue('B'.$i,  $item['name']);
                $this->objPHPExcel->getActiveSheet()->setCellValue('C'.$i,  $item['username']);
                $this->objPHPExcel->getActiveSheet()->setCellValue('D'.$i,  $item['user_email']);
                $this->objPHPExcel->getActiveSheet()->setCellValue('E'.$i,  round($item['sale_rmb_count']), 2);
                $this->objPHPExcel->getActiveSheet()->setCellValue('F'.$i,  intval($item['sale_hwb_count']));
            }

            $objWriter = \PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename=直播员销量统计.xlsx');
            header('Cache-Control: max-age=0');
            $objWriter->save('php://output');
            exit();
        }

        $this->_successData($ret);
    }
}