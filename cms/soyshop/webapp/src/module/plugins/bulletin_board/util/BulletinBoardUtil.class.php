<?php

class BulletinBoardUtil {

	const UPLOAD_KEY = "soyboard_post_image_";

	public static function getUsageProhibitedHtmlTagList(){
		return SOY2Logic::createInstance("module.plugins.bulletin_board.logic.ShapeHTMLLogic")->usageProhibitedHtmlTagList();
	}

	public static function getUsagableHtmlTagList(){
		return SOYShop_DataSets::get("bulletin_board_usage_html_tag.config", array(
			"a",
			"b",
			"code",
			"font",
			"h1",
			"h2",
			"h3",
			"h4",
			"h5",
			"h6",
			"hr",
			"i",
			"pre",
			"q",
			"s",
			"strong",
			"sub",
			"sup",
			"u"
		));
	}

	public static function shapeHTML($html){
		return SOY2Logic::createInstance("module.plugins.bulletin_board.logic.ShapeHTMLLogic", array("html" => $html))->shape();
	}

	public static function nl2br($html){
		$html = trim($html);
		if(!strlen($html)) return "";

		$lines = explode("\n", $html);
		$html = "";

		$noBrMode = false;
		$lastLine = count($lines);
		for($i = 0; $i < $lastLine; $i++){
			$line = $lines[$i];

			//タブスペースの振る舞い
			if(is_numeric(strpos($line, "    "))) $line = str_replace("    ", "\t", $line);
			if(is_numeric(strpos($line, "  "))) $line = str_replace("  ", "\t", $line);

			//<code>タグの場合は<pre><code>にする
			if(is_numeric(strpos($line, "<code>")) && is_bool(strpos($line, "<pre><code>"))) $line = "<pre><code>";
			if(is_numeric(strpos($line, "</code>")) && is_bool(strpos($line, "</code></pre>"))) $line = "</code></pre>";

			$html .= $line;

			//<pre>内は改行なし
			if(is_numeric(strpos($line, "<pre>"))) $noBrMode = true;

			//改行なし
			if($line == "<code>") continue;

			if(!$noBrMode && $i < $lastLine - 1) $html .= "<br>";

			//改行なしモードを戻す
			if(is_numeric(strpos($line, "</pre>"))) $noBrMode = false;
			$html .= "\n";
		}

		//</code>
		$html = str_replace("<br>\n</code>", "</code>", $html);

		$html = str_replace("<code>\n", "<code>", $html);
		$html = str_replace("\n</code>", "</code>", $html);

		return trim($html);
	}

	public static function returnHTML($html){
		return SOY2Logic::createInstance("module.plugins.bulletin_board.logic.ShapeHTMLLogic", array("html" => $html))->return();
	}


	/** 画像のアップロード周り **/
	public static function getUploadSessionKey($topicId, $userId){
		return self::UPLOAD_KEY . $topicId . "_" . $userId;
	}

	public static function getEditUploadSessionKey($postId, $topicId, $userId){
		return self::UPLOAD_KEY . "edit_" . $postId . "_" . $topicId . "_" . $userId;
	}

	public static function pushEmptyValues($array){
		$cnt = count($array);
		$diff = 12 - $cnt;
		for($i = 0; $i < $diff; $i++){
			$array[] = "";
		}
		return $array;
	}

	//画像のパスからファイル名を取得
	public static function path2filename($path){
		return trim(trim(substr($path, strrpos($path, "/")), "/"));
	}
}
