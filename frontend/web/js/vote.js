//投票按钮
$(function($){
	var finger = "";//指纹
	var isWx = false;//是否在微信打开
	//获取指纹码
	var fp1 = new Fingerprint().get();
	var fp2 = new Fingerprint({canvas: true}).get();
	var fp3 = new Fingerprint({ie_activex: true}).get();
	var fp4 = new Fingerprint({screen_resolution: true}).get();
	//叠加生成指纹码
	if("undefined" != typeof fp1){
		finger = finger+fp1;
	}
	//叠加生成指纹码
	if("undefined" != typeof fp2){
		finger = finger+fp2;
	}
	//叠加生成指纹码
	if("undefined" != typeof fp3){
		finger = finger+fp3;
	}
	//叠加生成指纹码
	if("undefined" != typeof fp4){
		finger = finger+fp4;
	}
	//通过config接口注入权限验证配置
	wx.config({
	    debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
	    appId: appId, // 必填，公众号的唯一标识
	    timestamp:timestamp , // 必填，生成签名的时间戳
	    nonceStr: nonceStr, // 必填，生成签名的随机串
	    signature: signature,// 必填，签名，见附录1
	    jsApiList: ['onMenuShareTimeline',
			'onMenuShareAppMessage',
			'openWithQQBrowser',
			'onMenuShareQQ',
			'onMenuShareWeibo',
			'startRecord用于',
			'stopRecord',
			'onVoiceRecordEnd',
			'playVoice',
			'pauseVoice',
			'stopVoice',
			'onVoicePlayEnd',
			'uploadVoice',
			'downloadVoice',
			'chooseImage',
			'previewImage',
			'uploadImage',
			'downloadImage',
			'translateVoice',
			'getNetworkType',
			'openLocation',
			'getLocation',
			'hideOptionMenu',
			'showOptionMenu',
			'hideMenuItems',
			'showMenuItems',
			'hideAllNonBaseMenuItem',
			'showAllNonBaseMenuItem',
			'closeWindow',
			'scanQRCode',
			'chooseWXPay',
			'openProductSpecificView',
			'addCard',
			'chooseCard',
			'OpenCard'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
	});
	wx.ready(function(){
		isWx = true;
		
		// //获取“分享到朋友圈”按钮点击状态及自定义分享内容接口
		wx.onMenuShareTimeline({
			title: '跤坛英雄榜选手人气投票', // 分享标题
			link: 'http://vote.xinhuiwen.com/vote/test', // 分享链接
			imgUrl: logoUrl, // 分享图标
			success: function () {
				// 用户确认分享后执行的回调函数
				// alert('分享成功');
			}
		});
		//分享给朋友
		wx.onMenuShareAppMessage({
		    title: '跤坛英雄榜选手人气投票', // 分享标题
		    desc: '关注“新汇闻”微信公众号，回复“投票”，为您喜爱的选手进行投票加油！', // 分享描述
		    link: 'http://vote.xinhuiwen.com/vote/test', // 分享链接
		    imgUrl: logoUrl, // 分享图标
		    type: 'link', // 分享类型,music、video或link，不填默认为link
		    dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
		    success: function () { 
		        // 用户确认分享后执行的回调函数
		    },
		    cancel: function () { 
		        // 用户取消分享后执行的回调函数
		    }
		});

		wx.onMenuShareQQ({
			title: '跤坛英雄榜选手人气投票', // 分享标题
			desc: '关注“新汇闻”微信公众号，回复“投票”，为您喜爱的选手进行投票加油！', // 分享描述
			link: 'http://vote.xinhuiwen.com/vote/test', // 分享链接
			imgUrl: logoUrl, // 分享图标
			success: function () { 
			// 用户确认分享后执行的回调函数
			},
			cancel: function () { 
			// 用户取消分享后执行的回调函数
			}
		});
		
		//获取网络状态接口
		// wx.getNetworkType({
		//     success: function (res) {
		//         var networkType = res.networkType; // 返回网络类型2g，3g，4g，wifi
		//     }
		// });
		// wx.hideOptionMenu();//隐藏右上角菜单
		// wx.getNetworkType();
		
	    	// config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。
	});
	wx.error(function(res){
		// alert(res);
		// alert('出错了');
	    	// config信息验证失败会执行error函数，如签名过期导致验证失败，具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对于SPA可以在这里更新签名。
	});
	function decimal(num,v){
		var vv = Math.pow(10,v);
		return Math.round(num*vv)/vv;
	}
	$(".myvote").click(function(){
		//安卓和IOS 没有token的话，要登录
		if(source=='android' && token==''){
			window.newsControl.toLogin();
			return false;
		}else if (source == 'ios' && token==''){
			toLogin();
			return false;
		}
		if(isWx || source=='ios' || source=='android'){
			vote_id = $(this).attr("vote_id");
			if (token!=''){
				//如果有token 替代掉原来的指纹吗
				finger = token;
			}
			$.post('/vote/votes',{'vote_id':vote_id,'finger':finger},function(result){
				var res = eval("("+result+")");
				if(res.status.code==1){
					if(source=='android' && token!=''){
						window.newsControl.toResult('ok');
						return false;
					}else if (source == 'ios' && token!=''){
						toResult('ok');
						return false;
					}else{
						alert("投票成功");
					}
					//修改投票数
					vote_selector = $(".my_vote_cnt"+vote_id);
					//修改进度占百分比
					ratio = res.vote_ratio;
					vote_selector.parent().find(".finish").attr("vote_ratio",ratio);
					//修改进度条
					vote_selector.parent().find(".finish").css("width",ratio+"%");
					vote_selector.parent().find(".ing").text(ratio+"%");
					// vote_cnt = Number(vote_selector.text());
					// vote_selector.text(String(vote_cnt+1));
					vote_selector.text(res.vote_cnt+'票');
				}else{
					if(source=='android' && token!=''){
						window.newsControl.toResult(res.status.content);
						return false;
					}else if (source == 'ios' && token!=''){
						toResult(res.status.content);
						return false;
					}else{
						alert(res.status.content);
					}
					// $(".yu").text(res.status.content);
					// $(".ze_error").show();
				}
			});
		}else{
			alert('扫描底部二维码关注后再投票');
			// location.hash ="QRCode";
			// $(".yu").text('扫描底部二维码关注后再投票');
			// $(".ze_error").show();
		}
	});
	$("img.btn_hide_ok").bind("click",function(){
		$(".ze_ok").hide();
	});
	$("img.btn_hide_error").bind("click",function(){
		$(".ze_error").hide();
	});
	var cnt =1;
	var isContinue;
	var ms = 6000; //变量MS: 从0%到100%需要的毫秒数
	var time = setInterval(function(){
		var finish = $("#bar .finish");
		var ratio;
		var width;
		isContinue = false;
		for (var i = 0; i < finish.length; i++) {
			ratio = finish.eq(i).attr("vote_ratio")+"%";
			// width = finish.eq(i).css("width");
			//元素当前进度
			width = finish[i].style.width;
			if(!(ratio=="0%" && width=="")){
				if (width != ratio ){
					// width = width.substr(0,width.indexOf("px"))
					// finish.eq(i).css("width",cnt+"%");
					finish[i].style.width= cnt+"%";
					isContinue = true;
					finish.eq(i).parent().find(".ing").text(cnt+"%");
				}
			}
		}
		if (cnt==100){
			clearInterval(time);
		}
		cnt++;
		// if(!isContinue)
		// 	clearInterval(time);

		// i=i+(1000/ms);
		// if(i>100){
		// 	clearInterval(time);
		// 	$("#bar .ing").text(ratio+"%")
		// }

		// $("#bar .finish").css("width",i+"%");
		// i=i+(1000/ms);
		// $("#bar .ing").text(Math.round(i)+"%");
		// if(i>ratio){
		// 	clearInterval(time);
		// 	$("#bar .ing").text(ratio+"%")
		// }
	},50);
});
