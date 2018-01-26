<?php

use yii\db\Migration;

class m171130_014504_update_section_goods extends Migration
{
    public function init()
    {
        parent::init();
        $this->db = yii::$app->vrlive;
    }

    public function safeUp()
    {
        $this->createIndex('section_id', \common\models\SectionGoods::tableName(), 'section_id');
    }

    public function safeDown()
    {
        echo "m171130_014504_update_section_goods cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171130_014504_update_section_goods cannot be reverted.\n";

        return false;
    }
    */
}
