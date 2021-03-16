<?php

class ShapeHTMLLogic extends SOY2LogicBase {

	private $html;

	function __construct(){
		SOY2::import("module.plugins.bulletin_board.util.BulletinBoardUtil");
	}

	private function _usageProhibitedHtmlTagList(){
		return array(
			"applet",
			"area",
			"div",
			"frame",
			"iframe",
			//"img",
			"map",
			"noscript",
			"object",
			"script",
			"span",
			"style"
		);
	}

	private function _usageProhibitedMetaTagList(){
		return array("html", "head", "body", "meta", "title", "link");
	}

	private function _usageProhibitedInputTagList(){
		return array("button", "form", "input", "isindex", "option", "select", "textarea");
	}

	//変則的な変換のタグ
	private function _otherTagList(){
		return array("a");
	}

	function usageProhibitedHtmlTagList(){
		return self::_usageProhibitedHtmlTagList();
	}

	//変換
	function shape(){
		$this->html = trim($this->html);
		if(!strlen($this->html)) return "";

		//pre、quoteとcode
		self::_shapeHTMLInAnyTags();

		//HTMLのコメントを削除
		self::_removeCommentTag();

		//JavaScriptのイベントやclass属性を削除
		foreach(array("on", "data", "class", "id", "style") as $t){
			self::_removeProperties($t);
		}
		self::_removeNoValuePropeties();

		//$list = self::_usageProhibitedHtmlTagList();
		$list = array("script", "style");	//許可されていないタグから許可されているタグ以外に変更　許可されているタグ以外は後回しにする
		$list = array_merge($list, self::_usageProhibitedMetaTagList());
		$list = array_merge($list, self::_usageProhibitedInputTagList());
		$list = array_merge($list, self::_otherTagList());
		foreach($list as $tag){
			switch($tag){
				case "button":
				case "form":
				case "option":
				case "select":
				case "textarea":
					for(;;){
						preg_match('/<' . $tag . '.*?>.*?<\/' . $tag . '>/mis', $this->html, $tmp);
						if(!isset($tmp[0]) || !strlen($tmp[0])) break;
						$this->html = str_replace($tmp[0], "", $this->html);
					}

					//片割れしかないタグを削除
					for(;;){
						preg_match('/<' . $tag . '*?>/', $this->html, $tmp);
						if(!isset($tmp[0]) || !strlen($tmp[0])) break;
						$this->html = str_replace($tmp[0], "", $this->html);
					}
					for(;;){
						preg_match('/<\/' . $tag . '*?>/', $this->html, $tmp);
						if(!isset($tmp[0]) || !strlen($tmp[0])) break;
						$this->html = str_replace($tmp[0], "", $this->html);
					}
					break;
				//閉じタグありのHTMLタグの場合
				case "script":
				case "style":
					for(;;){
						preg_match('/<' . $tag . '.*?>.*?<\/' . $tag . '>/mis', $this->html, $tmp);
						if(!isset($tmp[0]) || !strlen($tmp[0])) break;
						$new = str_replace("<", "&lt;", $tmp[0]);
						$new = str_replace(">", "&gt;", $new);
						$this->html = str_replace($tmp[0], $new, $this->html);
					}

					//片割れしかないタグを削除
					for(;;){
						preg_match('/<' . $tag . '*?>/', $this->html, $tmp);
						if(!isset($tmp[0]) || !strlen($tmp[0])) break;
						$this->html = str_replace($tmp[0], "", $this->html);
					}
					for(;;){
						preg_match('/<\/' . $tag . '*?>/', $this->html, $tmp);
						if(!isset($tmp[0]) || !strlen($tmp[0])) break;
						$this->html = str_replace($tmp[0], "", $this->html);
					}
					break;
				case "a":
					preg_match_all('/<' . $tag . ' .*?>.*?<\/' . $tag . '>/mis', $this->html, $tmps);
					if(!isset($tmps[0]) || !count($tmps[0])) break;
					foreach($tmps[0] as $tmp){
						$anchor = self::_addTargetPropWithAnchorTag($tmp);
						if($anchor != $tmp) $this->html = str_replace($tmp, $anchor, $this->html);
					}
					break;
				default:
					for(;;){
						preg_match('/<' . $tag . '.*?>/mis', $this->html, $tmp);
						if(!isset($tmp[0]) || !strlen($tmp[0])) break;
						$this->html = str_replace($tmp[0], "", $this->html);
					}

					//念の為に閉じタグがあるか？を調べておく
					for(;;){
						preg_match('/<\/' . $tag . '.*?>/mis', $this->html, $tmp);
						if(!isset($tmp[0]) || !strlen($tmp[0])) break;
						$this->html = str_replace($tmp[0], "", $this->html);
					}
			}
		}

		//許可されているタグ以外を削除
		self::_removeUsageProhibitedHtmlTags();

		//<?php>がある場合
		self::_removePhpTag();

		// ->を-&gt;に変換する
		self::_shapeAllow();

		//シングルクオート、ダブルクオートや&を変換する
		self::_escapeHTMLTag();

		//imgタグの使用方法に問題がないか？あれば消す
		self::_checkImgTag();

		return $this->html;
	}

