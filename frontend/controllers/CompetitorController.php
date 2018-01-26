<?php
namespace frontend\controllers;
use frontend\controllers\PublicBaseController;
use common\models\LiveCompetitor;
use common\models\LiveUserProp;
use common\models\LiveProp;
use common\models\LivePk;
use common\models\LivePkCompetitor;

class CompetitorController extends PublicBaseController
{
	/**
	 * 选手列表 
	 */
	public function actionGetcompetitorlist(){
		$token = !empty($_REQUEST['token']) ? $_REQUEST['token'] : '';
		if($token!=''){
			$userData = $this->_checkToken($token);
			if($userData == false){
				$this->_errorData(0055, '用户未登录');
				exit;
			}
		}
	    
		//查询选手信息
		/* $competitor = $this->live_competitor_model->where("status=1")
		->field("competitor_id,nickname,real_name,avatar,popular_value")
		->order("popular_value desc,convert(real_name USING gbk) asc ")
		->select(); */
		
		$competitor = LiveCompetitor::find(['competitor_id', 'nickname', 'real_name', 'avatar', 'popular_value'])
						->where(['status'=>1])
						->orderBy(['popular_value'=>SORT_DESC, 'convert(real_name USING gbk)'=>SORT_ASC])
						->asArray()
						->all();
						
		/* $sumPopular = $this->live_competitor_model->where("status=1")->sum("popular_value"); */
		$sumPopular = LiveCompetitor::find()->where(['status'=>1])->sum("popular_value");
		
		//计算支持率 人气值/总人气值
		$i = 1;
		foreach ($competitor as $key => $val) {
			$support =  (round($val['popular_value'] / $sumPopular,3)*100);
			$competitor[$key]['support'] = $support;
			//人气值处理
			$popularValue = intval($val['popular_value']);
			if($popularValue>=10000){
				$popularValue = round($popularValue/10000,1)."万";
				$competitor[$key]['popular_value'] = $popularValue;
			}
			$competitor[$key]['ranking'] = $i;
			$i++;
			if(!$val['popular_value']){
				$competitor[$key]['popular_value'] = '0';
			}
		}
		$this->_successData($competitor);
	}
	
	/**
	 * 获取直播参赛选手列表
	 */
	public function actionCompetitorlist(){
		$token   = !empty($_REQUEST['token']) ? $_REQUEST['token'] : '';
		$live_id = !empty($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '';
		if($token!=''){
			$userData = $this->_checkToken($token);
			if($userData == false){
				$this->_errorData(0055, '用户未登录');
				exit;
			}
		}
		if(!$live_id){
			$this->_errorData(0001, '参数错误');
		}
		//查询选手信息
		/* $competitor= M("vrlive.live_competitor", null);
		$user_prop = M("vrlive.live_user_prop", null);
		$live_pk   = M("vrlive.live_pk"); */
		
		$competitor = new LiveCompetitor();
		$user_prop  = new LiveUserProp();
		$live_pk	= new LivePk();
		
		/* $pk_competitor = M("vrlive.live_pk_competitor"); */
		
		$pk_competitor = new LivePkCompetitor();
		
		/* $live_pk_info = $live_pk->where(array('live_id'=>$live_id, 'status' =>1))->select('id as pk_id,title')->orderBy('create_time asc')->all(); //pk列表 */
		$live_pk_info = $live_pk::find()->select('id as pk_id,title')->where(['live_id'=>$live_id, 'status'=>1])->orderBy('create_time asc')->asArray()->all();
		
		if($live_pk_info){
			foreach ($live_pk_info as $key=>$val){
				/* $competitor_info = $pk_competitor->where(array('live_id'=>$live_id, 'pk_id'=>$val['pk_id']))->field('competitor_id,competitor_name,pk_id,live_id,group_id')->select(); //pk组选手 */
				$competitor_info = LivePkCompetitor::find()->select('competitor_id,competitor_name,pk_id,live_id,group_id')->where(['live_id'=>$live_id, 'pk_id'=>$val['pk_id']])->asArray()->all();
				
				if($competitor_info){
					foreach ($competitor_info as $k=>$v){
						/* $info = $competitor->where(array('competitor_id'=>$v['competitor_id']))->field('avatar, popular_value')->find(); */
						$info = $competitor::find()->select('avatar, popular_value')->where(['competitor_id'=>$v['competitor_id']])->asArray()->one();
						/* $data = $user_prop->where(array('live_id'=>$live_id, 'pk_id'=>$val['pk_id'], 'competitor_id'=>$v['competitor_id']))->field('sum(sentiment_value) as popular')->find(); */
						$data = $user_prop::find()->select('sum(sentiment_value) as popular')->where(['live_id'=>$live_id, 'pk_id'=>$val['pk_id'], 'competitor_id'=>$v['competitor_id']])->asArray()->one();
						$pk_popular = $user_prop::find()->select('sum(sentiment_value) as pk_popular')->where(['live_id'=>$live_id, 'pk_id'=>$val['pk_id']])->asArray()->one();
						if(!empty($pk_popular['pk_popular']) && $pk_popular['pk_popular'] != 0){
							$competitor_info[$k]['pk_percent'] = round(($data['popular']/$pk_popular['pk_popular']*2),2)*100 . '%';
						}else{
							$competitor_info[$k]['pk_percent'] = round((0.5 * 2),2)*100 . '%';
						}

						$competitor_info[$k]['popular'] = $data['popular'];
						$competitor_info[$k]['total_popular'] = $info['popular_value'];
						$competitor_info[$k]['avatar'] = $info['avatar'];
					}
				}
				$live_pk_info[$key]['competitor'] = $competitor_info;
			}
		}
	
		$this->_successData($live_pk_info);
	}
}