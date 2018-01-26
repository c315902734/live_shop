<?php

use yii\db\Migration;

class m171204_070348_live_weme_video extends Migration
{
    public function init(){
        $this->db = Yii::$app->vrlive;
        parent::init();
    }

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";
        }
        $this->createTable('{{live_weme_video}}',[
            "id" =>$this->primaryKey(),
            "video_url" => "varchar(255) DEFAULT NULL COMMENT '原始视频地址' ",
            "file_id" => "varchar(60) DEFAULT NULL COMMENT '腾讯云文件id' ",
            "thumbnail_url" => "varchar(255) DEFAULT NULL COMMENT '封面图' ",
            "txy_video_url" => "varchar(255) DEFAULT NULL COMMENT '腾讯云视频地址' ",
            "company_id" => "varchar(255) DEFAULT NULL COMMENT '腾讯云视频地址' ",
            "create_time" => "datetime DEFAULT null COMMENT '保存时间' ",
            "update_time" => "datetime DEFAULT null COMMENT '修改时间' "
        ],$tableOptions);
    }

    public function safeDown()
    {
        echo "m171204_070348_live_weme_video cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171204_070348_live_weme_video cannot be reverted.\n";

        return false;
    }
    */
}
