<?php
namespace Tenant\Controller;
use Think\Controller;
class ExcelController extends Controller {
    public function index(){
//        echo session($_POST['mail']);
// 	   $this->display();
		$filename = "data.xlsx";
		$headArr=array("1","2");
		$data = array(array("12",22),array(31,32));
		$this->getExcel($fileName, $headArr, $data);
    }

    public  function getExcel($fileName,$headArr,$data){
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");

        $date = date("Y_m_d",time());
        $fileName .= "_{$date}.xls";

        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new \PHPExcel();
        $objProps = $objPHPExcel->getProperties();

        //设置表头
        $key = ord("A");
        //print_r($headArr);exit;
        foreach($headArr as $v){
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $key += 1;
        }

        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();

        //print_r($data);exit;
        foreach($data as $key => $rows){ //行写入
            $span = ord("A");
            foreach($rows as $keyName=>$value){// 列写入
                $j = chr($span);
                $objActSheet->setCellValue($j.$column, $value);
                $span++;
            }
            $column++;
        }

        $fileName = iconv("utf-8", "gb2312", $fileName);
        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }

    
    public function upload()
    {
    	
        header("Content-Type:text/html;charset=utf-8");
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     20145728 ;// 设置附件上传大小
        $upload->exts      =     array('xls', 'xlsx');// 设置附件上传类
        $upload->savePath  =      '/'; // 设置附件上传目录
        // 上传文件
        $info   =   $upload->uploadOne($_FILES['excelData']);
        $filename = './Uploads'.$info['savepath'].$info['savename'];
        $exts = $info['ext'];
        //print_r($info);exit;
        
        if(!$info) {// 上传错误提示错误信息
              $this->error($upload->getError());
          }else{// 上传成功
                  $this->goods_import($filename, $exts);
        }
    }
    
    //导入数据页面
    public function import()
    {
    	$this->display('goods_import');
    }
    
    //导入数据方法
    protected function goods_import($filename, $exts='xls')
    {
    	//导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
    	import("Org.Util.PHPExcel");
    	//创建PHPExcel对象，注意，不能少了\
    	$PHPExcel=new \PHPExcel();
    	//如果excel文件后缀名为.xls，导入这个类
    	$exts = substr($filename, strlen($filename)-3);
    	//tag_debug($exts);
    	if($exts == 'xls'){
    		import("Org.Util.PHPExcel.Reader.Excel5");
    		$PHPReader=new \PHPExcel_Reader_Excel5();
    	}else if($exts == 'lsx'){
    		import("Org.Util.PHPExcel.Reader.Excel2007");
    		$PHPReader=new \PHPExcel_Reader_Excel2007();
    	}
    
    
    	//载入文件
    	$PHPExcel=$PHPReader->load($filename);
    	//获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
    	$currentSheet=$PHPExcel->getSheet(0);
    	//获取总列数
    	$allColumn=$currentSheet->getHighestColumn();
    	//获取总行数
    	$allRow=$currentSheet->getHighestRow();
    	//循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
    	for($currentRow=1;$currentRow<=$allRow;$currentRow++){
    		//从哪列开始，A表示第一列
    		for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
    			//数据坐标
    			$address=$currentColumn.$currentRow;
    			//读取到的数据，保存到数组$arr中
    			$data[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
    		}
    
    	}
    	//tag_debug($_POST);
    	if(!empty($_POST['id'])){
    		if(!empty($_POST['deptree'])){//构造组织结构树操作
    			$this->create_tree($data);
    		}else{
    			$this->create_import($data);//构造树操作
    		}
    	}else{
    		$this->save_import($data);
    	}
    }
    

    //保存导入数据并且自动生成树状结构
    public function create_tree($data)
    {
    	//print_r($data);exit;
    
    	
    	$department = M("Department");
    	$res = $department->field("dep_id,dep_name,dep_tnt_id")->where("dep_pid=0,dep_tnt_id={$_SESSION['tnt_id']}")->select();
    	$rootid=$res[0]['dep_id'];
    	$t = array();
    	foreach ($res as $k1=>$v1){
    		$t[$v1['dep_name']]=$v1;
    	}
    	//	tag_debug($t);
    	$res = $t;
    	$dd = array();
    	foreach ($data as $v){
    		if(empty($res[$v['A']])){
    			$da['dep_tnt_id'] = $_SESSION['tnt_id'];
    			$da['dep_pid'] = $rootid;
    			$da['dep_note'] = "无";
    			$da['dep_name'] = $v['A'];
    			$result =$department->add($da);
    			if(!$result){
    				$this->error('数据导入失败,原因:组织结构错误!');
    			}
    			$res[$v['A']]=array("dep_id"=>$result,"dep_name"=>$v['A'],"dep_tnt_id"=>$_SESSION['tnt_id']);
    		}
    		if(empty($res[$v['B']])){
    			$da['dep_tnt_id'] = $_SESSION['tnt_id'];
    			$da['dep_pid'] = $res[$v['A']]['dep_id'];
    			$da['dep_note'] = $v['C'];
    			$da['dep_name'] = $v['B'];
    			$result =$department->add($da);
    			if(!$result){
    				$this->error('数据导入失败,原因:组织结构错误!');
    			}
    			$res[$v['B']]=array("dep_id"=>$result,"dep_name"=>$v['B'],"dep_tnt_id"=>$_SESSION['tnt_id']);
    		}
    	}
    	if($result){
    		$this->success('数据导入成功', U('Index/index'));
    	}else{
    		$this->error('数据导入失败');
    	}
    	//print_r($info);
    
    }
    
