<?php

use yii\db\Migration;

class m160926_034320_vote_user extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{vote_user}}',[
            'id' => $this->primaryKey(),
            'open_id' => $this->string()->notNull(),//微信open_id
            'nickname' => $this->string()->notNull(),
            'sex' => $this->smallInteger()->notNull(),
            'language' => $this->string()->notNull(),
            'city' => $this->string()->notNull(),
            'province' => $this->string()->notNull(),
            'country' => $this->string()->notNull(),
            'headimgurl'=> $this->text(),
            'subscribe_time'=>$this->string()->notNull(),
            'unionid' => $this->string()->notNull(),
        ],$tableOptions);
        $this->addCommentOnColumn('{{vote_user}}','headimgurl','用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空');
        $this->addCommentOnColumn('{{vote_user}}','unionid','只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段');

    }

    public function down()
    {
        echo "m160926_034320_vote_user cannot be reverted.\n";

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
