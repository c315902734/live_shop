<?php

use yii\db\Migration;

class m171010_082835_ad_startup_admin extends Migration
{
    public function init()
    {
        $this->db = Yii::$app->vrnews1;
        parent::init();
    }
    
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='后台广告启动页管理'";
        }
        $this->createTable('{{ad_startup_admin}}', [
            "id"          => $this->primaryKey(),// "bigint(20) NOT NULL AUTO_INCREMENT COMMENT '广告ID'",
            "admin_id"    => "bigint(20) NOT NULL COMMENT '管理者id'",
            "title"       => "varchar(255) NOT NULL COMMENT '广告标题'",
            "is_active"   => "tinyint(4) DEFAULT '0' COMMENT '是否启用：0:禁用；1：启用'",
            "create_time" => "datetime DEFAULT NULL COMMENT '创建时间'",
            "update_time" => "datetime DEFAULT NULL COMMENT '更新时间'",
        ], $tableOptions);
    }
    
    public function down()
    {
        echo "m171010_082835_ad_startup_admin cannot be reverted.\n";
        
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
