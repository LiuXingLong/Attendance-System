<?php if (!defined('THINK_PATH')) exit();?>
<script type="text/javascript">

function form_deplist(){
	mng_id = $('#mng_id').val();
	
	var mng_listdep = $('input[name=mng_listdep]:checked');
	var mng_listdeps='';
	//alert(mng_listdep);
	mng_listdep.each(function(i){
	//	alert($(this).val());
		mng_listdeps = mng_listdeps+$(this).val()+',';
	});
	mng_listdeps = mng_listdeps.substr(0,mng_listdeps.length-1);
	//alert(mng_listdeps);
	//show_slide("添加失败", mng_listdeps);return;
	$.post("/Tenant/Manager/changeDeplist",
			{"mng_id":mng_id,"mng_listdep":mng_listdeps},
			function(data){
			if(data['status']){
				row = $('#dg_par3').datagrid('getSelected');
				index = $('#dg_par3').datagrid('getRowIndex', row.id);
				r = data['content'];
				
				$('#dg_par3').datagrid('updateRow',{
					index: index,
					row: {
						deplist : r['mng_listdep']
					}
				});
				show_slide("修改成功！",data['info']);
			}else{
				show_slide("修改失败！",data['info']);
			}
	});
}
</script>

<form id="form_deplist">
<input type='hidden' name='mng_id' id='mng_id' value='<?php echo ($mng_id); ?>'/>
<?php echo ($listdep); ?>
<input type="button" value="提交" onclick="form_deplist();" />
</form>