<?php
/**
 * @class IndexPage
 * @date 2008-10-29T18:46:55+09:00
 * @author SOY2HTMLFactory
 */
class IndexPage extends WebPage{
	
	private $logic;
	
	function doPost(){
		if(soy2_check_token() && ($_POST["Plugin"])){
			
			$pluginDao = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");
			
			foreach($_POST["Plugin"] as $pluginId => $int){
				if((int)$int < 1) $int = SOYShop_PluginConfig::DISPLAY_ORDER_MAX;
				
				try{
					$plugin = $pluginDao->getById($pluginId);
				}catch(Exception $e){
					continue;
				}
				
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
	}

	function __construct(){
		SOY2::import("domain.plugin.SOYShop_PluginConfig");
		WebPage::__construct();
		
		$this->addForm("form");
		
		$this->createAdd("module_type_list", "_common.Plugin.ModuleTypeListComponent", array(
			"list" => SOYShop_PluginConfig::getPluginTypeList(),
			"mode" => SOYShop_PluginConfig::MODE_INSTALLED
		));

    	$this->addLink("search_modules", array(
    		"link" => SOY2PageController::createLink("Plugin.List")
    	));
	}
}


function my_sort_by_type($a, $b){
	return ($a->getType() >= $b->getType());
}

?>