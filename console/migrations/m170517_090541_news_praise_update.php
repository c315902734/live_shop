<?php

use yii\db\Migration;

class m170517_090541_news_praise_update extends Migration
{
    public function init()
    {
        $this->db = Yii::$app->vrnews1;
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function up()
    {
        $this->alterColumn('{{news_praise}}', 'create_time', "int(10) default 0");
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
