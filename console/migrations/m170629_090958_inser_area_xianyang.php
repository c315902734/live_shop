<?php

use yii\db\Migration;

class m170629_090958_inser_area_xianyang extends Migration
{
	public function init()
	{
		$this->db = Yii::$app->vrnews1;
		parent::init(); // TODO: Change the autogenerated stub
	}
	
    public function up()
    {
    	$this->insert('{{area}}',[
    			'name' => '咸阳',
    			'initial' => 'X',
    			'initial_group' => '5',
    			'pinyin' => 'xianyang',
    			'establish_status' => '0',
    			'disable_status' => '1',
    			'live_status' => '0'
    	]);
    }

    public function down()
    {
        echo "m170629_090958_inser_area_xianyang cannot be reverted.\n";

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