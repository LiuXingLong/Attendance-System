<?php
date_default_timezone_set('PRC');
$config['username'] = "root";
$config['password'] = "root";
$config['host'] = "localhost";
$config['port'] = "80";
$config['dbname'] = "signer";

$LOCAL_HOST = "http://loveme1234567.oicp.net/"; //  http://loveme1234567.oicp.net/     http://www.hnustcotrip.cn/

define("BASE_URL", $LOCAL_HOST);
define("SIGN_URL", BASE_URL . "Manager/Manager/signByCode");
define("BIND_URL", BASE_URL . "Manager/Weixin/bind");
define("SIGN_REQUEST", BASE_URL . "weixin/sign.php");
define("CONFIG_HOST", $config['host']);
define("CONFIG_USERNAME", $config['username']);
define("CONFIG_PASSWORD", $config['password']);
define("CONFIG_PORT", $config['port']);
define("CONFIG_DBNAME", $config['dbname']);

define("NO_STATUS", 0);
define("EXIT_ORDER", "exit");
define("CDM_1_1", 111);
define("CDM_1_2", 112);
define("TOKEN", "weixin");

$wechatObj = new DengmiCallbackapi();
if (isset ($_GET['echostr'])) {
	/**
		* 首次设置绑定微信公众号设置的  URL  此后不再调用
		*/
	$wechatObj->valid();
} else {
	$wechatObj->responseMsg();
}
?>
<?php
/**
 * CotripCallbackapi类为整个系统的核心类，处理系统的主要逻辑
 */
class DengmiCallbackapi {
	/**
	 * 主要接受微信服务器发送的请求以验证域名的正确性
	 */
	private $client;
	private $openid;
	private $sc_mi;
	private $sc_user;
	private $connection;
	/**
	 * 构造函数自在首次 new 时调用一次
	 */
	public function __construct() {
		$this->client = new ClientTextRequest(0, 0);
		$this->connection = mysql_connect(CONFIG_HOST, CONFIG_USERNAME, CONFIG_PASSWORD);
		mysql_select_db(CONFIG_DBNAME, $this->connection);
		mysql_query("set names utf8", $this->connection);
	}
	/**
		* 首次设置绑定微信公众号设置的  URL
		*/
	public function valid() {
		$echoStr = $_GET["echostr"];
		if ($this->checkSignature()) {
			echo $echoStr;
			exit ();
		}
	}
	/**
	 * 经过一定的运算将结果返回个微信服务器以验证微信自定义服务器URL是否正确
	 * 调用方式 $this->checkSignature();
	 * @return boolean 成功返回true 失败返回false
	 */
	private function checkSignature() {
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];

