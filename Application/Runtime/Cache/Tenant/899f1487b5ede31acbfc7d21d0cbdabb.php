<?php if (!defined('THINK_PATH')) exit();?>
<script type="text/javascript">

function form_deplist(){
	per_id_old = $('#per_id_old').val();
	per_id_new = $('#per_id_new').val();
	//alert(mng_listdeps);
	//show_slide("添加失败", mng_listdeps);return;
	$.post("/mysigner/Tenant/Manager/changePersonDep",
			{"per_id_old":per_id_old,"per_id_new":per_id_new},
			function(data){
			if(data['status']){
				row = $('#dg_par').datagrid('getSelected');
				index = $('#dg_par').datagrid('getRowIndex', row.id);
				$('#dg_par').datagrid('deleteRow', index);
				r = data['content'];
				show_slide("修改成功！",data['info']);
			}else{
				show_slide("修改失败！",data['info']);
			}
	});
}
</script>

<form id="form_deplist">
<input type='hidden' name='per_id_old' id='per_id_old' value='<?php echo ($per_id_old); ?>'/>
<select name="per_id_new" id="per_id_new">
<?php if(is_array($listdep)): $i = 0; $__LIST__ = $listdep;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$d): $mod = ($i % 2 );++$i;?><option value="<?php echo ($d["dep_id"]); ?>"><?php echo ($d["dep_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
</select>
<input type="button" value="提交" onclick="form_deplist();" />
</form>