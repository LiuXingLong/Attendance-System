<?php
namespace Tenant\Controller;

use Think\Controller;
use \Think\Model;
use Org\Util\Date;

class PersonController extends BaseController {
	private $m = null;
	private function M() {
		return $m === null ? $this->m = M ( "Person" ) : $this->m;
	}
	public function index() {
		// $this->display ();
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
		$res = $m->field ( $field )->where ( $where )->order('per_uid')->select ();

		if (! $res) {
			$this->returnAjax ( false );
		}
		$r = removePre ( $res, "per_" );
		$this->ajaxReturn ( $r );
	}
	public function getPersonByDep(){
		if(empty($_GET['per_dep_id'])){
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
				"per_photo",
				"per_uid"
		);
		$where["per_dep_id"] = $_GET['per_dep_id'];
		$res = $m->field ( $field )->where ( $where )->order('per_uid')->select ();
		
		if (! $res) {
			foreach ($fields as $k=>$v){
				$res[0][$v] = "-";
			}
			$r = removePre ( $res, "per_" );
			$this->ajaxReturn ( $r);
		}else {
			foreach ($res as $k=>$v){
			  if($res[$k]['per_sex']=='0')$res[$k]['per_sex']='男';
			  else if($res[$k]['per_sex']=='1') $res[$k]['per_sex']='女';
			  else 	$res[$k]['per_sex']='-';		
			}
		}	
		$r = removePre ( $res, "per_" );
		$this->ajaxReturn ( $r );
	}
	/**
	 * 添加租户
	 */
	public function add() {
		$m = D ( "Person" );
		$model = M ( "Person" );
		
		$tenant = M("Tenant");
		$rrr = $tenant->field("tnt_max_persons")->where(array("tnt_id"=>$_SESSION['tnt_id']))->select();
		$max = count($model->field("per_id")->where(array("per_tnt_id"=>$_SESSION['tnt_id']))->select());
		if($rrr[0]['tnt_max_persons'] <= $max){
			$this->returnAjax(false,"你的用户数量已经达到最大值,无法继续添加,请联系管理员添加最大用户数!");
		}
		$_POST['per_tnt_id']= $_SESSION['tnt_id'];
		if ($m->create ()) {
			$where['per_uid'] = $_POST ["per_uid"];
			$res = $model->where ( $where )->select();
			if (count($res) > 0) {
				$this->returnAjax ( false, "该标识用户已经存在！" );
			}
			
			$id = $m->add ();
			$_POST ['per_id'] = $id;
			$id ? $this->returnAjax ( true, "增加成员成功！", $_POST ) : $this->returnAjax ( false, "添加失败！" );
		} else {
			$this->returnAjax ( false, "增加成员失败，原因：[" . $m->getError () . "]" );
		}
	}
	/**
	 * 删除租户
	 */
	public function delete() {
		$m = M ( "Person" );
		empty ( $_POST ['per_id'] ) && $this->returnAjax ( false, "错误请求!" );
		$id = $_POST ['per_id'];
		$m->delete ( $id ) ? $this->returnAjax ( true, "删除成功" ) : $this->returnAjax ( false, "删除失败" );
	}
	
	/**
	 * 更新租户信息
	 */
	public function update() {
		if (empty ( $_POST ['per_id'] )) {
			$this->returnAjax ( false );
		}
		$res = removePre($_POST, "per_");
		$m = D ( "Person" );
		if ($m->create ()) {
			$id = $m->save ();
			// $_POST['id'] = $id;
			
			$id ? $this->returnAjax ( true, "修改成员信息成功！", $_POST ) : $this->returnAjax ( false, "修改成员信息失败！" . $m->getError (),$res );
		} else {
			$this->returnAjax ( false, "修改成员信息失败，原因：[" . $m->getError () . "]",$res  );
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