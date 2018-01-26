<?php

use yii\db\Migration;

class m171127_033459_cloudLiveVrlive extends Migration
{
    public function init() {
        $this->db = Yii::$app->vrlive;
        parent::init();
    }
    public function up()
    {

 

         $this->addColumn('{{live}}', 'is_cloud', "TINYINT(1) default '0' COMMENT '云直播'");

 
   }

    public function down()
    {
    


    }
}