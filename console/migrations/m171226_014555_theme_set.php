<?php

use yii\db\Migration;

class m171226_014555_theme_set extends Migration
{
    public function init(){
        $this->db = Yii::$app->vradmin1;
        parent::init();
    }

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";
        }
        $this->createTable('{{theme_set}}',[
            "id" =>$this->primaryKey(),
            "start_time" => "datetime DEFAULT NULL COMMENT '主题开始时间' ",
            "end_time" => "datetime DEFAULT NULL COMMENT '主题结束时间' "
        ],$tableOptions);

        $this->insert('{{theme_set}}',[
            'start_time' => '2018-01-01 00:00:00',
            'end_time' => '2018-01-03 23:59:59'
        ]);
    }

    public function safeDown()
    {
        echo "m171226_014555_theme_set cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171226_014555_theme_set cannot be reverted.\n";

        return false;
    }
    */
}
