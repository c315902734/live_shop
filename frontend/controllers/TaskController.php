<?php
namespace frontend\controllers;
use Yii;
use yii\base\Controller;


class TaskController extends PublicBaseController
{
	/**
	 * 积分详情
	 */
	public function actionIndex(){
		$user_id = yii::$app->request->post('user_id');
		/* if(!$user_id){
			return 'user does not exist';
		} */
		$task = new \common\models\Task();
		$list = $task->taskList($user_id);
		$this->_successData($list);
// 		return $this->render('index', ['list'=>$list]);
	}

	/**
	 * 积分详情
	 */
	public function actionTaskdetail(){
		return $this->render('detail');
	}
}