<?php

use yii\db\Migration;

class m170724_073215_role_power_relation extends Migration
{
    public function init(){
        $this->db = Yii::$app->vradmin1;
        parent::init();
    }

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='角色权限表'";
        }
        $this->createTable('{{role_power_relation}}',[
            "id" =>$this->primaryKey(),
            "role_id"  => "bigint(20) NOT NULL COMMENT '角色id'",
            "power_id"  => "bigint(20) NOT NULL COMMENT '权限id'",
            "action_ids"  => "varchar(200) DEFAULT '' COMMENT '权限功能id'",
            "create_time" => "int(10) NOT NULL DEFAULT 0 COMMENT '创建时间' ",
            "desc" => "varchar(255) NOT NULL DEFAULT '' COMMENT '描述' "
        ],$tableOptions);
    }

    public function down()
    {
        echo "m170724_073215_role_power_relation cannot be reverted.\n";

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
