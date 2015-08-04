<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="text/html" charset="UTF-8">
<title>Manager登录</title>
<script type="text/javascript" src="/Public/jui/jquery.min.js"></script>
<script type="text/javascript" src="/Public/jui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="/Public/jui/locale/easyui-lang-zh_CN.js"></script>
<link href="/Public/jui/themes/default/easyui.css" type="text/css" rel="stylesheet" />
<link href="/Public/jui/themes/icon.css" type="text/css" rel="stylesheet" />
<script>
	$(function() {
		$('#loginForm').form({
			url : '/Manager/Login/doLogin',
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
					window.location="/Manager/Index/index";
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
		title="Manager登录" style="width: 400px">
		<div style="padding: 10px 60px 20px 60px">
			<form id="loginForm" method="post" action="/Manager/Login/doLogin">
				<table cellpadding="5">
					<tr>
						<td>租户:</td>
						<td>
							<select class="easyui-combobox" name="tnt_id" style="width:100px;" data-options="editable:false">   
    							<?php if(is_array($managers)): $i = 0; $__LIST__ = $managers;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["tnt_id"]); ?>"><?php echo ($vo["tnt_username"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>  
							</select> 
						</td>
					</tr>
					<tr>
						<td>账号:</td>
						<td><input type="text" name="uid" class="easyui-textbox" required="required"/></td>
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
							src="/Manager/Code/getCode"
							onclick="javascript:this.src='/Manager/Code/getCode?'+Math.random();"></td>
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