	private function _addTargetPropWithAnchorTag($tag){
		$props = self::_getProps($tag);
		if(!array_key_exists("target", $props)) $props["target"] = "_blank";
		if(!array_key_exists("rel", $props)) $props["rel"] = "noopener";

		//テキストの取得
		preg_match('/<a.*?>(.*?)<\/a>/', $tag, $tmp);
		$txt = (isset($tmp[1]) && strlen($tmp[1])) ? htmlspecialchars($tmp[1], ENT_QUOTES, "UTF-8") : null;
		if(is_null($txt) && isset($props["href"]) && strlen($props["href"])) $txt = htmlspecialchars($props["href"], ENT_QUOTES, "UTF-8");
		if(is_null($txt)) return null;

		if(!isset($props["href"]) || !strlen($props["href"])) return $txt;

		$tag = "<a";
		foreach($props as $key => $value){
			$tag .= " " . $key . "=\"" . $value . "\"";
		}
		$tag .= ">" . $txt . "</a>";

		return $tag;
	}

	private function _getProps($imgTag){
		$list = array();

		// prop="***"の方を調べる
		preg_match_all('/[a-zA-Z_0-9\-]*?=\".*?\"/', $imgTag, $tmp);
		if(isset($tmp[0]) && is_array($tmp[0]) && count($tmp[0])){
			foreach($tmp[0] as $p){
				$prop = explode("=", $p);
				if(!isset($prop[1])) continue;
				$v = trim(trim($prop[1], "\""));
				if(!strlen($v)) continue;
				$idx = trim($prop[0]);
				$list[$idx] = $v;
			}
		}

		// prop='***'の方を調べる
		preg_match_all("/[a-zA-Z_0-9\-]*?='.*?'/", $imgTag, $tmp);
		if(isset($tmp[0]) && is_array($tmp[0]) && count($tmp[0])){
			foreach($tmp[0] as $p){
				$prop = explode("=", $p);
				if(!isset($prop[1])) continue;
				$v = trim(trim($prop[1], "'"));
				if(!strlen($v)) continue;
				$idx = trim($prop[0]);
				$list[$idx] = $v;
			}
		}

		return $list;
	}

	private function _shapeHTMLInAnyTags(){
		// <code prop="****">のような形があるかもしれないので整形
		foreach(array("code", "pre", "quote") as $tag){
			preg_match_all('/<' . $tag . '.*?>/ims', $this->html, $tmps);
			if(isset($tmps[0]) && is_array($tmps[0]) && count($tmps[0])){
				foreach($tmps[0] as $tmp){
					$this->html = str_replace($tmp, "<" . $tag . ">", $this->html);
				}
			}
		}

		foreach(array("code", "pre") as $tag){
			preg_match_all('/<' . $tag . '>(.*?)<\/' . $tag . '>/ims', $this->html, $tmps);
			if(!isset($tmps[1])) break;
			foreach($tmps[1] as $tmp){
				$fragment = $tmp;
				$fragment = str_replace("<", "&lt;", $fragment);
				$fragment = str_replace(">", "&gt;", $fragment);

				$this->html = str_replace($tmp, $fragment, $this->html);
			}
		}

		//閉じタグがなければ末尾に閉じタグを付ける
		foreach(array("code", "pre") as $tag){
			preg_match_all('/<' . $tag . '>(.*?)<\/' . $tag . '>/ims', $this->html, $tmps);
			if(isset($tmps[1]) && count($tmps[1])) break;

			if(is_numeric(strpos($this->html, "<" . $tag . ">"))) {
				$this->html .= "</" . $tag . ">";
			}
		}

		//開始タグと閉じタグの数が合わない場合
		foreach(array("code", "pre") as $tag){
			$startTagCnt = substr_count($this->html, "<" . $tag . ">");
			$endTagCnt = substr_count($this->html, "</". $tag . ">");
			if($startTagCnt > $endTagCnt){
				$this->html .= "</" . $tag . ">";
			}else if($startTagCnt < $endTagCnt){
				$this->html = "<" . $tag . ">" . $this->html;
			}else {
				//何もしない
			}
		}
	}

