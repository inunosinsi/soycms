<?php

class SOYShopPluginLogic extends SOY2LogicBase{

	private $dao;

	function __construct(){
		$this->dao = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");
	}

	/**
	 * prepare コンストラクタに任せて、prepareでは何もしない
	 */
	function prepare(){}
	
	/**
	 * type指定なしですべて
	 *
	 * @return array
	 */
	function getInstalledModules($type = null){
		$installed = array();
		if($type == null){
			$modulelist = $this->dao->get();
		}else{
			$modulelist = $this->dao->getByType($type);
		}

		foreach($modulelist as $module){
			if($module->getIsActive())
				$installed[] = $module;
		}
		return $installed;
	}
	
	/**
	 * type指定してモジュールリストを取得する
	 * type指定なしですべて
	 */
	function getModulesByType($type = null){
		if(isset($type)){
			$modulelist = $this->dao->getByType($type);
		}else{
			$modulelist = $this->dao->get();
		}

		return $modulelist;
	}
	
	/**
	 * DBに登録されているモジュールを検索
	 */
	function searchModules(){
		//この段階で初めてiniファイルを検索する。
		$this->checkNewIni();
		try{
			return $this->dao->get();
		}catch(Exception $e){
			return array();
		}
	}

	/**
	 * インストール実行
	 */
	function installModule($id){
		try{
			if(is_numeric($id)){
				$module = $this->dao->getById($id);
			}else{
				$module = $this->dao->getByPluginId($id);
			}
			$module->setIsActive(1);
			$this->dao->update($module);
		}catch(Exception $e){
			return;
		}

		SOYShopPlugin::load("soyshop.plugin.install",$module);
		SOYShopPlugin::invoke("soyshop.plugin.install", array(
			"mode" => "install"
		));
	}


	/**
	 * アンインストール
	 */
	function uninstallModule($id){
		try{
			if(is_numeric($id)){
				$module = $this->dao->getById($id);
			}else{
				$module = $this->dao->getByPluginId($id);
			}
			$module->setIsActive(0);
			$this->dao->update($module);
		}catch(Exception $e){
			return;
		}
			

		SOYShopPlugin::load("soyshop.plugin.install",$module);
		SOYShopPlugin::invoke("soyshop.plugin.install", array(
			"mode" => "uninstall"
		));
	}
	
	/**
	 * plugin.iniに記載されている内容に基づいて、
	 */
	function initModuleByPluginIni(){
		if(file_exists(SOY2::RootDir() . "logic/init/plugin/plugin.ini")){
			$pluginDao = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");
			try{
				$list = $pluginDao->getActiveModules();
			}catch(Exception $e){
				$list = array();
			}
			
			if(count($list)){
				foreach($list as $module){
					$this->uninstallModule($module->getPluginId());
				}
			}
			
			$list = $this->readModuleFile();
			foreach($list as $moduleId){
				$this->installModule(trim($moduleId));
			}
		}
	}
	
	
	function readModuleFile(){
		if(file_exists(SOY2::RootDir() . "logic/init/plugin/plugin.ini")){
			$pluginListFile = SOY2::RootDir() . "logic/init/plugin/plugin.ini";
		}else{
			$pluginListFile = SOY2::RootDir() . "logic/init/plugin/plugin.default.ini";
		}
		
		return explode("\n", file_get_contents($pluginListFile));
	}
	
	/**
	 * iniファイルを探してdbに登録
	 */
	function checkNewIni() {
		$scandir = SOYSHOP_SITE_DIRECTORY . ".plugins/";
		$this->checkNewIniImpl($scandir);
		
		$scandir = SOYSHOP_MODULE_DIR . "features/";
		$this->checkNewIniImpl($scandir);
	}
		
	function checkNewIniImpl($scandir){
		
		$modulelist = array();
		$inidata = array();
		
		if(!is_dir($scandir))return;
		$files = scandir($scandir);

		foreach($files as $file){
			if($file[0] == ".")continue;
			if(is_dir($scandir.$file)) {
				$inifile = $scandir.$file."/module.ini";
				if(file_exists(($inifile = $scandir.$file."/module.ini"))) {
					$ini = parse_ini_file($inifile);
					$pluginId = $file;
					try{
						$this->dao->getByPluginId($pluginId);

					/* もしデータベースに存在しなければiniを見つけ次第dbに登録*/
					}catch(Exception $e){
						$module = new SOYShop_PluginConfig();
						$module->setPluginId($pluginId);
						$module->setType(@$ini["type"]);
						$module->setIsActive(0);

						/*データベースに追加*/
						try{
							$this->dao->insert($module);
						}catch(Exception $e){
							//
						}
						
	  				}
	  			}
			}
		}
	}
}
?>