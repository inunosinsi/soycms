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

	$endpoint = soycms_module_func_get_endpoint($html);
	if(strlen($endpoint)){
		$getParams = soycms_module_func_get_get_parameters($endpoint);
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
	$last_page_number = (int)ceil($total / $getParamLim);

	SOY2::import("site_include.plugin.soycms_search_block.component.BlockPluginPagerComponent");
	$obj->createAdd("pager", "BlockPluginPagerComponent", array(
		"list" => array(),
		"current" => $current,
		"last"	 => $last_page_number,
		"url"		=> $url,
		"soy2prefix" => "p_block",
	));

	$obj->addModel("has_pager", array(
		"soy2prefix" => "p_block",
		"visible" => ($last_page_number >1)
	));
	$obj->addModel("no_pager", array(
		"soy2prefix" => "p_block",
		"visible" => ($last_page_number <2)
	));

	$obj->addLink("first_page", array(
		"soy2prefix" => "p_block",
		"link" => $url,
	));

	$obj->addLink("last_page", array(
		"soy2prefix" => "p_block",
		"link" => ($last_page_number > 0) ? $url . "page-" . ($last_page_number - 1) : null,
	));

	$obj->addLabel("current_page", array(
		"soy2prefix" => "p_block",
		"text" => max(1, $current + 1),
	));

	$obj->addLabel("pages", array(
		"soy2prefix" => "p_block",
		"text" => $last_page_number,
	));
	
	$obj->display();
}

if(!class_exists("OutputBlogEntriesJSONPluginListComponent")){
	class OutputBlogEntriesJSONPluginListComponent extends HTMLList {

		private $keys;

		protected function populateItem($entity){
			$arr = (is_array($entity)) ? $entity : array();
			$entryLink = (is_numeric(array_search("url", $this->keys)) && isset($arr["url"]) && is_string($arr["url"])) ? htmlspecialchars($arr["url"], ENT_QUOTES, "UTF-8") : "";
			
			foreach($this->keys as $key){
				switch($key){
					case "cdate":
					case "udate":
						$k = ($key == "cdate") ? "create_date" : "update_date";
						$this->createAdd($k,"DateLabel",array(
							"soy2prefix"=>"cms",
							"text" => (isset($arr[$key]) && is_numeric($arr[$key])) ? (int)$arr[$key] : 0
						));
						break;
					case "title":
						$title = (isset($arr[$key]) && is_string($arr[$key])) ? htmlspecialchars($arr[$key], ENT_QUOTES, "UTF-8") : "";
						$this->createAdd($key, "CMSLabel", array(
							"soy2prefix" => "cms",
							"html" => (strlen($entryLink)) ? "<a href=\"".$entryLink."\">".$title."</a>" : $title
						));
						$this->createAdd($key."_plain", "CMSLabel", array(
							"soy2prefix" => "cms",
							"text" => $title
						));
						break;
					case "content":
					case "more":
						$this->createAdd($key, "CMSLabel", array(
							"soy2prefix" => "cms",
							"html" => (isset($arr[$key]) && is_string($arr[$key])) ? $arr[$key] : ""
						));
						break;
					case "url":
						$this->addLink("entry_link", array(
							"soy2prefix" => "cms",
							"link" => $entryLink
						));
						break;
					case "thumbnail":
						$tmbArr = (isset($arr[$key]) && is_array($arr[$key])) ? $arr[$key] : array();
						foreach(array("thumbnail", "trimming", "upload") as $tmbIdx){
							$tmbImgPath = (isset($tmbArr[$tmbIdx]) && is_string($tmbArr[$tmbIdx])) ? trim($tmbArr[$tmbIdx]) : "";
							$this->addModel("is_".$tmbIdx, array(
								"soy2prefix" => "cms",
								"visible" => (strlen($tmbImgPath) > 0)
							));

							$this->addModel("no_".$tmbIdx, array(
								"soy2prefix" => "cms",
								"visible" => (strlen($tmbImgPath) === 0)
							));

							$this->addImage($tmbIdx, array(
								"soy2prefix" => "cms",
								"src" => $tmbImgPath
							));

							$this->addLabel($tmbIdx."_path_text", array(
								"soy2prefix" => "cms",
								"text" => $tmbImgPath
							));
						}
						break;
					default:
						$this->createAdd($key, "CMSLabel", array(
							"soy2prefix" => "cms",
							"text" => (isset($arr[$key]) && is_string($arr[$key])) ? $arr[$key] : ""
						));
						break;
				}
			}
		}

		function setKeys($keys){
			$this->keys = $keys;
		}
	}
}