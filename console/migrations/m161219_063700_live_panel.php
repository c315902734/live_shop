<?php

use yii\db\Migration;

class m161219_063700_live_panel extends Migration
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
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='直播员面板管理模块的图文消息存储表：\r\n一个live_id对应多个图文消息记录；\r\n直播室的用户发言及直播员对其的评论，可以作为一条图文消息记录。'";
        }
        
        $this->createTable('{{live_panel_manage}}',[
		  "id" =>$this->primaryKey(),// "bigint(20) NOT NULL AUTO_INCREMENT COMMENT '直播员面板管理模块的图文消息存储表'",
		  "live_id" => "bigint(20) NOT NULL COMMENT '直播id'",
		  "create_time" => "datetime DEFAULT NULL COMMENT '创建时间'",
		  "update_time" => "datetime DEFAULT NULL COMMENT '更新时间'",
		  "pic_txt_content" => "longtext COMMENT '图文内容,或者是对上榜用户的回复内容：内容存json格式数据'",
		  "content_type" => "tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：原始图文消息内容；2：上榜数据内容'",
		  "onlist_user_id" => "bigint(20) DEFAULT '0' COMMENT '上榜用户编号'",
		  "onlist_nickname" => "varchar(60) DEFAULT NULL COMMENT '上榜用户昵称'",
		  "user_speak_content" => "longtext COMMENT '上榜用户的发言内容,内容存json格式数据'",
		  "user_speak_time" => "datetime DEFAULT NULL COMMENT '上榜用户发言时间'",
		  "sort_number" => "tinyint(4) DEFAULT '0' COMMENT '图文消息的排序序号'",
          "is_top" => "tinyint(4) DEFAULT '0' COMMENT '是否置顶数据：0:不是被置顶的数据；1：该数据被置顶'",
          "creator_id" => "int(11) DEFAULT '0' COMMENT '管理员编号'",
          "json_data" => "text  COMMENT '发送融云data数据'"
        		
        	//,"PRIMARY" => "KEY('id')"
	        ],$tableOptions); 
        
    }

    public function down()
    {
        echo "m161219_063700_live_panel cannot be reverted.\n";

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
