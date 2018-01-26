<?php

use yii\db\Migration;

class m170310_070917_vote extends Migration
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
        $this->createTable('{{vote}}',[
                "vote_id" =>$this->primaryKey(),
                "title" => "varchar(255) NOT NULL COMMENT '' ",
                "vote_num" => "smallint(3) NOT NULL DEFAULT 0 COMMENT '投票次数' ",
                "huiwenbi" => "smallint(3) NOT NULL DEFAULT 0 COMMENT '投票完成增加的汇闻币' ",
                "cover_image" => "varchar(255) NOT NULL DEFAULT '' COMMENT '' ",
                "abstract" => "varchar(255) NOT NULL DEFAULT '' COMMENT '' ",
                "start_time" => "int(10) NOT NULL DEFAULT 0 COMMENT '' ",
                "end_time" => "int(10) NOT NULL DEFAULT 0 COMMENT '' ",
                "host" => "varchar(255) NOT NULL DEFAULT '' COMMENT '主办方' ",
                "contractors" => "varchar(255) NOT NULL DEFAULT 0 COMMENT '承办方' ",
                "vote" => "varchar(255) DEFAULT '' COMMENT '投票选项' ",
                "type" => "tinyint(1) NOT NULL DEFAULT 0 COMMENT '' ",
                "create_time" => "int(10) NOT NULL DEFAULT 0 COMMENT '投票选项' "
        ],$tableOptions);
    }

    public function down()
    {
        echo "m170310_070917_vote cannot be reverted.\n";

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
