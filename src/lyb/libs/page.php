<?php
/*
+-----------------------------------------------------------------------
|	文件概要：分页显示函数
|	文件名称：page.php
|	创建时间：2010-5-20
+-----------------------------------------------------------------------
*/
	function _PAGEFT($totle, $displaypg = 20, $url = '') {

		global $page, $firstcount, $pagenav, $_SERVER;

		$GLOBALS["displaypg"] = $displaypg;

		if (!$page)
			$page = 1;
		if (!$url) {
			$url = $_SERVER["REQUEST_URI"];
		}

		//URL分析：
		$parse_url = parse_url($url);
		$url_query = $parse_url["query"]; //单独取出URL的查询字串
		if ($url_query) {
			$url_query = preg_replace("/(^|&)page=$page/", "", $url_query);
			$url = str_replace($parse_url["query"], $url_query, $url);
			if ($url_query)
				$url .= "&page";
			else
				$url .= "page";
		} else {
			$url .= "?page";
		}
		$lastpg = ceil($totle / $displaypg); //最后页，也是总页数
		$page = min($lastpg, $page);
		$prepg = $page -1; //上一页
		$nextpg = ($page == $lastpg ? 0 : $page +1); //下一页
		$firstcount = ($page -1) * $displaypg;

		//开始分页导航条代码：
		$pagenav = "显示第 <B>" . ($totle ? ($firstcount +1) : 0) . "</B>-<B>" . min($firstcount + $displaypg, $totle) . "</B> 条记录&nbsp;&nbsp;共 <font color='red'>$totle</font> 条留言";

		//如果只有一页则跳出函数：
		if ($lastpg <= 1)
			return false;

		$pagenav .= " <a href='$url=1'>首页</a> ";
		if ($prepg)
			$pagenav .= " <a href='$url=$prepg'>前页</a> ";
		else
			$pagenav .= " 前页 ";
		if ($nextpg)
			$pagenav .= " <a href='$url=$nextpg'>后页</a> ";
		else
			$pagenav .= " 后页 ";
		$pagenav .= " <a href='$url=$lastpg'>尾页</a> ";

		//下拉跳转列表，循环列出所有页码：
		$pagenav .= "　到第 <select class='border:1px solid #98CCFA;' name='topage' size='1' onchange='window.location=\"$url=\"+this.value'>\n";
		for ($i = 1; $i <= $lastpg; $i++) {
			if ($i == $page)
				$pagenav .= "<option value='$i' selected>$i</option>\n";
			else
				$pagenav .= "<option value='$i'>$i</option>\n";
		}
		$pagenav .= "</select> 页&nbsp;&nbsp;&nbsp;共 <font color='red'>$lastpg</font> 页";
	}
?>
