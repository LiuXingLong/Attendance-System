<?php
namespace Tenant\Controller;

use Think\Controller;
use Think\Model;

class LoginController extends Controller {
	public function index() {
		$this->display ();
	}
	public function doLogin() {
		// $this->ajaxReturn(array("msg"=>"message","status"=>"true"));
		if (empty ( $_POST ["name"] ) || empty ( $_POST ["password"] ) || empty ( $_POST ["code"] )) {
			// $this->error("输入不能为空","index");
			$this->ajaxReturn ( array (
					"content" => null,
					"info" => "输入不能为空",
					"status" => false 
			) );
			exit ( 1 );
		}
		$code = $_POST ['code'];
		$Ver = new CodeController ();
		if (! $Ver->check_verify ( $code )) {
			// 验证码错误
			 $this->ajaxReturn(array("content"=>null,"info"=>"验证码错误","status"=>false));
			 $this->error("验证码错误","index");
			 exit ( 1 );
		}
		$username = $_POST ['name'];
		$password = $_POST ["password"];
		$m = M ( "Tenant" );
		$res = $m->field ( "tnt_password,tnt_id,tnt_isrun" )->where ( "tnt_username='%s'",$username )->select ();//防注入查找
		
		// print_r($res);

		if ($res [0] ['tnt_password'] == md5 ( $password )) {
			switch ($res [0] ['tnt_isrun']) {
				case 0 :
					$this->returnAjax(false,"你的租户系统处于停用状态，请及时与管理员进行联系！");
					break;
				case 1 :
					$_SESSION ['tnt_username'] = $username . $res [0] ['tnt_id'];
					$_SESSION ['tnt_id'] = $res [0] ['tnt_id'];
					// $this->redirect("Index/index");
					$this->ajaxReturn ( array (
							"content" => null,
							"info" => "登录成功！",
							"status" => true
					) );
					break;
				case 2 :
					$this->returnAjax(false,"系统处于维护状态，请联系管理员获取详细信息！");
					break;
				default :
					break;
			}
		}
		// $res=$m->select();
		// var_dump($res);
		// $this->error($username."用户名或者密码错误！".$password);
		$this->ajaxReturn ( array (
				"content" => null,
				"info" => "用户名或者密码错误！",
				"status" => false 
		) );
	}
	public function doLogout() {
		$m = M("Tenant");
		$date = date("Y-m-d",time());
		$m->save(array("tnt_lasttime"=>$date,"tnt_id"=>$_SESSION['tnt_id']));
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