<?php
/*
+-----------------------------------------------------------------------
|	文件概要：MYSQL数据库连接类
|	文件名称：mysql.class.php
|	创建时间：2010-9-7
+-----------------------------------------------------------------------
*/
class mysql {
	private $server; //服务器名
	private $user; //数据库用户名
	private $password; //数据库密码
	private $database; //数据库名
	private $link; //MYSQL连接标识符
	private $charset = "utf8"; //数据库编码,默认为UTF8

	/*=====================================================
	 * 方法:__construct
	 * 功能:构造函数
	 * 参数:$server,$user,$password,$database,$charset
	 * 说明:实例化时自动连接数据库.
	 ====================================================*/
	function __construct($server, $user, $password, $database, $charset) {
		$this->server = $server;
		$this->user = $user;
		$this->password = $password;
		$this->database = $database;
		$this->charset = $charset;
		$this->connect();
	}

	/*====================================================
	 * 方法:connect
	 * 功能:连接数据库
	 * 参数:无
	 * 说明:连接MYSQL服务器,连接数据库,设置字符编码
	 ===================================================*/
	function connect() {
		$this->link = mysql_connect($this->server, $this->user, $this->password) or die($this->error("数据库服务器连接出错!"));
		mysql_select_db($this->database, $this->link) or die($this->error("数据库连接出错!"));
		mysql_query("set names '$this->charset'");
	}

	/*===================================================
	 * 方法:query
	 * 功能:执行SQL
	 * 参数:$sql
	 * 说明:对传过来的SQL语句执行,并返回结果$result资源标识符
	 ==================================================*/
	function query($sql) {
		$result = mysql_query($sql, $this->link);
		if (!$result) {
			$this->error($sql . "语句执行失败!");
			return false;
		} else {
			return $result;
		}
	}	
	
	/*===================================================
	 * 方法:fetcharray
	 * 功能:从结果集中取一行做为数组
	 * 参数:$result资源标识符
	 * 说明:需要提供SQL语句执行返回的资源标识符
	 ==================================================*/
	function fetcharray($result) {
		return mysql_fetch_array($result);
	}

	/*===================================================
	 * 方法:fetchall
	 * 功能:从结果集中取出所有记录做为二维数组$arr
	 * 参数:$result资源标识符
	 * 说明:循环取所有记录保存为$arr
	 ==================================================*/
	function fetchall($result) {
		$arr[] = array ();
		while ($row = mysql_fetch_array($result)) {
			$arr[] = $row;
		}
		mysql_free_result($result);
		return $arr;
	}

	/*===================================================
	 * 方法:numrows
	 * 功能:统计结果集中记录数
	 * 参数:$result资源标识符
	 * 说明:统计行数
	 ==================================================*/
	function numrows($result) {
		return mysql_num_rows($result);
	}

	/*===================================================
	 * 方法:numfields
	 * 功能:统计结果集中字段数
	 * 参数:$result资源标识符
	 * 说明:统计字段数
	 ==================================================*/
	function numfields($result) {
		return mysql_num_fields($result);
	}

	/*===================================================
	 * 方法:affectedrows
	 * 功能:取得前一次MySQL操作所影响的记录行数
	 * 参数:无
	 * 说明:取得前一次MySQL操作所影响的记录行数
	 ==================================================*/
	function affectedrows() {
		return mysql_affected_rows($this->link);
	}

	/*===================================================
	 * 方法:version
	 * 功能:取得MYSQL版本
	 * 参数:无
	 * 说明:取得当前数据库服务器MYSQL的版本
	 ==================================================*/
	function version() {
		return mysql_get_server_info();
	}

	/*===================================================
	 * 方法:insertid
	 * 功能:取得上一步INSERT操作产生的ID
	 * 参数:无
	 * 说明:取得上一步INSERT操作产生的自增字段ID
	 ==================================================*/
	function insertid() {
		return mysql_insert_id($this->link);
	}
	
