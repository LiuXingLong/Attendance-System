<?php
/**
*author:zhuwei313@hotmail.com
*github:@daheige
*time:2014-12-13
*dec:create qr pic
*/
include 'phpqrcode/qrlib.php';
// create pic
// $text,$filename,errorCorrectionLevel(L,M,Q,H),matrixPointSize(1,10)表示规格大小
/**
 * L-默认：可以识别已损失的7%的数据
 * M-可以识别已损失15%的数据
 * Q-可以识别已损失25%的数据
 * H-可以识别已损失30%的数据
 */

$pic4 = "my4.jpg";
$pic5 = "my5.jpg";
$pic6 = "my6.jpg";
$pic7 = "my7.jpg";
$pic10 = "my10.jpg";
// 生成不同大小的二维码
QRcode::png ( 'http ://www.baidu.com', $pic4, 'M', 4 );
QRcode::png ( 'http://www.baidu.com', $pic5, 'M', 5 );
QRcode::png ( 'http://www.baidu.com', $pic6, 'M', 6 );
QRcode::png ( 'http://www.baidu.com', $pic7, 'M', 7 );
QRcode::png ( urldecode ( urlencode ( '你在那呢？' ) ), $pic10, 'M', 10 );

// 创建图片可以是png,jpg格式的
// 将图片重定向到浏览器
// QRcode::png('http://www.baidu.com');//重定向到浏览器
echo "<img src='$pic4'/><br/>";
echo "<img src='$pic5'/><br/>";
echo "<img src='$pic6'/><br/>";
echo "<img src='$pic7'/><br/>";
echo "<img src='$pic10'/><br/>";
?>