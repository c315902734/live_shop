<?php

use yii\db\Migration;

class m170425_071112_sensitive_words extends Migration
{
    public function init(){
        $this->db = Yii::$app->vrnews1;
        parent::init();
    }

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='敏感词表'";
        }
        $this->createTable('{{sensitive_words}}',[
            "id" =>$this->primaryKey(),
            "words" => "varchar(255) NOT NULL COMMENT '敏感词' ",
            "create_time" => "int(10) DEFAULT NULL COMMENT '添加时间' "
        ],$tableOptions);
    }

    public function down()
    {
        echo "m170425_071112_sensitive_words cannot be reverted.\n";

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
