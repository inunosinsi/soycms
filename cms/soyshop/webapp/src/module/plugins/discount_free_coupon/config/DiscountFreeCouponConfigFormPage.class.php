<?php

class DiscountFreeCouponConfigFormPage extends WebPage{

	private $isError = false;
	private $errors = array();
	private $config;
	private $dao;
	private $categoryLogic;

	function __construct() {
		SOY2::import("module.plugins.discount_free_coupon.domain.SOYShop_CouponDAO");
		SOY2::import("module.plugins.discount_free_coupon.util.DiscountFreeCouponUtil");
		SOY2::import("module.plugins.discount_free_coupon.component.CouponListComponent");
		$this->dao = SOY2DAOFactory::create("SOYShop_CouponDAO");
		$this->categoryLogic = SOY2Logic::createInstance("module.plugins.discount_free_coupon.logic.CouponCategoryLogic");
	}

	function doPost(){

		if(soy2_check_token()){

			if(isset($_POST["csv"])){

				$logic = SOY2Logic::createInstance("module.plugins.discount_free_coupon.logic.DiscountFreeCouponCsvLogic");

				if(isset($_POST["csv"]["coupon"])){
					$labels = $logic->getLabels();
					$lines = $logic->getLines();
					$charset = (isset($_POST["coupon"]["charset"])) ? $_POST["coupon"]["charset"] : "Shift-JIS";
					$fileName = "discount_free_coupon_";
				}else{
					$labels = $logic->getLogLabels();
					$lines = $logic->getLogLines();
					$charset = (isset($_POST["log"]["charset"])) ? $_POST["log"]["charset"] : "Shift-JIS";
					$fileName = "discount_free_coupon_log_";
				}

				if(count($lines) == 0) return;

				set_time_limit(0);

				header("Cache-Control: public");
				header("Pragma: public");
	    		header("Content-Disposition: attachment; filename=" . $fileName.date("YmdHis", time()) . ".csv");
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

			if(isset($_POST["Register"])){
				if(self::_checkValidate($_POST["Register"], true)){
					$coupon = SOY2::cast("SOYShop_Coupon", (object)DiscountFreeCouponUtil::convertObject($_POST["Register"]));
					$coupon->setIsDelete(SOYShop_Coupon::NOT_DELETED);
					try{
						$this->dao->insert($coupon);
						$this->config->redirect("issued");
					}catch(Exception $e){
						//
					}
				}
			}
			$this->isError = true;
		}

		//各設定内容を変更する
		if(isset($_POST["edit_save"])){
			$edit = $_POST["Edit"];

			//idを取得できなかった場合は処理を終了
			if(isset($edit["id"])){
				if(self::_checkValidate($edit)){
					$coupon = self::_getCouponById($edit["id"]);
					if(is_null($coupon->getId())) $this->config->redirect("error");
					$coupon = SOY2::cast($coupon, (object)DiscountFreeCouponUtil::convertObject($edit));

					try{
						$this->dao->update($coupon);
						$this->config->redirect("updated");
					}catch(Exception $e){
						$this->config->redirect("error");
					}
				}
			}
		}

		//削除フラグ
		if(isset($_POST["remove"])){
			$edit = $_POST["Edit"];

			//idを取得できなかった場合は処理を終了
			if(isset($edit["id"])){
				$coupon = self::_getCouponById($edit["id"]);
				if(is_null($coupon->getId())) $this->config->redirect("error");

				//削除フラグをアクティブにする
				$coupon->setCouponCode(self::_renameCode($coupon->getCouponCode()));
				$coupon->setIsDelete(SOYShop_Coupon::DELETED);

				try{
					$this->dao->update($coupon);
				}catch(Exception $e){
					$this->config->redirect("error");
				}

				$this->config->redirect("deleted");
			}
		}

		//設定変更
		if(isset($_POST["Config"])){
			$config = $_POST["Config"];

			$config["min"] = DiscountFreeCouponUtil::convertNumber($config["min"], 0);
			$config["max"] = DiscountFreeCouponUtil::convertNumber($config["max"], null);
			$config["disitsMin"] = DiscountFreeCouponUtil::convertNumber($config["disitsMin"], 4);
			$config["disitsMax"] = DiscountFreeCouponUtil::convertNumber($config["disitsMax"], 16);

			if($config["disitsMin"] > $config["disitsMax"]){
				$config["disitsMin"] = ($config["disitsMax"] < 4) ? $config["disitsMax"] - 1 : 4;
			}

			try{
				DiscountFreeCouponUtil::saveConfig($config);
				$this->config->redirect("updated");
			}catch(Exception $e){
				$this->config->redirect("error");
			}
		}
	}

	function execute(){

		parent::__construct();

		$this->addLink("register_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=discount_free_coupon#register")
		));

