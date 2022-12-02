<?php

class IndexPage extends CMSWebPageBase{

	function __construct($arg){
		parent::__construct();

		// プラグインの古い分類分けの機能は廃止
		// if(isset($_GET["non_active"])){
		// 	$result = $this->run("Plugin.PluginListAction",array(
		// 		"state"=>false
		// 	));
		// 	if(!$result->success()){
		// 		$this->addErrorMessage("PAGE_LIST_GET_FAILED");
		// 		$this->jump("");
		// 		return;
		// 	}

		// 	$plugins = $result->getAttribute("plugins");
			

		// 	$this->createAdd("plguin_category_list","_component.Plugin.CategoryListComponent",array(
		// 		"list"=>array($this->getMessage("SOYCMS_INACTIVE_PLUGINS")=>$plugins)
		// 	));
		// 	$this->addLink("plugin_category_delete_link", array(
		// 		"visible" => false
		// 	));

		// }else if(isset($_GET["category"])){
		// 	$category = $_GET["category"];

		// 	$result = $this->run("Plugin.PluginListAction",array(
		// 		"state"=>true
		// 	));
		// 	if(!$result->success()){
		// 		$this->addErrorMessage("PAGE_LIST_GET_FAILED");
		// 		$this->jump("");
		// 		return;
		// 	}

		// 	$plugins = $result->getAttribute("plugins");
		// 	if(!isset($plugins[$category])){
		// 		$this->jump("Plugin");
		// 	}

		// 	$this->createAdd("plguin_category_list","_component.Plugin.CategoryListComponent",array(
		// 		"list"=>array($category=>$plugins[$category])
		// 	));


		// }else{

		$result = $this->run("Plugin.PluginListAction",array(
		));
		if(!$result->success()){
			$this->addErrorMessage("PAGE_LIST_GET_FAILED");
			$this->jump("");
			return;
		}

		$plugins = $result->getAttribute("plugins");
		
		// カテゴリ分けを加味
		$classifiedPlugins = CMSPlugin::classifyPluginsByType($plugins);
		$this->createAdd("plguin_category_list","_component.Plugin.CategoryListComponent",array(
			"list" => $classifiedPlugins,
			"labels" => self::_getPluginTypeLabelList($classifiedPlugins)
		));
		$this->addLink("plugin_category_delete_link", array(
			"visible" => false
		));
		//}

		$this->addForm("hidden_form", array(
			"action"=>SOY2PageController::createLink("Plugin.CreateCategory")
		));


		//メニューの表示
		$result = $this->run("Plugin.PluginListAction",array(
			"state"=>true
		));
		if(!$result->success()){
			$this->addErrorMessage("PAGE_LIST_GET_FAILED");
			$this->jump("");
			return;
		}

		$plugins = $result->getAttribute("plugins");
		$this->createAdd("plugin_category_menu","_component.Plugin.CategoryLinkListComponent",array(
			"list"=>$plugins
		));
		if(count($plugins)<1){
			 DisplayPlugin::hide("have_categories");
		}
	}

	/**
	 * @param array
	 * @return array
	 */
	private function _getPluginTypeLabelList(array $classifiedPlugins){
		if(!count($classifiedPlugins)) return array();
		
		$arr = array();
		$arr[] = $this->getMessage("SOYCMS_ACTIVE_PLUGINS");

		$pluginTypeList = Plugin::getPluginTypeList();
		
		foreach($classifiedPlugins as $idx => $pluginGroup){
			if($idx === 0) continue;	//$arr[] = $this->getMessage("SOYCMS_ACTIVE_PLUGINS");の行で既に対応済み
			if(!count($pluginGroup) || is_null($pluginGroup[0]->getType()) || !isset($pluginTypeList[$pluginGroup[0]->getType()])) continue;
			$arr[] = $this->getMessage("SOYCMS_NOT_ACTIVE_PLUGINS")."：".$pluginTypeList[$pluginGroup[0]->getType()];
		}

		$arr[] = $this->getMessage("SOYCMS_NOT_ACTIVE_PLUGINS")."：未分類";

		return $arr;
	}
}
