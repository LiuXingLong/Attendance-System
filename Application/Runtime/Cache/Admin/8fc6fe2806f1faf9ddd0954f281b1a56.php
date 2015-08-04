<?php if (!defined('THINK_PATH')) exit();?>
<script type="text/javascript">
	function pagerFilter(data) {
		if (typeof data.length == 'number' && typeof data.splice == 'function') { // is array
			data = {
				total : data.length,
				rows : data
			}
		}
		var dg = $(this);
		var opts = dg.datagrid('options');
		var pager = dg.datagrid('getPager');
		pager.pagination({
			onSelectPage : function(pageNum, pageSize) {
				opts.pageNumber = pageNum;
				opts.pageSize = pageSize;
				pager.pagination('refresh', {
					pageNumber : pageNum,
					pageSize : pageSize
				});
				dg.datagrid('loadData', data);
			}
		});
		if (!data.originalRows) {
			data.originalRows = (data.rows);
		}
		var start = (opts.pageNumber - 1) * parseInt(opts.pageSize);
		var end = start + parseInt(opts.pageSize);
		data.rows = (data.originalRows.slice(start, end));
		return data;
	}

	$(function() {

		$('#dg').datagrid({
			loadFilter : pagerFilter,//翻页过滤器
			url : '/Admin/Tenant/getTenants',
			//			width : 100,
			//			height : 100,
			nowrap : true,//内容不换行  
			rownumbers : true,//行号  
			animate : true,//动画效果  
			collapsible : true,//是否折叠  
			rownumbers : true,
			pagination : true,
			pageSize : 20,//默认每页显示条数
			toolbar : '#tb',//工具条
			pageList : [ 10, 20, 30, 50, 100, 200, 500, 1000 ],//设置每页显示的条数，要配合JS的PageFilter用
			idField : 'id',//主键位置，设置此可以定位行数并且对于多选会记录页数选中行
			multiSort : true,
			checkbox : true,
			singleSelect : true,
			fitColumns : true,//自动扩张Y方向，如果自动调节长宽适应表格
			fit : true,//自动扩张XY方向，如果为false不会自动扩张
			remoteSort : false,
			multiSort : true,
			striped : true,
			columns : [ [ {
				field : "ck",
				title : "选择",
				checkbox : true
			}, {
				field : "id",
				title : "id",
				width : 20,
				sortable : true,
				sorter : function(a, b) {
					return (parseInt(a) > parseInt(b) ? 1 : -1);
				}
			}, {
				field : "username",
				title : "租户名(账号)",
				width : 50
			}, {
				field : "password",
				title : "密码",
				width : 100
			}, {
				field : "regtime",
				title : "录入时间",
				width : 30
			}, {
				field : "lasttime",
				title : "最后登录",
				width : 30
			}, {
				field : "tel",
				title : "电话",
				width : 60
			}, {
				field : "note",
				title : "备注",
				width : 120
			}, {
				field : "max_persons",
				title : "最多用户数",
				width : 40
			}, {
				field : 'isrun',
				title : '状态',
				width : 20,
				editor : 'text',
				sortable : true,
				sorter : function(a, b) {
					return (parseInt(a) > parseInt(b) ? 1 : -1);
				},
				styler : function(value, row, index) {
					if (value == "1") {
						return 'color:green;';
					} else if (value == "0") {
						return 'color:red;';
					} else if (value == "2") {
						return 'color:orange;';
					} else {
						return 'color:black;';
					}
				},
				formatter : function(value) {
					if (value == "1") {
						value = '启用中';
					} else if (value == "0") {
						value = "停用中";
					} else if (value == "2") {
						value = "维护中";
					} else {
						value = "-";
					}
					return value;
				}
			} ] ]
		});
		//		$("#update_tnt").window();
		//		$("#update_tnt").window("close");

	});

	function doRun() {
		var row = $('#dg').datagrid('getSelected');

		if (row) {
			var index = $('#dg').datagrid('getRowIndex', row.id);
			$.messager
					.confirm(
							'启用',
							'确定启用改租户？',
							function(r) {
								if (r) {
									$
											.post(
													"/Admin/Tenant/run",
													{
														id : row.id
													},
													function(data) {
														if (data.status) {
															$('#dg')
																	.datagrid(
																			'updateRow',
																			{
																				index : index,
																				row : {
																					isrun : 1
																				}
																			});
															show_slide(
																	"操作成功",
																	"租户[<font color='blue'>"
																			+ row.username
																			+ "</font>]已经成功调为<font color='green'>启用</font>状态！");
														} else {
															show_side(
																	"操作失败",
																	data['info']);
														}
													});
								}
							});

		}
	}
	function dounRun() {
		var row = $('#dg').datagrid('getSelected');

		if (row) {
			var index = $('#dg').datagrid('getRowIndex', row.id);
			$.messager
					.confirm(
							'停用',
							'确定停用该租户？',
							function(r) {
								if (r) {
									$
											.post(
													"/Admin/Tenant/unrun",
													{
														id : row.id
													},
													function(data) {
														if (data.status) {
															$('#dg')
																	.datagrid(
																			'updateRow',
																			{
																				index : index,
																				row : {
																					isrun : 0
																				}
																			});
															show_slide(
																	"操作成功",
																	"租户[<font color='blue'>"
																			+ row.username
																			+ "</font>]已经成功调为<font color='red'>停用</font>状态！");
														} else {
															show_side(
																	"操作失败",
																	data['info']);
														}
													});
								}
							});

		}
	}

	function doAdd() {
		var row = $('#dg').datagrid('getSelected');
		//		if (!row)return;
		$('#form_add_tnt').form('submit');
	}
	function doUpdate() {
		$.messager.confirm('修改租户', '确定修改？', function(r) {
			if (!r)
				return false;
			else {
				// 退出操作;
				$('#form_update_tnt').form("submit");
			}
		});
	}
	function doDelTnt() {
		var row = $('#dg').datagrid('getSelected');

		if (row) {
			var index = $('#dg').datagrid('getRowIndex', row.id);
			$.messager.confirm('删除租户', '确定删除？', function(r) {
				if (r) {
					// 退出操作;
					$.post("/Admin/Tenant/delete", {
						id : row['id']
					}, function(data) {
						if (data['status']) {
							show_slide("删除成功", "租户[<font color='blue'>"
									+ row['username'] + "</font>]已经成功被删除！");

							$('#dg').datagrid('cancelEdit', index).datagrid(
									'deleteRow', index);
						}
					});
				}
			});
		}
	}
	function doAddTnt() {
		$('#add_tnt').window("open");
		$('#form_add_tnt').form({
			url : '/Admin/Tenant/add',
			onSubmit : function() {
				// do some check    
				// return false to prevent submit; 
				if (!$('#form_add_tnt').form('validate')) {
					show_slide("错误", "请填写正确表单信息");
					return false;
				}
				return true;
			},
			success : function(data) {
				var obj = eval('(' + data + ')');
				if (obj['status']) {
					show_slide("添加成功", "添加租户成功");
					$('#add_tnt').window("close");
					//在datagrid中添加行
					data = obj['content'];
					$('#dg').datagrid('insertRow', {
						index : 0, // 索引从0开始
						row : {
							username : data['username'],
							tel : data['tel'],
							isrun : data['isrun'],
							regtime : data['regtime'],
							lasttime : data['lasttime'],
							note : data['note'],
							id : data['id'],
							password : data['password'],
							max_persons:data['max_persons']
						}
					});
				} else {
					show_slide("添加失败", obj['info']);
				}
			}
		});
	}
	function doUpdateTnt() {
		var row = $('#dg').datagrid('getSelected');

		if (!row)
			return;
		var index = $('#dg').datagrid('getRowIndex', row['id']);
		$("#update_username").textbox("setValue", row.username);
		$("#update_password").textbox("setValue", row.password);
		$("#update_tel").textbox("setValue", row.tel);
		$("#update_regtime").textbox("setValue", row.regtime);
		$("#update_lasttime").textbox("setValue", row.lasttime);
		$("#update_max_persons").textbox("setValue", row.max_persons);
		//			$("#update_isrun").textbox("setValue",row.isrun);
		$("#update_note").textbox("setValue", row.note);
		$("#update_id").val(row['id']);
		$("#update_id_label").html(row['id']);
		$('#update_tnt').window("open");
		$('#form_update_tnt')
				.form(
						{
							url : '/Admin/Tenant/update',
							onSubmit : function() {
								// do some check    
								// return false to prevent submit; 
								if (!$('#form_update_tnt').form('validate')) {
									show_slide("错误", "请填写正确表单信息");
									return false;
								}

								return true;
							},
							success : function(data) {
								//							alert(data);

								//	return;
								var obj = eval('(' + data + ')');
								if (obj['status']) {
									data = obj['content'];
									//				window.location = "/Admin/Index/index";
									var row = $('#dg').datagrid('getSelected');
									var index = $('#dg').datagrid(
											'getRowIndex', row['id']);
									$('#update_tnt').window("close");
									if (row) {
										show_slide(
												"修改成功",
												"租户[<font color='blue'>"
														+ row['username']
														+ "</font>]已经<font color='green'>修改成功</font>！");

										$('#dg').datagrid('updateRow', {
											index : index,
											row : {
												username : data['username'],
												tel : data['tel'],
												isrun : data['isrun'],
												regtime : data['regtime'],
												lasttime : data['lasttime'],
												note : data['note'],
												id : data['id'],
												password : data['password'],
												max_persons:data['max_persons']
											}
										});
									}
								} else {
									show_slide("修改失败", obj['info']);
								}
							}
						});

	}
	function doSearch() {
		kws = $('#search_uid').val();
		len = kws.length;
		$('#dg').datagrid('load', {
			keywords : kws
		});
		$('#dg').datagrid('clearSelections');
	}
	function doReload() {
		$('#dg').datagrid('reload', {});
	}
	function saveData() {

	}
	function outExcel() {
		$.post("/Admin/Tenant/outExcel", null, function(data) {

		});
	}
	function inData() {

	}
