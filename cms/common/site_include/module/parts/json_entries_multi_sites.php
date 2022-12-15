<?php
/**
 * ブログ記事JSON出力プラグイン(output_blog_entries_json)連携用
 */
function soycms_json_entries_multi_sites($html, $htmlObj){
	if(!function_exists("soycms_module_func_get_endpoint")) SOY2::import("site_include.plugin.output_blog_entries_json.func.fn", ".php");
	if(!file_exists("soycms_oje_pdo")) SOY2::import("site_include.plugin.output_blog_entries_json.func.db", ".php");

	$endpoints = soycms_module_func_get_endpoints_by_comment($html);
	$lm = soycms_module_func_get_entry_limit($html);

	// HTMLから<!-- oje:endpoint="***" -->と<!-- oje:count="***" -->を消しておく
	if(count($endpoints)){
		preg_match_all('/<!--.*oje:endpoint=".*?".*?-->/', $html, $tmps);
		if(isset($tmps[0]) && count($tmps[0])){
			foreach($tmps[0] as $endpointComment){
				$html = str_replace($endpointComment, "", $html);
			}
		}
	}

	preg_match('/<!--.*oje:count="[\d]*".*?-->/', $html, $tmp);
	if(isset($tmp[0]) && strlen($tmp[0])){
		$html = str_replace($tmp[0], "", $html);
	}
	

	$obj = $htmlObj->create("soycms_json_entries_multi_sites", "HTMLTemplatePage", array(
		"arguments" => array("soycms_json_entries_multi_sites", $html)
	));

	if(!soycms_oje_entry_exsits() && count($endpoints)){
		$pdo = soycms_oje_pdo();	// PDOをオープン
		$pdo->beginTransaction();
		$stmt = $pdo->prepare("INSERT INTO oje_entries(cdate, udate, data) VALUES(:cdate, :udate, :data)");

		foreach($endpoints as $endpoint){
			$getParams = soycms_module_func_get_get_parameters($endpoint);

			// 下記2種のパラメータはいらない
			if(isset($getParams["limit"])) unset($getParams["limit"]);
			if(isset($getParams["offset"])) unset($getParams["offset"]);

			//下記パラメータは必ず
			$getParams["is_url"] = 1;
			$getParams["remove_limit"] = 1;	//件数の制限を外す
			$endpoint = soycms_module_func_rebuild_endpoint($endpoint, $getParams);
			
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
				// 結果をデータベースに挿入する
				if(isset($json["entries"]) && is_array($json["entries"]) && count($json["entries"])){
					foreach($json["entries"] as $arr){
						$stmt->execute(array(":cdate" => $arr["cdate"], ":udate" => $arr["udate"], ":data" => soy2_serialize($arr)));
					}
				}
			}
		}
		$pdo->commit();
		$stmt = null;
		$pdo = null;	// PDOをクローズ　クローズのタイミングでデータのフラッシングを行うことを期待している
	}

	$url = soycms_get_page_url_by_frontcontroller(true);
	preg_match('/page-\d+/', $_SERVER["REQUEST_URI"], $args);
	$current = (isset($args[0]) && strpos($args[0], "page-") === 0) ? (int)str_replace("page-", "", $args[0]) : 0;
	$offset = $current * $lm;
	
	$entries = soycms_oje_get_entries($lm, $offset);
	$total = soycms_oje_get_total();
		
	// // cms:id生成用のkeyを設定
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

	// debug用タグ
	$obj->addLabel("total", array(
		"soy2prefix" => "p_block",
		"text" => soy2_number_format($total)
	));

	
	/** ページャに関するcms:id */
	if(!class_exists("OutputBlogEntriesJSONPagerComponent")) SOY2::import("site_include.plugin.output_blog_entries_json.component.OutputBlogEntriesJSONPagerComponent");
	OutputBlogEntriesJSONPagerComponent::pager($obj, $url, $current, $total, $lm);

	$obj->display();
}