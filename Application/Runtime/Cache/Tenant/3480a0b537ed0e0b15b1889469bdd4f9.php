<?php if (!defined('THINK_PATH')) exit();?>
<script type="text/javascript">
	$(function() {
		var cur_index = 0;
		$('#tg_partree').treegrid(
				{
					url : '/Tenant/Department/getDepTree',
					method : 'get',
					lines : true,
					//		rownumbers : true,
					idField : 'id',
					treeField : 'name',
					height : "100%",
					toolbar : "#tb1",
					singleSelect : true,
					//			width : "50%",
					fitColumns : true,
					columns : [ [ {
						field : 'id',
						hidden : true
					}, {
						title : '组织名称',
						field : 'name',
						width : 50,
						editor : 'text'
					}, {
						field : 'note',
						title : '备注',
						width : 30,
						editor : 'text'

					}, {
						field : 'size',
						title : '人数',
						width : 20,
						align : 'right'
					//						editor : 'text'
					} ] ],
					onClickRow : function(row) {
						if (row.id != cur_index) {
							//	alert(cur_index);
							cur_index = row.id;
//							if (row.isleaf)
								{
									$('#dg_par').datagrid("load",
										{per_dep_id:row.id});
								}
						}
					}
				});
		$('#dg_par').datagrid({
			url : '/Tenant/Person/getPersonByDep',
			method : 'get',
			lines : true,
			rownumbers : true,
			idField : 'id',
			singleSelect:true,
			treeField : 'name',
			height : "100%",
			fitColumns : true,
			toolbar : "#tb2",
			//				width : "50%",
			columns : [ [ {
				field : 'name',
				title : '姓名',
				width : 10,
				editor : 'text'
			},{
				field : 'uid',
				title : '标识',
				width : 20,
				align : 'center',
				editor : 'text'
			},  {
				field : 'sex',
				title : '性别',
				width : 10,
				align : 'center',
				editor : 'text'
			}, {
				field : 'password',
				title : '密码',
				width : 10,
				editor : 'text'
			}, {
				field : 'email',
				title : '邮箱',
				width : 20,
				editor : 'text'
			}, {
				field : 'note',
				title : '备注',
				width : 30,
				editor : 'text'
			}, {
				field : 'photo',
				title : '照片',
				width : 20,
				editor : 'text'
			} ] ]
		});
	});
	function tb1_add() {
		row = $('#tg_partree').treegrid('getSelected');
		if (!row)
			return;
		$('#tb1_add_win').window("open");
		$('#tb1_add_form').form({
			url : '/Tenant/Department/add',
			onSubmit : function(param) {
				if (!$('#tb1_add_form').form('validate')) {
					show_slide("错误", "请填写正确表单信息");
					return false;
				}
				param.dep_pid = row.id;
				return true;
			},
			success : function(data) {
				obj = eval('(' + data + ')');
				if (obj['status']) {
					show_slide("添加成功", "添加机构成功");
					$('#tb1_add_win').window("close");
					//在datagrid中添加行
					data = obj['content'];
					$('#tg_partree').treegrid('append', {
						parent : row.id,
						data : [ {
							id : data['id'],
							name : data['name'],
							note : data['note']
						} ]
					});

				} else {
					show_slide("添加失败", obj['info']);
				}
			}
		});
	}

	var editingId;

	function tb1_edit() {
		if (editingId != undefined) {
			$('#tg_partree').treegrid('select', editingId);
			return;
		}
		var row = $('#tg_partree').treegrid('getSelected');
		if (row) {
			editingId = row.id;
			$('#tg_partree').treegrid('beginEdit', editingId);
		}

	}
	function tb1_save() {
		if (editingId != undefined) {
			var row = $('#tg_partree').treegrid('getSelected');
			$.messager.confirm('保存', '是否保存？', function(r) {
				if (r) {
					// 退出操作;
					$('#tg_partree').treegrid('endEdit', editingId);
					$.post("/Tenant/Department/update", {
						dep_id : row.id,
						dep_name : row.name,
						dep_note : row.note
					}, function(data) {
						if (data['status']) {
							var node = $('#tg_partree').tree('getSelected');
							if (node){
								$('#tg_partree').tree('update', {
									target: node.target,
									name:data['dep_name'],
									note:data['dep_note']
								});
							}
							editingId = undefined;
							show_slide("保存成功", "已经成功保存！");
						} else {
							var node = $('#tg_partree').tree('getSelected');
							if (node){
								$('#tg_partree').tree('update', {
									target: node.target,
									name:data['dep_name'],
									note:data['dep_note']
								});
							}
							tb1_cancel();
							show_slide("失败", data.info);
						}
					});
				} else {
					tb1_cancel();
					show_slide("失败", "保存失败！");
				}
			});
		}
	}
	function tb1_cancel() {
		if (editingId != undefined) {
			$('#tg_partree').treegrid('cancelEdit', editingId);
			editingId = undefined;
		}
	}
	function tb1_del() {
		var row = $('#tg_partree').treegrid('getSelected');

		if (row) {
			var index = $('#tg_partree').treegrid('getRowIndex', row.id);
			$.messager.confirm('删除租户', '确定删除？', function(r) {
				if (r) {
					// 退出操作;
					$.post("/Tenant/Department/delete", {
						dep_id : row['id']
					}, function(data) {
						if (data['status']) {
							show_slide("删除成功", "成功删除！");

							$('#tg_partree').treegrid('remove', row['id']);
						}
					});
				}
			});
		}
	}

	//datagrid
	function tb2_add() {
		row = $('#tg_partree').datagrid('getSelected');
		if (!row) {
			show_slide("操作有误", "请选择一个组织选项再进行添加！");
			return;
		}
		$('#tb2_add_win').window("open");
		$('#tb2_add_form').form({
			url : '/Tenant/Person/add',
			onSubmit : function(para) {
				if (!$('#tb2_add_form').form('validate')) {
					show_slide("错误", "请填写正确表单信息");
					return false;
				}
				para.per_dep_id=row.id;
				return true;
			},
			success : function(data) {
				obj = eval('(' + data + ')');
				if (obj['status']) {
					show_slide("添加成功", "添加机构成功");
					$('#tb2_add_win').window("close");
					//在datagrid中添加行
					data = obj['content'];
					$('#dg_par').datagrid('insertRow', {
						index : 0,
						row : {
							id : data['per_id'],
							name : data['per_name'],
							sex:data['per_sex'],
							password:data['per_password'],
							email:data['per_email'],
							photo:data['per_photo'],
							note:data['per_note'],
							uid:data['per_uid']
						} 
					});

				} else {
					show_slide("添加失败", obj['info']);
				}
			}
		});
	}

	function tb2_del() {
		row = $('#dg_par').datagrid('getSelected');

		if (row) {
			index = $('#dg_par').datagrid('getRowIndex', row.id);
			$.messager.confirm('删除人员', '确定删除？', function(r) {
				if (r) {
					// 退出操作;
					$.post("/Tenant/Person/delete", {
						per_id : row['id']
					}, function(data) {
						if (data['status']) {
							show_slide("删除成功", "[<font color='blue'>"
									+ row['name'] + "</font>]已经成功被删除！");

							$('#dg_par').datagrid('deleteRow', index);
						}
					});
				}
			});
		}
	}
	
	function tb2_change_dep() {
		row = $('#dg_par').datagrid('getSelected');

		if (row) {
			index = $('#dg_par').datagrid('getRowIndex', row.id);
		//	$('#dg_par').datagrid('deleteRow', index);
			$('#change_to_dep').window('refresh','/Tenant/Manager/listdep2?per_id='+row['id']);
			$('#change_to_dep').window('open');
		}
	}
	var editingId2;

	function tb2_edit() {
		if (editingId2 != undefined) {
			$('#dg_par').datagrid('selectRecord', editingId2);
			return;
		}
		row = $('#dg_par').datagrid('getSelected');
		index = $('#dg_par').datagrid('getRowIndex', row.id);
		if (row) {
			editingId2 = row.id;
			$('#dg_par').datagrid('beginEdit', index);
		}

	}
	function tb2_save() {
		if (editingId2 != undefined) {
			$.messager.confirm('保存', '是否保存？', function(r) {
				
				if (r) {
					// 退出操作;
					
					row = $('#dg_par').datagrid('getSelected');
					index = $('#dg_par').datagrid('getRowIndex', row.id);
					$('#dg_par').datagrid('endEdit',index);
					$.post("/Tenant/Person/update", {
						per_id : row['id'],
						per_uid : row['uid'],
						per_name : row['name'],
						per_sex : row['sex'],
						per_password : row['password'],
						per_email : row['email'],
						per_note : row['note'],
						per_photo : row['photo']
					}, function(data) {
						if (data['status']) {
							row = $('#dg_par').datagrid('getSelected');
							index = $('#dg_par').datagrid('getRowIndex', row.id);
						//	$('#dg_par').datagrid('endEdit', index);
							r = data['content'];
							$('#dg_par').datagrid('updateRow',{
								index: index,
								row: {
									id : r['id'],
									uid : r['uid'],
									name : r['name'],
									sex : r['sex'],
									password : r['passowrd'],
									email : r['email'],
									note : r['note'],
									photo : r['photo']
								}
							});
							editingId2 = undefined;
							
							show_slide("保存成功", "已经成功保存！");
						} else {
							tb2_cancel();
							show_slide("失败", "保存失败!");
						}
					});
				} else {
					tb2_cancel();
					show_slide("失败", "保存失败！");
				}
			});
		}
	}
	function tb2_cancel() {
		if (editingId2 != undefined) {
			row = $('#dg_par').datagrid('getSelected');
			index = $('#dg_par').datagrid('getRowIndex', row.id);
			$('#dg_par').datagrid('cancelEdit', index);
			editingId2 = undefined;
		}
	}
	function in_excel(){
		$('#id_in_excel').window('refresh');
		$('#id_in_excel').window('open');
	}
	function in_excel_dep(){
		$('#id_in_excel_dep').window('refresh');
		$('#id_in_excel_dep').window('open');
	}
	function uploadexcel(){
		//上传处理
		row = $('#tg_partree').datagrid('getSelected');
		if (!row) {
			show_slide("操作有误", "请选择一个组织选项再进行添加！");
			return;
		}
		$('#dep_id').val(row.id);
		$('#uploadExcel').submit();
	}
	function uploadexcel_dep(){
		//上传处理
		row = $('#tg_partree').datagrid('getSelected');
		if (!row) {
			show_slide("操作有误", "请选择一个组织选项再进行添加！");
			return;
		}
		$('#dep_id_dep').val(row.id);
		$('#uploadExcel_dep').submit();
	}
