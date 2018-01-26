<?php

use yii\db\Migration;

class m170921_105417_add_alias_field_to_column_type extends Migration
{
    public function init()
    {
        $this->db = Yii::$app->vrnews1;
        parent::init(); // TODO: Change the autogenerated stub
    }
    
    public function up()
    {
        $this->addColumn('{{news_column_type}}', 'alias', "varchar(20) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '别名，全拼'");
    }

    public function down()
    {
        echo "m170921_105417_add_alias_field_to_column_type cannot be reverted.\n";

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
