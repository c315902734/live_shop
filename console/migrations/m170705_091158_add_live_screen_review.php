<?php

use yii\db\Migration;

class m170705_091158_add_live_screen_review extends Migration
{
    public function init()
    {
        $this->db = Yii::$app->vrlive;
        parent::init(); // TODO: Change the autogenerated stub
    }
    public function up()
    {
        $this->addColumn('{{live}}', 'screen', "tinyint(4) default 0 COMMENT '画面方向，0横屏，1竖屏'");
        $this->addColumn('{{live}}', 'review', "tinyint(4) default 0 COMMENT '审核，0通过，1不通过'");
    }

    public function down()
    {
        echo "m170705_091158_add_live_screen_review cannot be reverted.\n";

        return false;
    }


}