<?php
/**
 * ブログ記事JSON出力プラグイン(output_blog_entries_json)連携用
 */
function soycms_json_entries($html, $htmlObj){
	$obj = $htmlObj->create("soycms_json_entries", "HTMLTemplatePage", array(
		"arguments" => array("soycms_json_entries", $html)
	));

	if(!file_exists("soycms_module_func_get_endpoint")) SOY2::import("site_include.plugin.output_blog_entries_json.func.fn", ".php");

	$entries = array();
	$total = 0;

	$endpoints = soycms_module_func_get_endpoints_by_comment($html, 1); //コメント形式 oje:endpoint
	$endpoint = (count($endpoints) > 0) ? $endpoints[0] : soycms_module_func_get_endpoint($html); // hidden形式(古い仕様)
	unset($endpoints);
	if(strlen($endpoint)){
		$getParams = soycms_module_func_get_get_parameters($endpoint);
		if(!isset($getParams["is_url"]) || !is_numeric($getParams["is_url"])) $getParams["is_url"] = 1;	// URLは必ず取得する
		if(count($getParams)) $endpoint = soycms_module_func_rebuild_endpoint($endpoint, $getParams);
		if(function_exists("curl_init")){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $endpoint);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$resp = curl_exec($ch);
			curl_close($ch);
		}else{
			$resp = @file_get_contents($endpoint);
		}
		
		if(is_string($resp)){
			$json = json_decode($resp, true);
			if(isset($json["entries"]) && is_array($json["entries"])) $entries = $json["entries"];
			if(isset($json["total"]) && is_numeric($json["total"])) $total = (int)$json["total"];
		}
	}

	// cms:id生成用のkeyを設定
	SOY2::import("site_include.plugin.output_blog_entries_json.util.OutputBlogEntriesJsonUtil");
	$keys = OutputBlogEntriesJsonUtil::keys();

	if(count($entries)){
		foreach($entries[0] as $key => $v){
			if(is_bool(array_search($key, $keys))){
				$keys[] = $key;
			}
		}
	}

	if(!class_exists("OutputBlogEntriesJSONPluginListComponent")) SOY2::import("site_include.plugin.output_blog_entries_json.component.OutputBlogEntriesJSONPluginListComponent");
	$obj->createAdd("entry_list", "OutputBlogEntriesJSONPluginListComponent", array(
		"soy2prefix" => "p_block",
		"list" => $entries,
		"keys" => $keys
	));

	
	/** ページャに関するcms:id */
	$url = soycms_get_page_url_by_frontcontroller(true);
	preg_match('/page-\d+/', $_SERVER["REQUEST_URI"], $args);
	$current = (isset($args[0]) && strpos($args[0], "page-") === 0) ? (int)str_replace("page-", "", $args[0]) : 0;

	$getParamLim = (isset($getParams["limit"]) && is_numeric($getParams["limit"]) && $getParams["limit"] > 0) ? (int)$getParams["limit"] : 15;	// 15の理由は特に決めていない
	if(!class_exists("OutputBlogEntriesJSONPagerComponent")) SOY2::import("site_include.plugin.output_blog_entries_json.component.OutputBlogEntriesJSONPagerComponent");
	OutputBlogEntriesJSONPagerComponent::pager($obj, $url, $current, $total, $getParamLim);
	
	$obj->display();
}