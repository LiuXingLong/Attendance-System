<?php
/**
 * 移除数组中字段名称的前缀
 * @param string $array_data 数据数组
 * @param string $pre 要去掉前缀
 * @return string|multitype:unknown
 */

function removePre($array_data,$pre){
	if(empty($pre) || empty($array_data))
		return $array_data;
	$res = array();
	$len = strlen($pre);
	foreach ($array_data as $key=>$value){
		if(is_numeric($key)){
			$res[$key] = removePre($value, $pre);
		}else{
			$key = substr($key, $len);
			$res[$key] = $value;
		}
	}
	return $res;
}
?>