</script>
<div id="update_tnt" class="easyui-window" title="修改租户信息"
	style="width: 350px; height: 450px;"
	data-options="iconCls:'icon-edit',modal:true,closed:true">
	<div class="easyui-layout" data-options="fit:true" align="center">
		<form id="form_update_tnt" method="post">
			<table cellpadding="5">
				<tr>
					<td>ID:</td>
					<td><input type="hidden" id="update_id" name="id" /><label
						id="update_id_label"></label></td>
				</tr>
				<tr>
					<td>用户名:</td>
					<td><input id="update_username" class="easyui-textbox"
						type="text" name="username" data-options="required:true"
						editable="false" /></td>
				</tr>
				<tr>
					<td>密码:</td>
					<td><input id="update_password" class="easyui-textbox"
						type="text" name="password" data-options="required:true"></input></td>
				</tr>
				<tr>
					<td>电话:</td>
					<td><input id="update_tel" class="easyui-textbox" type="text"
						name="tel" data-options="required:true"></input></td>
				</tr>
				<tr>
					<td>录入时间:</td>
					<td><input id="update_regtime" type="text" name="regtime"
						class="easyui-datebox" required="required" editable="false"></input></td>
				</tr>
				<tr>
					<td>最后登录:</td>
					<td><input id="update_lasttime" type="text" name="lasttime"
						class="easyui-datebox" required="required" editable="false"></input></td>
				</tr>
				<tr>
					<td>最多用户数:</td>
					<td><input id="update_max_persons" class="easyui-textbox" type="text"
						name="max_persons" data-options="required:true"></input></td>
				</tr>
				<tr>
					<td>是否启用:
					<td><input id="update_isrun" class="easyui-radio" name="isrun"
						type="radio" checked="true" value="1" />是<input name="isrun"
						class="easyui-radio" type="radio" value="0" />否</td>
				</tr>
				<tr>
					<td>备注:</td>
					<td><input id="update_note" class="easyui-textbox" type="text"
						name="note" data-options="multiline:true" style="height: 60px;"></input></td>
				</tr>
				<tr>
					<td></td>
					<td><a href="javascript:void(0)" class="easyui-linkbutton"
						style="width: 100px;" iconcls="icon-edit" onclick="doUpdate();">修改</a>
				</tr>
			</table>
		</form>
	</div>
