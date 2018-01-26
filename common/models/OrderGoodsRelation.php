<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "order_goods_relation".
 *
 * @property integer $id
 * @property integer $order_id
 * @property string $goods_id
 * @property integer $virtual_goods_id
 * @property integer $attribute_value_id
 */
class OrderGoodsRelation extends \yii\db\ActiveRecord
{
    const ORDER_PAID_CODE = 1;
    const ORDER_NOT_DEL_CODE = 0;
    const ORDER_CONFIRM_RECEIPT_CODE = 4;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_goods_relation';
    }

    public static function getDb()
    {
        return Yii::$app->vrshop;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'goods_id', 'virtual_goods_id', 'attribute_value_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'goods_id' => 'Goods ID',
            'virtual_goods_id' => 'Virtual Goods ID',
            'attribute_value_id' => 'Attribute Value ID',
        ];
    }

    /**
     * @param $company_id
     * @param $goods_name
     * @param $display_type
     * @param $order_status
     * @param $start_time
     * @param $end_time
     * @param $rmb_turnover_sort_rules
     * @param $hwb_turnover_sort_rules
     * @param $export_data
     * @param $page
     * @param $size
     * @return array
     */
    public static function getSellOutStatistic($company_id, $goods_name, $display_currency_type, $order_status, $start_time, $end_time, $rmb_turnover_sort_rules, $hwb_turnover_sort_rules, $export_data, $page, $size)
    {
        $where = '1';

        if ($company_id) {
            $where .= ' AND g.company_id = '.$company_id;
        }
        if ($goods_name) {
            $where .= " AND g.goods_name LIKE '%{$goods_name}%'";
        }
        // 交易币种筛选
        if ($display_currency_type !== false) {
            switch ($display_currency_type) {
                case 0:
                    $where .= ' AND so.pay_type = 0';
                    break;
                case 1:
                    $where .= ' AND so.pay_type IN (1,2)';
                    break;
                case 2:
                    $where .= ' AND so.pay_type = 3';
                    break;
                default :
                    break;
            }
        }
        if ($start_time) {
            $where .= ' AND so.create_time > '.$start_time;
        }
        if ($end_time) {
            $where .= ' AND so.create_time <'.$end_time;
        }

        $sort = '';
        if ($rmb_turnover_sort_rules == 1) {
            $sort = ['sales_rmb_total' => SORT_ASC];
        } elseif ($rmb_turnover_sort_rules == 2) {
            $sort = ['sales_rmb_total' => SORT_DESC];
        }
        if ($hwb_turnover_sort_rules == 1) {
            $sort = ['sales_hwb_total' => SORT_ASC];
        } elseif ($hwb_turnover_sort_rules == 2) {
            $sort = ['sales_hwb_total' => SORT_DESC];
        }

        $count = Goods::find()
            ->alias('g')
            ->select([
                'g.goods_id',
                'g.goods_name',
                new Expression('g.huiwenbi AS `goods_hwb_pirce`'),
                new Expression('g.rmb_price AS `goods_rmb_price`'),
                new Expression("IF(ogr.goods_id, SUM(ogr.goods_rmb_price * ogr.goods_num), '-') AS sales_rmb_total"),
                new Expression("IF(ogr.goods_id, SUM(ogr.goods_hwb_price * ogr.goods_num), '-') AS sales_hwb_total"),
            ])
            ->leftJoin(OrderGoodsRelation::tableName().' ogr', 'g.goods_id = ogr.goods_id')
            ->leftJoin(ShopOrder::tableName().' so', 'ogr.order_id = so.order_id')
            ->where($where)
            ->groupBy('g.goods_id')
            ->orderBy($sort)
            ->count();

        if ($export_data) {
            $list = Goods::find()
                ->alias('g')
                ->select([
                    'g.goods_id',
                    'g.goods_name',
                    new Expression('g.huiwenbi AS `goods_hwb_pirce`'),
                    new Expression('g.rmb_price AS `goods_rmb_price`'),
                    new Expression("IF(ogr.goods_id, SUM(ogr.goods_rmb_price * ogr.goods_num), '-') AS sales_rmb_total"),
                    new Expression("IF(ogr.goods_id, SUM(ogr.goods_hwb_price * ogr.goods_num), '-') AS sales_hwb_total"),
                    new Expression("IF(ogr.goods_id, SUM(ogr.goods_num), '-') AS sell_out_total"),
                ])
                ->leftJoin(OrderGoodsRelation::tableName().' ogr', 'g.goods_id = ogr.goods_id')
                ->leftJoin(ShopOrder::tableName().' so', 'ogr.order_id = so.order_id')
                ->where($where)
                ->groupBy('g.goods_id')
                ->orderBy($sort)
                ->asArray()->all();
        } else {
            $list = Goods::find()
                ->alias('g')
                ->select([
                    'g.goods_id',
                    'g.goods_name',
                    new Expression('g.huiwenbi AS `goods_hwb_pirce`'),
                    new Expression('g.rmb_price AS `goods_rmb_price`'),
                    new Expression("IF(ogr.goods_id, SUM(ogr.goods_rmb_price * ogr.goods_num), '-') AS sales_rmb_total"),
                    new Expression("IF(ogr.goods_id, SUM(ogr.goods_hwb_price * ogr.goods_num), '-') AS sales_hwb_total"),
                    new Expression("IF(ogr.goods_id, SUM(ogr.goods_num), '-') AS sell_out_total"),
                ])
                ->leftJoin(OrderGoodsRelation::tableName().' ogr', 'g.goods_id = ogr.goods_id')
                ->leftJoin(ShopOrder::tableName().' so', 'ogr.order_id = so.order_id')
                ->where($where)
                ->groupBy('g.goods_id')
                ->orderBy($sort)
                ->offset(($page - 1) * $size)
                ->limit($size)
                ->asArray()->all();
        }

        /*
         * 头部统计
         * 根据查询条件统计
         */
        // 销售统计 人民币
        $sales_data['rmb_turnover'] = 0;
        $sales_data['rmb_completed_order_turnover'] = 0;
        $sales_data['rmb_incomplete_order_turnover'] = 0;
        // 销售统计 汇闻币
        $sales_data['hwb_turnover'] = 0;
        $sales_data['hwb_completed_order_turnover'] = 0;
        $sales_data['hwb_incomplete_order_turnover'] = 0;

        $goods_sale_list = OrderGoodsRelation::find()
            ->alias('ogr')
            ->select('ogr.goods_id, ogr.goods_num, ogr.goods_hwb_price, ogr.goods_rmb_price, so.status')
            ->innerJoin(Goods::tableName().' g', 'ogr.goods_id = g.goods_id')
            ->leftJoin(ShopOrder::tableName().' so', 'ogr.order_id = so.order_id')
            ->where($where)
            ->asArray()->all();

        foreach ($goods_sale_list as $key=>$order) {
            if (in_array($order['status'], [4,5])) {
                $sales_data['rmb_completed_order_turnover'] += $order['goods_num'] * $order['goods_rmb_price'];
                $sales_data['hwb_completed_order_turnover'] += $order['goods_num'] * $order['goods_hwb_price'];
            } else {
                // 未完成交易
                $sales_data['rmb_incomplete_order_turnover'] += $order['goods_num'] * $order['goods_rmb_price'];
                $sales_data['hwb_incomplete_order_turnover'] += $order['goods_num'] * $order['goods_hwb_price'];
            }
        }

        //交易额 = 已完成订单交易额 + 未完成订单交易额
        $sales_data['rmb_turnover'] = round($sales_data['rmb_completed_order_turnover'] + $sales_data['rmb_incomplete_order_turnover'], 2);
        $sales_data['hwb_turnover'] = intval($sales_data['hwb_completed_order_turnover'] + $sales_data['hwb_incomplete_order_turnover']);

        $list = ['count'=>$count, 'list'=>$list];
        return ['sales_data'=>$sales_data, 'sales_goods_list'=>$list];
    }

    /**
     * @param int $company_id
     * @param int $goods_id
     * @param bool $order_status
     * @param int $transaction_type
     * @param int $display_currency_type
     * @param int $start_time
     * @param int $end_time
     * @param int $rmb_turnover_sort_rules
     * @param int $hwb_turnover_sort_rules
     * @param int $page
     * @param int $size
     * @return array
     */
    public static function getSalesGoodsStatistic($company_id = 0, $goods_id = 0, $order_status = false, $transaction_type = 0, $display_currency_type = 0, $start_time = 0, $end_time = 0, $rmb_turnover_sort_rules = 0, $hwb_turnover_sort_rules = 0, $export_data = 0, $page = 1, $size = 10){
//        $where = "ogr.goods_id = {$goods_id} AND so.order_type = 1";
        $where = "ogr.goods_id = {$goods_id}";
        $sort = '';

        if ($company_id) {
            $where .= " AND so.company_id = {$company_id}";
        }
        if ($order_status) {
            $where .= " AND so.status = {$order_status}";
        }
        // 交易状态 完成 未完成
        if ($transaction_type) {
            if ($transaction_type == 1) {
                $where .= ' AND so.status IN (4,5)';
            } else {
                $where .= ' AND so.status NOT IN (4,5)';
            }
        }
        // 交易币种筛选
        if ($display_currency_type !== false) {
            switch ($display_currency_type) {
                case 0:
                    $where .= ' AND so.pay_type = 0';
                    break;
                case 1:
                    $where .= ' AND so.pay_type IN (1,2)';
                    break;
                case 2:
                    $where .= ' AND so.pay_type = 3';
                    break;
                default :
                    break;
            }
        }
        if ($start_time) {
            $where .= " AND so.create_time > {$start_time}";
        }
        if ($end_time) {
            $where .= " AND so.create_time < {$end_time}";
        }

        if ($rmb_turnover_sort_rules == 1) {
            $sort = ['rmb_total' => SORT_ASC];
        } elseif ($rmb_turnover_sort_rules == 2) {
            $sort = ['rmb_total' => SORT_DESC];
        }
        if ($hwb_turnover_sort_rules == 1) {
            $sort = ['hwb_total' => SORT_ASC];
        } elseif ($hwb_turnover_sort_rules == 2) {
            $sort = ['hwb_total' => SORT_DESC];
        }

        $order_goods_count = ShopOrder::find()
            ->alias("so")
            ->select("so.order_id, g.goods_id, g.huiwenbi as goods_hwb_price, g.rmb_price as goods_rmb_price, so.status")
            ->leftJoin(OrderGoodsRelation::tableName().' ogr', 'so.order_id = ogr.order_id')
            ->leftJoin(Goods::tableName(). ' g', 'ogr.goods_id = g.goods_id')
            ->where($where)
            ->orderBy('so.create_time DESC')
            ->count();

        if ($export_data) {
            $order_goods_list = ShopOrder::find()
                ->alias("so")
                ->select([
                    'so.order_id',
                    'g.goods_id',
                    'g.goods_name',
                    'ogr.goods_num',
                    New Expression('IF(ogr.goods_hwb_price, ogr.goods_hwb_price, g.huiwenbi) as goods_hwb_price'),
                    New Expression('IF(ogr.goods_rmb_price, ogr.goods_rmb_price, g.rmb_price) as goods_rmb_price'),
                    New Expression('(ogr.goods_num * goods_hwb_price) as hwb_total'),
                    New Expression('(ogr.goods_num * goods_rmb_price) as rmb_total'),
                    'so.status'
                ])
                ->leftJoin(OrderGoodsRelation::tableName().' ogr', 'so.order_id = ogr.order_id')
                ->leftJoin(Goods::tableName(). ' g', 'ogr.goods_id = g.goods_id')
                ->where($where)
                ->orderBy('so.create_time DESC')
                ->orderBy($sort)
                ->asArray()->all();
        } else {
            $order_goods_list = ShopOrder::find()
                ->alias("so")
                ->select([
                    'so.order_id',
                    'g.goods_id',
                    'g.goods_name',
                    'ogr.goods_num',
                    New Expression('IF(ogr.goods_hwb_price, ogr.goods_hwb_price, g.huiwenbi) as goods_hwb_price'),
                    New Expression('IF(ogr.goods_rmb_price, ogr.goods_rmb_price, g.rmb_price) as goods_rmb_price'),
                    New Expression('(ogr.goods_num * goods_hwb_price) as hwb_total'),
                    New Expression('(ogr.goods_num * goods_rmb_price) as rmb_total'),
                    'so.status'
                ])
                ->leftJoin(OrderGoodsRelation::tableName().' ogr', 'so.order_id = ogr.order_id')
                ->leftJoin(Goods::tableName(). ' g', 'ogr.goods_id = g.goods_id')
                ->where($where)
                ->orderBy('so.create_time DESC')
                ->orderBy($sort)
                ->offset(($page - 1) * $size)
                ->limit($size)
                ->asArray()->all();
//            ->createCommand()->getRawSql(); echo $order_goods_list;die();
        }


        $sales_data['rmb_turnover'] = 0;
        $sales_data['rmb_completed_order_turnover'] = 0;
        $sales_data['rmb_incomplete_order_turnover'] = 0;

        $sales_data['hwb_turnover'] = 0;
        $sales_data['hwb_completed_order_turnover'] = 0;
        $sales_data['hwb_incomplete_order_turnover'] = 0;
        foreach ($order_goods_list as $order) {
            if (in_array($order['status'], [4,5])) {
                $sales_data['rmb_completed_order_turnover'] += $order['rmb_total'];
                $sales_data['hwb_completed_order_turnover'] += $order['hwb_total'];
            } else {
                $sales_data['rmb_incomplete_order_turnover'] += $order['rmb_total'];
                $sales_data['hwb_incomplete_order_turnover'] += $order['hwb_total'];
            }
        }

        $sales_data['rmb_turnover'] = round($sales_data['rmb_completed_order_turnover'] + $sales_data['rmb_incomplete_order_turnover'], 2);
        $sales_data['hwb_turnover'] = intval($sales_data['hwb_completed_order_turnover'] + $sales_data['hwb_incomplete_order_turnover']);

        $order_list = ['count'=>$order_goods_count, 'list'=>$order_goods_list];
        return ['sales_order_data'=>$sales_data, 'order_list'=>$order_list];
    }

    /**
     * @param int $company_id
     * @param string $live_name
     * @param int $display_currency_type
     * @param int $order_status
     * @param int $start_time
     * @param int $end_time
     * @param int $page
     * @param int $size
     * @return array
     */
    public static function getLiveSalesGoodsStatistic($company_id = 0, $live_name = '', $display_currency_type = 0, $order_status = 0, $start_time = 0, $end_time = 0, $page = 1, $size = 10)
    {
        $where_condition = 'sg.id > 0';
        if ($company_id) {
            $where_condition .= ' AND au.company_id ='.$company_id;
        }
        if ($live_name) {
            $where_condition .= " AND ls.title LIKE '%{$live_name}%'";
        }
        if ($display_currency_type) {
            if ($display_currency_type == 1) {
                // 只显示汇闻币的交易
                $where_condition .= ' AND so.pay_type = 0';
            } elseif ($display_currency_type == 2) {
                // 显示混合支付的交易
                $where_condition .= ' AND so.pay_type IN (1,2,3)';
            }
        }
        if ($order_status) {
            if ($order_status == 1) {
                $where_condition .= ' AND so.status IN (4,5)';
            } elseif ($order_status == 2) {
                $where_condition .= ' AND so.status NOT IN (4,5)';
            }
        }
        if ($start_time) {
            $where_condition .= ' AND so.create_time > '.$start_time;
        }
        if ($end_time) {
            $where_condition .= ' AND so.create_time < '.$end_time;
        }

        $list = LiveNew::find()
            ->alias('ln')
            ->select([
                'au.company_id', 'ls.section_id', 'ls.title', 'au.username', 'au.user_email',
                new Expression("IF(ogr.id, SUM(ogr.goods_num), '-') as sale_goods_count"),
                new Expression("IF(ogr.id, SUM(ogr.goods_hwb_price), '-') as sale_hwb_count"),
                new Expression("IF(ogr.id, SUM(ogr.goods_rmb_price), '-') as sale_rmb_count"),
                new Expression("GROUP_CONCAT(so.order_id) as order_ids"),
            ])
            ->leftJoin('vradmin1.admin_user au', 'ln.creator_id = au.admin_id')
            ->leftJoin('vrlive.live_section ls', 'ln.live_id = ls.live_id')
            ->leftJoin('vrlive.section_goods sg', 'ls.section_id = sg.section_id')
            ->leftJoin('vrshop.order_goods_relation ogr', 'sg.good_id = ogr.goods_id')
            ->leftJoin('vrshop.shop_order so', 'ogr.order_id = so.order_id')
            ->where($where_condition)
            ->groupBy('ls.section_id')
            ->offset(($page - 1) * $size)
            ->limit($size)
            ->asArray()->all();

        $count = LiveNew::find()
            ->alias('ln')
            ->leftJoin('vradmin1.admin_user au', 'ln.creator_id = au.admin_id')
            ->leftJoin('vrlive.live_section ls', 'ln.live_id = ls.live_id')
            ->leftJoin('vrlive.section_goods sg', 'ls.section_id = sg.section_id')
            ->leftJoin('vrshop.order_goods_relation ogr', 'sg.good_id = ogr.goods_id')
            ->leftJoin('vrshop.shop_order so', 'ogr.order_id = so.order_id')
            ->where($where_condition)
            ->groupBy('ls.section_id')
            ->count();

        $sales_data['rmb_turnover'] = 0;
        $sales_data['rmb_completed_order_turnover'] = 0;
        $sales_data['rmb_incomplete_order_turnover'] = 0;

        $sales_data['hwb_turnover'] = 0;
        $sales_data['hwb_completed_order_turnover'] = 0;
        $sales_data['hwb_incomplete_order_turnover'] = 0;
        foreach ($list as $key=>$item) {
            if ($item['order_ids']) {
                $order_info = yii::$app->db->createCommand("
                        SELECT 
                          so.order_id, ogr.goods_id, ogr.goods_num, ogr.goods_hwb_price, ogr.goods_rmb_price, so.status 
                        FROM vrshop.shop_order so 
                        LEFT JOIN vrshop.order_goods_relation ogr ON so.order_id = ogr.order_id 
                        WHERE so.order_id IN ({$item['order_ids']})
                    ")->queryAll();
                foreach ($order_info as $k=>$order) {
                    if (in_array($order['status'], [4,5])) {
                        $sales_data['hwb_completed_order_turnover']  += $order['goods_hwb_price'] * $order['goods_num'];
                        $sales_data['rmb_completed_order_turnover']  += $order['goods_rmb_price'] * $order['goods_num'];
                    } else {
                        $sales_data['hwb_incomplete_order_turnover'] += $order['goods_hwb_price'] * $order['goods_num'];
                        $sales_data['rmb_incomplete_order_turnover'] += $order['goods_rmb_price'] * $order['goods_num'];
                    }
                }
            }
        }

        $sales_data['rmb_turnover'] = round($sales_data['rmb_completed_order_turnover'] + $sales_data['rmb_incomplete_order_turnover'], 2);
        $sales_data['hwb_turnover'] = intval($sales_data['hwb_completed_order_turnover'] + $sales_data['hwb_incomplete_order_turnover']);

        return ['sale_data'=>$sales_data, 'list'=>['count'=>$count, 'list'=>$list]];
    }

    /**
     * @param int $live_id
     * @param int $display_currency_type
     * @param string $goods_name
     * @param int $start_time
     * @param int $end_time
     * @param int $page
     * @param int $size
     */
    public static function getLiveSalesGoodsInfo($section_id = 0, $display_currency_type = false, $goods_name = '', $start_time = 0, $end_time = 0, $page = 1, $size = 10){
        $where = '1';
        if ($section_id) {
            $where .= ' AND sg.section_id = '.$section_id;
        }
        // 交易币种筛选
        if ($display_currency_type !== false) {
            switch ($display_currency_type) {
                case 0:
                    $where .= ' AND so.pay_type = 0';
                    break;
                case 1:
                    $where .= ' AND so.pay_type IN (1,2)';
                    break;
                case 2:
                    $where .= ' AND so.pay_type = 3';
                    break;
                default :
                    break;
            }
        }
        if ($goods_name) {
            $where .= " AND g.goods_name LIKE '%{$goods_name}%'";
        }
        if ($start_time) {
            $where .= " AND so.create_time > {$start_time}";
        }
        if ($end_time) {
            $where .= " AND so.create_time < {$end_time}";
        }

        /* 直播员信息?? */
        $anchor_info = LiveSection::find()
            ->select('live_man_cate, live_man_alias, live_man_avatar_url')
            ->where(['section_id'=>$section_id])
            ->asArray()->one();

        /* 交易额 */
        $sales_data['rmb_turnover'] = 0;
        $sales_data['rmb_completed_order_turnover'] = 0;
        $sales_data['rmb_incomplete_order_turnover'] = 0;

        $sales_data['hwb_turnover'] = 0;
        $sales_data['hwb_completed_order_turnover'] = 0;
        $sales_data['hwb_incomplete_order_turnover'] = 0;

        /* 直播中售卖商品统计 */
        $list_count = SectionGoods::find()
            ->alias('sg')
            ->leftJoin('vrshop.goods g', 'sg.good_id = g.goods_id')
            ->leftJoin('vrshop.order_goods_relation ogr', 'g.goods_id = ogr.goods_id')
            ->leftJoin('vrshop.shop_order so', 'ogr.order_id = so.order_id AND so.order_type = 1')
            ->where($where)
            ->groupBy('sg.good_id')
            ->count();

        $list = SectionGoods::find()
            ->alias('sg')
            ->select('ogr.order_id, g.goods_id, g.goods_name, g.huiwenbi, g.rmb_price, ogr.goods_num, ogr.goods_rmb_price, ogr.goods_hwb_price, so.status')
            ->leftJoin('vrshop.goods g', 'sg.good_id = g.goods_id')
            ->leftJoin('vrshop.order_goods_relation ogr', 'g.goods_id = ogr.goods_id AND ogr.from_live_id > 0')
            ->leftJoin('vrshop.shop_order so', 'ogr.order_id = so.order_id')
            ->where($where)
            ->asArray()->all();

        $group_list = [];
        foreach ($list as $key=>$item) {
            if (in_array($item['status'], [4,5])) {
                $sales_data['rmb_completed_order_turnover'] += $item['goods_rmb_price'];
                $sales_data['hwb_completed_order_turnover'] += $item['goods_hwb_price'];
            } else {
                $sales_data['rmb_incomplete_order_turnover'] += $item['goods_rmb_price'];
                $sales_data['hwb_incomplete_order_turnover'] += $item['goods_hwb_price'];
            }

            /* 分组统计 */
            $group_list[$item['goods_id']]['goods_id'] = $item['goods_id'];
            $group_list[$item['goods_id']]['goods_name'] = $item['goods_name'];
            $group_list[$item['goods_id']]['goods_hwb_price'] = $item['huiwenbi'];
            $group_list[$item['goods_id']]['goods_rmb_price'] = $item['rmb_price'];
            @$group_list[$item['goods_id']]['sale_hwb_count'] += $item['goods_hwb_price'];
            @$group_list[$item['goods_id']]['sale_rmb_count'] += $item['goods_rmb_price'];
            @$group_list[$item['goods_id']]['sale_goods_count'] += $item['goods_num'];
        }


        $sales_data['rmb_turnover'] = round($sales_data['rmb_completed_order_turnover'] + $sales_data['rmb_incomplete_order_turnover'], 2);
        $sales_data['hwb_turnover'] = intval($sales_data['hwb_completed_order_turnover'] + $sales_data['hwb_incomplete_order_turnover']);

        return ['sales_data'=>$sales_data, 'anchor_info'=>$anchor_info, 'sales_goods_list'=>['count'=>$list_count, 'list'=>$group_list]];
    }

    /**
     * @param $company_id
     * @param $pusher_clerk_name
     * @param $rmb_turnover_sort_rules
     * @param $hwb_turnover_sort_rules
     * @param $page
     * @param $size
     */
    public static function pusherClerkGoodsSaleStatistic($company_id = 0, $pusher_clerk_name = '', $rmb_turnover_sort_rules = false, $hwb_turnover_sort_rules = false, $page = 1, $size = 10)
    {
        $where = '1';
        $sort = '';
        if ($company_id) {
            $where .= ' AND au.company_id = '.intval($company_id);
        }
        if ($pusher_clerk_name) {
            $where .= " AND au.username like '%{$pusher_clerk_name}%'";
        }
        if ($rmb_turnover_sort_rules !== false) {
            if ($rmb_turnover_sort_rules == 1) {
                $sort['sale_rmb_count'] = SORT_ASC;
            } else {
                $sort['sale_rmb_count'] = SORT_DESC;
            }
        }
        if ($hwb_turnover_sort_rules !== false) {
            if ($hwb_turnover_sort_rules == 1) {
                $sort['sale_hwb_count'] = SORT_ASC;
            } else {
                $sort['sale_hwb_count'] = SORT_DESC;
            }
        }

        $count = LiveCameraAngle::find()
            ->alias('lca')
            ->select([
                'lca.operator_id',
                'au.company_id',
                New Expression('SUM(ogr.goods_num) AS sale_count'),
                New Expression('SUM(ogr.goods_num * ogr.goods_rmb_price) AS sale_rmb_count'),
                New Expression('SUM(ogr.goods_num * ogr.goods_hwb_price) AS sale_hwb_count')
            ])
            ->innerJoin('vradmin1.admin_user au', 'lca.operator_id = au.admin_id')
            ->innerJoin('vrshop.order_goods_relation ogr', 'lca.live_id = ogr.from_live_id')
            ->where($where)
            ->groupBy('lca.operator_id')
            ->count();

        $list = LiveCameraAngle::find()
            ->alias('lca')
            ->select([
                'lca.operator_id',
                'au.username',
                'au.user_email',
                'ogr.goods_id',
                New Expression('SUM(ogr.goods_num) as sale_count'),
                New Expression('SUM(ogr.goods_num * ogr.goods_rmb_price) as sale_rmb_count'),
                New Expression('SUM(ogr.goods_num * ogr.goods_hwb_price) as sale_hwb_count')
            ])
            ->innerJoin('vradmin1.admin_user au', 'lca.operator_id = au.admin_id')
            ->innerJoin('vrshop.order_goods_relation ogr', 'lca.live_id = ogr.from_live_id')
            ->leftJoin('vrshop.shop_order so', 'ogr.order_id = so.order_id')
            ->where($where)
            ->groupBy('lca.operator_id')
            ->orderBy($sort)
            ->asArray()->all();

        return ['count'=>$count, 'list'=>$list];
    }

    public static function pusherClerkSaleInfo($pusher_clerk_id = 0, $live_name = '', $page = 1, $size = 10)
    {
        $where = '1';
        if ($pusher_clerk_id) {
            $where .= '';
        }
        if ($live_name) {
            $where .= " AND ls.title LIKE '%{$live_name}%'";
        }

        $count = LiveCameraAngle::find()
            ->alias('lca')
            ->innerJoin('vradmin1.admin_user au', 'lca.operator_id = au.admin_id')
            ->innerJoin('vrlive.live_section ls', 'lca.live_id = ls.section_id')
            ->leftJoin('vrlive.section_goods sg', 'ls.section_id = sg.section_id')
            ->leftJoin('vrshop.order_goods_relation ogr', 'sg.good_id = ogr.goods_id and ogr.from_live_id = sg.section_id')
            ->where($where)
            ->groupBy('ls.section_id')
            ->count();

        $list = LiveCameraAngle::find()
            ->alias('lca')
            ->select([
                'lca.live_id', 'ls.title', 'au.username', 'au.user_email',
                new Expression('sum(ogr.goods_hwb_price * ogr.goods_num) as sale_hwb_count'),
                new Expression('sum(ogr.goods_rmb_price * ogr.goods_num) as sale_rmb_count'),
                'c.name'
            ])
            ->innerJoin('vradmin1.admin_user au', 'lca.operator_id = au.admin_id')
            ->leftJoin('vrnews1.company c', 'au.company_id = c.name')
            ->innerJoin('vrlive.live_section ls', 'lca.live_id = ls.section_id')
            ->leftJoin('vrlive.section_goods sg', 'ls.section_id = sg.section_id')
            ->leftJoin('vrshop.order_goods_relation ogr', 'sg.good_id = ogr.goods_id and ogr.from_live_id = sg.section_id')
            ->where($where)
            ->groupBy('ls.section_id')
            ->limit($size)
            ->offset(($page - 1) * $size)
            ->asArray()->all();


        /* 交易额统计 */
        $sales_data['rmb_turnover'] = 0;
        $sales_data['rmb_completed_order_turnover'] = 0;
        $sales_data['rmb_incomplete_order_turnover'] = 0;

        $sales_data['hwb_turnover'] = 0;
        $sales_data['hwb_completed_order_turnover'] = 0;
        $sales_data['hwb_incomplete_order_turnover'] = 0;
        $sale_list = LiveCameraAngle::find()
            ->alias('lca')
            ->select('ogr.goods_id, ogr.goods_num, ogr.goods_rmb_price, ogr.goods_hwb_price, so.`status`')
            ->innerJoin('vrlive.live_section ls', 'lca.live_id = ls.section_id')
            ->leftJoin('vrlive.section_goods sg', 'ls.section_id = sg.section_id')
            ->innerJoin('vrshop.order_goods_relation ogr', 'sg.good_id = ogr.goods_id and ogr.from_live_id = sg.section_id')
            ->leftJoin('vrshop.shop_order so', 'ogr.order_id = so.order_id')
            ->where($where)
            ->limit($size)
            ->offset(($page - 1) * $size)
            ->asArray()->all();
        if ($sale_list) {
            foreach ($sale_list as $key=>$order) {
                if (in_array($order['status'], [4,5])) {
                    $sales_data['rmb_completed_order_turnover'] += $order['goods_rmb_price'];
                    $sales_data['hwb_completed_order_turnover'] += $order['goods_hwb_price'];
                } else {
                    $sales_data['rmb_incomplete_order_turnover'] += $order['goods_rmb_price'];
                    $sales_data['hwb_incomplete_order_turnover'] += $order['goods_hwb_price'];
                }
            }
        }
        $sales_data['rmb_turnover'] = round($sales_data['rmb_completed_order_turnover'] + $sales_data['rmb_incomplete_order_turnover'], 2);
        $sales_data['hwb_turnover'] = intval($sales_data['hwb_completed_order_turnover'] + $sales_data['hwb_incomplete_order_turnover']);


        return ['sale_data'=>$sales_data, 'list'=>['count'=>$count, 'list'=>$list]];
    }
}