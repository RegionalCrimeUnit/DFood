	//检查表单
	//去空格
	String.prototype.Trim = function() { 
		var m = this.match(/^\s*(\S+(\s+\S+)*)\s*$/); 
		return (m == null) ? "" : m[1]; 
	}
	function chkForm() {
	    var username,msgcontent,success
		username	= $("#username").val();
		msgcontent	= $("#msgcontent").val();
		success		= "留言发送成功";
		if (username.Trim()=='' || username.length>6) {  
			$("#msg").removeClass();
			$("#msg").addClass('error').slideDown('slow').html("昵称必须小于6位且不能为空");
			$("#username").focus().select();
            return false; 
        }
		if (msgcontent.Trim()=='') {  
			$("#msg").removeClass();
			$("#msg").addClass('error').slideDown('slow').html("请填写留言");
			$("#msgcontent").focus().select();
            return false; 
        }
		$.ajax({                           
			type: "POST",
			beforeSend:function(){
				$("#msg").addClass('success').slideDown('slow').html("正在发送...");
				$('#bt_send').css('display','none');
				$('#sending').html('正在提交...');
			},
			url: "guestbook.php",
			dataType: 'text',
			data: "action=sendMsg&username="+username+"&msgcontent="+msgcontent,
			success: function(data){
				var msg=data.split("@fen");
				if(msg[0]=='1'){
					$("#msg").addClass('success').slideDown('slow').html(msg[1]);
					$('#sending').html('提交成功，正在返回...');
					$("#username").val('');
					$("#msgcontent").val('');
					setTimeout(function() {
						location.replace("default.php");
					}, 2000);
				}else{
					if(msg[1]==''){
						$("#msg").addClass('error').slideDown('slow').html('未知错误');
					}else{
						$("#msg").addClass('error').slideDown('slow').html(msg[1]);
					}					
				}
			}
	    });
		return true;
	}
	
	//删除留言
	function delmsg(id){
		$.ajax({                           
			type: "POST",
			beforeSend:function(){$("#innerTips"+id).addClass('success').slideDown('slow').html("正在删除...");},
			url: "guestbook.php",
			dataType: 'text',
			data: "action=delMsg&Id="+id,
			success: function(data){				
				var msg=data.split("@fen");
				if(msg[0]=='1'){
					$("#innerTips"+id).addClass('success').slideDown('slow').html(msg[1]);
					setTimeout(function() {
						location.replace("default.php");
					}, 3000);
				}else{
					$("#innerTips"+id).removeClass();
					if(msg[1]==''){
						$("#innerTips"+id).addClass('error').slideDown('slow').html('未知错误');
					}else{
						$("#innerTips"+id).addClass('error').slideDown('slow').html(msg[1]);
					}					
				}
			}
	    });
		//return true;
	}
	
	//注销
	function logout(){
		$.ajax({                           
			type: "POST",
			beforeSend:function(){$("#msg").addClass('success').slideDown('slow').html("正在注销...");},
			url: "guestbook.php",
			dataType: 'text',
			data: "action=logout",
			success: function(data){
				var msg=data.split("@fen");
				if(msg[0]=='1'){
					location.replace("default.php");
				}else{
					$("#msg").removeClass();
					if(msg[1]==''){
						$("#msg").addClass('error').slideDown('slow').html('未知错误');
					}else{
						$("#msg").addClass('error').slideDown('slow').html(msg[1]);
					}					
				}
			}
	    });
		//return true;
	}
	
	//换肤
	$(function(){
		var $li =$("#skin li");
		$li.click(function(){
			switchSkin( this.id );
		});
		var cookie_skin = $.cookie( "MyCssSkin");
		if (cookie_skin) {
			switchSkin( cookie_skin );
		}
	});
	function switchSkin(skinName){
		$("#"+skinName).addClass("selected")                 //当前<li>元素选中
			.siblings().removeClass("selected");  //去掉其它同辈<li>元素的选中
		$("#cssfile").attr("href","css/"+ skinName +".css"); //设置不同皮肤
		$.cookie( "MyCssSkin" ,  skinName , { path: '/', expires: 10 });
	}
	
	//打开回复文本域
	function openReply(id){
		if($('#replytxt'+id).text()=='回复'){
			$('#'+id).slideDown('normal');
			$('#replytxt'+id).text('关闭回复');
		}else{
			$('#'+id).slideUp('normal');
			$('#replytxt'+id).text('回复');
		}		
	}
	
	//提交回复
	function chkReply(id){
		var rc=$('#replymsg'+id).val();
		var nowtime=new Date().toLocaleString();
		if(rc==''){ 
			alert('回复内容不能为空');
			return;
		}
		$.ajax({                           
			type: "POST",
			beforeSend:function(){
				$('#bt_sendr'+id).css('display','none');
				$("#replysending"+id).addClass('success').fadeIn('slow').html("正在提交...");
			},
			url: "guestbook.php",
			dataType: 'text',
			data: "action=replyMsg&Id="+id+'&replyContent='+rc,
			success: function(data){
				$("#replysending"+id).removeClass();
				var msg=data.split("@fen");
				if(msg[0]=='1'){
					$("#replysending"+id).addClass('success').fadeIn('slow').html(msg[1]);
					$('#replymsg'+id).val('');
					$('#msgcont'+id).append('<div class="reply">回复：['+nowtime+']<br/>'+rc+'</div>');
					setTimeout(function() {
						$("#replysending"+id).fadeOut('slow').html('');
						$('#bt_sendr'+id).css('display','');
					}, 3000);
				}else{
					if(msg[1]==''){
						$("#replysending"+id).addClass('error').fadeIn('slow').html('未知错误');
					}else{
						$("#replysending"+id).addClass('error').fadeIn('slow').html(msg[1]);
					}					
				}
			}
	    });
	return true;
	}