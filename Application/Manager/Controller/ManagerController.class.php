<?php
namespace Manager\Controller;
use Think\Controller;
use \Think\Model;
use Org\Util\Date;

class ManagerController extends BaseController {
	private $m = null;
	private function M() {
		return $m === null ? $this->m = M ( "Manager" ) : $this->m;
	}
	public function index() {
		// $this->display ();
		//测试
		$m = M("Department");
		$res = $m->select();
		$v = $this->tree(removePre($res, "dep_"),0);
		$this->prn($v);
	}
	/**
	 * 获取所有租户数据
	 */
	public function getPersons() {
		if(empty($_POST['per_dep_id'])){
			$this->returnAjax(false,"非法请求!");
		}
		$m = M ( "Person" );
		$fields = array (
				"per_id",
				"per_tnt_id",
				"per_dep_id",
				"per_name",
				"per_sex",
				"per_password",
				"per_email",
				"per_note",
				"per_photo" 
		);
		$where["per_tnt_id"] = $_SESSION['tnt_id'];
		$res = $m->field ( $field )->where ( $where )->select ();
		if (! $res) {
			$this->returnAjax ( false );
		}
		$r = removePre ( $res, "per_" );
		$this->ajaxReturn ( $r );
	}
	public function getManagers(){
		$m = M ( "Manager" );
		$fields = array (
				"mng_id",
				"mng_tnt_id",
				"mng_name",
				"mng_sex",
				"mng_password",
				"mng_email",
				"mng_note",
				"mng_deplist",
				"mng_uid"
		);
		$where["mng_tnt_id"] = $_SESSION['tnt_id'];
		$res = $m->where ( $where )->select ();
		
		if (! $res) {
			foreach ($fields as $k=>$v){
				$res[0][$v] = "-";
			}
			$r = removePre ( $res, "mng_" );
			$this->ajaxReturn ( $r);
		}
		
		$r = removePre ( $res, "mng_" );
		$this->ajaxReturn ( $r );
	}
	/**
	 * 添加租户
	 */
	public function add() {
		$m = D ( "Manager" );
		$model = M ( "Manager" );
		$_POST['mng_tnt_id']= $_SESSION['tnt_id'];
		if ($m->create ()) {
			$where['mng_uid'] = $_POST ["mng_uid"];
			$res = $model->where ( $where )->select();
			if (count($res) > 0) {
				$this->returnAjax ( false, "该标识用户已经存在！" );
			}	
			$id = $m->add ($_POST);
			$_POST ['mng_id'] = $id;
			$id ? $this->returnAjax ( true, "增加成员成功！", $_POST ) : $this->returnAjax ( false, "添加失败！" );
		} else {
			$this->returnAjax ( false, "增加成员失败，原因：[" . $m->getError () . "]" );
		}
	}
	/**
	 * 删除租户
	 */
	public function delete() {
		$m = M ( "Manager" );
		empty ( $_POST ['mng_id'] ) && $this->returnAjax ( false, "错误请求!" );
		$id = $_POST ['mng_id'];
		$m->delete ( $id ) ? $this->returnAjax ( true, "删除成功" ) : $this->returnAjax ( false, "删除失败" );
	}
	
