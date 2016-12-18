<?php
if(!file_exists('libs/install_lock.txt'))
{
	header("Location:install/index.php");
    exit();
}
session_start();
error_reporting(0);
include "config.php";
require_once('libs/page.php'); 
include "libs/function.php";
include "libs/mysql.class.php";
$WEB_dbprefix=WEB_dbprefix;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?PHP echo WEB_TITLE; ?></title>
<link href="css/skin_blue.css" rel="stylesheet" type="text/css"  id="cssfile" />
<link type="text/css" media="screen" rel="stylesheet" href="js/colorbox/colorbox.css" />
<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="js/global.js"></script>
<script type="text/javascript" src="js/jquery.cookie.js"></script>
<script type="text/javascript" src="js/colorbox/jquery.colorbox-min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	//colorbox
	$(".adminlogin").colorbox({
		width:'330px',
		transition:'elastic',
		opacity:0.5
	});
	$(".changepwd").colorbox({width:'330px',transition:'elastic',opacity:0.5});
});
</script>

<link rel="stylesheet" href="css/style.css" type="text/css" />

<script src="js/jquery-2.0.3.min.js" type="text/javascript"></script>	
<script type="text/javascript">
$(document).ready(function(){

	$(".side ul li").hover(function(){
		$(this).find(".sidebox").stop().animate({"width":"124px"},200).css({"opacity":"1","filter":"Alpha(opacity=100)","background":"#ae1c1c"})	
	},function(){
		$(this).find(".sidebox").stop().animate({"width":"54px"},200).css({"opacity":"0.8","filter":"Alpha(opacity=80)","background":"#000"})	
	});
	
});

//回到顶部
function goTop(){
	$('html,body').animate({'scrollTop':0},600);
}
</script>

</head>
<body>
<div class="side">
	<ul>
		<li><a href=""><div class="sidebox"><img src="img/side_icon01.png">留言日志</div></a></li>
		<li><a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=478940608&site=qq&menu=yes"><div class="sidebox"><img src="img/side_icon04.png">QQ客服</div></a></li>
		<li><a href="http://www.crown999.gq" ><div class="sidebox"><img src="img/side_icon03.png">新浪微博</div></a></li>
		
	</ul>
</div>

<div id="header">
<h1><?php echo WEB_TITLE;?></h1>
</div>
<div id="container">
	<div id="msg" style="display:none;"></div>
	<div class="postmsg">		
		<form>
		<div class="posttip">
		<ul id="skin">			
			<li id="skin_blue" title="蓝色" class="selected">蓝色</li>
			<li id="skin_green" title="绿色">绿色</li>
			<li id="skin_red" title="红色">红色</li>
			<li id="skin_yellow" title="黄色">黄色</li>
		</ul>
		<?php
		if(!empty($_SESSION['fig']) && $_SESSION['fig']!=''){
		?>
		<span class="building">欢迎, <?php echo $_SESSION["adminuser"] ?>&nbsp;&nbsp;<a href="changepwd.php" class="changepwd" target="_blank">修改密码</a>&nbsp;&nbsp;<a href="javascript:logout();">注销</a>&nbsp;&nbsp;</span>
		<?php
		}
		?>
		发表留言
		</div>
		<div class="postarea">
			<table border="0" cellpadding="2" cellspacing="1">
			<tr>
				<td width="60px"><label for="username">昵称(*)：</label></td>
				<td><input type="text" name="username" id="username" value="" /></td>
			</tr>
			<tr>
				<td width="60px"><label for="content">内容(*)：</label></td>
				<td><textarea name="msgcontent" id="msgcontent"></textarea></td>
			</tr>
			<tr>
				<td width="60px"></td>
				<td><input name="Submit" type="button" class="bt" id="bt_send" value="提交留言" onclick="return chkForm();" /><span id="sending"></span></td>
			</tr>
			</table>
		</div>
		</form>
	</div>
	
	<?php
	$page=isset($_GET['page']) ? $_GET['page'] : 1;
	$sql="select * from {$WEB_dbprefix}message order by Id desc";
	$connDb=conn_Db();
	$result = $connDb -> query($sql);
	$total = $connDb -> numrows($result);	#记录数
	_PAGEFT($total,WEB_PAGE,'');
	$row=$connDb -> fetcharray($result);
	if(!empty($row[0])){
		$results=$connDb->query("select * from {$WEB_dbprefix}message order by Id desc limit $firstcount,$displaypg");
	}
	?>
	<div class="msglist">
		<div class="posttip">留言列表</div>
		<div class="showmsg">			
			<?php
			if(isset($results) && $results==true){
			while($rows = $connDb -> fetcharray($results)){
			?>
			<div id="innerTips<?php echo $rows["Id"] ?>"></div>
			<ul>
				<li class="msgtop">
					<span class="building"><?php echo $total ?>F</span>
					<?php
					if(!empty($_SESSION['fig']) && $_SESSION['fig']!=''){
					?>
					<span class="building">				
					<a href="javascript:openReply(<?php echo $rows["Id"] ?>);" id="replytxt<?php echo $rows["Id"] ?>">回复</a>&nbsp;&nbsp;
					<a href="javascript:delmsg(<?php echo $rows["Id"] ?>);">删除</a>&nbsp;&nbsp;
					</span>
					<?php
					}
					?>
					<span class="nickname"><b>昵称：</b><?php echo $rows["UserName"] ?></span>
					<b>时间：</b><?php echo $rows["DateTime"] ?>
				</li>
				<li class="msgcont" id="msgcont<?php echo $rows["Id"] ?>">
					<?php echo $rows["MsgContent"] ?>
					<div class="replyaction" id="<?php echo $rows["Id"] ?>">
						<textarea id="replymsg<?php echo $rows["Id"] ?>"  class="replymsg"></textarea><br/>
						<input name="Submit" type="button" class="bt" id="bt_sendr<?php echo $rows["Id"] ?>" value="提交回复" onclick="return chkReply(<?php echo $rows["Id"] ?>);" />
						<span id="replysending<?php echo $rows["Id"] ?>" style="display:block; width:50px;"></span>
					</div>
					<?php
					$sql_reply="select * from {$WEB_dbprefix}reply where belongId=".$rows['Id'];
					$result_r = $connDb -> query($sql_reply);
					while($row_r = $connDb -> fetcharray($result_r)){
					?>
					<div class="reply">回复：[<?php echo $row_r["dateTime"] ?>]<br/><?php echo $row_r["replyContent"] ?></div>
					<?php
					}
					?>
				</li>
			</ul>
			<?php
				$total--;
			}
			}else{		
				echo "没有留言";
			}
			?>
			<div class="pagenav">
			<?php echo $pagenav ?>
			</div>
		</div>
	</div>
</div>
<div id="footer">
  <p>Copyright © 2016 <a href="http://dfood.ml"><?php echo WEB_TITLE;?></a>. All rigths reserved&nbsp;&nbsp;
  <?php if(empty($_SESSION['fig'])){ ?>
  <a href="admin.php" class="adminlogin" target="_blank">管理</a>
  <?php } ?>
  </p>
  <p>多鱼点餐系统是你用过最方便快捷的下单系统</p>
  <p>重案组工作室 </p>
  <p>QQ：478940608（黄蜂）</p>
</div>
<div id="footerborder"></div>
</body>
</html>