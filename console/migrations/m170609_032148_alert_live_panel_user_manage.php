<?php

use yii\db\Migration;

class m170609_032148_alert_live_panel_user_manage extends Migration
{
	public function init()
	{
		$this->db = Yii::$app->vrlive;
		parent::init(); // TODO: Change the autogenerated stub
	}
	
    public function up()
    {
    	$this->addColumn('{{live_panel_user_manage}}', 'creator_name', "varchar(45) default NULL COMMENT '用户名称'");
    	$this->addColumn('{{live_panel_user_manage}}', 'creator_nickname', "varchar(30) default NULL COMMENT '用户昵称'");
    	$this->addColumn('{{live_panel_user_manage}}', 'creator_avatar', "varchar(200) default NULL COMMENT '用户头像'");
    }

    public function down()
    {
        echo "m170609_032148_alert_live_panel_user_manage cannot be reverted.\n";

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
