<?php

use yii\db\Migration;

class m160924_041358_vote_client extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{vote_client}}',[
            'id' => $this->primaryKey(),
            'finger' => $this->string()->notNull(),//指纹码
            'vote_id' => $this->integer(10)->notNull(),//投票选手ID
            'created_at' => $this->integer()->notNull(),//投票时间
        ],$tableOptions);
    }

    public function down()
    {
        echo "m160924_041358_vote_client cannot be reverted.\n";

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
