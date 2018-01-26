<?php
namespace frontend\controllers;

use common\models\NewsRecommend;
use common\service\Record;
use yii;
use common\models\News;
use common\models\NewsVideo;
use common\models\Live;
use common\models\Area;
use common\models\NewsColumn;
use common\models\LiveNewsRelation;

class NewslinkController extends PublicBaseController
{
	public function actionGetnewslink(){
		if(yii::$app->request->isGet || yii::$app->request->isPost){
			/* 关联新闻 */
			$news_id   = $_REQUEST['news_id'];
			$get_vote  = isset($_REQUEST['get_vote']) ? $_REQUEST['get_vote'] : 0;// ? $_REQUEST['get_vote'] : 0;

			if(!$news_id ){
				$news_id   = yii::$app->request->post('news_id', 0);
			}
			if(!$news_id){$this->_errorData('10001', '新闻ID错误');}

			/*
			 * 判断是否是新闻栏目新闻
			 * 如果是返回值加上vr的精彩推荐
			 **/
            $video_news_info = NewsColumn::find()
                ->alias('nc')
                ->select('nc.column_id, n.news_id')
                ->leftJoin(News::tableName().' n', 'nc.column_id = n.column_id')
                ->where(['nc.name'=>'视频', 'nc.type'=>'1', 'n.news_id'=>$news_id])
                ->asArray()->one();

            /* 相关新闻 */
            $list = NewsRecommend::find()->where(
                ['and',
                    ['>', 'weight', '69'],
                    ['=', 'news_id', $news_id]
                ])
                ->orderBy('weight DESC')
                ->asArray()
                ->all();
            if(!$get_vote){
                $and_select = 'news.vote_id=0';
            }else{
                $and_select = '';
            }
            $trans_field = ' ,vrnews1.news_video.video_url as video_url,vrnews1.news_video.video_url1,vrnews1.news_video.video_url2,vrnews1.news_video.width,vrnews1.news_video.width1,vrnews1.news_video.width2,vrnews1.news_video.height,vrnews1.news_video.height1,vrnews1.news_video.height2,vrnews1.news_video.size,vrnews1.news_video.size1,vrnews1.news_video.size2 ,vrnews1.news_video.`file_id` as file_id';
            $list_news_data = array();
            if($list){
                foreach($list as $news){
                    $_news_info = News::find()
                        ->select("news.news_id,news.vote_id,abstract as news_abstract,title,subtitle,content,cover_image,reference_type,reference_id,type,column_id,area_id,type_id,special_news_id,top_status,full_status,full_title,full_subtitle,full_cover_image,source_id,source_name,special_id,special_type,special_title,special_abstract,special_image,thumbnail_url,duration,play_count,category,outer_url_ishot,outer_url,external_link".$trans_field)
                        ->join('LEFT JOIN', 'news_video', 'news.news_id=news_video.news_id')
                        ->where(['news.news_id'=>$news['recommend_id'],'news.status'=>0])->andWhere($and_select)
                        ->asArray()
                        ->one();
                    //查看是否有 引用新闻
                    if(!empty($_news_info['reference_type']) && $_news_info['reference_type'] == 1 && !empty($_news_info['reference_id'])){
                        //查看 引用视频信息
                        $ref_news = NewsVideo::find()->where(" news_id = ".$_news_info['reference_id'])->asArray()->one();
                        $_news_info["thumbnail_url"]   = $ref_news['thumbnail_url'];
                        $_news_info["duration"]        = $ref_news['duration'];
                        $_news_info["play_count"]      = $ref_news['play_count'];
                        $_news_info["category"]        = $ref_news['category'];
                    }

                    if($_news_info){
                        if($_news_info['type'] == 5){
                            $_news_info['content'] = json_decode($_news_info['content'], true);
                        }else{
                            $_news_info['content'] = [];
                        }

                        if($_news_info['title']){
                            $_news_info['title'] = html_entity_decode($_news_info['title'], ENT_QUOTES);
                        }

                        $_news_info = $this->Processdata($_news_info);
                        //处理不同类型 新闻 图片大小
                        $_news_info = $this->getcheckinfo($_news_info);
                        if(empty($_news_info['content']) ||  $_news_info == '""'){
                            $_news_info['content'] = (array)$_news_info['content'];
                        }

                        if($_news_info['vote_id']){
                            $_news_info['vote_url'] = yii::$app->params['vote_url'];
                        }

                        $list_news_data[] = $_news_info;
                    }
                }
            }
            /* 相关新闻 END */

            if ($video_news_info) {
                //如果是视频栏目新闻 返回精彩推荐
                $video_recommend_list = NewsVideo::videoRecommend($video_news_info['column_id'], $news_id);
                $list = array('link_news'=>$list_news_data, 'Recommend_news'=>[], 'video_list'=>$video_recommend_list);
            } else {
                /* 推荐阅读 */
                $recommend_arr = array();
                $recommend_arr[] = $this->getFirstNewsInfoByColumnName('要闻', $get_vote);
                $recommend_arr[] = $this->getFirstNewsInfoByColumnName('本地', $get_vote);
                $recommend_arr[] = $this->getFirstNewsInfoByColumnName('跤坛', $get_vote);
                $recommend_arr[] = $this->getFirstNewsInfoByColumnName('说法', $get_vote);

                //处理不同类型 新闻 图片大小
                foreach ($recommend_arr as $k=>$v){
                    $recommend_arr[$k] = $this->getcheckinfo($v);
                    if(empty($recommend_arr[$k]['content']) ||  $recommend_arr[$k]['content'] == '""'){
                        $recommend_arr[$k]['content'] = (array)$recommend_arr[$k]['content'];
                    }
                }
                /* 推荐阅读 */
                $list = array('link_news'=>$list_news_data, 'Recommend_news'=>$recommend_arr, 'video_list'=>[]);
            }

			if($list){
				$this->_successData($list);
			}
			$this->_errorData('10002', '获取相关新闻错误');
		}
	}
	
	
	public function actionGetlivelink(){
		if(yii::$app->request->isPost || yii::$app->request->isPost){
			/* 关联新闻 */
			$news_id   = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : 0;

			if(!$news_id){$this->_errorData('10001', '新闻ID错误');}
			
			$list = LiveNewsRelation::find()->where(
					['and',
							['>', 'weight', '69'],
							['=', 'live_id', $news_id]
					])
					->orderBy('weight DESC')
					->asArray()
					->all();
			
			$list_news_data = array();
			
			$trans_field = ' ,vrnews1.news_video.video_url as video_url,vrnews1.news_video.video_url1,vrnews1.news_video.video_url2,vrnews1.news_video.width,vrnews1.news_video.width1,vrnews1.news_video.width2,vrnews1.news_video.height,vrnews1.news_video.height1,vrnews1.news_video.height2,vrnews1.news_video.size,vrnews1.news_video.size1,vrnews1.news_video.size2 ,vrnews1.news_video.`file_id` as file_id';
			
			if($list){
				foreach($list as $news){
					$list_news_data = array();
					if($list){
						foreach($list as $news){
							$_news_info = News::find()
							->select("news.news_id,abstract as news_abstract,title,subtitle,content,cover_image,reference_type,reference_id,type,column_id,area_id,type_id,special_news_id,top_status,full_status,full_title,full_subtitle,full_cover_image,source_id,source_name,special_id,special_type,special_title,special_abstract,special_image,thumbnail_url,duration,play_count,category,outer_url,external_link".$trans_field)
							->join('LEFT JOIN', 'news_video', 'news.news_id=news_video.news_id')
							->where(['news.news_id'=>$news['news_id'],'news.status'=>0])
							->asArray()
							->one();
							
							/* 生成签名 */
							$newarray = array();
							$arr['timestamp'] = time();
							$arr['app_key']   = 'app_key';
							$arr['unique']    = 'xinhuiwen,fighting!';
							reset($arr);
							while (list($key, $val) = each($arr)) {
								if ($key != "sign") {
									$newarray[$key] = $arr[$key];
								}
							}
							ksort($newarray);
							$string = "";
							while (list($key, $val) = each($newarray)) {
								$string .= $key . '=' . $val . '&';
							}
							$string = rtrim($string, '&');
							$arr['sign'] = md5($string);
							
							
							if($_news_info){
								if($_news_info['type'] == 5){
									$_news_info['content'] = json_decode($_news_info['content'], true);
									$_news_info['url']     = '/index.php?g=Details&m=NewsDetails&a=NewsPhotos&id='.$news['news_id'].'&timestamp='.$arr['timestamp'].'&app_key='.$arr['app_key'].'&unique='.$arr['unique'].'&sign='.$arr['sign'];
								}elseif($_news_info['type'] == 4){
									$_news_info['url'] = '/index.php?g=Details&m=NewsDetails&a=NewsVideo&video_id='.$news['news_id'].'&timestamp='.$arr['timestamp'].'&app_key='.$arr['app_key'].'&unique='.$arr['unique'].'&sign='.$arr['sign'];
									$_news_info['content'] = [];
								}elseif($_news_info['type'] == 3){
									$_news_info['url'] = '/index.php?g=SubscriptionNum&m=SubscriptionNum&a=Topics&id='.$news['news_id'].'&timestamp='.$arr['timestamp'].'&app_key='.$arr['app_key'].'&unique='.$arr['unique'].'&sign='.$arr['sign'].'&type=qq';
									$_news_info['content'] = [];
								}else{
									$_news_info['url'] = '/index.php?g=Details&m=NewsDetails&a=NewsText&hotid='.$news['news_id'].'&timestamp='.$arr['timestamp'].'&app_key='.$arr['app_key'].'&unique='.$arr['unique'].'&sign='.$arr['sign'];
									$_news_info['content'] = [];
								}
								if($_news_info['title']){
									$_news_info['title'] = html_entity_decode($_news_info['title'], ENT_QUOTES);
								}
								
								$_news_info        = $this->Processdata($_news_info);
								$list_news_data[] = $_news_info;
							}
						}
					}
				}
			}
			
			/* 相关新闻 */
			
			/* 推荐阅读 */
// 			$recommend_arr = array();
// 			if($this->getFirstNewsInfoByColumnName('要闻')){
// 				$recommend_arr[] = $this->getFirstNewsInfoByColumnName('要闻');
// 			}
// 			if($this->getFirstNewsInfoByColumnName('本地')){
// 				$recommend_arr[] = $this->getFirstNewsInfoByColumnName('本地');
// 			}
// 			if($this->getFirstNewsInfoByColumnName('跤坛')){
// 				$recommend_arr[] = $this->getFirstNewsInfoByColumnName('跤坛');
// 			}
// 			if($this->getFirstNewsInfoByColumnName('说法')){
// 				$recommend_arr[] = $this->getFirstNewsInfoByColumnName('说法');
// 			}
			/* 推荐阅读 */
			
// 			$list = array('link_news'=>$list_news_data);
			
			if($list){
				$this->_successData($list_news_data);
			}
			$this->_errorData('10002', '获取相关新闻错误');
		}
	}
	
	
	/* 获取前四栏目一条新闻 */
	private function getFirstNewsInfoByColumnName($column_name = null, $get_vote=0){
		$model = new News();
		$news_info = '';

		if($column_name){
			if($column_name == '本地'){
				$area_model = new Area();
                $ret = $area_model::find()->where(['name'=>'北京'])->select('area_id')->asArray()->one();
				$andwhere = 'news.area_id = '.$ret['area_id'];
			}else{
				$ret = NewsColumn::find()->where(['name'=>$column_name])->select('column_id')->asArray()->one();
				$andwhere = 'news.column_id = '.$ret['column_id'];
			}

			if(!$get_vote){
                $andwhere .= ' and vrnews1.news.vote_id=0 ';
            }

			if($ret){
				/*$news_info = $model::find()
				->leftJoin('news_video', 'news.news_id = news_video.news_id')
				->where($andwhere." AND news.weight >= 70 and news.type in (1, 3, 4, 5, 6, 7, 8) and news.top_status = 0 and news.special_news_id = 0 and news.status=0")
				->select([
						"news.news_id",
						"abstract as news_abstract",
						"title",
						"subtitle",
						"content",
						"cover_image",
                        "vote_id",
						"reference_type",
						"reference_id",
						"type",
						"column_id",
						"area_id",
						"DATE_FORMAT(create_time,'%Y/%m/%d %H:%i:%s') as create_time",
						"type_id",
						"special_news_id",
						"top_status",
						"full_status",
						"full_title",
						"full_subtitle",
						"full_cover_image",
						"source_id",
						"source_name",
						"special_id",
						"special_type",
						"special_title",
						"special_abstract",
						"special_image",
						"thumbnail_url",
						"duration",
						"play_count",
						"category",
						"outer_url_ishot",
						"outer_url",
						"external_link",
                        "vrnews1.news_video.video_url as video_url",
                        "vrnews1.news_video.video_url1",
                        "vrnews1.news_video.video_url2",
                        "vrnews1.news_video.width",
                        "vrnews1.news_video.width1",
                        "vrnews1.news_video.width2",
                        "vrnews1.news_video.height",
                        "vrnews1.news_video.height1",
                        "vrnews1.news_video.height2",
                        "vrnews1.news_video.size",
                        "vrnews1.news_video.size1",
                        "vrnews1.news_video.size2",
                        "vrnews1.news_video.`file_id` as file_id"
						])
				->orderBy([
							"case when `refresh_time` is null then year(create_time) else from_unixtime(refresh_time, '%Y')  end" => SORT_DESC,
							"case when `refresh_time` is null then month(create_time) else from_unixtime(refresh_time, '%m') end"    => SORT_DESC,
							"case when `refresh_time` is null then day(create_time) else from_unixtime(refresh_time, '%d') end" => SORT_DESC,
							'top_status' => SORT_DESC,
							'weight'     => SORT_DESC,
							'refresh_time' => SORT_DESC,
							'create_time' => SORT_DESC,
						]
				)
				->asArray()
				->one();*/

                $news_info = yii::$app->db->createCommand("
                                                          SELECT 
                                                           `news`.`news_id`, `abstract` AS `news_abstract`, `title`, `subtitle`, `content`, `cover_image`, `vote_id`, 
                                                           `reference_type`, `reference_id`, `type`, `column_id`, `area_id`, DATE_FORMAT(create_time,'%Y/%m/%d %H:%i:%s') as create_time, 
                                                           `type_id`, `special_news_id`, `top_status`, `full_status`, `full_title`, `full_subtitle`, `full_cover_image`, `source_id`, 
                                                           `source_name`, `special_id`, `special_type`, `special_title`, `special_abstract`, `special_image`, `thumbnail_url`, 
                                                           `duration`, `play_count`, `category`, `outer_url_ishot`, `outer_url`, `external_link`, 
                                                           `vrnews1`.`news_video`.`video_url` AS `video_url`, `vrnews1`.`news_video`.`video_url1`, `vrnews1`.`news_video`.`video_url2`, 
                                                           `vrnews1`.`news_video`.`width`, `vrnews1`.`news_video`.`width1`, `vrnews1`.`news_video`.`width2`, `vrnews1`.`news_video`.`height`, 
                                                           `vrnews1`.`news_video`.`height1`, `vrnews1`.`news_video`.`height2`, `vrnews1`.`news_video`.`size`, `vrnews1`.`news_video`.`size1`, 
                                                           `vrnews1`.`news_video`.`size2`, `vrnews1`.`news_video`.`file_id` AS `file_id` 
                                                          FROM 
                                                           `vrnews1`.`news` 
                                                          LEFT JOIN `vrnews1`.`news_video` ON news.news_id = news_video.news_id 
                                                          WHERE 
                                                            {$andwhere}  AND news.weight >= 70 and news.type in (1, 3, 4, 5, 6, 7, 8) 
                                                            and news.top_status = 0 and news.special_news_id = 0 and news.status=0 
                                                          ORDER BY 
                                                           case when `refresh_time` is null then year(create_time) else from_unixtime(refresh_time, '%Y')  end DESC, 
                                                           case when `refresh_time` is null then month(create_time) else from_unixtime(refresh_time, '%m') end DESC, 
                                                           case when `refresh_time` is null then day(create_time) else from_unixtime(refresh_time, '%d') end DESC, 
                                                           `top_status` DESC, `weight` DESC, `refresh_time` DESC, `create_time` DESC
                                                       ")->queryOne();

				//查看是否有 引用新闻
				if(!empty($news_info['reference_type']) && $news_info['reference_type'] == 1 && !empty($news_info['reference_id'])){
					//查看 引用视频信息
					$ref_news = NewsVideo::find()->where(" news_id = ".$news_info['reference_id'])->asArray()->one();
					$news_info["thumbnail_url"]   = $ref_news['thumbnail_url'];
					$news_info["duration"]        = $ref_news['duration'];
					$news_info["play_count"]      = $ref_news['play_count'];
					$news_info["category"]        = $ref_news['category'];
				}

				if($news_info['type'] == 5){
					$news_info['content'] = json_decode($news_info['content']);
				}else{
					$news_info['content'] = [];
				}

				if($news_info['vote_id']){
                    $news_info['vote_url'] = yii::$app->params['vote_url'];
                }
				
				if($news_info['title']){
					$news_info['title'] = html_entity_decode($news_info['title'], ENT_QUOTES);
				}
				
				$news_info = $this->Processdata($news_info);
			}
		}

		return $news_info;
	}
	
	/**
	 *  热门资讯
	 */
	public function actionGetHotNews(){
		/* $list = NewsColumn::find()
			->where('status = 1 AND weight >= 70')
			->asArray()
			->all();

		if(!$list){
			return '';
		}

		$c_list = array();
		foreach($list as $key=>$val){
			if($val['type'] == 2){
				$c_list = Area::find()->where('1')->asArray()->all();
			}else{
				$c_list[] = $val;
			}
		}
		array_unshift($c_list, $list[0]); */
		/*  */
		$c_list = array(
				array('column_id'=>1, 'name'=>'要闻', 'type'=>1, 'status'=>1, 'live_status'=>1),
				array('area_id'=>1,   'name'=>'北京', 'type'=>1, 'status'=>1, 'live_status'=>1),
				array('area_id'=>2, 'name'=>'石家庄', 'type'=>1, 'status'=>1, 'live_status'=>1),
				array('area_id'=>3, 'name'=>'保定', 'type'=>1, 'status'=>1, 'live_status'=>1),
				array('column_id'=>3, 'name'=>'跤坛', 'type'=>1, 'status'=>1, 'live_status'=>1),
				array('column_id'=>4, 'name'=>'说法', 'type'=>1, 'status'=>1, 'live_status'=>1),
				array('column_id'=>10, 'name'=>'城市管理', 'type'=>1, 'status'=>1, 'live_status'=>1),
				array('column_id'=>6, 'name'=>'公司', 'type'=>1, 'status'=>1, 'live_status'=>1),
				array('column_id'=>7, 'name'=>'教育', 'type'=>1, 'status'=>1, 'live_status'=>1),
				array('column_id'=>5, 'name'=>'公益', 'type'=>1, 'status'=>1, 'live_status'=>1),
				array('column_id'=>8, 'name'=>'生活', 'type'=>1, 'status'=>1, 'live_status'=>1),
		);

		
		$news_list = array();
		//$c_list = array(array('area_id'=> 1, 'name'=>'北京'));
		foreach($c_list as $key=>$val){
			if(isset($val['area_id'])){
//				$redis = Yii::$app->cache;
//				$update = Yii::$app->params['environment'] . "_hotnews_1_" . $val['area_id'];
//				$redis_info = $redis->get($update);
//				if($redis_info){
//					$news_list[$val['name']] = $redis_info;
//				}else{

					$news_list[$val['name']] = $this->getOneNews(0, $val['area_id']);
//				}
			}else if(isset($val['column_id'])){
//				$redis = Yii::$app->cache;
//				$update = Yii::$app->params['environment'] . "_hotnews_0_" . $val['column_id'];
//				$redis_info = $redis->get($update);
//				if($redis_info){
//					$news_list[$val['name']] = $redis_info;
//				}else {
					$news_list[$val['name']] = $this->getOneNews($val['column_id'], 0);
//				}
			}
		}

		if($news_list){
			$this->_successData($news_list);
		}
		$this->_errorData('131', 'error');
	}
	
	 private function getOneNews($cid, $aid){
		$model = new News();
		$news_info = '';
		if($cid){
			$andwhere = 'news.column_id = '.$cid;
		}else{
			$andwhere = 'news.area_id = '.$aid;
		}
		$trans_field = ' ,vrnews1.news_video.video_url as video_url,vrnews1.news_video.video_url1,vrnews1.news_video.video_url2,vrnews1.news_video.width,vrnews1.news_video.width1,vrnews1.news_video.width2,vrnews1.news_video.height,vrnews1.news_video.height1,vrnews1.news_video.height2,vrnews1.news_video.size,vrnews1.news_video.size1,vrnews1.news_video.size2 ,vrnews1.news_video.`file_id` as file_id';
		$news_info = $model::find()
		->join('LEFT JOIN', 'news_video', 'news.news_id = news_video.news_id')
		->where("news.weight >= 70 and news.type not in (2,9,10,11,12,13,14) and news.top_status = 0 and news.special_news_id = 0 and news.status = 0 and ".$andwhere)
		->select([
				"news.news_id",
				"abstract as news_abstract",
				"title",
				"subtitle",
				"content",
				"cover_image",
				"reference_type",
				"reference_id",
				"type",
				"column_id",
				"area_id",
				"vote_id",
				"DATE_FORMAT(create_time,'%Y/%m/%d %H:%i:%s') as create_time",
				"type_id",
				"special_news_id",
				"top_status",
				"full_status",
				"full_title",
				"full_subtitle",
				"full_cover_image",
				"source_id",
				"source_name",
				"special_id",
				"special_type",
				"special_title",
				"special_abstract",
				"special_image",
				"thumbnail_url",
				"duration",
				"play_count",
				"category",
				"outer_url_ishot",
				"outer_url",
				"external_link",
				"year(create_time) as year1",
				"month(create_time) as month1",
				"day(create_time) as day1",
				"year(from_unixtime(refresh_time)) as year",
				"month(from_unixtime(refresh_time)) as month",
				"day(from_unixtime(refresh_time)) as day",
				"from_unixtime(refresh_time) as refresh_time".$trans_field
		])
		->orderBy([
				'case when `year` is null then `year1` else `year` end' => SORT_DESC,
				'case when `month` is null then `month1` else `month` end'    => SORT_DESC,
				'case when `day` is null then `day1` else `day` end' 			=> SORT_DESC,
				'top_status' => SORT_DESC,
				'weight'=>SORT_DESC,
				'refresh_time' => SORT_DESC,
				'create_time' => SORT_DESC,
		])
		->asArray()
		->one();

		 if(!$news_info){return $news_info;}
		if($news_info['type'] == 5){
			$news_info['content'] = json_decode($news_info['content']);
		}else{
			$news_info['content'] = [];
		}

		if($news_info['title']){
			$news_info['title'] = html_entity_decode($news_info['title'], ENT_QUOTES);
		}

		if($news_info['vote_id']){
			//如果是投票 增加url字段跳转
			$news_info['vote_url'] = yii::$app->params['vote_url'].'?vote_id='.$news_info['vote_id'];
		}

		$news_info = $this->Processdata($news_info);
		return $news_info;
	}
	
	private function Processdata($news_info){
		//处理  返回值
		if($news_info['video_url']){
			unset($news_info['video_url1']);
			unset($news_info['video_url2']);
		}else if($news_info['video_url1']){
			$news_info['video_url'] = $news_info['video_url1'];
			unset($news_info['video_url1']);
			unset($news_info['video_url2']);
		}else if($news_info['video_url2']){
			$news_info['video_url'] = $news_info['video_url2'];
			unset($news_info['video_url1']);
			unset($news_info['video_url2']);
		}else{
			unset($news_info['video_url1']);
			unset($news_info['video_url2']);
		}
		if($news_info['height']){
			unset($news_info['height1']);
			unset($news_info['height2']);
		}else if($news_info['height1']){
			$news_info['height'] = $news_info['height1'];
			unset($news_info['height1']);
			unset($news_info['height2']);
		}else if($news_info['height2']){
			$news_info['height'] = $news_info['height2'];
			unset($news_info['height1']);
			unset($news_info['height2']);
		}else{
			unset($news_info['height1']);
			unset($news_info['height2']);
		}
		if($news_info['width']){
			unset($news_info['width1']);
			unset($news_info['width2']);
		}else if($news_info['width1']){
			$news_info['width'] = $news_info['width1'];
			unset($news_info['width1']);
			unset($news_info['width2']);
		}else if($news_info['width2']){
			$news_info['width'] = $news_info['width2'];
			unset($news_info['width1']);
			unset($news_info['width2']);
		}else{
			unset($news_info['width1']);
			unset($news_info['width2']);
		}
		if($news_info['size']){
			unset($news_info['size1']);
			unset($news_info['size2']);
		}else if($news_info['size1']){
			$news_info['size'] = $news_info['size1'];
			unset($news_info['size1']);
			unset($news_info['size2']);
		}else if($news_info['size2']){
			$news_info['size'] = $news_info['size2'];
			unset($news_info['size1']);
			unset($news_info['size2']);
		}else{
			unset($news_info['size1']);
			unset($news_info['size2']);
		}
		
		return $news_info;
	}
	private function getcheckinfo($value){
		$live_type = array(0=>'9',1=>'10',2=>'11',3=>'12',4=>'13',5=>'14');
		if($value && count($value) > 0){
			if ($value['type'] == '3') { //专题
				$value['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
			} else if ($value['type'] == '4') { //视频
				$value['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/206x142!' : '';
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
			} else if ($value['type'] == '5' ) { //图集
//			$value['content'] = json_decode($value['content']);
				if(!empty($value['reference_type']) && intval($value['reference_type']) == 1 && !empty($value['reference_id'])){
					//查看 被引用图集信息
					$ref_news = News::find()->where(['news_id'=>$value['reference_id']])->asArray()->one();
					if(!empty($ref_news['content'])){
						$ref_news['content'] = json_decode($ref_news['content']);
						foreach ($ref_news['content'] as $re_k=>$re_v){
							if($re_k < 3) {
								if(is_object($ref_news['content'][$re_k])){
									$str_con = substr($ref_news['content'][$re_k]->img,-2);
									if($str_con == '/s'){
										$value['content'][$re_k]->img = substr($ref_news['content'][$re_k]->img,0,-2);
									}
									if($value['content']=='""'){
										$tmp = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/224x150!' : '';
										$value['content'] = array($re_k=>array('img'=>$tmp));
									}else{
										$value['content'][$re_k]['img'] = $re_v->img ? $re_v->img . '?imageMogr2/thumbnail/224x150!' : '';
									}
								}else {
									$str_con = substr($ref_news['content'][$re_k]['img'],-2);
									if($str_con == '/s'){
										$value['content'][$re_k]['img'] = substr($ref_news['content'][$re_k]['img'],0,-2);
									}
									$value['content'][$re_k]['img'] = $re_v['img'] ? $re_v['img'] . '?imageMogr2/thumbnail/224x150!' : '';
								}
							}
						}
					}else{
						$value['content'] = array();
					}
				}else {
					if (!empty($value['content'])) {
						foreach ($value['content'] as $k => $v) {
							if ($k < 3) {
								if (is_object($value['content'][$k])) {
									$str_con = substr($value['content'][$k]->img, -2);
									if ($str_con == '/s') {
										$value['content'][$k]->img = substr($value['content'][$k]->img, 0, -2);
									}
									$value['content'][$k]->img = $v->img ? $v->img . '?imageMogr2/thumbnail/224x150!' : '';
								} else {
									$str_con = substr($value['content'][$k]['img'], -2);
									if ($str_con == '/s') {
										$value['content'][$k]['img'] = substr($value['content'][$k]['img'], 0, -2);
									}
									$value['content'][$k]['img'] = $v['img'] ? $v['img'] . '?imageMogr2/thumbnail/224x150!' : '';
								}

							}
						}
					}else{
						$value['content'] = array();
					}
				}
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
			} else if ($value['type'] == '7') { //图文
				$value['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/206x142!' : '';
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
			} else if (in_array($value['type'], $live_type)) { //直播类型新闻
				$value['cover_image'] = $value['cover_image'] ? $value['cover_image'] . '?imageMogr2/thumbnail/710x340!' : '';
				$value['full_cover_image'] = $value['full_cover_image'] ? $value['full_cover_image'] . '?imageMogr2/thumbnail/710x236!' : '';
			}
		}
		return $value;
	}
	
}