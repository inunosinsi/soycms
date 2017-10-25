<?php
class ExportPage extends WebPage{

	var $logic;

	function __construct() {

		//管理制限の権限を取得
		$session = SOY2ActionSession::getUserSession();
		$appLimit = $session->getAttribute("app_shop_auth_limit");

		//権限がない場合は顧客トップにリダイレクト
		if(!$appLimit){
			SOY2PageController::jump("User");
		}

		parent::__construct();

		$this->buildForm();

		DisplayPlugin::toggle("retry", isset($_GET["retry"]));
	}

	function buildForm(){
		$this->addForm("export_form");

		DisplayPlugin::toggle("user_custom_search_field", SOYShopPluginUtil::checkIsActive("user_custom_search_field"));
		DisplayPlugin::toggle("point", SOYShopPluginUtil::checkIsActive("common_point_base"));

		$this->createAdd("customfield_list", "_common.User.CustomFieldListComponent", array(
			"list" => $this->getCustomFieldList()
		));

		//カスタムサーチフィールドリストを表示する
		$this->createAdd("custom_search_field_list", "_common.User.UserCustomSearchFieldImExportListComponent", array(
			"list" => $this->getCustomSearchFieldList()
		));
	}

	function getLabels(){
		$labels = array(
			"id" => "ID",

			"mailAddress" => "メールアドレス",
			"name" => "名前",
			"reading" => "フリガナ",
			"nickname" => "ニックネーム",
			"isPublish" => "公開状態",
			"genderText" => "性別",
			"birthdayText" => "生年月日",

			"zipCode" => "郵便番号",
			"areaText" => "住所（都道府県）",
			"address1" => "住所１",
			"address2" => "住所２",
			"telephoneNumber" => "電話番号",
			"faxNumber" => "FAX番号",

			"cellphoneNumber" => "携帯電話",
			"url" => "URL",
			"jobName" => "勤務先名称・職種",
			"jobZipCode" => "勤務先郵便番号",
			"jobAreaText" => "勤務先住所（都道府県）",
			"jobAddress1" => "勤務先住所１",
			"jobAddress2" => "勤務先住所２",

			"jobTelephoneNumber" => "勤務先電話番号",
			"jobFaxNumber" => "勤務先FAX番号",
			"attribute1" => "属性１",
			"attribute2" => "属性２",
			"attribute3" => "属性３",
			"memo" => "備考",
		);

		if(SOYShopPluginUtil::checkIsActive("common_point_base")){
			$labels["point"] = "ポイント";
		}

		return $labels;
	}

	function getCustomFieldList($flag = false){
		$dao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
		$config = SOYShop_UserAttributeConfig::load($flag);
		return $config;
	}

	function getCustomSearchFieldList(){
		SOY2::import("module.plugins.user_custom_search_field.util.UserCustomSearchFieldUtil");
		return UserCustomSearchFieldUtil::getConfig();
	}

	function doPost(){
		if(!soy2_check_token()){
			SOY2PageController::jump("User.Export?retry");
			exit;
		}

		set_time_limit(0);

		$logic = SOY2Logic::createInstance("logic.user.ExImportLogic");
		$this->logic = $logic;


		$format = $_POST["format"];
		$item = $_POST["item"];
		$displayLabel = @$format["label"];

		$logic->setSeparator(@$format["separator"]);
		$logic->setQuote(@$format["quote"]);
		$charset = (isset($format["charset"])) ? $format["charset"] : null;
		$logic->setCharset($charset);

		//出力する項目にセット
		$logic->setItems($item);
		$logic->setLabels($this->getLabels());
		$logic->setCustomFields($this->getCustomFieldList(true));
		$logic->setCustomSearchFields($this->getCustomSearchFieldList());

		//DAO: 2000件ずつデータを取得
		$limit = 2000;
		$step = 0;
		$dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		$dao->setLimit($limit);

		do{
			if(connection_aborted())exit;

			$dao->setOffset($step * $limit);
			$step++;

			try{
				$shopUsers = $dao->get();
			}catch(Exception $e){
				$shopUsers = array();
			}

			//CSV(TSV)に変換
			$lines = array();
			foreach($shopUsers as $shopUser){
				$lines[] = $logic->export($shopUser);
			}

			//ファイル出力
			$this->outputFile($lines, $charset, $displayLabel);

		}while(count($shopUsers) >= $limit);

		exit;
	}

	/**
	 * ファイル出力: 改行コードはCRLF
	 */
	function outputFile($lines, $charset, $displayLabel){
		static $headerSent = false;

		if(!$headerSent){
			$headerSent = true;
			header("Cache-Control: no-cache");
			header("Pragma: no-cache");
			header("Content-Disposition: attachment; filename=soyshop_users-" . date("Ymd") . ".csv");
			header("Content-Type: text/csv; charset=" . htmlspecialchars($charset) . ";");

			if($displayLabel){
				echo $this->logic->getHeader();
				echo "\r\n";
			}
		}

		if(count($lines) > 0){
			echo implode("\r\n", $lines);
			echo "\r\n";
		}
	}
}
