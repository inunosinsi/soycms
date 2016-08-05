<?php

class CreatePageLogic extends SOY2LogicBase{
	
	private $pageDao;
	private $templateDir;
	private $templateTypes;
	private $pageConfDir;
	private $pageLogic;
	private $configs;
	
	function __construct(){
		$this->pageDao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
		$this->templateDir = SOYSHOP_SITE_DIRECTORY . ".template/";
		$this->templateTypes = SOYShop_Page::getTypeTexts();
		$this->pageConfDir = SOYSHOP_SITE_DIRECTORY . ".page/";
		$this->pageLogic = SOY2Logic::createInstance("logic.site.page.PageCreateLogic");
		$this->configs = self::getPrefixConfig();
	}
	
	function create(){
		
		//全テンプレートを確認して、テンプレートが存在しない場合はテンプレートを作成する
		self::copyTemplates();
		
		//全ページを確認して、ページが存在しない場合はページを作成する
		self::copyPages();
	}
	
	/**
	 * テンプレートのコピーを行う。一度でも処理が行われたらtrueを返す
	 */
	private function copyTemplates(){
		
		foreach($this->templateTypes as $tempType => $title){
			$dir = $this->templateDir . $tempType . "/";
			
			if(is_dir($dir) && is_readable($dir)){
				$files = scandir($dir);
				foreach($files as $int => $file){
					if($file[0] == "."){
						unset($files[$int]);
						continue;
					}
					if(preg_match('/(.*)\.html$/', $file, $tmp)){
						//iniファイルがあることも確認
						if(!isset($tmp[1]) || !file_exists($dir . $tmp[1] . ".ini")) {
							unset($files[$int]);
							continue;
						}
						
						//notfoundファイルを外しておく
						if($tmp[1] === "notfound"){
							unset($files[$int]);
							continue;
						}
						
						//PC用に作成したテンプレートのみを抽出
						foreach($this->configs as $config){
							if(strpos($tmp[1], $config) === 0){
								unset($files[$int]);
								break;
							}
						}
					//iniファイルは削っておく
					}elseif(preg_match('/(.*)\.ini$/', $file, $tmp)){
						unset($files[$int]);
						continue;
					}
				}
				
				//PC用のテンプレートのみを取得できたので、キャリアごとのテンプレートの作成を開始する
				if(count($files)){
					foreach($files as $filename){
						$filename = str_replace(".html", "", $filename);
						
						//すでにファイルが作成されていないか確認する
						$newFilename = $_GET["create"] . "_" . $filename;
						if(!file_exists($dir . $newFilename . ".html") && !file_exists($dir . $newFilename . ".ini")){
							//ファイルが無ければ、PC版のファイルをコピーする
							copy($dir . $filename . ".html", $dir . $newFilename . ".html");
							$iniText = file_get_contents($dir . $filename . ".ini");
							$iniText = str_replace("name = \"", "name = \"(" . $_GET["create"] . ")", $iniText);
							$iniText = str_replace("name= \"", "name= \"(" . $_GET["create"] . ")", $iniText);
							file_put_contents($dir . $newFilename . ".ini", $iniText);
						}
					}
				}
			}
		}
	}
	
	private function copyPages(){
		
		//PC用のページを確認する
		foreach($this->templateTypes as $tempType => $title){
			$pages = self::getPages($tempType);
			
			//PC用のページのみ取得する
			foreach($pages as $int => $page){
				
				foreach($this->configs as $config){
					//携帯自動振り分けプラグインと多言語化の同時使用分は無視
					if(strpos($config, "_")) continue;
										
					//URIの頭にprefixがついているページ、もしくはURIが_notfoundのページを削除
					if(strpos($page->getUri(), $config . "/") === 0 || $page->getUri() === $config || $page->getUri() === "_404_not_found"){
						unset($pages[$int]);
						continue;
					}
				}
			}
			
			//PC版のページのみになったところで改めてページのコピーを行う
			foreach($pages as $page){
				
				//_homeの場合はprefixのみのURIとする
				if($page->getUri() == "_home"){
					$newUri = $_GET["create"];
				}else{
					$newUri = $_GET["create"] . "/" . $page->getUri();
				}
				
				if(!self::checkPageExist($newUri)){
					$newPage = new SOYShop_Page();
					$newPage->setName($page->getName() . "(" . $_GET["create"] . ")");
					$newPage->setUri($newUri);
					$newPage->setType($page->getType());
					
					//念の為に新しいテンプレートが存在しているか確認する
					$newTemplate = $_GET["create"] . "_" . $page->getTemplate();
					if(!file_exists($this->templateDir . $page->getType() . "/" . $newTemplate)){
						$newTemplate = $page->getTemplate();
					}
					
					$newPage->setTemplate($newTemplate);
					$newPage->setConfig($page->getConfig());
					$newPage->setObject($page->getObject());
					
					$this->pageLogic->create($newPage);
					
					//実行した後、クラスファイルのconfをコピーする
					$confFileName = self::convertClassFileName($page->getUri());
					if(file_exists($this->pageConfDir . $confFileName . ".conf")){
						$newConfFileName = self::convertClassFileName($newPage->getUri());
						copy($this->pageConfDir . $confFileName . ".conf", $this->pageConfDir . $newConfFileName . ".conf");
					}
				}
			}
		}
	}
	
	private function convertClassFileName($uri){
		$file = str_replace(".", "_", $uri);
		return str_replace("/", "_", $file) . "_page";
	}
	
	private function getPrefixConfig(){
		//テンプレートのタイプによって振り分け
		$configs = array();
		
		//多言語化サイトプラグイン
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
		$multiLangConfig = UtilMultiLanguageUtil::getConfig();
		
		foreach($multiLangConfig as $key => $values){
			if(isset($values["prefix"]) && strlen($values["prefix"])){
				$configs[$key] = $values["prefix"];
			}
		}
		
		//携帯自動振り分けプラグイン分
		SOY2::import("module.plugins.util_mobile_check.util.UtilMobileCheckUtil");
		$mobileCheckConfig = UtilMobileCheckUtil::getConfig();
		
		if(isset($mobileCheckConfig["prefix"]) && strlen($mobileCheckConfig["prefix"])){
			//念の為モバイルとスマホのプレフィックスが異なるか確認しておく
			if($mobileCheckConfig["prefix"] != $mobileCheckConfig["prefix_i"]){
				$configs["m"] = $mobileCheckConfig["prefix"];
			}
		}
		
		if(isset($mobileCheckConfig["prefix_i"]) && strlen($mobileCheckConfig["prefix_i"])){
			$configs["i"] = $mobileCheckConfig["prefix_i"];
		}
		
		//多言語化サイトと併用
		if(isset($configs["i"])){
			foreach($multiLangConfig as $key => $values){
				if(isset($values["prefix"]) && strlen($values["prefix"])){
					$configs[$configs["i"] . "_" . $key] = $configs["i"] . "_" . $values["prefix"];
				}
			}
		}
		
		if(count($configs)) krsort($configs);
		
		return $configs;
	}
	
	private function getPages($pageType){
		try{
			return $this->pageDao->getByType($pageType);
		}catch(Exception $e){
			return array();
		}
	}
	
	/**
	 * 指定したURLのページが存在しているかチェックする。存在していればtrueを返す
	 * @param string uri
	 * @return boolean
	 */
	private function checkPageExist($uri){
		try{
			$page = $this->pageDao->getByUri($uri);
		}catch(Exception $e){
			return false;
		}
		
		return (!is_null($page->getId()));
	}
}
?>