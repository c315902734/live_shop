<?php

use yii\db\Migration;

class m171026_032734_code_files_delindex extends Migration
{
    public function init(){
        $this->db = Yii::$app->vrlive;
        parent::init();
    }

    public function up()
    {
        $this->dropIndex('file_id', 'live_code_files');
    }

    public function safeDown()
    {
        echo "m171026_032734_code_files_delindex cannot be reverted.\n";

        return false;
    }


}
