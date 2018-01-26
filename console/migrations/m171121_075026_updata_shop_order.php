<?php

use yii\db\Migration;

class m171121_075026_updata_shop_order extends Migration
{
    public function init()
    {
        parent::init();
        $this->db = yii::$app->vrshop;
    }

    public function safeUp()
    {
        $this->dropColumn(\common\models\ShopOrder::tableName(), 'activity_id');
    }

    public function safeDown()
    {
        echo "m171121_075026_updata_shop_order cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171121_075026_updata_shop_order cannot be reverted.\n";

        return false;
    }
    */
}
