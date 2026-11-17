<?php
SyncTemplatePlugin::registerPlugin();

class SyncTemplatePlugin{

	const PLUGIN_ID = "sync_template";

	private $output_date = "-";
	private $sync_date = "-";
	private $targetDir = "";
	private $autoImport = false;
	private $convertURL = false;
	private $ignoreTimestamp = false;
	//拡張子を.phpにするかどうか
	private $useExtPhpIfPhpAllowed = false;
	//出力ファイル名の先頭のIDの0埋め
	private $zeroPaddingWidth = 0;

	const EXT_HTML = ".html";
	const EXT_PHP  = ".php";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"テンプレート同期プラグイン",
			"type" => Plugin::TYPE_PAGE,
			"description"=>'ページ（ブログ）テンプレートを実ファイルと同期させます。<br />SOY CMSに格納されているテンプレートをファイルに書き出したり、<br />書き出したファイルをSOY CMSに格納したりすることが可能です。',
			"author"=>"株式会社株式会社Brassica",
			"modifier"=>"Jun Okada",
			"url"=>"https://brassica.jp/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.5.1"
		));

		SOY2::import("site_include.plugin.sync_template.util.SyncTemplateUtil");

		CMSPlugin::addPluginConfigPage($this->getId(),array(
			$this,"config_page"
		));

		if($this->autoImport){
			$this->auto_import();
		}

	}

	function config_page($message){
		SyncTemplateUtil::checkDirectory();

		//export
		if(@$_POST["export"]){
			$res = SyncTemplateUtil::makeDirectory();
			if(!$res){
				return '<p>出力先のディレクトリを作成することが出来ません。</p>';
			}
			$this->export();
			exit;
		}

		//import
		if(@$_POST["import"]){
			$this->import();
			CMSPlugin::redirectConfigPage();
			exit;
		}
		
		//config
		if(@$_POST["delete"]){
			SyncTemplateUtil::delete();
			if(@$_POST["delete_dir"]){
				SyncTemplateUtil::deleteDirectory();
			}
			CMSPlugin::redirectConfigPage();
			exit;
		}

		if($this->autoImport){
			if($this->auto_import()){
				CMSPlugin::redirectConfigPage();
			}
		}

		if(isset($_POST["ignoreTimestamp"])){
			$this->ignoreTimestamp = true;
		}

		$html = '<style type="text/css">'.file_get_contents(dirname(__FILE__)."/style.css").'</style>';

		ob_start();
		include_once(dirname(__FILE__)."/config.php");
		$html .= ob_get_contents();
		ob_clean();

		$html.= '<p class="export_time_head">書き出しディレクトリ</p><p class="export_time_body">'.$this->targetDir.'</p><br style="clear:both" />';
		$html.= '<p class="export_time_head">最終出力時刻</p><p class="export_time_body">' . (is_numeric($this->output_date) ? date("Y-m-d H:i:s",$this->output_date) : "-") . "</p>";
		$html.= '<p class="export_time_head">最終同期時刻</p><p class="export_time_body">' . (is_numeric($this->sync_date) ? date("Y-m-d H:i:s",$this->sync_date) : "-") . "</p>";
		$html.= '<br style="clear:both"/>';

		$html .= file_get_contents(dirname(__FILE__)."/description.html");

		return $html;
	}

	function export(){
		$convert = $this->convertURL;
		$this->exportTemplates($convert);
		$this->output_date = time();
		CMSPlugin::savePluginConfig(self::PLUGIN_ID,$this);
		CMSPlugin::redirectConfigPage();
	}

	function import(){
		$convert = $this->convertURL;
		$imports = @$_POST["imports"];

		if(class_exists("SOY2Debug")) SOY2Debug::trace($_POST);

//		if(empty($imports)){
//			$imports = scandir(SyncTemplateUtil::getSiteDirectory().SyncTemplateUtil::TARGET_DIR);
//		}

		$this->importTemplates($imports,$convert);
		$this->sync_date = time();
		CMSPlugin::savePluginConfig(self::PLUGIN_ID,$this);
		CMSPlugin::redirectConfigPage();
	}

	function auto_import(){
		$targetFiles = $this->getModifiedFiles();

		if(count($targetFiles) >0){
			$this->importTemplates($targetFiles,$this->convertURL);
			$this->sync_date = time();
			CMSPlugin::savePluginConfig(self::PLUGIN_ID,$this);
			return true;
		}
	}

	function exportTemplates(bool $convert=false){
		 $targetDir = SyncTemplateUtil::targetDirectory();

		 $siteConfig = soycms_get_site_config_object();

		 $pageDao = SOY2DAOFactory::create("cms.PageDAO");
		 $pages = $pageDao->get();

		 foreach($pages as $page){
		 	//TOPページ
		 	if(strlen($page->getUri())<1){
		 		$page->setUri("/");
		 	}

			//ID
			$pageId = $page->getId();
			if($this->zeroPaddingWidth > 0){
				$pageId = str_pad($pageId, $this->zeroPaddingWidth, "0", STR_PAD_LEFT);
			}

			//ファイル名（拡張子なし）
		 	$url = $targetDir ."/" . $pageId . "_" . preg_replace("/[\/\.]/","_",$page->getUri());

		 	if($page->getPageType() == Page::PAGE_TYPE_BLOG){
			 	//ブログページ
		 		$template = unserialize($page->getTemplate());
		 		foreach($template as $key => $value){
		 			$this->exportTemplate($url . "_" . $key, $value, $convert, $siteConfig);
		 		}
		 	}else{
				//その他
			 	$template = $page->getTemplate();
			 	$this->exportTemplate($url, $template, $convert, $siteConfig);
		 	}
		 }

	}

	/**
	 * @param String filepath 出力先のファイル名（絶対パス）
	 * @param String template
	 * @param Boolean convert
	 * @param SiteConfig config
	 */
	function exportTemplate($filepath,$template,$convert,$config){

		//拡張子
		if($this->useExtPhpIfPhpAllowed){
			$filepath .= self::EXT_PHP;//.php
		}else{
			$filepath .= self::EXT_HTML;//.html
		}


		//URLの書き換え
		if($convert){
			$siteId = UserInfoUtil::getSite()->getSiteId();
			$url = UserInfoUtil::getSiteURL();

			//URLの変換: /siteId => http://example.com/siteId
			$regex = '/(href|src)="\/?'.$siteId.'\/(.*?)"/i';
			if(class_exsits("SOY2Debug")) SOY2Debug::trace($regex);
			$template = preg_replace($regex,'$1="'.$url.'$2"',$template);
		}

		//文字コードの変換
		$template = $config->convertToSiteCharset($template);

		@file_put_contents($filepath, $template);
		@chmod($filepath,0666);
	}

	function importTemplates($imports,$convert = false){

		$targetDir = SyncTemplateUtil::targetDirectory();
		$pageDao = soycms_get_hash_table_dao("page");
		$blogPageDao = soycms_get_hash_table_dao("blog_page");

		$config = soycms_get_site_config_object();

		$output_date = max($this->output_date,$this->sync_date);

		//ファイルの更新日時にかかわらずすべてインポートする
		foreach($imports as $import){
			if($import[0] == ".")continue;
			$filepath = $targetDir ."/" . $import;

			//先頭の数字がID
			$id = (int)preg_replace("/^([0-9]+).*/",'$1',$import);

			try{
				$page = $pageDao->getById($id);
			}catch(Exception $e){
				continue;
			}

			if($page->getPageType() == Page::PAGE_TYPE_BLOG){
				$key = preg_replace('/^.*_([a-zA-Z]+)\.(html|php)$/','$1',$import);
				$page = SOY2::cast("BlogPage",$page);
				$this->importBlogTemplate($page,$key,$filepath,$convert,$config);
			}else{
				$this->importTemplate($page,$filepath,$convert,$config);
			}
		}
	}

	function importTemplate($page,$filepath,$convert,$config){
		$template = file_get_contents($filepath);

		if($convert){
			$siteId = UserInfoUtil::getSite()->getSiteId();
			$url = UserInfoUtil::getSiteURL();

			//URLの変換
			$regex = '/(href|src)="'.str_replace("/","\/",$url).'(.*?)"/i';
			$template = preg_replace($regex,'$1="/'.$siteId.'/$2"',$template);
		}

		$template = $config->convertFromSiteCharset($template);

		$page->setTemplate($template);
		soycms_get_hash_table_dao("page")->update($page);
	}

	function importBlogTemplate($page,$key,$filepath,$convert,$config){

		$template = file_get_contents($filepath);

		if($convert){
			$siteId = UserInfoUtil::getSite()->getSiteId();
			$url = UserInfoUtil::getSiteURL();

			//URLの変換
			$regex = '/(href|src)="'.str_replace("/","\/",$url).'(.*?)"/i';
			$template = preg_replace($regex,'$1="/'.$siteId.'/$2"',$template);
		}

		$template = $config->convertFromSiteCharset($template);

		$old_template = $page->_getTemplate();
		$old_template[$key] = $template;
//		foreach($old_template as $key => $value){
//			if(!in_array($key,array(BlogPage::TEMPLATE_ARCHIVE,
//				BlogPage::TEMPLATE_TOP,
//				BlogPage::TEMPLATE_ENTRY,
//				BlogPage::TEMPLATE_POPUP))){
//				unset($old_template[$key]);
//			}
//		}
		$page->setTemplate(serialize($old_template));
		soycms_get_hash_table_dao("blog_page")->update($page);
	}

	/**
	 * 変更のあったファイルを配列にして返す
	 */
	function getModifiedFiles(){
		$targetDir = SyncTemplateUtil::targetDirectory();
		$templates = array();
		$dupulicatedIds = array();//IDの重複回数
		$output_date = max($this->output_date,$this->sync_date);

		// ブログのテンプレートの種類によって並び替える
		$driftByTypeAndExt = array(
			"_top"    .self::EXT_HTML => 60,
			"_top"    .self::EXT_PHP  => 61,
			"_archive".self::EXT_HTML => 40,
			"_archive".self::EXT_PHP  => 41,
			"_entry"  .self::EXT_HTML => 20,
			"_entry"  .self::EXT_PHP  => 21,
		);

		if(is_dir($targetDir)){
			$files = scandir($targetDir);

			foreach($files as $file){
				if($file[0] == ".")continue;

				/*
				 * exportしたファイルのみを対象とする
				 */
				$matches = array();
				if(!preg_match("/^([0-9]+)_/",$file,$matches))continue;

				//並び替えのための数値
				if(is_numeric($matches[1])){
					$id = $matches[1]*100;
				}else{
					//念のため
					$id = 10000000;
				}

				/*
				 * 前回出力時以降に更新されたファイルを対象にする
				 * 更新日時を無視するときはすべてのファイルが対象になる
				 */
				if($output_date < filemtime($targetDir . "/" . $file) || $this->ignoreTimestamp){
					/* ブログなど同じIDのファイルがあった場合に対応 */

					// ブログのテンプレートの種類によって順番を変える
					if(strpos($file,"_top.") !== false || strpos($file,"_archive") !== false || strpos($file,"_entry.") !== false){
						foreach($driftByTypeAndExt as $tail => $drift){
							if(strrpos($file,$tail) === strlen($file) - strlen($tail)){
								$id -= $drift;
								break;
							}
						}
					}

					//原因不明の重複IDファイル
					if(isset($templates[$id])){
						$dupulicatedIds[$id]++;
						$id += $dupulicatedIds[$id];
					}else{
						$dupulicatedIds[$id] = 0;
					}

					$templates[$id] = $file;
				}
			}

			ksort($templates);
		}

		return $templates;
	}

	function getConvertURL(){
		return $this->convertURL;
	}
	function setConvertURL($convertURL){
		$this->convertURL = $convertURL;
	}

	function getAutoImport(){
		return $this->autoImport;
	}
	function setAutoImport($autoImport){
		$this->autoImport = $autoImport;
	}
	
	function getUseExtPhpIfPhpAllowed(){
		return $this->useExtPhpIfPhpAllowed;
	}
	function setUseExtPhpIfPhpAllowed($useExtPhpIfPhpAllowed){
		$this->useExtPhpIfPhpAllowed = $useExtPhpIfPhpAllowed;
	}

	function getZeroPaddingWidth(){
		return $this->zeroPaddingWidth;
	}
	function setZeroPaddingWidth($zeroPaddingWidth){
		$this->zeroPaddingWidth = $zeroPaddingWidth;
	}

	public static function registerPlugin(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new SyncTemplatePlugin();
			$obj->useExtPhpIfPhpAllowed = defined("SOYCMS_ALLOW_PHP_SCRIPT") && SOYCMS_ALLOW_PHP_SCRIPT;
			$obj->zeroPaddingWidth = 2;
		}
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
