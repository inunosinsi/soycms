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
			"type" => Plugin::TYPE_OPTIMIZE,
			"description"=>"記事中の画像タグでloading属性を追加する",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/article/3278",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.9"
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

		//記事詳細ページを開いている時のみ
		if($htmlObj instanceOf EntryComponent){
			$content = (is_numeric($entryId)) ? soycms_get_entry_object($entryId)->getContent() : "";
			$lines = explode("\n", $content);
			if(count($lines)){
				if(!function_exists("x_get_properties_by_img_tag")) SOY2::import("site_include.plugin.x_cls.func.fn", ".php");
				if(!function_exists("get_loading_property")) SOY2::import("site_include.plugin.x_lazy_load.func.fn", ".php");

				$html = array();
				foreach($lines as $idx => $line){
					if(is_numeric(stripos($line, "img")) || is_numeric(stripos($line, "iframe"))){
						$tags = x_get_tags($line);
						if(count($tags)){							
							$old = x_get_embedded_element_tag($tags);
							if(strlen($old)){
								$props = x_get_properties_by_tag($old);
								
								$embedType = x_get_tag_element($old);
								$props["loading"] = get_loading_property($embedType);
								if($embedType == "img" && isset($props["alt"])) unset($props["alt"]);

								$new = x_rebuild_tag($embedType, $old, $props);
								if($new != $old) $line = str_replace($old, $new, $line);
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
