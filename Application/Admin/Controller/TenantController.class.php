<?php
namespace Admin\Controller;

use Think\Controller;
use \Think\Model;
use Org\Util\Date;

class TenantController extends BaseController {
	private $m = null;
	private function M() {
		return $m === null ? $this->m = M ( "Tenant" ) : $this->m;
	}
	public function index() {
	}
	/**
	 * 获取所有租户数据
	 */
	public function getTenants() {
		$m = M ( "Tenant" );
		$fields = array (
				"tnt_username",
				"tnt_id",
				"tnt_password",
				"tnt_tel",
				"tnt_regtime",
				"tnt_lasttime",
				"tnt_note",
				"tnt_isrun" ,
				"tnt_max_persons"
		);
		//判断是否有关键字、若有则进行关键字查询
		if (! empty ( $_POST ['keywords'] )) {
			$where ['tnt_username'] = array (
					'like',
					'%' . $_POST ['keywords'] . '%' 
			);
			$res = $m->field ( $field )->where ( $where )->select ();
		} else {
			$res = $m->field ( $field )->select ();
		}
		if (! $res) {
			$this->returnAjax ( false );
		}
		//去掉前缀，主要为了防止Sql注入漏洞的发生
		$r = removePre ( $res, "tnt_" );
		$this->ajaxReturn ( $r );
	}
	/**
	 * 添加租户
	 */
	public function add() {
		$m = D ( "Tenant" );
		//创建Model类
		$model = M ( "Tenant" );
		if ($m->create ()) {
			$where['tnt_username'] = $_POST ["username"];
			$res = $model->where ( $where )->select();
			//若对应租户名已经存在则提示错误
			if (count($res) > 0) {
				$this->returnAjax ( false, "用户名已经存在！" );
			}
			//验证密码
			$_POST['password'] = md5($_POST['password']);
			//添加租户
			$id = $m->add ();
			$_POST ['id'] = $id;
			$id ? $this->returnAjax ( true, "增加租户成功！", $_POST ) : $this->returnAjax ( false, "添加失败！" );
		} else {
			$this->returnAjax ( false, "增加租户失败，原因：[" . $m->getError () . "]" );
		}
	}
	/**
	 * 删除租户
	 */
	public function delete() {
		$m = M ( "Tenant" );
		empty ( $_POST ['id'] ) && $this->returnAjax ( false, "错误" );
		$id = $_POST ['id'];
		$m->delete ( $id ) ? $this->returnAjax ( true, "删除成功" ) : $this->returnAjax ( false, "删除失败" );
	}
	
	/**
	 * 修改租户信息
	 */
	public function update() {
		if (empty ( $_POST ['id'] )) {
			$this->returnAjax ( "false" );
		}
		$m = D ( "Tenant" );

		if ($m->create ()) {
			
			$id = $m->save ();
			// $_POST['id'] = $id;
			$_POST['password'] = md5($_POST['password']);
			$id ? $this->returnAjax ( true, "修改租户信息成功！", $_POST ) : $this->returnAjax ( false, "修改租户信息失败！" . $m->getError () );
		} else {
			$this->returnAjax ( false, "修改租户信息失败，原因：[" . $m->getError () . "]" );
		}
	}
	/**
	 * 设置租户状态调为可用
	 */
	public function run() {
		//判断参数
		if (empty ( $_POST ['id'] )) {
			$this->returnAjax ( "false" );
		}
		$data = array (
				"tnt_isrun" => 1 
		);
		$m = M ( "Tenant" );
		$m->where ( "tnt_id={$_POST['id']}" )->save ( $data ) ? $this->returnAjax ( true, "启用该租户成功！" ) : $this->returnAjax ( false, "启用该租户失败！" );
	}
	/**
	 * 将租户状态调为不可用
	 */
	public function unrun() {
		if (empty ( $_POST ['id'] )) {
			$this->returnAjax ( "false" );
		}
		$data = array (
				"tnt_isrun" => 0 
		);
		$m = M ( "Tenant" );
		$m->where ( "tnt_id={$_POST['id']}" )->save ( $data ) ? $this->returnAjax ( true, "停用该租户成功！" ) : $this->returnAjax ( false, "停用该租户失败！" );
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