	private function _shapeAllow(){
		if(is_numeric(strpos($this->html, "->"))) $this->html = str_replace("->", "-&gt;", $this->html);
		if(is_numeric(strpos($this->html, "=>"))) $this->html = str_replace("=>", "=&gt;", $this->html);
		if(is_numeric(strpos($this->html, "<-"))) $this->html = str_replace("<-", "&lt;-", $this->html);
		if(is_numeric(strpos($this->html, "<="))) $this->html = str_replace("<=", "&lt;=", $this->html);
	}

	private function _escapeHTMLTag(){
		$this->html = str_replace("'", "&#039;", $this->html);
		//$this->html = str_replace("\"", "&quot;", $this->html);
		//$this->html = str_replace("&", "&amp;", $this->html);
	}

	private function _checkImgTag(){
		preg_match_all('/<.*img.*src=\"(.*?)\".*>/ims', $this->html, $tmps);
		if(!isset($tmps[1]) || !count($tmps[1])) return;

		//srcのURLを確認
		$cnt = count($tmps[1]);
		for($i = 0; $i < $cnt; $i++){
			$path = $tmps[1][$i];

			//拡張子がないものはダメ
			if(is_bool(strpos($path, "."))){
				$this->html = str_replace($tmps[0][$i], "", $this->html);
				continue;
			}

			//拡張子がjpgでないものはダメ
			$ext = substr($path, strrpos($path, ".") + 1);
			if(is_bool(stripos($ext, "jpg")) && is_bool(stripos($ext, "jpeg"))){
				$this->html = str_replace($tmps[0][$i], "", $this->html);
				continue;
			}

			//パスが当サイトのものか？
			if(is_bool(strpos($path, "/" . SOYSHOP_ID . "/.tmp/")) && is_bool(strpos($path, "/" . SOYSHOP_ID . "/files/board/"))){
				$this->html = str_replace($tmps[0][$i], "", $this->html);
				continue;
			}

			//httpから始まるURLの場合
			if(strpos($path, "http") === 0){
				if(is_bool(strpos($path, $_SERVER["HTTP_HOST"]))){
					$this->html = str_replace($tmps[0][$i], "", $this->html);
					continue;
				}
			}else{	// スラッシュから始まる絶対パスの場合
				if(is_bool(strpos($path, "/" . SOYSHOP_ID . "/"))){
					$this->html = str_replace($tmps[0][$i], "", $this->html);
					continue;
				}
			}
		}
	}

	private function _removeProperties($prop="class"){
		for(;;){
			if($prop=="on"){
				preg_match('/ ' . $prop . '[a-zA-Z].*?=\".*?\"/ims', $this->html, $tmp);
			}else{
				preg_match('/ ' . $prop . '.*?=\".*?\"/ims', $this->html, $tmp);
			}

			if(!isset($tmp[0])) break;

			$this->html = str_replace($tmp[0], "", $this->html);
			self::_removeSpace();
		}

		for(;;){
			preg_match('/' . $prop . '.*?=\'.*?\'/ims', $this->html, $tmp);
			if(!isset($tmp[0])) break;

			$this->html = str_replace($tmp[0], "", $this->html);
			self::_removeSpace();
		}
	}

	//prop="***"ではない形の属性値
	private function _removeNoValuePropeties(){
		self::_removeSpace(3);
		preg_match_all('/<.* ([^=]*?)>/i', $this->html, $tmps);
		if(!isset($tmps[1]) || !is_array($tmps[1]) || !count($tmps[1])) return;

		for($i = 0; $i < count($tmps[0]); $i++){
			if(substr_count($tmps[0][$i], ">") > 1) continue;
			$tmp = $tmps[1][$i];
			$this->html = str_replace(" " . $tmp . ">", ">", $this->html);
			self::_removeSpace(1);
		}
	}

