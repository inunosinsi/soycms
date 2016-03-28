<?php

class IndexPage extends CMSWebPageBase{
	
	function IndexPage($arg){
		WebPage::WebPage();
		
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
	
	function populateItem($arg,$key){
		$this->createAdd("category_name","HTMLLabel",array(
			"text"=>$key
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
		
		$this->createAdd("no_plugin","HTMLModel",array(
			"visible" => (count($arg)<1)
		));
		
	}
	
}

class CategoryLinkList extends HTMLList{
	
	function populateItem($arg,$key){
		$this->createAdd("plugin_category_link","HTMLLink",array(
			"text"=>$key,
			"link"=>SOY2PageController::createLink("Plugin")."?category=".rawurldecode($key)
		));
		
	}
	
}
class PluginList extends HTMLList{
	
	var $counter = 0;
	
	function populateItem($plugin){
		
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
			"style" => (($this->counter%2)==0) ? "background-color:#F4F9FE" : ""
		));
		
		$this->counter++;
		
	}
	
}

?>