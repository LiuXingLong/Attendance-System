<?php if (!defined('THINK_PATH')) exit();?><!--  MyTags:commonfiles /-->
<style type="text/css">
.pos_fixed
{
	position:fixed;		
	top:150px;
	right:-380px;
}
</style>
<script type="text/javascript">
     var start=null;
	//  结束签到	
	function end_code2() {
		mng_id = $('#mng_id').val();
		var mng_listdep = $('input[id=mng_listdep]:checked');
		var mng_listdeps = '';
		mng_listdep.each(function(i) {
			mng_listdeps = mng_listdeps + $(this).val() + ',';
		});
		mng_listdeps = mng_listdeps.substr(0, mng_listdeps.length - 1);
		$.post("/22/Manager/Manager/endCode2",{"pers":mng_listdeps},function(data){
			$('#status_signer').html("<font color='red'>签到已经结束,管理员确认中...</font>");
			$('#commit_code2').linkbutton('enable');
			$('#start_code2').linkbutton('disable');
			$('#end_code2').linkbutton('disable');
			show_slide("停止签到", "本次签到签到已经停止,请管理员确认...！");
		});
	}
	// 提交数据
	function commit_code2() {
		mng_id = $('#mng_id').val();
		var mng_listdep = $('input[id=mng_listdep]:checked');
		var mng_listdeps = '';
		mng_listdep.each(function(i) {
			mng_listdeps = mng_listdeps + $(this).val() + ',';
		});
		mng_listdeps = mng_listdeps.substr(0, mng_listdeps.length - 1);
		$.post("/22/Manager/Manager/commitCode2",{"pers":mng_listdeps},function(data){
			if(data['status']){
				$('#status_signer').html("<font color='green'>数据已经成功提交</font>");
				$('#start_code2').linkbutton('disable');
				$('#end_code2').linkbutton('disable');
				$('#commit_code2').linkbutton('disable');
				show_slide("提交成功", "本次签到情况已经成功提交到数据库,请点击退出！");
				$('#per_come').html('<img width="100%" height="120px" id="per_come_img" src="/22/Manager/Manager/jpgTest?'+Math.random()+'"  title="点击放大" alt="点击放大" onclick="showBigPic2(this);"/>');
				
				mng_listdep = $('input[id=mng_listdep]').not("input:checked");
				mng_listdeps = '';
				mng_listdep.each(function(i) {
					mng_listdeps += "<font color='red'>"+$(this).next().text()+"</font><br/>";
				});
				$('#not_come').html(mng_listdeps);
			}else{
				show_slide("提交失败", "数据提交失败,请重新提交！");
			}
		});
		window.clearInterval(start);
	}
	// 把    rgb 转成     “#xxxx”(HEX )  格式。		
	function rgb2hex(rgb) {
		  rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
		  function hex(x) {
		    return ("0" + parseInt(x).toString(16)).slice(-2);
		  }
		  return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
	}
	$(function(){		
		//获取点击变法事件
		$('input[id=mng_listdep]').change(function(){
			if($(this).get(0).checked){
				// 选中变绿色
				$(this).parent().css("background-color","#99FF66");
				$(".pos_fixed").animate({right:"-380px"});				
					// alert("请假！");								
			}else{
				// 未选中
				$(this).parent().css("background-color","#ffffff");	
			}
		});			
		// 鼠标移动到请假的     学生上    显示请假信息照片
		$("td").hover(function(){
				//移动到目标上执行				
				var rgb=$(this).css("background-color");
				var color=rgb2hex(rgb);												
				if(color=="#aca120"){
					var value=$(this).children().val();
					var str1="../../../../Public/image/";
				    var str2=".jpg";					
					value=str1.concat(value,str2);				
					$("#pos_fixed").attr('src',value);					
					$(".pos_fixed").animate({right:"5px"});					
					// alert("请假！");				
				}			
			}		
		,function(){
			//移出目标上执行
			var rgb=$(this).css("background-color");
			var color=rgb2hex(rgb);
			if(color=="#aca120"){				
				$(".pos_fixed").animate({right:"-380px"});				
				// alert("请假！");				
			}																
		});				
	});
	//  开始点到
	function start_code2(){	
		start=setInterval(checkCode, 1000);
		$.get("/22/Manager/Manager/startCode2", function(data) {
			if (data['status']) {
				show_slide("系统信息", "开启微信二维码签到成功！");
				$('#status_signer').html("<font color='green'>签到中...</font>");
				$('#start_code2').linkbutton('disable');
				$('#commit_code2').linkbutton('disable');
				$('#end_code2').linkbutton('enable');
			}
		});
	}	
	//获取签到的人数据   然后    改变颜色
	function checkCode() {
		$.get("/22/Manager/Manager/checkByCode", function(data) {		
			if (data['status']) {
			    c = data['content'];
				per_uid = null;
				per_uid = c['per_uid'];
				qingjia = null;
				qingjia = c['qingjia'];				
				if(qingjia != null){
					for (i = 0; i < qingjia.length; i++){	
						//alert('请假');
						if(!$("#form_codelist").find("input[value=" + qingjia[i] + "]").get(0).checked){
							// 没有被选择才变色      点到者确认请假有效 选择了    就不会变灰色了
							$("#form_codelist").find("input[value=" + qingjia[i] + "]").attr(
									"checked", false).parent().css("background-color","#ACA120");
						}	
					}
				}				
				if(per_uid != null){
					for (j = 0; j < per_uid.length; j++) {
						$("#form_codelist").find("input[value=" + per_uid[j] + "]").attr(
								"checked", true).parent().css("background-color","#99FF66");					
					}
				}					
			}	
		});
	}		
	function showBigPic(){
		t=$("#big_code");
		t.attr("src",t.attr("src")+"?"+Math.random());
		$('#win_bigcode').window('open');
	}
	function showBigPic2(t){
		t=$("#big_tongji");
		t.attr("src",t.attr("src")+"?"+Math.random());
		$('#win_bigcode2').window('open');
	}
