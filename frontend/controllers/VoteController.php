<?php

namespace frontend\controllers;
use common\models\Votes;
use common\models\VoteClient;
use common\models\VoteWxinfo;
use common\models\VoteUser;
use yii\data\Pagination;
use xr0m3oz\curl;
use yii;
use yii\helpers\Url;

class VoteController extends \yii\web\Controller
{
    public $request;
    //YII2的防止csrf攻击，你则不能多次提交相同的表单。如果你希望当前可以多次重复提交，可以在当前提交的表单controller中添加
    // public $enableCsrfValidation = false;
    public function init(){
        $this->request = Yii::$app->request;
        parent::init();
    }

    
    //投票入口页面
    public function actionIndex()
    {

        return $this->redirect('http://vote.xinhuiwen.com/vote/test');
        exit;
        //微信接口认证
        //判断是否是微信验证请求
        $timestamp = $this->request->get('timestamp');
        $nonce = $this->request->get('nonce');
        $timestamp = $this->request->get('timestamp');
        $signature = $this->request->get('signature');
        $echostr = $this->request->get('echostr');
        if(isset($timestamp) && isset($nonce) && isset($signature) && isset($echostr)){
            //验证微信
            $tmpArr = array(Yii::$app->params['token'], $timestamp, $nonce);
            sort($tmpArr, SORT_STRING);
            $tmpStr = implode( $tmpArr );
            $tmpStr = sha1( $tmpStr );
            if($tmpStr == $signature){
                if(isset($echostr))
                    echo $echostr;
                else
                    exit;
            }else{
                return 'error request';
            }
        }else{
            //显示假的投票页面
        }
    }

    public function actionTest()
    {
        exit();
        //微信接口认证
        //判断是否是微信验证请求
        $timestamp = $this->request->get('timestamp');
        $nonce = $this->request->get('nonce');
        $timestamp = $this->request->get('timestamp');
        $signature = $this->request->get('signature');
        $echostr = $this->request->get('echostr');
        if(isset($timestamp) && isset($nonce) && isset($signature) && isset($echostr)){
            //验证微信
            $tmpArr = array(Yii::$app->params['token'], $timestamp, $nonce);
            sort($tmpArr, SORT_STRING);
            $tmpStr = implode( $tmpArr );
            $tmpStr = sha1( $tmpStr );
            if($tmpStr == $signature){
                if(isset($echostr))
                    echo $echostr;
                else
                    exit;
            }else{
                return 'error request';
            }
        }else{
            // $callback = 'http://vote.xinhuiwen.com/vote/callback?active=auth_sign';
            // //静默跳转，不用不授权，但是只能拿到openid
            // $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.Yii::$app->params['app_key'].'&redirect_uri='.$callback.'&response_type=code&scope=snsapi_base&state=1#wechat_redirect';
            // //静默跳转，不用不授权，但是只能拿到openid
            // $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.Yii::$app->params['app_key'].'&redirect_uri='.$callback.'&response_type=code&scope=snsapi_base&state=1#wechat_redirect';
            // //用户授权跳转，可以拿到用户所有信息
            // // $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.Yii::$app->params['app_key'].'&redirect_uri='.$callback.'&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect';
            // header("Location:".$url);
            // exit;
            
            //如果带着token必须验证
            $source = $this->request->get('source');
            if($source=='ios' or $source =='android'){
                $token = $this->request->get('token');
                if($token){
                    $userData =json_decode($this->getUserInfo($token),true);
                    if(!$userData['Success']){
                        $token = '';
                    }
                }else{
                    $token = '';
                }    
            }else{
                $source = 'noapp';
                $token = '';
            }
            //查询数据库access_token和jsapi_ticket是否失效，如果失效获取并更新数据库
            $voteWxinfo = VoteWxinfo::find()->where(['app_key'=>Yii::$app->params['app_key'],'app_secret'=>Yii::$app->params['app_secret']])->one();
            $time = time();
            if (!$voteWxinfo || $time>=$voteWxinfo->access_expires_in || $time>=$voteWxinfo->jsapi_expires_in){
                //如果不存在 accessToken 或者 accessToken 失效
                if (!$voteWxinfo || $time>=$voteWxinfo->access_expires_in){
                   //获取access_token
                    $noncestr = $this->getRandStr(8);
                    $timestamp = time();
                    //获取access_token
                    $addrUrl = Yii::$app->params['apiUrl']."cgi-bin/token?grant_type=client_credential&appid=".Yii::$app->params['app_key']."&secret=".Yii::$app->params['app_secret'];
                    $curl = new curl\Curl();
                    $response = $curl->get($addrUrl);
                    $accessTokenJson = json_decode($response->body,true);
                    $access_token = $accessTokenJson['access_token'];
                    $access_expires_in = time()+intval($accessTokenJson['expires_in']); 
                }else{
                    $access_token = $voteWxinfo->access_token;
                    $access_expires_in = $voteWxinfo->access_expires_in;
                }
                //如果没有jsapi 或者 jsapi 失效
                if(!$voteWxinfo || $time>=$voteWxinfo->jsapi_expires_in){
                    //获取 jsapi_ticket
                    $addrUrl = Yii::$app->params['apiUrl']."cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
                    $curl = new curl\Curl();
                    $response = $curl->get($addrUrl);
                    $jsapiTicketJson = json_decode($response->body,true);
                    //保存jsapi
                    $jsapi_ticket = $jsapiTicketJson['ticket'];
                    $jsapi_expires_in = time()+intval($jsapiTicketJson['expires_in']);
                }else{
                    $jsapi_ticket = $voteWxinfo->jsapi_ticket;
                    $jsapi_expires_in = $voteWxinfo->jsapi_expires_in;
                }
                if (!$voteWxinfo){
                    //添加数据
                    $voteWxinfo = new VoteWxinfo();
                    $voteWxinfo->app_key = Yii::$app->params['app_key'];
                    $voteWxinfo->app_secret = Yii::$app->params['app_secret'];
                }
                $voteWxinfo->access_token = $access_token;
                $voteWxinfo->access_expires_in = $access_expires_in;
                $voteWxinfo->jsapi_ticket = $jsapi_ticket;
                $voteWxinfo->jsapi_expires_in = $jsapi_expires_in;
                $voteWxinfo->save();
            }else{
                $jsapi_ticket = $voteWxinfo->jsapi_ticket;
            }
            //记录并获得签名
            $timestamp = time();
            $appId = Yii::$app->params['app_key'];
            // $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $url = Url::base(true).Url::current();
            $noncestr = $this->getRandStr(8);
            //计算签名
            //步骤1.对所有待签名参数按照字段名的ASCII 码从小到大排序（字典序）后，使用URL键值对的格式（即key1=value1&key2=value2…）拼接成字符串string1：
            $string1 = 'jsapi_ticket='.$jsapi_ticket.'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$url;
            // var_dump($string1);
            //步骤2. 对string1进行sha1签名，得到signature
            $signature = sha1( $string1 );
            $logoUrl = Url::home(true).'localFile/jtxyb_logo.png';
            $votes = Votes::find()->orderBy(['sex'=>SORT_ASC,'vote_cnt'=>SORT_DESC])->asArray()->all();
            //计算百分比
            $sumVoteCnt = 0;
            foreach ($votes as $val) {
                $sumVoteCnt += $val['vote_cnt'];
            }
            foreach ($votes as $key => $val) {
                $votes[$key]['ratio'] = round($val['vote_cnt']/$sumVoteCnt,2)*100;
            }
            $this->getView()->title = "跤坛英雄榜";
            return $this->render('test',[
                'votes' => $votes,
                'appId' => $appId,
                'timestamp' => $timestamp,
                'signature' => $signature,
                'noncestr' => $noncestr,
                'logoUrl' => $logoUrl,
                'sumVoteCnt'=>$sumVoteCnt,
                'token' => $token,
                'source' => $source,
            ]);
        }
    }

