<?php
@set_time_limit(0);
//error_reporting(E_ALL);
error_reporting(E_ALL || ~E_NOTICE);
include_once "../config.php";
$verMsg = '1.0';	#版本信息
$s_lang = 'utf-8';	#语言编码
$dfDbname = 'board';	#数据库名称
$errmsg = '';
$insLockfile = '../libs/install_lock.txt';
$moduleCacheFile = dirname(__FILE__).'/modules.tmp.inc';

define('DEDEINC',dirname(__FILE__).'/../include');
define('DEDEDATA',dirname(__FILE__).'/../data');
define('Tun2ROOT',ereg_replace("[\\/]install",'',dirname(__FILE__)));
header("Content-Type:text/html;charset=utf-8");
require_once(Tun2ROOT.'/install/install.inc.php');

foreach(Array('_GET','_POST','_COOKIE') as $_request)
{
	 foreach($$_request as $_k => $_v) ${$_k} = RunMagicQuotes($_v);
}


if( file_exists('../libs/install_lock.txt') )
{
	exit(" 程序已运行安装，如果你确定要重新安装，请先从FTP中删除 libs/install_lock.txt！");
}

if(empty($step))
{
	$step = 1;
}
/*------------------------
使用协议书
function _1_Agreement()
------------------------*/
if($step==1)
{
	include('./templates/step-1.html');
	exit();
}
/*------------------------
环境测试
function _2_TestEnv()
------------------------*/
else if($step==2)
{
	 $phpv = phpversion();
	 $sp_os = @getenv('OS');
	 $sp_gd = gdversion();
	 $sp_server = $_SERVER['SERVER_SOFTWARE'];
	 $sp_host = (empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_HOST'] : $_SERVER['REMOTE_ADDR']);
	 $sp_name = $_SERVER['SERVER_NAME'];
	 $sp_max_execution_time = ini_get('max_execution_time');
	 if (substr(PHP_VERSION, 0, 1) == '5') {
            $sp_php_version= "<font color=#0588c1>[√]".phpversion()."</font>";
         } else {
			$sp_php_version= "<font color=red>[×]Off</font>" ;
         } 
	 $sp_allow_reference = (ini_get('allow_call_time_pass_reference') ? '<font color=#0588c1>[√]On</font>' : '<font color=red>[×]Off</font>');
     $sp_safe_mode = (ini_get('safe_mode') ? '<font color=red>[×]On</font>' : '<font color=#0588c1>[√]Off</font>');
     $sp_gd = ($sp_gd>0 ? '<font color=#0588c1>[√]On</font>' : '<font color=red>[×]Off</font>');
     $sp_mysql = (function_exists('mysql_connect') ? '<font color=#0588c1>[√]On</font>' : '<font color=red>[×]Off</font>');

   if($sp_mysql=='<font color=red>[×]Off</font>')
   {
   		$sp_mysql_err = true;
   }
   else
   {
   		$sp_mysql_err = false;
   }

   $sp_testdirs = array(
        '/',
        '/libs/*',
        '/install',

        
   );
	 include('./templates/step-2.html');
	 exit();
}
/*------------------------
设置参数
function _3_WriteSeting()
------------------------*/
else if($step==3)
{
  if(!empty($_SERVER['REQUEST_URI']))
  {
  	$scriptName = $_SERVER['REQUEST_URI'];
  }
  else
  {
  	$scriptName = $_SERVER['PHP_SELF'];
  }

  $basepath = eregi_replace('/install(.*)$','',$scriptName)."/";

  if(empty($_SERVER['HTTP_HOST']))
  {
  	$baseurl = 'http://'.$_SERVER['HTTP_HOST'];
  }
  else
  {
  	$baseurl = "http://".$_SERVER['SERVER_NAME'];
  }

  include('./templates/step-3.html');
	exit();
}
/*------------------------
普通安装
function _4_Setup()
------------------------*/
else if($step==4)
{

  $conn = mysql_connect($dbhost,$dbuser,$dbpwd) or die("<script>alert('数据库服务器或登录密码无效，\\n\\n无法连接数据库，请重新设定！');history.go(-1);</script>");
  mysql_query("SET NAMES utf8"); 
   
  mysql_query("CREATE DATABASE IF NOT EXISTS `".$dbname."`;",$conn);	#创建数据库

  mysql_select_db($dbname) or die("<script>alert('选择数据库失败，可能是你没权限，请预先创建一个数据库！');history.go(-1);</script>");

  //获得数据库版本信息


  $fp = fopen(dirname(__FILE__)."/config.php","r");
  $configStr1 = fread($fp,filesize(dirname(__FILE__)."/config.php"));
  fclose($fp);

  //common.inc.php
    $configStr1 = str_replace("~webname~",$webname,$configStr1);
	$configStr1 = str_replace("~dbhost~",$dbhost,$configStr1);
	$configStr1 = str_replace("~dbname~",$dbname,$configStr1);
	$configStr1 = str_replace("~dbuser~",$dbuser,$configStr1);
	$configStr1 = str_replace("~dbprefix~",$dbprefix,$configStr1);
	$configStr1 = str_replace("~dbpwd~",$dbpwd,$configStr1);

  @chmod(Tun2ROOT.'/',0777);
  
  $fp = fopen(Tun2ROOT."/config.php","w") or die("<script>alert('写入配置失败，请检查../目录是否可写入！');history.go(-1);</script>");
  fwrite($fp,$configStr1);
  fclose($fp);
  
  //创建数据表
  
  $query = '';
  $fp = fopen(dirname(__FILE__).'/sql.txt','r');
	while(!feof($fp))
	{
		$query.=str_replace('#@_',$dbprefix,fgets($fp));
	}
		$arr = preg_split("/[;]+/",$query,-1,PREG_SPLIT_NO_EMPTY);

             foreach($arr as $path){
                          mysql_query($path,$conn);
              } 

	fclose($fp);

	//增加管理员帐号
	$adminquery = "INSERT INTO `{$dbprefix}admin` (`adminName`, `adminPwd`) VALUES
('$adminuser', '".md5($adminpwd)."');";
	mysql_query($adminquery,$conn);
	
	
    mysql_close($conn);


  	//锁定安装程序
  	$fp = fopen($insLockfile,'w');
  	fwrite($fp,'Simple Board - 简易留言板,安装成功！http://blog.sina.com.cn/webtechnology');
  	fclose($fp);
  	include('./templates/step-4.html');
  	exit();
}

/*------------------------
检测数据库是否有效
function _10_TestDbPwd()
------------------------*/
else if($step==10)
{
	header("Pragma:no-cache\r\n");
  header("Cache-Control:no-cache\r\n");
  header("Expires:0\r\n");
	$conn = @mysql_connect($dbhost,$dbuser,$dbpwd);
	if($conn)
	{
	  $rs = mysql_select_db($dbname,$conn);
	  if(!$rs)
	  {
		   $rs = mysql_query(" CREATE DATABASE `$dbname`; ",$conn);
		   if($rs)
		   {
		  	  mysql_query(" DROP DATABASE `$dbname`; ",$conn);
		  	  echo "<font color='#0588c1'>信息正确</font>";
		   }
		   else
		   {
		      echo "<font color='red'>数据库不存在，也没权限创建新的数据库！</font>";
		   }
	  }
	  else
	  {
		    echo "<font color='#0588c1'>信息正确</font>";
	  }
	}
	else
	{
		echo "<font color='red'>数据库连接失败！</font>";
	}
	@mysql_close($conn);
	exit();
}
?>
