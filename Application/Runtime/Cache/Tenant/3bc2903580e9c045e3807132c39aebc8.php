<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>租户管理员</title>
<script type="text/javascript" src="/Public/jui/jquery.min.js"></script>
<script type="text/javascript" src="/Public/jui/jquery.easyui.min.js"></script>
<script type="text/javascript" src="/Public/jui/locale/easyui-lang-zh_CN.js"></script>
<link href="/Public/jui/themes/default/easyui.css" type="text/css" rel="stylesheet" />
<link href="/Public/jui/themes/icon.css" type="text/css" rel="stylesheet" />
<script>
	$(function() {
		getSysInfo();
	});
	function getSysInfo() {
		$.post("/Tenant/Index/getSysInfo", null, function(data) {
			if (data.status) {
				content = data.content;
				$("#sys_time").html(content['sys_time']);
				$("#sys_tenant_total").html(content['sys_tenant_total']);
				isrun = content['sys_tenant_isrun'];
				$("#sys_tenant_isrun").html(isrun);

				$("#sys_time_bj").html(content['sys_time_bj']);
				$("#sys_env").html(content['sys_env']);
				$("#sys_os").html('<font color="blue">' + content['sys_os']+ '</font>');
				$("#sys_server_name").html(content['sys_server_name']);
				$("#sys_server_ip").html(content['sys_server_ip']);
				$("#sys_space").html(content['sys_space']);
				$("#sys_max_upload").html(content['sys_max_upload']);
			}
		});
	}
	function getCenter() {
		var title = $('#cc').layout('panel', 'center').panel("options").title;
		alert(title);
	}
	function logout() {
		$.messager.confirm('确认退出', '您确认退出吗？', function(r) {
			if (r) {
				$.get("/Tenant/Login/doLogout",null,function(data){
					if(data['status']){
						$(window).attr("location", "/Tenant/Index/index");
					}else{
						alert("退出登录失败");
					}
				});
				
			}
		});
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
	function changeSecret() {
		$("#cha_username").val("<?php echo (session('tenant_username')); ?>");
		$("#win-changeSecret").window("open");
		$('#form_changeSecret').form({
			url : '/Tenant/Index/changeSecret',
			onSubmit : function() {
				if (!$('#form_changeSecret').form('validate')) {
					show_slide("错误", "请填写正确表单信息");
					return false;
				}
				return true;
			},
			success : function(data) {
				var obj = eval('(' + data + ')');
				if (obj['status']) {
					data = obj['content'];
					$('#win-changeSecret').window("close");
					show_slide("修改密码成功", "修改密码成功，清牢记新密码！");
				} else {
					show_slide("修改密码失败", obj['info']);
					$('#cha_code_img').click();
				}
			}
		});
	}

</script>
</head>
<body class="easyui-layout" id="cc">
	<div data-options="region:'north'" style="width: 100px; height: 30px;">
现在是：<span id="time_LiHot"></span>
<script language="JavaScript">
setTime_LiHot();
function setTime_LiHot(){
	var dt=new Date();
	var arr_week=new Array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
	var strWeek=arr_week[dt.getDay()];
	var strHour=dt.getHours();
	var strMinutes=dt.getMinutes();
	var strSeconds=dt.getSeconds();
	var time_LiHot = document.getElementById("time_LiHot");
        if (strHour<10) strHour="0"+strHour;
	if (strMinutes<10) strMinutes="0"+strMinutes;
	if (strSeconds<10) strSeconds="0"+strSeconds;
	var strYear=dt.getFullYear()+"年";
	var strMonth=(dt.getMonth()+1)+"月";
	var strDay=dt.getDate()+"日";
	var strTime=strHour+":"+strMinutes+":"+strSeconds;
	time_LiHot.innerHTML=strYear+strMonth+strDay+" <font color='red'>"+strTime+"</font> "+strWeek;
}
setInterval("setTime_LiHot()",1000);
</script>
	
		 <a onclick="logout();" href="#"
			class="easyui-linkbutton c8" style="width: 100px; float: right;"
			iconcls="icon-no">退出</a> <a onclick="changeSecret();" href="#"
			class="easyui-linkbutton c8" style="width: 100px; float: right;"
			iconcls="icon-edit">修改密码</a>
		<div class="easyui-window"
			data-options="title:'修改密码',closed:true,resizable:false,iconCls:'icon-edit',collapsible:false,minimizable:false,maximizable:false,draggable:true,modal:true
			,onClose:function(){$('#form_changeSecret').form('clear');}"
			id="win-changeSecret" style="width: 350px; height: 300px;"
			align="center">
			<form method="post" id="form_changeSecret">
				<table cellpadding="5">
					<tr>
						<td>管理员帐号：</td>
						<td><label><?php echo (session('tenant_username')); ?></label><input
							id="cha_username" name="cha_username" type="hidden"
							value="<?php echo (session('tenant_username')); ?>" /></td>
					</tr>
					<tr>
						<td>原始密码：</td>
						<td><input type="text" name="cha_password_old"
							class="easyui-textbox" required="required" /></td>
					</tr>
					<tr>
						<td>新密码：</td>
						<td><input type="text" name="cha_password_new"
							class="easyui-textbox" required="required" /></td>
					</tr>
					<tr>
						<td>验证码：</td>
						<td><input type="text" name="cha_code" class="easyui-textbox"
							required="required" /></td>
					</tr>
					<tr>
						<td></td>
						<td><img style="width: 180px; height: 50px" alt="点击刷新"
							id="cha_code_img" src="/Tenant/Code/getCode"
							onclick="javascript:this.src='/Tenant/Code/getCode?'+Math.random();" /></td>
					</tr>
					<tr>
						<td></td>
						<td><a href="#" class="easyui-linkbutton"
							style="width: 100px;" iconcls="icon-edit"
							onclick="$('#form_changeSecret').form('submit');">修改</a>
					</tr>
				</table>
			</form>
		</div>
	</div>
	<div data-options="region:'west',title:'系统信息',split:false"
		style="width: 230px; padding-left: 2px; padding-right: 2px;">
		<div style="width: 224px;" class="easyui-panel" title="系统状态"
			data-options="tools:'#menu_sysinfo'">
			<div id="menu_sysinfo">
				<a onclick="getSysInfo();" href="#" class="easyui-linkbutton c8"
					iconcls="icon-reload">刷新</a>
			</div>
			<div>租户信息：</div>
			<div style="margin: 5px">
				<table>
					<tr>
						<td>租户账号：</td>
						<td id="sys_tenant_total">-</td>
					</tr>
					<tr>
						<td>注册时间：</td>
						<td id="sys_tenant_isrun">-</td>
					</tr>
					<tr>
						<td>最后登录：</td>
						<td id="sys_os">-</td>
					</tr>
					<tr>
						<td>联系电话：</td>
						<td id="sys_max_upload">-</td>
					</tr>
					<tr>
						<td>最多用户：</td>
						<td id="sys_server_name">-</td>
					</tr>
					<tr>
						<td>北京时间：</td>
						<td id="sys_time_bj">-</td>
					</tr>
					<tr>
						<td>系统时间：</td>
						<td id="sys_time">-</td>
					</tr>
					<tr>
						<td>服务器IP：</td>
						<td id="sys_server_ip">-</td>
					</tr>
					<tr>
						<td>剩余空间：</td>
						<td id="sys_space">-</td>
					</tr>
					<tr>
						<td>运行环境：</td>
						<td id="sys_env">-</td>
					</tr>

				</table>
			</div>
		</div>
		<div style="width: 224px;" class="easyui-panel" title="系统公告"
			data-options="tools:'#menu_sysoperation'">
			<div id="menu_sysoperation">
				<a onclick="$('#repair').siblings().linkbutton('enable');$('#unrepair').siblings().linkbutton('enable');" href="#" class="easyui-linkbutton c8"
					iconcls="icon-reload">刷新</a>
			</div>
			<div style="margin: 5px">
				<table>
					<?php if(is_array($news)): $i = 0; $__LIST__ = $news;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$n): $mod = ($i % 2 );++$i;?><tr title="<?php echo ($n['title']); ?>">
						<td><?php echo (date("Y-m-d",$n['time'])); ?></td>
						<td title="<?php echo ($n['title']); ?>:<?php echo ($n['content']); ?>"><?php echo (substr($n['title'],0,20)); ?>...</td>
						<!--td>
						<a title="<?php echo ($n['id']); ?>" onclick="doDelete($(this).attr('title'));" href="#"
							class="easyui-linkbutton c8" iconcls="icon-no">删除</a>
						</td-->
					</tr><?php endforeach; endif; else: echo "" ;endif; ?>
				</table>
			</div>
		</div>
	</div>
	<div data-options="region:'center',title:'主体内容'"
		style="background: #eee;" href="center.html" style="overflow:hidden;">
	</div>
</body>
</html>