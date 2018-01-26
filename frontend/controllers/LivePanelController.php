<?php
namespace frontend\controllers;

use common\models\LivePanelManage;
use common\models\LivePanelFiles;

class LivePanelController extends PublicBaseController{
	

	//供前台调用
	public function actionGetPicTxt(){
		
		$res = LivePanelManage::find()->select("live_panel_manage.create_id,")
			->join('left join','vradmin.ad_users',"vradmin.ad_users.id=live_panel_manage.creator_id")
			->where(["live_id"=>$live_id])->orderBy("is_top desc, ".$SortBySql.$SortOrdSql)->asArray()->all();
		foreach ($res as $key=>$val){
			if(empty($val['user_speak_content'])){
				$val['user_speak_content'] = '';
			}
			$val['create_time'] =  date("Y-m-d", strtotime($val['create_time']));
			$res2 = LivePanelFiles::find()->select("*")->where(["live_id"=>$live_id,"msg_id"=>$val['id']])->asArray()->all();
			$val['msg_files']= $res2;
			$val['opt_type'] = '4';//表示是加载图文消息数据：
			$res[$key]=$val;
		}
		
		$ret = array('totop'=>$top,'tolist'=>$tolist,'data'=>$res,'user_id'=>$user_id);
		$this->_successData($ret);		
	} 
    /**
     * 获取每个直播面板的所有图文消息的数据:供后台使用
     * @return 
     */
    public function actionGetLivePanel(){    	
    	$token = '';
        $user_id = '';
  		 /*
        $token   = isset($_REQUEST['token']) ? $_REQUEST['token'] : '';
        if(!$token){
        	$this->_errorData(9001, '未经授权访问');
        	exit;
        } 
        else{
        	$userData = $this->_checkToken($token);
        	if($userData == false){
        		$this->_errorData(0055, '用户未登录');
        		exit;
        	}	
        }        
       */
        $live_id   = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '0';
        $msg_id = !empty($_REQUEST['msg_id']) ? $_REQUEST['msg_id'] : '0';//图文直播中的某一消息的编号,如果指定了值，则只返回该消息的内容而非该live_id的全部msg_id的内容
        $file_tag_id = !empty($_REQUEST['file_tag_id']) ? $_REQUEST['file_tag_id'] : '0';//图文直播的某一消息中的文件编号，如果指定了该值，则只返回该文件编号的url地址，而不包含其他的内容
        
        $sort_by   = isset($_REQUEST['sb']) ? $_REQUEST['sb'] : 's';//s：按sort_number排序;c:按create_time排序；u：按update_time排序        
        $SortBySql = 'sort_number';
        if ($sort_by=='c') {$SortBySql = "create_time";} 
        if ($sort_by=='u') {$SortBySql = "update_time";}
        if ($sort_by=='s') {$SortBySql = "sort_number";}
        
        $sort_order= isset($_REQUEST['so']) ? $_REQUEST['so'] : 'a';//排序方向：a：升序；d：降序
        $SortOrdSql = ' asc';
        if ($sort_order=='a') {$SortOrdSql = " asc";} 
        if ($sort_order=='d') {$SortOrdSql = " desc";}

        //$userData = array('userId'=>'201614747844623579');//测试数据
        $user_id = '';//$userData['userId'];
        if ($file_tag_id>0){        		 
        	$res = LivePanelFiles::find()->select("*")->where(["live_id"=>$live_id,"msg_id"=>$msg_id,'file_tag_id'=>$file_tag_id])->asArray()->one();
        	$ret = array('token'=>$token,'total'=>1,'data'=>array('msg_files'=>$res),'user_id'=>$user_id);
        	$this->_successData($ret);
        	exit;
        }
        if ($msg_id>0){
        	$res1 = LivePanelManage::find()->select("*")->where(["live_id"=>$live_id,"id"=>$msg_id])->asArray()->one();
        	$res2 = LivePanelFiles::find()->select("*")->where(["live_id"=>$live_id,"msg_id"=>$msg_id])->asArray()->all(); 
        	$res1['msg_files']=$res2;
        	$ret = array('token'=>$token,'total'=>1,'data'=>$res1,'user_id'=>$user_id);
        	$this->_successData($ret);
        	exit;
        }
        
        $total = LivePanelManage::find()->select("*")->where(["live_id"=>$live_id])->count();
        $res = LivePanelManage::find()->select("*")->where(["live_id"=>$live_id])->orderBy("is_top desc, ".$SortBySql.$SortOrdSql)->asArray()->all();
        foreach ($res as $key=>$val){
        	if(empty($val['user_speak_content'])){
        		$val['user_speak_content'] = '';
        	}
        	$val['create_time'] =  date("Y-m-d", strtotime($val['create_time']));
        	$res2 = LivePanelFiles::find()->select("*")->where(["live_id"=>$live_id,"msg_id"=>$val['id']])->asArray()->all();
        	$val['msg_files']= $res2;
        	$val['opt_type'] = '4';//表示是加载图文消息数据：
        	$res[$key]=$val;
        }        	
        $ret = array('token'=>$token,'total'=>$total,'data'=>$res,'user_id'=>$user_id);        
        $this->_successData($ret);
    }
    /**
     * 删除某直播面板的某条图文消息的数据
     * @return
     */
    public function actionDelLivePanel(){
    	$token = '';
    	$user_id = '';
    	/*
    	 $token   = isset($_REQUEST['token']) ? $_REQUEST['token'] : '';
    	if(!$token){
    	$this->_errorData(9001, '未经授权访问');
    	exit;
    	}
    	else{
    	$userData = $this->_checkToken($token);
    	if($userData == false){
    	$this->_errorData(0055, '用户未登录');
    	exit;
    	}
    	}
    	*/
    	$live_id   = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '0';
    	$msg_id   = isset($_REQUEST['msg_id']) ? $_REQUEST['msg_id'] : '0';
    	//$userData = array('userId'=>'201614747844623579');//测试数据
    	$user_id = '';//$userData['userId'];    	
    	$total = LivePanelManage::find()->select("*")->where(["live_id"=>$live_id,"id"=>$msg_id])->count();
    	LivePanelManage::deleteAll(["live_id"=>$live_id,"id"=>$msg_id]);    	
    	$ret = array('token'=>$token,"total"=>$total,'live_id'=>$live_id,'msg_id'=>$msg_id,'user_id'=>$user_id);
    	$this->_successData($ret);
    }
    
