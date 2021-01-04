<?php

class ShapeHTMLLogic extends SOY2LogicBase {

	private $html;

	function __construct(){

	}

	private function _usageProhibitedHtmlTagList(){
		return array(
			"applet",
			"area",
			"frame",
			"iframe",
			"img",
			"map",
			"noscript",
			"object",
			"script",
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

		//JavaScriptのイベントやclass属性を削除
		foreach(array("on", "data", "class", "id", "style") as $t){
			self::_removeProperties($t);
		}
		self::_removeNoValuePropeties();

		$list = self::_usageProhibitedHtmlTagList();
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

		//preとcode
		self::_shapeHTMLInAnyTags();

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
		foreach(array("code", "pre") as $tag){
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

	private function _removeProperties($prop="class"){
		for(;;){
			preg_match('/ ' . $prop . '.*?=\".*?\"/ims', $this->html, $tmp);
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
		for(;;){
			preg_match('/<.* ([^=]*?)>/i', $this->html, $tmp);
			if(!isset($tmp[1])) break;
			$this->html = str_replace(" " . $tmp[1] . ">", ">", $this->html);
			self::_removeSpace(1);
		}
	}

	private function _removeSpace($try=5){
		$i = 0;
		for(;;){
			if($i++ > $try || is_bool(strpos($this->html, " >"))) break;
			$this->html = str_replace(" >", ">", $this->html);
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

		return $this->html;
	}

	function setHtml($html){
		$this->html = $html;
	}
}
