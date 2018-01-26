<?php

use yii\db\Migration;

class m170811_073309_create_live_code_files extends Migration
{
	public function init(){
		$this->db = Yii::$app->vrlive;
		parent::init();
	}
	
    public function up()
    {
    	$tableOptions = null;
    	if ($this->db->driverName === 'mysql') {
    		// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
    		$tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='直播码模式录制文件表'";
    	}
    	
    	$this->createTable('{{live_code_files}}',[
    			"id" =>$this->primaryKey(),// "bigint(20) NOT NULL AUTO_INCREMENT COMMENT '直播员面板管理模块的图文消息存储表'",
    			"code_camera_id" => "bigint(20) NOT NULL",
    			"file_id" => "varchar(60) DEFAULT NULL COMMENT '合并录制文件任务id'",
    			"file_size" => "int(11) DEFAULT NULL COMMENT '文件大小'",
    			"video_url" => "varchar(100) DEFAULT NULL COMMENT '文件地址'",
    			"duration" => "varchar(50) DEFAULT NULL COMMENT '时长'",
    			"file_format" => "varchar(10) DEFAULT NULL COMMENT '视频格式'"
    	],$tableOptions);
    	$this->createIndex('file_id', 'live_code_files', ['file_id'],true);
    	$this->createIndex('video_url', 'live_code_files', ['video_url'],true);
    }

    public function down()
    {
        echo "m170811_073309_create_live_code_files cannot be reverted.\n";

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
