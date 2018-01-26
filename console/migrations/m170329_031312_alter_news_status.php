<?php

use yii\db\Migration;

class m170329_031312_alter_news_status extends Migration
{
    public function init()
    {
        $this->db = Yii::$app->vrnews1;
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function up()
    {
        $this->addColumn('{{news}}', 'status', "tinyint(4) default 0 COMMENT '新闻状态，0 已发布，1草稿，2定时发布' ");
    }

    public function down()
    {
        echo "m170329_031312_alter_news_status cannot be reverted.\n";

        return false;
    }

    
}
