<?php

/**
 * モジュールのHTML内にあるhiddenの値からエンドポイントのURLを取得する
 * 例：<input type="hidden" id="output_blog_entries_json_connector" value="https://example/site/1.json?limit=10&content=&more=&is_url=1&thumbnail">
 * @param string
 * @return string
 */
function soycms_module_func_get_endpoint(string $html){
	$lines = explode("\n", $html);
	foreach($lines as $l){
		if(is_bool(stripos($l, "<input"))) continue;
		preg_match('/<input.*type=\"hidden\".*id=\"output_blog_entries_json_connector\".*value=\"(.*?)\".*>/', $l, $tmp);
		if(isset($tmp[1])) return $tmp[1];
	}
	return "";
}

/**
 * 取得したエンドポイントのURLからGETパラメータを取得する
 * @param string
 * @return array
 */
function soycms_module_func_get_get_parameters(string $endpoint){
	$res = strpos($endpoint, "?");
	if(is_bool($res)) return array();

	$arr = explode("&", substr($endpoint, $res+1));
	if(!count($arr)) return array();

	$params = array();
	foreach($arr as $v){
		if(is_numeric(strpos($v, "="))){
			$vv = explode("=", $v);
			$params[$vv[0]] = $vv[1];
		}else{
			$params[$v] = "";
		}
	}

	if(!isset($params["limit"]) || !is_numeric($params["limit"])) $params["limit"] = 99999;

	//ページャ用のGETパラメータ URIの末尾がpage-{整数}の場合に使用可
	$reqUri = $_SERVER["REQUEST_URI"];
	if(is_numeric(strpos($reqUri, "?"))) $reqUri = substr($reqUri, 0, strpos($reqUri, "?"));
	preg_match('/page-(\d+?)/', $reqUri, $tmp);
	$page = 1;
	if(isset($tmp[1]) && (int)$tmp[1] > 0) $page = (int)$tmp[1];
	$params["offset"] = $page-1;
	
	return $params;
}

/**
 * ページャ用にエンドポインtURLを組み立て直す
 * @param string, array
 * @return string
 */
function soycms_module_func_rebuild_endpoint(string $endpoint, array $params){
	$endpoint = substr($endpoint, 0, strpos($endpoint, "?"));
	return $endpoint . "?" . http_build_query($params);
}