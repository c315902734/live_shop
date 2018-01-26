<?php

use yii\db\Migration;

class m171130_014650_update_goods extends Migration
{
    public function init()
    {
        parent::init();
        $this->db = yii::$app->vrshop;
    }

    public function safeUp()
    {
        $this->alterColumn(\common\models\Goods::tableName(), 'goods_stock', 'INT UNSIGNED NOT NULL DEFAULT 0');
    }

    public function safeDown()
    {
        echo "m171130_014650_update_goods cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171130_014650_update_goods cannot be reverted.\n";

        return false;
    }
    */
}
