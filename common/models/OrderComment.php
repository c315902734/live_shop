<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "order_comment".
 *
 * @property integer $comment_id
 * @property string $company_id
 * @property integer $order_id
 * @property string $goods_id
 * @property string $user_id
 * @property integer $examine_status
 * @property string $examine_reason
 * @property string $ip_address
 * @property string $create_time
 * @property string $content
 * @property integer $is_del
 */
class OrderComment extends \yii\db\ActiveRecord
{
    public static function getDb()
    {
        return yii::$app->vrshop;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_comment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'order_id', 'goods_id', 'user_id', 'examine_status', 'is_del'], 'integer'],
            [['create_time'], 'safe'],
            [['content'], 'string'],
            [['examine_reason'], 'string', 'max' => 255],
            [['ip_address'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'comment_id' => 'Comment ID',
            'company_id' => 'Company ID',
            'order_id' => 'Order ID',
            'goods_id' => 'Goods ID',
            'user_id' => 'User ID',
            'examine_status' => 'Examine Status',
            'examine_reason' => 'Examine Reason',
            'ip_address' => 'Ip Address',
            'create_time' => 'Create Time',
            'content' => 'Content',
            'is_del' => 'Is Del',
        ];
    }

    /**
     * 后台-评论列表
     * @param $company_id
     * @param $comment_status
     * @param $order_id
     * @param $consignee
     * @param $goods_name
     * @param $start_time
     * @param $end_time
     * @param $page
     * @param $size
     */
    public static function CommentList($company_id, $comment_status, $order_id, $consignee, $goods_name, $start_time, $end_time, $page = 1, $size = 10){
        $where = '1 = 1 AND oc.is_del = 0';
        if($company_id){
            $where .= " AND oc.company_id = {$company_id}";
        }
        if($comment_status){
            $where .= " AND oc.examine_status = {$comment_status}";
        }
        if($order_id){
            $where .= " AND oc.order_id = {$order_id}";
        }
        if($consignee){
            $where .= " AND ua.consignee LIKE '%{$company_id}%'";
        }
        if($goods_name){
            $where .= " AND g.goods_name LIKE '%{$company_id}%'";
        }
        if($start_time){
            $where .= " AND oc.create_time > {$start_time}";
        }
        if($end_time){
            $where .= " AND oc.create_time < {$end_time}";
        }

        $offset = ($page - 1) * $size;

        $count = self::find()
            ->alias('oc')
            ->leftJoin('vrshop.shop_order o', 'oc.order_id = o.order_id')
            ->leftJoin('vrshop.goods g', 'oc.goods_id = g.goods_id')
            ->leftJoin('vrshop.user_address ua', 'o.reciver_id = ua.address_id')
            ->where($where)
            ->orderBy('oc.create_time DESC')
            ->asArray()
            ->count();

        $list = self::find()
            ->alias('oc')
            ->select([
                'oc.comment_id',
                'o.order_number',
                'g.goods_name',
                'ua.consignee',
                'oc.score',
                'oc.ip_address',
                'oc.create_time',
                'oc.content',
                'oc.examine_status',
            ])
            ->leftJoin('vrshop.shop_order o', 'oc.order_id = o.order_id')
            ->leftJoin('vrshop.goods g', 'oc.goods_id = g.goods_id')
            ->leftJoin('vrshop.user_address ua', 'o.reciver_id = ua.address_id')
            ->where($where)
            ->orderBy('oc.create_time DESC')
            ->offset($offset)
            ->limit($size)
            ->asArray()
            ->all();

        $list  || $list = [];
        $count || $count = [];

        $return_data = ['count'=>$count, 'list'=>$list];
        return $return_data;
    }

    /**
     * 前台-添加评论
     * @param $order_id
     * @param $goods_id
     * @param $user_id
     * @param $comment
     * @param $ip_addr
     * @param $examine_status
     * @param int $is_del
     * @return bool
     */
    public static function AddComment($company_id, $order_id, $goods_id, $user_id, $comment, $goods_score, $ip_addr, $examine_status, $is_del = 0){
        $comment_model = new self;
        $comment_model->company_id = $company_id;
        $comment_model->order_id = $order_id;
        $comment_model->goods_id = $goods_id;
        $comment_model->user_id  = $user_id;
        $comment_model->content  = $comment;
        $comment_model->score    = $goods_score;
        $comment_model->ip_address  = $ip_addr;
        $comment_model->create_time = date('Y-m-d H:i:s', time());
        $comment_model->examine_status = $examine_status;
        $comment_model->is_del = $is_del;

        $ret = $comment_model->save();
        return $ret;
    }

    /**
     * 前台-商品评价列表
     * @param $company_id
     * @param $goods_id
     * @param $comment_pass_code
     * @param $comment_not_del_code
     * @param int $page
     * @param int $size
     * @return array
     */
    public static function GetGoodsCommentList($company_id, $goods_id, $comment_pass_code, $comment_not_del_code, $page = 1, $size = 10){
        $offset = ($page - 1) * $size;
        $count = OrderComment::find()
            ->alias('c')
            ->select(['c.comment_id', 'u.nickname', 'c.content', 'c.create_time'])
            ->leftJoin('vruser1.user u', 'c.user_id = u.user_id')
            ->where(['goods_id'=>$goods_id, 'examine_status'=>$comment_pass_code, 'is_del'=>$comment_not_del_code])
            ->orderBy('create_time DESC')
            ->asArray()
            ->count();
        $count || $count = 0;

        $list = OrderComment::find()
            ->alias('c')
            ->select(['c.comment_id', 'u.nickname', 'c.content', 'c.create_time'])
            ->leftJoin('vruser1.user u', 'c.user_id = u.user_id')
            ->where(['goods_id'=>$goods_id, 'examine_status'=>$comment_pass_code, 'is_del'=>$comment_not_del_code])
            ->orderBy('create_time DESC')
            ->offset($offset)
            ->limit($size)
            ->asArray()
            ->all();
        $list || $list = [];

        $return_data = ['count'=>$count, 'list'=>$list];
        return $return_data;
    }
}

