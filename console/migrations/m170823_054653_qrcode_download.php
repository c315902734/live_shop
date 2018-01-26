<?php

use yii\db\Migration;

class m170823_054653_qrcode_download extends Migration
{
    public function init(){
        $this->db = Yii::$app->vruser1;
        parent::init();
    }

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='扫描二维码下载统计'";
        }
        $this->createTable('{{qrcode_download}}',[
            "id" =>$this->primaryKey(),
            "type" => "tinyint(4) DEFAULT 1 COMMENT '1 ios 2 android' ",
            "create_time" => "datetime DEFAULT NULL COMMENT '创建时间' "
        ],$tableOptions);
    }

    public function down()
    {
        echo "m170823_054653_qrcode_download cannot be reverted.\n";

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
