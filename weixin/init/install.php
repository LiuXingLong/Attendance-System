<html>
<meta charset="UTF-8" />
<head>
<title>安装向导</title>
<link href="res/cotrip.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="res/cotrip.js"></script>
</head>

<body>
<?php
require_once 'Token.php';
require_once 'Menu.class.php';

if(empty($_POST['AppID']) || empty($_POST['AppSecret'])){
	echo "非法访问";
}
$AppID = $_POST['AppID'];
$AppSecret = $_POST['AppSecret'];

$token = new Token();
$access_token = $token->getToken($AppID, $AppSecret);
if($access_token != false){
	$ticket = getTicket($access_token);
	if($ticket != false){
		$menu = new Menu($access_token);
		$res = $menu->createDefaultMenu() ? "菜单创建成功" : "菜单创建失败";
		echo $res. "<br/>";
		echo "以下为你的微信公众账号的永久二维码访问链接和图片,请妥善保存<br/>";
		echo "<font color=\"red\">[重要提示]:由于微信服务器每天限制动态获取二维码,请不要频繁刷新本页面，以免造成微信服务器拒绝服务</font></br>";
		echo "<font color=\"blue\">若要分享你的微信公众账号,请复制下面图片或者分享下面链接即可获取公众号永久二维码</font></br>";
		echo "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket={$ticket}";
		echo "<img src=\"https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket={$ticket}\"/>";
	}else {
		echo "系统初始化失败,请检查微信“AppID”和“AppSecret”是否正确。";
	}	
}

function getTicket($access_token){
	$uri = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$access_token;
	$data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": 100000}}}';
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $uri );
		curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt ( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)' );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt ( $ch, CURLOPT_AUTOREFERER, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
	$tmpInfo = curl_exec ( $ch );
	if (curl_errno ( $ch )) {
		return curl_error ( $ch );
	}
	curl_close ( $ch );
	$result = json_decode ( $tmpInfo, true );
	$res = empty($result['ticket']) ? false : $result['ticket'];
	return $res;
}
?>
</body>
</html>