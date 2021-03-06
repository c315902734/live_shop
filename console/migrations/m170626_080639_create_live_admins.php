<?php
use yii\db\Schema;
use yii\db\Migration;

class m170626_080639_create_live_admins extends Migration
{
    public function init()
    {
        $this->db = Yii::$app->vrlive;
        parent::init(); // TODO: Change the autogenerated stub
    }
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='直播 业务管理员'";
        }
        $this->createTable('{{live_manager}}',[
            'id'          => Schema::TYPE_PK,
            'live_id'     => Schema::TYPE_BIGINT . "(20) default 0 COMMENT '直播ID'",
            'admin_id'    => Schema::TYPE_BIGINT . "(20) DEFAULT 0 COMMENT '业务员ID'",
            'admin_name'  => "varchar(30) DEFAULT '' COMMENT '业务员名称'",
            'create_time' => Schema::TYPE_DATETIME." DEFAULT NULL COMMENT '添加时间'",
        ],$tableOptions);
    }

    public function down()
    {
        echo "m170626_080639_create_live_admins cannot be reverted.\n";

        return false;
    }


}
