<?php

use yii\db\Migration;

class m171206_074528_friendCircle2 extends Migration
{

     public function init() {
        $this->db = Yii::$app->vrlive;
        parent::init();
    }
    public function up()
    {
		$this->execute('SET foreign_key_checks = 0');

        $this->addColumn('{{live_weme}}', 'type', "TINYINT(1) default '0' COMMENT ''");
        $this->addColumn('{{live_weme}}', 'stime', "INT(11) default '0' COMMENT ''");


 
 
$this->execute('SET foreign_key_checks = 1;');
 }

    public function down()
    {
    

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
