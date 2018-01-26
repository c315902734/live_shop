<?php

use yii\db\Migration;

class m170419_071921_inser_area_citys extends Migration
{
	public function init()
	{
		$this->db = Yii::$app->vrnews1;
		parent::init(); // TODO: Change the autogenerated stub
	}
	
    public function up()
    {
    	$this->insert('{{area}}',[
    			'name' => '临沧',
    			'initial' => 'L',
    			'initial_group' => '3',
    			'pinyin' => 'lincang',
    			'establish_status' => '0',
    			'disable_status' => '1',
    			'live_status' => '0'
    	]);
    	$this->insert('{{area}}',[
    			'name' => '阜阳',
    			'initial' => 'F',
    			'initial_group' => '2',
    			'pinyin' => 'fuyang',
    			'establish_status' => '0',
    			'disable_status' => '1',
    			'live_status' => '0'
    	]);
    	$this->insert('{{area}}',[
    			'name' => '安庆',
    			'initial' => 'A',
    			'initial_group' => '1',
    			'pinyin' => 'anqing',
    			'establish_status' => '0',
    			'disable_status' => '1',
    			'live_status' => '0'
    	]);
    	$this->insert('{{area}}',[
    			'name' => '郴州',
    			'initial' => 'C',
    			'initial_group' => '1',
    			'pinyin' => 'chenzhou',
    			'establish_status' => '0',
    			'disable_status' => '1',
    			'live_status' => '0'
    	]);
    	$this->insert('{{area}}',[
    			'name' => '长沙',
    			'initial' => 'C',
    			'initial_group' => '1',
    			'pinyin' => 'changsha',
    			'establish_status' => '0',
    			'disable_status' => '1',
    			'live_status' => '0'
    	]);
    	$this->insert('{{area}}',[
    			'name' => '呼和浩特',
    			'initial' => 'H',
    			'initial_group' => '2',
    			'pinyin' => 'huhehaote',
    			'establish_status' => '0',
    			'disable_status' => '1',
    			'live_status' => '0'
    	]);
    	$this->insert('{{area}}',[
    			'name' => '济宁',
    			'initial' => 'J',
    			'initial_group' => '2',
    			'pinyin' => 'jining',
    			'establish_status' => '0',
    			'disable_status' => '1',
    			'live_status' => '0'
    	]);
    	$this->insert('{{area}}',[
    			'name' => '聊城',
    			'initial' => 'L',
    			'initial_group' => '3',
    			'pinyin' => 'liaocheng',
    			'establish_status' => '0',
    			'disable_status' => '1',
    			'live_status' => '0'
    	]);
    	$this->insert('{{area}}',[
    			'name' => '枣庄',
    			'initial' => 'Z',
    			'initial_group' => '5',
    			'pinyin' => 'zaozhuang',
    			'establish_status' => '0',
    			'disable_status' => '1',
    			'live_status' => '0'
    	]);
    	$this->insert('{{area}}',[
    			'name' => '西安',
    			'initial' => 'X',
    			'initial_group' => '5',
    			'pinyin' => 'xian',
    			'establish_status' => '0',
    			'disable_status' => '1',
    			'live_status' => '0'
    	]);
    	$this->insert('{{area}}',[
    			'name' => '延安',
    			'initial' => 'Y',
    			'initial_group' => '5',
    			'pinyin' => 'yanan',
    			'establish_status' => '0',
    			'disable_status' => '1',
    			'live_status' => '0'
    	]);
    	$this->insert('{{area}}',[
    			'name' => '商丘',
    			'initial' => 'S',
    			'initial_group' => '4',
    			'pinyin' => 'shangqiu',
    			'establish_status' => '0',
    			'disable_status' => '1',
    			'live_status' => '0'
    	]);
    }

    public function down()
    {
        echo "m170419_071921_inser_area_citys cannot be reverted.\n";

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
