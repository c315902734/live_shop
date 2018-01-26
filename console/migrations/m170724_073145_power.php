<?php

use yii\db\Migration;

class m170724_073145_power extends Migration
{
    public function init(){
        $this->db = Yii::$app->vradmin1;
        parent::init();
    }

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='权限表'";
        }
        $this->createTable('{{power}}',[
            "power_id" =>$this->primaryKey(),
            "power_name" => "varchar(200) NOT NULL COMMENT '权限名称' ",
            "create_time" => "int(10) NOT NULL DEFAULT 0 COMMENT '创建时间' ",
            "desc" => "varchar(255) NOT NULL DEFAULT '' COMMENT '描述' "
        ],$tableOptions);
    }

    public function down()
    {
        echo "m170724_073145_power cannot be reverted.\n";

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
