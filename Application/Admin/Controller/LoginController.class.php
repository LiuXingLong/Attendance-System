<?php
namespace Admin\Controller;

use Think\Controller;
use Think\Model;

class LoginController extends Controller {
	public function index() {
		$this->display ();
	}
	public function doLogin() {
		// 参数非法，则重新登录
		if (empty ( $_POST ["name"] ) || empty ( $_POST ["password"] ) || empty ( $_POST ["code"] )) {
			$this->ajaxReturn ( array (
					"content" => null,
					"info" => "输入不能为空",
					"status" => false 
			) );
			exit ( 1 );
		}		
		//验证码生成
		$code = $_POST ['code'];
		$Ver = new CodeController ();
		if (! $Ver->check_verify ( $code )) {
			// 验证码错误
			$this->ajaxReturn ( array (
					"content" => null,
					"info" => "验证码错误",
					"status" => false 
			) );
			$this->error ( "验证码错误", "index" );
			exit ( 1 );
		}	
		//获取用户名
		$username = $_POST ['name'];
		//获取密码
		$password = $_POST ["password"];	
		//创建systemmanager 的Model
		$m = new Model ( "systemmanager" );		
		//$res = $m->field ( "sys_password,sys_id" )->where ( "sys_username='{$username}'" )->select ();				
		$res=$m->field ( "sys_password,sys_id" )->where("sys_username='%s'",$username)->select();  // 防注入	查找
		if ($res [0] ['sys_password'] == md5 ( $password )) {
			//若账号和密码都正确则登录成功，并存入相关session
			$_SESSION ['systemmanager'] = $username;
			$_SESSION ['sys_id'] = $res [0] ['sys_id'];
			$this->ajaxReturn ( array (
					"content" => null,
					"info" => "登录成功！",
					"status" => true 
			) );
		}
		//用户名或者密码错误，则登录失败
		$this->ajaxReturn ( array (
				"content" => null,
				"info" => "用户名或者密码错误！",
				"status" => false 
		) );
	}
	public function doLogout() {
		session ( "[destroy]" );
		$this->ajaxReturn ( array (
				"content" => null,
				"info" => "用户名或者密码错误！",
				"status" => true 
		) );
	}
}
?>