<?php

use yii\db\Migration;

class m170921_070857_up_activity_prize extends Migration
{
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->db = yii::$app->vrshop;
    }

    public function safeUp()
    {
        $this->dropColumn(\common\models\ActivityLotteryPrize::tableName(), 'hidename');
        $this->dropColumn(\common\models\ActivityLotteryPrize::tableName(), 'hidepic');
        $this->dropColumn(\common\models\ActivityLotteryPrize::tableName(), 'hidehuiwenbi');
    }

    public function safeDown()
    {
        echo "m170921_070857_up_activity_prize cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170921_070857_up_activity_prize cannot be reverted.\n";

        return false;
    }
    */
}
