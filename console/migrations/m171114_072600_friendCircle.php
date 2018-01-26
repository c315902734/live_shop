<?php

use yii\db\Migration;

class m171114_072600_friendCircle extends Migration {

    public function init() {
        $this->db = Yii::$app->vrlive;
        parent::init();
    }

    public function up() {
        $this->execute('SET foreign_key_checks = 0');

        $this->createTable('{{%live_friend_circle}}', [
            'tid' => 'MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT',
            'fid' => 'MEDIUMINT(8) UNSIGNED NOT NULL',
            'posttableid' => 'SMALLINT(6) UNSIGNED NOT NULL DEFAULT \'0\'',
            'typeid' => 'SMALLINT(6) UNSIGNED NOT NULL DEFAULT \'0\'',
            'sortid' => 'SMALLINT(6) UNSIGNED NOT NULL DEFAULT \'0\'',
            'readperm' => 'TINYINT(3) UNSIGNED NOT NULL DEFAULT \'0\'',
            'url' => 'VARCHAR(1000) NOT NULL',
            'author' => 'CHAR(15) NOT NULL',
            'authorid' => 'MEDIUMINT(8) UNSIGNED NOT NULL',
            'subject' => 'VARCHAR(1000) NOT NULL',
            'dateline' => 'INT(10) UNSIGNED NOT NULL',
            'lastpost' => 'INT(10) UNSIGNED NOT NULL',
            'lastposter' => 'CHAR(15) NOT NULL',
            'views' => 'INT(10) UNSIGNED NOT NULL DEFAULT \'2467\'',
            'replies' => 'MEDIUMINT(8) UNSIGNED NOT NULL',
            'displayorder' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
            'digest' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
            'rate' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
            'special' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
            'attachment' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
            'moderated' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
            'closed' => 'MEDIUMINT(8) UNSIGNED NOT NULL',
            'stickreply' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT \'0\'',
            'recommends' => 'SMALLINT(6) NOT NULL DEFAULT \'0\'',
            'heats' => 'INT(10) UNSIGNED NOT NULL',
            'status' => 'SMALLINT(6) UNSIGNED NOT NULL DEFAULT \'0\'',
            'isgroup' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
            'favtimes' => 'MEDIUMINT(8) NOT NULL DEFAULT \'0\'',
            'sharetimes' => 'MEDIUMINT(8) NOT NULL DEFAULT \'0\'',
            'stamp' => 'TINYINT(3) NOT NULL DEFAULT \'-1\'',
            'icon' => 'VARCHAR(1000) NOT NULL DEFAULT \'-1\'',
            'pushedaid' => 'MEDIUMINT(8) NOT NULL DEFAULT \'0\'',
            'cover' => 'VARCHAR(1000) NOT NULL',
            'replycredit' => 'SMALLINT(6) NOT NULL DEFAULT \'0\'',
            'relatebytag' => 'CHAR(255) NOT NULL',
            'maxposition' => 'INT(8) UNSIGNED NOT NULL',
            'bgcolor' => 'CHAR(8) NOT NULL',
            'comments' => 'INT(10) UNSIGNED NOT NULL',
            'hidden' => 'SMALLINT(6) UNSIGNED NOT NULL DEFAULT \'0\'',
            'avatar' => 'VARCHAR(255) NULL',
            'PRIMARY KEY (`tid`)'
                ], "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB");

        $this->createIndex('digest', '{{%live_friend_circle}}', 'digest', 0);
        $this->createIndex('sortid', '{{%live_friend_circle}}', 'sortid', 0);
        $this->createIndex('displayorder', '{{%live_friend_circle}}', 'fid, displayorder, lastpost', 0);
        $this->createIndex('typeid', '{{%live_friend_circle}}', 'fid, typeid, displayorder, lastpost', 0);
        $this->createIndex('recommends', '{{%live_friend_circle}}', 'recommends', 0);
        $this->createIndex('heats', '{{%live_friend_circle}}', 'heats', 0);
        $this->createIndex('authorid', '{{%live_friend_circle}}', 'authorid', 0);
        $this->createIndex('isgroup', '{{%live_friend_circle}}', 'isgroup, lastpost', 0);
        $this->createIndex('special', '{{%live_friend_circle}}', 'special', 0);

        $this->createTable('{{%live_friend_comment}}', [
            'pid' => 'BIGINT(20) UNSIGNED NOT NULL',
            'fid' => 'MEDIUMINT(8) UNSIGNED NOT NULL',
            'tid' => 'BIGINT(20) UNSIGNED NOT NULL',
            'first' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
            'author' => 'VARCHAR(15) NOT NULL',
            'authorid' => 'MEDIUMINT(8) UNSIGNED NOT NULL',
            'subject' => 'VARCHAR(80) NOT NULL',
            'dateline' => 'INT(10) UNSIGNED NOT NULL',
            'message' => 'MEDIUMTEXT NOT NULL',
            'useip' => 'VARCHAR(15) NOT NULL',
            'port' => 'SMALLINT(6) UNSIGNED NOT NULL DEFAULT \'0\'',
            'invisible' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
            'anonymous' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
            'usesig' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
            'htmlon' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
            'bbcodeoff' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
            'smileyoff' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
            'parseurloff' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
            'attachment' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
            'rate' => 'SMALLINT(6) NOT NULL DEFAULT \'0\'',
            'ratetimes' => 'TINYINT(3) UNSIGNED NOT NULL DEFAULT \'0\'',
            'status' => 'INT(10) NOT NULL DEFAULT \'0\'',
            'avatar' => 'VARCHAR(255) NOT NULL',
            'comment' => 'TINYINT(1) NOT NULL DEFAULT \'0\'',
            'replycredit' => 'INT(10) NOT NULL DEFAULT \'0\'',
            'position' => 'INT(8) UNSIGNED NOT NULL AUTO_INCREMENT',
            'PRIMARY KEY (`position`)'
                ], "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB");

        $this->createIndex('pid', '{{%live_friend_comment}}', 'pid', 1);
        $this->createIndex('fid', '{{%live_friend_comment}}', 'fid', 0);
        $this->createIndex('authorid', '{{%live_friend_comment}}', 'authorid, invisible', 0);
        $this->createIndex('dateline', '{{%live_friend_comment}}', 'dateline', 0);
        $this->createIndex('invisible', '{{%live_friend_comment}}', 'invisible', 0);
        $this->createIndex('displayorder', '{{%live_friend_comment}}', 'tid, invisible, dateline', 0);
        $this->createIndex('first', '{{%live_friend_comment}}', 'tid, first', 0);

        $this->createTable('{{%live_gallery}}', [
            'id' => 'INT(11) NOT NULL AUTO_INCREMENT',
            'url' => 'VARCHAR(255) NULL',
            'dateline' => 'INT(11) NULL',
            'status' => 'SMALLINT(6) NULL DEFAULT \'0\'',
            'nickname' => 'VARCHAR(255) NULL',
            'PRIMARY KEY (`id`)'
                ], "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB");

        $this->createTable('{{%live_weme}}', [
            'id' => 'INT(11) NOT NULL AUTO_INCREMENT',
            'accountID' => 'VARCHAR(255) NULL',
            'mirrtalkID' => 'VARCHAR(255) NULL',
            'aid' => 'CHAR(50) NULL',
            'au' => 'CHAR(50) NULL',
            'mediatype' => 'CHAR(20) NULL',
            'pt' => 'TINYINT(4) NULL',
            'url' => 'VARCHAR(350) NULL',
            'fr' => 'INT(11) NULL',
            'at' => 'TINYINT(4) NULL',
            'mtime' => 'INT(11) NULL',
            'pw' => 'INT(11) NULL',
            'ph' => 'INT(11) NULL',
            'sz' => 'INT(11) NULL',
            'videoLength' => 'INT(11) NULL',
            'videoTime' => 'INT(11) NULL',
            'gpslist' => 'VARCHAR(255) NULL',
            'T' => 'INT(11) NULL',
            'N' => 'INT(11) NULL',
            'E' => 'INT(11) NULL',
            'V' => 'INT(11) NULL',
            'A' => 'INT(11) NULL',
            'D' => 'INT(11) NULL',
            'status' => 'SMALLINT(1) NULL DEFAULT \'0\' COMMENT \'0:未发送,1:已发送到图文直播,2:已发送到汇友圈,3:已发送到图文直播和汇友圈\'',
            'flag' => 'SMALLINT(1) NULL COMMENT \'0:未读,1:已读\'',
            'PRIMARY KEY (`id`)'
                ], "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB");

        $this->createTable('{{%live_weme_nickname}}', [
            'id' => 'MEDIUMINT(9) NOT NULL AUTO_INCREMENT',
            'url' => 'VARCHAR(1000) NULL',
            'mirrtalkID' => 'VARCHAR(255) NULL',
            'nickname' => 'VARCHAR(255) NULL',
            'PRIMARY KEY (`id`)'
                ], "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB");
    }

    public function down() {
        $this->execute('SET foreign_key_checks = 0');
        $this->dropTable('{{%live_weme_nickname}}');
        $this->dropTable('{{%live_weme_nickname}}');
        $this->dropTable('{{%live_weme_nickname}}');
        $this->dropTable('{{%live_weme_nickname}}');
        $this->dropTable('{{%live_weme_nickname}}');
        $this->execute('SET foreign_key_checks = 1;');
    }

}
