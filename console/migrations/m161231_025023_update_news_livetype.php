<?php

use yii\db\Migration;

class m161231_025023_update_news_livetype extends Migration
{
    public function init()
    {
        $this->db = Yii::$app->vrnews1;
        parent::init(); // TODO: Change the autogenerated stub
    }
    public function up()
    {
        $this->addColumn('{{news}}', 'live_status', "tinyint(4) default '0' COMMENT '直播来源状态，0创建直播同时创建新闻，1创建新闻关联直播'");
    }

    public function down()
    {
        echo "m161231_025023_update_news_livetype cannot be reverted.\n";

        return false;
    }

}
