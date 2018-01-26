<?php

use yii\db\Migration;

class m171120_090713_update_order_goods extends Migration
{
    public function init()
    {
        parent::init();

        $this->db = yii::$app->vrshop;
    }

    public function safeUp()
    {
        $this->addColumn(\common\models\OrderGoodsRelation::tableName(), 'from_live_id', "INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '来自的直播ID' AFTER `virtual_goods_id`");
    }

    public function safeDown()
    {
        echo "m171120_090713_update_order_goods cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171120_090713_update_order_goods cannot be reverted.\n";

        return false;
    }
    */
}
