<?php

use yii\db\Migration;

class m171030_023711_entry_type extends Migration
{
    public function init()
    {
        $this->db = Yii::$app->vrnews1;
        parent::init();
    }
    
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='入口类别表'";
        }
        $this->createTable('{{entry_type}}', [
            "entry_type_id"            => $this->primaryKey(),//文章类别ID
            "title"       => "int(11) NOT NULL COMMENT '类别名称' ",
            "parent_id"   => "int(10) NOT NULL DEFAULT 0 COMMENT '父级类别入口ID,顶级入口为默认值0' "
        ], $tableOptions);
    }

    public function down()
    {
        echo "m171030_023711_entry_type cannot be reverted.\n";

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
