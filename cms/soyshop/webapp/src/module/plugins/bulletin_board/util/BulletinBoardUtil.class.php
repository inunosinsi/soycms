<?php

class BulletinBoardUtil {

	const FIELD_ID_MANAGER_SIDE = "management_side";
	const FIELD_ID_PROFILE = "profile";
	const FIELD_ID_SIGNATURE = "signature";
	const UPLOAD_KEY = "soyboard_post_image_";

	public static function getFieldList(){
		SOY2::import("module.plugins.user_custom_search_field.util.UserCustomSearchFieldUtil");
		return array(
			self::FIELD_ID_MANAGER_SIDE => array("label" => "運営側アカウント", "type" => UserCustomSearchFieldUtil::TYPE_CHECKBOX, "option" => "運営側", "is_admin_only" => 1),
			self::FIELD_ID_PROFILE => array("label" => "紹介文", "type" => UserCustomSearchFieldUtil::TYPE_TEXTAREA),
			self::FIELD_ID_SIGNATURE => array("label" => "署名", "type" => UserCustomSearchFieldUtil::TYPE_TEXTAREA),
		);
	}

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

	public static function autoInsertAnchorTag($html){
		//手動でアンカータグを入れている場合は、アンカータグの自動挿入はなし
		preg_match('/<a .*?>/', $html, $tmp);
		if(isset($tmp[0]) && strlen($tmp[0])) return $html;

		preg_match_all('/https?:\/{2}[\w\/:%#\$&\?\(\)~\.=\+\-]+/', $html, $tmps);
		if(!isset($tmps[0]) || !count($tmps[0])) return $html;

		foreach($tmps[0] as $url){
			//src="***"とhref="***"形式でないか？調べておく
			if(is_numeric(strpos($html, "src=\"" . $url))) continue;
			if(is_numeric(strpos($html, "href=\"" . $url))) continue;
			if(is_numeric(strpos($html, "src='" . $url))) continue;
			if(is_numeric(strpos($html, "href='" . $url))) continue;

			$new = "<a href=\"" . htmlspecialchars($url, ENT_QUOTES, "UTF-8") . "\" target=\"_blank\" rel=\"noopener\">" . $url . "</a>";
			$html = str_replace($url, $new, $html);
		}

		return $html;
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

	/** 通知メール周り **/
	public static function getMailConfig(){
		return SOYShop_DataSets::get("bulletin_board_mail.config", array(
			"footer" => "SOY Board on SOY Shop"
		));
	}

	public static function saveMailConfig($values){
		SOYShop_DataSets::put("bulletin_board_mail.config", $values);
	}
}
