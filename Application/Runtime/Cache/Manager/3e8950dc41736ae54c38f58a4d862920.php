<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css"
	href="/Public/css/bootstrap.css" />
<script type="text/javascript" src="/Public/jui/jquery.min.js"></script>
<title>微信签到系统绑定</title>
<script type="text/javascript">
	function checkBind(){
		tenant_id=$('#tnt_id').val();
		uid=$('#uid').val();
		password=$('#password').val();
		if(tenant_id.length==0 || uid.length==0 || password.length==0){
			alert("输入不能为空");
			return false;
		}
		openID="<?php echo ($openID); ?>";
		$.post("/Manager/Weixin/doBind",{"tnt_id":tenant_id,"uid":uid,"password":password,"openID":openID},function(data){
			if(data['status']){				
		//		alert("绑定成功!");
				$('.col-md-12').hide();
				$('.row').html("<font color='green'>绑定成功！注意：普通用户还需实名认证，请通过配置中的实名认证上传自己的头像后再使用！</font>").css("text-align","center");
			}else{
				alert(data['info']);
			}
		});
	}
</script>
</head>
<body>
	<div class="container-flow" role="main">
		<div class="row">
			<div class="col-md-12">
				<form>
					<table class="table table-striped table-bordered table-condensed">
						<tr>
							<th colspan="2" style="text-align:center">微信账号绑定</th>
						</tr>
						<tr>
							<td>租户:</td>
							<td><select name="tnt_id" id="tnt_id">
								<?php if(is_array($tnts)): $i = 0; $__LIST__ = $tnts;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$tnt): $mod = ($i % 2 );++$i;?><option value="<?php echo ($tnt["tnt_id"]); ?>"><?php echo ($tnt["tnt_username"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>	
							</select></td>
						</tr>
						<tr>
							<td>工号:</td>
							<td><input class="form-control" type="text" name="uid" id="uid"></td>
						</tr>
						<tr>
							<td>密码:</td>
							<td><input class="form-control" type="password" id="password"
								name="password"></td>
						</tr>
						<tr>
							<td></td>
							<td><button class="btn btn-info" style="width: 100%"
									type="button" onclick="checkBind();">立刻绑定</button></td>
						</tr>
					</table>
				</form>
			</div>
		</div>
	</div>
</body>
</html>