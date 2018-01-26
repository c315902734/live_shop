<?php

use yii\db\Migration;

class m171130_012704_live_tags_relation extends Migration
{
    public function init(){
        $this->db = Yii::$app->vrlive;
        parent::init();
    }

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";
        }
        $this->createTable('{{live_tags_relation}}',[
            "id" =>$this->primaryKey(),
            "live_id" => "bigint(20) DEFAULT NULL COMMENT '直播id' ",
            "tag_id" => "int(11) DEFAULT NULL COMMENT '标签id' ",
            "type" => "tinyint(4) DEFAULT 1 COMMENT '类型 1直播 2新闻' ",
            "create_time" => "datetime DEFAULT null COMMENT '创建时间' ",
            "creator" => "bigint(20) DEFAULT null COMMENT '创建人' "
        ],$tableOptions);
    }

    public function safeDown()
    {
        echo "m171130_012704_live_tags_relation cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171130_012704_live_tags_relation cannot be reverted.\n";

        return false;
    }
    */
}