    public function actionCallback(){
        echo 'wang';exit;
        $active = $this->request->get('active');
        $code = $this->request->get('code',false);
        if($active=='auth_sign' && $code){
            //获取临时access_token 和 openid
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.Yii::$app->params['app_key'].'&secret='.Yii::$app->params['app_secret'].'&code='.$code.'&grant_type=authorization_code';
            $curl = new curl\Curl();
            $response = $curl->get($url);
            $res = json_decode($response->body,true);
            //换取用户信息
            if(isset($res['openid'])){
                $openId = $res['openid'];
                //查询用户信息是否记录
                $voteUser = VoteUser::find()->where(['open_id'=>$openId])->one();
                if ($voteUser){
                    //跳转回投票页面
                    echo '进入了投票页面';
                }else{
                    //通过调用接口获取用户信息
                    // $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$res['access_token']."&openid=".$res['openid']."&lang=zh_CN";
                    $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$res['access_token']."&openid=".$res['openid']."&lang=zh_CN";
                    $curl = new curl\Curl();
                    $response = $curl->get($url);
                    $userInfo = json_decode($response->body,true);
                    var_dump($userInfo);exit;
                    if(isset($userInfo['openid'])){
                        $voteUser = new VoteUser();
                        $voteUser->open_id = $userInfo['openid'];
                        $voteUser->nickname = $userInfo['nickname'];
                        $voteUser->sex = $userInfo['sex'];
                        $voteUser->language = $userInfo['language'];
                        $voteUser->city = $userInfo['city'];
                        $voteUser->province = $userInfo['province'];
                        $voteUser->country = $userInfo['country'];
                        $voteUser->headimgurl = $userInfo['headimgurl'];
                        $voteUser->subscribe_time = $userInfo['subscribe_time'];
                        $voteUser->unionid = $userInfo['unionid'];
                        if($voteUser->save()){
                            //跳转进入投票页面
                            echo '进入了投票页面';
                        }
                    }

                }
            }
        }
    }
    public function actionVotes()
    {
        date_default_timezone_set('Asia/Shanghai');
        //投票时间 2016.10.01 - 2016.10.15
        // $sVoteTime = strtotime('2016/10/01 00:00:00');
        // $eVoteTime = strtotime('2016/10/15 23:59:59');
        $sVoteTime = strtotime('2016/09/28 00:00:00');
        $eVoteTime = strtotime('2017/10/15 23:59:59');
        $current = time();
        if ($current<$sVoteTime){
            // echo '3';
            $result['status'] = array('code'=>3,'content'=>'抱歉,投票还未开始!');
            echo json_encode($result);
            exit;
        }else if ($current>$eVoteTime){
            // echo '4';//投票已经结束
            $result['status'] = array('code'=>3,'content'=>'抱歉，投票已经结束！');
            echo json_encode($result);
            exit;
        }
        $finger = $this->request->post('finger');
        //每人每天5票
		date_default_timezone_set('Asia/Shanghai');
        $sTime = strtotime(date('Y-m-d',time())." 00:00:00");
        $eTime = strtotime(date('Y-m-d',time())." 23:59:59");
        $voteCnt = VoteClient::find()->where(['finger'=>$finger])->andWhere(['>=','created_at',$sTime])->andWhere(['<=','created_at',$eTime])->count();
        if($voteCnt < 5){
            //每选手只能被投1票
            $voteId = $this->request->post('vote_id');
            $isVote = VoteClient::find()->where(['finger'=>$finger,'vote_id'=>$voteId])->one();
            if(!$isVote){
                $votes = Votes::findOne($voteId);
                //更新投票数
                $votes->vote_cnt++;
                $voteCnt = $votes->vote_cnt;
                $votes->save();
                //保存指纹投票记录
                $voteClient = new VoteClient();
                $voteClient->finger = $finger;
                $voteClient->vote_id = $voteId;
                $voteClient->save();
                $result['status'] = array('code'=>1,'content'=>'投票成功!');
                $result['vote_cnt'] = $voteCnt++;
                //计算支持率
                $votes = Votes::find()->orderBy(['sex'=>SORT_ASC,'vote_cnt'=>SORT_DESC])->asArray()->all();
                //计算百分比
                $sumVoteCnt = 0;
                foreach ($votes as $val) {
                    $sumVoteCnt += $val['vote_cnt'];
                }
                $result['vote_ratio'] = round($result['vote_cnt']/$sumVoteCnt,2)*100;
                echo json_encode($result);
            }else{
                // echo '0';
                $result['status'] = array('code'=>0,'content'=>'抱歉，您已经投过TA了!');
                echo json_encode($result);
            }
        }else{
            // echo '2';
            $result['status'] = array('code'=>2,'content'=>'抱歉，一天只能投5次票!');
            echo json_encode($result);
        }
    }
    /**
     * [getRandStr 获取随机字符串]
     * @param  [type] $length [字符串长度]
     * @return [type]         [生成的随机串]
     */
    function getRandStr($length){
        $str = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
        $randString = '';
        $len = strlen($str)-1;
        for($i = 0;$i < $length;$i ++){
            $num = mt_rand(0, $len);
            $randString .= $str[$num];
        }
        return $randString ;
    }
    //对象转换成数组
    function objToArr($obj){ 
        if(is_object($obj)) {  
            $obj = (array)$obj;  
         }if(is_array($obj)) {  
             foreach($obj as $key=>$value) {  
                 $obj[$key] = $this->objToArr($value);  
                 }  
         }  
         return $obj;  

    }

    private function getUserInfo($token=''){
        $ch = curl_init();

        // Set url
        curl_setopt($ch, CURLOPT_URL, 'https://api.xinhuiwen.com/Api/User/GetUserId?token='.$token);

        // Set method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        // Set options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Cookie: PHPSESSID=k0fjhqunvbvd7rl54kiqt37l32; _csrf-frontend=7c27d11ce1c18e45a760136518b24987ae4a2d147e91111736d046fcbfe72338a%3A2%3A%7Bi%3A0%3Bs%3A14%3A%22_csrf-frontend%22%3Bi%3A1%3Bs%3A32%3A%22JqsJ4ar6jd-QcoSvL9qsbRWQew51WGHL%22%3B%7D; tgw_l7_route=816a5b2e7e135ad3b890c2da3a7f1a3e",
        ]);


        // Send the request & save response to $resp
        $resp = curl_exec($ch);
        if(!$resp) {
            // Close request to clear up some resources
            curl_close($ch);
            return false;
        } else {
            // Close request to clear up some resources
            curl_close($ch);
            return $resp;
        }
    }
}
