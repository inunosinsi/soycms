<?php

class CustomSearchExImportPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
	}

	function doPost(){

		if(soy2_check_token()){

			//CSVのエクスポート
			if(isset($_POST["output_customfield_config"])){

				$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
				$configs = SOYShop_ItemAttributeConfig::load();

				header("Cache-Control: no-cache");
				header("Pragma: no-cache");
				header("Content-Disposition: attachment; filename=csfconfig.csv");
				header("Content-Type: text/csv; charset=UTF8;");

				echo "field_id,label,type,options\r\n";

				foreach($configs as $config){
					if($config->getType() === "image" || $config->getType() === "link") continue;

					$line = array();
					$line[] = $config->getFieldId();
					$line[] = $config->getLabel();
					$line[] = ($config->getType() === "input") ? CustomSearchFieldUtil::TYPE_STRING : $config->getType();


					$array = $config->getConfig();
					if(is_array($array) && isset($array["option"])){
						$o = str_replace(array("\n", "\r"), " ", trim($array["option"]));
						$line[] = str_replace("  ", " ", $o);
					}

					echo implode(",", $line) . "\r\n";
				}
				exit;

			//CSVのインポート
			}else if(isset($_POST["import_customfield_config"]) && isset($_FILES["csv"]) && count($_FILES["csv"])){
				$configs = CustomSearchFieldUtil::getConfig();
				$dbLogic = SOY2Logic::createInstance("module.plugins.custom_search_field.logic.DataBaseLogic");

				$file  = $_FILES["csv"];
				$logic = SOY2Logic::createInstance("logic.csv.ExImportLogicBase");
				$logic->setSeparator("comma");
//				$logic->setQuote("checked");
				$logic->setCharset("UTF-8");

				if(!$logic->checkUploadedFile($file)) $this->configObj->redirect("eximport&failed");

				//ファイル読み込み・削除
				$fileContent = file_get_contents($file["tmp_name"]);
				unlink($file["tmp_name"]);

				//データを行単位にばらす
				$lines = $logic->GET_CSV_LINES($fileContent);	//fix multiple lines
				array_shift($lines);

				foreach($lines as $line){
					if(empty($line)) continue;

					$values = explode(",", $line);
					for($i = 0; $i < count($values); $i++){
						if(strpos($values[$i], "\"") !== false){
							$values[$i] = str_replace("\"", "", $values[$i]);
						}
					}

					//field_idの文字列チェック
					if(!self::checkPattern($values[0])) continue;

					//すでにfield_idがあるかチェック
					if(self::checkKeyExist($configs, $values[0])) continue;

					//入力されているタイプが正しいか？
					if(!CustomSearchFieldUtil::checkIsType($values[2])) continue;

					if($dbLogic->addColumn($values[0], $values[2])){
						$configs[$values[0]] = array("label" => $values[1], "type" => $values[2], "option" => str_replace(" ", "\n", $values[3]));
					}
				}
				CustomSearchFieldUtil::saveConfig($configs);
				$this->configObj->redirect("eximport&successed");

			//カスタムフィールドの値をサーチフィールドへ移行
			}else if(isset($_POST["migrate_customfield_values"])){
				if(SOY2Logic::createInstance("module.plugins.custom_search_field.logic.DataBaseLogic")->migrate()){
					$this->configObj->redirect("eximport&successed");
				}
			}
		}

		$this->configObj->redirect("eximport&failed");
	}

	function execute(){
		parent::__construct();

		$this->addLabel("nav", array(
			"html" => LinkNaviAreaComponent::build()
		));

		DisplayPlugin::toggle("successed", isset($_GET["successed"]));
		DisplayPlugin::toggle("failed", isset($_GET["failed"]));

		$this->addForm("export_form");

		$this->addForm("import_form", array(
			"enctype" => "multipart/form-data"
		));

		$this->addForm("migrate_form");
	}

	private function checkPattern($str){
		return (preg_match("/^[a-zA-Z0-9-_]+$/", trim($str)));
	}
	private function checkKeyExist($configs, $key){
		return (array_key_exists($key, $configs));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>