</div>
<div id="tt" class="easyui-tabs" fit="true" border="false">
	<div title="租户信息">
		<table id="dg"
			data-options="
				rownumbers:true,
				autoRowHeight:false,
				pagination:true,
				pageSize:10">
		</table>
		<div id="tb" style="padding: 2px 5px;">
			<a href="#" style="" class="easyui-linkbutton" iconCls="icon-reload"
				onclick="doReload();">刷新全部</a> 租户名: <input id="search_uid"
				class="easyui-combobox" value="请选择"
				data-options="valueField:'username',textField:'username',url:'/Admin/Tenant/getTenants',onSelect: function(rec){  
				  $('#search_uid').val(rec.username);}" />
			<a href="#" class="easyui-linkbutton" iconCls="icon-search"
				onclick="doSearch();">查询</a><a href="#" class="easyui-linkbutton"
				iconCls="icon-add" onclick="doAddTnt();">添加租户</a><a href="#"
				class="easyui-linkbutton" iconCls="icon-remove"
				onclick="doDelTnt();">删除租户</a><a href="#" class="easyui-linkbutton"
				iconCls="icon-edit" onclick="doUpdateTnt();">修改信息</a> <a href="#"
				class="easyui-linkbutton" iconCls="icon-ok" onclick="doRun();">启用</a>
			<a href="#" class="easyui-linkbutton" iconCls="icon-cancel"
				onclick="dounRun();">停用</a> <!--  a href="#" class="easyui-linkbutton"
				iconCls="icon-save" style="float: right" onclick="saveData();">备份</a>
			<a href="/Admin/Tenant/outExcel" class="easyui-linkbutton"
				iconCls="icon-print" style="float: right" onclick="outExcel();">导出Excel</a>
			<a href="#" class="easyui-linkbutton" iconCls="icon-back"
				style="float: right" onclick="inData();">导入数据</a -->
		</div>
	</div>
