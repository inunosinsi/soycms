<?php
/*
 * 記事同期プラグイン
 */

define('$this->getId()',"sync_template");

class SyncEntryPlugin{

	const PLUGIN_ID = "sync_entry";

	var $output_date = "-";
	var $output_time = "-";
	var $sync_date = "-";
	var $sync_time = "-";
	var $targetDir = "entries";
	//自動インポート
	var $autoImport = false;
	//出力ファイル名の先頭のIDの0埋め
	var $zeroPaddingWidth = 0;
	//出力ファイルの属性（文字列）
	var $fileMode = "0666";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"記事同期プラグイン",
			"description"=>'記事を実ファイルと同期させます。<br />SOY CMSに格納されている記事をファイルに書き出したり、<br />書き出したファイルをSOY CMSに格納したりすることが可能です。',
			"author"=>"株式会社日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"0.0.2"
		));

		CMSPlugin::addPluginConfigPage($this->getId(),array(
			$this,"config_page"
		));

		if($this->autoImport){
			$this->autoImport();
		}

	}

	function config_page($message){

		$res = $this->checkDir();
		if(!$res){
			return '<p>出力先のディレクトリを作成することが出来ません。</p>';
		}

		//export
		if(@$_POST["export"]){
			$this->export();
			exit;
		}

		//import
		if(@$_POST["import"]){
			$this->import();
			exit;
		}

		if($this->autoImport){
			if($this->autoImport()){
				CMSPlugin::redirectConfigPage();
			}
		}

		//delete
		if(@$_POST["delete"]){
			$this->delete();
			if(@$_POST["delete_dir"]){
				$this->deleteDir();
			}
			CMSPlugin::redirectConfigPage();
			exit;
		}

		//save
		if(isset($_POST["save"])){
			$this->setTargetDir($_POST["targetDir"]);
			if($this->checkDir()){
				//ディレクトリに問題がなければ他も入れて保存
				$this->setConfig($_POST["config"]);
				CMSPlugin::savePluginConfig($this->getId(),$this);
				CMSPlugin::redirectConfigPage();
			}else{
				echo "<p class=\"error\">出力先のディレクトリを作成することが出来ません</p>";
			}
		}

		$html = '<style type="text/css">'.file_get_contents(dirname(__FILE__)."/style.css").'</style>';

		ob_start();
		include_once(dirname(__FILE__)."/config.php");
		$html .= ob_get_contents();
		ob_clean();

		$html.= '<p class="export_time_head">最終出力時刻</p><p class="export_time_body">' . (is_numeric($this->output_date) ? date("Y-m-d H:i:s",$this->output_date) : "-");
		$html.= '&nbsp;(' . (is_numeric($this->output_time) ? round($this->output_time,3) . " sec." : "-") . ")</p>";
		$html.= '<p class="export_time_head">最終同期時刻</p><p class="export_time_body">' . (is_numeric($this->sync_date) ? date("Y-m-d H:i:s",$this->sync_date) : "-");
		$html.= '&nbsp;(' . (is_numeric($this->sync_time) ? round($this->sync_time,3) . " sec." : "-") . ")</p>";
		$html.= '<br style="clear:both"/>';

		$html .= file_get_contents(dirname(__FILE__)."/description.html");

		return $html;
	}

	function checkDir(){
		$targetDir = $this->getTargetDir(true);

		if(!file_exists($targetDir)){
			$res = mkdir($targetDir);
			if($res){
				chmod($targetDir, 0777);
				if(is_writable($targetDir)){
					file_put_contents($targetDir."/.htaccess", "Deny from all");
					$res = true;
				}else{
					$res = false;
				}
			}
		}else{
			if(is_dir($targetDir) && is_writable($targetDir)){
				$res = true;
			}else{
				$res = false;
			}
		}

		return $res;

	}

	/**
	 * 出力
	 */
	function export(){

		$start = microtime(true);


		if(isset($_POST["all_entries"]) && $_POST["all_entries"]){
			//全部
			$this->exportEntries(null);
		}elseif(isset($_POST["label"]) && is_array($_POST["label"])){
			//複数ラベル
			foreach($_POST["label"] as $labelId){
				$this->exportEntries((int)$labelId);
			}
		}elseif(isset($_POST["label"]) && strlen($_POST["label"])){
			//単独ラベル
			$this->exportEntries((int)$_POST["label"]);
		}

		$this->output_time = microtime(true) - $start;

		$this->output_date = time();
		CMSPlugin::savePluginConfig($this->getId(),$this);
		CMSPlugin::redirectConfigPage();
	}

	/**
	 * 入力
	 */
	function import(){
		$imports = @$_POST["imports"];

		$start = microtime(true);

		$this->importEntries($imports);

		$this->sync_time = microtime(true) - $start;

		$this->sync_date = time();
		CMSPlugin::savePluginConfig($this->getId(),$this);
		CMSPlugin::redirectConfigPage();
	}

	/**
	 * 変更されたファイルがあればそれをインポートしてしまう。
	 */
	function autoImport(){
		$imports = $this->getModifiedFiles();
		if(count($imports) >0){
			$start = microtime(true);
			$this->importEntries($imports);
			$this->sync_time = microtime(true) - $start;
			$this->sync_date = time();
			CMSPlugin::savePluginConfig($this->getId(),$this);
			return true;
		}
	}

	function getModifiedFiles(){
		$targetDir = $this->getTargetDir(true);
		$template_modified = array();
		$output_date = max($this->output_date,$this->sync_date);
		$target_file = "";

		$files = (file_exists($targetDir) && is_dir($targetDir)) ? scandir($targetDir) : array();

		foreach($files as $file){
			if($file[0] == ".")continue;

			if($output_date < filemtime($targetDir . "/" . $file)){
				$template_modified[] = $file;
				continue;
			}
		}

		return $template_modified;
	}

	/**
	 * 指定されたラベルの付いた記事をファイルに書き出す
	 */
	function exportEntries($labelId){
		$targetDir = $this->getTargetDir(true);

		if($labelId){
			$res = SOY2ActionFactory::createInstance("Entry.EntryListAction",array(
				"id" => $labelId
			))->run();

			$entries = $res->getAttribute("Entities");
		}else{
			$entries = SOY2DAOFactory::create("cms.EntryDAO")->get();
		}

		foreach($entries as $entry){
			file_put_contents($targetDir . "/" . $this->padByZero($entry->getId())."_title.html",   $entry->getTitle());
			file_put_contents($targetDir . "/" . $this->padByZero($entry->getId())."_content.html", $entry->getContent());
			file_put_contents($targetDir . "/" . $this->padByZero($entry->getId())."_more.html",    $entry->getMore());

			//誰でも書き込み、読み込みできるようにする
			chmod($targetDir . "/" . $this->padByZero($entry->getId())."_title.html",   $this->getFileModeForChmod());
			chmod($targetDir . "/" . $this->padByZero($entry->getId())."_content.html", $this->getFileModeForChmod());
			chmod($targetDir . "/" . $this->padByZero($entry->getId())."_more.html",    $this->getFileModeForChmod());
		}

	}

	/**
	 * 文字列の先頭をzeroPaddingWidthで指定された桁数になるように0で埋める
	 */
	function padByZero($str){
		return str_pad($str, $this->zeroPaddingWidth, "0", STR_PAD_LEFT);
	}

	/**
	 * ファイルの配列を受け取って、ファイルの内容を記事に適用する
	 * @param Array(String)
	 */
	function importEntries($imports){

		$targetDir = $this->getTargetDir(true);

		$entryDAO = SOY2DAOFactory::create("cms.EntryDAO");

		foreach($imports as $import){
			if($import[0] == ".")continue;
			$filepath = $targetDir ."/" . $import;

			//最終出力より古いファイルはインポートしない
			if(filemtime($filepath) <= $this->output_date){
				continue;
			}

			//最終同期より古いファイルはインポートしない
			if($this->sync_date && (filemtime($filepath) <= $this->sync_date)){
				continue;
			}

			//ファイルからEntry.idを取得する
			$id = (int)preg_replace("/^([0-9]+).*/",'$1',$import);

			//おかしかったら飛ばす
			if(!strlen($id) || $id <1){
				continue;
			}

			try{
				//記事を取得
				$entry = $entryDAO->getById($id);

				//ファイルのタイプに応じてインポートする
				$isUpdated = false;
				switch(true){
					case preg_match('/_title/',$import):
						$entry->setTitle(file_get_contents($filepath));
						$isUpdated = true;
						break;
					case preg_match('/_content/',$import):
						$entry->setContent(file_get_contents($filepath));
						$isUpdated = true;
						break;
					case preg_match('/_more/',$import):
						$entry->setMore(file_get_contents($filepath));
						$isUpdated = true;
						break;
					default:
						//
				}

				//記事を更新
				//@TODO actionかlogicを使うべき
				if($isUpdated){
					$entryDAO->update($entry);
				}
			}catch(Exception $e){
				continue;
			}


		}

	}

	/**
	 * 書き出されているファイルを削除する
	 */
	function delete(){
		$export_dir = $this->getTargetDir(true);
		if(is_dir($export_dir)){
			foreach(scandir($export_dir) as $file){
				if($file[0] == ".")continue;
				unlink($export_dir."/".$file);
			}
		}
	}

	/**
	 * 出力ディレクトリを削除する
	 */
	function deleteDir(){
		$export_dir = $this->getTargetDir(true);
		@unlink($export_dir."/.htaccess");
		@rmdir($export_dir);
	}

	/**
	 * 出力ディレクトリが削除可能か
	 */
	function isDirDeletable(){
		$export_dir = $this->getTargetDir(true);
		$parent_dir = dirname($export_dir);
		return is_dir($export_dir) && is_writable($parent_dir) && is_executable($parent_dir);
	}

	/**
	 * 出力されているファイルの数
	 */
	function countExportedFiles(){
		$count = 0;
		$export_dir = $this->getTargetDir(true);
		if(is_dir($export_dir)){
			foreach(scandir($export_dir) as $file){
				if($file[0] == ".")continue;
				$count++;
			}
		}
		return $count;
	}

	/**
	 * 設定を保存
	 */
	function setConfig($settings){
		$this->setAutoImport($settings["autoImport"]);
		$this->zeroPaddingWidth = (int)$settings["zeroPaddingWidth"];
		$this->setFileMode($settings["fileMode"]);
	}


	public static function register(){

		$obj = CMSPlugin::loadPluginConfig(SyncEntryPlugin::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new SyncEntryPlugin();
		}

		CMSPlugin::addPlugin(SyncEntryPlugin::PLUGIN_ID,array($obj,"init"));

	}

	function getTargetDir($flag = false) {
		if($flag){
			if(defined("_SITE_ROOT_")){
				$siteDir = _SITE_ROOT_."/";
			}elseif(class_exists("UserInfoUtil")){
				$siteDir = UserInfoUtil::getSiteDirectory();
			}else{
				$siteDir = "";
			}

			return $siteDir.$this->targetDir;
		}
		return $this->targetDir;
	}
	function setTargetDir($targetDir) {
		$targetDir = str_replace(array("\\","/"),"_",$targetDir);
		$this->targetDir = $targetDir;
	}
	function setAutoImport($val) {
		$this->autoImport = $val ? true : false;
	}

	/**
	 * chmod用に8進数表現の文字列を整数として返す
	 * @return int
	 */
	function getFileModeForChmod(){
		return octdec($this->fileMode);
	}
	function getFileMode(){
		return $this->fileMode;
	}
	function setFileMode($val){
		$val = (string)$val;
		if(strlen($val) == 3){
			$val = "0".$val;
		}
		$this->fileMode = $val;
	}
}


SyncEntryPlugin::register();
?>
