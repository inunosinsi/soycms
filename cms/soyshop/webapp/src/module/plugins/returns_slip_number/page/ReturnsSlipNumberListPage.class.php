<?php

class ReturnsSlipNumberListPage extends WebPage {

	private $configObj;

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["export"])){
				$labels = array("返送伝票番号");

				$searchLogic = SOY2Logic::createInstance("module.plugins.returns_slip_number.logic.SearchReturnsSlipNumberLogic");
				$searchLogic->setLimit(100);
				$searchLogic->setCondition(self::getParameter("search_condition"));
				$lines = $searchLogic->getOnlySlipNumbers();

				$charset = (isset($_POST["charset"])) ? $_POST["charset"] : "Shift-JIS";

				if(count($lines) == 0) return;

				set_time_limit(0);

				header("Cache-Control: public");
				header("Pragma: public");
				header("Content-Disposition: attachment; filename=returns_slip_number_" .date("YmdHis", time()) . ".csv");
				header("Content-Type: text/csv; charset=" . htmlspecialchars($charset).";");

				ob_start();
				echo implode(",", $labels);
				echo "\n";
				echo implode("\n", $lines);
				$csv = ob_get_contents();
				ob_end_clean();

				echo mb_convert_encoding($csv, $charset, "UTF-8");
				exit;
			}

			if(isset($_POST["import"])){
				$file  = $_FILES["csv"];
				$charset = (isset($_POST["charset"])) ? $_POST["charset"] : "Shift_JIS";

				$logic = SOY2Logic::createInstance("logic.csv.ExImportLogicBase");
				$logic->setSeparator("comma");
		        $logic->setQuote("checked");
		        $logic->setCharset($charset);

				if(!$logic->checkUploadedFile($file)){
		            SOY2PageController::jump("Extension.returns.slip_number?failed");
		            exit;
		        }
		        // if(!$logic->checkFileContent($file)){
		        //     SOY2PageController::jump("Extension.returns.slip_number?invalid");
		        //     exit;
		        // }

				//ファイル読み込み・削除
		        $fileContent = file_get_contents($file["tmp_name"]);
		        unlink($file["tmp_name"]);

		        //データを行単位にばらす
		        $lines = $logic->GET_CSV_LINES($fileContent);    //fix multiple lines
				array_shift($lines);	//必ず先頭行を削除

		        //先頭行削除
				//if(isset($format["label"])) array_shift($lines);
				$slipDao = SOY2DAOFactory::create("SOYShop_ReturnsSlipNumberDAO");
				$slipLogic = SOY2Logic::createInstance("module.plugins.returns_slip_number.logic.ReturnsSlipNumberLogic");

				foreach($lines as $line){
		            if(empty($line)) continue;

					//PON対応 @ToDo 他のCSVのパターンがあったときはその都度考える
					$v = explode(",", $line);
					if(count($v)){
						$slipNumber = trim(str_replace("\"", "", $v[0]));

						try{
							$slipId = $slipDao->getBySlipNumberAndNoReturn($slipNumber)->getId();
						}catch(Exception $e){
							continue;
						}

						$slipLogic->changeStatus((int)$slipId, "return");
					}
				}

				SOY2PageController::jump("Extension.returns.slip_number?updated");
			}
		}

		SOY2PageController::jump("Extension.returns_slip_number?failed");
	}

	function __construct(){
		SOY2::import("module.plugins.returns_slip_number.domain.SOYShop_ReturnsSlipNumberDAO");

		//リセット
		if(isset($_POST["reset"])){
			self::setParameter("search_condition", null);
			SOY2PageController::jump("Extension.returns_slip_number");
		}

		//ここで翻訳ファイルを読み込む
		MessageManager::addMessagePath("admin");

		parent::__construct();

		if(isset($_GET["return"])) self::changeStatus();
		if(isset($_GET["remove"])) self::remove();

		foreach(array("successed", "failed", "removed", "invalid") as $t){
			DisplayPlugin::toggle($t, isset($_GET[$t]));
		}

		self::buildSearchForm();

		$searchLogic = SOY2Logic::createInstance("module.plugins.returns_slip_number.logic.SearchReturnsSlipNumberLogic");
 		$searchLogic->setLimit(100);
 		$searchLogic->setCondition(self::getParameter("search_condition"));
 		$slips = $searchLogic->get();
 		$total = $searchLogic->getTotal();

		DisplayPlugin::toggle("no_slip_number", $total === 0);
		DisplayPlugin::toggle("is_slip_number", $total > 0);

		SOY2::import("module.plugins.returns_slip_number.component.ReturnsSlipNumberListComponent");
		$this->createAdd("slip_number_list", "ReturnsSlipNumberListComponent", array(
			"list" => $slips
		));

		self::buildExportForm();
		self::buildImportForm();
	}

	private function buildSearchForm(){

		//POSTのリセット
		if(isset($_POST["search_condition"])){
			foreach($_POST["search_condition"] as $key => $value){
				if(is_array($value)){
					//
				}else{
					if(!strlen($value)){
						unset($_POST["search_condition"][$key]);
					}
				}
			}
		}

		if(isset($_POST["search"]) && !isset($_POST["search_condition"])){
			self::setParameter("search_condition", null);
			$cnd = array();
		}else{
			$cnd = self::getParameter("search_condition");
		}
		//リセットここまで

		$this->addModel("search_area", array(
			"style" => (isset($cnd) && count($cnd)) ? "display:inline;" : "display:none;"
		));

		$this->addForm("search_form");

		SOY2::import("module.plugins.returns_slip_number.domain.SOYShop_ReturnsSlipNumber");
		$this->addCheckBox("no_return", array(
			"name" => "search_condition[is_return][]",
			"value" => SOYShop_ReturnsSlipNumber::NO_RETURN,
			"selected" => (isset($cnd["is_return"]) && is_numeric(array_search(SOYShop_ReturnsSlipNumber::NO_RETURN, $cnd["is_return"]))),
			"label" => "未返送"
		));

		$this->addCheckBox("is_return", array(
			"name" => "search_condition[is_return][]",
			"value" => SOYShop_ReturnsSlipNumber::IS_RETURN,
			"selected" => (isset($cnd["is_return"]) && is_numeric(array_search(SOYShop_ReturnsSlipNumber::IS_RETURN, $cnd["is_return"]))),
			"label" => "返送済み(注文詳細で返却済みのものは除く)"
		));
	}

	private function changeStatus(){
		if(soy2_check_token()){
			$mode = (!isset($_GET["back"])) ? "return" : "back";
			if(SOY2Logic::createInstance("module.plugins.returns_slip_number.logic.ReturnsSlipNumberLogic")->changeStatus((int)$_GET["return"], $mode)){
				SOY2PageController::jump("Extension.returns_slip_number?successed");
			}else{
				SOY2PageController::jump("Extension.returns_slip_number?failed");
			}
		}
	}

	private function remove(){
		if(soy2_check_token()){
			$slipId = (int)$_GET["remove"];
			try{
				SOY2DAOFactory::create("SOYShop_ReturnsSlipNumberDAO")->deleteById($slipId);
				SOY2PageController::jump("Extension.returns_slip_number?removed");
			}catch(Exception $e){
				SOY2PageController::jump("Extension.returns_slip_number?failed");
			}
		}
	}

	private function buildExportForm(){
		$this->addForm("export_form");
	}

	private function buildImportForm(){
		$this->addForm("import_form", array(
             "ENCTYPE" => "multipart/form-data"
        ));
	}

	private function getParameter($key){
		if(array_key_exists($key, $_POST)){
			$value = $_POST[$key];
			self::setParameter($key,$value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("Plugin.Return.Slip:" . $key);
		}
		return $value;
	}
	private function setParameter($key,$value){
		SOY2ActionSession::getUserSession()->setAttribute("Plugin.Return.Slip:" . $key, $value);
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
