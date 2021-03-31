<?php
SOYCMS_Search_Block_Plugin::registerPlugin();

class SOYCMS_Search_Block_Plugin{

	const PLUGIN_ID = "soycms_search_block";

	function getId(){
		return self::PLUGIN_ID;
	}

	/**
	 * 初期化
	 */
	function init(){

		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"SOY CMS検索結果ブロックプラグイン",
			"description"=>"プラグインブロックでブログ記事の検索結果を表示します",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"1.4.1"
		));

		if(CMSPlugin::activeCheck($this->getId())){
			CMSPlugin::addPluginConfigPage($this->getId(),array(
				$this,"config_page"
			));

			//管理画面側
			if(!defined("_SITE_ROOT_")){
				//
			}else{
				CMSPlugin::setEvent('onPageOutput', self::PLUGIN_ID, array($this, "onPageOutput"));
			}
			CMSPlugin::setEvent('onPluginBlockLoad',self::PLUGIN_ID, array($this, "onLoad"));
			CMSPlugin::setEvent('onPluginBlockAdminReturnPluginId',self::PLUGIN_ID, array($this, "returnPluginId"));
		}
	}

	function onPageOutput($obj){
		//ブログページでトップページ以外では以下のコードを読み込まない
		if(get_class($obj) == "CMSBlogPage" && ($obj->mode == "_entry_" || $obj->mode == "_month_" || $obj->mode == "_category_")) return;

		SOY2::import("site_include.plugin.soycms_search_block.component.BlockPluginPagerComponent");
		$logic = SOY2Logic::createInstance("site_include.plugin.soycms_search_block.logic.SearchBlockEntryLogic");

		$url = (isset($_SERVER["REDIRECT_URL"])) ? $_SERVER["REDIRECT_URL"] : "";
		if(strpos($url, "page-")) $url = substr($url, 0, strpos($url, "/page-")) . "/";

		$pageId = (int)$_SERVER["SOYCMS_PAGE_ID"];
		SOY2::import("site_include.plugin.soycms_search_block.util.PluginBlockUtil");
		$limit = PluginBlockUtil::getLimitByPageId($pageId);
		if(is_null($limit)) $limit = 100000;

		$query = (isset($_GET["q"]) && strlen(trim($_GET["q"]))) ? htmlspecialchars(trim($_GET["q"]), ENT_QUOTES, "UTF-8") : null;

		$obj->addLabel("search_keyword", array(
			"soy2prefix" => "cms",
			"text" => $query
		));

		$args = $logic->getArgs();
		$labelId = PluginBlockUtil::getLabelIdByPageId($pageId);
		$current = (isset($args[0]) && strpos($args[0], "page-") === 0) ? (int)str_replace("page-", "", $args[0]) : 0;
		$last_page_number = (int)ceil($logic->getTotal($labelId, $query) / $limit);

		$obj->createAdd("s_pager", "BlockPluginPagerComponent", array(
			"list" => array(),
			"current" => $current,
			"last"	 => $last_page_number,
			"url"		=> $url,
			"queries" => array("q" => $query),
			"soy2prefix" => "p_block",
		));

		$obj->addModel("s_has_pager", array(
			"soy2prefix" => "p_block",
			"visible" => ($last_page_number >1)
		));
		$obj->addModel("s_no_pager", array(
			"soy2prefix" => "p_block",
			"visible" => ($last_page_number <2)
		));

		$obj->addLink("s_first_page", array(
			"soy2prefix" => "p_block",
			"link" => $url . "?q=" . $query,
		));

		$obj->addLink("s_last_page", array(
			"soy2prefix" => "p_block",
			"link" => ($last_page_number > 0) ? $url . "page-" . ($last_page_number - 1) . "?q=" . $query : null,
		));

		$obj->addLabel("s_current_page", array(
			"soy2prefix" => "p_block",
			"text" => max(1, $current + 1),
		));

		$obj->addLabel("s_pages", array(
			"soy2prefix" => "p_block",
			"text" => $last_page_number,
		));
	}

	function onLoad(){

		//検索クエリが空文字の場合は検索をやめる
		if(!isset($_GET["q"]) || strlen(trim($_GET["q"])) === 0) return array();
		$query = htmlspecialchars(trim($_GET["q"]), ENT_QUOTES, "UTF-8");

		//検索結果ブロックプラグインのUTILクラスを利用する
		SOY2::import("site_include.plugin.soycms_search_block.util.PluginBlockUtil");
		$pageId = (int)$_SERVER["SOYCMS_PAGE_ID"];

		//ラベルIDを取得とデータベースから記事の取得件数指定
		$labelId = PluginBlockUtil::getLabelIdByPageId($pageId);
		if(is_null($labelId)) return array();

		$count = PluginBlockUtil::getLimitByPageId($pageId);

		return SOY2Logic::createInstance("site_include.plugin.soycms_search_block.logic.SearchBlockEntryLogic")->search($labelId, $query, $count);
	}

	function returnPluginId(){
		return self::PLUGIN_ID;
	}

	/**
	 * 設定画面の表示
	 */
	function config_page($message){
		SOY2::import("site_include.plugin.soycms_search_block.config.SearchBlockConfigPage");
		$form = SOY2HTMLFactory::createInstance("SearchBlockConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){

		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new SOYCMS_Search_Block_Plugin();
		}
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
