<?php
CLSPlugin::register();

class CLSPlugin{

	const PLUGIN_ID = "x_cls";

	const MODE_PROPERTY = 0;
	const MODE_PICTURE = 1;

	//挿入するページ
	var $config_per_page = array();
	var $config_per_blog = array();

	private $mode = self::MODE_PROPERTY;
	private $resizeDir = "cls";
	private $minWidth = 400;
	private $resizeWidth = 320;

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"Cumulative Layout Shiftプラグイン",
			"description"=>"Cumulative Layout Shift対策で画像のサイズを取得してHTMLタグを生成しなおす。(注)一行一画像の状態のみ対応",
			"author"=>"齋藤毅",
			"url"=>"",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.5"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			SOY2::import("site_include.plugin.x_cls.util.CLSUtil");
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
				$this,"config_page"
			));

			//公開側
			if(defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onOutput',self::PLUGIN_ID, array($this,"onOutput"), array("filter"=>"all"));
			}
		}
	}

	function onOutput($arg){
		$html = &$arg["html"];
		$page = &$arg["page"];

		//アプリケーションページと404ページの場合は静的化しない→静的化プラグイン等と併用しても意味がないため
		if($page->getPageType() == Page::PAGE_TYPE_APPLICATION || $page->getPageType() == Page::PAGE_TYPE_ERROR) return $html;

		//CLSの対象ページであるか？
		if(!isset($this->config_per_page[$page->getId()]) || $this->config_per_page[$page->getId()] != 1) return $html;

		switch($page->getPageType()){
			case Page::PAGE_TYPE_BLOG:
				$webPage = &$arg["webPage"];
				switch($webPage->mode){
					case CMSBlogPage::MODE_TOP:
					case CMSBlogPage::MODE_ENTRY:
					case CMSBlogPage::MODE_MONTH_ARCHIVE:
					case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
						if(!isset($this->config_per_blog[$page->getId()][$webPage->mode]) || $this->config_per_blog[$page->getId()][$webPage->mode] != 1) return $html;
						break;
					case CMSBlogPage::MODE_RSS:
					case CMSBlogPage::MODE_POPUP:
						return $html;
				}
				break;
			case Page::PAGE_TYPE_NORMAL:
			case Page::PAGE_TYPE_APPLICATION:
			default:
				//何もしない
		}


		$lines = explode("\n", $html);
		if(!count($lines)) return $html;

		// @ToDo 一行に複数画像を取得する方法を考える
		$htmls = array();
		foreach($lines as $line){
			//画像ファイルのある行を探す
			if(is_numeric(stripos($line, "<img"))){
				//属性を全て取得
				$props = self::_getProps($line);
				if(count($props) && isset($props["src"])){
					//画像のサイズを取得
					$info = self::_getImageInfo($props["src"]);

					// pictureモード
					if($this->mode == self::MODE_PICTURE) {
						$line = self::_setPictureElement($line, $info, $props);
					}else{
						if($this->mode == self::MODE_PROPERTY && count($info)) $props = self::_mergeProps($info, $props);
						if(count($props)) $line = self::_rebuildImgTag($line, $props);
					}
				}
			}

			$htmls[] = $line;
		}

		return implode("\n", $htmls);
	}

	private function _getProps($line){
		preg_match('/<img(.*?)>/i', $line, $tmp);
		if(!isset($tmp[1])) return array();
		$p = trim(trim($tmp[1], "/"));
		if(!strlen($p)) return array();

		$props = explode(" ", $p);
		if(!count($props)) return array();

		$list = array();
		foreach($props as $p){
			$prop = explode("=", $p);
			if(!isset($prop[1])) continue;
			$v = trim(trim($prop[1], "\""));
			if(!strlen($v)) continue;
			$idx = trim($prop[0]);
			$list[$idx] = $v;
		}

		return $list;
	}

	private function _getImageInfo($path){
		if(strpos($path, "/") !== 0){
			// @ToDo スラッシュから始まらない場合はドメインを削除
		}

		$path = $_SERVER["DOCUMENT_ROOT"] . $path;
		if(!file_exists($path)) return array();

		$info = getimagesize($path);
		return array("width" => $info[0], "height" => $info[1]);
	}

	private function _mergeProps($props, $info){
		foreach($info as $idx => $v){
			$props[$idx] = $v;
		}
		return $props;
	}

	private function _rebuildImgTag($line, $props){
		if(!is_array($props) || !count($props)) return $line;
		$imgTag = "<img";
		foreach($props as $idx => $v){
			$imgTag .= " " . $idx . "=\"" . $v . "\"";
		}
		$imgTag .= ">";
		return preg_replace('/<img(.*?)>/i', $imgTag, $line);
	}

	private function _setPictureElement($line, $info, $props){
		//小さい画像はMODE_PROPERTY対応
		if(!isset($info["width"]) || $info["width"] < $this->resizeWidth) return self::_rebuildImgTag($line, self::_mergeProps($info, $props));

		preg_match('/<img.*?>/', $line, $tmp);
		if(isset($tmp[0])){
			//画像のリサイズをかます
			$src = self::_getSrc($line);
			if(is_null($src)) return $line;

			$newSrc = self::_autoGenerateMiniImageFile($src);
			if(is_null($newSrc)) return $line;

			//imgタグを書き換える
			$line = str_replace($src, $newSrc, $line);

			/** ダメだったコードを残しておく **/
			//$line = self::_rebuildImgTag($line, self::_mergeProps(self::_getImageInfo($newSrc), self::_getProps($line)));
			/** ダメだったコードを残しておく **/

			$tag = "<picture><source srcset=\"" . $src . "\" media=\"(min-width:" . $this->minWidth . "px)\">";
			preg_match('/<img.*?>/', $line, $tmp);	//新しくなったimgタグを再び正規表現で調べる
			$line = str_replace($tmp[0], $tag . $tmp[0] . "</picture>", $line);
		}
		return $line;
	}

	private function _getSrc($line){
		preg_match('/<img(.*?)>/i', $line, $tmp);
		if(!isset($tmp[1])) return null;
		$p = trim(trim($tmp[1], "/"));
		if(!strlen($p)) return null;

		$props = explode(" ", $p);
		if(!count($props)) return array();

		foreach($props as $p){
			$prop = explode("=", $p);
			if(!isset($prop[1])) continue;

			$idx = trim($prop[0]);
			if($idx == "src") {
				return trim(trim($prop[1], "\""));
			}
		}

		return null;
	}

	private function _autoGenerateMiniImageFile($path){
		//スラッシュから始まらない場合は何もしない
		if(strpos($path, "/") !== 0) return null;

		$fullpath = $_SERVER["DOCUMENT_ROOT"] . $path;
		if(strpos($fullpath, "//")) $fullpath = str_replace("//", "/", $fullpath);
		if(!file_exists($fullpath)) return null;

		$dir = rtrim(substr($fullpath, 0, strrpos($fullpath, "/")), "/") . "/" . $this->resizeDir . "/";
		if(!file_exists($dir)) mkdir($dir);

		$filename = ltrim(substr($fullpath, strrpos($fullpath, "/")), "/");
		$newFullpath = $dir . $filename;
		if(!file_exists($newFullpath)){
			soy2_resizeimage($fullpath, $newFullpath, $this->resizeWidth);
			exec("guetzli --quality 84 " . $newFullpath . " " . $newFullpath);
		}

		return rtrim(substr($path, 0, strrpos($path, "/")), "/") . "/" . $this->resizeDir . "/" . $filename;
	}

	function config_page(){
		SOY2::import("site_include.plugin.x_cls.config.CLSConfigPage");
		$form = SOY2HTMLFactory::createInstance("CLSConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function getMode(){
		return $this->mode;
	}
	function setMode($mode){
		$this->mode = $mode;
	}

	function getMinWidth(){
		return $this->minWidth;
	}
	function setMinWidth($minWidth){
		$this->minWidth = $minWidth;
	}

	function getResizeWidth(){
		return $this->resizeWidth;
	}
	function setResizeWidth($resizeWidth){
		$this->resizeWidth = $resizeWidth;
	}

	function getResizeDir(){
		return $this->resizeDir;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new CLSPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
