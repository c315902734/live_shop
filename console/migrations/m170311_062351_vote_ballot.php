<?php

use yii\db\Migration;

class m170311_062351_vote_ballot extends Migration
{
    public function init(){
        $this->db = Yii::$app->vrnews1;
        parent::init();
    }


    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT=''";
        }
        $this->createTable('{{vote_ballot}}',[
                "ballot_id" =>$this->primaryKey(),
                "vote_id" => "bigint(20) NOT NULL DEFAULT 0 COMMENT '投票ID'",
                "option_id" => "bigint(20) NOT NULL DEFAULT 0 COMMENT '选项ID' ",
                "user_id" => "bigint(20) NOT NULL DEFAULT 0 COMMENT '用户ID' ",
                "create_time" => "int(10)  DEFAULT 0 COMMENT '投票时间' "
        ],$tableOptions);
    }

    public function down()
    {
        echo "m170311_062351_vote_ballot cannot be reverted.\n";

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
