<?php
/**
 * @class IndexPage
 * @date 2008-10-29T18:46:55+09:00
 * @author SOY2HTMLFactory
 */
class IndexPage extends WebPage{

	private $logic;

	function doPost(){
		if(soy2_check_token()){

			$pluginDao = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");

			if(isset($_POST["Plugin"])){
				foreach($_POST["Plugin"] as $pluginId => $int){
					if((int)$int < 1) $int = SOYShop_PluginConfig::DISPLAY_ORDER_MAX;
					$plugin = soyshop_get_plugin_object($pluginId);
					if(is_null($plugin->getId())) continue;

					if($int != $plugin->getDisplayOrder()){
						$plugin->setDisplayOrder($int);
						try{
							$pluginDao->update($plugin);
						}catch(Exception $e){
							//
						}
					}
				}
			}

			if(isset($_POST["all"])){
				try{
					$pluginDao->executeUpdateQuery("UPDATE soyshop_plugins SET is_active = 0;");
				}catch(Exception $e){
					var_dump($e);
				}
			}

			SOY2PageController::jump("Plugin?successed");
		}
	}

	function __construct(){
		SOY2::import("domain.plugin.SOYShop_PluginConfig");
		parent::__construct();

		$this->addForm("form");

		$this->createAdd("module_type_list", "_common.Plugin.ModuleTypeListComponent", array(
			"list" => SOYShop_PluginConfig::getPluginTypeList(),
			"mode" => SOYShop_PluginConfig::MODE_INSTALLED
		));

    	$this->addLink("search_modules", array(
    		"link" => SOY2PageController::createLink("Plugin.List")
    	));

		//隠しモード /soyshop/logic/init/plugin/plugin.iniがある場合、全てのプラグインを一括でアンインストールできるボタンを表示する
		DisplayPlugin::toggle("all_uninstall_button", ((int)SOY2ActionSession::getUserSession()->getAttribute("isdefault") === 1) && SOYShopPluginUtil::checkPluginListFile());
		$this->addForm("all_uninstall_form");
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("プラグイン管理");
	}
}


function my_sort_by_type($a, $b){
	return ($a->getType() >= $b->getType());
}
