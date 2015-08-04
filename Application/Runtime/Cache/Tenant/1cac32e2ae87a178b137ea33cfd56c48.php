<?php if (!defined('THINK_PATH')) exit();?>
<script type="text/javascript">
	$(function() {
		$('#dg_par3').datagrid({
			url : '/Tenant/Manager/getManagers',
			method : 'get',
			lines : true,
			rownumbers : true,
			idField : 'id',
			singleSelect : true,
			treeField : 'name',
			height : "100%",
			fitColumns : true,
			toolbar : "#tb3",
			//				width : "50%",
			columns : [ [ {
				field : 'name',
				title : '姓名',
				width : 10,
				editor : 'text'
			},  {
				field : 'uid',
				title : '工号',
				width : 15,
				align : 'center',
				editor : 'text'
			}, {
				field : 'password',
				title : '密码',
				width : 20,
				align : 'center',
				editor : 'text'
			}, {
				field : 'email',
				title : '邮箱',
				width : 15,
				align : 'center',
				editor : 'text'
			}, {
				field : 'note',
				title : '备注',
				width : 20,
				align : 'center',
				editor : 'text'
			}, {
				field : 'deplist',
				title : '关系部门',
				width : 30,
				align : 'center'
			//	editor : 'text'
			} ] ]
		});
	});
	
	
	function tb3_add() {
		$('#tb3_add_win').window("open");
		$('#tb3_add_form').form({
			url : '/Tenant/Manager/add',
			onSubmit : function() {
				if (!$('#tb3_add_form').form('validate')) {
					show_slide("错误", "请填写正确表单信息");
					return false;
				}
				return true;
			},
			success : function(data) {
				obj = eval('(' + data + ')');
				if (obj['status']) {
					show_slide("添加成功", "添加机构成功");
					$('#tb3_add_win').window("close");
					//在datagrid中添加行
				//	data = obj['content'];
					data = obj['content'];
					$('#dg_par3').datagrid('insertRow', {
						index : 0,
						row : {
							id : data['mng_id'],
							name : data['mng_name'],
							sex:data['mng_sex'],
							password:data['mng_password'],
							email:data['mng_email'],
			//				deplist:data['mng_deplist'],
							note:data['mng_note'],
							uid:data['mng_uid']
						} 
					});

				} else {
					show_slide("添加失败", obj['info']);
				}
			}
		});
	}

	function tb3_del() {
		row = $('#dg_par3').datagrid('getSelected');

		if (row) {
			index = $('#dg_par3').datagrid('getRowIndex', row.id);
			$.messager.confirm('删除人员', '确定删除？', function(r) {
				if (r) {
					// 退出操作;
					$.post("/Tenant/Manager/delete", {
						mng_id : row['id']
					}, function(data) {
						if (data['status']) {
							show_slide("删除成功", "[<font color='blue'>"
									+ row['name'] + "</font>]已经成功被删除！");

							$('#dg_par3').datagrid('deleteRow', index);
						}
					});
				}
			});
		}
	}
	
	var editingId3;

	function tb3_edit() {
		if (editingId3 != undefined) {
			$('#dg_par3').datagrid('selectRecord', editingId3);
			return;
		}
		row = $('#dg_par3').datagrid('getSelected');
		index = $('#dg_par3').datagrid('getRowIndex', row.id);
		if (row) {
			editingId3 = row.id;
			$('#dg_par3').datagrid('beginEdit', index);
		}

	}
	function tb3_save() {
		if (editingId3 != undefined) {
			$.messager.confirm('保存', '是否保存？', function(r) {
				if (r) {
					// 退出操作;
					row = $('#dg_par3').datagrid('getSelected');
					index = $('#dg_par3').datagrid('getRowIndex', row.id);
					$('#dg_par3').datagrid('endEdit',index);
					$.post("/Tenant/Manager/update", {
						mng_id : row['id'],
						mng_uid : row['uid'],
						mng_name : row['name'],
						mng_sex : row['sex'],
						mng_password : row['password'],
						mng_email : row['email'],
						mng_note : row['note'],
					}, function(data) {
						if (data['status']) {
							row = $('#dg_par3').datagrid('getSelected');
							index = $('#dg_par3').datagrid('getRowIndex', row.id);
						//	$('#dg_par3').datagrid('endEdit', index);
							r = data['content'];
							$('#dg_par3').datagrid('updateRow',{
								index: index,
								row: {
									id : r['id'],
									uid : r['uid'],
									name : r['name'],
									sex : r['sex'],
									password : r['passowrd'],
									email : r['email'],
									note : r['note']
								}
							});
							editingId3 = undefined;
							
							show_slide("保存成功", "已经成功保存！");
						} else {
							tb2_cancel();
							show_slide("失败", "保存失败!");
						}
					});
				} else {
					tb3_cancel();
					show_slide("失败", "保存失败！");
				}
			});
		}
	}
	function tb3_cancel() {
		if (editingId3 != undefined) {
			row = $('#dg_par3').datagrid('getSelected');
			index = $('#dg_par3').datagrid('getRowIndex', row.id);
			$('#dg_par3').datagrid('cancelEdit', index);
			editingId3 = undefined;
		}
	}
	function tb3_listdep(){
		row = $('#dg_par3').datagrid('getSelected');
		if(row){
			$('#listdep').window('refresh','/Tenant/Manager/listdep?mng_id='+row['id']);
			$('#listdep').window('open');
		}
	}
