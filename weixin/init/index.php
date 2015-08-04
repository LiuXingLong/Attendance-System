<html>
<meta charset="UTF-8" />
<head>
<title>安装</title>

<style>
.baseTable {
	background: none repeat scroll 0 0 #000000;
}

.baseTable tr td {
	background: none repeat scroll 0 0 #FFFFFF;
	font-size: 12px;
	padding: 2px 8px;
}
</style>
</head>

<body>
<form action="install.php" method="post">
<span>请先按照安装手册获取微信公众平台提供的AppID和AppSecret,并输入对应位置,点击下一步</span>
		<table class="baseTable" cellspacing="1" cellpadding="0" border="0">
			<tr>
				<td>AppID:</td>
				<td><input type="text" name="AppID" /></td>
			</tr>
			<tr>
				<td>AppSecret:</td>
				<td><input type="text" name="AppSecret"/></td>
			</tr>
			<tr>
				<td>获取微信公众号[二维码]</td>
				<td><input type="submit" value="下一步"></td>
			</tr>
		</table>
</form>

</body>
</html>