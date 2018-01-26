<?php

use yii\db\Migration;

class m170808_064140_add_table_hwb_pool extends Migration
{
    public function init(){
        $this->db = Yii::$app->vradmin1;
        parent::init();
    }

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=MyISAM COMMENT='cms资金池'";
        }
        $this->createTable('{{hwb_pool}}',[
            "id"      => $this->primaryKey()->unsigned(),
            "operator"     => $this->string()->notNull()->defaultValue('')->comment('操作人'),
            "type"         => $this->smallInteger()->unsigned()->notNull()->defaultValue(0)->comment('类型   0：增加  1:大转盘抽奖 ...'),
            "recharge_amount"    => $this->decimal(8,2)->unsigned()->notNull()->defaultValue(0.00)->comment('充值金额'),
            "huiwenbi"     => $this->integer()->notNull()->defaultValue(0)->comment('汇闻币'),
            "balance"         => $this->integer()->notNull()->defaultValue(0)->comment('余额'),
            "create_time"     => $this->dateTime()->notNull()->defaultValue(0),
            "remarks"     => $this->string()->notNull()->defaultValue('')->comment('备注'),
        ],$tableOptions);
    }

    public function down()
    {
        echo "m170808_064140_add_table_hwb_pool cannot be reverted.\n";

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
