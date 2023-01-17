<?php
AccessibilityPlugin::register();

class AccessibilityPlugin{

	const PLUGIN_ID = "x_accessibility";

	//挿入するページ
	var $config_per_page = array();
	var $config_per_blog = array();

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"ユーザー補助プラグイン",
			"type" => Plugin::TYPE_OPTIMIZE,
			"description"=>"ユーザ補助プラグイン対策でimgタグに自動でaltを挿入する",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/article/4907",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.1"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
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

		//ユーザ補助の対象ページであるか？
		if(!isset($this->config_per_page[$page->getId()]) || $this->config_per_page[$page->getId()] != 1) return $html;

		switch($page->getPageType()){
			case Page::PAGE_TYPE_BLOG:
				switch(SOYCMS_BLOG_PAGE_MODE){
					case CMSBlogPage::MODE_TOP:
					case CMSBlogPage::MODE_ENTRY:
					case CMSBlogPage::MODE_MONTH_ARCHIVE:
					case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
						if(!isset($this->config_per_blog[$page->getId()][SOYCMS_BLOG_PAGE_MODE]) || $this->config_per_blog[$page->getId()][SOYCMS_BLOG_PAGE_MODE] != 1) return $html;
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

		if(!function_exists("x_get_properties_by_img_tag")) SOY2::import("site_include.plugin.x_cls.func.fn", ".php");

		$htmls = array();
		foreach($lines as $line){
			//画像ファイルのある行を探す
			if(is_numeric(stripos($line, "<img"))){
				//一行に複数のimgタグ対応
				preg_match_all('/<img.*?>/', $line, $tmp);
				if(isset($tmp[0]) && is_array($tmp[0]) && count($tmp[0])){
					foreach($tmp[0] as $imgTag){
						$props = x_get_properties_by_img_tag($imgTag);
						if(count($props) && isset($props["src"])){
							$newTag = "";

							if(!isset($props["alt"]) || !strlen($props["alt"])) $props["alt"] = self::_getFileName($props["src"]);
							if(count($props)) $newTag = x_rebuild_image_tag($imgTag, $props);
							
							if(strlen($newTag) && $imgTag != $newTag){
								$line = str_replace($imgTag, $newTag, $line);
							}
						}
					}
				}
			//HTMLタグに言語の属性がない場合
			}else if(is_numeric(strpos($line, "<html"))){
				preg_match_all('/<html.*?>/', $line, $tmp);
				if(isset($tmp[0]) && is_array($tmp[0]) && count($tmp[0])){
					foreach($tmp[0] as $tag){
						$props = x_get_properties_by_tag($tag);
						if(!isset($props["lang"])) $props["lang"] = "ja";
						$newTag = x_rebuild_tag("html", $tag, $props);
						
						if(strlen($newTag) && $tag != $newTag){
							$line = str_replace($tag, $newTag, $line);
						}
					}
				}
			}

			$htmls[] = $line;
		}

		return implode("\n", $htmls);
	}

	/**
	 * @param string
	 * @return string
	 */
	private function _getFileName(string $src){
		if(!strlen($src)) return "no file name.";

		$filename = trim(substr($src, strrpos($src, "/")), "/");
		
		// 拡張子を削除
		return substr($filename, 0, strpos($filename, "."));
	}

	function config_page(){
		SOY2::import("site_include.plugin.x_accessibility.config.AccessibilityConfigPage");
		$form = SOY2HTMLFactory::createInstance("AccessibilityConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new AccessibilityPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
