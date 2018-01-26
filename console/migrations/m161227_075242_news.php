<?php

use yii\db\Migration;

class m161227_075242_news extends Migration
{
    public function init()
    {
        $this->db = Yii::$app->vrnews1;
        parent::init(); // TODO: Change the autogenerated stub
    }
    public function up()
    {
        $this->addColumn('{{news}}', 'live_id', "bigint(20) default 0 COMMENT '直播id' ");
    }

    public function down()
    {
        echo "m161227_075242_news cannot be reverted.\n";

        return false;
    }


}
