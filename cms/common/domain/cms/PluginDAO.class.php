<?php
/**
 * @entity cms.Plugin
 *
 */
class PluginDAO {

    function get(){
    	$pluginsArray = CMSPlugin::getPluginMenu();
    	$plugins = array();
    	foreach($pluginsArray as $key => $array){
    		$plugins[$key] = $this->getObject($key,$array);
    	}

    	return $plugins;
    }

    private static function getPluginDBName(){
    	return SOY2::RootDir()."db/plugin.db";
    }
    function getCategoryArray(){

    	$content = @file_get_contents(self::getPluginDBName());
    	if(strlen($content) == 0){
    		$content = array();
    	}else{
    		$content = unserialize($content);
    		if(!is_array($content)){
    			$content = array();
    		}
    	}

    	return $content;

    }

    function saveCategoryArray($array){
    	$content = serialize($array);
    	return file_put_contents(self::getPluginDBName(),$content);
    }

    function update(Plugin $plugin){

    	$content = $this->getCategoryArray();

    	foreach($content as $key => $ids){
    		foreach($ids as $index => $id){
    			if($id == $plugin->getId()){
    				unset($content[$key][$index]);
    			}
    		}
    	}



    	if(isset($content[$plugin->getCategory()])){
    		$content[$plugin->getCategory()][] = $plugin->getId();
    	}

    	$this->saveCategoryArray($content);

    	return ;
    }

    function addPluginCategory($label){
    	$category = $this->getCategoryArray();
    	if(isset($category[$label])){
    		return false;
    	}else{
    		$category[$label] = array();
    		$this->saveCategoryArray($category);
    		return true;
    	}
    }

    function deletePluginCategory($label){
    	$category = $this->getCategoryArray();
    	if(isset($category[$label])){
    		unset($category[$label]);
    		$this->saveCategoryArray($category);
    		return true;
    	}
    }

    function modifyPluginCategory($old,$new){
    	$category = $this->getCategoryArray();
    	if(!isset($category[$old])){
    		return false;
    	}else{
    		$tmp = $category[$old];
    		unset($category[$old]);
    		$category[$new] = $tmp;
    		return true;
    	}
    }


    function getActives(){
    	$plugins = $this->get();
    	$result = array();

    	foreach($plugins as $key =>$plugin){
    		if($plugin->isActive()){
    			$result[$plugin->getId()] = $plugin;
    		}
    	}
    	return $result;
    }

    function getNonActives(){
    	$plugins = $this->get();
    	$result = array();

    	foreach($plugins as $key =>$plugin){
    		if(!$plugin->isActive()){
    			$result[$plugin->getId()] = $plugin;
    		}
    	}
    	return $result;
    }

    function getCategorizedPlugins(){
    	$plugins = $this->get();
    	$categories = $this->getCategoryArray();

    	$non_categorized = array();
    	$result = array();

    	foreach($categories as $category => $plugin_ids ){
    		$result[$category] = array();

    		foreach($plugin_ids as $id){
    			if(isset($plugins[$id])){
    				if($plugins[$id]->isActive()){
    					$result[$category][$id] = $plugins[$id];
    				}
    				unset($plugins[$id]);
    			}
    		}
    	}

    	foreach($plugins as $key => $plugin){
    		if(!$plugin->isActive()){
    			unset($plugins[$key]);
    		}
    	}
    	if(!empty($plugins)){
    		$result[CMSMessageManager::get("SOYCMS_NO_CATEGORY")] = $plugins;
    	}
    	return $result;
    }

    private function getObject($id,$array){
    	$plugin = new Plugin();
    	@$plugin->setId($id);
    	@$plugin->setAuthor($array["author"]);
    	@$plugin->setName($array["name"]);
    	@$plugin->setDescription($array["description"]);
    	@$plugin->setUrl($array["url"]);
    	@$plugin->setMail($array["mail"]);
    	@$plugin->setVersion($array["version"]);
    	@$plugin->setConfig($array["config"]);
    	@$plugin->setCustom($array["custom"]);
    	@$plugin->setIsActive( (file_exists(CMSPlugin::getSiteDirectory().'/.plugin/'. $id .".active")? 1 :0) );
    	return $plugin;
    }

    function getById($id){
    	$pluginArray = CMSPlugin::getPluginMenu($id);

    	if(!$pluginArray)return;

    	return $this->getObject($id,$pluginArray);
    }

    function toggleActive($id){
    	$plugin = $this->getById($id);

    	if(!$plugin)return null;

    	if($plugin->isActive()){
    		unlink(CMSPlugin::getSiteDirectory().'/.plugin/'. $plugin->getId() .".active");
    	}else{
    		file_put_contents(CMSPlugin::getSiteDirectory().'/.plugin/'. $plugin->getId() .".active","active");
    	}

    	//プラグインのonDisable onActive関数の実行
    	if($plugin->isActive()){
    		CMSPlugin::callLocalPluginEventFunc('onDisable',$id);
    	}else{
    		CMSPlugin::callLocalPluginEventFunc('onActive',$id);
    	}
    	if(isset($event[$id])){
    		call_user_func($event[$id][0]);
    	}

    	//新しく切り替えたものを返す
    	return !$plugin->isActive();

    }
}
