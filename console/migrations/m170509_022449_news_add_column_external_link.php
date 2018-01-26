<?php

use yii\db\Migration;

class m170509_022449_news_add_column_external_link extends Migration
{
	
	public function init()
	{
		$this->db = Yii::$app->vrnews1;
		parent::init(); // TODO: Change the autogenerated stub
	}
	
    public function up()
    {
    	$this->addColumn('{{news}}', 'external_link', "varchar(300) default NULL COMMENT '外链(列表处)'");
    }

    public function down()
    {
        echo "m170509_022449_news_add_column_external_link cannot be reverted.\n";

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