		$token = TOKEN;
		$tmpArr = array (
			$token,
			$timestamp,
			$nonce
		);
		sort($tmpArr);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		if ($tmpStr == $signature) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 用来判断是否绑定
	 * 出入参数 $openID
	 * 调用方式 $this->noYesbang($openID);
	 * 绑定返回 1  未绑定返回 0
	 * 
	 */
	private function noYesbang($openID) {
		$con = mysqli_connect(CONFIG_HOST, CONFIG_USERNAME, CONFIG_PASSWORD, CONFIG_DBNAME);	
		if (mysqli_connect_errno($con)) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		mysqli_set_charset($con, "utf8");
		$result = mysqli_query($con, "SELECT * FROM bind where open_id='$openID'");
		$rowcount = mysqli_num_rows($result);
		mysqli_free_result($result);
		mysqli_close($con);
		return $rowcount;
	}
	/**
	 * 检测普通用户是否实名认证
	 * 出入参数 $openID
	 * 调用方式 $this->renzheng($openID);
	 * 绑定返回 1  未绑定返回 0
	 */
	private function renzheng($openID){
		$con=mysqli_connect(CONFIG_HOST,CONFIG_USERNAME,CONFIG_PASSWORD,CONFIG_DBNAME);
		if(mysqli_connect_errno($con)){
			echo "Failed to connect to MySQL: ".mysqli_connect_error();
		}
		mysqli_set_charset($con, "utf8");
		if($_SESSION['wx_note']==0){
			$sql="select per_photo from person where per_id in(select per_id from bind where open_id='$openID')";
			$result=mysqli_query($con,$sql);
			$row=mysqli_fetch_array($result);
		}
		else $row[0]=1;
		mysqli_free_result($result);
		mysqli_close($con);
		if($row[0]==null) return 0;
		else return 1;		
	}
	/**
	 *传入变量将在客户的输出变量值用来测试数据代码
	 *传入参数  $canshu
	 *调用方式  $this->cout($canshu);
	 */
	public function cout($shuju) {
		$resultStr = $this->client->sprintf($shuju);
		echo $resultStr;
		exit ();
	}
	/**
	 * 点到者 更改位置信息
	 */
	public function actionLocation(){
		$con = mysqli_connect(CONFIG_HOST, CONFIG_USERNAME, CONFIG_PASSWORD, CONFIG_DBNAME);
		if (mysqli_connect_errno($con)) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		mysqli_set_charset($con, "utf8");						
		$sql = "UPDATE manager SET Latitude='{$_SESSION['Latitude']}',Longitude='{$_SESSION['Longitude']}' where mng_uid={$_SESSION['wx_per_id']}";			
		$result = mysqli_query($con, $sql);	
		mysqli_close($con);
		return "您当前的位置为:\n纬度{$_SESSION['Latitude']}\n经度{$_SESSION['Longitude']}\n以成功更新您的位置···";
	}
	/**
	 * 用户 上传的位置信息
	 */
	public function userLocation(){
		$con = mysqli_connect(CONFIG_HOST, CONFIG_USERNAME, CONFIG_PASSWORD, CONFIG_DBNAME);
		if (mysqli_connect_errno($con)) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		mysqli_set_charset($con, "utf8");						
		$sql = "UPDATE person SET Latitude='{$_SESSION['Latitude']}',Longitude='{$_SESSION['Longitude']}' where per_id={$_SESSION['wx_per_id']}";			
		$result = mysqli_query($con, $sql);	
		mysqli_close($con);
		return "您当前的位置为:\n纬度{$_SESSION['Latitude']}\n经度{$_SESSION['Longitude']}\n以成功上传···";
	}
	/**
	 * 返回 用户签到状态
	 */
	private function userflag(){
		$con = mysqli_connect(CONFIG_HOST, CONFIG_USERNAME, CONFIG_PASSWORD, CONFIG_DBNAME);
		if (mysqli_connect_errno($con)) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		mysqli_set_charset($con, "utf8");
		$result=mysqli_query($con,"SELECT flag FROM person WHERE per_id={$_SESSION['wx_per_id']}");
		$row = mysqli_fetch_array($result);
		mysqli_close($con);
		return $row['flag'];
	}
	/*
	防注入写法，还没确认
	private function userfalg2(){
		$con = mysqli_connect(CONFIG_HOST, CONFIG_USERNAME, CONFIG_PASSWORD, CONFIG_DBNAME);
		if ($conn->connect_error) {
    		die("Connection failed: " . $conn->connect_error);
		}		
		$stmt = $conn->prepare("SELECT falg FROM person WHERE per_id=(openID)values(?)");
		$stmt->bind_param("i", $openID);
		$openID=$_SESSION['wx_per_id'];	
		$result=$stmt->execute();		
		$stmt->close();
		$conn->close();
		return $result;
	}
	*/
	
