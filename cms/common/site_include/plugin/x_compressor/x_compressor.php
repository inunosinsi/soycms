<?php
CompressorPlugin::register();

class CompressorPlugin{

	const PLUGIN_ID = "x_compressor";

	//挿入するページ
	var $config_per_page = array();
	var $config_per_blog = array();

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"HTML圧縮プラグイン",
			"description"=>"HTMLを圧縮して、サーバ間のデータ転送を高速化する",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/article/3193",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.2"
		));
		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			//管理画面側
			if(!defined("_SITE_ROOT_")){
				//何もしない
			//公開側
			}else{
				CMSPlugin::setEvent('onOutput',self::PLUGIN_ID, array($this,"onOutput"), array("filter"=>"all"));
			}
		}
	}

	function onOutput($arg){
		$html = &$arg["html"];
		$page = &$arg["page"];

		//アプリケーションページと404ページの場合は静的化しない→静的化プラグイン等と併用しても意味がないため
		if($page->getPageType() == Page::PAGE_TYPE_APPLICATION || $page->getPageType() == Page::PAGE_TYPE_ERROR) return $html;

		//HTML圧縮の対象ページであるか？
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

		//圧縮を行う
		$lines = explode("\n", $html);
		$lineCnt = count($lines);
		if($lineCnt === 0) return $html;

		$isHTML5 = false;
		$isPre = false;

		$htmls = array();
		for($i = 0; $i < $lineCnt; $i++){
			$line = trim($lines[$i]);
			if(!strlen($line)) continue;
			$line = str_replace("\t", "", $line);
			if(!strlen($line)) continue;

			if(!$isHTML5 && strpos($line, "!DOCTYPE html") !== false) $isHTML5 = true;

			//半角スペースが２つ続く場合は一つのする
			$line = self::_deleteSpace($line);

			//imgのaltの値が空である場合は削除
			if(strpos($line, "alt=\"\"") !== false){
				$line = str_replace("alt=\"\"", "", $line);
			}

			//xhtmlの記述をなくす
			if($isHTML5) $line = self::_xhtml2html5($line);

			//半角スペースが２つ続く場合は一つのする
			$line = self::_deleteSpace($line);
			if(!strlen($line)) continue;

			//preタブであった場合 様々なパターンのpreがあるので要検討
			if(stripos($line, "<pre>") !== false){
				$pre = array();
				$pre[] = $line;
				for(;;){
					$i++;
					$line = $lines[$i];
					if(stripos($line, "</pre>") !== false){
						$pre[] = $line;
						break;
					}
					$pre[] = $line;
				}
				$line = rtrim(implode("\n", $pre), "\n");
			}

			$h[] = $line;
		}

		return implode("", $h);
	}

	//半角スペースが２つ続いている場合は１つのする
	private function _deleteSpace($line){
		for(;;){
			if(strpos($line, "  ") === false) break;
			$line = str_replace("  ", " ", $line);
		}
		return $line;
	}

	private function _xhtml2html5($line){
		for(;;){
			if(strpos($line, " />") === false) break;
			$line = str_replace(" />", ">", $line);
			if(strpos($line, " >") !== false){
				$line = str_replace(" >", ">", $line);
			}
		}
		return $line;
	}

	function config_page(){
		SOY2::import("site_include.plugin.x_compressor.config.HTMLCompressorConfigPage");
		$form = SOY2HTMLFactory::createInstance("HTMLCompressorConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new CompressorPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
