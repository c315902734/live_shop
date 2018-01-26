<?php

use yii\db\Migration;

class m161222_110011_live_panel_images extends Migration
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
    		$tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='用来存储图文消息中的图片文件信息'";
    	}
    	$this->createTable('{{live_panel_images}}',[
    			"id" =>$this->primaryKey(),
    			"live_id" => "bigint(20) NOT NULL COMMENT '直播id(vrlive.live.id)'",
    			"msg_id" => "bigint(20) NOT NULL COMMENT '图文消息编号(vrlive.live_panel_manage.id)'",
    			"file_url" => "varchar(255) DEFAULT NULL COMMENT '图片在万象有图的地址'",
    			],$tableOptions);
    }

    public function down()
    {
        echo "m161222_110011_live_panel_images cannot be reverted.\n";

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