	/**
	 * 处理系统的主要业务逻辑的方法
	 * 将用户请求进行解析并根据不同请求调用不同接口
	 */
	public function responseMsg() {
		$postStr = file_get_contents("php://input");		
		/*
		 //测试用  将微信发来的所有信息存到数据库	 
		$con = mysqli_connect(CONFIG_HOST, CONFIG_USERNAME, CONFIG_PASSWORD, CONFIG_DBNAME);
		if (mysqli_connect_errno($con)) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		mysqli_set_charset($con, "utf8");
		mysqli_query($con,"insert into test values(0,'$postStr')");
		mysqli_close($con);			
		exit();
		*/
		$resultStr = "";
		if (!empty ($postStr)) {	
			$res = "";					
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);		
			$fromUsername = $postObj->FromUserName;
			$toUsername = $postObj->ToUserName;
			
			//$Latitude=$postObj->Latitude;
			//$Longitude=$postObj->Longitude;
					
			$Latitude=floatval($postObj->Latitude);
			$Longitude=floatval($postObj->Longitude);
									
			session_id($fromUsername); //  存入  session 编号
			session_start();
							
			$this->client = new ClientTextRequest($fromUsername, $toUsername);
			$this->openid = $fromUsername;
			if (empty ($_SESSION['wx_per_id'])) {
				$result_arr = mysql_query("select * from bind where open_id='$fromUsername'", $this->connection);
				$result_arr = mysql_fetch_array($result_arr);
				if(!empty($result_arr)){
					$_SESSION['wx_per_id'] = $result_arr['per_id'];
					$_SESSION['wx_tnt_id'] = $result_arr['tnt_id'];
					$_SESSION['wx_note']=$result_arr['note'];
					$_SESSION['bangding']=1;
				}
			}							
			//$rowcount = $this->noYesbang($fromUsername);
			//$row=$this->renzheng($fromUsername);
			if(empty($_SESSION['renzheng'])){
				$row=$this->renzheng($fromUsername);
				if($row==1) $_SESSION['renzheng']=1;
			}		
			$keyword = $postObj->EventKey;
			$event = $postObj->Event;
			if($event == LOCATION){
				if($_SESSION['renzheng']==1){
					$_SESSION['Latitude']=$Latitude;
					$_SESSION['Longitude']=$Longitude;
				}
			}
			$RX_TYPE = trim($postObj->MsgType); //消息类型		
			if(($_SESSION['bangding']==1&&$_SESSION['renzheng']==1)||$keyword == 301 ||$keyword == 302 ||$event == subscribe || $event == LOCATION||$RX_TYPE==image){	
				switch ($RX_TYPE) {
					case "text" :
						$res = $this->handleText($postObj);
						break;
					case "event" :
						$res = $this->handleEvent($postObj);
						break;
					case "image" :				
						if($_SESSION['pic_sysphoto']==1){
						   unset($_SESSION['pic_sysphoto']); 													 						   	
						   if($_SESSION['key']==102){
						   		unset($_SESSION['key']);
								$flag=$this->userflag();
								if($_SESSION['wx_note']==1){
									$res = "此功能仅为普通用户提供···";
								}	
						   		else if($flag==1){
						   			$res=$this->getphoto($postObj);	
						   		}							   							   						   								   						   	
						   		else{
						   			$res = "还未到签到时间···";
						   		} 					   									   						   	
						   }else if($_SESSION['key']==201){	
						   		unset($_SESSION['key']);					   							  							   						   							   						   	
						   		$res=$this->leave($postObj);							   									   					   	
						   }else if($_SESSION['key']==302){	
						   		unset($_SESSION['key']);					   							  							   						   							   						   	
						   		if($_SESSION['bangding']==1) $res=$this->renzhen($postObj);
						   		else $res = "提醒：您未绑定，请通过配置中的绑定功能绑定后再使用！"; 							   									   					   	
						   }	   
						}else{
							if($_SESSION['bangding']==1&&$_SESSION['renzheng']==1)$res = $this->actionDefault();
							else if(empty($_SESSION['bangding']))$res = "提醒：您未绑定，请通过配置中的绑定功能绑定后再使用！";
							else if(empty($_SESSION['renzheng']))$res = "提醒：您还需要实名认证，请通过配置中的实名认证上传自己的头像后再使用！";
						}									
						break;						
					default :
						break;
				}		
				echo $resultStr = $this->client->sprintf($res);
			}
			else if(empty($_SESSION['bangding'])||empty($_SESSION['renzheng'])){
				if(empty($_SESSION['bangding'])){
					echo $resultStr = $this->client->sprintf("提醒：您未绑定，请通过配置中的绑定功能绑定后再使用！");
				}else if(empty($_SESSION['renzheng'])){
					echo $resultStr = $this->client->sprintf("提醒：您还需要实名认证，请通过配置中的实名认证上传自己的头像后再使用！");
				}		
			} 
			else {
				$resultStr = $this->client->sprintf("系统繁忙");
				echo $resultStr;
				exit();
			}		
		}		
	}
	
	/**
	 * 将处理之后的数据返回给微信服务器进入，进而返回给用户，其中101...为用户的请求代码
	 * @param object $postObj 封装了用户的请求
	 * @return Ambigous <string, NULL>|string
	 */
	public function handleText($postObj) {
		$resultStr = "";
		$flag=$this->userflag();
		$keyword = $postObj->Content;
		$openID = $postObj->FromUserName;	
		switch ($keyword) {
			case "101" :
				//判断是否点到者  如果是更换数据存地位置
				if($_SESSION['wx_note']==1){			
					$resultStr =$this->actionLocation();	
				}
				else if($flag==1){
					$resultStr = $this->userLocation();
				}else {
					$resultStr = "还未到签到时间···";
				} 			
				break;
			case "202" :
				$resultStr = $this->getSignInfo($openID);
				break;		
			case "203" :
				$resultStr = $this->getDengmi();
				break;
			case "301" :
				if(empty($_SESSION['bangding'])){
					$resultStr = sprintf('<a href="%s?openID=%s">立刻绑定</a>', BIND_URL, $openID);
				}else{
					$resultStr="您以绑定过了！无需再绑定！";
				}
				break;
			case "303" :
				$resultStr = $this->jiebang($openID);
				break;
			case "304" :
				$resultStr = $this->actionHelp();
				break;
			default :
				$resultStr = $this->actionDefault();
				break;
		}
		return $resultStr;
	}
	/**
	 * 处理用户的按键事件，若用户点击微信客户端上的菜单按钮，将进入下面处理逻辑区
	 * @param object $object
	 * @return string
	 */
	public function handleEvent($object) {	
		$contentStr = "";
		$openID = $object->FromUserName;		
		switch (strtolower($object->Event)) {
			case "subscribe" :
				$contentStr = $this->actionSubscribe();
				break;
			case "unsubscribe" :
				$contentStr = $this->jiebang($openID);
				break;
			case "scancode_waitmsg" :		
				$contentStr = $this->waitMsg($object);
				break;			
			case "pic_sysphoto" :
				//弹出系统拍照发图的事件推送																									
				$_SESSION['pic_sysphoto']=1;											
				$_SESSION['key']="$object->EventKey";																					
				break;				
				
			/*
			case "location" :
				//获取用户地理位置			
				$contentStr = "上传位置：纬度 " . $object->Latitude . ";" . "经度 " . $object->Longitude . ";" . "精度" . $object->Precision;
				break;
			*/
			case "click" :
				$contentStr = $this->handleClick($object);
				break;
			default :
				break;
		}
		return $contentStr;
	}

	/**
	 * 处理用户的具体点击事件
	 * @param object $object 封装了用户的点击事件的按钮代号
	 * @return string
	 */
	private function handleClick($object) {
		$contentStr = "";
		$flag=$this->userflag();
		//echo $flag;		
		$openID = $object->FromUserName;
		$key = $object->EventKey;
		switch ($key) {
			case "101" :
				//判断是否点到者  如果是更换数据存地位置
				if($_SESSION['wx_note']==1){			
					$contentStr =$this->actionLocation();	
				}
				else if($flag==1){
					$contentStr = $this->userLocation();
				}
				else {
					$contentStr = "还未到签到时间···";
				} 			
				break;
			case "202" :
				$contentStr = $this->getSignInfo($openID);
				break;
			case "203" :
				$contentStr = $this->getDengmi();
				break;
			case "301" :
				if(empty($_SESSION['bangding'])){
					$contentStr = sprintf('<a href="%s?openID=%s">立刻绑定</a>', BIND_URL, $openID);
				}else{
					$contentStr="您以绑定过了！无需再绑定！";
				}
				break;
			case "303" :
				$contentStr = $this->jiebang($openID);
				break;
			case "304" :
				$contentStr = $this->actionHelp();
				break;
			default :
				$contentStr = $this->actionDefault();
				break;
		}
		return $contentStr;
	}

	/**
	 * 扫码签到
	 */
	public function waitMsg($object) {
		$key = $object->ScanCodeInfo->ScanResult;
		if($_SESSION['wx_note']==1){
			return "此功能仅为普通用户提供···";
		}
		$tmpInfo = file_get_contents(SIGN_REQUEST . "?rand={$key}&per_id={$_SESSION['wx_per_id']}");
		//return SIGN_REQUEST . "?rand={$key}&per_id={$_SESSION['wx_per_id']}";		
		if ($tmpInfo == "签到成功")
			return "签到成功!";
		else
			return "签到失败,原因:" . $tmpInfo;
	}
		
	/**
	 *自拍签到
	 */
	private function getphoto($postObj) {
		$con = mysqli_connect(CONFIG_HOST, CONFIG_USERNAME, CONFIG_PASSWORD, CONFIG_DBNAME);
		if (mysqli_connect_errno($con)) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		mysqli_set_charset($con, "utf8");
		if($_SESSION['wx_note']==0){
			$sql ="select qingjia,per_photo from person where per_id={$_SESSION['wx_per_id']}";
			$result = mysqli_query($con, $sql);	
			$result = mysqli_fetch_array($result);
			if(!empty($result[0])){
				mysqli_close($con);
				return "您已申请请假过了，无法再通过人脸识别签到了。";
			}else{
				//这里有个小漏洞     自拍签到成功后，再自拍签到其它照片  会报出 	  "签到失败！无法找到人脸信息！请重新尝试下..."  错误信息 但不会影响实际签到结果	
				$face_id2=$this->get_face_id($postObj->PicUrl);
				if($face_id2==null){
					return "签到失败！无法找到人脸信息！请重新尝试下...";
				}		
				else{
					$sql = "UPDATE person SET per_photo2='{$postObj->PicUrl}' where per_id={$_SESSION['wx_per_id']}";
					mysqli_query($con, $sql);
					$face_id1=$this->get_face_id($result[1]);
					mysqli_close($con);
					if($this->get_similarity($face_id1,$face_id2)){
						return "人脸信息已验证成功，如果系统未提示签到成功！可能是因为没有上传位置信息，或你未在签到的地点。";
					}else{
						return "签到失败！人脸信息验证失败,可能不是本人！";
					}
				}				
			}		
		}else {
			return "此功能仅为普通用户提供···";
		}									
	}
	/**
	 *请假申请
	 */
	 private function leave($postObj){
	 	$con = mysqli_connect(CONFIG_HOST, CONFIG_USERNAME, CONFIG_PASSWORD, CONFIG_DBNAME);
		if (mysqli_connect_errno($con)){
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		mysqli_set_charset($con, "utf8");
		if($_SESSION['wx_note']==0){
			$sql = "UPDATE person SET qingjia='{$postObj->PicUrl}' where per_id={$_SESSION['wx_per_id']}";			
			$result = mysqli_query($con, $sql);	
			mysqli_close($con);	
			$url=$postObj->PicUrl;
			$filename= "../Public/image/"."{$_SESSION['wx_per_id']}".".jpg";
			$this->getImg($url, $filename);	// 保存请假照			
			return "请假申请信息已提交成功！\n【提示】请假后你将无法使用自拍签到和扫描签到，所以请不要随意请假。如果请假信息存在问题可以重新申请假否则你将作为缺勤处理或者到签到地点向点到者反应情况。";
		}else{
			return "此功能仅为普通用户提供···";
		}								
	 }
	 /**
	  * 获取脸张脸的相识度
	  * 
	  * 参数 face_id1   face_id2
	  * 
	  * 相识度  低于 75   返回 false
	  *         否则      返回 true
	  */
	 public function get_similarity($face_id1,$face_id2){
		$url="http://apicn.faceplusplus.com/v2/recognition/compare?api_secret=c2yLqP9r0hANnQw2btEi66xMB7RnVJXa&api_key=45332807563c09622d3fba7254b89b98&face_id2={$face_id2}&face_id1={$face_id1}";
		$curl = curl_init(); //初始化
		curl_setopt($curl, CURLOPT_URL, $url);   //设置抓取的url
		curl_setopt($curl, CURLOPT_HEADER, 0);   //设置头文件的信息   不  作为数据流输出
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  //设置获取的信息以文件流的形式返回，而不是直接输出。
		$data = curl_exec($curl); //执行命令
		curl_close($curl);  //关闭URL请求		
		$data=json_decode($data,true);// 将json 数据转换为数组数据
		//var_dump($data); //显示获得的数据
		$num= $data['similarity'];
		if($num<75)return false;	
		else return true;
	}
	 /**
	  * 获取人脸信息
	  * 无人脸信息时 face_id 为空
	  */
	 public function get_face_id($image){
		$url="http://apicn.faceplusplus.com/v2/detection/detect?api_key=45332807563c09622d3fba7254b89b98&api_secret=c2yLqP9r0hANnQw2btEi66xMB7RnVJXa&url={$image}";	
		$curl = curl_init();   //初始化
		curl_setopt($curl, CURLOPT_URL, $url); //设置抓取的url	
		curl_setopt($curl, CURLOPT_HEADER, 0);//设置头文件的信息   不  作为数据流输出
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//设置获取的信息以文件流的形式返回，而不是直接输出。
		$data = curl_exec($curl); //执行命令		
		//$data=json_decode($data);	// 将json 数据转换为对像数据
		//echo $data->face[0]->face_id; //显示获得的数据	
		$data=json_decode($data,true);// 将json 数据转换为数组数据
		//echo $data['face'][0]['face_id']; //显示获得的数据		
		//关闭URL请求
		curl_close($curl);		
		return $data['face'][0]['face_id'];
	} 
	 /**
	  *实名认证照片
	  */
	 private function renzhen($postObj){
	 	$con = mysqli_connect(CONFIG_HOST, CONFIG_USERNAME, CONFIG_PASSWORD, CONFIG_DBNAME);
	 	$openID = $postObj->FromUserName;
		if (mysqli_connect_errno($con)) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		mysqli_set_charset($con, "utf8");
		if($_SESSION['wx_note']==1){
			mysqli_close($con);
			return "非普通用户无需认证！";
		}
		else{
			$sql="SELECT per_photo,per_uid FROM person WHERE per_id={$_SESSION['wx_per_id']} ";
			$flag=mysqli_query($con,$sql);
			$flag = mysqli_fetch_array($flag);		
			if($flag[0]==null){
				if($this->get_face_id($postObj->PicUrl)==null){
					return "认证失败！无法找到人脸信息！请重新尝试下...";					
				}else{
					$sql = "UPDATE person SET per_photo='{$postObj->PicUrl}' where per_id={$_SESSION['wx_per_id']}";			
					mysqli_query($con, $sql);	
					mysqli_close($con);			
					$url=$postObj->PicUrl;
					$filename= "../Public/imagePeople/"."$flag[1]".".jpg";
					$this->getImg($url, $filename);	// 保存实名认证照				
					return "实名认证成功！";
				}
			}
			else {
				mysqli_close($con);
				return "您以实名认证过了，无需再认证！";
			}
		}								
	 }	
	/**
	 * 下载请假图片
	 * 图片链接         $url
	 * 和图片保存位置   $filename
	 */
	public function getImg($url = "", $filename = ""){
		$ch = curl_init();
		$fp = fopen($filename,'wb');
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_FILE,$fp);
		curl_setopt($ch,CURLOPT_HEADER,0);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);  
		//curl_setopt($ch,CURLOPT_RETURNTRANSFER,false);//以数据流的方式返回数据,当为false是直接显示出来  
		curl_setopt($ch,CURLOPT_TIMEOUT,60);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
	}	
	/** 
	 * 数据统计
	 */
	public function getSignInfo($openID) {
		if($_SESSION['wx_note']==1){
			return "此功能仅为普通用户提供···";
		}
		$id = $_SESSION['wx_per_id'];
		$tempArr = array (
			"total" => 0,
			"come" => 0,
			"notcome" => 0,
			"details" => array ()
		);
		$result = mysql_query("SELECT sgn_id,sgn_list,sgn_time FROM sgn WHERE sgn_tnt_id={$_SESSION['wx_tnt_id']} 
				AND sgn_dep_id IN(SELECT p.per_dep_id FROM person p WHERE p.per_id={$id}) 
				ORDER BY sgn_time DESC", $this->connection);
		while ($r = mysql_fetch_assoc($result)) {
			$res[] = $r;
		}
		if (!empty ($res['sgn_id'])) {
			$t = array ();
			$t[0] = $res;
			$res = $t;
		}
		$msg = "";
		foreach ($res as $k => $v) {
			$list = $v['sgn_list'];
			$l = json_decode($list, true);
			if (isset ($l[$id])) {
				$tempArr["total"]++;
				$tempArr["details"][$res[$k]['sgn_id']]['time'] = date("Y-m-d H:m:s", $res[$k]['sgn_time']);

				if ($l[$id] == 1) {
					$tempArr["come"]++;
					$tempArr["details"][$res[$k]['sgn_id']]['iscome'] = 1;
				} else {
					$tempArr["notcome"]++;
					$tempArr["details"][$res[$k]['sgn_id']]['iscome'] = 0;
					$msg .= date("Y-m-d H:m:s", $res[$k]['sgn_time']) . " 缺勤\n";
				}
			}
		}
		$returnStr = sprintf("缺勤次数:%s\n已到次数:%s\n总共需要签到次数:%s\n目前平时成绩为：%.2f分\n缺勤记录:\n%s", $tempArr['notcome'], $tempArr['come'], $tempArr['total'], ($tempArr['come'] / $tempArr['total'] * 100), $msg);
		return $returnStr;
	}
	
	/**
	 * 获取系统公告栏的最后一条公告！
	 */
	private function getDengmi() {
		$con = mysqli_connect(CONFIG_HOST, CONFIG_USERNAME, CONFIG_PASSWORD, CONFIG_DBNAME);
		if (mysqli_connect_errno()) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		mysqli_set_charset($con, "utf8");
		$result = mysqli_query($con, "SELECT * FROM news ORDER BY id DESC");
		while ($row = mysqli_fetch_array($result)) {
			mysqli_free_result($result);
			mysqli_close($con);
			return $row['content'];
		}
	}
		
	/**
	 *  解除绑定
	 */
	private function jiebang($openID) {
		$con = mysqli_connect(CONFIG_HOST, CONFIG_USERNAME, CONFIG_PASSWORD, CONFIG_DBNAME);
		if (mysqli_connect_errno()) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		mysqli_set_charset($con, "utf8");
		if($_SESSION['wx_note']==0){
			$sql="update person set per_photo=null where per_id in(select per_id from bind where open_id='$openID')";
			mysqli_query($con,$sql);
			$sql="select per_uid from person where per_id={$_SESSION['wx_per_id']}";
			$per_uid=mysqli_query($con,$sql);
			$per_uid=mysqli_fetch_array($per_uid);
			$image="../Public/imagePeople/"."$per_uid[0]".".jpg";
			unlink($image);
		}
		mysqli_query($con, "DELETE FROM bind where open_id='$openID'");
		mysqli_close($con);
		session_destroy(); //  解绑摧毁   session_id();
		return "解绑成功";
	}

	/**
	 * 当用户关注微信公众平台时调用
	 * @return string
	 */
	private function actionSubscribe() {
		$contentStr = "感谢您关注【签到系统】微信公众号!我们的微信号是:【gh_32c634aa8e4f】我们的目标是为你提供最优质的签到服务【签到系统】微信公众平台通过发送消息的基本功能如下:输入功能列表前序号使用对应功能.例如:输入\"101\"使用【上传位置】功能:
		1.签到
		   101.【上传位置】
		2.个人管理
			202.【数据统计】
			203.【系统公告】
		3.配置
			301.【绑定微信】
			303.【解除绑定】
			304.【使用帮助】
		";
		return $contentStr;
	}

	/**
	 * 当输入的短信操作不存在时作出提醒
	 * @return string
	 */
	private function actionDefault() {
		$contentStr = "服务不存在或正在建设中.目前发送短信仅有如下服务：
		1.签到
		   101.【上传位置】
		2.个人管理
			202.【数据统计】
			203.【系统公告】
		3.配置
			301.【绑定微信】
			303.【解除绑定】
			304.【使用帮助】
		";
		return $contentStr;
	}
	/**
	 * 当用户点击使用帮助时调用
	 * @return string
	 */
	private function actionHelp() {
		$contentStr = "欢迎使用【电子点名系统】,我们的微信公众号是【gh_32c634aa8e4f】,有什么问题可发送邮件到1351024799@qq.com联系管理员,我们将及时解决您的问题!\n【温馨提示】：如果你绑定的信息或实名认证信息错误将无法正常签到，需解绑重新绑定和实名认证，同时请开启您的手机GPS功能！";
		return $contentStr;
	}
}