</script>
<div class="easyui-panel" title="微信二维码签到"
	style="width: 99%; height: 99%; padding: 5px;">
	<div class="easyui-layout" data-options="fit:true">	
		<div data-options="region:'west',split:true"
			style="width: 240px; padding: 10px">			
			<div>
				<a class="easyui-linkbutton" id="start_code2" value="开始" onclick="start_code2();" data-options="disabled:false">开始签到</a>
				<a class="easyui-linkbutton" id="end_code2" value="结束" onclick="end_code2();" data-options="disabled:true" >结束签到</a>
				<a class="easyui-linkbutton" id="commit_code2" value="提交" onclick="commit_code2();" data-options="disabled:true" >提交数据</a>
				<div style="padding:1px auto" id="status_signer"><font color="red">等待签到</font></div>
			</div>			
			<img style="width:180px;height:180px;cursor:pointer" src="/22/<?php echo ($pic_code); ?>" title="点击放大" alt="点击放大" onclick="showBigPic(this);"/>			
				<div id="win_bigcode" class="easyui-window" title="微信二维码" data-options="modal:true,closed:true,resizable:false,minimizable:false,maximizable:false,draggable:false" style="width:600px;height:600px;padding:10px;">
					<img id="big_code" style="width:100%;height:100%;" src="/22/<?php echo ($pic_code); ?>" onclick="showBigPic();"/>
				</div>
				<div>
					微信二维码扫描说明:<br/>
					使用方法:<br/>
					1.点击左上角[开始签到]按钮后,系统签到才会有效.<br/>
					2.用户点击微信客户端点击[二维码签到]并且扫描上面的二维码,若收到签到成功标识即签到成功.<br/>
					注意事项:<br/>
					1.二维码签到之前必须绑定个人工号或者学号等,否则无效.<br/>
					2.只有在点击上面的[开始签到]之后,点击[结束签到]之前这段时间签到才有效,否则无效.<br/>
					3.只有在管理员点击左上角提交数据后数据才会录入数据库.否则为无效签到.<br/>
					其他说明:<br/>
					1.管理员在点击结束签到后可以确认数据后在进行提交,提高数据有效性.
				</div>				
		</div>		
		<div data-options="region:'east'" style="width: 200px; padding: 5px">
			本次签到数据统计结果如下:<br/>
				<div id="win_bigcode2" class="easyui-window" title="统计结果" data-options="modal:true,closed:true,resizable:false,minimizable:false,maximizable:false,draggable:false" style="width:600px;height:500px;padding:10px;">
					<img id="big_tongji" style="width:100%;height:100%;" src="/22/Manager/Manager/jpgTest" onclick="showBigPic();"/>
				</div>
				<table class="mytable" width="100%">
					<tr><td id="per_come"></td></tr>
					<tr><td>未到人员:</td></tr>
					<tr><td id="not_come"></td></tr>
				</table>
				<img src="" id="pos_fixed" class="pos_fixed" alt="" width="380" height="280">
		</div>			
		<!-- <img src="../../../../Public/image/<?php echo ($sub["per_id"]); ?>.jpg" class="pos_fixed" alt="" width="380" height="280"> -->		
		<div data-options="region:'center'" style="padding: 3px">	
		<div id="before_commit">	
			<form id="form_codelist">
				<input type='hidden' name='mng_id' id='mng_id' value='<?php echo ($mng_id); ?>' />
					<?php if(is_array($listdep)): $i = 0; $__LIST__ = $listdep;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><span style="float:left;width:190px">
						<table class="mytable" style="width:100%">
							<tr><th><?php echo ($vo["dep_name"]); ?></th></tr>
					   		<?php if(is_array($vo['children'])): $i = 0; $__LIST__ = $vo['children'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub): $mod = ($i % 2 );++$i;?><tr><td><input type='checkbox' name='per_id' id='mng_listdep' value='<?php echo ($sub["per_id"]); ?>'><span>[<?php echo ($sub["per_uid"]); ?>]&nbsp; <?php echo ($sub["per_name"]); ?></span></td></tr><?php endforeach; endif; else: echo "" ;endif; ?>
					   </table>
					   </span><?php endforeach; endif; else: echo "" ;endif; ?>					
			  </form>				  
		 </div>
		 </div>
	</div>
</div>
<div>
</div>