    /**
     * 置顶某直播面板的某条图文消息的数据
     * @return
     */				
    public function actionSetTopPanel(){  
    	$token = '';
    	$user_id = '';
    	/*
    	 $token   = isset($_REQUEST['token']) ? $_REQUEST['token'] : '';
    	if(!$token){
    	$this->_errorData(9001, '未经授权访问');
    	exit;
    	}
    	else{
    	$userData = $this->_checkToken($token);
    	if($userData == false){
    	$this->_errorData(0055, '用户未登录');
    	exit;
    	}
    	}
    	*/
    
    	$live_id   = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '0';
    	$msg_id   = isset($_REQUEST['msg_id']) ? $_REQUEST['msg_id'] : '0';
    	//$userData = array('userId'=>'201614747844623579');//测试数据
    	$user_id = '';//$userData['userId'];
    	$LivePanel = LivePanelManage::find()->where(["live_id"=>$live_id,"id"=>$msg_id])->asArray()->one();
		if (!empty($LivePanel)){
    		$sort_number = $LivePanel['sort_number'];
	    	$connection = LivePanelManage::getDb();
	    	$sql ="update vrlive.live_panel_manage set sort_number=sort_number+1 where live_id=$live_id and sort_number<$sort_number";
	    	$command=$connection->createCommand($sql);    	
	    	$command->execute();
	    	$sql ="update vrlive.live_panel_manage set sort_number=1 where live_id=$live_id and id='$msg_id'";
	    	$command=$connection->createCommand($sql);
	    	$command->execute();
	    	
	    	//全部取消置顶
	    	$sql ="update vrlive.live_panel_manage set is_top=0 where live_id=$live_id";
	    	$command=$connection->createCommand($sql);
	    	$command->execute();
	    	//把当前的置顶
	    	$sql ="update vrlive.live_panel_manage set is_top=1 where live_id=$live_id and id='$msg_id'";
	    	$command=$connection->createCommand($sql);
	    	$command->execute();
		}	    	
	    $ret = array('token'=>$token,'total'=>'1','live_id'=>$live_id,'msg_id'=>$msg_id,'user_id'=>$user_id);
    	$this->_successData($ret);
    }
    
    
    /**
     * 获取所有的置顶和上榜的列表
     * @cong.zhao
     */
    public function actionTopAndOnList(){
    	$live_id   = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '0';
    	$resource_type =  isset($_REQUEST['resource_type']) ? $_REQUEST['resource_type'] : 'array';
    	if(!$live_id){
    		$this->_errorData('0001','参数错误');
    	}
    	$returnData = LivePanelManage::GetTopAndOnList($live_id, $resource_type);
    	$this->_successData($returnData, "查选成功");
    }
    
    
    /**
     * 聊天室消息置顶
     * @cong.zhao
     */
    public function actionSetTop(){
    	$live_id   = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '0';
    	$msg_id   = isset($_REQUEST['msg_id']) ? $_REQUEST['msg_id'] : '0';

    	if(!$live_id || !$msg_id){
    		$this->_errorData('0001','参数错误');
    	}
    	$returnData = LivePanelManage::SetTop($live_id,$msg_id);
    	$this->_successData($returnData, "设置成功");
    }
    
    
    /**
     * 删除消息接口
     * @cong.zhao
     */
    public function actionDelMsg(){
    	$msg_id   = isset($_REQUEST['msg_id']) ? $_REQUEST['msg_id'] : '0';
    
    	if(!$msg_id){
    		$this->_errorData('0001','参数错误');
    	}
    	$returnData = LivePanelManage::DelMsg($msg_id);
    	$this->_successData($returnData, "删除成功");
    }
    
