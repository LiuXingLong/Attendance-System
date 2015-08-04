<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="text/html" charset="UTF-8">
<title>租户登录</title>
<script type="text/javascript" src="/Public/jui/jquery.min.js"></script>
<script type="text/javascript" src="/Public/jui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="/Public/jui/locale/easyui-lang-zh_CN.js"></script>
<link href="/Public/jui/themes/default/easyui.css" type="text/css" rel="stylesheet" />
<link href="/Public/jui/themes/icon.css" type="text/css" rel="stylesheet" />
<script>
	$(function() {
		$('#loginForm').form({
			url : '/Tenant/Login/doLogin',
			onSubmit : function() {
				// do some check    
				// return false to prevent submit; 
				if (!$('#loginForm').form('validate'))
					return false;
				return true;
			},
			success:function(data){ 
				var obj=eval('('+data+')');
				if(obj['status']){
					window.location="/Tenant/Index/index";
				}else{
					show_slide("登录失败",obj['info'])   ;
					$("#login_code_img").click();
				}  
		    }  
		});
	});
	function doPostLoginForm() {
		$('#loginForm').submit();
	}
	function show_slide(v_title, v_msg) {
		v_title = v_title ? v_title : "消息提示";
		v_msg = v_msg ? v_msg : "有新消息";
		$.messager.show({
			title : v_title,
			msg : v_msg,
			timeout : 5000,
			showType : 'slide'
		});
	}
</script>
</head>
<body>
	<div style=""></div>
	<div class="easyui-window"
		data-options="modal:true,closable:false,collapsible:false,minimizable:false,maximizable:false,draggable:false,resizable:false"
		title="租户登录" style="width: 400px">
		<div style="padding: 10px 60px 20px 60px">
			<form id="loginForm" method="post" action="/Tenant/Login/doLogin">
				<table cellpadding="5">
					<tr>
						<td>用户名:</td>
						<td><input type="text" name="name" class="easyui-textbox" required="required"/></td>
					</tr>
					<tr>
						<td>密码:</td>
						<td><input type="password" name="password" class="easyui-textbox" required="required"/></td>
					</tr>
					<tr>
						<td>验证码:</td>
						<td><input type="text" name="code" class="easyui-textbox" required="required"/></td>
					</tr>
					<tr>
						<td></td>
						<td><img style="width: 180px; height: 50px" alt="点击刷新" id="login_code_img"
							src="/Tenant/Code/getCode"
							onclick="javascript:this.src='/Tenant/Code/getCode?'+Math.random();"></td>
					</tr>
				</table>
			</form>
			<div style="text-align: center;">
				<a href="javascript:void(0)" class="easyui-linkbutton c1"
					style="width: 100px" onclick="doPostLoginForm();">安全登录</a>
			</div>
		</div>
	</div>

</body>
</html>