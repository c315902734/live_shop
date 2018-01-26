<?php

use yii\db\Migration;

/**
 * Handles the creation of table `basket`.
 */
class m171107_111341_create_basket_table extends Migration
{
    public function init()
    {
        $this->db = Yii::$app->vrnews1;
        parent::init();
    }
    /**
     * @inheritdoc
     */
    public function up()
    {
        
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT='内容集合篮子表'";
        }
        $this->createTable('{{basket}}', [
            "id"            => $this->primaryKey(),
            "title"    => "varchar(255) DEFAULT NULL COMMENT '集标题' ",
            "description" => "varchar(255) DEFAULT NULL COMMENT '集摘要简介' ",
            "column_id"   => "int(11) NOT NULL  COMMENT '栏目id'",
            "column_type" => "tinyint(4) DEFAULT '0' COMMENT '是否本地栏目：0:非本地栏目；1：本地栏目'",
            "news_id"       => "bigint(20)  DEFAULT '0' COMMENT '篮子对应文章ID' ",
            "basket_type_id" => "int(11) DEFAULT '1' COMMENT '篮子类别ID 0: 通用篮子 1:快直播篮子' ",
            "operater_id"   => "int(11) NOT NULL COMMENT '创建者ID' ",
            "is_active"     => "tinyint(4) DEFAULT '0' COMMENT '是否开启：0:关闭；1：开启'",
            "terminal_id"   => "tinyint(4) DEFAULT '0' COMMENT '适配终端：0: 全部 1.PC 电脑；2：APP 移动端 3:COLLECT 采集端'",
            "weight"        => "int(20) DEFAULT '0' COMMENT '重量'",//集权重
            "create_time"   => "int(10) NOT NULL DEFAULT 0 COMMENT '创建时间' ",
            "update_time"   => "int(10) NOT NULL DEFAULT 0 COMMENT '更新时间' ",
            
        ], $tableOptions);
        
    }
    
    
    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('basket');
    }
}
