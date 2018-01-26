<?php

use yii\db\Migration;

class m170619_074616_insert_area_shangrao extends Migration
{
	public function init()
	{
		$this->db = Yii::$app->vrnews1;
		parent::init(); // TODO: Change the autogenerated stub
	}
	
    public function up()
    {
    	$this->insert('{{area}}',[
    			'name' => '上饶',
    			'initial' => 'S',
    			'initial_group' => '4',
    			'pinyin' => 'shangrao',
    			'establish_status' => '0',
    			'disable_status' => '1',
    			'live_status' => '0'
    	]);
    }

    public function down()
    {
        echo "m170619_074616_insert_area_shangrao cannot be reverted.\n";

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