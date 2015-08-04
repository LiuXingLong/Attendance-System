<?php
namespace Manager\Controller;

use Think\Controller;
// 本类由系统自动生成，仅供测试用途
class CodeController extends Controller {
	Public function getCode() {
		$fontttf = rand(1, 7).".TTF";
		$config = array (
				'expire' =>30,  //验证码有效时间
				'fontSize' => 30, // 验证码字体大小
				'length' => 4, // 验证码位数
				'useNoise' => false ,
				'useZh'=>true,
				'fontttf'=>$fontttf,
//				'useImgBg'=>true,
//				'fontttf'=>"xingkai.ttf"
				'fontttf'=>"6.ttf"  // 字体类型
		); // 关闭验证码杂点
		$Verify = new \Think\Verify ( $config );
		$Verify->entry ();
	}
	public function check_verify($code, $id = '') {
		$verify = new \Think\Verify ();
		return $verify->check ( $code, $id );
	}
}
?>