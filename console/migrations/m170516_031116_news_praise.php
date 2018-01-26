<?php

use yii\db\Migration;

class m170516_031116_news_praise extends Migration
{
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->db = yii::$app->vrnews1;
    }

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='投票'";
        }
        $this->createTable('{{news_praise}}',[
            "id" =>$this->primaryKey(),
            "news_id" => $this->bigInteger(20)->notNull()->defaultValue(0)->comment('新闻的ID'),
            "user_id" => $this->bigInteger(20)->notNull()->defaultValue(0)->comment('前台用户的ID'),
            "create_time" => $this->decimal(8,2)->notNull()->defaultValue(0)->comment('点赞时间'),
            "status" => $this->integer(10)->notNull()->defaultValue(0)->comment('点赞状态'),
        ],$tableOptions);

        $this->createIndex('news_id', 'news_praise', ['news_id']);
        $this->createIndex('user_id', 'news_praise', ['user_id']);
    }

    public function down()
    {
        echo "m170516_031116_news_praise cannot be reverted.\n";

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
