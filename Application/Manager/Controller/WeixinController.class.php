<?php
namespace Manager\Controller;

use Think\Controller;
// 本类由系统自动生成，仅供测试用途
class WeixinController extends Controller {
	Public function index() {
		// tag('action_init'); // 添加action_init 标签
		$this->display();
	}
	public function bind(){
		if(empty($_REQUEST['openID'])){
			$this->returnAjax(false);
		}
		$tenant = M("Tenant");
		$res = $tenant->field("tnt_id,tnt_username")->where("tnt_isrun=1")->select();
		$this->assign("tnts",$res);
		$this->assign("openID",$_REQUEST['openID']);
		$this->display();
	}
	public function doBind(){
		if(empty($_REQUEST['tnt_id']) || empty($_REQUEST['uid']) || empty($_REQUEST['password']) || empty($_REQUEST['openID'])){
			$this->returnAjax(false,"绑定失败");
		}
		$tnt_id = $_REQUEST['tnt_id'];
		$uid = $_REQUEST['uid'];
		$password = $_REQUEST['password'];
		$openID = $_REQUEST['openID'];
		
		$bind = M("Bind");
		$r=$bind->field("per_id")->where("open_id='%s'",$openID)->select(); // 防注入
		
		if(!empty($r)){
			$this->returnAjax(false,"该账号已经绑定过微信号,请不要重新绑定!");
		}
		$person = M("Person");
		$where['per_tnt_id'] = $tnt_id;
		$where['per_uid'] = $uid;
		$res = $person->field("per_password,per_id")->where("per_tnt_id=%d and per_uid='%s'",$tnt_id,$uid)->select();	// 防注入
//		tag_debug($res);
		if(empty($res[0])){		
			$person = M("manager");
			$where1['mng_tnt_id'] = $tnt_id;
			$where1['mng_uid'] = $uid;
			$res = $person->field("mng_password,mng_uid")->where("mng_tnt_id=%d and mng_uid=%d",$tnt_id,$uid)->select();// 防注入
			if(empty($res[0])){
				$this->returnAjax(false,"用户名或者密码错误!");
			}
		}
		if($res[0]['per_password'] == $password||$res[0]['mng_password'] == $password){					
			if($res[0]['per_id']){
				$data['per_id']=$res[0]['per_id'];
				$data['note']=0;
			} 	
			else{
				$data['per_id']=$res[0]['mng_uid'];
				$data['note']=1;
			} 		
			$data['open_id']=$openID;
			$data['tnt_id']=$tnt_id;
			$data['time']=time();					
			if($bind->add($data)){
				$this->returnAjax(true,"绑定成功!");
			}else{
				$this->returnAjax(false,"绑定失败,用户名或者密码错误!");
				//$this->returnAjax(false,"绑定失败!");
			}
		}else{
			$this->returnAjax(false,"用户名或者密码错误!");
		}
	}
	/**
	 * ajaxReturn的简化版本
	 * @param string $status        	
	 * @param string $info        	
	 * @param string $content        	
	 */
	private function returnAjax($status = true, $info = "操作成功", $content = null) {		
		session_destroy(); //  摧毁  session		
		$this->ajaxReturn ( array (
				"content" => $content,
				"info" => $info,
				"status" => $status 
		), "json" );
	}
}
?>