</script>
<div id="listdep" class="easyui-window" title="修改管理部门" style="width:600px;height:500px"   
        data-options="iconCls:'icon-save',modal:true,closed:true">   

</div>  
<div data-options="region:'north',collapsed:false,height:28">
				<div id="part_search">
					工号: <input id="part_search_id" class="easyui-combobox" value="请选择"
						data-options="valueField:'id',textField:'username',url:'data.json',onSelect: function(rec){  
				  $('#search_uid').val(rec.id);}" />
					<a href="#" class="easyui-linkbutton" iconCls="icon-search"
						onclick="doSearch();">查询</a>
		</div>
</div>
<table title="点名者信息" class="easyui-datagrid" id="dg_par3">
</table>
<div id="tb3">
	<a href="#" class="easyui-linkbutton" iconCls="icon-add"
		onclick="tb3_add();">添加</a><a href="#" class="easyui-linkbutton"
		iconCls="icon-cancel" onclick="tb3_del();">删除</a><a href="#"
		class="easyui-linkbutton" iconCls="icon-edit" onclick="tb3_edit();">编辑</a><a
		href="#" class="easyui-linkbutton" iconCls="icon-save"
		onclick="tb3_save();">保存</a><a href="#" class="easyui-linkbutton"
		iconCls="icon-back" onclick="tb3_cancel();">取消</a><a href="#"
		class="easyui-linkbutton" iconCls="icon-reload" style="float: right;"
		onclick="$('#dg_par3').datagrid('reload');"></a><a href="#" class="easyui-linkbutton"
		iconCls="icon-edit" style="float:right;" onclick="tb3_listdep();">点名部门管理</a>
	<div id="tb3_add_win" class="easyui-window" title="添加人员"
		style="width: 350px; height: 350px;"
		data-options="iconCls:'icon-add',modal:true,closed:true">
		<div class="easyui-layout" data-options="fit:true" align="center">
			<form id="tb3_add_form" method="post">
				<table cellpadding="5">
					<tr>
						<td>姓名:</td>
						<td><input id="tb3_name" class="easyui-textbox"
							data-options="required:true" type="text" name="mng_name" /></td>
					</tr>
					<tr>
						<td>工号:</td>
						<td><input id="tb3_uid" class="easyui-textbox"
							data-options="required:true" type="text" name="mng_uid" /></td>
					</tr>
					<tr>
						<td>密码:</td>
						<td><input id="tb3_password" class="easyui-textbox"
							data-options="required:true" type="text" name="mng_password" /></td>
					</tr>
					<tr>
						<td>性别:</td>
						<td><input id="tb3_sex" class="easyui-textbox"
							data-options="required:true" type="text" name="mng_sex" /></td>
					</tr>
					<tr>
						<td>邮箱:</td>
						<td><input id="tb3_email" class="easyui-textbox"
							data-options="required:true" type="text" name="mng_email" /></td>
					</tr>
					<tr>
						<td>备注:</td>
						<td><input id="tb3_note" class="easyui-textbox"
							data-options="required:true" type="text" name="mng_note" /></td>
					</tr>
					<tr>
						<td></td>
						<td><a href="javascript:void(0)" class="easyui-linkbutton"
							style="width: 100px;" iconcls="icon-add"
							onclick="$('#tb3_add_form').form('submit');">添加人员</a>
					</tr>
				</table>
			</form>
		</div>
	</div>
</div>