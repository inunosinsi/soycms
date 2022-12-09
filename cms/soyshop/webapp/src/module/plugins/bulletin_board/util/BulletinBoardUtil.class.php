<?php

class BulletinBoardUtil {

	const FIELD_ID_GITHUB = "github_url";
	const FIELD_ID_TWITTER = "twitter_url";
	const FIELD_ID_MANAGER_SIDE = "management_side";
	const FIELD_ID_PROFILE = "profile";
	const FIELD_ID_SIGNATURE = "signature";
	const UPLOAD_KEY = "soyboard_post_image_";

	public static function getFieldList(){
		SOY2::import("module.plugins.user_custom_search_field.util.UserCustomSearchFieldUtil");
		return array(
			self::FIELD_ID_GITHUB => array("label" => "GitHub", "type" => UserCustomSearchFieldUtil::TYPE_URL),
			self::FIELD_ID_TWITTER => array("label" => "Twitter", "type" => UserCustomSearchFieldUtil::TYPE_URL),
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
			"img",
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
		$isEnclosePreTag = false;
		$lastLine = count($lines);
		for($i = 0; $i < $lastLine; $i++){
			$line = $lines[$i];

			// >から始まる行は<pre>で囲いたい
			if(!$isEnclosePreTag){
				if(strpos($line, ">") === 0) {
					// >を削除
					$line = self::_removeGreaterString($line);
					$line = "<pre>"  . rtrim($line);
					$isEnclosePreTag = true;
					//次の行で > から始まらなかった場合は閉じる
					if(!isset($lines[$i + 1]) || is_bool(strpos($lines[$i + 1], ">"))){
						$line .= "</pre>";
						$isEnclosePreTag = false;
					}
				}
			}else{
				if(is_bool(strpos($line, ">"))){
					$html = rtrim($html) . "</pre>\n";
					$isEnclosePreTag = false;
				}else{
					$line = self::_removeGreaterString($line);
				}
			}

			//<quote>を<backquote>に変換
			if(is_numeric(strpos($line, "<quote>"))) $line = str_replace("<quote>", "<backquote>", $line);
			if(is_numeric(strpos($line, "</quote>"))) $line = str_replace("</quote>", "</backquote>", $line);

			//タブスペースの振る舞い
			if(is_numeric(strpos($line, "    "))) $line = str_replace("    ", "\t", $line);
			if(is_numeric(strpos($line, "  "))) $line = str_replace("  ", "\t", $line);

			//<code>タグの場合は<pre><code>にする。<backquote>も同様の扱い
			foreach(array("code", "backquote") as $t){
				if(is_numeric(strpos($line, "<" . $t . ">")) && is_bool(strpos($line, "<pre><" . $t . ">"))) $line = trim(str_replace("<" . $t . ">", "<pre><" . $t . ">", $line));
				if(is_numeric(strpos($line, "</" . $t . ">")) && is_bool(strpos($line, "</" . $t . "></pre>"))) {
					$line = trim(str_replace("</" . $t . ">", "</" . $t . "></pre>", $line));
					if(strpos($line, "</" . $t . ">") === 0){	//何かのテキストの後に</code>がある場合はrtrimを行わない
						$html = rtrim($html);	//末端の改行を外す
					}else{
						$html = rtrim($html) . "\n";
					}
				}
			}


			if(is_numeric(strpos($line, "</pre>")) && strpos($line, "</pre>") === 0){	//何かのテキストの後に</pre>がある場合はrtrimを行わない
				$html = rtrim($html);	//末端の改行を外す
			}

			//<pre>内は<br>の改行なし
			if(is_numeric(strpos($line, "<pre>"))) {
				$noBrMode = true;
				if(strlen(strip_tags($line)) === 0) $line = trim($line);
			}

			$html .= $line;

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

	private static function _removeGreaterString($line){
		for(;;){
			if(is_bool(strpos($line, ">"))) break;
			$line = substr($line, 1);
		}
		return $line;
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

	//URLの表記を省略する
	public static function abbrUrlText($html){
		$html = trim($html);
		if(!strlen($html)) return "";

		$lines = explode("\n", $html);
		$html = "";
		foreach($lines as $line){
			preg_match_all('/<a.*href=\".*\">(.*?)<\/a>/', $line, $tmps);
			if(isset($tmps[1]) && count($tmps[1])){
				$txt = $tmps[1][0];
				$res = strpos($txt, "http");
				if(is_numeric($res) && $res === 0 && strlen($txt) > 30){
					$anc = "<a href=\"" . $txt . "\" target=\"_blank\" rel=\"noopener\">" . substr($txt, 0, 30) . "...</a>";
					$line = str_replace($tmps[0][0], $anc, $line);
				}
			}
			$html .= $line . "\n";
		}

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
