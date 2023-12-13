<?php

XBootstrapAssistantPlugin::register();

class XBootstrapAssistantPlugin{

	const PLUGIN_ID = "x_bootstrap_assistant";
	
	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"Bootstrap補助プラグイン",
			"type" => Plugin::TYPE_OPTIMIZE,
			"description"=>"埋め込み要素でBootstrapのクラスを追加する",
			"author"=>"齋藤毅",
			"url"=>"",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.1"
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
								if(CMSPlugin::activeCheck("x_lazy_load")){
									$props["loading"] = get_loading_property($embedType, self::PLUGIN_ID);
									if($embedType == "img" && isset($props["alt"])) unset($props["alt"]);
								}

								if(!isset($props["class"])) $props["class"] = "";

								switch($embedType){
									case "img":
										$props["class"] .= " img-fluid";
										break;
									case "iframe":
									default:
										$props["class"] .= " embed-responsive-item";
										break;
								}

								$props["class"] = trim($props["class"]);

								$new = x_rebuild_tag($embedType, $old, $props);
								if($new != $old) $line = str_replace($old, $new, $line);

								if($embedType == "iframe"){
									$width = (isset($props["width"])) ? (int)$props["width"] : 0;
									$height = (isset($props["height"])) ? (int)$props["height"] : 0;

									$old = x_get_parent_element_tag_by_embedded_tag($tags);
									$props = x_get_properties_by_tag($old);
									if(!isset($props["class"])) $props["class"] = "";

									$props["class"] .= " embed-responsive embed-responsive-".self::_calcAspectRatio($width, $height);
									$props["class"] = trim($props["class"]);
									$new = x_rebuild_tag(x_get_tag_element($old), $old, $props);
									if($new != $old) $line = str_replace($old, $new, $line);
								}
							}
						}
					}

					$html[] = $line;
				}

				$content = implode("\n", $html);
			}
		}

		$htmlObj->createAdd("bootstrap_assistant_content", "CMSLabel", array(
			"soy2prefix" => "cms",
			"html" => $content
		));
	}

	/**
	 * embed-responsive-16by9のアスベクト比 https://getbootstrap.jp/docs/4.1/utilities/embed/
	 * @param int, int
	 * @return string
	 */
	private function _calcAspectRatio(int $width=0, int $height=0){
		if($width <= 0 || $height <= 0) return "16by9";
		$rate = round(($height/$width)*10);

		if($rate >= 9) {
			return "1by1";
		}else if($rate >= 8){
			return "4by3";
		}else if($rate >= 5){
			return "16by9";
		}else {
			return "21by9";
		}
	}

	function config_page(){
		SOY2::import("site_include.plugin.x_bootstrap_assistant.config.BootstrapAssistantConfigPage");
		$form = SOY2HTMLFactory::createInstance("BootstrapAssistantConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new XBootstrapAssistantPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
