<?php
/*
+-----------------------------------------------------------------------
|	文件概要：后台处理文件
|	文件名称：config.php
|	创建时间：2010-9-7
+-----------------------------------------------------------------------
*/

header("Content-Type: text/html; charset=utf-8");

#报告所有错误
error_reporting(E_ALL);
//error_reporting(0);

session_start();
$_POST['action'] !=''&& isset($_POST['action']) ? $action=$_POST["action"] : $action="";
$messageReturn=array("0","参数错误");

include "config.php";
include "libs/function.php";
include "libs/mysql.class.php";
$WEB_dbprefix=WEB_dbprefix;

#动作判断函数
if($action!=""){
	if(!in_array($action,array("sendMsg","replyMsg","delMsg","changepwd","adminlogin","logout"))){error($messageReturn);}
	switch($action){
		case "sendMsg":
			sendMsg();
		break;
		case "replyMsg":
			replyMsg();
		break;
		case "delMsg":
			delMsg();
		break;
		case "changepwd":
			changePwd();
		break;
		case "adminlogin":
			adminLogin();
		break;
		case "logout":
			logOut();
		break;
		default:error($messageReturn);
	}
}

#留言提交函数
function sendMsg() {
	global $messageReturn,$WEB_dbprefix;
	$userName=trim($_POST['username']);
	$msgContent=checkhtml(trim($_POST['msgcontent']));
	$dtime=date("Y-m-d H:i:s");
	
	if($userName=='' || $userName==null) {
		$messageReturn[1]="昵称不能为空";
		error($messageReturn);
	}
	if($msgContent=='' || $msgContent==null){
		$messageReturn[1]="内容不能为空";
		error($messageReturn);
	}
	
	$sql="insert into {$WEB_dbprefix}message(UserName,MsgContent,DateTime) values('$userName','$msgContent','$dtime')";
	$result = conn_Db() -> query($sql);
	if($result==true){
		$messageReturn[0]=1;
		$messageReturn[1]="留言成功";
		error($messageReturn);
	}else{
		$messageReturn[1]="留言失败";
		error($messageReturn);
	}
}

#回复留言函数
function replyMsg(){
	global $messageReturn,$WEB_dbprefix;
	$id=$_POST['Id'];
	$replycnt=trim($_POST['replyContent']);
	$dtime=date("Y-m-d H:i:s");
	
	if($id=='' || $id==null || !intval($id)) {
		$messageReturn[1]="参数不正确";
		error($messageReturn);
	}
	if($replycnt=='' || $replycnt==null){
		$messageReturn[1]="请填写回复内容";
		error($messageReturn);
	}
	
	$sql="insert into {$WEB_dbprefix}reply(belongId,replyContent,dateTime) values('$id','$replycnt','$dtime')";
	$result = conn_Db() -> query($sql);
	if($result==true){
		$messageReturn[0]=1;
		$messageReturn[1]="回复成功";
		error($messageReturn);
	}else{
		$messageReturn[1]="回复失败";
		error($messageReturn);
	}
}

#删除留言函数
function delMsg(){	
	global $messageReturn,$WEB_dbprefix;
	$Id=$_POST['Id'];
	
	if($Id=='' || $Id==null) {
		$messageReturn[1]="留言Id不正确";
		error($messageReturn);
	}
	$connDb=conn_Db();
	$sql_r="delete from {$WEB_dbprefix}reply where belongId='$Id'";
	$connDb -> query($sql_r);
	$sql="delete from {$WEB_dbprefix}message where Id='$Id'";
	$result = $connDb -> query($sql);
	if($result==true){
		$messageReturn[0]=1;
		$messageReturn[1]="删除成功";
		error($messageReturn);
	}else{
		$messageReturn[1]="删除失败";
		error($messageReturn);
	}
}

#更改管理密码
function changePwd(){
	global $messageReturn,$WEB_dbprefix;
	$adminId=trim($_POST['adminid']);
	$adminName=trim($_POST['adminname']);
	$oldpwd=md5(trim($_POST['oldpwd']));
	$newpwd=md5(trim($_POST['newpwd']));
	$renewpwd=md5(trim($_POST['renewpwd']));
	
	if($adminName=='' || $adminName==null) {
		$messageReturn[1]="用户名不能为空";
		error($messageReturn);
	}
	if($oldpwd=='' || $oldpwd==null){
		$messageReturn[1]="旧密码不能为空";
		error($messageReturn);
	}
	if($newpwd=='' || $newpwd==null) {
		$messageReturn[1]="新密码不能为空";
		error($messageReturn);
	}
	if($newpwd != $renewpwd){
		$messageReturn[1]="两次输入的密码不相同";
		error($messageReturn);
	}
	
	$sql="select * from {$WEB_dbprefix}admin where adminName='".$_SESSION['adminuser']."' and adminPwd='$oldpwd'";
	$connDb=conn_Db();
	$result = $connDb -> query($sql);
	$row = $connDb -> numrows($result);
	if($row>0){
		$sqlupdate = "update {$WEB_dbprefix}admin set adminName='$adminName',adminPwd='$renewpwd' where Id='$adminId'";
		$pwdchange = $connDb -> query($sqlupdate);
		if($pwdchange==true){
			$messageReturn[0]=1;
			$messageReturn[1]="修改成功,正在返回...";
			error($messageReturn);
		}else{
			$messageReturn[1]="修改失败";
			error($messageReturn);
		}
	}else{
		$messageReturn[1]="用户名或密码错误";
		error($messageReturn);
	}
}

#管理员登陆函数
function adminLogin(){
	global $messageReturn,$WEB_dbprefix;
	$adminName=trim($_POST['adminname']);
	$adminPwd=md5(trim($_POST['adminpwd']));
	
	if($adminName=='' || $adminName==null) {
		$messageReturn[1]="用户名不能为空";
		error($messageReturn);
	}
	if($adminPwd=='' || $adminPwd==null){
		$messageReturn[1]="密码不能为空";
		error($messageReturn);
	}
	
	$sql="select * from {$WEB_dbprefix}admin where adminName='$adminName' and adminPwd='$adminPwd'";
	$connDb=conn_Db();
	$result = $connDb -> query($sql);
	$row=$connDb -> numrows($result);
	$rows = $connDb -> fetcharray($result);
	if($row>0){
		$_SESSION['fig'] = $rows['Id'];
		$_SESSION['adminuser'] = $adminName;
		$messageReturn[0]=1;
		$messageReturn[1]="登陆成功,正在返回...";
		error($messageReturn);
	}else{
		$messageReturn[1]="用户名或密码错误";
		error($messageReturn);
	}
}

#退出管理
function logOut(){
	global $messageReturn;
	session_unset();
	session_destroy();
	$messageReturn[0]=1;
	$messageReturn[1]="注销成功";
	error($messageReturn);
}

#错误处理函数
function error($msg){
	exit ($msg[0]."@fen".$msg[1]);
}
?>