/**
 * 
 * 发送文本事件信息给用户类
 */
class ClientTextRequest {
	private $textTpl;
	private $fromUsername;
	private $toUsername;
	private $time;
	private $msgType;
	/**
	 * 构造函数
	 * @param unknown $fromUsername
	 * @param unknown $toUsername
	 * @param unknown $time
	 */
	public function __construct($fromUsername, $toUsername) {
		$this->textTpl = "<xml>
		                        <ToUserName><![CDATA[%s]]></ToUserName>
		                        <FromUserName><![CDATA[%s]]></FromUserName>
		                        <CreateTime>%s</CreateTime>
		                        <MsgType><![CDATA[%s]]></MsgType>
		                        <Content><![CDATA[%s]]></Content>
		                        <FuncFlag>0</FuncFlag>
		                        </xml>";
		$this->fromUsername = $fromUsername;
		$this->toUsername = $toUsername;
		$this->time = time();
		$this->msgType = "text";
	}
	/**
	 * 封装要发送给微信服务器的信息，如果要发送给客户只需要 输出返回值即可 然后通过 echo 打印返回的值即可
	 * @param unknown $contentStr
	 * @return string
	 */
	public function sprintf($contentStr) {
		return sprintf($this->textTpl, $this->fromUsername, $this->toUsername, $this->time, $this->msgType, $contentStr);
	}
}
?>
<?php

