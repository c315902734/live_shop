<?php

use yii\db\Migration;

class m170323_063533_personnel extends Migration
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
        $this->createTable('{{personnel}}',[
                "personnel_id" =>$this->primaryKey(),
                "name" => "varchar(200) DEFAULT NULL COMMENT '员工姓名' ",
                "cover_image" => "varchar(255) DEFAULT NULL COMMENT '员工头像' ",
                "email" => "varchar(200)  DEFAULT NULL COMMENT '' ",
                "id_card" => "varchar(100) DEFAULT NULL COMMENT '身份证' ",
                "company_id" => "bigint(20) DEFAULT 0 COMMENT '所属公司ID' ",
                "create_time" => "int(10) NOT NULL DEFAULT 0 COMMENT '' "
        ],$tableOptions);
    }

    public function down()
    {
        echo "m170323_063533_personnel cannot be reverted.\n";

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
