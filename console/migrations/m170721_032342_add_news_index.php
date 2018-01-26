<?php

use yii\db\Migration;

class m170721_032342_add_news_index extends Migration
{
    public function init(){
        $this->db = Yii::$app->vrnews1;
        parent::init();
    }

    public function up()
    {
        $this->createIndex('type', 'news', ['type']);
    }

    public function down()
    {
        echo "m170721_032342_add_news_index cannot be reverted.\n";

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
