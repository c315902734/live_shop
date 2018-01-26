<?php

use yii\db\Migration;

class m160923_100300_votes extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{votes}}',[
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(10)->notNull(),//在nma系统中的选手ID
            'group_id' => $this->integer(10)->notNull(),//所在年龄组ID
            'sex' => $this->smallInteger(2)->notNull(),
            'user_name' => $this->string()->notNull(),//选手名称
            'alias_name' => $this->string()->notNull(),//外号
            'image' => $this->string()->notNull(),//头像地址
            'vote_cnt' => $this->integer(10)->notNull(),
        ],$tableOptions);
        
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '刘天胜',
            'alias_name' => '玉临风',
            'image' => '/user/man/1.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '赵荟彬',
            'alias_name' => '中原豹',
            'image' => '/user/man/2.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '石伟',
            'alias_name' => '电光侠',
            'image' => '/user/man/3.png',
            'vote_cnt' => 0,
        ]);

        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '额日登宝力嘎',
            'alias_name' => '飞天隼',
            'image' => '/user/man/4.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '朱梦豪',
            'alias_name' => '霹雳手',
            'image' => '/user/man/5.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '陈道玞',
            'alias_name' => '神跤浪子',
            'image' => '/user/man/6.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '古日嘎拉',
            'alias_name' => '草原猎人',
            'image' => '/user/man/7.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '芮伟杰',
            'alias_name' => '风暴战神',
            'image' => '/user/man/8.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '周志鑫',
            'alias_name' => '惊天雷',
            'image' => '/user/man/9.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '巴图额尔敦',
            'alias_name' => '烈马金枪',
            'image' => '/user/man/10.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '吕文龙',
            'alias_name' => '泰山玉龙',
            'image' => '/user/man/11.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '王峥',
            'alias_name' => '移山圣手',
            'image' => '/user/man/12.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '郝成垚',
            'alias_name' => '白金战士',
            'image' => '/user/man/13.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '萨其日夫',
            'alias_name' => '丛林狼',
            'image' => '/user/man/14.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '呼德尔',
            'alias_name' => '大漠孤客',
            'image' => '/user/man/15.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '郭阔',
            'alias_name' => '冀中圣斗士',
            'image' => '/user/man/16.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '刘小涛',
            'alias_name' => '入海蛟龙',
            'image' => '/user/man/17.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '肖立果',
            'alias_name' => '摘月神勾',
            'image' => '/user/man/18.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '巴雅尔',
            'alias_name' => '八臂天王',
            'image' => '/user/man/19.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '乌日图那斯吐',
            'alias_name' => '赤手神龙',
            'image' => '/user/man/20.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '额尔登朝克图',
            'alias_name' => '青铜斗士',
            'image' => '/user/man/21.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '赵珂',
            'alias_name' => '无敌小金刚',
            'image' => '/user/man/22.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '史尚勇',
            'alias_name' => '忻州卧虎刀',
            'image' => '/user/man/23.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '樊健',
            'alias_name' => '镇三江',
            'image' => '/user/man/24.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '哈旦巴特尔',
            'alias_name' => '大漠军刀',
            'image' => '/user/man/25.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '安悠扬',
            'alias_name' => '金刚战士',
            'image' => '/user/man/26.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '乌云毕力格',
            'alias_name' => '塞外雄鹰',
            'image' => '/user/man/27.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '贾京阳',
            'alias_name' => '无敌刀客',
            'image' => '/user/man/28.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '赵强',
            'alias_name' => '香山少侠',
            'image' => '/user/man/29.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 1,
            'user_name' => '陈冲',
            'alias_name' => '月光战神',
            'image' => '/user/man/30.png',
            'vote_cnt' => 0,
        ]);
        //女生
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 2,
            'user_name' => '刘思雨',
            'alias_name' => '紫心剑客',
            'image' => '/user/woman/12.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 2,
            'user_name' => '苏日古格',
            'alias_name' => '追风雨蝶',
            'image' => '/user/woman/11.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 2,
            'user_name' => '那琴',
            'alias_name' => '射雕玉女',
            'image' => '/user/woman/10.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 2,
            'user_name' => '古鸿凤',
            'alias_name' => '霹雳娇娃',
            'image' => '/user/woman/9.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 2,
            'user_name' => '李安然',
            'alias_name' => '霹雳火',
            'image' => '/user/woman/8.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 2,
            'user_name' => '马楠',
            'alias_name' => '津门小龙女',
            'image' => '/user/woman/7.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 2,
            'user_name' => '李领兄',
            'alias_name' => '紫衫龙女',
            'image' => '/user/woman/6.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 2,
            'user_name' => '王珏',
            'alias_name' => '玉麒麟',
            'image' => '/user/woman/5.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 2,
            'user_name' => '张璐',
            'alias_name' => '齐鲁霸王花',
            'image' => '/user/woman/4.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 2,
            'user_name' => '王月琳',
            'alias_name' => '欢迎天使',
            'image' => '/user/woman/3.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 2,
            'user_name' => '铁亚萍',
            'alias_name' => '昆仑跤花',
            'image' => '/user/woman/2.png',
            'vote_cnt' => 0,
        ]);
        $this->insert('{{votes}}',[
            'user_id' => 0,
            'group_id' => 0,
            'sex' => 2,
            'user_name' => '王银凤',
            'alias_name' => '火凤凰',
            'image' => '/user/woman/1.png',
            'vote_cnt' => 0,
        ]);
        
        
        


    }

    public function down()
    {
        echo "m160923_100300_votes cannot be reverted.\n";

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
