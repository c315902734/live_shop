<?php

use yii\db\Migration;

class m170824_070019_update_shop_order extends Migration
{
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->db = yii::$app->vrshop;
    }

    public function up()
    {
        $this->addColumn('vrshop.shop_order', 'rmb_price', "DECIMAL(8,2) UNSIGNED  NOT NULL DEFAULT '0' COMMENT '人民币价格' AFTER `huiwenbi`");
    }

    public function down()
    {
        echo "m170824_070019_update_shop_order cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