		$this->addLink("category_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=discount_free_coupon&category")
		));

		self::_buildSearchForm();
		self::_buildList();

		$this->addForm("form", array(
			"action" => SOY2PageController::createLink("Config.Detail?plugin=discount_free_coupon"),
			"method" => "post"
		));

		self::_buildRegisterForm();

		foreach(array("issued", "error", "deleted") as $t){
			DisplayPlugin::toggle($t, isset($_GET[$t]));
		}

		self::_buildConfigForm();
		self::_buildError();

		$this->addLabel("category_code_js", array(
			"html" => $this->categoryLogic->createCodePrefixList()
		));

		$this->addLabel("code_js", array(
			"html" => file_get_contents(dirname(dirname(__FILE__)) . "/js/code.js")
		));
	}

	private function _buildSearchForm(){
		$cnds = self::_getSearchCondition();

		$this->addModel("search_form_area", array(
			"attr:id" => "search_form",
			"style" => (count($cnds)) ? "display:block;" : "display:none;"
		));

		$this->addForm("search_form", array(
			"method" => "get",
			"action" => SOY2PageController::createLink("Config.Detail")
		));

		foreach(array("name", "code", "name_or_code") as $t){
			$this->addInput("search_coupon_" . $t, array(
				"name" => "Search[" . $t . "]",
				"value" => (isset($cnds[$t])) ? $cnds[$t] : ""
			));
		}

		//クーポンの種類
		foreach(SOYShop_Coupon::getCouponTypeList() as $idx => $label){
			$this->addCheckBox("search_coupon_type_" . $idx, array(
				"name" => "Search[coupon_type][]",
				"value" => $idx,
				"selected" => (isset($cnds["coupon_type"]) && is_numeric(array_search($idx, $cnds["coupon_type"]))),
				"label" => $label
			));
		}

		//期限切れ
		$this->addCheckBox("search_expired", array(
			"name" => "Search[expired]",
			"value" => 1,
			"selected" => (isset($cnds["expired"]) && $cnds["expired"] == 1),
			"label" => "期限切れのクーポンを表示する"
		));

		$pluginId = (isset($_GET["plugin"])) ? $_GET["plugin"] : "";
		$this->addInput("plugin_id", array(
			"name" => "plugin",
			"value" => $pluginId
		));

		$this->addLink("reset_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=" . $pluginId)
		));

	}

	private function _buildList(){
		$searchLogic = SOY2Logic::createInstance("module.plugins.discount_free_coupon.logic.SearchCouponLogic");
		$searchLogic->setCondition(self::_getSearchCondition());
		$coupons = $searchLogic->search();
		$total = $searchLogic->getTotal();
		DisplayPlugin::toggle("coupon", $total > 0);
		DisplayPlugin::toggle("coupon_list", $total > 0);

		/** CSVフォーム **/

		$this->addForm("csv_form");

		$this->addInput("time_limit_start", array(
			"name"  => "Csv[timeLimitStart]",
			"value" => (isset($_POST["Register"]["timeLimitStart"])) ? $_POST["Register"]["timeLimitStart"] : ""
		));

		$this->addInput("time_limit_end", array(
			"name"  => "Register[timeLimitEnd]",
			"value" => (isset($_POST["Register"]["timeLimitEnd"])) ? $_POST["Register"]["timeLimitEnd"] : ""
		));

		/** 登録済みクーポン一覧 **/

		$this->addForm("edit_form");

		$this->createAdd("coupon_list", "CouponListComponent", array(
			"list" => $coupons,
			"categoryList" => $this->categoryLogic->getCategoryList()
		));
	}

	private function _getSearchCondition(){
		// @ToDo いずれはセッションで管理
		return (isset($_GET["Search"]) && is_array($_GET["Search"])) ? $_GET["Search"] : array();
	}

	private function _getCouponById($id){
		try{
			return $this->dao->getById($id);
		}catch(Exception $e){
			return new SOYShop_Coupon();
		}
	}

	private function _getCoupons(){
		try{
			return $this->dao->getNotDeleted();
		}catch(Exception $e){
			return array();
		}
	}

	private function _buildRegisterForm(){

		//カテゴリが登録されているか？
		$categoryList = $this->categoryLogic->getCategoryList();
		DisplayPlugin::toggle("has_category_list", count($categoryList));

		$this->addSelect("category", array(
			"name" => "Register[categoryId]",
			"options" => $categoryList,
			"selected" => (isset($_POST["Register"]["categoryId"])) ? $_POST["Register"]["categoryId"] : ""
		));

		$this->addInput("name", array(
			"name"  => "Register[name]",
			"value" => (isset($_POST["Register"]["name"])) ? $_POST["Register"]["name"] : ""
		));
		$this->addInput("coupon_code", array(
			"name"  => "Register[couponCode]",
			"value" => (isset($_POST["Register"]["couponCode"])) ? $_POST["Register"]["couponCode"] : ""
		));

		$this->addLabel("coupon_conde_annotation_disits_min", array(
			"text" => DiscountFreeCouponUtil::getDisitsMin()
		));
		$this->addLabel("coupon_conde_annotation_disits_max", array(
			"text" => DiscountFreeCouponUtil::getDisitsMax()
		));

		$this->addCheckBox("coupon_type_price", array(
			"name" => "Register[couponType]",
			"value" => SOYShop_Coupon::TYPE_PRICE,
			"selected" => ((isset($_POST["Register"]["couponType"]) && $_POST["Register"]["couponType"] != SOYShop_Coupon::TYPE_PERCENT) || !isset($_POST["Register"]["couponType"])),
			"label" => "値引き額"
		));

		$this->addCheckBox("coupon_type_percent", array(
			"name" => "Register[couponType]",
			"value" => SOYShop_Coupon::TYPE_PERCENT,
			"selected" => (isset($_POST["Register"]["couponType"]) && $_POST["Register"]["couponType"] == SOYShop_Coupon::TYPE_PERCENT),
			"label" => "値引き率"
		));

		$this->addCheckBox("coupon_type_delivery", array(
			"name" => "Register[couponType]",
			"value" => SOYShop_Coupon::TYPE_DELIVERY,
			"selected" => (isset($_POST["Register"]["couponType"]) && $_POST["Register"]["couponType"] == SOYShop_Coupon::TYPE_DELIVERY),
			"label" => "送料無料"
		));

		$this->addInput("discount", array(
			"name"  => "Register[discount]",
			"value" => (isset($_POST["Register"]["discount"]) && strlen($_POST["Register"]["discount"]) > 0) ? (int)$_POST["Register"]["discount"] : 0
		));
		$this->addInput("discout_percent", array(
			"name" => "Register[discountPercent]",
			"value" => (isset($_POST["Register"]["discountPercent"]) && strlen($_POST["Register"]["discountPercent"]) > 0) ? (int)$_POST["Register"]["discountPercent"] : 0
		));
		$this->addInput("count", array(
			"name"  => "Register[count]",
			"value" => (isset($_POST["Register"]["count"])) ? $_POST["Register"]["count"] : ""
		));
		$this->addInput("memo", array(
			"name"  => "Register[memo]",
			"value" => (isset($_POST["Register"]["memo"])) ? $_POST["Register"]["memo"] : ""
		));
		$this->addInput("price_limit_min", array(
			"name" => "Register[priceLimitMin]",
			"value" => (isset($_POST["Register"]["priceLimitMin"])) ? $_POST["Register"]["priceLimitMin"] : ""
		));

		$this->addInput("price_limit_max", array(
			"name" => "Register[priceLimitMax]",
			"value" => (isset($_POST["Register"]["priceLimitMax"])) ? $_POST["Register"]["priceLimitMax"] : ""
		));

		$this->addInput("time_limit_start", array(
			"name"  => "Register[timeLimitStart]",
			"value" => (isset($_POST["Register"]["timeLimitStart"])) ? $_POST["Register"]["timeLimitStart"] : ""
		));
		$this->addInput("time_limit_end", array(
			"name"  => "Register[timeLimitEnd]",
			"value" => (isset($_POST["Register"]["timeLimitEnd"])) ? $_POST["Register"]["timeLimitEnd"] : ""
		));
	}

	private function _buildConfigForm(){

		$config = DiscountFreeCouponUtil::getConfig();

		$this->addForm("config_form");

		$this->addInput("config_enable_amount_min", array(
			"name" => "Config[min]",
			"value" => (isset($config["min"])) ? $config["min"] : 0
		));

		$this->addInput("config_enable_amount_max", array(
			"name" => "Config[max]",
			"value" => (isset($config["max"])) ? $config["max"] : ""
		));

		$this->addInput("config_code_disits_min", array(
			"name" => "Config[disitsMin]",
			"value" => DiscountFreeCouponUtil::getDisitsMin(),
			"style" => "ime-mode:inactive;width:80px;"
		));

		$this->addInput("config_code_disits_max", array(
			"name" => "Config[disitsMax]",
			"value" => DiscountFreeCouponUtil::getDisitsMax(),
			"style" => "ime-mode:inactive;width:80px;"
		));
	}

	private function _buildError(){

		foreach(array(
			"name", "count", "coupon_length", "coupon_reg", "coupon", "discount",
			"discount_percent", "price_limit", "price_limit_compare", "time_limit",
			"time_limit_compare"
		) as $t){
			DisplayPlugin::toggle($t . "_error", isset($this->errors[$t]));
		}
	}

	//クーポンを削除する時にリネームする
	private function _renameCode($code){
		$i = 0;
		for(;;){
			$check = $code . "_delete_" . $i++;
			try{
				$res = $this->dao->executeQuery("SELECT id FROM soyshop_coupon WHERE coupon_code = :code", array(":code" => $check));
			}catch(Exception $e){
				break;
			}
			if(count($res) === 0) break;
		}
		return $check;
	}

	//isCodeCheckは更新の際、クーポンコードの入力がないため、クーポンコードのチェックを省くためのフラグ
	private function _checkValidate($values, $isCodeCheck=false){

		//クーポン名が入力されていない場合
		if(strlen($values["name"]) == 0){
			$this->errors["name"] = true;
		}

		//更新の場合はクーポンコード周りのチェックを行わない
		if($isCodeCheck){
			//クーポンコードが4文字から16文字以内でない場合
			if(
				strlen($values["couponCode"]) < DiscountFreeCouponUtil::getDisitsMin()
				 ||
				strlen($values["couponCode"]) > DiscountFreeCouponUtil::getDisitsMax()
			){
				$this->errors["coupon_length"] = true;
			}

			//クーポンコードが半角英数字以外の文字で入力されていない場合
			if(!preg_match("/^[a-zA-Z0-9]+$/", $values["couponCode"])){
				$this->errors["coupon_reg"] = true;
			}
		}

		//回数に数字以外の文字列が入力されていた時
		if(isset($values["count"]) && strlen($values["count"]) > 0){
			$count = DiscountFreeCouponUtil::convertNumber($values["count"]);
			if(!is_numeric($count)){
				$this->errors["count"] = true;
			}
		}

		//値引き額に数字以外の値が入力されていた場合
		if((int)$values["discount"] !== 0){
			if(!is_numeric($values["discount"])){
				$this->errors["discount"] = true;
			}
		}

		//値引き率に数字以外の値が入力されていた場合
		if((int)$values["discountPercent"] !== 0){
			if(!is_numeric($values["discountPercent"])){
				$this->errors["discountPercent"] = true;
			}
		}

		$min = 0;
		$max = 0;

		//利用可能金額に数字以外の文字列が入力された場合
		if(isset($values["priceLimitMin"]) && strlen($values["priceLimitMin"]) > 0){
			$min = DiscountFreeCouponUtil::convertNumber($values["priceLimitMin"], null);
			if(!preg_match("/^[0-9]+$/", $min)){
				$this->errors["price_limit"] = true;
			}
		}

		if(isset($values["priceLimitMax"]) && strlen($values["priceLimitMax"]) > 0){
			$max = DiscountFreeCouponUtil::convertNumber($values["priceLimitMax"], null);
			if(!preg_match("/^[0-9]+$/", $max)){
				$this->errors["price_limit"] = true;
			}
		}

		if($min > 0 && $max > 0){
			if($min > $max){
				$this->errors["price_limit_compare"] = true;
			}
		}


		$start = null;
		$end = null;

		//有効期限に数字以外の文字列が入力された場合
		if(isset($values["timeLimitStart"]) && strlen($values["timeLimitStart"]) > 0){
			$start = DiscountFreeCouponUtil::removeHyphen($values["timeLimitStart"]);
			if(!preg_match("/^[0-9]+$/", $start)){
				$this->errors["time_limit"] = true;
			}
		}

		if(isset($values["timeLimitEnd"]) && strlen($values["timeLimitEnd"]) > 0){
			$end = DiscountFreeCouponUtil::removeHyphen($values["timeLimitEnd"]);
			if(!preg_match("/^[0-9]+$/", $end)){
				$this->errors["time_limit"] = true;
			}
		}

		if(isset($start) && isset($end)){
		//開始日が終了日よりも後の場合
			if($start >= $end){
				$this->errors["time_limit_compare"] = true;
			}
		}

		return (count($this->errors) == 0);
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}
}
