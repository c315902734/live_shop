<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 2017/3/10
 * Time: 11:28
 */
namespace frontend\controllers;

use common\models\UserAmount;
use common\models\Vote;
use common\models\VoteBallot;
use common\models\VoteClass;
use common\models\VoteOption;
use Yii;
use yii\base\Controller;

class ActivityController extends PublicBaseController
{
    public $request;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->request = Yii::$app->request;
    }

    /**
     * 投票的主要信息 和 一级
     */
    public function actionVoteInfo(){
        $vote_id = $this->request->post('vote_id', 0);

        if(!$vote_id){
            $this->_errorData('102', 'ID错误');
        }

        $vote_data = Vote::find()->where(['vote_id'=>$vote_id])->asArray()->one();
        $group_arr = VoteClass::find()->where(['vote_id'=>$vote_id, 'parent_id'=>0])->asArray()->all();

        if(!$vote_data){
            $this->_errorData('102', 'ID错误');
        }

        $vote_data['host']  = unserialize($vote_data['host']);
        $vote_data['contractors'] = $vote_data['contractors'] ? unserialize($vote_data['contractors']) : '';
        $vote_data['vote']  = $vote_data['vote'] ? unserialize($vote_data['vote']) : '';
        $vote_data['group'] = $group_arr;
        $vote_data['start_time'] = date('Y-m-d', $vote_data['start_time']);
        $vote_data['end_time'] = date('Y-m-d', $vote_data['end_time']);

        if($vote_data){
            $this->_successData($vote_data);
        }
        $this->_errorData('101', '投票没有找到');
    }

    /**
     *
     */
    public function actionVoteClassData(){
        $vote_id = $this->request->post('vote_id', 0);
        $parent_id = $this->request->post('pid', 0);
        $page = $this->request->post('page', 1);
        $size = $this->request->post('size', 8);

        //pid下所有的子集和子集内容
        $class_list = VoteClass::find()->where(['vote_id'=>$vote_id, 'parent_id'=>$parent_id])->asArray()->all();

        if(is_array($class_list) && count($class_list)>0){
            //获取第一条二级下的内容
            $count = VoteOption::find()->where(['class_id'=>$class_list[0]['class_id']])->count();

            if($page == 1){
                $_start = 0;
            }else{
                $_start = ($page - 1) * $size;
            }
            $_end   = $size;

            $list_sql = 'SELECT vo.*,count(vb.ballot_id) as ball_count FROM vrnews1.vote_option as vo LEFT JOIN vrnews1.vote_ballot as vb ON vo.option_id=vb.option_id WHERE vo.class_id='.$class_list[0]['class_id'].' GROUP BY vo.option_id ORDER BY ball_count DESC LIMIT '.$_start.','.$_end;
            $connection  = Yii::$app->db;
            $model = $connection->createCommand($list_sql);
            $list = $model->queryAll();

            if($list){
                foreach($list as $k=>&$v){
                    if($page == 1 && $k == 0){
                        $v['index'] = $k + 1;
                    }elseif($page == 1 && $k == 1){
                        $v['index'] = $k + 1;
                    }elseif($page == 1 && $k == 2){
                        $v['index'] = $k + 1;
                    }

                    if($v['abstract']){
                        $v['abstract'] = unserialize($v['abstract']);
                    }
                }
                unset($v);
            }

            $class_list[0]['list'] = $list;
        }

        if($class_list){
            $vote_info = Vote::find()->select(['start_time', 'end_time'])->where(['vote_id'=>$vote_id])->asArray()->one();
            $return_list['vote_create_time'] = date('Y-m-d', $vote_info['start_time']);
            $return_list['vote_end_time'] = date('Y-m-d', $vote_info['end_time']);
            $return_list['option_count'] = $count;
            $return_list['list'] = $class_list;
            $this->_successData($return_list);
        }
        $this->_errorData('103', 'error');
    }

    /**
     * 获取投票选项
     */
    public function actionVoteOption(){
        $vote_id  = $this->request->post('vote_id', 0);
        $class_id = $this->request->post('pid', 0);
        $page = $this->request->post('page', 1);
        $size = $this->request->post('size', 8);

        if(!$vote_id || !$class_id) $this->_errorData('104', 'ID错误');

        $count = VoteOption::find()->where(['class_id'=>$class_id])->count();
        if($page == 1){
            $_start = 0;
        }else{
            $_start = ($page - 1) * $size;
        }
        $_end   = $size;

        $list_sql = 'SELECT 
                      vo.*,count(vb.ballot_id) as ball_count 
                      FROM vrnews1.vote_option as vo 
                      LEFT JOIN vrnews1.vote_ballot as vb ON vo.option_id=vb.option_id 
                      WHERE vo.class_id='.$class_id.' 
                      GROUP BY vo.option_id
                      ORDER BY ball_count DESC
                      LIMIT '.$_start.','.$_end.'
                      ';

        $connection  = Yii::$app->db;
        $model = $connection->createCommand($list_sql);
        $list = $model->queryAll();

        if($list){
            foreach($list as $k=>&$v){
                if($page == 1 && $k == 0){
                    $v['index'] = $k + 1;
                }elseif($page == 1 && $k == 1){
                    $v['index'] = $k + 1;
                }elseif($page == 1 && $k == 2){
                    $v['index'] = $k + 1;
                }

                if($v['abstract']){
                    $v['abstract'] = unserialize($v['abstract']);
                }
            }
            unset($v);

            $vote_info = Vote::find()->select(['start_time', 'end_time'])->where(['vote_id'=>$vote_id])->asArray()->one();
            $option_list['vote_info'] = $vote_info;
            $option_list['vote_info']['create_time'] = date('Y-m-d', $vote_info['start_time']);
            $option_list['vote_info']['end_time'] = date('Y-m-d', $vote_info['end_time']);
            $option_list['vote_info']['option_count'] = $count ? $count : 0;

            $option_list['list'] = $list;
            $this->_successData($option_list);
        }
        $this->_errorData('105', 'error');
    }

    /**
     * 开始投票
     */
    public function actionVote(){
        $vote_id   = $this->request->post('vote_id', 0);
        $option_id = $this->request->post('option_id', 0);

        $user = $this->_getUserData();

        if(!$user['userId'] || !$user){
            $this->_errorData('121', '用户未登录');
        }
        $user_id = (string)$user['userId'];

        if(!$vote_id || !$option_id) $this->_errorData('106', '参数错误');

        if(!$user_id) $this->_errorData('121', '用户未登录');

        $vote_info = Vote::find()->select(['vote_num', 'huiwenbi', 'start_time', 'end_time'])->where(['vote_id'=>$vote_id])->asArray()->one();

        if(!$vote_info) $this->_errorData('111', '此投票不存在');

        if(time() < $vote_info['start_time']){
            $this->_errorData('112', '此投票未开始');
        }
        if(time() > $vote_info['end_time']){
            $this->_errorData('113', '此投票已结束');
        }


        $start_time = strtotime(date('Y-m-d', time()));
        $end_time = strtotime(date('Y-m-d', time()).' 23:59:59');

        //是否已经投过此票
        $vote_user_count = VoteBallot::find()->where(['user_id'=>$user_id, 'option_id'=>$option_id, 'vote_id'=>$vote_id])->andWhere(['>=', 'create_time', $start_time])->andWhere(['<=', 'create_time', $end_time])->asArray()->count();
        if($vote_user_count >= 1){
            $this->_errorData('116', '每天每人只能给一个人投一票');
        }

        //今天投票次数
        $user_vote_count = VoteBallot::find()
            ->where(['user_id'=>$user_id, 'vote_id'=>$vote_id])
            ->andWhere(['>=', 'create_time', $start_time])
            ->andWhere(['<=', 'create_time', $end_time])
            ->asArray()
            ->count();

        if($user_vote_count >= $vote_info['vote_num']){
            $this->_errorData('110', '今日投票次数已用完');
        }

        $vote_ballot_model = new VoteBallot();
        $vote_ballot_model->vote_id = $vote_id;
        $vote_ballot_model->option_id = $option_id;
        $vote_ballot_model->user_id = $user_id;
        $vote_ballot_model->create_time = time();
        $ret = $vote_ballot_model->save();

        if($ret) {
            if($vote_info['huiwenbi'] > 0){
                $add_data['user_id']      = $user_id;
                $add_data['operate_cnt']  = $vote_info['huiwenbi'];
                $add_data['operate']      = '1';
                $add_data['operate_name'] = '参加投票';
                $add_data['task_id']      = '7';
                UserAmount::addUserAmount($add_data);
            }

            $this->_successData('ok');
        }else{
            $this->_errorData('120', '投票失败');
        }
        $this->_errorData('107', 'error');
    }

    /**
     * 搜索选项
     */
    public function actionVoteSearch(){
        $vote_id = $this->request->post('vote_id', 0);
        $option_name = $this->request->post('name', 0);

        if(!$vote_id || !$option_name) $this->_errorData('108', '参数错误');

        $option_list_sql = "SELECT vo.*,count(vb.ballot_id) as ball_count FROM vrnews1.vote_option as vo LEFT JOIN vrnews1.vote_ballot as vb ON vo.option_id=vb.option_id WHERE vo.name LIKE '%$option_name%' AND vo.vote_id=$vote_id GROUP BY vo.option_id";
        $connection  = Yii::$app->db;
        $model = $connection->createCommand($option_list_sql);
        $option_list = $model->queryAll();

        $vote_info = $this->getVoteInfo($vote_id);

        if(is_array($option_list)){
            foreach ($option_list as &$v) {
                if($v['abstract']){
                    $v['abstract'] = unserialize($v['abstract']);
                }
            }
            unset($v);
        }

        $return_arr = array('vote_info'=>$vote_info, 'option_list'=>$option_list);
        $this->_successData($return_arr);
    }

    protected function getVoteInfo($vote_id){
        if(!$vote_id) return array();

        $vote_info = Vote::find()->where(['vote_id'=>$vote_id])->asArray()->one();

        return $vote_info;
    }

}