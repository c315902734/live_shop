<?php

use yii\db\Migration;

class m170912_054651_updata_shop_goods extends Migration
{
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->db = yii::$app->vrshop;
    }

    public function safeUp()
    {
        $this->addColumn('goods', 'abstract', "VARCHAR(200) NOT NULL DEFAULT '' COMMENT '摘要' AFTER `tags`");
    }

    public function safeDown()
    {
        echo "m170912_054651_updata_shop_goods cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170912_054651_updata_shop_goods cannot be reverted.\n";

        return false;
    }
    */
}