/**
 * 数据库操作类
 */
 
 /*
class Database {
	private $CONFIG_HOST; //数据库服务器地址
	private $CONFIG_USERNAME; //数据库用户名
	private $CONFIG_PASSWORD; //数据库密码
	private $CONFIG_DBNAME; //数据库名
	public function __construct($host, $username, $password, $dbname) {
		$this->CONFIG_HOST = $host;
		$this->CONFIG_USERNAME = $username;
		$this->CONFIG_PASSWORD = $password;
		$this->CONFIG_DBNAME = $dbname;
	}

	 // 查找数据
	 // 传入  查询SQL语句
	 // 返回一个查询的结果对象
	 
	public function Select($SQL) {
		$con = mysqli_connect($CONFIG_HOST, $CONFIG_USERNAME, $CONFIG_PASSWORD, $CONFIG_DBNAME);
		if (mysqli_connect_errno()) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		mysqli_set_charset($con, "utf8");
		$result = mysqli_query($con, $SQL);
		mysqli_close($con);
		return $result;
		//$row = mysqli_fetch_array($result);//数组
		//$rowcount=mysqli_num_rows($result);//数组中数据组数
	}
	
	
	 // 删除数据 插入数据  修改数据
	 // 传入  SQL语句
	 // 成功返回  true  失败返回 false
	 
	public function Deleted($SQL) {
		$con = mysqli_connect($CONFIG_HOST, $CONFIG_USERNAME, $CONFIG_PASSWORD, $CONFIG_DBNAME);
		if (mysqli_connect_errno()) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		mysqli_set_charset($con, "utf8");
		if (!mysqli_query($con, $SQL)) {
			mysqli_close($con);
			return false; //失败
		}
		mysqli_close($con);
		return true; //成功
	}
}

 
 //  距离计算类

class juli{	
	settype($myLat, "float");  
	settype($myLng, "float"); 
	$myLat = $postObj->Location_X;//接收到的当前位置的纬度  
	$myLng = $postObj->Location_Y;//接收到的当前位置的经度  
	$Label = $postObj->Label;//接收到的当前地理位置信息  
	$Label = iconv("UTF-8","GBK",$Label);  
	$find = stripos($Label,' ');//过滤掉邮政编码 纯属为了整洁性  
	if($find!==false)  {  
   	 $Label = substr($Label,0,$find);  
	}  
	//以下为核心代码  
	$range = 180 / pi() * 1 / 6372.797;     //里面的 1 就代表搜索 1km 之内，单位km  
	$lngR = $range / cos($myLat * pi() / 180);  
	$maxLat = $myLat + $range;//最大纬度  
	$minLat = $myLat - $range;//最小纬度  
	$maxLng = $myLng + $lngR;//最大经度  
	$minLng = $myLng - $lngR;//最小经度  
	//得出这四个值以后，就可以根据你数据库里存的经纬度信息查找记录了~ 
}
 */



/*
    方倍工作室 http://www.cnblogs.com/txw1958/
    CopyRight 2013 www.doucube.com  All Rights Reserved
*/
/*
define("TOKEN", "weixin");
$wechatObj = new wechatCallbackapiTest();
if (isset($_GET['echostr'])) {
    $wechatObj->valid();
}else{
    $wechatObj->responseMsg();
}
class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        <FuncFlag>0</FuncFlag>
                        </xml>";
            if($keyword == "?" || $keyword == "？")
            {
                $msgType = "text";
                $contentStr = date("Y-m-d H:i:s",time());
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }
        }else{
            echo "";
            exit;
        }
    }
}*/
?>