    //保存导入数据并且自动生成树状结构
    public function create_import($data)
    {
    	//print_r($data);exit;
    
    	$id=$_POST['id'];
    	$department = M("Department");
    	$res = $department->field("dep_id,dep_name,dep_tnt_id")->where("dep_tnt_id={$_SESSION['tnt_id']}")->select();
    	$t = array();
    	foreach ($res as $k1=>$v1){
    		$t[$v1['dep_name']]=$v1;
    	}
    	//	tag_debug($t);
    	$res = $t;
    	$person=M('Person');
    	$dd = array();
    	foreach ($data as $v){
    		if(empty($res[$v['C']])){
    			$da['dep_tnt_id'] = $_SESSION['tnt_id'];
    			$da['dep_pid'] = $id;
    			$da['dep_note'] = "无";
    			$da['dep_name'] = $v['C'];
    			$result =$department->add($da);
    			if(!$result){
    				$this->error('数据导入失败,原因:组织结构错误!');
    			}
    			$res[$v['C']]=array("dep_id"=>$result,"dep_name"=>$v['C'],"dep_tnt_id"=>$_SESSION['tnt_id']);
    		}
    		if(empty($res[$v['D']])){
    			$da['dep_tnt_id'] = $_SESSION['tnt_id'];
    			$da['dep_pid'] = $res[$v['C']]['dep_id'];
    			$da['dep_note'] = "无";
    			$da['dep_name'] = $v['D'];
    			$result =$department->add($da);
    			if(!$result){
    				$this->error('数据导入失败,原因:组织结构错误!');
    			}
    			$res[$v['D']]=array("dep_id"=>$result,"dep_name"=>$v['D'],"dep_tnt_id"=>$_SESSION['tnt_id']);
    		}
    		{
    			$d['per_uid']=$v['B'];
    			$d['per_tnt_id']=$res[$v['D']]['dep_tnt_id'];
    			$d['per_name']=$v['A'];
    			$d['per_password']=$v['B'];
    			$d['per_dep_id']=$res[$v['D']]['dep_id'];
    			$d['per_sex']=intval(substr($v['F'],16,1))%2==0?1:0;
    			$d['per_note']=$v['E'];
    			$d['per_email']=$v['F'];
    			//	tag_debug($d);
    			$dd[]=$d;
    		}
    	}
 //   	tag_debug($res);
    	$result = $person->addAll($dd);
    	if($result){
    		$this->success('数据导入成功', U('Index/index'));
    	}else{
    		$this->error('数据导入失败');
    	}
    	//print_r($info);
    
    }
    
    //保存导入数据
    public function save_import($data)
    {
    	//print_r($data);exit;
    
    	$department = M("Department");
    	$res = $department->field("dep_id,dep_name,dep_tnt_id")->where("dep_tnt_id={$_SESSION['tnt_id']}")->select();
    	$t = array();
    	foreach ($res as $k1=>$v1){
    		$t[$v1['dep_name']]=$v1;
    	}
    //	tag_debug($t);
    	$res = $t;
    	$person=M('Person');
    	$dd = array();
    	foreach ($data as $v){
    		if(!empty($res[$v['D']])){
    			$d['per_uid']=$v['B'];
    			$d['per_tnt_id']=$res[$v['D']]['dep_tnt_id'];
    			$d['per_name']=$v['A'];
    			$d['per_password']=$v['B'];
    			$d['per_dep_id']=$res[$v['D']]['dep_id'];
    			$d['per_sex']=intval(substr($v['F'],16,1))%2==0?1:0;
    			$d['per_note']=$v['E'];
    			$d['per_email']=$v['F'];
    		//	tag_debug($d);
    			$dd[]=$d;
    		}
    	}
    	$result = $person->addAll($dd);
//     	$Goods = M('user');
//     	$add_time = date('Y-m-d H:i:s', time());
//     	foreach ($data as $k=>$v){
//     		$date['name'] = $v['A'];
//     		$date['sex'] = $v['B'];
//     		$result = M('user')->add($date);
//     	}
    	if($result){
    		$this->success('数据导入成功', U('Index/index'));
    	}else{
    		$this->error('数据导入失败');
    	}
    	//print_r($info);
    
    }
}
?>