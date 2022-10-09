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
	/**
	if(!class_exists("JsonEntriesModulePagerLogic")) SOY2::import("site_include.plugin.output_blog_entries_json.func.pager", ".php");
	$start = $getParams["offset"];
	$end = $start + $getParams["limit"];
	if($end > 0 && $start == 0) $start = 1;

	$pager = new JsonEntriesModulePagerLogic();
	$pager->setPageUrl("http://localhost:8080/site2/");
	$pager->setPage($getParams["offset"]+1);
	$pager->setStart($start);
	$pager->setEnd($end);
	$pager->setLimit($getParams["limit"]);
	$pager->setTotal($total);
	
	//件数情報表示
	$obj->addLabel("count_start", array(
		"soy2prefix" => "cms",
		"text" => $pager->getStart()
	));
	$obj->addLabel("count_end", array(
		"soy2prefix" => "cms",
		"text" => $pager->getEnd()
	));
	$obj->addLabel("count_max", array(
		"soy2prefix" => "cms",
		"text" => $pager->getTotal()
	));

	//ページへのリンク
	$obj->addModel("has_next_prev_pager", $pager->getHasNextOrPrevParam());
	$obj->addModel("has_next_pager", $pager->getHasNextParam());
	$obj->addModel("has_prev_pager", $pager->getHasPrevParam());
	$obj->addLink("next_pager", $pager->getNextParam());
	$obj->addLink("prev_pager", $pager->getPrevParam());
	$obj->createAdd("pager_list", "JsonEntriesModuleSimplePager", $pager->getPagerParam());

	//ページへジャンプ
	$obj->addForm("pager_jump", array(
		"soy2prefix" => "cms",
		"method" => "get",
		"action" => $pager->getPageURL()."/"
	));
	$obj->addSelect("pager_select", array(
		"soy2prefix" => "cms",
		"name" => "page",
		"options" => $pager->getSelectArray(),
		"selected" => $pager->getPage(),
		"onchange" => "location.href=this.parentNode.action+this.options[this.selectedIndex].value"
	));
	**/

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