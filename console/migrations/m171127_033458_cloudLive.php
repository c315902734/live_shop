<?php

use yii\db\Migration;

class m171127_033458_cloudLive extends Migration
{
    public function init() {
        $this->db = Yii::$app->vradmin1;
        parent::init();
    }
    public function up()
    {
		$this->execute('SET foreign_key_checks = 0');
 
$this->createTable('{{%api_user_action}}', [
	'id' => 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT',
	'actionName' => 'VARCHAR(50) NOT NULL COMMENT \'行为名称\'',
	'uid' => 'INT(11) NULL DEFAULT \'0\' COMMENT \'操作用户ID\'',
	'nickname' => 'VARCHAR(50) NULL COMMENT \'用户昵称\'',
	'addTime' => 'INT(11) NULL DEFAULT \'0\' COMMENT \'操作时间\'',
	'data' => 'TEXT NULL COMMENT \'用户提交的数据\'',
	'url' => 'VARCHAR(200) NULL COMMENT \'操作URL\'',
	'PRIMARY KEY (`id`)'
], "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB");
 

 
/* Table api_user_action */
$this->batchInsert('{{%api_user_action}}',['id','actionName','uid','nickname','addTime','data','url'],[['1','直播','1','','1510734386','a:3:{i:0;a:3:{s:3:"cid";i:1;s:4:"name";s:6:"直播";s:3:"url";s:33:"live/live-list?size=10&category=1";}i:1;a:3:{s:3:"cid";i:2;s:4:"name";s:9:"云直播";s:3:"url";s:33:"live/live-list?size=10&is_cloud=1";}i:2;a:3:{s:3:"cid";i:3;s:4:"name";s:2:"VR";s:3:"url";s:33:"live/live-list?size=10&category=2";}}','app-action/api-user-action-detail'],
]);
 
   }

    public function down()
    {
    
    	        $this->execute('SET foreign_key_checks = 0');
$this->dropTable('{{%api_user_action}}');
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
