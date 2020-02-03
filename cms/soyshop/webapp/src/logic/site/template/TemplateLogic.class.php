<?php

class TemplateLogic extends SOY2LogicBase{

	private $dir;
	private $files;
	private $mode;

	function __construct(){
		SOY2::import("domain.site.SOYShop_Page");
		$this->dir = SOYSHOP_SITE_DIRECTORY . ".template/";
	}

	/** テンプレート一覧周り **/
	function getTemplateList($types){

		$res = array();
		foreach($types as $key => $name){
			$dir = $this->dir . $key . "/";

			if(is_dir($dir) && is_readable($dir)){
				$files = scandir($dir);
				foreach($files as $file){
					if($file[0] == ".") continue;
					if(preg_match('/(.*)\.html$/', $file, $tmp)){

						if(is_readable($dir . $tmp[1] . ".ini")){
							$array = parse_ini_file($dir . $tmp[1] . ".ini");

							$res[] = array(
								"type" => $key,
								"file" => $file,
								"name" => (isset($array["name"]) && strlen($array["name"]) > 0) ? $array["name"] : $file,
							);
						}
					}
				}
			}
		}

		//仕分け cは端末や言語設定のこと
		$list = array();
		$configs = self::getPrefixConfig();
		if(count($configs)){
			foreach($configs as $prefix){
				$tempRes = array();
				foreach($res as $int => $temps){
					$flag = false;
					if(strpos($prefix, "/") === false){
						if(preg_match('/^' . $prefix . '_/', $temps["file"])) $flag = true;
					}else{
						$check = str_replace("/", "_", $prefix);
						if(preg_match('/^' . $check . '_/', $temps["file"])) $flag = true;
					}

					if($flag){
						$tempRes[$temps["type"]][] = array(
							"type" => $types[$temps["type"]],
							"path" => $temps["type"] . "/" . $temps["file"],
							"name" => $temps["name"]
						);
						unset($res[$int]);
					}
				}
				$list[$prefix] = $tempRes;
			}
			ksort($list);
		}

		//PC版
		$tempRes = array();
		foreach($res as $int => $temps){
			$tempRes[$temps["type"]][] = array(
				"type" => $types[$temps["type"]],
				"path" => $temps["type"] . "/" . $temps["file"],
				"name" => $temps["name"]
			);
			unset($res[$int]);
		}

		$results = array();
		$results["jp"] = $tempRes;

		//最後に並べ替え
		foreach($list as $p => $temps){
			$results[$p] = $temps;
		}

		return $results;
	}

	function getApplicationTemplateList($mode = "cart"){
		$templateDir = $this->dir . $mode . "/";
		if(!file_exists($templateDir)) return array();

		$files = scandir($templateDir);
		$res = array();
		foreach($files as $file){
			if($file[0] == ".") continue;
			if(preg_match('/(.*)\.html$/', $file, $tmp)){

				if(file_exists($templateDir . $tmp[1] . ".ini")){
					$array = parse_ini_file($templateDir . $tmp[1] . ".ini");

					$res[$file] = array(
						"path" => $mode . "/" . $file,
						"type" => $tmp[1],
						"name" => (isset($array["name"]) && strlen($array["name"]) > 0) ? $array["name"] : $file,
					);
				}

				//もう一階層
				if(file_exists($templateDir . $tmp[1] . "/")
					&& is_dir($templateDir . $tmp[1] . "/")){
					$appId = $tmp[1];
					$subDir = $templateDir . $tmp[1] . "/";
					$sub_files = scandir($subDir);


					foreach($sub_files as $file){
						if($file[0] == ".") continue;
						if(preg_match('/(.*)\.html$/', $file, $tmp)){
							if(file_exists($subDir . $tmp[1] . ".ini")){
								$array = parse_ini_file($subDir . $tmp[1] . ".ini");

								$res[$appId . "/" . $file] = array(
									"path" => $mode . "/" . $appId. "/" . $file,
									"type" => $appId . " (" . $tmp[1] . ")",
									"name" => (isset($array["name"]) && strlen($array["name"]) > 0) ? $array["name"] : $file,
								);
							}
						}
					}
				}
			}/* end loop */
		}

		return $res;
	}

