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
			"version"=>"0.7"
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
		for($i = 0; $i < $lineCnt; ++$i){
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

			//<p></p>は<br>にする
			if(strpos($line, "<p></p>") !== false){
				$line = str_replace("<p></p>", "<br>", $line);
			}

			//xhtmlの記述をなくす
			if($isHTML5) $line = self::_xhtml2html5($line);

			//半角スペースが２つ続く場合は一つのする
			$line = self::_deleteSpace($line);
			if(!strlen($line)) continue;

			//preタブであった場合 様々なパターンのpreがあるので要検討
			preg_match('/<pre.*?>/', $line, $res);
			if(isset($res[0])){
				$pre = array();
				$pre[] = $line;
				if(stripos($line, "</pre>") === false) {	//同じ行に</pre>がある場合はおかしくなるのでチェックしておく
					for(;;){
						$i++;
						$line = $lines[$i];
						if(stripos($line, "</pre>") !== false){
							$pre[] = $line;
							break;
						}
						$pre[] = $line;
					}
				}

				$line = "\n" . rtrim(implode("\n", $pre), "\n") ."\n";
			}

			//リンクでhttpから始まる絶対パスの場合、スラッシュから始まる絶対パスに変更
			if(stripos($line, "<a") !== false){
				preg_match_all('/href=\"(.*?)\"/', $line, $tmp);
				if(isset($tmp[1]) && is_array($tmp[1]) && isset($_SERVER["HTTP_HOST"])){
					foreach($tmp[1] as $old){
						if(strpos($old, "http://www.facebook.com") === 0 || strpos($old, "https://www.facebook.com") === 0) continue;
						if(strpos($old, "http://twitter.com") === 0 || strpos($old, "https://twitter.com") === 0) continue;
						if(preg_match('/https?:\/\/' . $_SERVER["HTTP_HOST"] . '\//', $old, $tmp2)){
							$new = str_replace("https://", "", $old);
							$new = str_replace("http://", "", $new);
							$new = str_replace($_SERVER["HTTP_HOST"], "", $new);
							$line = str_replace($old, $new, $line);
						}
					}
				}
			}

			//コメントをなくす
			if(preg_match('/<!--[\s\S]*?-->/', $line, $tmp)){
				$line = preg_replace('/<!--[\s\S]*?-->/', '', $line);
			}

			$htmls[] = $line;
		}

		return implode("", $htmls);
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
