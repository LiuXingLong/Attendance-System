<?php
namespace Manager\Controller;

use Think\Controller;
use Think\Model;

class LoginController extends Controller {
	public function index() {
		$m = M('Tenant');
		$res = $m->select();
		if(empty($res)){
			$this->show("无租户可选择！请联系管理员。");
			return;
		}
		$this->assign("managers",$res);
		$this->display ();
	}
	public function doLogin() {
		// $this->ajaxReturn(array("msg"=>"message","status"=>"true"));
		if (empty ( $_POST ["uid"] ) || empty ( $_POST ["password"] ) || empty ( $_POST ["tnt_id"] ) || empty ( $_POST ["code"] )) {
			// $this->error("输入不能为空","index");
			$this->ajaxReturn ( array (
					"content" => null,
					"info" => "输入不能为空",
					"status" => false 
			) );
		//	exit ( 1 );
		}
		$code = $_POST ['code'];  // 验证码
		$Ver = new CodeController ();
		if (! $Ver->check_verify ( $code )) {
			// 验证码错误
			 $this->ajaxReturn(array("content"=>null,"info"=>"验证码错误","status"=>false));
		}
		$password = $_POST ["password"];  // 密码
		$tnt_id = $_POST ["tnt_id"];
		$m = M ( "Manager" );
		$where['mng_uid'] = $_POST ["uid"];       // 用户名
		$where['mng_tnt_id'] = $_POST ["tnt_id"]; // 所属租户
			
		$res=$m->field ( "mng_password,mng_id" )->where("mng_uid=%d and mng_tnt_id=%d",$where['mng_uid'],$where['mng_tnt_id'])->select();  // 防注入	查找		
		//$res = $m->field ( "mng_password,mng_id" )->where ( $where )->select ();
					
		if(empty($res)){
			$this->returnAjax(false,"租户或用户名错误！");
		}
		$T = M("Tenant");
		$whe['tnt_id'] = $_POST ["tnt_id"]; 		
		$res2 = $T->field("tnt_isrun")->where("tnt_id=%d",$whe['tnt_id'])->select(); // 防注入查找		
		if($res2[0]['tnt_isrun']==0){
			$this->returnAjax(false,"你所属的租户处于停用状态，请及时与租户进行联系！");
		}
		// print_r($res);
		//md5加密，以后是否需要修改待讨论  
		if ($res [0] ['mng_password'] ==  $password ) {
					//登入信息验证成功保存登入信息
					$_SESSION ['mng_id'] = $res [0] ['mng_id'];
					$_SESSION ['tnt_id'] = $_POST ["tnt_id"];
					// $this->redirect("Index/index");
					$this->ajaxReturn ( array (
							"content" => null,
							"info" => "登录成功！",
							"status" => true
					) );
		}
		// $res=$m->select();
		// var_dump($res);
		// $this->error($username."用户名或者密码错误！".$password);
		$this->ajaxReturn ( array (
				"content" => null,
				"info" => "密码错误！",
				"status" => false 
		) );
	}
	public function doLogout() {
		session ( "[destroy]" );			
		$this->ajaxReturn ( array (
		"content" => null,
		"info" => "",
		"status" => true 
		) );
	}
	private function returnAjax($status = true, $info = "操作成功", $content = null) {
		$this->ajaxReturn ( array (
				"content" => $content,
				"info" => $info,
				"status" => $status
		), "json" );
	}
}
?>