<?php

use yii\db\Migration;

class m170104_072203_alter_version extends Migration
{
    public function init()
    {
        $this->db = Yii::$app->vradmin1;
        parent::init(); // TODO: Change the autogenerated stub
    }
    public function up()
    {
        $this->addColumn('{{version}}', 'show_video', "tinyint(4) default '0' COMMENT '是否显示视频栏目，0否，1是'");
    }

    public function down()
    {
        echo "m170104_072203_alter_version cannot be reverted.\n";

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