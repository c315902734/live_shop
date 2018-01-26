<?php

use yii\db\Migration;

class m171218_055803_update_hwb_pool extends Migration
{
    public function init()
    {
        parent::init();
        $this->db = yii::$app->vradmin1;
    }

    public function safeUp()
    {
        $this->addColumn(\common\models\HwbPool::tableName(), 'pool_type', "TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '类型 1：活动 2：运营 3：任务' AFTER `balance`");
        $this->update(\common\models\HwbPool::tableName(), ['pool_type' => 1], '1');
    }

    public function safeDown()
    {
        echo "m171218_055803_update_hwb_pool cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171218_055803_update_hwb_pool cannot be reverted.\n";

        return false;
    }
    */
}
