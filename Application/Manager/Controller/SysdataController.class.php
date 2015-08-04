<?php
namespace Manager\Controller;

use \Think\Controller;
use \Think\Model;

class SysdataController extends BaseController {
 public function index(){
		$M = M();
        $tabs = $M->query('SHOW TABLE STATUS');
        $total = 0;
		//dump($tabs);
        foreach ($tabs as $k => $v) {
//            $tabs[$k]['size'] = byteFormat($v['Data_length'] + $v['Index_length']);
            $total+=$v['Data_length'] + $v['Index_length'];
        }
 //       $this->assign("list", $tabs);
 //       $this->assign("total", byteFormat($total));
 //       $this->assign("tables", count($tabs));
        print_r($tabs);
        //$this->display();
    }

}
?>