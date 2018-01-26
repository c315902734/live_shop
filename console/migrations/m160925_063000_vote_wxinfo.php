<?php

use yii\db\Migration;

class m160925_063000_vote_wxinfo extends Migration
{
    /*public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{vote_wxinfo}}',[
            'id' => $this->primaryKey(),
            'access_token' => $this->string()->notNull(),//微信认证access_token
            'access_expires_in' => $this->integer(10)->notNull(),//access_token 到期时间
            'jsapi_ticket' => $this->integer(10)->notNull(),//jsapi_ticket
            'jsapi_expires_in' => $this->integer(10)->notNull(),//jsapi_ticket 到期时间
        ],$tableOptions);
        $this->insert('{{vote_wxinfo}}'[
            'access_token' => '',
            'access_expires_in' => '',
            'jsapi_ticket' => '',
            'jsapi_expires_in' => ''
        ]);
    }

    public function down()
    {
        echo "m160925_063000_vote_wxinfo cannot be reverted.\n";

        return false;
    }*/

    
    // Use safeUp/safeDown to run migration code within a transaction
    //事务性迁移，比如创建表的时候还添加了数据
    public function safeUp()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{vote_wxinfo}}',[
            'id' => $this->primaryKey(),
            'app_key' => $this->string()->notNull(),
            'app_secret' => $this->string()->notNull(),
            'access_token' => $this->string()->notNull(),//微信认证access_token
            'access_expires_in' => $this->integer(10)->notNull(),//access_token 到期时间
            'jsapi_ticket' => $this->string()->notNull(),//jsapi_ticket
            'jsapi_expires_in' => $this->integer(10)->notNull(),//jsapi_ticket 到期时间
        ],$tableOptions);
        //默认插入一条数据
        // $this->insert('{{vote_wxinfo}}',[
        //     'access_token' => '',
        //     'access_expires_in' => 0,
        //     'jsapi_ticket' => '',
        //     'jsapi_expires_in' => 0,
        // ]);
    }

    public function safeDown()
    {
        // $this->delete('vote_wxinfo',['id'=>1]);
        $this->dropTable('vote_wxinfo');
    }
    
}
