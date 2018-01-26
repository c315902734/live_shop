<?php

use yii\db\Migration;

class m171107_091051_create_live_robot_chat_answer extends Migration
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
        $this->createTable('{{live_robot_chat_answer}}',[
            "id" =>$this->primaryKey(),
            "name" => "varchar(50) DEFAULT NULL COMMENT '回答人姓名' ",
            "photo" => "varchar(200) DEFAULT NULL COMMENT '回答人头像' ",
            "target" => "varchar(50) DEFAULT NULL COMMENT '回答目标' ",
            "content" => "varchar(255) DEFAULT NULL COMMENT '答案集' ",
            "num" => "int(10) NOT NULL DEFAULT 1 COMMENT '发送次数' ",
            "question_id" => "bigint(20) NOT NULL DEFAULT 0 COMMENT '问题id' ",
            "create_time" => "datetime DEFAULT null COMMENT '导入时间' "
        ],$tableOptions);
    }

    public function safeDown()
    {
        echo "m171107_091051_create_live_robot_chat_answer cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171107_091051_create_live_robot_chat_answer cannot be reverted.\n";

        return false;
    }
    */
}
