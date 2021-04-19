<?php

class SlipNumberListPage extends WebPage {

	private $configObj;
	const OUTPUT_LIMIT = 300;

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["export"])){
				$labels = array("伝票番号");

				$searchLogic = SOY2Logic::createInstance("module.plugins.slip_number.logic.SearchSlipNumberLogic");
				$searchLogic->setLimit(self::OUTPUT_LIMIT);
				$searchLogic->setCondition(self::_getParameter("search_condition"));
				$lines = $searchLogic->getOnlySlipNumbers();

				$charset = (isset($_POST["charset"])) ? $_POST["charset"] : "Shift-JIS";

				if(count($lines) == 0) return;

				set_time_limit(0);

				header("Cache-Control: public");
				header("Pragma: public");
				header("Content-Disposition: attachment; filename=slip_number_" .date("YmdHis", time()) . ".csv");
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
		            SOY2PageController::jump("Extension.slip_number?failed");
		            exit;
		        }
		        // if(!$logic->checkFileContent($file)){
		        //     SOY2PageController::jump("Extension.slip_number?invalid");
		        //     exit;
		        // }

				//ファイル読み込み・削除
		        $fileContent = file_get_contents($file["tmp_name"]);
		        unlink($file["tmp_name"]);

		        //データを行単位にばらす
		        $lines = $logic->GET_CSV_LINES($fileContent);    //fix multiple lines

				//PONであるか調べる
				SOY2::import("module.plugins.slip_number.util.SlipNumberUtil");
				$isPon = SlipNumberUtil::checkIsPon($lines[0]);

				array_shift($lines);	//必ず先頭行を削除

		        //先頭行削除
				//if(isset($format["label"])) array_shift($lines);
				$slipDao = SOY2DAOFactory::create("SOYShop_SlipNumberDAO");
				$slipLogic = SOY2Logic::createInstance("module.plugins.slip_number.logic.SlipNumberLogic");

				foreach($lines as $line){
					if(empty($line)) continue;

					$v = explode(",", $line);
					if(count($v)){
						//PON対応
						if($isPon && isset($v[2])){
							//04/26のような日付が無い場合は処理を止める
							preg_match('/^[0-9]*\/[0-9]*/', trim($v[2], "\""), $tmp);
							if(!isset($tmp[0])) continue;
						}

						$slipNumber = trim(str_replace("\"", "", $v[0]));

						try{
							$slipId = $slipDao->getBySlipNumberAndNoDelivery($slipNumber)->getId();
						}catch(Exception $e){
							continue;
						}

						$slipLogic->changeStatus((int)$slipId, "delivery");
					}
				}

				SOY2PageController::jump("Extension.slip_number?updated");
			}
		}

		//SOY2PageController::jump("Extension.slip_number?failed");
	}

	function __construct(){
		SOY2::import("module.plugins.slip_number.domain.SOYShop_SlipNumberDAO");

		//リセット
		if(isset($_POST["reset"])){
			self::setParameter("search_condition", null);
			SOY2PageController::jump("Extension.slip_number");
		}

		//ここで翻訳ファイルを読み込む
		MessageManager::addMessagePath("admin");

		parent::__construct();

		if(isset($_GET["delivery"])) self::_changeStatus();
		if(isset($_GET["remove"])) self::_remove();

		foreach(array("successed", "failed", "removed", "invalid") as $t){
			DisplayPlugin::toggle($t, isset($_GET[$t]));
		}

		self::_buildSearchForm();

		$searchLogic = SOY2Logic::createInstance("module.plugins.slip_number.logic.SearchSlipNumberLogic");
		$searchLogic->setLimit(self::OUTPUT_LIMIT);
		$searchLogic->setCondition(self::_getParameter("search_condition"));
		$slips = $searchLogic->get();
		$total = $searchLogic->getTotal();

		$cnt = count($slips);

		DisplayPlugin::toggle("no_slip_number", $cnt === 0);
		DisplayPlugin::toggle("is_slip_number", $cnt > 0);

		$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");
		$orderIds = ($cnt > 0) ? self::_getOrderIds($slips) : array();
		$pairList = ($cnt > 0) ? $orderLogic->getOrderIdAndUserIdPairList($orderIds) : array();

		SOY2::import("module.plugins.slip_number.component.SlipNumberListComponent");
		$this->createAdd("slip_number_list", "SlipNumberListComponent", array(
			"list" => $slips,
			"trackingNumberList" => ($cnt > 0) ? $orderLogic->getTrackingNumberListByIds($orderIds) : array(),
			"userNameList" => ($cnt > 0) ? SOY2Logic::createInstance("logic.user.UserLogic")->getUserNameListByUserIds($pairList) : array(),
			"pairList" => $pairList,
			"orderDateList" => ($cnt > 0) ? $orderLogic->getOrderDateListByIds($orderIds) : array()
		));

		self::_buildExportForm();
		self::_buildImportForm();
	}

	private function _getOrderIds($slips){
		if(!is_array($slips) || !count($slips)) return array();

		$ids = array();
		foreach($slips as $slip){
			if(is_numeric(array_search((int)$slip->getOrderId(), $ids))) continue;
			$ids[] = (int)$slip->getOrderId();
		}
		return $ids;
	}

	private function _buildSearchForm(){

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
			self::_setParameter("search_condition", null);
			$cnd = array();
		}else{
			$cnd = self::_getParameter("search_condition");
		}
		//リセットここまで

		$this->addModel("search_area", array(
			"style" => (isset($cnd) && count($cnd)) ? "display:inline;" : "display:none;"
		));

		$this->addForm("search_form");

		SOY2::import("module.plugins.slip_number.domain.SOYShop_SlipNumber");
		$this->addCheckBox("no_delivery", array(
			"name" => "search_condition[is_delivery][]",
			"value" => SOYShop_SlipNumber::NO_DELIVERY,
			"selected" => (isset($cnd["is_delivery"]) && is_numeric(array_search(SOYShop_SlipNumber::NO_DELIVERY, $cnd["is_delivery"]))),
			"label" => "未発送"
		));

		$this->addCheckBox("is_delivery", array(
			"name" => "search_condition[is_delivery][]",
			"value" => SOYShop_SlipNumber::IS_DELIVERY,
			"selected" => (isset($cnd["is_delivery"]) && is_numeric(array_search(SOYShop_SlipNumber::IS_DELIVERY, $cnd["is_delivery"]))),
			"label" => "発送済み(注文詳細で発送済みのものは除く)"
		));
	}

	private function _changeStatus(){
		if(soy2_check_token()){
			$mode = (!isset($_GET["back"])) ? "delivery" : "back";
			if(SOY2Logic::createInstance("module.plugins.slip_number.logic.SlipNumberLogic")->changeStatus((int)$_GET["delivery"], $mode)){
				SOY2PageController::jump("Extension.slip_number?successed");
			}else{
				SOY2PageController::jump("Extension.slip_number?failed");
			}
		}
	}

	private function _remove(){
		if(soy2_check_token()){
			$slipId = (int)$_GET["remove"];
			try{
				SOY2DAOFactory::create("SOYShop_SlipNumberDAO")->deleteById($slipId);
				SOY2PageController::jump("Extension.slip_number?removed");
			}catch(Exception $e){
				SOY2PageController::jump("Extension.slip_number?failed");
			}
		}
	}

	private function _buildExportForm(){
		$this->addForm("export_form");
	}

	private function _buildImportForm(){
		$this->addForm("import_form", array(
             "ENCTYPE" => "multipart/form-data"
        ));
	}

	private function _getParameter($key){
		if(array_key_exists($key, $_POST)){
			$value = $_POST[$key];
			self::_setParameter($key,$value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("Plugin.Slip:" . $key);
		}
		return $value;
	}
	private function _setParameter($key,$value){
		SOY2ActionSession::getUserSession()->setAttribute("Plugin.Slip:" . $key, $value);
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
