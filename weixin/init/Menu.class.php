<?php
include_once 'Token.php';
class Menu {
	private $access_token;
	public function Menu($access_token) {
		$this->access_token = $access_token;
	}
	
	/**
	 * 创建菜单
	 * 通过传入的参数创建微信菜单,返回1创建成功,返回0创建失败
	 * $data 为创建菜单的json数据
	 */
	public function createMenu($data) {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $this->access_token );
		curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt ( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)' );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt ( $ch, CURLOPT_AUTOREFERER, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		$tmpInfo = curl_exec ( $ch );
		if (curl_errno ( $ch )) {
			return curl_error ( $ch );
		}
		
		curl_close ( $ch );
		$result = json_decode ( $tmpInfo, true );
		if (strcmp ( $result ['errcode'], "0" ) == 0)
			return 1;
		else
			return 0;
	}
	
	/**
	 * 创建默认菜单
	 * 
	 * @return 1创建成功,0创建失败
	 */
	public function createDefaultMenu() {
 	$data = '{			
     "button":[
     {
          "name":"签到",
          "sub_button":[
          	{
               "type": "click", 
               "name":"上传位置",
               "key":"101"
            },
           {
               "type": "pic_sysphoto", 
               "name":"自拍签到",
               "key":"102"
            },
            {	
               	"type":"scancode_waitmsg",
                "name": "扫码签到", 
                "key": "103"
            }]
      },
      {
           "name":"信息管理",
           "sub_button":[
			{
               "type":"pic_sysphoto",
               "name":"请假申请",
               "key":"201"
            },
           {
               "type":"click",
               "name":"数据统计",
               "key":"202"
            }, 
			{
               "type":"click",
               "name":"系统公告",
               "key":"203"
            }]
      },
      {
           "name":"配置",
           "sub_button":[
           {
               "type":"click",
               "name":"绑定微信",
               "key":"301"
            },
            {
               "type":"pic_sysphoto",
               "name":"实名认证",
               "key":"302"
            },
            {
               "type":"click",
               "name":"解除绑定",
               "key":"303"
            },
            {
               "type":"click",
               "name":"使用帮助",
               "key":"304"
            }]
      }]
 }';
 	
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $this->access_token );
		curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt ( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)' );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt ( $ch, CURLOPT_AUTOREFERER, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		$tmpInfo = curl_exec ( $ch );
		if (curl_errno ( $ch )) {
			return curl_error ( $ch );
		}
		
		curl_close ( $ch );
		$result = json_decode ( $tmpInfo, true );
		if (strcmp ( $result ['errcode'], "0" ) == 0)
			return 1;
		else
			return 0;
	}
	// 获取菜单
	public function getMenu() {
		return file_get_contents ( "https://api.weixin.qq.com/cgi-bin/menu/get?access_token=" . $this->access_token );
	}
	// 删除菜单
	public function deleteMenu() {
		return file_get_contents ( "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=" . $this->access_token );
	}
}