<?php
namespace common\service;

use yii\redis\Cache;

class redis extends Cache{

    public function get($key){
        return $this->getValue($key);
    }
}