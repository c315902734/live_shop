<?php

use yii\db\Migration;

class m161227_072622_area extends Migration
{

    public function init()
    {
        $this->db = Yii::$app->vrnews1;
        parent::init(); // TODO: Change the autogenerated stub
    }
    public function up()
    {
        $this->addColumn('{{area}}', 'live_status', "smallint(3) default '0' COMMENT '直播权限状态，0关闭，1开启'");
    }

    public function down()
    {
        echo "m161227_072622_area cannot be reverted.\n";

        return false;
    }

}
