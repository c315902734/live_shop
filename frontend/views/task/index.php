<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>金币</title>
   <link rel="stylesheet" href="./css/index2.css">
    <meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <meta name="format-detection" content="telephone=no, email=no" /> 
</head>
<body>
	<div class="container">
		<!--<div class="header">-->
			<!--<label class="label1"><a href="#"><img src="img/1.png" alt=""></a></label>-->
			<!--<label class="label2">每日任务</label>-->
		<!--</div>-->
		<div class="section">
			<table class="gridtable">
				<tr>
				    <th>任务</th><th>奖励数量</th><th>上限</th><th>已获得</th>
				</tr>
				<?php
				$total_num = 0;
				$max       = 0;
					foreach($list as $keys=>$vo){
						$total_num += $vo['num'];
						$max += $vo['max_cnt'];
				?>
				<tr>
					<td><?php echo $vo['task_name'];?></td>
					<td>+<?php echo $vo['amount'];?></td>
					<td><?php echo $vo['max_cnt'];?></td>
					<td><?php if($vo['num']){echo $vo['num'];}else{echo 0;}?></td>
				</tr>
				<?php } ?>
           </table>
           <div class="xiang"><a href="#" onclick="toDetail();">查看任务详情></a></div>
           <div class="huo">
           	<p style="font-size:18px;">今日已获得<em style="color:#2ea7e0;padding:5px;"><?php echo $total_num;?></em>汇闻币</p>
           	<p>可再获得<em style="color:#2ea7e0;padding:5px;"><?php echo $max-$total_num;?></em>汇闻币</p>
           </div>
		</div>
		<div class="footer"></div>
	</div>
</body>
</html>
<script>
	function toDetail(){
		var u = navigator.userAgent, app = navigator.appVersion;
		var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; //android终端或者uc浏览器
		var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
		if (isAndroid) {
			Control.toTaskDetail();
		} else if(isiOS) {
			toTaskDetail();
		}
	}
</script>