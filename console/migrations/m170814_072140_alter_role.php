<?php

use yii\db\Migration;

class m170814_072140_alter_role extends Migration
{
    public function init()
    {
        $this->db = Yii::$app->vradmin1;
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function up()
    {
        $this->addColumn('{{role}}', 'is_son_show', "tinyint(2) DEFAULT '0' COMMENT '子公司是否可见 0否 1是'");
    }

    public function down()
    {
        echo "m170814_072140_alter_role cannot be reverted.\n";

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
