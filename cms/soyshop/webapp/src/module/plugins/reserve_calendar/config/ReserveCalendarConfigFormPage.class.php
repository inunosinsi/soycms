<?php

class ReserveCalendarConfigFormPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
	}

	function doPost(){
		if(soy2_check_token() && isset($_POST["Config"])){
			ReserveCalendarUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$config = ReserveCalendarUtil::getConfig();

		$this->addForm("form");

		$this->addCheckBox("is_tmp_order", array(
			"name" => "Config[tmp]",
			"value" => ReserveCalendarUtil::IS_TMP,
			"selected" => (!isset($config["tmp"]) || $config["tmp"] == ReserveCalendarUtil::IS_TMP),
			"label" => "仮登録を行う(β版)",
			"elementid" => "is_tmp_order"
		));

		$this->addCheckBox("no_tmp_order", array(
			"name" => "Config[tmp]",
			"value" => ReserveCalendarUtil::NO_TMP,
			"selected" => (isset($config["tmp"]) && $config["tmp"] == ReserveCalendarUtil::NO_TMP),
			"label" => "仮登録を行わない",
			"elementid" => "no_tmp_order"
		));

		$this->addCheckBox("send_at_time_tmp_order", array(
			"name" => "Config[send_at_time_tmp]",
			"value" => ReserveCalendarUtil::IS_SEND,
			"selected" => (isset($config["send_at_time_tmp"]) && $config["send_at_time_tmp"] == ReserveCalendarUtil::IS_SEND),
			"label" => "仮登録の予約の際にメール文面に本登録用のURLを含める"
		));

		$this->addCheckBox("only", array(
			"name" => "Config[only]",
			"value" => ReserveCalendarUtil::IS_ONLY,
			"selected" => (isset($config["only"]) && (int)$config["only"] === ReserveCalendarUtil::IS_ONLY),
			"label" => "注文時の商品個数は１個のみ"
		));

		$this->addCheckBox("show_price", array(
			"name" => "Config[show_price]",
			"value" => ReserveCalendarUtil::IS_SHOW,
			"selected" => (isset($config["show_price"]) && (int)$config["show_price"] === ReserveCalendarUtil::IS_SHOW),
			"label" => "公開側のカレンダーでプランに価格を表示する"
		));

		$this->addCheckBox("ignore", array(
			"name" => "Config[ignore]",
			"value" => ReserveCalendarUtil::RESERVE_LIMIT_IGNORE,
			"selected" => (isset($config["ignore"]) && (int)$config["ignore"] === ReserveCalendarUtil::RESERVE_LIMIT_IGNORE),
			"label" => "残席以上の予約数があっても管理画面から予約を行うことができる"
		));

		$this->addCheckBox("cancel_button", array(
			"name" => "Config[cancel_button]",
			"value" => ReserveCalendarUtil::RESERVE_DISPLAY_CANCEL_BUTTON,
			"selected" => (isset($config["cancel_button"]) && (int)$config["cancel_button"] === ReserveCalendarUtil::RESERVE_DISPLAY_CANCEL_BUTTON),
			"label" => "キャンセルボタンを表示する"
		));

		DisplayPlugin::toggle("pre_register_annotation", !SOYShopPluginUtil::checkIsActive("change_order_status_invalid"));

		//
		$cartId = SOYShop_DataSets::get("config.cart.cart_id");
		$mypageId = SOYShop_DataSets::get("config.mypage.id");
		DisplayPlugin::toggle("recommend", $cartId != "bootstrap" || $mypageId != "bootstrap");
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
