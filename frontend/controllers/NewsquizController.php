<?php
namespace frontend\controllers;

use common\models\NewsQuiz;
use common\models\Quiz;
use common\models\QuizRule;
use common\models\UserQuiz;
use common\models\User1;
use common\models\UserAmount;

class NewsquizController extends BaseApiController
{
	/**
	 * 投注
	 */
	public function actionBetting(){
		$token = !empty($_REQUEST['token']) ? $_REQUEST['token'] : '';
		$userData = $this->_checkToken($token);
		if($userData == false){
			$this->_errorData(0055, '用户未登录');
		}else{
			//token有效
			$user_id = $userData['userId'];
			$quiz_id = !empty($_REQUEST['quiz_id']) ? $_REQUEST['quiz_id'] : '';
			$rule_id = !empty($_REQUEST['rule_id']) ? $_REQUEST['rule_id'] : '';
			$news_id = !empty($_REQUEST['news_id']) ? $_REQUEST['news_id'] : '';
			$amount = !empty($_REQUEST['amount']) ? $_REQUEST['amount'] : '';
			if(!$quiz_id || !$user_id || !$rule_id || !$news_id || !$amount || intval($amount)<=0){
				$this->_errorData(0001, '参数错误');
			}else{
				//确认新闻、竞猜题目、竞猜选项是否存在
				/* $res_news = $this->news_quiz_model
				->field('vrnews1.quiz.correct_id as correct_id')
				->join("vrnews1.quiz quiz on quiz.quiz_id = news_quiz.quiz_id","right")
				->join("vrnews1.quiz_rule quiz_rule on quiz_rule.quiz_id = quiz.quiz_id","right")
				->where("news_quiz.news_id=".$news_id." and news_quiz.quiz_id=".$quiz_id)
				->find(); */
				$res_news = NewsQuiz::find()->select('vrnews1.quiz.correct_id as correct_id')
							->join('right join', 'vrnews1.quiz quiz', 'quiz.quiz_id = news_quiz.quiz_id')
							->join('right join', 'vrnews1.quiz_rule quiz_rule', 'quiz_rule.quiz_id = quiz.quiz_id')
							->where(['news_quiz.news_id'=>$news_id, 'news_quiz.quiz_id'=>$quiz_id])
							->asArray()
							->one();
							//->createCommand()->getRawSql();
				//如果已经公布答案，提示用户
				if($res_news['correct_id']!='0'){
					// $this->_errorData(5386, '竞猜已经结束');
					// exit;
				}
				/* $res_quiz_rule = $this->quiz_rule_model->where("quiz_id=".$quiz_id." and rule_id=".$rule_id)->find(); */
				$res_quiz_rule = QuizRule::find()->where(['quiz_id'=>$quiz_id, 'rule_id'=>$rule_id])->asArray()->one();
				
				
				if($res_news && $res_quiz_rule){
					//确认，是否已经竞猜过
					/* $betting = $this->user_quiz_model
					->where("quiz_id=".$quiz_id." and user_id=".$user_id)
					->select(); */
					$betting = UserQuiz::find()->where(['quiz_id'=>$quiz_id, 'user_id'=>$user_id])->all();
					
					if($betting){
						$this->_errorData(5383, '已经竞猜过');
					}else{
						//确认是否拥有充足金币
						/* $user = $this->user_model->getUserById($user_id); */
						$user = User1::getUserById($user_id);
						
						if($user && intval($user['amount'])>=intval($amount)){
							//记录投注信息
							/* $user_quiz['id']         = $this->createId();
							$user_quiz['user_id']    = $user_id;
							$user_quiz['news_id']    = $news_id;
							$user_quiz['quiz_id']    = $quiz_id;
							$user_quiz['rule_id']    = $rule_id;
							$user_quiz['amount']     = $amount;
							$user_quiz['won_cnt']    = 0;
							$user_quiz['created_at'] = date('Y-m-d H:i:s',time());
							$res                     = $this->user_quiz_model->add($user_quiz); */
							$user_quiz_model = new UserQuiz();
							//$user_quiz_model->id = $this->createId();
							$user_quiz_model->user_id = $user_id;
							$user_quiz_model->news_id = $news_id;
							$user_quiz_model->quiz_id = $quiz_id;
							$user_quiz_model->rule_id = $rule_id;
							$user_quiz_model->amount = $amount;
							$user_quiz_model->won_cnt = 0;
							$user_quiz_model->created_at = date('Y-m-d H:i:s',time());
							$res = $user_quiz_model->insert();
							
							if($res){
								// $data['amount'] = intval($user['amount'])-intval($amount);
								// $res = $this->user_model->where('user_id='.$user_id)->save($data);
								//扣除金币，并且增加日志记录
								$param['user_id']      = $user_id;
								$param['operate_cnt']  = $amount;
								$param['operate']      = 2;
								$param['operate_name'] = '竞猜';
								$userAmount            = new UserAmount();
								$res = $userAmount->addUserAmount($param);
								if($res){
									//重新获取当前比赛的数据
									/* $quiz_rule = $this->quiz_rule_model
									->field("quiz_rule.`rule_id` , quiz.`quiz_id`,quiz_rule.`rule_name`,quiz.`quiz_name`,quiz.`correct_id`")
									->join("vrnews1.quiz quiz on quiz.quiz_id = quiz_rule.quiz_id","left")
									->where("quiz.quiz_id = ".$quiz_id)
									->order("quiz.created_at,quiz_rule.rule_id")
									->select(); */
									$quiz_rule = QuizRule::find()->select('quiz_rule.`rule_id` , quiz.`quiz_id`,quiz_rule.`rule_name`,quiz.`quiz_name`,quiz.correct_id')
												->join('left join', 'vrnews1.quiz', 'quiz.quiz_id = quiz_rule.quiz_id')
												->where(['quiz.quiz_id'=>$quiz_id])
												->orderBy('quiz.created_at,quiz_rule.rule_id')												
												->asArray()
												->all();
									$result = array();
									foreach ($quiz_rule as $key=>$val) {
										//确认外层数据，只有第一次创建的时候添加
//										$result[$val['quiz_name']] = '';
										if(!array_key_exists($val['quiz_name'], $result)){
											//题目
											$result[$val['quiz_name']]['quiz_name'] = $val['quiz_name'];
											//每个题目的总金币数
											/* $quiz_amount = $this->user_quiz_model
											->where("quiz_id=".$val['quiz_id'])
											->sum('amount'); */
											$quiz_amount = UserQuiz::find()->where(['quiz_id'=>$val['quiz_id']])->sum('amount');
											
											if($quiz_amount)
												$result[$val['quiz_name']]['amount'] = $quiz_amount;
												else
													$result[$val['quiz_name']]['amount'] = '0';
													//默认当前用户对该题目没有进行过押注
													$result[$val['quiz_name']]['is_correct'] = 0;
													$result[$val['quiz_name']]['bett_amount'] = '0';
													//是否已经公布正确答案
													$result[$val['quiz_name']]['correct_id'] = $val['correct_id'];
										}
										//确定哪些字段在内层展示
										$output['quiz_id'] = $val['quiz_id'];//题目ID
										$output['rule_id'] = $val['rule_id'];//答案ID
										$output['rule_name'] = $val['rule_name'];//答案
										$output['quiz_name'] = $val['quiz_name'];//题目
										//当前选项是否是正确答案
										if ($val['correct_id']!='0' && $val['correct_id']==$val['rule_id'])
											$output['is_correct'] = 1;
											else
												$output['is_correct'] = 0;
												//当前选项的总押注金额
												/* $amount = $this->user_quiz_model
												->where("rule_id=".$val['rule_id'])
												->sum('amount'); */
												$amount = UserQuiz::find()->where(['rule_id'=>$val['rule_id']])->sum('amount');
												
												if($amount)
													$output['amount'] = $amount;
													else
														$output['amount'] = '0';
														//支持率
														//题目的总支持数
														/* $quiz_cnt = $this->user_quiz_model
														->where("quiz_id=".$val['quiz_id'])
														->count(); */
														$quiz_cnt = UserQuiz::find()->where(['quiz_id'=>$val['quiz_id']])->count();
														
														if($quiz_cnt==0){
															$output['support'] = '0%';
														}else{
															//每个选项的支持数
															/*$rule_cnt = $this->user_quiz_model
															 ->where("rule_id=".$val['rule_id'])
															 ->count();
															 $output['support'] = (round($rule_cnt / $quiz_cnt,2)*100).'%'; */
															//每个选项的支持数
															/* $rule_cnt = $this->user_quiz_model
															->where("rule_id=".$val['rule_id'])
															->count(); */
															$rule_cnt = UserQuiz::find()->where(['rule_id'=>$val['rule_id']])->count();
															
															$output['support'] = (round($output['amount'] / $result[$val['quiz_name']]['amount'],3)*100).'%';
														}
														//如果有用户，判断当前用户是否已经对当前选项押注过，以及押注金额
														if($user_id){
															//查询是否给此选项投过票
															/* $betting = $this->user_quiz_model
															->where("rule_id=".$val['rule_id']." and user_id=".$user_id)
															->select(); */
															$betting = UserQuiz::find()->where(['rule_id'=>$val['rule_id'], 'user_id'=>$user_id])->asArray()->all();
															
															if($betting){
																//当前用户是否押注
																$output['is_betting'] = 1;
																//当前用户的押注金额
																//$betting[0] 考虑规则，一个人只能押注一次
																$output['bett_amount'] = $betting[0]['amount'];
																///--------注意外层数据
																//默认一个用户只能押注一次，所以此处赋值只会发生一次
																$result[$val['quiz_name']]['is_correct']  = 1;
																$result[$val['quiz_name']]['bett_amount'] = $betting[0]['amount'];
																//--------------
															}else{
																$output['is_betting'] = 0;
																$output['bett_amount'] = '0';
															}
														}else{//没有登录用户
															$output['is_betting'] = 0;
															$output['bett_amount'] = '0';
														}
														//储存内存数据进入列表
														$result[$val['quiz_name']]['rule'][] = $output;
									}
									$res = array();
									foreach ($result as $key => $val) {
										$res[] = $val;
									}
									$this->_successData($res, "押注成功");
								}else{
									$this->_errorData(5384, '抱歉！余额不足请充值');
								}
							}else{
								$this->_errorData("5394", "押注失败，请稍候再试");
							}
						}else{
							$this->_errorData(5384, '抱歉！余额不足请充值');
						}
					}
				}else{
					$this->_errorData(5385, '抱歉！竞猜内容错误，请刷新页面');
				}
			}
		}
	}
}