    /**
     * 根据用户id获取上榜回复信息
     */
    public function actionOnlistByUser(){
    	$page = isset($_REQUEST['page'])?$_REQUEST['page']:'';
    	$page = (!empty($page) && $page > 0) ? $page : 1;
    	$pageSize = isset($_REQUEST['pageSize'])?$_REQUEST['pageSize']:'';
    	$pageSize = (!empty($pageSize) && $pageSize > 0) ? $pageSize : 10;
    	$pageStart = ($page - 1) * $pageSize;
    	$pageEnd = $page * $pageSize;
    	$user_id   = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '0';
    	if(!$user_id){
    		$this->_errorData('0001','参数错误');
    	}
    	$returnData = LivePanelManage::GetOnlistByUserByUserId($user_id, $pageStart, $pageEnd);
    	$this->_successData($returnData, "查选成功");
    }
    
    
    /**
     * 拉取图文直播信息
     */
    public function actionMessageList(){
    	$pageSize = isset($_REQUEST['pageSize'])?$_REQUEST['pageSize']:'';
    	$pageSize = (!empty($pageSize) && $pageSize > 0) ? $pageSize : 10;
    	$live_id   = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '0';
    	$last_id   = isset($_REQUEST['last_id']) ? $_REQUEST['last_id'] : '0';
    	if(!$live_id){
    		$this->_errorData('0001','参数错误');
    	}
    	$returnData = LivePanelManage::GetMessageList($live_id, $last_id, 0, $pageSize);
    	$this->_successData($returnData, "查选成功");
    	
    	
    }
    
    
    /**
     * 实时获取最新图文信息
     */
    public function actionNewMessage(){
    	$pageSize = isset($_REQUEST['pageSize'])?$_REQUEST['pageSize']:'';
    	$pageSize = (!empty($pageSize) && $pageSize > 0) ? $pageSize : 10;
    	$live_id   = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '0';
    	$first_id   = isset($_REQUEST['first_id']) ? $_REQUEST['first_id'] : '0';
    	
    	if(!$live_id){
    		$this->_errorData('0001','参数错误');
    	}
    	$returnData = LivePanelManage::GetNewMessage($live_id, $first_id, 0, $pageSize);
    	$this->_successData($returnData, "查选成功");
    	 
    	 
    }
    
    
    /**
     * 图文消息发送(4-14)
     * @cong.zhao
     */
    public function actionUserSendMsg(){
    	$live_id   = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '0';
    	$user_id   = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '0';
    	$content   = isset($_REQUEST['content']) ? $_REQUEST['content'] : '';
    	$awarded_name   = isset($_REQUEST['awarded_name']) ? $_REQUEST['awarded_name'] : '';
    	$gift   = isset($_REQUEST['gift']) ? $_REQUEST['gift'] : '';
    	$gift_count   = isset($_REQUEST['gift_count']) ? $_REQUEST['gift_count'] : '';
    	
    	if(!$live_id){
    		$this->_errorData('0001','参数错误');
    	}
    	
    	$returnData = LivePanelManage::UserSendMsg($live_id, $user_id, $content, $awarded_name, $gift, $gift_count);
    	if($returnData){
    		$this->_successData($returnData,"发布成功");
    	}
    }
    
    /**
     * 图文消息列表拉取(4-17)
     * @cong.zhao
     */
    /**
     * 聊天室拉取图文直播信息
     */
    public function actionUserMessageList(){
    	$pageSize = isset($_REQUEST['pageSize'])?$_REQUEST['pageSize']:'';
    	$pageSize = (!empty($pageSize) && $pageSize > 0) ? $pageSize : 10;
    	$live_id   = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '0';
    	$last_id   = isset($_REQUEST['last_id']) ? $_REQUEST['last_id'] : '0';

    	if(!$live_id){
    		$this->_errorData('0001','参数错误');
    	}
    	$returnData = LivePanelManage::UserGetMessageList($live_id, $last_id, 0, $pageSize);
    	$this->_successData($returnData, "查选成功");	 
    }
    
    /**
     * 实时获取聊天室最新图文信息(4-17)
     * cong.zhao
     */
    public function actionUserNewMessage(){
    	$pageSize = isset($_REQUEST['pageSize'])?$_REQUEST['pageSize']:'';
    	$pageSize = (!empty($pageSize) && $pageSize > 0) ? $pageSize : 10;
    	$live_id   = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '0';
    	$first_id   = isset($_REQUEST['first_id']) ? $_REQUEST['first_id'] : '0';
    	 
    	if(!$live_id){
    		$this->_errorData('0001','参数错误');
    	}
    	$returnData = LivePanelManage::UserGetNewMessage($live_id, $first_id, 0, $pageSize);
    	$this->_successData($returnData, "查选成功");
    
    
    }
    
    
}