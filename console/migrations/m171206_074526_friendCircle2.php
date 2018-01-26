<?php

use yii\db\Migration;

class m171206_074526_friendCircle2 extends Migration
{

     public function init() {
        $this->db = Yii::$app->vrlive;
        parent::init();
    }
    public function up()
    {
		$this->execute('SET foreign_key_checks = 0');

 $this->addColumn('{{live}}', 'company_id', "INT(11) default '0' COMMENT '公司id'");
 $this->addColumn('{{live_friend_circle}}', 'live_id', "BIGINT(11) default '0' COMMENT '直播id'");
 $this->addColumn('{{live_weme}}', 'company_id', "INT(11) default '0' COMMENT ''");
 $this->addColumn('{{live_weme}}', 'area_id', "MEDIUMINT(3) default NULL COMMENT ''");
$this->addColumn('{{live_weme}}', 'area_name', "CHAR(50) default NULL COMMENT ''");
$this->addColumn('{{live_weme}}', 'tags', "VARCHAR(1000) default NULL COMMENT ''");
$this->addColumn('{{live_weme}}', 'is_forward', "TINYINT(1) default '0' COMMENT ''");
$this->addColumn('{{live_weme}}', 'device', "TINYINT(1) default '1' COMMENT ''");
$this->addColumn('{{live_weme}}', 'coordinate_type', "TINYINT(1) default '1' COMMENT ''");
$this->addColumn('{{live_weme}}', 'screenshot', "VARCHAR(1000) default NULL COMMENT ''");
$this->addColumn('{{live_weme}}', 'tencent_url', "VARCHAR(1000) default NULL COMMENT ''");
$this->addColumn('{{live_weme}}', 'publish_status', "TINYINT(1) default '1' COMMENT ''");



$this->createTable('{{%live_setting}}', [
	'name' => 'VARCHAR(100) NOT NULL',
	'data' => 'TEXT NOT NULL',
	'remark' => 'VARCHAR(200) NOT NULL',
	'PRIMARY KEY (`name`,`remark`)'
], "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB");
 
$this->createTable('{{%live_tag}}', [
	'id' => 'INT(11) NOT NULL AUTO_INCREMENT',
	'company_id' => 'INT(11) NOT NULL DEFAULT \'0\'',
	'tag_name' => 'VARCHAR(100) NOT NULL',
	'PRIMARY KEY (`id`)'
], "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB");
 
 
$this->execute('SET foreign_key_checks = 1;');
$this->execute('SET foreign_key_checks = 0');
 
/* Table live_setting */
$this->batchInsert('{{%live_setting}}',['name','data','remark'],[['cms_backend_api','fz3.cms.api.xinhuiwen.com',''],
]);
 
$this->execute('SET foreign_key_checks = 1;');    }

    public function down()
    {
    
    	        $this->execute('SET foreign_key_checks = 0');
$this->dropTable('{{%live_tag}}');
$this->dropTable('{{%live_tag}}');
$this->execute('SET foreign_key_checks = 1;');		    }

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
