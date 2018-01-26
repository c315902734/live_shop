<?php

use yii\db\Migration;

class m161226_105806_update_admin_user_rclond_token extends Migration
{
	public function init(){
		$this->db = Yii::$app->vradmin1;
		parent::init();
	}
	
    public function up()
    {
    	$this->addColumn('{{admin_user_rcloud_token}}', 'other_rcloud_token1', "varchar(120) NOT NULL  COMMENT 'token1'");
    	$this->addColumn('{{admin_user_rcloud_token}}', 'other_rcloud_token2', "varchar(120) NOT NULL  COMMENT 'token2'");
    }

    public function down()
    {
        echo "m161226_105806_update_admin_user_rclond_token cannot be reverted.\n";

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