	/*===================================================
	 * 方法:checksql
	 * 功能:检查SQL语句
	 * 参数:SQL语句
	 * 说明:关闭非永久数据库连接
	 ==================================================*/
	function checksql($db_string, $querytype = 'select') {
        $clean = '';
        $old_pos = 0;
        $pos = - 1;

        //如果是普通查询语句，直接过滤一些特殊语法
        if ($querytype == 'select') {
            $notallow1 = "[^0-9a-z@\._-]{1,}(union|sleep|benchmark|load_file|outfile)[^0-9a-z@\.-]{1,}";

            //$notallow2 = "--|/\*";
            if (eregi ( $notallow1, $db_string )) {
                exit ( "<font size='5' color='red'>Safe Alert: Request Error step 1 !</font>" );
            }
        }

        //完整的SQL检查
        while ( true ) {
            $pos = strpos ( $db_string, '\'', $pos + 1 );
            if ($pos === false) {
                break;
            }
            $clean .= substr ( $db_string, $old_pos, $pos - $old_pos );
            while ( true ) {
                $pos1 = strpos ( $db_string, '\'', $pos + 1 );
                $pos2 = strpos ( $db_string, '\\', $pos + 1 );
                if ($pos1 === false) {
                    break;
                } elseif ($pos2 == false || $pos2 > $pos1) {
                    $pos = $pos1;
                    break;
                }
                $pos = $pos2 + 1;
            }
            $clean .= '$s$';
            $old_pos = $pos + 1;
        }
        $clean .= substr ( $db_string, $old_pos );
        $clean = trim ( strtolower ( preg_replace ( array ('~\s+~s' ), array (' ' ), $clean ) ) );

        //老版本的Mysql并不支持union，常用的程序里也不使用union，但是一些黑客使用它，所以检查它
        if (strpos ( $clean, 'union' ) !== false && preg_match ( '~(^|[^a-z])union($|[^[a-z])~s', $clean ) != 0) {
            $fail = true;
        }

        //发布版本的程序可能比较少包括--,#这样的注释，但是黑客经常使用它们
        elseif (strpos ( $clean, '/*' ) > 2 || strpos ( $clean, '--' ) !== false || strpos ( $clean, '#' ) !== false) {
            $fail = true;
        }

        //这些函数不会被使用，但是黑客会用它来操作文件，down掉数据库
        elseif (strpos ( $clean, 'sleep' ) !== false && preg_match ( '~(^|[^a-z])sleep($|[^[a-z])~s', $clean ) != 0) {
            $fail = true;
        } elseif (strpos ( $clean, 'benchmark' ) !== false && preg_match ( '~(^|[^a-z])benchmark($|[^[a-z])~s', $clean ) != 0) {
            $fail = true;
        } elseif (strpos ( $clean, 'load_file' ) !== false && preg_match ( '~(^|[^a-z])load_file($|[^[a-z])~s', $clean ) != 0) {
            $fail = true;
        } elseif (strpos ( $clean, 'into outfile' ) !== false && preg_match ( '~(^|[^a-z])into\s+outfile($|[^[a-z])~s', $clean ) != 0) {
            $fail = true;
        }

        //老版本的MYSQL不支持子查询，我们的程序里可能也用得少，但是黑客可以使用它来查询数据库敏感信息
        elseif (preg_match ( '~\([^)]*?select~s', $clean ) != 0) {
            $fail = true;
        }
        if (! empty ( $fail )) {
            exit ( "<font size='5' color='red'>Safe Alert: Request Error step 2!</font>" );
        } else {
            return $db_string;
        }
    }

	/*===================================================
	 * 方法:close
	 * 功能:关闭连接
	 * 参数:无
	 * 说明:关闭非永久数据库连接
	 ==================================================*/
	function close() {
		mysql_close($this->link);
	}

	/*===================================================
	 * 方法:error
	 * 功能:提示错误
	 * 参数:$err_msg
	 * 说明:对给出的错误提示内容给予ECHO
	 ==================================================*/
	function error($err_msg = "") {
		if ($err_msg == "") {
			echo "Errno:" . mysql_errno . "</br>";
			echo "Error:" . mysql_error . "</br>";
		} else {
			echo $err_msg;
		}
	}

	/*===================================================
	 * 方法:__destruct
	 * 功能:析构函数
	 * 参数:无
	 * 说明:释放类,关闭连接
	 ==================================================*/
	function __destruct() {
		$this->close();
	}
}
?>
