<?php
/**
 * @class IndexPage
 * @date 2008-10-29T18:46:55+09:00
 * @author SOY2HTMLFactory
 */
class ListPage extends WebPage{

	private $logic;

	function ListPage(){
		
		SOY2::import("domain.plugin.SOYShop_PluginConfig");
		WebPage::WebPage();
		
		$this->addActionLink("plugin_ini_button", array(
			"link" => SOY2PageController::createLink("Plugin.List"),
			"visible" => (SOYShopPluginUtil::checkPluginListFile()),
			"onclick" => "return confirm('プラグイン一覧を初期化します。よろしいですか？');"
		));

    	$logic = SOY2Logic::createInstance("logic.plugin.SOYShopPluginLogic");
		$logic->prepare();
		
		$this->logic = $logic;
		
		//plugin.iniに記載されている内容で初期化
		if(soy2_check_token()){
			$logic->initModuleByPluginIni();
			SOY2PageController::jump("Plugin?updated");
		}

		//一旦モジュールをすべて読み込む
		$logic->searchModules();
		
		$this->createAdd("module_type_list", "_common.Plugin.ModuleTypeListComponent", array(
			"list" => SOYShop_PluginConfig::getPluginTypeList(),
			"mode" => SOYShop_PluginConfig::MODE_ALL
		));
	}

	private static function SortByType($a, $b){
		return ($a->getType() >= $b->getType());
	}
}

