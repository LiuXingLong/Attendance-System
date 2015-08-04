<?php
namespace Manager\Controller;

use Think\Controller;
// 本类由系统自动生成，仅供测试用途
class BaseController extends Controller {
	Public function _initialize() {
		// tag('action_init'); // 添加action_init 标签
		if (empty ( $_SESSION ['mng_id'] )) {
			$this->redirect ( "Login/index" );
			exit ( 1 );
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