	private function getPrefixConfig(){
		//テンプレートのタイプによって振り分け
		$configs = array();

		//多言語化サイトプラグインがアクティブの時
		if(SOYShopPluginUtil::checkIsActive("util_multi_language")){
			SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
			if(class_exists("UtilMultiLanguageUtil")){
				$multiLangConfig = UtilMultiLanguageUtil::getConfig();

				foreach($multiLangConfig as $key => $values){
					if(
						(isset($values["prefix"]) && strlen($values["prefix"])) &&
						(isset($values["is_use"]) && $values["is_use"] == UtilMultiLanguageUtil::IS_USE)
					){
						$configs[$key] = $values["prefix"];
					}
				}
			}
		}

		//携帯自動振り分けプラグインがアクティブの時
		if(SOYShopPluginUtil::checkIsActive("util_mobile_check")){
			SOY2::import("module.plugins.util_mobile_check.util.UtilMobileCheckUtil");
			if(class_exists("UtilMobileCheckUtil")){
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
				if(SOYShopPluginUtil::checkIsActive("util_multi_language") && isset($configs["i"]) && class_exists("UtilMultiLanguageUtil")){
					foreach($multiLangConfig as $key => $values){
						if(
							(isset($values["prefix"]) && strlen($values["prefix"])) &&
							(isset($values["is_use"]) && $values["is_use"] == UtilMultiLanguageUtil::IS_USE)
						){
							$configs[$configs["i"] . "/" . $key] = $configs["i"] . "/" . $values["prefix"];
						}
					}
				}
			}
		}

		if(count($configs)) krsort($configs);

		return $configs;
	}

	function checkHasTempDir(){
		$array = array(SOYShop_Page::TYPE_CART, SOYShop_Page::TYPE_MYPAGE);
		foreach($array as $mode){
			$dir = $this->dir . $mode . "/";
			if(is_dir($dir) && is_readable($dir)){
				$files = scandir($dir);

				foreach($files as $file){
					if($file[0] == ".") continue;

					//アプリケーションテンプレートのディレクトリが一つでもあればtrueを返す
					if(file_exists($dir . $file) && is_dir($dir . $file)){
						return true;
					}
				}
			}
		}

		return false;
	}




	/** アプリケーションテンプレート周り **/
	function getApplicationTemplates($mode = SOYShop_Page::TYPE_CART){
		//初期化
		$this->files = array();
		$this->mode = $mode;
		$dir = $this->dir . $mode . "/";

		self::scanDirRecursive($dir);

		//一つ目のディレクトリ名を配列のキーにして返す
		return $this->files;
	}

	//再帰でアプリケーションテンプレートを探して取得する
	private function scanDirRecursive($dir, $r = false){
		if(is_dir($dir) && is_readable($dir)){
			$files = scandir($dir);
			foreach($files as $file){
				if($file[0] == ".") continue;

				//ディレクトリ
				if(is_dir($dir . $file)){
					self::scanDirRecursive($dir . $file . "/", true);
				//ファイル
				}else{
					//二回目以降の読み込み
					if($r){
						$this->files[] = self::convertFileName($dir, $file);
					}
				}
			}
		}
	}

	//ディレクトリ構造を.区切りにしてファイル名にする
	private function convertFileName($dir, $file){
		$d = str_replace($this->dir, "", $dir);
		$d = str_replace($this->mode . "/", "", $d);
		return str_replace("/", ".", $d) . $file;
	}

	//管理画面でテンプレートを取得する
	function getTemplateFile($args){
		$dir = $this->dir;
		for($i = 0; $i < count($args); $i++){
			$dir .= str_replace(".", "/", $args[$i]);
			if($i !== count($args) - 1) $dir .= "/";
		}
		$dir .= "Page.html";
		return (file_exists($dir) && !is_dir($dir)) ? $dir : null;
	}
}