	/**
	 * 更新租户信息
	 */
	public function update() {
		if (empty ( $_POST ['mng_id'] )) {
			$this->returnAjax ( false );
		}
		$res = removePre($_POST, "mng_");
		$m = D ( "Manager" );
		if ($m->create ()) {
			$id = $m->save ();
			// $_POST['id'] = $id;
			
			$id ? $this->returnAjax ( true, "修改成员信息成功！", $_POST ) : $this->returnAjax ( false, "修改成员信息失败！" . $m->getError (),$res );
		} else {
			$this->returnAjax ( false, "修改成员信息失败，原因：[" . $m->getError () . "]",$res  );
		}
	}
	//二维码签到
	public function listdep(){
		session("code2",array());
		$m = M("Department");
		$res = $m->select();
		$mng_id = $_GET['mng_id'];
		$v = "";
		$this->assign("mng_id",$mng_id);
		$mng = M("Manager");
		$w['mng_id'] = $_GET['mng_id'];
		$r = $mng->field("mng_deplist")->where($w)->select();
		
		if(!empty($r)){
			$arr = split(",", $r[0]["mng_deplist"]);
			
			$v .= $this->prn($this->tree(removePre($res, "dep_"),0),$arr);
		}else{
			$v .= $this->prn($this->tree(removePre($res, "dep_"),0),array());
		}
		$v = $this->tree3($arr);
	//	print_r($this->tree3($arr));exit(0);
	//	print_r($arr);

		//二维码
		vendor('phpqrcode.phpqrcode');
		$pic_code = "Public/Code2/code.jpg";
		$uuid = md5(\Org\Util\String::uuid()).session_id();
		\QRcode::png ( $uuid, $pic_code, 'M', 16 );

		session("sgn_code2",$uuid);
//		print_r(session("code2"));exit(0);
		$this->assign("pic_code",$pic_code);
		$this->assign("listdep",$v);
		$this->display("Index/signcode");
	}
	public function searchInit(){
		$mng = M("Manager");
		$w['mng_id'] = session("mng_id");
		$r = $mng->field("mng_deplist")->where($w)->select();
		
		if(!empty($r)){
			$arr = split(",", $r[0]["mng_deplist"]);
		}else{
		}
		$v = $this->tree3($arr);
		foreach ($v as $k=>$va){
			unset($v[$k]['children']);
		}
		$this->ajaxReturn($v);
	}
	public function tree3($arr){
		$code2 = session("code2");
		$code2['deps']=array();
		$person = M("Person");
		$department = M("Department");
		$ret=array();
		foreach ($arr as $k=>$v){
			$where['dep_id'] = $v;
			$deps = $department->field(array('dep_name','dep_pid'))->where($where)->select();
			if(count($deps) > 0){
				$parent = $department->field(array('dep_name','dep_pid'))->where(array("dep_id"=>$deps[0]['dep_pid']))->select();
				$ret[$k]['dep_name'] = $parent[0]['dep_name']."【".$deps[0]['dep_name']."】";
				$ret[$k]['dep_id'] = $v;  				
			}
			$where2['per_dep_id'] = $v;
			$pers = $person->order("per_uid ASC")->field(array('per_id','per_uid','per_name'))->where($where2)->select();
			$temp = array();
			foreach ($pers as $k1=>$v1){
				$v1['iscome']=false;
				$temp[$v1['per_id']]=$v1;
			}
			if(count($pers) > 0){
				$ret[$k]['children']=$pers;
				$code2['deps'][$v]=$temp;
			}
		}
		session("code2",$code2);
		return $ret;
	}
	// 开启开始签到标志
	public function startflag(){
		$mng = M("Manager");
		$w['mng_id'] = session("mng_id");
		$r = $mng->field("mng_deplist")->where($w)->select();
		if(!empty($r)){		
			$arr = split(",", $r[0]["mng_deplist"]);			
			$person = M("Person");						
			foreach ($arr as $k=>$v){
				$where['per_dep_id'] = $v;							
				$P=$person->field("per_uid")->where($where)->select();							
				//dump(count($P));									
				for($i=0;$i<count($P);$i++){						
					//dump($P[$i]['per_uid']);					
					$A['per_uid']=$P[$i]['per_uid'];
					$data['flag']=1;
					$person->where($A)->save($data);								
				}				
			}
		}	
	}
	public function startCode2(){
		$code2 = session("code2");
		$code2['isstart']=true;
		session("code2",$code2);
		$this->startflag(); // 开启开始签到标志
		$this->returnAjax(true,"开始签到...!");
	}
	// 关闭开始签到标志
	public function endflag(){
		$mng = M("Manager");
		$w['mng_id'] = session("mng_id");
		$r = $mng->field("mng_deplist")->where($w)->select();
		if(!empty($r)){		
			$arr = split(",", $r[0]["mng_deplist"]);			
			$person = M("Person");						
			foreach ($arr as $k=>$v){
				$where['per_dep_id'] = $v;							
				$P=$person->field("per_uid")->where($where)->select();							
				//dump(count($P));									
				for($i=0;$i<count($P);$i++){
					$coun++;						
					//dump($P[$i]['per_uid']);					
					$A['per_uid']=$P[$i]['per_uid'];
					$data['flag']=null;
					$person->where($A)->save($data);								
				}				
			}
		}	
	}
	public function endCode2(){
		$code2 = session("code2");
		$code2['isstart']=false;
		$p =$_POST['pers'];//获取客户端的签到签到数据 以 ,分割
		$pers = split(",", $p);//签到了的组成的数组 一个数组
//		$this->returnAjax(true,"",print_r($_POST));
		$deps = $code2['deps'];
		foreach ($deps as $k=>$v){
			foreach ($v as $k1=>$v1){
				if(in_array($k1,$pers)){
					$deps[$k][$k1]['iscome']=true;
				}
			}
		}	
		$code2['deps'] = $deps;
		session("code2",$code2);
		$this->endflag(); // 关闭开始签到标志
		$this->returnAjax(true,"",null);
	}
	
