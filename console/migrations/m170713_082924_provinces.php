<?php

use yii\db\Migration;

class m170713_082924_provinces extends Migration
{
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->db = yii::$app->vrnews1;
    }

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='省份信息表'";
        }
        $this->createTable('{{provinces}}',[
            "id" =>$this->primaryKey(),
            "provinceid" => $this->string(20)->notNull()->defaultValue(0)->comment('省份ID'),
            "province" => $this->string(50)->notNull()->defaultValue(0)->comment('省份名称'),
        ],$tableOptions);
    }

    public function down()
    {
        echo "m170713_082924_provinces cannot be reverted.\n";

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