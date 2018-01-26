<?php

use yii\db\Migration;

class m171113_082300_live_robot_chat_user extends Migration
{
    public function init(){
        $this->db = Yii::$app->vrlive;
        parent::init();
    }

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";
        }
        $this->createTable('{{live_robot_chat_user}}',[
            "id" =>$this->primaryKey(),
            "username" => "varchar(50) DEFAULT NULL COMMENT '用户名' ",
            "avatar" => "varchar(200) DEFAULT null COMMENT '用户头像' ",
            "status" => "tinyint(4) DEFAULT 1 COMMENT '用户状态 1正常 0删除' ",
            "create_time" => "datetime DEFAULT null COMMENT '导入时间' "
        ],$tableOptions);
    }

    public function safeDown()
    {
        echo "m171113_082300_live_robot_chat_user cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171113_082300_live_robot_chat_user cannot be reverted.\n";

        return false;
    }
    */
}
