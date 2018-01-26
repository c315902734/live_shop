<?php

use yii\db\Migration;

class m170222_100914_alter_comment_content extends Migration
{
    public function init()
    {
        $this->db = Yii::$app->vrnews1;
        parent::init(); // TODO: Change the autogenerated stub
    }
    public function up()
    {
        $this->alterColumn('{{news_comment}}', 'content', " text default NULL COMMENT '评论内容'");
    }

    public function down()
    {
        echo "m170222_100914_alter_comment_content cannot be reverted.\n";

        return false;
    }


}
