<?php

use yii\db\Migration;

class m170220_081545_alter_comment_show extends Migration
{
    public function init()
    {
        $this->db = Yii::$app->vrnews1;
        parent::init(); // TODO: Change the autogenerated stub
    }
    public function up()
    {
        $this->addColumn('{{news_comment}}', 'is_show', "tinyint(4) default 0 COMMENT '（内容过滤）是否显示，默认0显示，1不显示' ");
    }

    public function down()
    {
        echo "m170220_081545_alter_comment_show cannot be reverted.\n";

        return false;
    }


}
