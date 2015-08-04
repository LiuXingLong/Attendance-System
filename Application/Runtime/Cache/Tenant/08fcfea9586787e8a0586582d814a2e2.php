<?php if (!defined('THINK_PATH')) exit();?>
  <form id="addform" action="<?php echo U('Index/upload');?>" method="post" enctype="multipart/form-data">
        <div class="control-group">
          <label>Excel表格：</label>
                <input type="file" name="excelData" value=""  datatype="*4-50"  nullmsg="请填写产品！" errormsg="不能少于4个字符大于50个汉字"/>
                <span class="Validform_checktip"></span>
        </div>
        <div class="control-group">
          <img style="display:none;" src="images/loading.gif" />
          <input type="submit" class="btn btn-primary Sub" value="导入" />
        </div>
    </form>