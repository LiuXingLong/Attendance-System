<?php
namespace Common\TagLib;
use Think\Template\TagLib;
Class Mytags extends TagLib {
	protected $tags = array (
			
			// 定义标签
			'commonfiles' => array (
					'close' => 0 
			) 
	) // input标签
;
	public function _commonfiles($attr, $content) {
		$str = '<script type="text/javascript" src="__PUBLIC__/jui/jquery.min.js"></script>
<script type="text/javascript" src="__PUBLIC__/jui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="__PUBLIC__/jui/locale/easyui-lang-zh_CN.js"></script>
<link href="__PUBLIC__/jui/themes/default/easyui.css" type="text/css" rel="stylesheet" />
<link href="__PUBLIC__/jui/themes/icon.css" type="text/css" rel="stylesheet" />';
		return $str;
	}
}
?>