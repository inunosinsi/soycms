<?php

class ReturnsSlipNumberListPage extends WebPage {

	private $configObj;
	const OUTPUT_LIMIT = 300;

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["export"])){
				$labels = array("返送伝票番号","D","S","P");	//ダミーのカラム

				$searchLogic = SOY2Logic::createInstance("module.plugins.returns_slip_number.logic.SearchReturnsSlipNumberLogic");
				$searchLogic->setLimit(self::OUTPUT_LIMIT);
				$searchLogic->setCondition(self::_getParameter("search_condition"));
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
		            SOY2PageController::jump("Extension.returns_slip_number?failed");
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
				$lines = $logic->encodeFrom($lines);
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
						//PONではv[2]がステータス 3がプロセス
						if(isset($v[3]) && $v[3] != 1) continue;
						if(isset($v[2])){
							if(strpos($v[2], "配達") === false || strpos($v[2], "完了") === false) continue;
						}

						$slipNumber = trim(str_replace("\"", "", $v[0]));

						try{
							$slipId = $slipDao->getBySlipNumberAndNoReturn($slipNumber)->getId();
						}catch(Exception $e){
							continue;
						}

						$slipLogic->changeStatus((int)$slipId, "return");
					}
				}

				SOY2PageController::jump("Extension.returns_slip_number?updated");
			}

			SOYShopPlugin::invoke("soyshop.slip.html", array(
				"mode" => "post"
			));
		}
	}

	function __construct(){
		SOY2::import("module.plugins.returns_slip_number.domain.SOYShop_ReturnsSlipNumberDAO");

		//リセット
		if(isset($_POST["reset"])){
			self::_setParameter("search_condition", null);
			SOY2PageController::jump("Extension.returns_slip_number");
		}

		//ここで翻訳ファイルを読み込む
		MessageManager::addMessagePath("admin");
		SOYShopPlugin::load("soyshop.slip.html");

		parent::__construct();

		if(isset($_GET["return"])) self::_changeStatus();
		if(isset($_GET["remove"])) self::_remove();

		foreach(array("successed", "failed", "removed", "invalid") as $t){
			DisplayPlugin::toggle($t, isset($_GET[$t]));
		}

		self::_buildSearchForm();

		$searchLogic = SOY2Logic::createInstance("module.plugins.returns_slip_number.logic.SearchReturnsSlipNumberLogic");
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

		SOY2::import("module.plugins.returns_slip_number.component.ReturnsSlipNumberListComponent");
		$this->createAdd("slip_number_list", "ReturnsSlipNumberListComponent", array(
			"list" => $slips,
			"trackingNumberList" => ($cnt > 0) ? $orderLogic->getTrackingNumberListByIds($orderIds) : array(),
			"userNameList" => ($cnt > 0) ? SOY2Logic::createInstance("logic.user.UserLogic")->getUserNameListByUserIds($pairList) : array(),
			"pairList" => $pairList,
			"orderDateList" => ($cnt > 0) ? $orderLogic->getOrderDateListByIds($orderIds) : array()
		));

		self::_buildExportForm();
		self::_buildImportForm();

		$this->addLabel("extension_html", array(
			"html" => SOYShopPlugin::display("soyshop.slip.html")
		));
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
			//"style" => (isset($cnd) && count($cnd)) ? "display:inline;" : "display:none;"
			"style" => "display:inline;"
		));

		$this->addForm("search_form");

		foreach(array("item_name", "user_name") as $t){
			$this->addInput("search_" . $t, array(
				"name" => "search_condition[" . $t . "]",
				"value" => (isset($cnd[$t])) ? $cnd[$t] : ""
			));
		}

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

		$this->createAdd("custom_search_item_list", "_common.Order.CustomSearchItemListComponent", array(
			"list" => self::_getCustomSearchItems($cnd)
		));
	}

	private function _getCustomSearchItems($cnd){
		//検索フォームの拡張ポイント
		SOYShopPlugin::load("soyshop.slip.search");
		$items = SOYShopPlugin::invoke("soyshop.slip.search", array(
			"mode" => "form",
			"params" => (isset($cnd["customs"])) ? $cnd["customs"] : array()
		))->getSearchItems();

		//再配列
		$list = array();
		foreach($items as $item){
			if(is_null($item)) continue;
			$key = key($item);
			if($key == "label"){
				$list[] = $item;
			//複数の項目が入っている
			}else{
				foreach($item as $v){
					$list[] = $v;
				}
			}
		}

		return $list;
	}

	private function _changeStatus(){
		if(soy2_check_token()){
			$mode = (!isset($_GET["back"])) ? "return" : "back";
			if(SOY2Logic::createInstance("module.plugins.returns_slip_number.logic.ReturnsSlipNumberLogic")->changeStatus((int)$_GET["return"], $mode)){
				SOY2PageController::jump("Extension.returns_slip_number?successed");
			}else{
				SOY2PageController::jump("Extension.returns_slip_number?failed");
			}
		}
	}

	private function _remove(){
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
			$value = SOY2ActionSession::getUserSession()->getAttribute("Plugin.Return.Slip:" . $key);
		}
		return $value;
	}
	private function _setParameter($key,$value){
		SOY2ActionSession::getUserSession()->setAttribute("Plugin.Return.Slip:" . $key, $value);
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
