<?php

use yii\db\Migration;

class m161219_101423_update_live extends Migration
{
	public function init(){
		$this->db = Yii::$app->vrlive;
		parent::init();
	}
    public function up()
    {    	
    	$this->alterColumn('{{live}}', 'category',"tinyint(4) default '1' COMMENT '直播类型：0：表示未设置直播类型,请选择;1视频直播;2VR直播;3图文直播;4视频加图文直播;5VR加图文直播'");
    	$this->addColumn('{{live}}', 'live_man_cate', "varchar(32) default '' COMMENT '直播员类别：如xxx等'");
    	$this->addColumn('{{live}}', 'live_man_alias', "varchar(32) default '' COMMENT '直播员别名'");
    	$this->addColumn('{{live}}', 'live_man_avatar_url', "varchar(255) default 'http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png' COMMENT '直播员头像地址'");
    }

    public function down()
    {
        echo "m161219_101423_update_live cannot be reverted.\n";

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