	/**
	 * 清空 上传的照片 位置  数据
	 */
	public function Cleandata(){
		$mng = M("Manager");
		$w['mng_id'] = session("mng_id");
		$r = $mng->field("mng_deplist")->where($w)->select();
		if(!empty($r)){		
			$arr = split(",", $r[0]["mng_deplist"]);			
			$person = M("Person");						
			foreach ($arr as $k=>$v){
				$where['per_dep_id'] = $v;							
				$P=$person->field(array('per_uid','per_id','qingjia'))->where($where)->select();									
				//dump(count($P));									
				for($i=0;$i<count($P);$i++){
					$coun++;						
					//dump($P[$i]['per_uid']);					
					$A['per_uid']=$P[$i]['per_uid'];
					$data['per_photo2']=null;
					$data['Latitude']=null;
					$data['Longitude']=null;
					$data['qingjia']=null;
					$person->where($A)->save($data);				
					if(!empty($P[$i]['qingjia'])){
						//删除请假图片
						$image="./Public/image/".$P[$i]['per_id'].".jpg";
						unlink($image);						
					}					
				}				
			}
		}	
	}
	/**
	 * 签到结束
	 * 
	 * 提交数据 和 清除数据
	 */
	public function commitCode2(){
		$code2 = session("code2");
		$code2['isstart']=false;
		$p =$_POST['pers'];//获取客户端的签到签到数据 以 ,分割
		$pers = split(",", $p);//签到了的组成的数组 一个数组
		$deps = $code2['deps'];
		$count_notcome = 0;
		$count_come = 0;
		$iscome=array();
		foreach ($deps as $k=>$v){
			$iscome[$k]=array();
			foreach ($v as $k1=>$v1){
				if(in_array($k1,$pers)){
					$deps[$k][$k1]['iscome']=true;
					$iscome[$k][$k1]=1;
					$count_come++;
				}else{
					$iscome[$k][$k1]=0;
					$count_notcome++;
				}
			}
		}
		$commit['commit_info']['count_come'] = $count_come;
		$commit['commit_info']['count_notcome'] = $count_notcome;
		$code2['deps'] = $deps;
		session("code2",$code2);
		$sgn = M("Sgn");
		foreach ($iscome as $k=>$v){
			$data['sgn_dep_id']=$k;
			$data['sgn_mng_id']=session("mng_id");
			$data['sgn_tnt_id']=session("tnt_id");
			$data['sgn_time']=time();
			$data['sgn_list']=json_encode($v,true);
			if($sgn->add($data)){
				
			}else{
				$this->returnAjax(true,"数据保存失败");
			}
		} 
		$this->Cleandata();//清空 上传的照片 位置  数据
		session("commit",$commit);
		session("code2",null);		
		$this->returnAjax(true,"数据保存成功",null);	
	}
	/******************  人脸识别处理  **********************/
	
