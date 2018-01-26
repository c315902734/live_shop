<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'POST oauth2/<action:\w+>' => 'oauth2/rest/<action>',
            	'POST thirdParty/thirdPartyLogin' => 'third-party/third-party-login',                             				//第三方登录接口
            	'POST Entrance/index' => 'entrance/index',                                                                      				//获取快捷入口列表信息
            	'POST Version/getIOSVersion' => 'version/get-ios-version',                                     		 				 //获取IOS最新版本号
            	'POST Version/getVersion' => 'version/get-version',                                                       				 //获取安卓最新版本号
            	'POST User/getProvinceList' => 'user/get-province-list',                                                 				 //获取开通省份列表 
            	'POST User/getCityList' => 'user/get-city-list',																	 				 //获取省份下的开通城市
            	'POST User/UpdateUserInfo' => 'update-user-info',														  				//修改用户信息
            	'POST User/getVisitorRcloudToken' => 'get-visitor-rcloud-token',								  				//根据设备获取融云token
            	'POST User/GetUserId' => 'user/get-user-id',																	  				//根据token 获取用户ID
            	'POST User/BindPhone' => 'user/bind-phone',																  				//绑定手机号
            	'POST Movement/messageAll' => 'movement/message-all',											  				//用户获取的推送消息
            	'POST User/FedUserInfo' => 'user/fed-user-info',														      				//用户反馈信息
            	'POST User/login' => 'user/login',																					  				//用户登录--手机号码
            	'POST User/sendVerifyCode' => 'third-party/send-verify-code',									  				//用户注册-发送验证码
            	'POST User/register' => 'third-party/register',																	  				//用户注册-完成注册
            	'POST User/getMyInfo' => 'user/get-my-info',															      				//我的资料
            	'POST User/uploadAvatar' => 'user/uploadAvatar',										  				  				//我的资料--上传头像
            	'POST User/changePwd' => 'user/change-pwd',																  				//账户安全-修改密码
            	'POST User/changeMobile' => 'user/change-mobile',													  				//账户安全-更换手机号
            	'POST User/forgetPwd' => 'third-party/forget-pwd',														  				//用户注册-找回密码
            	'POST User/message' => 'user/message',																		  				//消息列表（系统通知）
            	'POST Newscomment/commentList' => 'newscomment/comment-list',						  				//评论/回复列表接口
            	'POST Newscomment/commentDel' => 'newscomment/comment-del',						  				//评论/回复删除接口
            	'POST Newscomment/commentAdd' => 'newscomment/comment-add',						  				//发布评论/回复接口
            	'POST Newscommentlike/commentZan' => 'newscommentlike/comment-zan',			  				//评论/回复点赞/取消点赞
            	'POST Newsusercollect/collectList' => 'newsusercollect/collect-list',						        			//收藏列表接口
            	'POST Newsusercollect/collectAdd' => 'newsusercollect/collect-add',						        		    //添加收藏接口
            	'POST Newsusercollect/collectDel' => 'newsusercollect/collect-del',						        			//取消收藏接口
            	'POST Information/UserNewsCollectStatus' => 'information/user-news-collect-status',		        //用户是否收藏过某一条新闻接口
            	'POST Usersubscribesource/subscribeList' => 'usersubscribesource/subscribe-list',				    //我的订阅列表接口
            	'POST Usersubscribesource/subscribeAdd' => 'usersubscribesource/subscribe-add',			    //添加订阅接口
            	'POST Usersubscribesource/subscribeDel' => 'usersubscribesource/subscribe-del',				    //取消订阅接口
            	'POST Newssource/sourceListl' => 'newssource/source-list',						  									//更多订阅列表接口
            	'POST Newssource/sourceDetail' => 'newssource/source-detail',						  							//订阅号详情列表接口
            	'POST news_column/columnType' => 'news-column/column-type',						  						//新闻栏目
            	'POST information/infolist' => 'information/info-list',						 										    //新闻列表
            	'POST information/speciallist' => 'information/special-list',						  									//专题新闻列表
            	'POST information/newinfo' => 'information/new-info',						 									    //新闻详情（不含直播）
            	'POST information/clickvideonum' => 'information/click-video-num',						  					//点击视频 点击次数加1
            	'POST news_column/getArea' => 'news-column/get-area',						  									//地区
            	'POST news_column/getBanner' => 'news-column/get-banner',						 						    //轮播图
            	'POST Api/live/liveList' => 'live/live-list',						 						    										//直播列表
            	'POST live/subscribeLive' => 'live/subscribe-liver',						 						    						//预约直播
            	'POST live/newsRecommend' => 'live/news-recommend',						 						    		   //相关新闻
            	'POST live/getLiveById' => 'live/get-live-by-id',						 						   							   //直播详情
            	'POST live/DescribeLVBChannel' => 'live/describelvb-channel',						 						   //机位详情
            	'POST Version/isOpen' => 'version/is-open',						 						    							   //判断隐藏引导页、直播
            	'POST Newsquiz/getNewsQuize' => 'newsquiz/get-news-quize',						 						   //获取新闻的竞猜信息
            	'POST Newsquiz/betting' => 'newsquiz/betting',						 						    					   //竞猜押注接口
            	'POST UserAmount/addAmount' => 'user-amount/add-amount',						 						   //充值记录
            	'POST User/getMyGold' => 'user/get-my-gold',						 						    						  //我的汇闻币
            	'POST Exchange/createAutoLoginRequest' => 'third-party/create-auto-login-request',			  //兑吧生产免登陆url
            	'POST competitor/getcompetitorlist' => 'competitor/getcompetitorlist',						 		      //选手列表
            	'POST prop/addgifts' => 'prop/addgifts',						 						    									  //送礼接口
            	'POST prop/getproplist' => 'prop/getproplist',						 						    						  //道具礼物列表
            	'POST Pay/index' => 'pay/index',						 						    												  //充值
            	'POST Recharge/orderr' => 'recharge/order',						 						    							  //IOS充值
            	'POST competitor/competitorlist' => 'competitor/competitorlist',						 						  //打赏列表
            	'POST prop/gifts' => 'prop/gifts',						 						    												  //打赏接口
            	'POST Recharge/notice' => 'recharge/notice',						 						    							  //IOS充值结果通知
                'POST friendCircle/friendCircleList' => 'friend-circle/friend-circle-list',
                'POST friendCircle/friendCircleDetail' => 'friend-circle/friend-circle-detail',
            ],
        ],
        
    ],
    'modules' => [
        'oauth2' => [
            'class' => 'filsh\yii2\oauth2server\Module',            
            'tokenParamName' => 'accessToken',
            'tokenAccessLifetime' => 3600 * 24,
            'storageMap' => [
                'user_credentials' => 'frontend\models\User',

            ],
            'grantTypes' => [
                'user_credentials' => [
                    'class' => 'OAuth2\GrantType\UserCredentials',
                ],
            	'client_credentials'=>[
            		'class' => 'OAuth2\GrantType\ClientCredentials',
            	],
                'refresh_token' => [
                    'class' => 'OAuth2\GrantType\RefreshToken',
                    'always_issue_new_refresh_token' => true
                ]
            ]
        ]
    ],
    'params' => $params,
];
