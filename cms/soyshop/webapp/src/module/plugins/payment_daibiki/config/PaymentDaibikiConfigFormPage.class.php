<?php

class PaymentDaibikiConfigFormPage extends WebPage{

	private $config;

	function __construct() {
		SOY2::import("module.plugins.payment_daibiki.util.PaymentDaibikiUtil");
		SOY2::imports("module.plugins.payment_daibiki.component.*");
		SOY2::imports("module.plugins.payment_daibiki.component.region.*");
		SOY2DAOFactory::importEntity("config.SOYShop_Area");
	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["payment_daibiki"])){
				try{
					//設定
					$config = (isset($_POST["Config"])) ? $_POST["Config"] : array();
					$config["auto_calc"] = (isset($config["auto_calc"])) ? (int)$config["auto_calc"] : 0;
					$config["include_delivery_price"] = (isset($config["include_delivery_price"])) ? (int)$config["include_delivery_price"] : 0;

					PaymentDaibikiUtil::saveConfig($config);

					//代引き手数料
					if(isset($_POST["payment_daibiki"]["price_table"])){
						$keys = $_POST["payment_daibiki"]["price_table"]["key"];
						$fees = $_POST["payment_daibiki"]["price_table"]["price"];

						$res = array();
						foreach($keys as $key => $price){
							if(!isset($fees[$key]) || !is_numeric($fees[$key]) || !is_numeric($price)) continue;

							$price = (int)$price;
							$fee   = (int)$fees[$key];

							//価格に対する手数料がない場合は無条件で入れ、価格に対する手数料が既にある場合は手数料が大きいものを記録しておく
							if(!isset($res[$price]) || $fee > $res[$price]){
								$res[$price] = $fee;
							}
						}

						ksort($res);

						PaymentDaibikiUtil::savePricesConfig($res);
					}else{
						PaymentDaibikiUtil::savePricesConfig(array());
					}

					//説明文
					if(isset($_POST["payment_daibiki"]["description"])){
						PaymentDaibikiUtil::saveDescriptionConfig($_POST["payment_daibiki"]["description"]);
					}

					//メール
					if(isset($_POST["payment_daibiki"]["mail"])){
						PaymentDaibikiUtil::saveMailConfig($_POST["payment_daibiki"]["mail"]);
					}

					//代引き不可商品
					if(isset($_POST["payment_daibiki"]["item_table"])){
						$forbidden = $_POST["payment_daibiki"]["item_table"];

						//空と重複を削除
						$forbidden = array_merge(array_unique(array_diff(array_map("trim",$forbidden), array(""))), array());


					//なんかの条件で空に出来ない時があるので、値が何も無ければ設定自体を空にする
					}else{
						$forbidden = array();
					}

					PaymentDaibikiUtil::saveForbiddenConfig($forbidden);

					//地域別の代引き手数料設定
					if(isset($_POST["add"]) && is_numeric($_POST["add_area"])){
						$byRegionConfigs = PaymentDaibikiUtil::getPricesByRegionConfig();
						if(!isset($byRegionConfigs[$_POST["add_area"]])){
							$byRegionConfigs[$_POST["add_area"]] = array();
							PaymentDaibikiUtil::savePricesByRegionConfig($byRegionConfigs);
						}
					//地域ごとの設定の削除
					}else if(isset($_POST["by_region_remove"])){
						$regionCode = key($_POST["by_region_remove"]);
						$byRegionConfigs = PaymentDaibikiUtil::getPricesByRegionConfig();
						if(is_numeric($regionCode) && isset($byRegionConfigs[$regionCode])){
							unset($byRegionConfigs[$regionCode]);
							PaymentDaibikiUtil::savePricesByRegionConfig($byRegionConfigs);
						}
					//地域ごとの代引き手数料設定
					}else if(isset($_POST["price_by_region"]) && count($_POST["price_by_region"])){
						$byRegionConfigs = array();
						foreach($_POST["price_by_region"] as $area => $values){
							$keys = $values["key"];
							$array = $values["price"];

							$res = array();
							foreach($keys as $key => $value){
								if(strlen($array[$key]) < 1)continue;
								if(strlen($value) < 1)continue;

								//number_format対応（たぶんヨーロッパだと使えない）
								$price = (int)str_replace(array(" ",","),"",$value);
								$fee   = (int)str_replace(array(" ",","),"",$array[$key]);

								if(isset($array[$key]) && strlen($array[$key]) >0 && strlen($value) > 0){
									$res[$price] = $fee;
								}
							}

							ksort($res);
							$byRegionConfigs[$area] = $res;
						}

						PaymentDaibikiUtil::savePricesByRegionConfig($byRegionConfigs);
					}

					$this->config->redirect("updated");
				}catch(Exception $e){
					//
				}
			}
		}
		$this->config->redirect("error");
	}

	function execute(){

		parent::__construct();

		DisplayPlugin::toggle("error", isset($_GET["error"]));

		self::buildForm();
	}

	private function buildForm(){

		$this->addForm("config_form");

		$this->createAdd("price_list", "PaymentDaibikiPriceListComponent", array(
			"list" => PaymentDaibikiUtil::getPricesConfig(true)
		));

		//地域ごとの代引き手数料設定
		$areas = SOYShop_Area::getAreas();
		$this->addSelect("area", array(
			"name" => "add_area",
			"options" => $areas
		));

		$byRegionConfigs = PaymentDaibikiUtil::getPricesByRegionConfig();
		DisplayPlugin::toggle("by_region", count($byRegionConfigs));

		$this->createAdd("region_list", "RegionListComponent", array(
			"list" => $byRegionConfigs,
			"areas" => $areas
		));
		//地域ごとの代引き手数料設定ここまで

		$this->createAdd("item_list", "PaymentDaibikiForbiddenItemListComponent", array(
			"list" => PaymentDaibikiUtil::getForbiddenConfig(true)
		));

		$config = PaymentDaibikiUtil::getConfig();

		$this->addCheckBox("include_delivery_price", array(
			"name" => "Config[include_delivery_price]",
			"value" => 1,
			"selected" => (isset($config["include_delivery_price"]) && $config["include_delivery_price"] == 1),
			"label" => "カートで代引き計算時に送料も加味する"
		));

		$this->addCheckBox("order_auto_calc", array(
			"name" => "Config[auto_calc]",
			"value" => 1,
			"selected" => (isset($config["auto_calc"]) && $config["auto_calc"] == 1),
			"label" => "注文の変更の際、自動で代引きの金額を算出して登録する"
		));

		$this->addTextArea("description", array(
			"name" => "payment_daibiki[description]",
			"value" => PaymentDaibikiUtil::getDescriptionConfig()
		));

		$this->addTextArea("mail", array(
			"name" => "payment_daibiki[mail]",
			"value" => PaymentDaibikiUtil::getMailConfig()
		));
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}
}
