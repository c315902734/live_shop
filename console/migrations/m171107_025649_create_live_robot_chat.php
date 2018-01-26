<?php

use yii\db\Migration;

class m171107_025649_create_live_robot_chat extends Migration
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
        $this->createTable('{{live_robot_chat}}',[
            "id" =>$this->primaryKey(),
            "name" => "varchar(50) DEFAULT NULL COMMENT '问题人姓名' ",
            "photo" => "varchar(200) DEFAULT NULL COMMENT '问题人头像' ",
            "type" => "tinyint(4) DEFAULT NULL COMMENT '问题类型 1消息 2送礼 3打招呼 4问答' ",
            "target" => "varchar(50) DEFAULT NULL COMMENT '问题目标' ",
            "content" => "varchar(255) DEFAULT NULL COMMENT '问题内容' ",
            "num" => "int(10) NOT NULL DEFAULT 1 COMMENT '发送次数' ",
            "live_id" => "bigint(20) NOT NULL DEFAULT 0 COMMENT '直播id' ",
            "create_time" => "datetime DEFAULT null COMMENT '导入时间' ",
            "chat_type" => "tinyint(4) DEFAULT 1 COMMENT '聊天类型 1直播中 2预热' "
        ],$tableOptions);
    }

    public function down()
    {
        echo "m171107_025649_create_live_robot_chat cannot be reverted.\n";

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
