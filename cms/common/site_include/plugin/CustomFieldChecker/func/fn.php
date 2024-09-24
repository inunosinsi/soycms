<?php
/**
 * @param string
 * @return array
 */
function checker_fn_search_cms_tags_by_file(string $path){
	if(!file_exists($path)) return array();

	// ファイルのパスにCustomFieldが付いているものは後ほど調べるので今はスルー
	if(soy2_strpos($path, "CustomField") >= 0) return array();

	// 下記プラグインはスルーしても問題ない
	if(soy2_strpos($path, "output_blog_entries_json") >= 0) return array();
	if(soy2_strpos($path, "ButtonSocial") >= 0) return array();
	if(soy2_strpos($path, "util_multi_language") >= 0) return array();
	if(soy2_strpos($path, "display_inquiry_content") >= 0) return array();
	if(soy2_strpos($path, "soycms_thumbnail") >= 0) return array();
	if(soy2_strpos($path, "limitation_browse_blog_entry") >= 0) return array();

	$tags = array();

	$code = str_replace(array("\r", "\n"), "", file_get_contents($path));
	$lines = explode(";", $code);
	foreach($lines as $line){
		foreach(array("createAdd", "addLink", "addModel", "addLabel", "addInput", "addTextArea") as $method){
			preg_match('/'.$method.'\(.*array\(.*\)\)/', $line, $tmp);
			if(!isset($tmp[0]) || !strlen($tmp[0])) continue;
			$tmp[0] = str_replace(" ", "", $tmp[0]);
			$tmp[0] = str_replace("'", "\"", $tmp[0]);
			if(soy2_strpos($tmp[0], "\"soy2prefix\"=>\"cms\"") < 0) continue;
			
			// 第一引数を取得
			$tmp[0] = substr($tmp[0], 0, soy2_strpos($tmp[0], ","));
			$tmp[0] = trim(trim(substr($tmp[0], soy2_strpos($tmp[0], "(")+1), "\""));
			
			if(!strlen($tmp[0])) continue;

			// debug用 定数や変数付きのラベル 特殊な書き方のファイルを探す時に使用
			if(soy2_strpos($tmp[0], "$") >= 0 || soy2_strpos($tmp[0], "::") >= 0) {
				//var_dump($path);
				//var_dump($tmp[0]);
				//echo "<hr>";
			}else{
				$tags[] = $tmp[0];
			}
		}
	}	
	
	return array_unique($tags);
}

/**
 * 標準タグのCSVを更新する
 * @return array
 */
function checker_fn_update_cms_tag_list(){
	$_d =  UserInfoUtil::getSiteDirectory() . ".cache/tag_constant/";
	if(!file_exists($_d)) mkdir($_d);
	$tagFilePath = $_d."tags.csv";
	
	// ファイル作成日が7日以内であればキャッシュの方を読み込む
	if(file_exists($tagFilePath) && filemtime($tagFilePath) > strtotime("-7day")) return;

	$tags = array();

	foreach(array(CMS_BLOCK_DIRECTORY, CMS_BLOG_BLOCK_DIRECTORY, CMS_PAGE_PLUGIN) as $constant){
		foreach(soy2_scanfiles($constant) as $f){		
			if(preg_match('/php$/', $f) === 0) continue;
			$_arr = checker_fn_search_cms_tags_by_file($f);
			$tags = array_unique(array_merge($tags, $_arr));
		}
	}

	// 手動で加える
	// common/site_include/blog/component/MonthArciveListComponent.class.phpより
	$tags[] = "no_first";
	$tags[] = "not_first";

	// common/site_include/plugin/soycms_thumbnail/soycms_thumbnail.php
	foreach(array("thumbnail", "upload", "trimming") as $_lab){
		$tags[] = $_lab;
		$tags[] = "is_".$_lab;
		$tags[] = "no_".$_lab;
		$tags[] = $_lab."_text";
		$tags[] = $_lab."_path_text";
	}

	// common/site_include/plugin/limitation_browse_blog_entry/limitation_browse_blog_entry.php
	$tags[] = "LimitationBrowseBlogEntry";

	// 専用の案件分
	$tags[] = "rec_tennis_qa_instance";

	// カスタムフィールド
	if(!class_exists("CustomField")) SOY2::import("site_include.plugin.CustomField.CustomField", "php");
	$cusObj = CMSPlugin::loadPluginConfig("CustomField");
	if($cusObj instanceof CustomFieldPlugin && is_array($cusObj->customFields) && count($cusObj->customFields)){
		foreach($cusObj->customFields as $_fieldObj){
			$tags[] = $_fieldObj->getId();
			$tags[] = $_fieldObj->getId()."_visible";
			$tags[] = $_fieldObj->getId()."_is_empty";
			$tags[] = $_fieldObj->getId()."_is_not_empty";
			$tags[] = "is_".$_fieldObj->getId();
			$tags[] = "no_".$_fieldObj->getId();
			if($_fieldObj->getType() == "pair"){
				for($i = 0; $i <= 10; $i++){
					$tags[] = $_fieldObj->getId()."_pair_".$i;
				}
			}
		}
	}

	// カスタムフィールドアドバンスド
	if(!class_exists("CustomFieldAdvanced")) SOY2::import("site_include.plugin.CustomFieldAdvanced.CustomFieldAdvanced", "php");
	$advObj = CMSPlugin::loadPluginConfig("CustomFieldAdvanced");
	if($advObj instanceof CustomFieldPluginAdvanced && is_array($advObj->customFields) && count($advObj->customFields)){
		foreach($advObj->customFields as $_fieldObj){
			$tags[] = $_fieldObj->getId();
			$tags[] = $_fieldObj->getId()."_visible";
			$tags[] = $_fieldObj->getId()."_is_empty";
			$tags[] = $_fieldObj->getId()."_is_not_empty";
			$tags[] = "is_".$_fieldObj->getId();
			$tags[] = "no_".$_fieldObj->getId();
			if($_fieldObj->getType() == "pair"){
				for($i = 0; $i <= 10; $i++){
					$tags[] = $_fieldObj->getId()."_pair_".$i;
				}
			}
		}
	}

	// 記事概要自動生成プラグイン(Gemini)
	$tags[] = "gemini_abstract";

	// cms:id="apps"
	$tags[] = "apps";
	
	if(count($tags)) file_put_contents($tagFilePath, implode("\n", $tags));
}

/**
 * @return array
 */
function checker_fn_get_tag_list(){
	checker_fn_update_cms_tag_list();

	$tags = array();

	$tagFilePath = UserInfoUtil::getSiteDirectory() . ".cache/tag_constant/"."tags.csv";
	if(file_exists($tagFilePath)){
		$tags = explode("\n", file_get_contents($tagFilePath));
	}

	return $tags;
}