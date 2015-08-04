<?php
namespace Tenant\Controller;

use Think\Controller;
// 本类由系统自动生成，仅供测试用途
class BaseController extends Controller {
	Public function _initialize() {
		// tag('action_init'); // 添加action_init 标签
		if (empty ( $_SESSION ['tnt_username'] )) {
			$this->display("Login:index" );
			$this->redirect ( "Login/index" );
			exit ( 0 );
		}
	}
	/**
	 * ajaxReturn的简化版本
	 * @param string $status        	
	 * @param string $info        	
	 * @param string $content        	
	 */
	private function returnAjax($status = true, $info = "操作成功", $content = null) {
		$this->ajaxReturn ( array (
				"content" => $content,
				"info" => $info,
				"status" => $status 
		), "json" );
	}
}
?>