</script>

<div id="part_tree" class="easyui-tabs" fit="true" border="false">
	<div title="组织机构信息">

		<div id="cccc" class="easyui-layout" data-options="fit:true">
			<div data-options="region:'north',collapsed:false,height:28">
				<div id="part_search">
					查询: <input id="part_search_id" class="easyui-text" 
						data-options="valueField:'id',textField:'username',url:'data.json',onSelect: function(rec){  
				  $('#search_uid').val(rec.id);}" />
					<a href="#" class="easyui-linkbutton" iconCls="icon-search"
						onclick="doSearch();">查询</a>
				</div>
			</div>
			<div data-options="region:'west',collapsed:false,width:'40%'">
				<div id="tb1">
					<a href="#"
						class="easyui-linkbutton" iconCls="icon-print"
						onclick="in_excel_dep();">导入Excel</a>
						<div id="id_in_excel_dep" class="easyui-window" title="excel导入组织结构信息" style="width:500px;height:150px"   
        					data-options="iconCls:'icon-save',modal:true,closed:true"> 
        					<form id="uploadExcel_dep" action="/Tenant/Excel/upload" method="post" enctype="multipart/form-data">
								<input type="hidden" name="id" id="dep_id_dep"/>
								<input type="hidden" name="deptree" value="1"/>
								<table style="width:100%">
								<tr><td>请选择文件:</td><td><input class="easyui-filebox" name="excelData" data-options="prompt:'选择Excel文件'" style="width:100%"></td></tr>
								<tr><td></td><td><a href="#"
						class="easyui-linkbutton" iconCls="icon-print"
						onclick="uploadexcel_dep();">上传</a></td></tr>
								</table>
							</form>
						</div>
					<a href="#" class="easyui-linkbutton" iconCls="icon-add"
						onclick="tb1_add();">添加</a><a href="#" class="easyui-linkbutton"
						iconCls="icon-cancel" onclick="tb1_del();">删除</a><a href="#"
						class="easyui-linkbutton" iconCls="icon-edit"
						onclick="tb1_edit();">编辑</a><a href="#" class="easyui-linkbutton"
						iconCls="icon-save" onclick="tb1_save();">保存</a><a href="#"
						class="easyui-linkbutton" iconCls="icon-back"
						onclick="tb1_cancel();">取消</a>
						
						<a href="#" style="float: right;"
						class="easyui-linkbutton" iconCls="icon-reload"
						onclick="$('#tg_partree').treegrid('reload');"></a>

					<div id="tb1_add_win" class="easyui-window" title="添加机构"
						style="width: 350px; height: 150px;"
						data-options="iconCls:'icon-add',modal:true,closed:true">
						<div class="easyui-layout" data-options="fit:true" align="center">
							<form id="tb1_add_form" method="post">
								<table cellpadding="5">
									<tr>
										<td>机构名称:</td>
										<td><input id="tb1_name" class="easyui-textbox"
											data-options="required:true" type="text" name="dep_name" /></td>
									</tr>
									<tr>
										<td>备注:</td>
										<td><input id="tb1_note" class="easyui-textbox"
											data-options="required:true" type="text" name="dep_note" /></td>
									</tr>
									<tr>
										<td></td>
										<td><a href="javascript:void(0)"
											class="easyui-linkbutton" style="width: 100px;"
											iconcls="icon-add"
											onclick="$('#tb1_add_form').form('submit');">添加机构</a>
									</tr>
								</table>
							</form>
						</div>
					</div>
				</div>
				<table title="组织结构" class="easyui-treegrid" id="tg_partree">
				</table>
			</div>
			<div data-options="region:'center',collapsed:false">
				<div id="tb2">
				<a href="#"
						class="easyui-linkbutton" iconCls="icon-print"
						onclick="in_excel();">导入Excel</a>
						<div id="id_in_excel" class="easyui-window" title="excel导入人员信息" style="width:500px;height:150px"   
        					data-options="iconCls:'icon-save',modal:true,closed:true"> 
        					<form id="uploadExcel" action="/Tenant/Excel/upload" method="post" enctype="multipart/form-data">
								<input type="hidden" name="id" id="dep_id"/>
								<table style="width:100%">
								<tr><td>请选择文件:</td><td><input class="easyui-filebox" name="excelData" data-options="prompt:'选择Excel文件'" style="width:100%"></td></tr>
								<tr><td></td><td><a href="#"
						class="easyui-linkbutton" iconCls="icon-print"
						onclick="uploadexcel();">上传</a></td></tr>
								</table>
							</form>
						</div>
					<a href="#" class="easyui-linkbutton" iconCls="icon-add"
						onclick="tb2_add();">添加</a><a href="#" class="easyui-linkbutton"
						iconCls="icon-cancel" onclick="tb2_del();">删除</a><a href="#"
						class="easyui-linkbutton" iconCls="icon-edit"
						onclick="tb2_edit();">编辑</a><a href="#" class="easyui-linkbutton"
						iconCls="icon-save" onclick="tb2_save();">保存</a><a href="#"
						class="easyui-linkbutton" iconCls="icon-back"
						onclick="tb2_cancel();">取消</a>
						<a href="#" class="easyui-linkbutton" iconCls="icon-reload"
						style="float: right;" onclick="$('#dg_par').datagrid('reload');"></a>
						<a href="#" class="easyui-linkbutton" iconCls="icon-edit"
						style="float: right;" onclick="tb2_change_dep();">移动</a>
