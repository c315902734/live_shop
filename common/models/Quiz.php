<?php

namespace common\models;

use Yii;
use common\models\NewsQuiz;
use common\models\QuizRule;
use common\models\UserQuiz;
use common\models\User1;
use common\models\UserAmount;

/**
 * This is the model class for table "quiz".
 *
 * @property string $quiz_id
 * @property string $quiz_name
 * @property string $correct_id
 * @property string $created_at
 */
class Quiz extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'quiz';
    }
    
    public static function getDb()
    {
        return Yii::$app->vrnews1;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['quiz_id'], 'required'],
            [['quiz_id', 'correct_id'], 'integer'],
            [['created_at'], 'safe'],
            [['quiz_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'quiz_id' => 'Quiz ID',
            'quiz_name' => 'Quiz Name',
            'correct_id' => 'Correct ID',
            'created_at' => 'Created At',
        ];
    }

    public static function getQuizInfo($news_id, $user_id){
        if(!$news_id){
            $res['code'] = '0001';
            $res['message'] = '参数错误';
            return $res;
        }else {
            //查询新闻的 竞猜 ids
            $quiz_ids = NewsQuiz::find()->where(['news_id' => $news_id])->asArray()->all();
            if (!$quiz_ids) {
                $res['code'] = '8873';
                $res['message'] = '没有竞猜内容';
                return $res;
                exit;
            }
            $where = "";
            foreach ($quiz_ids as $value) {
                $where .= ($where == "") ? "quiz.quiz_id =" . $value['quiz_id'] : " or quiz.quiz_id =" . $value['quiz_id'];
            }
            $quiz_rule = QuizRule::find()
                ->select("quiz_rule.`rule_id` , quiz.`quiz_id`,quiz_rule.`rule_name`,quiz.`quiz_name`,quiz.`correct_id`")
                ->leftJoin("vrnews1.quiz quiz", "quiz.quiz_id = quiz_rule.quiz_id")
                ->where($where)
                ->orderBy("quiz.created_at,quiz_rule.sort_num")
                ->asArray()->all();
            //查询支持率
            $result = array();
            foreach ($quiz_rule as $key => $val) {
                //确认外层数据，只有第一次创建的时候添加
//                $result[$val['quiz_name']] = '';
                if (!array_key_exists($val['quiz_name'], $result)) {
                    //题目
                    $result[$val['quiz_name']]['quiz_name'] = $val['quiz_name'];
                    //每个题目的总金币数
                    $quiz_amount = UserQuiz::find()->where(['quiz_id' => $val['quiz_id']])->sum('amount');
                    if ($quiz_amount)
                        $result[$val['quiz_name']]['amount'] = $quiz_amount;
                    else
                        $result[$val['quiz_name']]['amount'] = '0';
                    //默认当前用户对该题目没有进行过押注
                    $result[$val['quiz_name']]['is_correct'] = 0;
                    $result[$val['quiz_name']]['bett_amount'] = '0';
                    $result[$val['quiz_name']]['quiz_id'] = $val['quiz_id'];
                    //是否已经公布正确答案
                    $result[$val['quiz_name']]['correct_id'] = $val['correct_id'];
                }
                //确定哪些字段在内层展示
                $output['quiz_id'] = $val['quiz_id'];//题目ID
                $output['rule_id'] = $val['rule_id'];//答案ID
                $output['rule_name'] = $val['rule_name'];//答案
                $output['quiz_name'] = $val['quiz_name'];//题目
                //当前选项是否是正确答案
                if ($val['correct_id'] != '0' && $val['correct_id'] == $val['rule_id'])
                    $output['is_correct'] = 1;
                else
                    $output['is_correct'] = 0;

                //当前选项的总押注金额
                $amount = UserQuiz::find()->where(['rule_id' => $val['rule_id']])->sum('amount');
                if ($amount)
                    $output['amount'] = $amount;
                else
                    $output['amount'] = '0';

                //支持率
                //题目的总支持数
                $quiz_cnt = UserQuiz::find()->where(['quiz_id' => $val['quiz_id']])->count();
                if ($quiz_cnt == 0) {
                    $output['support'] = '0%';
                } else {
                    //每个选项的支持数
                    $rule_cnt = UserQuiz::find()->where(['rule_id' => $val['rule_id']])->count();
                    $output['support'] = (round($output['amount'] / $result[$val['quiz_name']]['amount'], 3) * 100) . '%';
//                        $output['support'] = (round($rule_cnt / $quiz_cnt,2)*100).'%';
                }

                //如果有用户，判断当前用户是否已经对当前选项押注过，以及押注金额
                if ($user_id) {
                    //查询是否给此选项投过票
                    $betting = UserQuiz::find()->where(['rule_id' => $val['rule_id'], 'user_id' => $user_id])->asArray()->all();
                    if ($betting) {
                        //当前用户是否押注
                        $output['is_betting'] = 1;
                        //当前用户的押注金额
                        //$betting[0] 考虑规则，一个人只能押注一次
                        $output['bett_amount'] = $betting[0]['amount'];
                        ///--------注意外层数据
                        //默认一个用户只能押注一次，所以此处赋值只会发生一次
                        $result[$val['quiz_name']]['is_correct'] = 1;
                        $result[$val['quiz_name']]['bett_amount'] = intval($betting[0]['amount']);
                        $get_amount = $output['bett_amount']+($output['bett_amount']/$output['amount'])*($quiz_cnt-$output['amount']);
                        $output['get_amount'] = round($get_amount);
                        //--------------
                    } else {
                        $output['is_betting'] = 0;
                        $output['bett_amount'] = '0';
                    }
                } else {//没有登录用户
                    $output['is_betting'] = 0;
                    $output['bett_amount'] = '0';
                }
                //储存内存数据进入列表
                $result[$val['quiz_name']]['rule'][] = $output;
            }
            $res = array();
            foreach ($result as $key => $val) {
                $res[] = $result[$key];
            }
            return $res;
        }
    }
}
