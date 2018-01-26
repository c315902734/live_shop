<?php

use yii\db\Migration;

class m170217_081121_live_admin extends Migration
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
    		$tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='直播采集端主持人账号'";
    	}
    	$this->createTable('{{live_admin}}',[
    			"admin_id" =>$this->primaryKey(),
    			"username" => "varchar(200) NOT NULL COMMENT '采集端主持人用户名' ",
    			"password" => "varchar(200) NOT NULL COMMENT '采集端主持人密码' "
    	],$tableOptions);
    }

    public function down()
    {
        echo "m170217_081121_live_admin cannot be reverted.\n";

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
