<?php

use yii\db\Migration;

class m170323_063520_company extends Migration
{
    public function init(){
        $this->db = Yii::$app->vrnews1;
        parent::init();
    }

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";
        }
        $this->createTable('{{company}}',[
                "company_id" =>$this->primaryKey(),
                "name" => "varchar(255) DEFAULT NULL COMMENT '公司名称' ",
                "prov" => "varchar(100) DEFAULT NULL COMMENT '省' ",
                "city" => "varchar(100) DEFAULT NULL COMMENT '市' ",
                "introduction" => "varchar(255) DEFAULT NULL COMMENT '简介' ",
                "phone" => "varchar(100) DEFAULT NULL COMMENT '联系电话' ",
                "create_time" => "int(10) NOT NULL DEFAULT 0 COMMENT '' ",
                "status" => "tinyint(1) NOT NULL DEFAULT 1 COMMENT '' ",
        ],$tableOptions);
    }

    public function down()
    {
        echo "m170323_063520_company cannot be reverted.\n";

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
