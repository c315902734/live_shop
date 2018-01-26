<?php

use yii\db\Migration;

class m170811_073005_alert_live_vamera_angle extends Migration
{
	public function init()
	{
		$this->db = Yii::$app->vrlive;
		parent::init(); // TODO: Change the autogenerated stub
	}
	
    public function up()
    {
        $this->addColumn('{{live_camera_angle}}', 'concat_task_id', " varchar(60) default NULL COMMENT '录制文件合并任务id'");
        $this->addColumn('{{live_camera_angle}}', 'concat_file_id', " varchar(60) default NULL COMMENT '合并任务生成的最新文件的file_id  迭代替换最后剩下完整文件的file_id'");
    }

    public function down()
    {
        echo "m170811_073005_alert_live_vamera_angle cannot be reverted.\n";

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
