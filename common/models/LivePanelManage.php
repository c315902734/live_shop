<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "live_panel_manage".
 *
 * @property string $id
 * @property string $live_id
 * @property string $create_time
 * @property string $update_time
 * @property string $pic_txt_content
 * @property integer $content_type
 * @property string $onlist_user_id
 * @property string $onlist_nickname
 * @property string $user_speak_content
 * @property string $user_speak_time
 * @property integer $sort_number
 */
class LivePanelManage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'live_panel_manage';
    }

    
    public static function getDb()
    {
    	return yii::$app->vrlive;
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['live_id'], 'required'],
            [['live_id', 'content_type', 'onlist_user_id', 'sort_number'], 'integer'],
            [['create_time', 'update_time', 'user_speak_time'], 'safe'],
            [['pic_txt_content', 'user_speak_content'], 'string'],
            [['onlist_nickname'], 'string', 'max' => 60],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'live_id' => 'Live ID',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'pic_txt_content' => 'Pic Txt Content',
            'content_type' => 'Content Type',
            'onlist_user_id' => 'Onlist User ID',
            'onlist_nickname' => 'Onlist Nickname',
            'user_speak_content' => 'User Speak Content',
            'user_speak_time' => 'User Speak Time',
            'sort_number' => 'Sort Number',
        ];
    }
    
    public static function GetTopAndOnList($live_id = null, $resource_type = 'array'){
    	if($resource_type == 'object'){
    		$init_top_data = (object)array();
    	}else{
    		$init_top_data = array();
    	}
    	$returnData = array('top_data'=>$init_top_data,'onlist_data'=>array());
    	if($live_id){
    		$model = new self();
    		//获取置顶信息
    		$top_data = $model::find()->select(['id','json_data','is_top','content_type'])->where(['live_id'=>$live_id,'is_top'=>'1'])->one();
    		if($top_data){
    			$first['msg_id'] = $top_data->id;
    			$first['is_top'] = $top_data->is_top;
    			$first['is_onlist'] = $top_data->content_type == '2' ? '1' : '0';
    			
    			$top_json_data_arr =  json_decode($top_data->json_data,TRUE);
    			if(isset($top_json_data_arr['images_url'])){
    				if($top_json_data_arr['images_url']){
    					$new_image_data = array();
    					foreach($top_json_data_arr['images_url'] as $key=>$value){
    						$new_image_data[$key]['thumbnail_image_url'] = $value.'/e';
    						$new_image_data[$key]['original_image_url'] = $value;
    					}
    					$top_json_data_arr['images_url'] = $new_image_data;
    					$top_json_data_arr['images_data'] = $new_image_data;
    				}
    			}
    			if(!empty($top_json_data_arr['on_list_user_info']) && count($top_json_data_arr['on_list_user_info']) > 0){
					$top_json_data_arr['on_list_user_info']['user_username'] = self::userTextDecode($top_json_data_arr['on_list_user_info']['user_username']);
					$top_json_data_arr['on_list_user_info']['user_content'] = self::userTextDecode($top_json_data_arr['on_list_user_info']['user_content']);
				}
    			$first['content'] = self::userTextDecode($top_json_data_arr['content']);
    			$first['compere_id'] = $top_json_data_arr['compere_id'];
    			$first['compere_name'] = $top_json_data_arr['compere_name'];
				$first['compere_cate'] = isset($top_json_data_arr['compere_cate']) ? $top_json_data_arr['compere_cate'] : "主持人"; //直播员类别
    			$first['compere_avatar'] = $top_json_data_arr['compere_avatar'];
    			$first['images_data'] = isset($top_json_data_arr['images_data']) ? $top_json_data_arr['images_data'] : array();
    			$first['videos_data'] = isset($top_json_data_arr['videos_data']) ? $top_json_data_arr['videos_data'] : array();
    			$first['images_url'] = isset($top_json_data_arr['images_url']) ? $top_json_data_arr['images_url'] : array();
    			$first['videos_url'] = isset($top_json_data_arr['videos_url']) ? $top_json_data_arr['videos_url'] : array();
    			$first['video_thumbnails'] = isset($top_json_data_arr['video_thumbnails']) ? $top_json_data_arr['video_thumbnails'] : array();
    			$first['on_list_user_info'] = $top_json_data_arr['on_list_user_info'] ? $top_json_data_arr['on_list_user_info'] : array();
    			$first['date_time'] = $top_json_data_arr['date_time'];

    			$returnData['top_data'] = $first;
    		}
    		//获取上榜信息
    		$onlist_data = $model::find()->select(['id','json_data','is_top','content_type'])->where(['live_id'=>$live_id,'is_top'=>'0','content_type'=>'2'])->orderBy(['create_time'=>SORT_DESC])->all();
    		if($onlist_data){
    			$other = array();
    			foreach($onlist_data as $key=>$value){
    				$other[$key]['msg_id'] = $value->id;
    				$other[$key]['is_top'] = $value->is_top;
    				$other[$key]['is_onlist'] = $value->content_type == '2' ? '1' : '0';
    				
    				$other_json_data_arr =  json_decode($value->json_data,TRUE);
    				if(isset($other_json_data_arr['images_url'])){
    					if($other_json_data_arr['images_url']){
    						$new_image_data = array();
    						foreach($other_json_data_arr['images_url'] as $key1=>$value1){
    							$new_image_data[$key1]['thumbnail_image_url'] = $value1.'/e';
    							$new_image_data[$key1]['original_image_url'] = $value1;
    						}
    						$other_json_data_arr['images_url'] = $new_image_data;
    						$other_json_data_arr['images_data'] = $new_image_data;
    					}
    				}
					if(!empty($other_json_data_arr['on_list_user_info']) && count($other_json_data_arr['on_list_user_info']) > 0){
						$other_json_data_arr['on_list_user_info']['user_username'] = self::userTextDecode($other_json_data_arr['on_list_user_info']['user_username']);
						$other_json_data_arr['on_list_user_info']['user_content'] = self::userTextDecode($other_json_data_arr['on_list_user_info']['user_content']);
					}
    				$other[$key]['content'] = self::userTextDecode($other_json_data_arr['content']);
    				$other[$key]['compere_id'] = $other_json_data_arr['compere_id'];
    				$other[$key]['compere_name'] = $other_json_data_arr['compere_name'];
					$other[$key]['compere_cate'] = isset($other_json_data_arr['compere_cate']) ? $other_json_data_arr['compere_cate'] : "主持人"; //直播员类别
    				$other[$key]['compere_avatar'] = $other_json_data_arr['compere_avatar'];
    				$other[$key]['images_data'] = isset($other_json_data_arr['images_data']) ? $other_json_data_arr['images_data'] : array();
    				$other[$key]['videos_data'] = isset($other_json_data_arr['videos_data']) ? $other_json_data_arr['videos_data'] : array();
    				$other[$key]['images_url'] = isset($other_json_data_arr['images_url']) ? $other_json_data_arr['images_url'] : array();
    				$other[$key]['videos_url'] = isset($other_json_data_arr['videos_url']) ? $other_json_data_arr['videos_url'] : array();
    				$other[$key]['video_thumbnails'] = isset($other_json_data_arr['video_thumbnails']) ? $other_json_data_arr['video_thumbnails'] : array();
    				$other[$key]['on_list_user_info'] = $other_json_data_arr['on_list_user_info'] ? $other_json_data_arr['on_list_user_info'] : array();
    				$other[$key]['date_time'] = $other_json_data_arr['date_time'];
    			}
    			$returnData['onlist_data'] = $other;
    		}
    	}
    	return $returnData;
    }
    
    
    public static function SetTop($live_id = null, $msg_id = null){
    	if($live_id && $msg_id){
    		$model = new self();
    		$data = $model::find()->where(['id'=>$msg_id])->one();
    		$data->is_top = '1';
    		$data->update_time = date('Y-m-d H:i:s',time());
    		if($data->save()){
    			LivePanelManage::updateAll(['is_top'=>'0','update_time'=>date('Y-m-d H:i:s',time())],"live_id = $live_id and  id not in ($msg_id)");
    			return true;
    		}
    	}
    	return false;
    }
    
    public static function DelMsg($msg_id = null){
    	if($msg_id){
    		if(LivePanelManage::deleteAll('id = :id', [':id' => $msg_id])){
    			return true;
    		}
    	}
    	return false;
    }
    
    
    public static function GetOnlistByUserByUserId($user_id = null, $pageStart, $pageEnd){
    	$returnData = array();
    	if($user_id){
    		$model = new self();
    		$data = $model::find()->select(['live_id','json_data'])->where(['onlist_user_id'=>$user_id])->asArray()->all();
    		if($data){
    			$i = 0;
    			foreach($data as $key=>$value){
    				if($key>=$pageStart && $key < $pageEnd){
    					$returnData[$i]['live_id'] = $value['live_id'];
	    				$json_data_arr = json_decode($value['json_data'],TRUE);
	    				
	    				if(isset($json_data_arr['images_url'])){
	    					if($json_data_arr['images_url']){
	    						$new_image_data = array();
	    						foreach($json_data_arr['images_url'] as $key1=>$value1){
	    							$new_image_data[$key1]['thumbnail_image_url'] = $value1.'/e';
	    							$new_image_data[$key1]['original_image_url'] = $value1;
	    						}
	    						$json_data_arr['images_url'] = $new_image_data;
	    						$json_data_arr['images_data'] = $new_image_data;
	    					}
	    				}
	    				
	    				$json_data_arr['extra']['compere']['content'] = self::userTextDecode($json_data_arr['content']);
	    				$json_data_arr['extra']['compere']['date_time'] = $json_data_arr['date_time'];
	    				$returnData[$i]['compere_id'] = $json_data_arr['compere_id'];
	    				$returnData[$i]['compere_name'] = $json_data_arr['compere_name'];
	    				$returnData[$i]['compere_avatar'] = $json_data_arr['compere_avatar'];
	    				$returnData[$i]['compere_content'] = $json_data_arr['compere_content'];
	    				$returnData[$i]['compere_date_time'] = $json_data_arr['date_time'];
	    				$returnData[$i]['onlist_user_username'] = $json_data_arr['on_list_user_info']['user_username'];
	    				$returnData[$i]['onlist_user_id'] = $json_data_arr['on_list_user_info']['user_id'];
	    				$returnData[$i]['onlist_user_content'] = $json_data_arr['on_list_user_info']['user_content'];
	    				$returnData[$i]['onlist_msg_time'] = $json_data_arr['on_list_user_info']['msg_time'];
	    				$returnData[$i]['onlist_msg_date'] = $json_data_arr['on_list_user_info']['msg_date'];
	    				$i ++;
    				}
    			}
    		}
    	}
    	return $returnData;
    }

    public static function GetMessageList($live_id = null, $last_id = 0, $pageStart = 0, $pageEnd = null){
    	
    	$returnData = array();
    	if($live_id){
    		$model = new self();
    		$andwhere = '';
    		if($last_id) $andwhere .= 'and id < '.$last_id;
    		//获取置顶信息
    		$data = $model::find()->select(['id','json_data','is_top','content_type'])
    		->where(['live_id'=>$live_id])
    		->andWhere("is_top !=1 and content_type = 1 $andwhere")
    		->orderBy(['id'=>SORT_DESC])
    		->offset($pageStart)
    		->limit($pageEnd-$pageStart)
    		->asArray()->all();
    		if($data){
    			foreach($data as $key=>$value){
    				$json_data_arr = json_decode($value['json_data'],TRUE);
    				if(isset($json_data_arr['images_url'])){
    					if($json_data_arr['images_url']){
    						$new_image_data = array();
    						foreach($json_data_arr['images_url'] as $key1=>$value1){
    							$new_image_data[$key1]['thumbnail_image_url'] = $value1.'/e';
    							$new_image_data[$key1]['original_image_url'] = $value1;
    						}
    						$json_data_arr['images_url'] = $new_image_data;
    						$json_data_arr['images_data'] = $new_image_data;
    					}
    				}

    				$returnData[$key]['msg_id'] = $value['id'];
    				$returnData[$key]['content'] = self::filter_words(self::userTextDecode($json_data_arr['content']));
    				$returnData[$key]['compere_id'] = $json_data_arr['compere_id'];
    				$returnData[$key]['compere_name'] = $json_data_arr['compere_name'];
    				$returnData[$key]['compere_avatar'] = $json_data_arr['compere_avatar'];
					$returnData[$key]['compere_cate']   = isset($json_data_arr['compere_cate']) ? $json_data_arr['compere_cate'] : "主持人"; //直播员类别
    				$returnData[$key]['images_data'] = isset($json_data_arr['images_data']) ? $json_data_arr['images_data'] : array();
    				$returnData[$key]['videos_data'] = isset($json_data_arr['videos_data']) ? $json_data_arr['videos_data'] : array();
    				$returnData[$key]['images_url'] = isset($json_data_arr['images_url']) ? $json_data_arr['images_url'] : array();
    				$returnData[$key]['videos_url'] = isset($json_data_arr['videos_url']) ? $json_data_arr['videos_url'] : array();
    				$returnData[$key]['video_thumbnails'] = isset($json_data_arr['video_thumbnails']) ? $json_data_arr['video_thumbnails'] : array();
    				$returnData[$key]['on_list_user_info'] = $json_data_arr['on_list_user_info'] ? $json_data_arr['on_list_user_info']: array();
    				$returnData[$key]['date_time'] = $json_data_arr['date_time'];
    			}
    		}
    	}
    	return $returnData; 
    }
    
    
    public static function   GetNewMessage($live_id = null, $first_id = 0, $pageStart = 0, $pageEnd = null){
    	 
    	$returnData = array();
    	if($live_id){
    		$model = new self();
    		$andwhere = '';
    		if($first_id) $andwhere .= 'and id > '.$first_id;
    		//获取置顶信息
    		$data = $model::find()->select(['id','json_data','is_top','content_type'])
    		->where(['live_id'=>$live_id])
    		->andWhere("is_top !=1 and content_type = 1 $andwhere")
    		->orderBy(['id'=>SORT_DESC])
    		->offset($pageStart)
    		->limit($pageEnd-$pageStart)
    		->asArray()->all();
    		if($data){
    			foreach($data as $key=>$value){
    				$json_data_arr = json_decode($value['json_data'],TRUE);
    				if(isset($json_data_arr['images_url'])){
    					if($json_data_arr['images_url']){
    						$new_image_data = array();
	    					foreach($json_data_arr['images_url'] as $key1=>$value1){
	    						$new_image_data[$key1]['thumbnail_image_url'] = $value1.'/e';
	    						$new_image_data[$key1]['original_image_url'] = $value1;
	    					}
	    					$json_data_arr['images_url'] = $new_image_data;
	    					$json_data_arr['images_data'] = $new_image_data;
    					}
    				}
    				
    				$returnData[$key]['msg_id'] = $value['id'];
    				$returnData[$key]['content'] = self::filter_words(self::userTextDecode($json_data_arr['content']));
    				$returnData[$key]['compere_id'] = $json_data_arr['compere_id'];
					$returnData[$key]['compere_cate'] = $json_data_arr['compere_cate'];
    				$returnData[$key]['compere_name'] = $json_data_arr['compere_name'];
    				$returnData[$key]['compere_avatar'] = $json_data_arr['compere_avatar'];
    				$returnData[$key]['images_data'] = isset($json_data_arr['images_data']) ? $json_data_arr['images_data'] : array();
    				$returnData[$key]['videos_data'] = isset($json_data_arr['videos_data']) ? $json_data_arr['videos_data'] : array();
    				$returnData[$key]['images_url'] = isset($json_data_arr['images_url']) ? $json_data_arr['images_url'] : array();

    				$returnData[$key]['videos_url'] = isset($json_data_arr['videos_url']) ? $json_data_arr['videos_url'] : array();
    				$returnData[$key]['video_thumbnails'] = isset($json_data_arr['video_thumbnails']) ? $json_data_arr['video_thumbnails'] : array();
    				$returnData[$key]['on_list_user_info'] = $json_data_arr['on_list_user_info'] ? $json_data_arr['on_list_user_info'] : array();
    				$returnData[$key]['date_time'] = $json_data_arr['date_time'];
    			}
    		}
    	}
    	return $returnData;
    }
    
    public static function UserSendMsg($live_id = NULL, $user_id = '0', $content = NULL, $awarded_name = NULL, $gift = NULL, $gift_count = 0){
    	$returnData = array();
    	if($live_id){
    		//查询用户信息
    		$user_info = User1::find()->where(['user_id'=>$user_id,'status'=>'1'])->one();
    		
    		$model = new LivePanelUserManage();
    		$model->live_id = $live_id;
    		$model->create_time = date('Y-m-d H:i:s',time());
    		$model->create_timestamp = self::msectime();

    		if($awarded_name && $gift && $gift_count){
    			$content = $user_info->nickname.'给'.$awarded_name.'赠送了'.$gift_count.'个'.$gift;
    		}
    		$model->pic_txt_content = self::userTextEncode($content);
			
    		
    		if($user_info){
    			$model->creator_id = $user_id;
    			$model->creator_name = $user_info->username;
    			$model->creator_nickname = $user_info->nickname;
    			$model->creator_avatar = $user_info->avatar;
    			if($model->save()){
    				//发送融云消息
    				$nonce = mt_rand();
    				$timeStamp = time();
    				$sign = sha1(Yii::$app->params['ryAppSecret'] . $nonce . $timeStamp);
    				$header = array(
    						'RC-App-Key:' . Yii::$app->params['ryAppKey'],
    						'RC-Nonce:' . $nonce,
    						'RC-Timestamp:' . $timeStamp,
    						'RC-Signature:' . $sign,
    				);
    				$msg_content = array(
							"content"	=>$content,
							"extra"		=>1,
	    					"user"	    =>array(
	    							"name"	=>$user_info->nickname,       //打赏人昵称
	    							"user_id"	=>$user_id,                              //打赏人id
	    							"icon"      =>$user_info->avatar             //打赏人头像
	    					)
					); 
    				$msg_content = json_encode($msg_content);
    				$data = "content=$msg_content&fromUserId=$user_id&toChatroomId=ptroom_$live_id&objectName=RC:TxtMsg";
    				
    				if(self::curl_http(Yii::$app->params['ryApiUrl']. '/message/chatroom/publish.json', $data, $header)){
    					$returnData['msg_id'] = $model->id;
    					return $returnData;
    				}
	
    			}
    		}
    	}
    	return false;
    }
    
    
    public static  function  curl_http($url, $post_data = '', $header=array(), $timeout=30){
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
    	curl_setopt($ch, CURLOPT_URL, $url);
    	$header[] = 'Content-Type:application/x-www-form-urlencoded';
    	$header[] = 'Accept-Charset: utf-8';
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    	if(!empty($post_data)){
    		curl_setopt($ch, CURLOPT_POST, true);
    		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    	}
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    	curl_setopt($ch, CURLOPT_HEADER, false);
    	curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
    	$response = curl_exec($ch);
    
    	if($error = curl_error($ch)){
    		die($error);
    	}
    
    	curl_close($ch);
    
    	return json_decode($response, true);
    }
    
    public static function UserGetMessageList($live_id = null, $last_id = 0, $pageStart = 0, $pageEnd = null){
    	 $keyword_arr = array(
    	 		'鲜花',
    	 		'跤靴',
    	 		'训练跤衣',
    	 		'竞技跤衣',
    	 		'至尊金跤衣'
    	 );
    	$returnData = array();
    	if($live_id){
    		$model = new LivePanelUserManage();
    		$andwhere = '';
    		if($last_id) $andwhere .= 'and id < '.$last_id;
    		//获取置顶信息
    		$data = $model::find()->select(['id','create_time','pic_txt_content','create_timestamp','creator_id','creator_name','creator_nickname','creator_avatar'])
    		->where("live_id = $live_id $andwhere")
    		->orderBy(['id'=>SORT_DESC])
    		->offset($pageStart)
    		->limit($pageEnd-$pageStart)
    		->asArray()->all();
    		if($data){
    			foreach($data as $key=>$value){
    				$returnData[$key]['msg_id'] = $value['id'];
    				$returnData[$key]['content'] = self::filter_words($value['pic_txt_content']);
    				$returnData[$key]['create_time'] = $value['create_time'];
    				$returnData[$key]['create_timestamp'] = $value['create_timestamp'];
    				$returnData[$key]['real_time'] = self::getRealTime($value['create_time']);
    				$returnData[$key]['pic_txt_content'] = self::filter_words(self::userTextDecode($value['pic_txt_content']));
    				$returnData[$key]['is_system_msg'] = '0';
    				
    				foreach($keyword_arr as $keyword_key=>$keyword_value){
    					if(strpos(self::userTextDecode($value['pic_txt_content']),$keyword_value)){
    						$returnData[$key]['is_system_msg'] = '1';
    						break;
    					}
    				}
    							
    				if($value['creator_id']){
    					if($value['creator_nickname']){
    						$returnData[$key]['user_id'] = $value['creator_id'];
    						$returnData[$key]['user_name'] = $value['creator_name'];
    						$returnData[$key]['nickname'] = $value['creator_nickname'];
    						$returnData[$key]['avatar'] = $value['creator_avatar'];
    					}else{
    						$user_info = User1::find()->select(['username','nickname','avatar'])->where(['user_id'=>$value['creator_id'],'status'=>'1'])->one();
    						if($user_info){
    						    $returnData[$key]['user_id'] = $value['creator_id'];
    						    $returnData[$key]['user_name'] = $user_info->username;
    						    $returnData[$key]['nickname'] = $user_info->nickname;
    						    $returnData[$key]['avatar'] = $user_info->avatar;
    						}
    					}
    					

    					/*-----------注释掉实时查询用户最新昵称和头像的代码-----------*/

    				}
    				$live_info = Live::findOne($live_id);
    				if($live_info){
    					$returnData[$key]['live_man_cate'] = $live_info->live_man_cate;
    					$returnData[$key]['live_man_alias'] = $live_info->live_man_alias;
    					$returnData[$key]['live_man_avatar_url'] = $live_info->live_man_avatar_url;
    				}
    			}
    		}
    	}
    	return $returnData;
    }
    
    public static function   UserGetNewMessage($live_id = null, $first_id = 0, $pageStart = 0, $pageEnd = null,$new = 0){
    	$keyword_arr = array(
    			'鲜花',
    			'跤靴',
    			'训练跤衣',
    			'竞技跤衣',
    			'至尊金跤衣'
    	);
    	$returnData = array();
    	if($live_id){
    		$model = new LivePanelUserManage();
    		$andwhere = '';
    		if($first_id) $andwhere .= 'and id > '.$first_id;
    		//获取置顶信息
    		$data = $model::find()->select(['id','create_time','pic_txt_content','creator_id','creator_name','creator_nickname','creator_avatar'])
    		->where("live_id = $live_id $andwhere")
    		->orderBy(['id'=>SORT_DESC])
    		->offset($pageStart)
    		->limit($pageEnd-$pageStart)
    		->asArray()->all();
    		if($data){
    			foreach($data as $key=>$value){
    				$returnData[$key]['msg_id']  = $value['id'];
    				$returnData[$key]['content'] = self::filter_words($value['pic_txt_content']);
    				$returnData[$key]['create_time'] = $value['create_time'];
    				$returnData[$key]['real_time']   = self::getRealTime($value['create_time']);
    				$returnData[$key]['pic_txt_content'] = self::filter_words(self::userTextDecode($value['pic_txt_content']));
    				$returnData[$key]['is_system_msg']   = '0';
    				
    				foreach($keyword_arr as $keyword_key=>$keyword_value){
    					if(strpos(self::userTextDecode($value['pic_txt_content']),$keyword_value)){
    						$returnData[$key]['is_system_msg'] = '1';
    						break;
    					}
    				}
    				
    			    if($value['creator_id']){
    			    	if($value['creator_nickname']){
    			    		$returnData[$key]['user_id']   = $value['creator_id'];
    			    		$returnData[$key]['user_name'] = $value['creator_name'];
							$returnData[$key]['nickname']  = $value['creator_nickname'];
    			    		$returnData[$key]['pic_txt_nickname']  = self::userTextDecode($value['creator_nickname']);
    			    		$returnData[$key]['avatar'] = $value['creator_avatar'];
    			    	}else{
    			    		$user_info = User1::find()->select(['username','nickname','avatar'])->where(['user_id'=>$value['creator_id']])->one();
    			    	   	if($user_info){
								$returnData[$key]['user_id'] = $value['creator_id'];
								$returnData[$key]['user_name'] = $user_info->username;
								$returnData[$key]['nickname'] = $user_info->nickname;
								$returnData[$key]['pic_txt_nickname']  = self::userTextDecode($user_info->nickname);
								$returnData[$key]['avatar'] = $user_info->avatar;
    			    		}
    			    	}
    			    	
    				}
					if($new == 1){
						$live_info = LiveSection::find()->where("section_id = ".$live_id)->one();
					}else{
						$live_info = Live::findOne($live_id);
					}

    				if($live_info){
    					$returnData[$key]['live_man_cate'] = $live_info->live_man_cate;
    					$returnData[$key]['live_man_alias'] = $live_info->live_man_alias;
    					$returnData[$key]['live_man_avatar_url'] = $live_info->live_man_avatar_url;
    				}
    			}
    		}
    	}
    	return $returnData;
    }
    
    /**
     *根据时间获取此时的比对时间
     *
     */
    static function getRealTime($date){
    	$realtime = '';
    	if($date){
    		if(time() - strtotime($date) < 3600){
    			$realtime = round((time() - strtotime($date))/60)."分钟前";
    		}elseif((time() - strtotime($date) > 3600) && (time() - strtotime($date) < 86400)){
    			$realtime = round((time() - strtotime($date))/3600)."小时前";
    		}else{
    			$realtime = date('Y-m-d',strtotime($date));
    		}
    	}
    	return $realtime;
    }
    

    /**
     * 把用户输入的文本转义（主要针对特殊符号和emoji表情）
     * @param $str
     * @return json
     */
    static function userTextEncode($str){
    	if(!is_string($str))return $str;
    	if(!$str || $str=='undefined')return '';
    
    	$text = json_encode($str);
    	$text = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i",function($str){
    		return addslashes($str[0]);
    	},$text);
    	return json_decode($text);
    }
    
    /**
     *解码上面的转义
     * @param $str
     * @return string
     */
    static  function userTextDecode($str){
    	$text = json_encode($str);
    	$text = preg_replace_callback('/\\\\\\\\/i',function($str){
    		return '\\';
    	},$text);
    	return json_decode($text);
    }

	/**
	 * 过滤敏感词
	 */
	public static function filter_words($content){
		$str = $content;
		$redis = Yii::$app->cache;
		$sensitive_words = $redis->get(Yii::$app->params['environment'].'_sensitive_words');
		if($sensitive_words && count($sensitive_words) > 0){
			$words = array_column($sensitive_words, 'words');
			for($i=0;$i<count($words);$i++){
				$replace = '';
				if(strstr($str, $words[$i])){
					for($j=1;$j<=strlen($words[$i])/3;$j++){
						$replace .= '*';
					}
					$str = str_replace($words[$i], $replace, $str);
				}
			}
		}
		return $str;
	}
	//获取 消息排序 ID
	public function getNextSortNumber(){
		$res = LivePanelManage::find()
			->select([
				"ifnull(max(sort_number),0)+1 as next_sort_number",
			])
			->asArray()->one();
		if($res) {
			$sort_number = $res['next_sort_number'];
		}else{
			$sort_number = 1;
		}
		return $sort_number;
	}
   
	//查看 图文消息列表
	public function get_MessageList($live_id,$last_id = 0,$pageEnd = null){
		$andwhere = '';
		if($last_id) $andwhere .= 'and id < '.$last_id;

		$res = LivePanelManage::find()
			->select("id,content_type,is_top,json_data")
			->where('live_id = '.$live_id)
			->andWhere(" is_top !=1 and content_type = 1 $andwhere")
			->orderBy("id desc")
			->offset(0)
			->limit($pageEnd)
			->asArray()->all();

		if($res){
			return $res;
		}else{
			return array();
		}
	}

	//获取 新图文消息列表
	public function get_NewMessage($live_id,$first_id = 0,$pageEnd = null){
		$andwhere = '';
		if($first_id) $andwhere .= 'and id > '.$first_id;

		$res = LivePanelManage::find()
			->select("id,content_type,is_top,json_data")
			->where('live_id = '.$live_id)
			->andWhere(" is_top !=1 and content_type = 1 $andwhere")
			->orderBy("id desc")
			->offset(0)
			->limit($pageEnd)
			->asArray()->all();

		if($res){
			return $res;
		}else{
			return array();
		}
	}
	
	//查看 图文消息 置顶和上榜列表
