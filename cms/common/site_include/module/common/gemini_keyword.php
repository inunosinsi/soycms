<?php
function soycms_gemini_keyword(string $html, WebPage $page){

	$obj = $page->create("gemini_keyword", "HTMLTemplatePage", array(
		"arguments" => array("gemini_keyword", $html)
	));

	//プラグインがアクティブかどうか？
	if(CMSPlugin::activeCheck("gemini_keyword")){
		// @ToDo 入力補完
		$html .= "<script>\n".file_get_contents(dirname(dirname(__DIR__))."/plugin/gemini_keyword/js/script.js")."</script>\n";
	}

	return $html;
}
