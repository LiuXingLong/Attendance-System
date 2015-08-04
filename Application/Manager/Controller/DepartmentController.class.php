<?php
namespace Manager\Controller;

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
		$manager = M("Manager");
		$m_where['mng_id'] = $_SESSION['mng_id'];
		$deps = $manager->field("mng_deplist")->where($m_where)->select();
	//	tag_debug($deps);
	//	$this->ajaxReturn(false,$deps[0]["mng_deplist"] );
		if(!empty($deps)){
			$m = M ( "Department" );
			$fields = array (
					"dep_id",
					"dep_pid",
					"dep_isleaf",
					"dep_note",
					"dep_name"
			);
			
		//	$deps[0]["mng_deplist"] .= ",1";
			$tmp_arr = split(",", $deps[0]["mng_deplist"]);
			$where["dep_tnt_id"] = $_SESSION['tnt_id'];
			$where['dep_id'] = array("in",$tmp_arr);
			$res = $m->field ( $field )->where($where)->select ();

			$c = count($res);
			$this->completeTree($res,$c);
		//	$res = $this->tree2($res, 0);
		//	tag_debug($res);
		//	$res = $m->query("select dep_id,dep_pid,dep_isleaf,dep_note,dep_name from department where dep_isleaf=0 or dep_id in ({$deps[0]['mng_deplist']})");
		//	echo $deps[0]["mng_deplist"];
		//		print_r($res);
			if (! $res) {
				$this->returnAjax ( false );
			}
			$r = removePre ( $res, "tnt_" );
			$tree = $this->tree2($r,0);
	//		tag_debug($tree);
	//		print_r($tree);
			$this->ajaxReturn( $tree);
			// print_r ( $this->getTree($r) );
			// $this->ajaxReturn ( $r );
		}else{
			$this->returnAjax(false,"系统繁忙！");
		}

	}
	private function completeTree(&$res,$c=0){
		$m = M("Department");
		$fields = array (
				"dep_id",
				"dep_pid",
				"dep_isleaf",
				"dep_note",
				"dep_name"
		);
		foreach ($res as $k => $v){
			if($v['dep_pid'] != 0){
			//	$this->completeTree($res, $v['dep_pid']);
				
				$where2['dep_id'] = $v['dep_pid'];
				$res2 = $m->field($field)->where($where2)->order('dep_id')->select();
			//	tag_debug($res2);
				if(empty($res2)){
					return;
				}
				$tmp_res = $res;
				$flag = true;
				foreach ($tmp_res as $k1 => $v1){
					if($v1['dep_id'] == $res2[0]['dep_id']){
					//	return;
						$flag = false;
					}
				}	
				if($flag){
					$res[] = $res2[0];
					$this->completeTree($res,$c);
				}
			}
		}
		
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
			$id ? $this->returnAjax ( true, "增加机构成功！", $res ) : $this->returnAjax ( false, "添加失败！" );
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