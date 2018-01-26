<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="/localFile/favicon.ico" />
    <title>法制与新闻客户端下载页面</title>
<!--    <link rel="stylesheet" href="/css/download_a.css">-->
    <link rel="stylesheet" type="text/css" href="/css/reset.css">
    <link rel="stylesheet" type="text/css" href="/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <meta name="format-detection" content="telephone=no, email=no" /> 
     <script type="text/javascript" src="/js/jquery.min.js"></script>
    <script type="text/javascript" src="https://h5.xinhuiwen.com/public/H5/js/MD5.js"></script>
    <style>
        .wxtip{background: rgba(0,0,0,0.8); text-align: center; position: fixed; left:0; top: 0; width: 100%; height: 100%; z-index: 2; display: none;}
        .wxtip-icon {width: 70px; height: 120px; /*background: url(images/share_icon.png) no-repeat;*/ display: block; position: absolute; right: 0px; top: -120px;}
        .wxtip-icon img{width: 100%; height: 100%;}
        .wxtip-txt{position: relative; z-index: 2;color: #000; font-size: 14px;
            width: 80%;height: 20%;top:40%;left:10%;border-radius: 5px;background: #fff;}
        .wxtip-txt .tipText{    padding: 30px 20px 0px 20px;text-align: left;
            display: inline-block;
            line-height: 20px;}
        .wxtip-txt .tipSure{color:#21de23;position: absolute;bottom:20px;left: 50%;margin-left:-16px; }
    </style>
</head>

<body class="body">
    <div id="load-container">
        <div class="load-logo">
            <img src="/localFile/download/banner.png" alt="">
        </div>
        <div class="load-btn-andr">
            <a href="#" onclick="is_weixn();return false"><img src="/localFile/download/loading@3x.png" alt="" ><span>点击安装</span></a>
        </div>

        <!-- 遮罩层 -->
        <div class="wxtip" id="JweixinTip">

            <div class="wxtip-txt">
                <span class="wxtip-icon"><img src="/localFile/download/arrow2.png" alt="">	</span>
                <span class="tipText">请点击微信右上角按钮，然后在弹出菜单中，点击在safari中打开，即可安装</span>
                <span class="tipSure">OK</span>
            </div>
        </div>

        <section >
            <div class="code" style="border-bottom:1px dashed #999;">
                <img src="/localFile/download/android_new/andr@3x.png" alt="">
                <span>您还可以到腾讯应用宝、VIVO、OPPO、</span>
                <span>华为、三星、联想等手机应用商店搜索下载</span>
            </div>
            <ul>
                <li><img src="/localFile/download/QQ@3x.png" alt=""><span>海量国内国际身边要闻一键订阅</span></li>
                <li><img src="/localFile/download/shipin@3x.png" alt=""><span>热点高清视频、VR主题直播任性约</span></li>
                <li><img src="/localFile/download/liwu@3x.png" alt=""><span>福利商品超值 兑幸运抽奖赢不停</span></li>
            </ul>
        </section>
        <span class="line"></span>
        <ol>
            <li>技术支持</li>
            <li>客服电话：4009005881</li>
            <li>客户服务邮箱：developer@xinhuiwen.com</li>
            <li></li>
            <li>京ICP备 16017854号-1</li>
            <li>Copyright  2016 All Rights Reserved</li>
            <li>Powered by xinhuiwen.com</li>
        </ol>
        <input type="hidden" id="userid" value="<?php if(isset($user_id)){echo $user_id;}else{echo '';}?>">
    </div>
    <script type="text/javascript">
        var btn1 = document.getElementById('load');
        function is_weixn(){
            var ua = navigator.userAgent.toLowerCase();
            var user_id = $("#userid").val();
            var datas = '';
            if(user_id){
                datas =  "type=1&user_id="+user_id;
            }
            if(ua.match(/MicroMessenger/i)=="micromessenger"){

                document.getElementById('JweixinTip').style.display='block';
                document.getElementById('JweixinTip').onclick=function(){
                    this.style.display='none';
                }
                $(".tipSure").click(function(){
                    $(".wxtip").hide();
                })

            } else {
                var appKey = "html";
                var unique = "xinhuiwen,fighting!";
                var timestamp = Date.parse(new Date()) / 1000;
                var md5Val = "app_key=" + appKey + "&timestamp=" + timestamp + "&unique=" + unique;
                var signature = $.md5(md5Val);
                if(user_id) {
                    //点击次数 加1
                    $.ajax({
                        type: "GET",
                        data: datas,
                        async: false,
                        url: "download_add",
                        success: function (ret) {
                            window.location.href='https://new.api.xinhuiwen.com/version/download?timestamp='+timestamp+'&unique='+unique+'&app_key='+appKey+'&sign='+signature;
                        }
                    });
                }else{
                    window.location.href='https://new.api.xinhuiwen.com/version/download?timestamp='+timestamp+'&unique='+unique+'&app_key='+appKey+'&sign='+signature;
                }
            }
        }
    </script>

</body>



<!--<body>-->
<!--    <div class="container">-->
<!--       <div class="header">-->
<!--         <div class="zuo"><img src="/localFile/download/android/logo.png" alt=""></div>-->
<!--         <div class="you">法制与新闻客户端</div>-->
<!--      </div> -->
<!--    <div class="section">-->
<!--       <div class="img"><img src="/localFile/download/android/xin.png" alt=""></div>-->
<!--       <div class="niu"><img src="/localFile/download/android/xia.png" alt="" onclick="is_weixn()"></div>-->
<!--       <div class="zi">-->
<!--         <p>您还可以到百度手机助手、豌豆英、91商城、360</p>-->
<!--         <p>手机助手、APPChina应用汇、搜狗手机助手、</p>-->
<!--         <p>联通沃商店搜索下载</p>-->
<!--       </div>-->
<!--    </div>-->
<!--  </div>-->
<!--  <script type="text/javascript">-->
<!--        function is_weixn(){-->
<!--            var ua = navigator.userAgent.toLowerCase();  -->
<!--            if(ua.match(/MicroMessenger/i)=="micromessenger") {  -->
<!--                alert("请点击微信右上角按钮，然后在弹出菜单中选择用浏览器打开，即可安装");-->
<!--            }else{-->
<!--                window.location.href='https://api.xinhuiwen.com/api/app/download';-->
<!--            }-->
<!--        }  -->
<!--    </script>-->
<!--</body>-->
</html>