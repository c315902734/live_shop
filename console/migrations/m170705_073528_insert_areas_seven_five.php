<?php

use yii\db\Migration;

class m170705_073528_insert_areas_seven_five extends Migration
{
	public function init()
	{
		$this->db = Yii::$app->vrnews1;
		parent::init(); // TODO: Change the autogenerated stub
	}
	
    public function up()
    {
    	$this->insert('{{area}}',[
    			'name' => '南阳',
    			'initial' => 'N',
    			'initial_group' => '3',
    			'pinyin' => 'nanyang',
    			'establish_status' => '0',
    			'disable_status' => '1',
    			'live_status' => '0'
    	]);
    	$this->insert('{{area}}',[
    			'name' => '洛阳',
    			'initial' => 'L',
    			'initial_group' => '3',
    			'pinyin' => 'luoyang',
    			'establish_status' => '0',
    			'disable_status' => '1',
    			'live_status' => '0'
    	]);
    	$this->insert('{{area}}',[
    			'name' => '周口',
    			'initial' => 'Z',
    			'initial_group' => '5',
    			'pinyin' => 'zhoukou',
    			'establish_status' => '0',
    			'disable_status' => '1',
    			'live_status' => '0'
    	]);
    	$this->insert('{{area}}',[
    			'name' => '平顶山',
    			'initial' => 'P',
    			'initial_group' => '4',
    			'pinyin' => 'pingdingshan',
    			'establish_status' => '0',
    			'disable_status' => '1',
    			'live_status' => '0'
    	]);
    }

    public function down()
    {
        echo "m170705_073528_insert_areas_seven_five cannot be reverted.\n";

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
