<?php

use yii\db\Migration;

class m171020_055519_alter_codefiles_vurl extends Migration
{
    public function init()
    {
        $this->db = Yii::$app->vrlive;
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function up()
    {
        $this->alterColumn('{{live_code_files}}', 'video_url', "varchar(150) DEFAULT NULL COMMENT '文件地址'");
    }

    public function safeDown()
    {
        echo "m171020_055519_alter_codefiles_vurl cannot be reverted.\n";

        return false;
    }


}
