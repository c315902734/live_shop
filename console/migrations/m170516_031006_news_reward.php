<?php

use yii\db\Migration;

class m170516_031006_news_reward extends Migration
{
    public function init(){
        $this->db = Yii::$app->vrnews1;
        parent::init();
    }

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='投票'";
        }
        $this->createTable('{{news_reward}}',[
                "id" =>$this->primaryKey(),
                "news_id" => $this->bigInteger(20)->notNull()->defaultValue(0)->comment('新闻的ID'),
                "user_id" => $this->bigInteger(20)->notNull()->defaultValue(0)->comment('前台用户的ID'),
                "hw_money" => $this->decimal(8,2)->notNull()->defaultValue(0)->comment('汇闻币'),
                "create_time" => $this->integer(10)->notNull()->defaultValue(0)->comment('打赏时间'),
        ],$tableOptions);

        $this->createIndex('news_id', 'news_reward', ['news_id']);
        $this->createIndex('user_id', 'news_reward', ['user_id']);
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
