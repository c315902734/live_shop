<?php

use yii\db\Migration;

class m161110_055316_update_oauth2_server extends Migration
{
    public function up()
    {
        $this->alterColumn('{{oauth_access_tokens}}', 'user_id', 'bigint(20) DEFAULT NULL');
        $this->alterColumn('{{oauth_authorization_codes}}', 'user_id', 'bigint(20) DEFAULT NULL');
        $this->alterColumn('{{oauth_clients}}', 'user_id', 'bigint(20) DEFAULT NULL');
        $this->alterColumn('{{oauth_refresh_tokens}}', 'user_id', 'bigint(20) DEFAULT NULL');
    }

    public function down()
    {
        echo "m161110_055316_update_oauth2_server cannot be reverted.\n";

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
