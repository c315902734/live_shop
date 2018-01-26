<?php

use yii\db\Migration;

class m171206_074530_friendCircle2 extends Migration
{

     public function init() {
        $this->db = Yii::$app->vradmin1;
        parent::init();
    }
    public function up()
    {
		$this->execute('SET foreign_key_checks = 0');

        $this->addColumn('{{admin_user}}', 'user_id', "BIGINT(20) default '0' COMMENT ''");
        $this->addColumn('{{admin_user}}', 'mirrtalkid', "varchar(255) default '' COMMENT ''");


 
 
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
