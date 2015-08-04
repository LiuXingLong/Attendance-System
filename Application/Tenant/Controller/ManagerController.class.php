<?php
namespace Tenant\Controller;

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
	public function listdep(){

		$m = M("Department");
		$res = $m->order("dep_id")->select();
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
	//	print_r($arr);

		$this->assign("listdep",$v);
		$this->display("Index/listdep");
	}
	public function listdep2(){
	
		$m = M("Department");
		$res = $m->field("dep_name,dep_id")->order("dep_id")->select();
		$per_id = $_GET['per_id'];

	
		//	print_r($arr);
	
		$this->assign("per_id_old",$per_id);
		$this->assign("listdep",$res);
		$this->display("Index/listdep2");
	}
	public function changePersonDep(){
		$per_id_old=$_REQUEST['per_id_old'];
		$per_id_new=$_REQUEST['per_id_new'];
		$data['per_dep_id']=$per_id_new;
		$data['per_id']=$per_id_old;
		$m=M("Person");
		if($m->save($data)){
			$this->returnAjax(true,"修改成功");
		}else
		{
			$this->returnAjax(false,"修改失败");
		}
	}
	private function prn($data,$r,$l=0){
		$r = $r ? $r : array();
		static $res = "";

		$tnbsp = "";
		$tl = $l+1;
		for ($i = 0; $i<$tl; $i++){
			$tnbsp.="&nbsp;&nbsp;";
		}
		$tnbsp .="┗";
		foreach ($data as $v){
			if($v['isleaf']){
				if(in_array($v['id'], $r)){
					$res .= $tnbsp.sprintf("<input type='checkbox' checked='true' name='mng_listdep' id='mng_listdep' value='%s'>%s<br/>",$v['id'],$v['name']);
				}else{
					$res .= $tnbsp.sprintf("<input type='checkbox' name='mng_listdep' id='mng_listdep' value='%s'>%s<br/>",$v['id'],$v['name']);
				}
				
			}else {
				$res .= "<hr/>".$tnbsp.$v['name']."<br/>";
				$this->prn($v['children'],$r,$tl);
			}
		}
		return $res;
	}
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
	public function changeDeplist(){
		
		if(empty($_POST['mng_id'])){
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