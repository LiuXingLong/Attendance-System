<?php
/**
 * 通过调用微信服务器接口，返回access_token，获取失败返回false;
 *$appid,$secret是微信公众平台账号提供的
 */
class Token {
	public function getToken($appid, $secret) {
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}";
		
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		$result = curl_exec ( $ch );
		curl_close ( $ch );
		
		$jsoninfo = json_decode ( $result, true );
		if (empty ( $jsoninfo ['errcode'] )) {
			$token = $jsoninfo ['access_token'];
			if (! empty ( $token )) {
				return $token;
			}
			return false;
		} else {
			return false;
		}
	}
	/**
	 * 返回一个Token对象实例
	 * @return Token实例
	 */
	public function tokenControl() {
		return new Token();
	}
}
?>