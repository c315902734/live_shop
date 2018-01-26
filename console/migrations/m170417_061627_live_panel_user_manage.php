<?php

use yii\db\Migration;

class m170417_061627_live_panel_user_manage extends Migration
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
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='聊天室图文消息存储'";
        }
        
        $this->createTable('{{live_panel_user_manage}}',[
		  "id" =>$this->primaryKey(),
		  "live_id" => "bigint(20) NOT NULL COMMENT '直播id'",
		  "create_time" => "datetime DEFAULT NULL COMMENT '发布时间'",
		  "update_time" => "datetime DEFAULT NULL COMMENT '更新时间'",
		  "pic_txt_content" => "longtext COMMENT '图文内容'",
		  "sort_number" => "tinyint(4) DEFAULT '0' COMMENT '排序序号'",
          "creator_id" => "bigint(20) DEFAULT '0' COMMENT '发布者id'",
        		
        	//,"PRIMARY" => "KEY('id')"
	        ],$tableOptions); 
        
    }

    public function down()
    {
        echo "m170417_061627_live_panel_user_manage cannot be reverted.\n";

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
