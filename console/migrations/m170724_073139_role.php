<?php

use yii\db\Migration;

class m170724_073139_role extends Migration
{
    public function init(){
        $this->db = Yii::$app->vradmin1;
        parent::init();
    }

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='角色表'";
        }
        $this->createTable('{{role}}',[
            "role_id" =>$this->primaryKey(),
            "role_name" => "varchar(200) NOT NULL COMMENT '角色名称' ",
            "create_time" => "int(10) NOT NULL DEFAULT 0 COMMENT '创建时间' ",
            "desc" => "varchar(255) NOT NULL DEFAULT '' COMMENT '描述' "
        ],$tableOptions);
    }

    public function down()
    {
        echo "m170724_073139_role cannot be reverted.\n";

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
