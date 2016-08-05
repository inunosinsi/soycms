<?php

class TemplateCreateLogic extends SOY2LogicBase{
	
	private $dir;
	
	function __construct(){
		$this->dir = SOYSHOP_SITE_DIRECTORY . ".template/";
	}
	
	function copyTemplates($mode, $appId){
		//コピー先のディレクトリ
		$dustDir = $this->dir . $mode . "/" . $appId . "/";
		
		//すでにファイルがある場合は処理を終了
		if(file_exists($dustDir) && is_dir($dustDir)) return;
		
		$appDir = SOY2::rootDir() . $mode . "/" . $appId . "/pages/";
		
		//システム箇所が存在していない場合は処理を終了
		if(!file_exists($appDir)) return;
		
		//コピー用のディレクトリを作成
		mkdir($dustDir);
		
		self::copyFilesRecursive($appDir, $dustDir);
	}
	
	private function copyFilesRecursive($dir, $dustDir){
		if(is_dir($dir) && is_readable($dir)){
			$files = scandir($dir);
			foreach($files as $file){
				if($file[0] == ".") continue;
				
				//ディレクトリ
				if(is_dir($dir . $file)){
					mkdir($dustDir . $file);
					self::copyFilesRecursive($dir . $file . "/", $dustDir . $file . "/");
				//ファイル
				}else{
					copy($dir . $file, $dustDir . $file);
				}
			}
		}
	}
}
?>