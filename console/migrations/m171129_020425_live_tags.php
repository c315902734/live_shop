<?php

use yii\db\Migration;

class m171129_020425_live_tags extends Migration
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
        $this->createTable('{{live_tags}}',[
            "id" =>$this->primaryKey(),
            "tag_name" => "varchar(50) DEFAULT NULL COMMENT '标签名' ",
            "type" => "tinyint(4) DEFAULT 1 COMMENT '标签类型 1系统标签 2普通标签' ",
            "status" => "tinyint(4) DEFAULT 1 COMMENT '标签状态 1正常 0删除' ",
            "create_time" => "datetime DEFAULT null COMMENT '创建时间' ",
            "creator" => "bigint(20) DEFAULT null COMMENT '创建人' "
        ],$tableOptions);
    }

    public function safeDown()
    {
        echo "m171129_020425_live_tags cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171129_020425_live_tags cannot be reverted.\n";

        return false;
    }
    */
}