//	public function get_TopMessageList($live_id){
//		$res = LivePanelManage::find()
//			->select("id,pic_txt_content as content,content_type,create_time,is_top,creator_id,json_data")
//			->where('live_id = '.$live_id)
//			->andWhere(" is_top =1 or content_type = 2 ")
//			->orderBy("is_top desc,create_time desc")
//			->asArray()->all();
//
//		if($res){
//			return $res;
//		}else{
//			return array();
//		}
//	}
	//查看 图文消息 置顶和上榜列表
	public static function Get_TopAndOnList($live_id = null){
		$init_top_data = (object)array();
		$returnData = array('top_data'=>$init_top_data,'onlist_data'=>array());
		if($live_id){
			$model = new self();
			//获取置顶信息
			$top_data = $model::find()->select(['id','json_data','is_top','content_type'])->where(['live_id'=>$live_id,'is_top'=>'1'])->one();
			if($top_data){
				$first['msg_id'] = $top_data->id;
				$first['is_top'] = $top_data->is_top;
				$first['is_onlist'] = $top_data->content_type == '2' ? '1' : '0';


				$top_json_data_arr = json_decode($top_data->json_data,TRUE);

				if(isset($top_json_data_arr['images_url'])){
					if($top_json_data_arr['images_url']){
						$new_image_data = array();
						foreach($top_json_data_arr['images_url'] as $key=>$value){
							$new_image_data[$key]['thumbnail_image_url'] = $value.'/e';
							$new_image_data[$key]['original_image_url'] = $value;
						}
						$top_json_data_arr['images_url'] = $new_image_data;
						$top_json_data_arr['images_data'] = $new_image_data;
					}
				}
				if(!empty($top_json_data_arr['on_list_user_info']) && count($top_json_data_arr['on_list_user_info']) > 0){
					$top_json_data_arr['on_list_user_info']['user_username'] = self::userTextDecode($top_json_data_arr['on_list_user_info']['user_username']);
					$top_json_data_arr['on_list_user_info']['user_content'] = self::userTextDecode($top_json_data_arr['on_list_user_info']['user_content']);
				}
				$first['content'] = self::userTextDecode($top_json_data_arr['content']);
				$first['compere_id'] = $top_json_data_arr['compere_id'];
				$first['compere_cate'] = isset($top_json_data_arr['compere_cate']) ? $top_json_data_arr['compere_cate'] : "主持人";
				$first['compere_name'] = $top_json_data_arr['compere_name'];
				$first['compere_avatar'] = $top_json_data_arr['compere_avatar'] ? $top_json_data_arr['compere_avatar'] : "http://vrlive-10047449.image.myqcloud.com/lv1481274674584a7532dc9b1.png";
				$first['images_data'] = isset($top_json_data_arr['images_data']) ? $top_json_data_arr['images_data'] : array();
				$first['videos_data'] = isset($top_json_data_arr['videos_data']) ? $top_json_data_arr['videos_data'] : array();
				$first['images_url'] = isset($top_json_data_arr['images_url']) ? $top_json_data_arr['images_url'] : array();
				$first['videos_url'] = isset($top_json_data_arr['videos_url']) ? $top_json_data_arr['videos_url'] : array();
				$first['video_thumbnails'] = isset($top_json_data_arr['video_thumbnails']) ? $top_json_data_arr['video_thumbnails'] : array();
				$first['on_list_user_info'] = $top_json_data_arr['on_list_user_info'] ? $top_json_data_arr['on_list_user_info'] : array();
				$first['date_time'] = $top_json_data_arr['date_time'];

				$returnData['top_data'] = $first;
			}
			//获取上榜信息
			$onlist_data = $model::find()->select(['id','json_data','is_top','content_type'])->where(['live_id'=>$live_id,'is_top'=>'0','content_type'=>'2'])->orderBy(['create_time'=>SORT_DESC])->all();
			if($onlist_data){
				$other = array();
				foreach($onlist_data as $key=>$value){
					$other[$key]['msg_id'] = $value->id;
					$other[$key]['is_top'] = $value->is_top;
					$other[$key]['is_onlist'] = $value->content_type == '2' ? '1' : '0';

					$other_json_data_arr =  json_decode($value->json_data,TRUE);
					if(isset($other_json_data_arr['images_url'])){
						if($other_json_data_arr['images_url']){
							$new_image_data = array();
							foreach($other_json_data_arr['images_url'] as $key1=>$value1){
								$new_image_data[$key1]['thumbnail_image_url'] = $value1.'/e';
								$new_image_data[$key1]['original_image_url'] = $value1;
							}
							$other_json_data_arr['images_url'] = $new_image_data;
							$other_json_data_arr['images_data'] = $new_image_data;
						}
					}
					if(!empty($other_json_data_arr['on_list_user_info']) && count($other_json_data_arr['on_list_user_info']) > 0){
						$other_json_data_arr['on_list_user_info']['user_username'] = self::userTextDecode($other_json_data_arr['on_list_user_info']['user_username']);
						$other_json_data_arr['on_list_user_info']['user_content'] = self::userTextDecode($other_json_data_arr['on_list_user_info']['user_content']);
					}
					$other[$key]['content'] = self::userTextDecode($other_json_data_arr['content']);
					$other[$key]['compere_id'] = $other_json_data_arr['compere_id'];
					$other[$key]['compere_cate'] = isset($top_json_data_arr['compere_cate']) ? $top_json_data_arr['compere_cate'] : "主持人";
					$other[$key]['compere_name'] = $other_json_data_arr['compere_name'];
					$other[$key]['compere_avatar'] = $other_json_data_arr['compere_avatar'];
					$other[$key]['images_data'] = isset($other_json_data_arr['images_data']) ? $other_json_data_arr['images_data'] : array();
					$other[$key]['videos_data'] = isset($other_json_data_arr['videos_data']) ? $other_json_data_arr['videos_data'] : array();
					$other[$key]['images_url'] = isset($other_json_data_arr['images_url']) ? $other_json_data_arr['images_url'] : array();
					$other[$key]['videos_url'] = isset($other_json_data_arr['videos_url']) ? $other_json_data_arr['videos_url'] : array();
					$other[$key]['video_thumbnails'] = isset($other_json_data_arr['video_thumbnails']) ? $other_json_data_arr['video_thumbnails'] : array();
					$other[$key]['on_list_user_info'] = $other_json_data_arr['on_list_user_info'] ? $other_json_data_arr['on_list_user_info'] : array();
					$other[$key]['date_time'] = $other_json_data_arr['date_time'];
				}
				$returnData['onlist_data'] = $other;
			}
		}
		return $returnData;
	}

	/**
	 * 获取当前时间到毫秒的时间戳
	 */
	public static function msectime() {
		list($msec, $sec) = explode(' ', microtime());
		$msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
		return $msectime;
	}
    
}
