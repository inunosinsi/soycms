<?php

class PaymentDaibikiConfigFormPage extends WebPage{

	private $config;

	function PaymentDaibikiConfigFormPage() {
		SOY2::imports("module.plugins.payment_daibiki.component.*");
	}

	function doPost(){
		if(soy2_check_token() && isset($_POST["payment_daibiki"])){
			try{

				//代引き手数料
				if(isset($_POST["payment_daibiki"]["price_table"])){
					$keys = $_POST["payment_daibiki"]["price_table"]["key"];
					$array = $_POST["payment_daibiki"]["price_table"]["price"];

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

					SOYShop_DataSets::put("payment_daibiki.price",$res);
				}

				//説明文
				if(isset($_POST["payment_daibiki"]["description"])){
					$description = $_POST["payment_daibiki"]["description"];
					SOYShop_DataSets::put("payment_daibiki.description",$description);
				}

				//メール
				if(isset($_POST["payment_daibiki"]["mail"])){
					$mail = $_POST["payment_daibiki"]["mail"];
					SOYShop_DataSets::put("payment_daibiki.mail",$mail);
				}

				//代引き不可商品
				if(isset($_POST["payment_daibiki"]["item_table"])){
					$forbidden = $_POST["payment_daibiki"]["item_table"];

					//空と重複を削除
					$forbidden = array_merge(array_unique(array_diff(array_map("trim",$forbidden), array(""))), array());

					SOYShop_DataSets::put("payment_daibiki.forbidden",$forbidden);

				}

				$this->config->redirect("updated");
			}catch(Exception $e){
				$this->config->redirect("error");
			}
		}
	}

	function execute(){

		WebPage::WebPage();

		$this->buildForm();

		$this->addModel("updated", array(
			"visible" => isset($_GET["updated"])
		));
		$this->addModel("error", array(
			"visible" => isset($_GET["error"])
		));
	}

	function buildForm(){

		$this->addForm("config_form", array(

		));

		$this->createAdd("price_list", "PaymentDaibikiPriceListComponent", array(
			"list" => $this->config->getPrices(true)
		));

		$this->createAdd("item_list", "PaymentDaibikiForbiddenItemListComponent", array(
			"list" => $this->config->getItems(true)
		));

		$this->addTextArea("description", array(
			"value" => $this->config->getDescription()
		));

		$this->addTextArea("mail", array(
			"value" => $this->config->getMailText()
		));
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}
}
?>