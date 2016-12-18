<?php

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>管理登陆</title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
<script type="text/javascript">
    //去空格
	String.prototype.Trim = function() { 
		var m = this.match(/^\s*(\S+(\s+\S+)*)\s*$/); 
		return (m == null) ? "" : m[1]; 
	}
	function ckLoginForm() {
	    var adminname,adminpwd
		adminname	= $("#adminname").val();
		adminpwd	= $("#adminpwd").val();
		if (adminname.Trim()=='' || adminname.replace(/[^\x00-\xff]/g, "**").length>12) {  
			$("#admininfo").addClass('adminerr').fadeIn('slow').html("用户名不能为空");
			$("#adminname").focus().select();
            return false; 
        }
		if (adminpwd.Trim()=='') {
			$("#admininfo").addClass('adminerr').fadeIn('slow').html("密码不能为空");
			$("#adminpwd").focus().select();
            return false; 
        }
		$.ajax({                           
			type: "POST",
			beforeSend:function(){$("#admininfo").addClass('adminsuccess').fadeIn('slow').html("正在登陆...");},
			url: "guestbook.php",
			dataType: 'text',
			data: "action=adminlogin&adminname="+adminname+"&adminpwd="+adminpwd,
			success: function(data){
				var data=data.split("@fen");
				if(data[0]=='0'){					
					$("#admininfo").removeClass();
					if(data[1]==''){
						$("#admininfo").addClass('adminerr').fadeIn('slow').html('未知错误');
					}else{
						$("#admininfo").addClass('adminerr').fadeIn('slow').html(data[1]);
					}	
				}else{
					$("#admininfo").addClass('adminsuccess').fadeIn('slow').html(data[1]);
					setTimeout(function() {						
						location.replace("default.php");
					}, 1500);				
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
	<div class="posttip"><span style="float:right;" id="admininfo"></span>管理员登陆</div>
	<div class="postarea">
		<table border="0" cellpadding="2" cellspacing="1" width="100%">
		<tr>
			<td width="60px" align="right"><label for="adminname">用户名：</label></td>
			<td><input type="text" name="adminname" id="adminname" value="" /></td>
		</tr>
		<tr>
			<td width="60px" align="right"><label for="content">密码：</label></td>
			<td><input type="password" name="adminpwd" id="adminpwd" value="" /></td>
		</tr>
		<tr>
			<td width="60px"></td>
			<td><input name="Submit" type="button" class="bt" id="bt_login" value="登陆" onclick="return ckLoginForm();" /></td>
		</tr>
		</table>
	</div>
	</form>
</div>
</body>
</html>