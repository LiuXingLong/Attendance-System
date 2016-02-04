<?php if (!defined('THINK_PATH')) exit();?><script type="text/javascript">
	$(function() {
		var cur_index = 0;
		$('#tg_partree').treegrid({
			url : '/22/Manager/Department/getDepTree',
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
						$('#dg_par').datagrid("load", {
							per_dep_id : row.id
						});
					}
				}
			}
		});
		$('#dg_par').datagrid({
			url : '/22/Manager/Person/getPersonByDep',
			method : 'get',
			lines : true,
			rownumbers : true,
			idField : 'id',
			singleSelect : true,
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
			}, {
				field : 'uid',
				title : '标识',
				width : 20,
				align : 'center',
				editor : 'text'
			}, {
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
				width : 20,
				editor : 'text'
			}, {
				field : 'photo',
				title : '照片',
				width : 20,
				editor : 'text'
			} ] ]
		});
	});
	/*
	function tb1_add() {
		row = $('#tg_partree').treegrid('getSelected');
		if (!row)
			return;
		$('#tb1_add_win').window("open");
		$('#tb1_add_form').form({
			url : '/22/Manager/Department/add',
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
	}*/
	/*
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
	 $.post("/22/Manager/Department/update", {
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
	 $.post("/22/Manager/Department/delete", {
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
	 */
	//datagrid
	/*
	function tb2_add() {
		row = $('#tg_partree').datagrid('getSelected');
		if (!row) {
			show_slide("操作有误", "请选择一个组织选项再进行添加！");
			return;
		}
		$('#tb2_add_win').window("open");
		$('#tb2_add_form').form({
			url : '/22/Manager/Person/add',
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
					$.post("/22/Manager/Person/delete", {
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
					$.post("/22/Manager/Person/update", {
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
	 */

	function sign_code(){
		$('#sign_code').window('refresh','/22/Manager/Manager/listdep?mng_id=1');
		$('#sign_code').window('open');
	}
	 function doSearch(){
		 $('#search_table tr td').parent().remove();
		 date1=$('#date1').datebox('getValue');
		 date2=$('#date2').datebox('getValue');
		 id=$('#part_search_id').combobox('getValue');
		 $.post("/22/Manager/Manager/search",{"date1":date1,"date2":date2,"id":id},function(data){
			 if(data['status']){
				 var r=data['content'];
				 for(var i in r){
					 var x=r[i];
					 var temp = "<tr><td>"+x['time']+"</td><td>"+x['total']+"</td><td>"+x['come']+"</td><td><font color=red>"+x['notcome']+"</font></td><td><span style='float:right'>"+x['per_come']+"</span></td></tr>";
				 	$('#search_table').append(temp);
				 }
			 }
			 $('#win_search_result').window('open');
		 });
	 }
	$(function(){
			   var curr_time = new Date();
			   var strDate = curr_time.getFullYear()+"-";
			   strDate += curr_time.getMonth()+1+"-";
			   strDate += curr_time.getDate();
var strDate2 = curr_time.getFullYear()+"-";
strDate2 += curr_time.getMonth()+1+"-";
strDate2 += curr_time.getDate()>7?curr_time.getDate()-7:curr_time.getDate();
$("#date1").val(strDate2);
$("#date2").val(strDate);
		});
</script>
<div id="sign_code" class="easyui-window" title="微信二维码签到" style="width:1280px;height:600px"   
        data-options="iconCls:'icon-save',modal:true,closed:true"> 