	private function _removeCommentTag(){
		for(;;){
			preg_match('/<!--.*?-->/', $this->html, $tmp);
			if(!isset($tmp[0])) break;
			$this->html = str_replace($tmp[0], "", $this->html);
		}
	}

	private function _removeUsageProhibitedHtmlTags(){
		preg_match_all('/<.*?>/', $this->html, $tmps);
		if(!count($tmps) || !isset($tmps[0]) || !count($tmps[0])) return;

		foreach($tmps[0] as $tmp){
			if(is_numeric(strpos($tmp, "<quote>")) || is_numeric(strpos($tmp, "</quote>")))	continue;	//<quote>は削除しない
			if(is_numeric(strpos($tmp, "</"))) continue;	//閉じタグは処理をしない
			if(!self::_checkIsRemoveTag($tmp)) continue;

			//開始タグ
			$this->html = str_replace($tmp, "", $this->html);

			//閉じタグ
			$endTag = self::_getEndTag($tmp);
			if(is_numeric(strpos($this->html, $endTag))) $this->html = $this->html = str_replace($endTag, "", $this->html);
		}
	}

	private function _checkIsRemoveTag($tag){
		static $tagList;
		if(is_null($tagList)) $tagList = BulletinBoardUtil::getUsagableHtmlTagList();

		foreach($tagList as $t){
			if(is_numeric(strpos($tag, "<" . $t)) && (is_numeric(strpos($tag, "<" . $t . ">")) || is_numeric(strpos($tag, "<" . $t . " ")))) return false;
		}

		return true;
	}

	private function _getEndTag($tag){
		$tag = trim(trim(trim(str_replace(array("<", ">"), "", $tag)), "/"));
		if(is_numeric(strpos($tag, " "))) $tag = trim(substr($tag, 0, strpos($tag, " ")));
		return "</" . $tag . ">";
	}

	private function _removePhpTag(){
		$this->html = str_replace("<?php>", "", $this->html);
		self::_removeSpace(1);
		$this->html = str_replace("<?php", "", $this->html);
		self::_removeSpace(1);
		$this->html = str_replace("?>", "", $this->html);
		self::_removeSpace(1);
		$this->html = str_replace(";>", "", $this->html);
		self::_removeSpace(1);
	}

	//スペースの削除はHTMLタグ内だけにする
	private function _removeSpace($try=5){
		$i = 0;
		for(;;){
			if($i++ > $try || is_bool(strpos($this->html, " >"))) break;
			$this->html = str_replace(" >", ">", $this->html);
		}

		$i = 0;
		for(;;){
			if($i++ > $try || is_bool(strpos($this->html, "> "))) break;
			$this->html = str_replace("> ", ">", $this->html);
		}

		$i = 0;
		for(;;){
			if($i++ > $try || is_bool(strpos($this->html, "< "))) break;
			$this->html = str_replace("< ", "<", $this->html);
		}
	}

	function return(){
		$this->html = trim($this->html);
		if(!strlen($this->html)) return "";

		$list = self::_usageProhibitedHtmlTagList();
		foreach($list as $tag){
			switch($tag){
				//閉じタグありのHTMLタグの場合
				case "script":
				case "style";
					for(;;){
						preg_match('/\&lt;' . $tag . '.*?\&gt;.*?\&lt;\/' . $tag . '\&gt;/mis', $this->html, $tmp);
						if(!isset($tmp[0]) || !strlen($tmp[0])) break;
						$new = str_replace("&lt;", "<", $tmp[0]);
						$new = str_replace("&gt;", ">", $new);
						$this->html = str_replace($tmp[0], $new, $this->html);
					}
					break;
				default:
					//
			}
		}

		//anchorタグ用
		foreach(array("target", "rel") as $t){
			self::_removeProperties($t);
		}

		self::_returnEscapedHTMLTag();

		return $this->html;
	}

	private function _returnEscapedHTMLTag(){
		$this->html = str_replace("&#039;", "'", $this->html);
		//$this->html = str_replace("&quot;", "\"", $this->html);
		$this->html = str_replace("&amp;", "&", $this->html);
	}

	function setHtml($html){
		$this->html = $html;
	}
}
