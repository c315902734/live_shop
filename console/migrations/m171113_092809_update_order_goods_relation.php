<?php

use yii\db\Migration;

class m171113_092809_update_order_goods_relation extends Migration
{
    public function init()
    {
        parent::init();
        $this->db = yii::$app->vrshop;
    }

    public function safeUp()
    {
        $this->addColumn(\common\models\OrderGoodsRelation::tableName(), 'goods_hwb_price', "DECIMAL(8, 2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '商品汇闻币价格' AFTER `goods_num`");
        $this->addColumn(\common\models\OrderGoodsRelation::tableName(), 'goods_rmb_price', "DECIMAL(8, 2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '商品人民币价格' AFTER `goods_num`");
        $this->addColumn(\common\models\OrderGoodsRelation::tableName(), 'create_time', "INT(10) UNSIGNED NOT NULL DEFAULT 0");
    }

    public function safeDown()
    {
        echo "m171113_092809_update_order_goods_relation cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171113_092809_update_order_goods_relation cannot be reverted.\n";

        return false;
    }
    */
}
