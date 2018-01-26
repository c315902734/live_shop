<?php

use yii\db\Migration;

class m170310_071421_vote_class extends Migration
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
        $this->createTable('{{vote_class}}',[
                "class_id" =>$this->primaryKey(),
                "vote_id" => "bigint(20) NOT NULL DEFAULT 0 COMMENT '投票ID'",
                "parent_id" => "bigint(20) NOT NULL DEFAULT 0 COMMENT '父ID' ",
                "class_name" => "varchar(100) NOT NULL DEFAULT 0 COMMENT '类名' ",
                "create_time" => "varchar(255)  DEFAULT '' COMMENT '图片' "
        ],$tableOptions);
    }

    public function down()
    {
        echo "m170310_071421_vote_class cannot be reverted.\n";

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
