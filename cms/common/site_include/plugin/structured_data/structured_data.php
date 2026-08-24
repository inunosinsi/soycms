<?php

class StructuredDataPlugin{

	const PLUGIN_ID = "structured_data";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){

		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"構造化データ挿入プラグイン",
			"type" => Plugin::TYPE_PAGE,
			"description"=>"構造化データを追加します",
			"author"=>"齋藤 毅",
			"url"=>"https://saitodev.co/article/6397",
			"mail"=>"info@saitodev.co",
			"version"=>"0.5.1"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
				$this,"config_page"
			));

			SOY2::import("site_include.plugin.structured_data.util.StructuredDataUtil");
			
			//公開画面側
			if(defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onPageOutput',$this->getId(),array($this,"onPageOutput"));
			}
		}
	}

	function onPageOutput($obj){
		$arr = array();
		$arr["@context"] = "https://schema.org";

		// ブログページ
		if($obj instanceof CMSBlogPage){
			$cdate = $udate = 0;
		
			// 記事一覧　アーカイブ
			if(property_exists($obj, "entries") && is_array($obj->entries) && count($obj->entries)){
				$arr["@type"] = "WebSite";

				// ブログページ名
				$blogPage = soycms_get_blog_page_object($_SERVER["SOYCMS_PAGE_ID"]);
				$arr["name"] = $blogPage->getTitle();

				list($cdate, $udate) = StructuredDataUtil::getOldestEntryCdateAndLatestEntryUdate((int)$blogPage->getBlogLabelId());				

			// 記事詳細
			}else if(property_exists($obj, "entry") && $obj->entry instanceof LabeledEntry){
				$cdate = (int)$obj->entry->getCdate();
				$udate = (int)$obj->entry->getUdate();

				// ブログ記事SEOプラグインより
				$desp = trim((string)$obj->entry->getDescription());
				
				$arr["@type"] = "Article";
				$arr["headline"] = $obj->entry->getTitle();
				if(strlen($desp)) $arr["description"] = $desp;
			}

			//ブログ共通
			if(date("Ymd", $cdate) === date("Ymd", $udate)){
				$cdate = $udate;
			}

			$arr["datePublished"] = date("Y-m-d\TH:i:sP", $cdate);
			if(date("Ymd", $cdate) !== date("Ymd", $udate)){
				$arr["dateModified"] = date("Y-m-d\TH:i:sP", $udate);
			}
		}else if($obj instanceof CMSPage){
			$arr["@type"] = "WebSite";
			$page = soycms_get_page_object($_SERVER["SOYCMS_PAGE_ID"]);
			$arr["dateModified"] = date("Y-m-d\TH:i:sP", $page->getUdate());
		}

		// 著者 author
		$cnf = StructuredDataUtil::getConfig();
		if(isset($cnf["author"])){
			$authors = $cnf["author"];
			if(count($authors) && strlen(trim($authors[0]))){
				$arr["author"] = array();				
				foreach($authors as $author){
					$author = trim($author);
					if(!strlen($author)) continue;
					$arr["author"][] = array(
						"@type" => "Person",
						"name" => $author	
					);
				}
			}
		}
	
		$obj->addLabel(self::PLUGIN_ID, array(
			"soy2prefix" => "s_block",
			"html" => "<script type=\"application/ld+json\">".json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK)."</script>"
		));
	}

	function config_page($message){
		SOY2::import("site_include.plugin.structured_data.config.StructuredDataConfigPage");
		$form = SOY2HTMLFactory::createInstance("StructuredDataConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new StructuredDataPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}

StructuredDataPlugin::register();
