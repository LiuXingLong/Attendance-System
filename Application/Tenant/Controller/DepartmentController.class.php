<?php
namespace Tenant\Controller;

use Think\Controller;
use \Think\Model;
use Org\Util\Date;

class DepartmentController extends Controller {
	private $m = null;
	private function M() {
		return $m === null ? $this->m = M ( "Department" ) : $this->m;
	}
	public function index() {
		// $this->display ();
	}
	/**
	 * 获取所有租户数据
	 */
	public function getDepTree() {
		$m = M ( "Department" );
		$fields = array (
				"dep_id",
				"dep_pid",
				"dep_isleaf",
				"dep_note",
				"dep_name"
		);
		$where = array("dep_tnt_id"=>$_SESSION['tnt_id']);
		$res = $m->field ( $field )->where($where)->order("dep_id")->select ();
		if (! $res) {
			$this->returnAjax ( false );
		}
		$r = removePre ( $res, "tnt_" );
		$this->ajaxReturn($this->tree2($r,0) );
		// print_r ( $this->getTree($r) );
		// $this->ajaxReturn ( $r );
	}
	/**
	 * 构造组织结构树
	 *
	 * @param unknown $deps        	
	 */
	private function tree2($data,$pid){
		$arr = array();
		foreach ($data as $v){
			if($pid == $v['pid']){
				//递归子树...
				$temp = $this->tree2($data, $v['id']);
				if($temp){
					$v['children'] = $temp;
					$v ['state'] = "closed";
				}else{
					$v['isleaf'] = true;
					
				}
				//添加进returnArray
				$arr[] = $v;
			}
		}
		return $arr;
	}
	/**
	 * 添加租户
	 */
	public function add() {
		$m = D ( "Department" );
		$model = M ( "Department" );
		if ($m->create ()) {
			$data ['dep_tnt_id'] =  $_SESSION["tnt_id"];
			$data ['dep_pid'] =  $_POST['dep_pid'];
			$data ['dep_isleaf'] =  1;
			$data ['dep_note'] =  $_POST['dep_note'];
			$data ['dep_name'] =  $_POST['dep_name'];
			$id = $m->add ($data);
			$data ['dep_id'] =  $id;
			$res = removePre($data, "dep_");
			if($id){
				$dt['dep_id'] = $_POST['dep_pid'];
				$dt['dep_isleaf'] =  0;
				$m->save($dt);
				$this->returnAjax ( true, "增加机构成功！", $res ) ; 
			}else{
				$this->returnAjax ( false, "添加失败！" );
			}
		} else {
			$this->returnAjax ( false, "增加机构失败，原因：[" . $m->getError () . "]" );
		}
	}
	/**
	 * 删除租户
	 */
	public function delete() {
		$m = M ( "Department" );
		empty ( $_POST ['dep_id'] ) && $this->returnAjax ( false, "错误" );
		$id = $_POST ['dep_id'];
		$m->delete ( $id ) ? $this->returnAjax ( true, "删除成功" ) : $this->returnAjax ( false, "删除失败" );
	}
	
	/**
	 * 更新租户信息
	 */
	public function update() {
		if (empty ( $_POST ['dep_id'] )) {
			$this->returnAjax ( "false" );
		}
		$data = array();
		$data['dep_id'] = $_POST['dep_id'];
		$data['dep_name'] = $_POST['dep_name'];
		$data['dep_note'] = $_POST['dep_note'];
		$m = D ( "Department" );
		if ($m->create ()) {
			$id = $m->save ();
			// $_POST['id'] = $id;
			$id ? $this->returnAjax ( true, "修改机构信息成功！", $data ) : $this->returnAjax ( false, "修改机构信息失败！" . $m->getError () );
		} else {
			$this->returnAjax ( false, "修改机构信息失败，原因：[" . $m->getError () . "]" );
		}
	}

	public function outExcel() {
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