	/**
	 * 人脸识别模块
	 * 
	 * 调用    main_face($per_uid);
	 * return  获取相识度，低于百分之 75  返回 false 否则返回 true
	 * 传入参数  $per_uid    如：$per_uid=1105010107;
	 * 
	 */
	public function get_imageurl($per_uid){
		$con=mysqli_connect("localhost","root","root","signer");
		if(mysqli_connect_errno($con)){
			echo "Failed to connect to MySQL:".mysqli_connect_error();
		}
		mysqli_set_charset($con,"utf8");
		$sql="SELECT per_photo,per_photo2 from person where per_uid={$per_uid}";// 根据	$per_uid 查找
		//$sql="SELECT per_photo,per_photo2 from person where per_id in(select per_id from bind where open_id='$openID')";  // 根据	$openID 查找
		$imageurl=mysqli_query($con,$sql);
		$row = mysqli_fetch_array($imageurl);	
		//var_dump($row); //显示数据类型和数据	
		return $row;
	}
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
		return $data['similarity'];
	}
	public function main_face($per_uid){
		$image=$this->get_imageurl($per_uid);
		$image1=$image[0];
		$image2=$image[1];
		if($image1==null||$image2==null) return false;
		$face_id1=$this->get_face_id($image1);
		$face_id2=$this->get_face_id($image2);
		$num=$this->get_similarity($face_id1,$face_id2);
		if($num<75)return false;	
		else return true;
	}
	/**************** 位置信息处理 *****************/	

	/**
	 * 处理位置信息
	 * 
	 * 调用  main_weizhi($mng_id,$per_uid);
	 * 
	 * 参数 
	 * $mng_id  点到者信息
	 * $per_uid 签到者信息
	 * 返回  
	 * 在规定范围类返回     true
	 * 不在规定范围类返回   false；
	 */
	public function get_person_weizhi($per_uid){
		$con=mysqli_connect("localhost","root","root","signer");
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
	public function get_manager_weizhi($mng_id){
		$con=mysqli_connect("localhost","root","root","signer");
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
	public function chuli_weizhi($wz1,$wz2){
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
	public function main_weizhi($wz1,$per_uid){
		//$mng_id=1;
		//$per_uid=1105010116;
		//$wz1=$this->get_manager_weizhi($mng_id);
		$wz2=$this->get_person_weizhi($per_uid);
		return $this->chuli_weizhi($wz1,$wz2);
	}
	
	/****************   主动发送消息给客户   *****************/

	/**
	 * 获取 access_token
	 * 参数 
	 * $appID       wx730fa2ad7987400a
	 * $appsecret   6cb332b8cd0158d857cf2bc9744015da
	 * @return
	 * 成功返回：access_token
	 * 失败返回：false
	 */
	public function get_Access_token($appID,$appsecret){
		$url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appID}&secret={$appsecret}";
		$ch=curl_init();
		curl_setopt ( $ch, CURLOPT_URL, $url);
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );	//设置获取的信息以文件流的形式返回，而不是直接输出。
		$data = curl_exec($ch); //执行命令
		curl_close($ch);  //关闭URL请求
		$data = json_decode ( $data, true ); // 将json 数据转换为数组数据
		if (empty ( $data ['errcode'] )) {
			$access_token = $data ['access_token'];
			if (! empty ( $access_token )) {
				return $access_token;
			}
			return false;
		} else {
			return false;
		}
	}	
	/**
	 * 主动发送短信
	 * 参数
	 * 发送  消息内容
	 * $access_token
	 * 
	 * @return 返回除了结果   errcode      42001 代表$access_token超时   0 代表请求成功
	 */	 
	public function Send_message($data,$access_token) {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}" );
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
		return $result ['errcode'];
	}
	
	/**
	 * 主动给客户发送消息
	 * 
	 * 参数 
	 * 
	 * 公众号     $appID   $appsecret
	 * 发送内容   $data
	 */
	public function main_new($appID,$appsecret,$data){
		while(empty($_SESSION['access_token'])){
			$_SESSION['access_token']=$this->get_Access_token($appID,$appsecret);
		}
		$flag=$this->Send_message($data,$_SESSION['access_token']);
		if($flag==42001){
			//  token失效
			$_SESSION['access_token']=$this->get_Access_token($appID,$appsecret);
			$flag=$this->Send_message($data,$_SESSION['access_token']);
		}	
	}		
	//  更新是否签到的标志
	public function iscome(){		
		$code2 = session("code2"); //  获取一次会话的全局变量		
		$deps = $code2['deps'];	
		$mng_id = session("mng_id");	
		$wz1=$this->get_manager_weizhi($mng_id);					
		$appID="wx730fa2ad7987400a";
		$appsecret="6cb332b8cd0158d857cf2bc9744015da"; 				
		foreach ($deps as $k=>$v){
			foreach ($v as $k1=>$v1){
				$per_uid=$v1['per_uid'];			
				if(empty($deps[$k][$k1]['iscome'])&&empty($deps[$k][$k1]['qingjia'])){									
					$face=$this->main_face($per_uid);	
					$weizhi=$this->main_weizhi($wz1,$per_uid);
					if($face&&$weizhi){						
						$deps[$k][$k1]['iscome']=true;												
						/* 
						 * 两次查询实现				
						$person=M("person");
						$where['per_uid']=$per_uid;
						$P=$person->field(array('per_id','per_photo2','Latitude','Longitude'))->where($where)->select();						
						
						$A['per_id']=$P[0]['per_id'];
						$bind=M("bind");
						$r=$bind->field("open_id")->where($A)->select();
						$openID=$r[0]['open_id'];
												
						*/
						$Model=new Model();
						$openID=$Model->query("select open_id from bind where per_id in(select per_id from person where per_uid={$per_uid})");
						$openID=$openID[0]['open_id'];					
						$content= "签到成功！";	
						$data= '{
					   			"touser":"'.$openID.'", 
					  			"msgtype":"text", 
					    		"text": { "content":"'.$content.'"}
					   		    }';   
		   				$this->main_new($appID,$appsecret,$data);
					}
					else{						
						$person=M("person");
						$where['per_uid']=$per_uid;
						$P=$person->field("qingjia")->where($where)->select();
						 //dump($P);		
						if(!empty($P[0]['qingjia'])){
							$deps[$k][$k1]['qingjia']=true;	
							/*												
							//下载请假图片    优化放入 微信上传时处理了							
						    $url=$P[0]['qingjia'];
							$filename= "./Public/image/"."$k1".".jpg";							
							//dump($P[0]['qingjia']);
							//dump($filename);
							$this->getImg($url, $filename);							
							*/										
						}
					}																																					
				}
				//dump($v1['per_uid']);
			}
		}	
		$code2['deps'] = $deps;	//  保存处理的结果到全局变量中	
		$_SESSION["code2"]=$code2;	
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
	// 返回以签到的人数组
	public function checkByCode(){
		$code2 = session("code2");
		$arr = array();//  更新是否签到的标志
		$deps = $code2['deps'];
		$this->iscome();
		foreach ($deps as $k=>$v){
			foreach ($v as $k1=>$v1){
				if($deps[$k][$k1]['iscome']){
					$arr['per_uid'][]=$k1;
				}
				if($deps[$k][$k1]['qingjia']){
					$arr['qingjia'][]=$k1;
				}
			}
		}	
		$this->returnAjax(true,"",$arr);
	}
	public function notCome(){
		$commit = session("commit");
		$res['total'] = $commit['commit_info']['count_come'] + $commit['commit_info']['count_notcome'];
		$res['$come'] = $commit['commit_info']['count_come'];
		$res['$notcome'] = $commit['commit_info']['not_come'];
		$res['$percome'] = $come/$total;
		$this->returnAjax(true,"查询成功",$res);
	}
	public function jpgTest(){
		$commit = session("commit");
		$total = $commit['commit_info']['count_come'] + $commit['commit_info']['count_notcome'];
		$chart = new Chart();
		$title = "统计(".$total."人)"; //标题
		$data = array($commit['commit_info']['count_come'],$commit['commit_info']['count_notcome']); //数据
		$size = 80; //尺寸
		$width = 200; //宽度
		$height = 180; //高度
		$legend = array("已到人数(".$commit['commit_info']['count_come'].")","缺勤人数(".$commit['commit_info']['count_notcome'].")");//说明
		$chart->create3dpie($title,$data,$size,$height,$width,$legend);
	}
	/**
	 * 根据搜索查询,查询参数
	 * 1.班级
	 * 2.开始时间
	 * 3.结束时间
	 */
	public function search(){
		$date1 = $_REQUEST['date1'];
		$date2 = $_REQUEST['date2'];
		$id = $_REQUEST['id'];
		if(empty($date1) || empty($date2)){
			return;
		}
		$sgn = M("Sgn");
		$date1 = strtotime($date1);
		$date2 = strtotime($date2);
		$where['sgn_dep_id'] = $id;
		$res = $sgn->query("SELECT sgn_id,sgn_list,sgn_time FROM sgn WHERE sgn_dep_id={$id} AND sgn_time BETWEEN {$date1} AND {$date2} ORDER BY sgn_time DESC");
		if(count($res)<=0){
			$this->returnAjax(true,"没有查询到相关记录!",null);
		}
	//	tag_debug($res);
		$tarr = array();
		$total = 0;
		$come = 0;
		$notcome = 0;
		foreach ($res as $k=>$v){
			$tempArr = array("total"=>0,"come"=>0,"notcome"=>0);
			$list = $v['sgn_list'];
			$l = json_decode($list,true);
			foreach($l  as $k1=>$v1){
				$tempArr["total"]++;
				$tempArr['time']=date( "Y-m-d H:m:s",$res[$k]['sgn_time']);
		
				if($l[$k1] == 1){
					$tempArr["come"]++;
				}else{
					$tempArr["notcome"]++;
				}
			}
			$tempArr['per_come']=round(($tempArr["come"]/($tempArr["notcome"]+$tempArr["come"])*100),2)."%";
			$tarr[] = $tempArr;
		}
	//	tag_debug($tarr);
	//	session("personSgnInfo",$tempArr);
		//tag_debug($tempArr);
		$this->returnAjax(true,"查询成功",$tarr);
	}
	/**
	 * 根据搜索查询,查询参数到处Excel数据
	 * 1.班级
	 * 2.开始时间
	 * 3.结束时间
	 */
	public function searchOutExcel(){
		$date1 = $_REQUEST['date1'];
		$date2 = $_REQUEST['date2'];
		$id = $_REQUEST['id'];
		if(empty($date1) || empty($date2)){
			return;
		}
		$result=array();
		$sgn = M("Sgn");
		$date1 = strtotime($date1);
		$date2 = strtotime($date2);
		$where['sgn_dep_id'] = $id;
		$res0 = $sgn->query("SELECT mng_deplist FROM manager WHERE mng_id={$_SESSION['mng_id']}");
		$deps = split(",", $res0[0]['mng_deplist']);
		foreach ($deps as $v0){
			$res = $sgn->query("SELECT sgn_id,sgn_list,sgn_time,CONCAT(d1.dep_name,d.dep_name) dep_name FROM sgn 
					JOIN department d ON d.dep_id=sgn_dep_id 
					JOIN department d1 ON d1.dep_id=d.dep_pid 
					WHERE sgn_time BETWEEN {$date1} AND {$date2} 
					and sgn_tnt_id={$_SESSION['tnt_id']} 
					and sgn_mng_id={$_SESSION['mng_id']} 
					and sgn_dep_id={$v0}  
					ORDER BY sgn_time DESC");
			//	tag_debug($res);
			$tarr = array();
			$total = 0;
			$come = 0;
			$notcome = 0;
			foreach ($res as $k=>$v){
				$tempArr = array("total"=>0,"come"=>0,"notcome"=>0);
				$list = $v['sgn_list'];
				$l = json_decode($list,true);
				foreach($l  as $k1=>$v1){
					$tempArr["total"]++;
					$tempArr['time']=date( "Y-m-d H:m:s",$res[$k]['sgn_time']);
			
					if($l[$k1] == 1){
						$tempArr["come"]++;
					}else{
						$tempArr["notcome"]++;
					}
				}
				$tempArr['dep_name'] = $res[$k]['dep_name'];
				$tempArr['per_come']=round(($tempArr["come"]/($tempArr["notcome"]+$tempArr["come"])*100),2)."%";
				$tarr[] = $tempArr;
			}
			$result[]=$tarr;
		}
		//	tag_debug($result);
		//	session("personSgnInfo",$tempArr);
		//tag_debug($tempArr);
		//$this->returnAjax(true,"查询成功",$tarr);
	}
	public function outPersonsToExcel(){
		$sgn=M("Sgn");
		$where = "";
		if(!empty($_REQUEST['date1']) && !empty($_REQUEST['date2'])){
			$date3 = $_REQUEST['date1'];
			$date4 = $_REQUEST['date2'];
			$date1 = strtotime($date3);
			$date2 = intval(strtotime($date4))+60*60*24;
			$where=" and sgn_time between {$date1} and {$date2} ";
		}
		
		$r0=$sgn->query("SELECT mng_deplist FROM manager WHERE mng_id={$_SESSION['mng_id']}");
		$deps=split(",", $r0[0]['mng_deplist']);
//		tag_debug($r0[0]['mng_deplist']);
		$res0=$sgn->query("SELECT p.per_id FROM person p WHERE p.per_dep_id in({$r0[0]['mng_deplist']})  order by p.per_uid ASC");
		$ret = array();
//		tag_debug($res0);
		$ret[] = array("工号","姓名","性别","所属机构","签到次数","缺勤次数","应到次数","签到率");
		foreach ($res0 as $v){
			$ret[]=$this->PersonInfo($v['per_id'],$where);
		}
		
		$excel = new ExcelController();
		$head = empty($date1) ? array(0=>"全部签到数据") :array($date3."到".$date4."期间的签到统计数据");

		
		$excel->getExcel("签到统计表.xlsx", $head, $ret);
	//	tag_debug($ret);
	}
	private function PersonInfo($id,$where){
		if(empty($id))return null;
		$person = M("Person");
		$tempArr = array("per_uid"=>0,"per_name"=>0,"per_sex"=>"男","dep_name"=>"","come"=>0,"notcome"=>0,"total"=>0,"per_come"=>0);
		$res = $person->query("SELECT sgn_id,per.per_name,per.per_sex,per.per_uid,sgn_list,sgn_time,CONCAT(d1.dep_name,d.dep_name) dep_name FROM sgn 
				join person per on per.per_id={$id} 
				JOIN department d ON d.dep_id=sgn_dep_id 
				JOIN department d1 ON d1.dep_id=d.dep_pid 
				WHERE  sgn_tnt_id={$_SESSION['tnt_id']} AND sgn_dep_id IN(SELECT p.per_dep_id FROM person p WHERE p.per_id={$id}) AND sgn_mng_id={$_SESSION['mng_id']}  {$where} ORDER BY sgn_time DESC");
		$tempArr["per_uid"] = $res[0]['per_uid'];
		$tempArr["per_name"] = $res[0]['per_name'];
		$tempArr["per_sex"] = $res[0]['per_sex']==0?"男":"女";
		if($res[0]['per_sex'] != 0 && $res[0]['per_sex'] != 1)$tempArr["per_sex"]="-";
		$tempArr["dep_name"] = $res[0]['dep_name'];
//tag_debug($person->_sql());
		foreach ($res as $k=>$v){
			$list = $v['sgn_list'];
			$l = json_decode($list,true);
			if(isset($l[$id])){
				$tempArr["total"]++;
				if($l[$id] == 1){
					$tempArr["come"]++;
				}else{
					$tempArr["notcome"]++;
				}
			}
		}
		$tempArr["per_come"] = round(($tempArr["come"]/($tempArr["notcome"]+$tempArr["come"])*100),2)."%";
		return $tempArr;
	}
	/**
	 * 抛弃
	 * @param unknown $data
	 * @param unknown $r
	 * @param number $l
	 * @return string
	 */
	private function prn($data,$r,$l=0){
		$r = $r ? $r : array();
		static $res = "";
		$p = M("Person");
		$tl = $l+1;
		foreach ($data as $v){
			if($v['isleaf']){
				if(in_array($v['id'], $r)){
					$where['per_dep_id'] = $v['id'];
					$pers = $p->field(array('per_id','per_uid','per_name'))->where($where)->select();
					$res .= $v['name']."<br/>";
					$res .="<table><tr>";
					foreach ($pers as $k1=>$v1){
						$res .= sprintf("<td><input type='checkbox' name='per_id' id='mng_listdep' value='%s'>%s(%s)&nbsp;</td>",$v1['per_id'],$v1['per_name'],$v1['per_uid']);
					}
					$res .="</tr></table>";
					//$res .= $pers[0]['per_name'];
				}
				
			}else {
				$res .= "<hr/>".$v['name']."<br/>";
				$this->prn($v['children'],$r,$tl);
			}
		}
		return $res;
	}
	/**
	 * 抛弃
	 * @param unknown $data
	 * @param unknown $pid
	 * @return multitype:boolean
	 */
	private function tree($data,$pid){
		$res = array();
		foreach($data as $k=>$v){
			if($v['pid'] == $pid){
				//迭代子树
				$temp = $this->tree($data, $v['id']);
				if(!empty($temp)){
					$v['isleaf'] = false;
					$v['children'] = $temp;
				}else{
					$v['isleaf'] = true;
				}
				$res[] = $v;
			}
		}
		return $res;
	}
	/**
	 * 抛弃
	 */
	public function changeDeplist(){
		
		if(empty($_POST['mng_id']) || empty($_POST['mng_listdep'])){
			return;
		}
		$m = M('Manager');
		$where['mng_id'] = $_POST['mng_id'];
//		print_r($_POST);
		$v['mng_deplist'] = $_POST['mng_listdep'];
		if($m->where($where)->save($v)){
			$this->returnAjax(true,"修改成功！",$_POST);
		}else{
			$this->returnAjax(false,"修改失败！");
		}
	}
	public function outExcel(){

	}
	/**
	 * ajaxReturn的简化版本
	 *
	 * @param string $status        	
	 * @param string $info        	
	 * @param string $content        	
	 */
	private function returnAjax($status = true, $info = "", $content = null) {
		$info = $status ? (empty ( $info ) ? "操作成功" : $info) : (empty ( $info ) ? "操作失败" : $info);
		$this->ajaxReturn ( array (
				"content" => $content,
				"info" => $info,
				"status" => $status 
		), "json" );
	}
}
?>