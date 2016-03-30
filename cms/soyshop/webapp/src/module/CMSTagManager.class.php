<?php

class CMSTagManager{

	const TAG_DIR = "tag";
	const INI_DIR = "_ini";

	private $templateType;
	private $iniDir;
	private $tagDir;
	
	function CMSTagManager(){}
	
	static function &getInstance(){
	 	static $_instance;
		
		if(!$_instance){
			$_instance = new CMSTagManager;
			
			if(is_null($_instance->tagDir)){
				$_instance->tagDir = dirname(__FILE__) . "/" . self::TAG_DIR . "/";
				$_instance->iniDir = $_instance->tagDir . self::INI_DIR . "/";
			}
		}
		
		return $_instance;
	}
	
	static function addTemplateType($dirName){
		$instance = &self::getInstance();
		$instance->templateType = $dirName;
	}
	
	static function get(){
		$instance = &self::getInstance();
		$tags = self::readIniFile($instance->iniDir . $instance->templateType . ".ini");
		
		$tagArray = array();
			
		foreach($tags as $tag){
			$values = explode(".", $tag);
			switch($values[0]){
				case "block":
					$tagArray[] = self::getBlockTag($instance->tagDir, $values);					
					break;
				case "module":
					$tagArray[] = self::getModuleTag($instance->tagDir, $values);
					break;
				case "plugin":
					break;
			}			
		}
		
		return $tagArray;
	}
	
	private static function readIniFile($iniFile){
		$tags = array();
		if(!file_exists($iniFile)) return $tags;
		
		$hFile = fopen($iniFile, "r");
		while($line = fgets($hFile, 1024)){
			//コメントの除去
			$line = preg_replace('/(\/\/.*)$/i','',$line);
	   			
			//空白行ならばスキップ
			if(trim($line) == ""){
	  			continue;
	   		}
	    			
	   		$tags[] = trim($line);
		}
		fclose($hFile);
		
		return $tags;
	}
	
	private static function getBlockTag($tagDir, $values){
		$path = $tagDir . implode("/", $values);
		
		if(file_exists($path . ".csv")){
			$tag = file_get_contents($path . ".csv");
		//itemとitem_listはどちらもitem.csvを読むことにする
		}elseif(file_exists(str_replace("_list", "", $path) . ".csv")){
			$tag = file_get_contents(str_replace("_list", "", $path) . ".csv");
		}else{
			$tag = null;
		}
		
		if(file_exists($path . ".sample.txt")){
			$sample = file_get_contents($path . ".sample.txt");
		}else{
			$sample = null;
		}
		
		if(isset($values[1]) && $values[1] != "free"){
			$id = "block:id=\"" . $values[1] . "\"";
		}else{
			$id = "フリーページ(block:id不要)";
		}
		
		return array("tag" => $tag, "sample" => $sample, "id" => $id);
	}
	
	private static function getModuleTag($tagDir, $values){
		$path = $tagDir . implode("/", $values);
		
		if(file_exists($path . ".csv")){
			$tag = file_get_contents($path . ".csv");
		//itemとitem_listはどちらもitem.csvを読むことにする
		}elseif(file_exists(str_replace("_list", "", $path) . ".csv")){
			$tag = file_get_contents(str_replace("_list", "", $path) . ".csv");
		}else{
			$tag = null;
		}
		
		if(file_exists($path . ".sample.txt")){
			$sample = file_get_contents($path . ".sample.txt");
		}else{
			$sample = null;
		}
		
		array_shift($values);
		$id = "shop:module=\"" . implode(".", $values) . "\"";
		
		return array("tag" => $tag, "sample" => $sample, "id" => $id);
	}	
}
?>