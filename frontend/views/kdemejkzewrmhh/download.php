<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="/localFile/favicon.ico" /> 
    <title>法制与新闻客户端下载页面</title>
    <link rel="stylesheet" type="text/css" href="/css/reset.css">
    <link rel="stylesheet" type="text/css" href="/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <meta name="format-detection" content="telephone=no, email=no" />
    <script type="text/javascript" src="/js/jquery.min.js"></script>
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
        <div class="load-btn load-btn-andr">
            <a href="#" onclick="is_weixn();return false">
                <span >去App Store下载</span>
<!--                <input type="button" value="去APP Store下载" >-->
            </a>
        </div>

        <!-- 遮罩层 -->
        <div class="wxtip" id="JweixinTip">

            <div class="wxtip-txt">
                <span class="wxtip-icon"><img src="/localFile/download/arrow2.png" alt="">	</span>
                <span class="tipText">请点击微信右上角按钮，然后在弹出菜单中，点击在浏览器中打开，即可安装</span>
                <span class="tipSure">OK</span>
            </div>
        </div>

        <section class="ios-sec">
            <div class="code">
                <img src="/localFile/download/ios_new/ios@3x.png" alt="">
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
        if($(window).width()>=410){
            $(".load-logo").height("200px");
            $(".code img").css({
                "width":"130px",
                "height":"130px"
            })
        }
        if($(window).width()<375){
            // $(".load-logo").height("200px");
            $(".wxtip-txt .tipText").css({
                "font-size":"12px"
            })
            $(".wxtip-txt .tipSure").css({
                bottom:"10px"
            })
        }

        var btn1 = document.getElementById('load');

        function is_weixn(){
            var ua = navigator.userAgent.toLowerCase();
            var user_id = $("#userid").val();
            var datas = '';
            if(user_id){
                datas =  "type=0&user_id="+user_id;
            }

            if(ua.match(/MicroMessenger/i)=="micromessenger") {
//                alert("请点击微信右上角按钮，然后在弹出菜单中，点击在Safari中打开，即可安装");

                document.getElementById('JweixinTip').style.display='block';

                document.getElementById('JweixinTip').onclick=function(){
                    this.style.display='none';
                }
                $(".tipSure").click(function(){
                    $(".wxtip").hide();
                })
            } else {
                if(user_id) {
                    //点击次数 加1
                    $.ajax({
                        type: "GET",
                        data: datas,
                        async: false,
                        url: "download_add",
                        success: function (ret) {
                            window.location.href = 'https://itunes.apple.com/us/app/fa-zhi-yu-xin-wen-re-dian/id1133184252?l=zh&ls=1&mt=8';
                        }
                    });
                }else{
                    window.location.href = 'https://itunes.apple.com/us/app/fa-zhi-yu-xin-wen-re-dian/id1133184252?l=zh&ls=1&mt=8';
                }

            }
        }
    </script>

</body>

</html>



<!--<body>-->
<!--    <div class="container">-->
<!--       <div class="header">-->
<!--         <div class="zuo"><img src="/localFile/download/ios/logo.png" alt=""></div>-->
<!--         <div class="you">法制与新闻客户端</div>-->
<!--      </div> -->
<!--    <div class="section">-->
<!--       <div class="img"><img src="/localFile/download/ios/xin.png" alt=""></div>-->
<!--       <div class="niu"><img src="/localFile/download/ios/ios.png" alt="" onclick="is_weixn()"></div>    -->
<!--    </div>-->
<!--  </div>-->
<!--  <script type="text/javascript">-->
<!--        function is_weixn(){  -->
<!--            var ua = navigator.userAgent.toLowerCase();  -->
<!--            if(ua.match(/MicroMessenger/i)=="micromessenger") {  -->
<!--                alert("请点击微信右上角按钮，然后在弹出菜单中，点击在Safari中打开，即可安装");-->
<!--            } else {  -->
<!--                window.location.href='https://itunes.apple.com/us/app/fa-zhi-yu-xin-wen-re-dian/id1133184252?l=zh&ls=1&mt=8';-->
<!--            }-->
<!--        }  -->
<!--  </script>-->
<!--</body>-->
