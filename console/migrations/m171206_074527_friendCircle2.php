<?php

use yii\db\Migration;

class m171206_074527_friendCircle2 extends Migration
{

     public function init() {
        $this->db = Yii::$app->vrlive;
        parent::init();
    }
    public function up()
    {
		$this->execute('SET foreign_key_checks = 0');

        $this->addColumn('{{live_weme_video}}', 'type', "TINYINT(1) default '0' COMMENT ''");
        $this->addColumn('{{live_weme_video}}', 'data', "VARCHAR(3000) default '0' COMMENT ''");
        $this->addColumn('{{live_weme_video}}', 'relation_ids', "VARCHAR(3000) default '0' COMMENT ''");
        $this->addColumn('{{live_friend_circle}}', 'update_time', "INT(11) default '0' COMMENT ''");


 
 
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
