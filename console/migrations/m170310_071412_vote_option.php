<?php

use yii\db\Migration;

class m170310_071412_vote_option extends Migration
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
        $this->createTable('{{vote_option}}',[
                "option_id" =>$this->primaryKey(),
                "vote_id" => "bigint(20) NOT NULL DEFAULT 0 COMMENT '投票ID'",
                "group_id" => "bigint(20) NOT NULL DEFAULT 0 COMMENT '组ID' ",
                "class_id" => "bigint(20) NOT NULL DEFAULT 0 COMMENT '类ID' ",
                "cover_image" => "varchar(255)  DEFAULT '' COMMENT '图片' ",
                "name" => "varchar(100) NOT NULL DEFAULT '' COMMENT '名称' ",
                "abstract" => "varchar(255) NOT NULL DEFAULT '' COMMENT '描述' ",
                "create_time" => "int(10) NOT NULL DEFAULT 0 COMMENT '创建时间' "
        ],$tableOptions);
    }

    public function down()
    {
        echo "m170310_071412_vote_option cannot be reverted.\n";

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
