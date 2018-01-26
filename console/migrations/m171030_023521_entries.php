<?php

use yii\db\Migration;

class m171030_023521_entries extends Migration
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
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='新版入口表'";
        }
        $this->createTable('{{entry}}', [
            "id"          => $this->primaryKey(),
            "news_id"    => "bigint(20) NOT NULL COMMENT '入口文章ID' ",
            "entry_type_id"     => "int(11) NOT NULL COMMENT '文章类别ID' ",
            "operater_id" => "int(11) NOT NULL COMMENT '操作者ID' ",
            "is_sticky"   => "tinyint(4) DEFAULT '1' COMMENT '是否置顶：0:非置顶；1：置顶'",
            "terminal_id" => "tinyint(4) DEFAULT '0' COMMENT '是否置顶：0:PC 电脑；1：APP 移动端 2:COLLECT 采集端'",
            "weight" => "int(20) DEFAULT '0' COMMENT '重量'",//根据权重排定，默认0
            "create_time" => "int(10) NOT NULL DEFAULT 0 COMMENT '创建时间' ",
            "update_time" => "int(10) NOT NULL DEFAULT 0 COMMENT '更新时间' ",
            
            
        ], $tableOptions);
    }
    
    
    public function down()
    {
        echo "m171030_023521_entries cannot be reverted.\n";
        
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
