<?php
/* @var $this yii\web\View */
// use yii\web\JqueryAsset;
?>
<div id="ze" class="ze_ok">
    <div class="cg">
        <div class="zhi" id="dao">
            <img class="btn_hide_ok" src="/localFile/button1.png" alt="">
        </div>
    </div>
</div>
<div id="ze" class="ze_error">
    <div class="sb">
        <div class="yu" style="font-size:12px">投票失败</div>
        <div class="zhi" id="dao">
            <img class="btn_hide_error" src="/localFile/button2.png" alt="">
        </div>
    </div>
</div>
<div class="header"><img src="/localFile/1.png" alt=""></div>
<div class="section">
	<div class="nav"><img src="/localFile/2.png" alt=""></div>
	<div class="wen">
           <p>登录<span style="color:red;">"法制与新闻客户端"</span>,为您喜爱的选手进行投票加油，参与投票的用户将</p>
           <p>有多大的几率获得跤坛英雄榜管委会特别奖励,人气王选手将有机会为</p>
           <p><span style="color:red;">"跤坛英雄榜"</span>代言。本活动最终解释权归跤坛英雄榜管委会</p>
         </div>
	<div class="sai">
		<a name="man"></a>
		<label class="zuo" style="color:white;">男子组</label>
		<label class="zhong">
			<a href="#woman" style="color:#333;text-decoration:none;">女子组</a>
		</label>
		<label class="you">
			<a href="#rule" style="color:#333;text-decoration:none;">详情规则</a>
		</label>
	</div>
	<?php
		$isWoman = false;
		$nounCnt = 0;
		for ($i=0; $i <count($votes) ; $i++) {
				//进入女子组
				if (!$isWoman && $votes[$i]['sex']==2){
	?>
					<div class="sai">
						<a name="woman"></a>
						<label class="zuo">
							<a href="#man" style="color:#333;text-decoration:none;">男子组
							</a>
						</label>
						<label class="zhong" style="color:white;">女子组</label>
						<label class="you">
							<a href="#rule" style="color:#333;text-decoration:none;">详情规则</a>
						</label>
					</div>
	<?php 
					$isWoman = true;
					//前三名计数 归零
					$nounCnt = 0;
				}
			if ($i % 2 == 0){?>
				<div class="tu">
	<?php 
			}
	?>
					<div class="img<?= $i%2 +1 ?>">
						<div class="jiao" <?php if($nounCnt>2){echo 'id="jian"';}?>>
							<?php
								if ($nounCnt==0){
							?>
									<img src="/localFile/12.png" alt="">
							<?php 
								}else if ($nounCnt==1){
							?>
									<img src="/localFile/13.png" alt="">
							<?php 
								}else if ($nounCnt==2){
							?>
									<img src="/localFile/14.png" alt="">
							<?php 
								}else{
							?>
									No.<?=$nounCnt+1 ?>
							<?php
								}
							?>
						</div>
						<div class="up">
							<img src="/localFile<?= $votes[$i]['image'] ?>" alt="">
						</div>
						<div class="ong">
							<p class="p1"><?= $votes[$i]['alias_name'] ?></p>
							<?php
								if ($nounCnt==2){
							?>	
									<p class="p2 myvote" vote_id='<?= $votes[$i]['id']?>' style="background:#f76203;border:1px solid #f76203;" >投票</p>
							<?php
								}else if ($nounCnt<2){
							?>
									<p class="p2 myvote" vote_id='<?= $votes[$i]['id']?>' >投票</p>
							<?php
								}else if ($nounCnt>2){
							?>
									<p class="p2 myvote" vote_id='<?= $votes[$i]['id']?>' style="background:#00bbff;border:1px solid #00bbff;">投票</p>
							<?php
								}
							?>

						</div>
						<div class="down">
							<div id="bar">
								<?php
									if ($nounCnt==2){
								?>	
										<div class="finish" vote_ratio='<?= $votes[$i]['ratio']?>' style="background:#f76203;"></div>
								<?php
									}else if($nounCnt<2){
								?>
										<div class="finish" vote_ratio='<?= $votes[$i]['ratio']?>' ></div>
								<?php
									}else if ($nounCnt>2){
								?>
										<div class="finish" vote_ratio='<?= $votes[$i]['ratio']?>' style="background:#00bbff;"></div>
								<?php
									}
								?>

								<p class="ing" style="margin-top:-14px;"><?= $votes[$i]['ratio'] ?>%</p>
							</div>
							<div class="shu my_vote_cnt<?=$votes[$i]['id']?>"><?= $votes[$i]['vote_cnt'] ?>票</div>
						</div>
					</div>
		<?php 
			if ($i % 2 !=0){
		?>
				</div>
		<?php 
			}
		?>
	<?php
			$nounCnt++;//排名计数归零
		} 
	?>
	<a name="rule"></a>
	<div class="sai"> 
		<label class="zuo">
			<a href="#man" style="color:#333;text-decoration:none;">男子组</a>
		</label>
		<label class="zhong">
			<a href="#woman" style="color:#333;text-decoration:none;">女子组</a>
		</label>
		<label class="you" style="color:white;">详情规则</label>
	</div>
	<div class="xing">
		<img src="/localFile/ti.png" alt="">
	</div>
	
</div>
<<!-- div class="footer"></div> -->

<script type="text/javascript">
	//定义变量
	var appId = '<?=$appId?>';
	var timestamp = '<?=$timestamp?>';
	var signature = '<?=$signature?>';
	var nonceStr = '<?=$noncestr?>';
	var logoUrl = '<?=$logoUrl?>';
	var sumVoteCnt = Number('<?=$sumVoteCnt?>');
	var token = '<?=$token?>';
	var source = '<?=$source?>';
</script>

<?php 
/*	foreach ($votes as $vote){
		// echo $vote->vote_cnt;
		echo "投票数：<div class='my_vote_cnt{$vote->id}' >".$vote->vote_cnt."</div>";
		echo '-';
		echo $vote->user_name;
		echo '-';
		echo $vote->group->group_name;
		echo "<input vote_id='{$vote->id}' class='myvote' type='button' value='投我'/>";
		echo '<br />';
	}*/
?>