<?php
/**
 * 系统的全局参数文件，包含系统所有要访问的类
 */
$LOCAL_HOST="http://loveme1234567.oicp.net/";    //  http://loveme1234567.oicp.net/     http://www.hnustcotrip.cn/

define("BASE_URL", $LOCAL_HOST);
define("SIGN_URL", BASE_URL."Manager/Manager/signByCode");
define("BIND_URL", BASE_URL."Manager/Weixin/bind");
define("SIGN_REQUEST",BASE_URL."weixin/sign.php");
?>