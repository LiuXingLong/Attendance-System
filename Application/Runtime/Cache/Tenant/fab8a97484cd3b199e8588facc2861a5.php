<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="text/html" charset="UTF-8">
<title>租户登录</title>
<script type="text/javascript" src="/22/Public/jui/jquery.min.js"></script>
<script type="text/javascript" src="/22/Public/jui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="/22/Public/jui/locale/easyui-lang-zh_CN.js"></script>
<link href="/22/Public/jui/themes/default/easyui.css" type="text/css" rel="stylesheet" />
<link href="/22/Public/jui/themes/icon.css" type="text/css" rel="stylesheet" />
<script>
	$(function() {
		$('#loginForm').form({
			url : '/22/Tenant/Login/doLogin',
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
					window.location="/22/Tenant/Index/index";
				}else{
					show_slide("登录失败",obj['info']);
					$("#login_code_img").click();
				}  
		    }  
		});
		$('#registerForm').form({
			url : '/22/Tenant/Login/doRegister',
			onSubmit : function() {
				// do some check    
				// return false to prevent submit; 
				if (!$('#registerForm').form('validate'))
					return false;
				return true;
			},
			success:function(data){ 
				var obj=eval('('+data+')');
				if(obj['status']){
					window.location="/22/Tenant/Index/index";
				}else{
					show_slide("注册失败",obj['info']);					
				}  
		    }  
		});
	});
	function doPostLoginForm() {
		$('#loginForm').submit();
	}
	function doPostRegisterForm() {
		$('#registerForm').submit();
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
	function register(){
		$('#loginForm').css("display","none");
		$('#registerForm').css("display","block");
		$('.panel-title').text(function(i,origText){
			return "租户注册";
		});
	}
</script>
<style>
 #registerForm input{
 	width: 151px;
 }
</style>
</head>
<body>
	<div class="easyui-window" data-options="modal:true,closable:false,collapsible:false,minimizable:false,maximizable:false,draggable:false,resizable:false" title="租户登录" style="width: 400px">
		<div style="padding: 10px 60px 20px 60px">			
			<form id="loginForm" method="post" action="/22/Tenant/Login/doLogin" style="display:block">
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
							src="/22/Tenant/Code/getCode"
							onclick="javascript:this.src='/22/Tenant/Code/getCode?'+Math.random();"></td>
					</tr>
				</table>
			<div style="text-align: center;">
				<a href="javascript:void(0)" class="easyui-linkbutton c1" style="width: 80px" onclick="register();">注册</a>&nbsp&nbsp		
				<a href="javascript:void(0)" class="easyui-linkbutton c1" style="width: 80px" onclick="doPostLoginForm();">登录</a>
			</div>
			</form>	
			<form id="registerForm" method="post" action="/22/Tenant/Login/doRegister" style="display:none">
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
						<td>确认密码:</td>
						<td><input type="password" name="password1" class="easyui-textbox" required="required"/></td>
					</tr>
					<tr>
						<td>电话:</td>
						<td><input type="text" name="telephone" class="easyui-textbox" required="required"/></td>
					</tr>
					<tr>
						<td>邮箱:</td>
						<td><input type="text" name="email" class="easyui-textbox" required="required"/></td>
					</tr>							
				</table>
				<div  style="text-align: center;">		
				<a href="javascript:void(0)" class="easyui-linkbutton c1" style="width: 100px" onclick="doPostRegisterForm();">确认</a>
			</div>
			</form>	
		</div>
	</div>
</body>
</html>