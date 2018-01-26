<?php
namespace frontend\controllers;

use common\models\AdStartupImages;

class AdStartupController extends PublicBaseController
{
    public function actionIndex()
    {
        $result = AdStartupImages::getNewAdStartup();
        $this->_successData($result);
    }
}
