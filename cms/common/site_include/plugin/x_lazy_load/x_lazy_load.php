<?php

XLazyLoadPlugin::register();

class XLazyLoadPlugin{

	const PLUGIN_ID = "x_lazy_load";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"LazyLoadプラグイン",
			"description"=>"記事中の画像タグでloading属性を追加する",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/article/3278",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.5"
		));

		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			if(defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onEntryOutput', self::PLUGIN_ID, array($this, "onEntryOutput"));
			}
		}
	}

	function onEntryOutput($arg){
		$entryId = $arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];

		$content = "";

		//記事一覧ページを開いている時のみ
		if($htmlObj instanceOf EntryComponent){
			try{
				$content = SOY2DAOFactory::create("cms.EntryDAO")->getById($entryId)->getContent();
			}catch(Exception $e){
				//
			}

			$lines = explode("\n", $content);
			if(count($lines)){
				$html = array();
				$imgTagCnt = 0;	//imgタグが何回出現したか？
				foreach($lines as $line){
					if(is_numeric(stripos($line, "<img"))){
						//alt=""があれば消しておく
						if(strpos($line, "alt=\"\"")){
							$line = str_replace("alt=\"\"", "", $line);
						}

						//imgタグ2回目からloading="lazy"を追加する
						if($imgTagCnt++ > 1){
							preg_match('/<img.*?loading=\".*\".*?>/', $line, $tmp);
							if(!count($tmp)){
								$line = str_replace("<img ", "<img loading=\"lazy\" ", $line);
							}
						}
					}
					$html[] = $line;
				}

				$content = implode("\n", $html);
			}
		}

		$htmlObj->createAdd("lazy_load_content", "CMSLabel", array(
			"soy2prefix" => "cms",
			"html" => $content
		));
	}

	function config_page(){
		SOY2::import("site_include.plugin.x_lazy_load.config.LazyLoadConfigPage");
		$form = SOY2HTMLFactory::createInstance("LazyLoadConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new XLazyLoadPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
