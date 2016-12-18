<?php
#数据库连接函数
function conn_Db(){
	$link_db=new mysql(WEB_SERVER,WEB_USER,WEB_PWD,WEB_DB,"utf8");
	return $link_db;
}

#字符串处理函数
function checkhtml($text){
	$text=str_replace("<","&lt;",$text);
	$text=str_replace(">","&gt;",$text);
	$text=str_replace("\n","<br />",$text);
	return $text;
}
?>