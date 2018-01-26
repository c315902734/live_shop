<?php

use yii\db\Migration;

class m170815_093652_admin_role extends Migration
{
    public function init(){
        $this->db = Yii::$app->vradmin1;
        parent::init();
    }

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='管理员角色表'";
        }
        $this->createTable('{{admin_role}}',[
            "id" =>$this->primaryKey(),
            "admin_id" => "int(11) NOT NULL COMMENT '管理员ID' ",
            "role_id" => "int(11) NOT NULL COMMENT '角色ID' ",
            "create_time" => "int(10) NOT NULL DEFAULT 0 COMMENT '创建时间' "
        ],$tableOptions);
    }

    public function down()
    {
        echo "m170815_093652_admin_role cannot be reverted.\n";

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
