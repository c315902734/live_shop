<?php

use yii\db\Migration;

class m171010_092159_ad_startup_images extends Migration
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
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='广告启动页图片'";
        }
        $this->createTable('{{ad_startup_images}}', [
            "id"            => $this->primaryKey(),// "bigint(20) NOT NULL AUTO_INCREMENT COMMENT '广告页图片ID'",
            "weight"        => "int(20) DEFAULT '0' COMMENT '重量'",//根据权重判定广告图片的弹出，默认0
            "term_id"       => "int(20) DEFAULT '0' COMMENT '栏位'",// 图片在广告页的创建、编辑页面的栏位，默认0
            "ad_startup_id" => "int(11) DEFAULT '0' COMMENT '启动页编号'",
            "file_url"      => "varchar(255) NOT NULL COMMENT '文件路径'",
            "create_time"   => "datetime DEFAULT NULL COMMENT '创建时间'",
            "update_time"   => "datetime DEFAULT NULL COMMENT '更新时间'",
        ], $tableOptions);
    }
    
    
    public function down()
    {
        echo "m171010_092159_ad_startup_images cannot be reverted.\n";
        
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
