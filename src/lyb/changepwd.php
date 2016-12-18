<?php
session_start();
error_reporting(0);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>修改密码</title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
<script type="text/javascript">
    //去空格
	String.prototype.Trim = function() { 
		var m = this.match(/^\s*(\S+(\s+\S+)*)\s*$/); 
		return (m == null) ? "" : m[1]; 
	}
	function ckPwdForm() {
	    var adminid,adminuser,oldpwd,newpwd,renewpwd
		adminid		= $('#adminId').val();
		adminuser	= $("#adminuser").val();
		oldpwd		= $("#oldpwd").val();
		newpwd		= $("#newpwd").val();
		renewpwd	= $("#renewpwd").val();
		if (adminuser.Trim()=='') {  
			$("#admininfo").addClass('adminerr').fadeIn('slow').html("用户名不能为空");
			$("#adminuser").focus().select();
            return false; 
        }
		if (oldpwd.Trim()=='') {
			$("#admininfo").addClass('adminerr').fadeIn('slow').html("旧密码不能为空");
			$("#oldpwd").focus().select();
            return false; 
        }
		if (newpwd.Trim()=='') {
			$("#admininfo").addClass('adminerr').fadeIn('slow').html("新密码不能为空");
			$("#newpwd").focus().select();
            return false; 
        }
		if (newpwd.Trim()!=renewpwd.Trim()) {
			$("#admininfo").addClass('adminerr').fadeIn('slow').html("请确认两次新密码相同");
			$("#renewpwd").focus().select();
            return false; 
        }
		$.ajax({                           
			type: "POST",
			beforeSend:function(){$("#admininfo").addClass('adminsuccess').fadeIn('slow').html("正在修改...");},
			url: "guestbook.php",
			dataType: 'text',
			data: "action=changepwd&adminid="+adminid+"&adminname="+adminuser+"&oldpwd="+oldpwd+"&newpwd="+newpwd+"&renewpwd="+renewpwd,
			success: function(data){
				$("#admininfo").removeClass();
				var msg=data.split("@fen");
				if(msg[0]=='1'){
					$("#admininfo").addClass('adminsuccess').fadeIn('slow').html(msg[1]);
					$("#adminuser").val('');
					$("#oldpwd").val('');
					$("#newpwd").val('');
					$("#renewpwd").val('');
					setTimeout(function() {						
						location.replace("default.php");
					}, 2000);
				}else{
					if(msg[1]==''){
						$("#admininfo").addClass('adminerr').fadeIn('slow').html('未知错误');
					}else{
						$("#admininfo").addClass('adminerr').fadeIn('slow').html(msg[1]);
					}					
				}
			}
	    });
		return true;
	}
 </script>
</head>
<body>
<div id="adminlogin">
	<form>
	<div class="posttip"><span style="float:right;" id="admininfo"></span>修改密码</div>
	<div class="postarea">
		<table border="0" cellpadding="2" cellspacing="1" width="100%">
		<tr>
			<td width="80px" align="right"><label for="adminuser">用户名：</label></td>
			<td><input type="text" name="adminuser" id="adminuser" value="<?php echo $_SESSION["adminuser"] ?>" /><input type="hidden" name="adminId" id="adminId" value="<?php echo $_SESSION["fig"] ?>" /></td>
		</tr>
		<tr>
			<td width="80px" align="right"><label for="oldpwd">原密码：</label></td>
			<td><input type="password" name="oldpwd" id="oldpwd" value="" /></td>
		</tr>
		<tr>
			<td width="80px" align="right"><label for="newpwd">新密码：</label></td>
			<td><input type="password" name="newpwd" id="newpwd" value="" /></td>
		</tr>
		<tr>
			<td width="80px" align="right"><label for="renewpwd">确认密码：</label></td>
			<td><input type="password" name="renewpwd" id="renewpwd" value="" /></td>
		</tr>
		<tr>
			<td width="80px"></td>
			<td><input name="Submit" type="button" class="bt" id="bt_pwd" value="修改" onclick="return ckPwdForm();" /></td>
		</tr>
		</table>
	</div>
	</form>
</div>
</body>
</html>