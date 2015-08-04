<?php
namespace Tenant\Model;

use Think\Model;

class TenantModel extends Model {
	protected $_validate = array (
			array (
					'tnt_username',
					'require',
					'帐号名称已经存在！' 
			), // 在新增的时候验证name字段是否唯一
			array (
					'tnt_password',
					'require',
					'密码不能为空！' 
			),
			
			// 在新增的时候验证name字段是否唯一
			array (
					'tnt_tel',
					'require',
					'联系方式不能为空' 
			),
			
			// 在新增的时候验证name字段是否唯一
			array (
					'tnt_regtime',
					'require',
					'注册时间不正确' 
			),
			
			// 在新增的时候验证name字段是否唯一
			array (
					'tnt_lasttime',
					'require',
					'最后登录时间不正确' 
			), // 在新增的时候验证name字段是否唯一
			array (
					'tnt_isrun',
					'require',
					'启用不正确' 
			), // 在新增的时候验证name字段是否唯一
			array (
					'tnt_note',
					'require',
					'备注不能为空' 
			) 
	);
	protected $_auto = array (
			array (
					'tnt_password',
					'md5',
					3,
					'function' 
			) 
	) // 对password字段在新增和编辑的时候使md5函数处理
;
	protected $_map = array (
			"username" => "tnt_username",
			"password" => "tnt_password",
			"tel" => "tnt_tel",
			"regtime" => "tnt_regtime",
			"lasttime" => "tnt_lasttime",
			"note" => "tnt_note",
			"isrun" => "tnt_isrun",
			"id" => "tnt_id" 
	);
}
?>