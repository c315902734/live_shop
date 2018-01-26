<?php

use yii\db\Migration;

class m170216_023300_resource_library extends Migration
{
	public function init(){
		$this->db = Yii::$app->vrnews1;
		parent::init();
	}
    public function up()
    {
    	$tableOptions = null;
    	if ($this->db->driverName === 'mysql') {
    		// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
    		$tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='资源池'";
    	}
    	$this->createTable('{{resource_library}}',[
    			"resource_id" =>"bigint(20) NOT NULL COMMENT '资源id'",
    			"thumbnail_url" => "varchar(200) COLLATE utf8_bin DEFAULT NULL COMMENT '缩略图地址(图片缩略图或视频封面)'",
    			"file_name" => "varchar(300) COLLATE utf8_bin DEFAULT NULL",
    			"file_id" => "varchar(60) COLLATE utf8_bin DEFAULT NULL COMMENT '腾讯云文件id'",
    			"url" => "varchar(200) COLLATE utf8_bin DEFAULT NULL COMMENT '其他时为对应的文件地址，视频时为视频地址'",
    			"url1" => "varchar(200) COLLATE utf8_bin DEFAULT NULL",
    			"url2" => "varchar(200) COLLATE utf8_bin DEFAULT NULL",
    			"duration" => "varchar(11) COLLATE utf8_bin DEFAULT '' COMMENT '视频时长 秒'",
    			"height" => "int(11) DEFAULT NULL COMMENT '视频高度'",
    			"height1" => "int(11) DEFAULT NULL COMMENT '视频高度'",
    			"height2" => "int(11) DEFAULT NULL COMMENT '视频高度'",
    			"width" => "int(11) DEFAULT NULL COMMENT '视频宽度'",
    			"width1" => "int(11) DEFAULT NULL COMMENT '视频宽度'",
    			"width2" => "int(11) DEFAULT NULL COMMENT '视频宽度'",
    			"size" => "int(11) DEFAULT NULL COMMENT '视频大小'",
    			"size1" => "int(11) DEFAULT NULL COMMENT '视频大小'",
    			"size2" => "int(11) DEFAULT NULL COMMENT '视频大小'",
    			"category" => "tinyint(4) DEFAULT NULL COMMENT '种类 1普通视频 2VR视频'",
    			"create_time" => "datetime DEFAULT NULL COMMENT '创建时间'",
    			"status" => "tinyint(4) DEFAULT NULL COMMENT '1:转码中 2:转码成功 3:转码失败'",
    			"type" => "tinyint(4) DEFAULT '1' COMMENT '类型 1:图片 2:视频 3:其他'",
    			],$tableOptions);
    }

    public function down()
    {
        echo "m170216_023300_resource_library cannot be reverted.\n";

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
