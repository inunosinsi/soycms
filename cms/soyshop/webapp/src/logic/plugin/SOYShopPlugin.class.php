<?php
include(dirname(__FILE__) ."/SOY2Plugin.php");

class SOYShopPlugin extends SOY2Plugin{

	private static $active = array();

	public static function load($extensionId = null,$module = null){
		static $loaded = array();
		
		if(in_array(array($extensionId, $module), $loaded)){
			return;
		}else{
			$loaded[] = array($extensionId, $module);
		}
		
		if($extensionId){
			self::active($extensionId);
		}
		
		self::prepare();
		
		if(!$module){
			$modulelist = self::getActiveModules();
			
    		foreach($modulelist as $module){
    			if($module->getIsActive()){
    				$module->load($extensionId);
    			}
    		}

		}else{
			$module->load($extensionId);
		}
	}

	public static function active($id){
		SOYShopPlugin::$active[] = $id;

	}

	public static function prepare(){
		$actives = array_unique(SOYShopPlugin::$active);

		$tmp = array();

		foreach($actives as $tmpStr){
			$tmpStr = str_replace(".","\\.",$tmpStr);
			$tmpStr = str_replace("*",".*",$tmpStr);
			$tmp[] = "(^" . $tmpStr.'$)';
		}
		$match = "/" . implode("|",$tmp) . '/';

		//delegeterの読み込み
		$dir = dirname(__FILE__) . "/extensions/";
		$scandir = scandir($dir);
		foreach($scandir as $name){
			if($name[0] == ".")continue;

			//末尾の.phpを削る
			$id = substr($name,0,strlen($name)-4);

			//activeでないものは除く
			if(!preg_match($match,$id)){
				continue;
			}

			if(!is_dir($dir . $name)){
				include_once($dir . $name);
			}
		}
	}
	
	protected static function getActiveModules(){
		static $modulelist;
		
		if(!$modulelist){
			$dao = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");
			$modulelist = $dao->getActiveModules();
		}
		
		return $modulelist;
	}

}
?>