<?php

use yii\db\Migration;

class m170627_015908_alert_news_reward_add_column_live_id extends Migration
{
	public function init()
	{
		$this->db = Yii::$app->vrnews1;
		parent::init(); // TODO: Change the autogenerated stub
	}
	
    public function up()
    {
    	$this->addColumn('{{news_reward}}', 'live_id', "bigint(20) NOT NULL DEFAULT '0' COMMENT '直播id'");
    }

    public function down()
    {
        echo "m170627_015908_alert_news_reward_add_column_live_id cannot be reverted.\n";

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
