<?php

use yii\db\Migration;

class m171011_085021_updata_shop_order extends Migration
{
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->db = yii::$app->vrshop;
    }

    public function safeUp()
    {
        $this->addColumn('{{shop_order}}', 'pingxx_sn', "varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'ping++订单号 用于退款' AFTER `order_number`");
    }

    public function safeDown()
    {
        echo "m171011_085021_updata_shop_order cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171011_085021_updata_shop_order cannot be reverted.\n";

        return false;
    }
    */
}