<div id="change_to_dep" class="easyui-window" title="人员机构移动" style="width:600px;height:500px"   
        data-options="iconCls:'icon-save',modal:true,closed:true">   

</div>  
					<div id="tb2_add_win" class="easyui-window" title="添加人员"
						style="width: 350px; height: 350px;"
						data-options="iconCls:'icon-add',modal:true,closed:true">
						<div class="easyui-layout" data-options="fit:true" align="center">
							<form id="tb2_add_form" method="post">
								<table cellpadding="5">
									<tr>
										<td>姓名:</td>
										<td><input id="tb2_name" class="easyui-textbox"
											data-options="required:true" type="text" name="per_name" /></td>
									</tr>
									<tr>
										<td>标识:</td>
										<td><input id="tb2_sex" class="easyui-textbox"
											data-options="required:true" type="text" name="per_uid" /></td>
									</tr>
									<tr>
										<td>性别:</td>
										<td><input id="tb2_sex" class="easyui-textbox"
											data-options="required:true" type="text" name="per_sex" /></td>
									</tr>
									<tr>
										<td>密码:</td>
										<td><input id="tb2_note" class="easyui-textbox"
											data-options="required:true" type="text" name="per_password" /></td>
									</tr>
									<tr>
										<td>邮箱:</td>
										<td><input id="tb2_note" class="easyui-textbox"
											data-options="required:true" type="text" name="per_email" /></td>
									</tr>
									<tr>
										<td>备注:</td>
										<td><input id="tb2_note" class="easyui-textbox"
											data-options="required:true" type="text" name="per_note" /></td>
									</tr>
									<tr>
										<td>照片:</td>
										<td><input id="tb2_note" class="easyui-textbox"
											data-options="required:true" type="text" name="per_photo" /></td>
									</tr>
									<tr>
										<td></td>
										<td><a href="javascript:void(0)"
											class="easyui-linkbutton" style="width: 100px;"
											iconcls="icon-add"
											onclick="$('#tb2_add_form').form('submit');">添加人员</a>
									</tr>
								</table>
							</form>
						</div>
					</div>
				</div>
				<div class="easyui-tabs" fit="true">  
					<div title="人员管理">   
						<table title="人员信息" class="easyui-datagrid" id="dg_par">
						</table>
					</div>
				</div>  
			</div>
		</div>
	</div>
	<div title="点名者管理"  data-options="href:'/Tenant/index/manager.html'">
	</div>
</div>