</div>
<div id="add_tnt" class="easyui-window" title="添加租户"
	style="width: 350px; height: 400px;"
	data-options="iconCls:'icon-add',modal:true,closed:true">
	<div class="easyui-layout" data-options="fit:true" align="center">
		<form id="form_add_tnt" method="post">
			<table cellpadding="5">
				<tr>
					<td>用户名:</td>
					<td><input id="add_username" class="easyui-textbox"
						type="text" name="username" data-options="required:true" /></td>
				</tr>
				<tr>
					<td>密码:</td>
					<td><input id="add_password" class="easyui-textbox"
						type="text" name="password" data-options="required:true"></input></td>
				</tr>
				<tr>
					<td>电话:</td>
					<td><input id="add_tel" class="easyui-textbox" type="text"
						name="tel" data-options="required:true"></input></td>
				</tr>
				<tr>
					<td>录入时间:</td>
					<td><input id="add_regtime" type="text" name="regtime"
						class="easyui-datebox" required="required" editable="false"></input></td>
				</tr>
				<tr>
					<td>最后登录:</td>
					<td><input id="add_lasttime" type="text" name="lasttime"
						class="easyui-datebox" required="required" editable="false"></input></td>
				</tr>
				<tr>
					<td>最多用户:</td>
					<td><input id="add_max_persons" class="easyui-textbox" type="text"
						name="max_persons" data-options="required:true"></input></td>
				</tr>
				<tr>
					<td>是否启用:
					<td><input id="add_isrun" class="easyui-radio" name="isrun"
						type="radio" checked="true" value="1" />是<input name="isrun"
						class="easyui-radio" type="radio" value="0" />否</td>
				</tr>
				<tr>
					<td>备注:</td>
					<td><input id="add_note" class="easyui-textbox" type="text"
						name="note" data-options="multiline:true" style="height: 60px;"></input></td>
				</tr>
				<tr>
					<td></td>
					<td><a href="javascript:void(0)" class="easyui-linkbutton"
						iconcls="icon-add" onclick="doAdd();" style="width: 100px;">添加</a></td>
				</tr>
			</table>
		</form>
	</div>
</div>