</div>
<div id="part_tree" class="easyui-tabs" fit="true" border="false">
	<div title="管理">

		<div id="cccc" class="easyui-layout" data-options="fit:true">
			<div data-options="region:'north',collapsed:false,height:28">
				<div id="part_search">
				<a href="/22/Manager/Manager/outPersonsToExcel" target="_blank" class="easyui-linkbutton" iconCls="icon-print" onclick="this.href='/22/Manager/Manager/outPersonsToExcel?date1='+$('#date1').datebox('getValue')+'&date2='+$('#date2').datebox('getValue');">导出签到统计数据</a>
					班级信息: <input id="part_search_id" class="easyui-combobox" value="请选择"
						data-options="valueField:'dep_id',width:'200px',textField:'dep_name',url:'/22/Manager/Manager/searchInit',onSelect: function(rec){  
				  $('#search_uid').val(rec.id);}" />
				  <input id="date1" type="text" name="regtime"
						class="easyui-datebox" required="required" editable="false"></input>  
				  <input id="date2" type="text" name="regtime"
						class="easyui-datebox" required="required" editable="false"></input>  
					<a href="#" class="easyui-linkbutton" iconCls="icon-search"
						onclick="doSearch();">统计查询</a>
		<div id="win_search_result" class="easyui-window" title="机构签到统计" data-options="modal:true,closed:true,resizable:false,minimizable:false,maximizable:false,draggable:false" style="width:700px;height:450px;padding:10px;">
			<div>该机构签到数据统计结果如下:</div>
			<table class="mytable" id="search_table" style="width:100%">
				<tr><th>记录时间</th><th>点到人数</th><th>签到人数</th><th>未到人数</th><th>到勤率</th></tr>
			</table>
		</div>
				</div>
			</div>
			<div data-options="region:'west',collapsed:false,width:'40%'">
				<div id="tb1">
					<a href="#" class="easyui-linkbutton" iconCls="icon-add"
						onclick="sign_code();">二维码点到</a>
						<a href="#" class="easyui-linkbutton"
						iconCls="icon-reload" style="float: right;"
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
				<table title="签到操作" class="easyui-treegrid" id="tg_partree">
				</table>
			</div>
<script type="text/javascript">
		function searchPerson(){	
			row = $('#dg_par').datagrid('getSelected');
			index = $('#dg_par').datagrid('getRowIndex', row.id);
			$.post("/22/Manager/Person/getSignInfo", {
					id : row['id']
			}, function(data) {
			if (data['status']) {
				var r=data['content'];
				$('#person_total').html(r['total']);
				$('#person_come').html(r['come']);
				$('#person_notcome').html("<font color='red'>"+r['notcome']+"</font>");
				$('#person_percome').html('<img src="/22/Manager/Person/getSignInfoImage?'+Math.random()+'"/>');
				
				temp="";
				var rr=r['details'];
				//alert(rr);return;
				var line="";
				var t=null;
				for(var t in rr){
					t=rr[t];
					line="";
					if(t['iscome'] == 1){
						line="<font color='green'>"+t['time']+":已到</font>";
					}else{
						line="<font color='red'>"+t['time']+":缺勤</font>";
					}
					temp += line+"<br/>";
				}
				$('#person_details').html(temp);
				$('#win_person_info').window('open');
				show_slide("查询成功", "查询该条数据成功！");
				} else {
				show_slide("查询失败", "查询该条数据失败!");
				}
			});
		}
</script>
			<div data-options="region:'center',collapsed:false">
				<div id="tb2">
					<a href="#" class="easyui-linkbutton" iconCls="icon-search"
						onclick="searchPerson();">签到信息</a><a href="#" class="easyui-linkbutton"
						iconCls="icon-reload" style="float: right;"
						onclick="$('#dg_par').datagrid('reload');"></a>
		<div id="win_person_info" class="easyui-window" title="个人签到统计" data-options="modal:true,closed:true,resizable:false,minimizable:false,maximizable:false,draggable:false" style="width:900px;height:500px;padding:10px;">
			该人员签到数据统计结果如下:<br/>
			<table class="mytable">
				<tr><td>签到次数:</td><td id="person_total"></td><td rowspan="4" id="person_details"></td></tr>
				<tr><td>已到次数:</td><td id="person_come"></td></tr>
				<tr><td>未到次数:</td><td id="person_notcome"></td></tr>
				<tr><td>签到率:</td><td id="person_percome"></td></tr>
			</table>
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
</div>