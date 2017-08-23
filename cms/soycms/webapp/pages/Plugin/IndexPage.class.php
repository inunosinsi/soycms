<?php

class IndexPage extends CMSWebPageBase{

	function __construct($arg){
		parent::__construct();

		if(isset($_GET["non_active"])){
			$result = $this->run("Plugin.PluginListAction",array(
				"state"=>false
			));
			if(!$result->success()){
				$this->addErrorMessage("PAGE_LIST_GET_FAILED");
				$this->jump("");
				return;
			}

			$plugins = $result->getAttribute("plugins");


			$this->createAdd("plguin_category_list","PluginCategoryList",array(
				"list"=>array($this->getMessage("SOYCMS_INACTIVE_PLUGINS")=>$plugins)
			));
			$this->createAdd("plugin_category_delete_link","HTMLLink",array(
				"visible" => false
			));

		}else if(isset($_GET["category"])){
			$category = $_GET["category"];

			$result = $this->run("Plugin.PluginListAction",array(
				"state"=>true
			));
			if(!$result->success()){
				$this->addErrorMessage("PAGE_LIST_GET_FAILED");
				$this->jump("");
				return;
			}

			$plugins = $result->getAttribute("plugins");
			if(!isset($plugins[$category])){
				$this->jump("Plugin");
			}

			$this->createAdd("plguin_category_list","PluginCategoryList",array(
				"list"=>array($category=>$plugins[$category])
			));


		}else{

			$result = $this->run("Plugin.PluginListAction",array(
			));
			if(!$result->success()){
				$this->addErrorMessage("PAGE_LIST_GET_FAILED");
				$this->jump("");
				return;
			}

			$plugins = $result->getAttribute("plugins");

			$active = array();
			$non_active = array();

			foreach($plugins as $key => $plugin){
				if($plugin->isActive()){
					$active[] = $plugin;
				}else{
					$non_active[] = $plugin;
				}
			}

			$this->createAdd("plguin_category_list","PluginCategoryList",array(
				"list"=>array($this->getMessage("SOYCMS_ACTIVE_PLUGINS")=>$active,$this->getMessage("SOYCMS_NOT_ACTIVE_PLUGINS")=>$non_active)
			));
			$this->createAdd("plugin_category_delete_link","HTMLLink",array(
				"visible" => false
			));

		}

		$this->createAdd("hidden_form","HTMLForm",array(
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
		$this->createAdd("plugin_category_menu","CategoryLinkList",array(
			"list"=>$plugins
		));
		if(count($plugins)<1){
			 DisplayPlugin::hide("have_categories");
		}
	}
}

class PluginCategoryList extends HTMLList{

	public function populateItem($arg,$key,$count){
		$targetId = "category-".$key;

		$this->createAdd("category_name","HTMLLink",array(
			"text"=>$key,
			"link" => "#".$targetId,
		));

		$this->createAdd("plugin_category_delete_link","HTMLActionLink",array(
			"link" => SOY2PageController::createLink("Plugin.DeleteCategory")."?category_name=".rawurldecode($key),
			"visible" => !in_array($key, array(
				CMSMessageManager::get("SOYCMS_NO_CATEGORY"),
				CMSMessageManager::get("SOYCMS_ACTIVE_PLUGINS"),
				CMSMessageManager::get("SOYCMS_NOT_ACTIVE_PLUGINS"),
			))
		));

		$this->createAdd("plugin_list","PluginList",array(
			"list" => $arg
		));

		$this->createAdd("has_plugin","HTMLModel",array(
				"visible" => (count($arg)),
				"attr:id" => $targetId,
		));
		$this->createAdd("no_plugin","HTMLModel",array(
				"visible" => !(count($arg))
		));
	}
}

class CategoryLinkList extends HTMLList{

	public function populateItem($arg,$key){
		$this->createAdd("plugin_category_link","HTMLLink",array(
			"text"=>$key,
			"link"=>SOY2PageController::createLink("Plugin")."?category=".rawurldecode($key)
		));
	}
}

class PluginList extends HTMLList{

	public function populateItem($plugin,$key,$counter){

		$this->createAdd("plugin_name","HTMLLabel",array(
			"text" => $plugin->getName(),
		));

		$this->createAdd("config_link","HTMLLink",array(
			"link" => SOY2PageController::createLink("Plugin.Config") ."?".$plugin->getId()
		));

		$this->createAdd("plugin_icon","HTMLImage",array(
			"src"=>$plugin->getIcon()
		));

		$this->createAdd("plugin_box","HTMLModel",array(
			"style" => (($counter%2)==0) ? "background-color:#F4F9FE" : ""
		));

	}

}
