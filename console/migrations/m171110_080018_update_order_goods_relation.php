<?php

use yii\db\Migration;

class m171110_080018_update_order_goods_relation extends Migration
{
    public function init()
    {
        parent::init();
        $this->db = yii::$app->vrshop;
    }

    public function safeUp()
    {
        $this->addColumn(\common\models\OrderGoodsRelation::tableName(),'from_live_company_id', "INT(20) NOT NULL DEFAULT 0 COMMENT '直播间所属公司ID'");
    }

    public function safeDown()
    {
        echo "m171110_080018_update_order_goods_relation cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171110_080018_update_order_goods_relation cannot be reverted.\n";

        return false;
    }
    */
}
