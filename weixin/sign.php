<?php
$config['username']="root";
$config['password']="root";
$config['host']="127.0.0.1";
$config['port']="80";
$config['dbname']="signer";
define("CONFIG_HOST",$config['host']);
define("CONFIG_USERNAME",$config['username']);
define("CONFIG_PASSWORD",$config['password']);
define("CONFIG_DBNAME",$config['dbname']);
?>
<?php
function get_person_weizhi($per_uid){
	$con=mysqli_connect(CONFIG_HOST,CONFIG_USERNAME,CONFIG_PASSWORD,CONFIG_DBNAME);
	if(mysqli_connect_errno($con)){
		echo "Failed to connect to MySQL:".mysqli_connect_error();
	}
	mysqli_set_charset($con,"utf8");
	$sql="SELECT Latitude,Longitude from person where per_uid={$per_uid}";// 根据	$per_uid 查找
	//$sql="select Latitude,Longitude from person where per_id in(select per_id from bind where open_id='$openID')";// 根据	$openID 查找
	$weizhi=mysqli_query($con,$sql);
	$row=mysqli_fetch_array($weizhi);
	//var_dump($row);
	return $row;
}
function get_manager_weizhi($mng_id){
	$con=mysqli_connect(CONFIG_HOST,CONFIG_USERNAME,CONFIG_PASSWORD,CONFIG_DBNAME);
	if(mysqli_connect_errno($con)){
		echo "Failed to connect to MySQL:".mysqli_connect_error();
	}
	mysqli_set_charset($con,"utf8");
	$sql="SELECT Latitude,Longitude from manager where mng_id={$mng_id}";// 根据	$per_uid 查找
	//$sql="select Latitude,Longitude from person where per_id in(select per_id from bind where open_id='$openID')";// 根据	$openID 查找
	$weizhi=mysqli_query($con,$sql);
	$row=mysqli_fetch_array($weizhi);
	//var_dump($row);
	return $row;
}
function chuli_weizhi($wz1,$wz2){
	if($wz1[0]==null||$wz2[0]==null) return false;
	$myLat = $wz1[0];//接收到的当前位置的纬度  
	$myLng = $wz1[1];//接收到的当前位置的经度  
	$range = 180 / pi() * 0.15 / 6372.797;     //里面的 1 就代表搜索 1km 之内，单位km  
	$lngR = $range / cos($myLat * pi() / 180); 
	$maxLat = $myLat + $range;//最大纬度  
	$minLat = $myLat - $range;//最小纬度  
	$maxLng = $myLng + $lngR;//最大经度  
    $minLng = $myLng - $lngR;//最小经度  
	if($wz2[0]>=$minLat&&$wz2[0]<=$maxLat&&$wz2[1]>=$minLng&&$wz2[1]<=$maxLng){
		return true;
	}
	else return false;
}
/**
 * main_weizhi($wz1,$per_uid);
 * 
 *处理位置信息
 * 
 * 参数 
 * $wz1   点到者位置
 * $per_uid 签到者信息
 * 返回  
 * 
 * 在规定范围类返回     true
 * 不在规定范围类返回   false；
 */
function main_weizhi($wz1,$per_uid){
	$wz2=get_person_weizhi($per_uid);
	return chuli_weizhi($wz1,$wz2);
}
?>
<?php
if(empty($_GET['rand']) || empty($_GET['per_id']) ){
	return;
}
session_id(substr($_GET['rand'], 32));
//echo substr($_GET['rand'], 32);
session_start();
echo signByCode();
function signByCode(){
		$code2 = $_SESSION["code2"];
		$con=mysqli_connect("localhost","root","root","signer");
		if(mysqli_connect_errno($con)){
			echo "Failed to connect to:".mysqli_connect_error();
		}
		mysqli_set_charset($con,"utf8");
		$sql="select qingjia,flag from person where per_id={$_GET['per_id']}";
		$row=mysqli_query($con,$sql);
		$row=mysqli_fetch_array($row);		
		if($row[1]==0){
			mysqli_close($con);
			return "签到尚未开始!请稍后...";
		}else if(!empty($row[0])){
			mysqli_close($con);
			return "您已申请请假过了，无法再通过扫描签到了。";
		}
		else if(empty($code2['isstart'])){
			return "二维码信息错误...";
		}
		else if(empty($_GET['per_id'])){
			 return "操作失败！请重新尝试！";
		}		
		$per_id = $_GET['per_id'];
		$mng_id = $_SESSION['mng_id'];
		$tnt_id = $_SESSION['tnt_id'];		
		$wz1=get_manager_weizhi($mng_id);		
		$code2 = $_SESSION["code2"];		
		$deps = $code2['deps'];
		foreach ($deps as $k=>$v){
			foreach ($v as $k1=>$v1){					
				if($k1==$per_id){
					$per_uid=$v1['per_uid'];
					if(main_weizhi($wz1,$per_uid))$deps[$k][$k1]['iscome']=true;
					else{

						  return "你可能不在签到地点或者未上传你的地理位置，请重新上传位置后再扫描二维码签到！";
					} 			
				}
			}
		}
		$code2['deps'] = $deps;	
		$_SESSION["code2"]=$code2;
		return "签到成功";
	}
?>