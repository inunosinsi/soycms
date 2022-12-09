<?php

class PayJpPage extends MainMyPagePageBase{

	private $token;
	private $payJpLogic;

	function doPost(){
		if(soy2_check_token() && soy2_check_referer()){
			//登録
			if(isset($_POST["register"]) || isset($_POST["register_x"])){

				$card = "";
				foreach($_POST["card"] as $c){
					$card .= $c;
				}

				$myCard = array(
					"number" => $card,
					"cvc" => $_POST["cvc"],
					"exp_month" => $_POST["month"],
					"exp_year" => (20 . $_POST["year"])
				);

				//セッションに入れる
				PayJpUtil::save("myCard", $myCard);
				PayJpUtil::save("name", $_POST["name"]);

				$customer["card"] = $myCard;
				$customer["card"]["name"] = PayJpUtil::get("name");
				$customer["email"] = $this->getUser()->getMailAddress();

				list($res, $err) = $this->payJpLogic->registCustomer($customer);
				$token = (!is_null($res)) ? $res->id : null;
				if(isset($token)){
					$this->payJpLogic->saveCustomerTokenByUserId($token, $this->getUser()->getId());
					PayJpUtil::clear("myCard");
					PayJpUtil::clear("name");
					$this->jump("credit/payJp?updated");
					exit;
				}

				/** @ToDo エラーコードを出したい **/
			}

			//削除
			if($_POST["remove"]){
				list($res, $err) = $this->payJpLogic->retrieveCustomer($this->token);

				if(isset($res)){
					$res->delete();
					$this->jump("credit/payJp?updated");
					exit;
				}
			}
			$this->jump("credit/payJp?failed");
			exit;
		}
	}

	function __construct(){
		$this->checkIsLoggedIn(); //ログインチェック

		//PAY.JPモジュールがインストールされているか？
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("payment_pay_jp")){
			$this->jump("top");
		}

		SOY2::import("module.plugins.payment_pay_jp.util.PayJpUtil");
		$this->payJpLogic = SOY2Logic::createInstance("module.plugins.payment_pay_jp.logic.PayJpLogic");
		$this->payJpLogic->initPayJp();
		$this->token = $this->payJpLogic->getCustomerTokenByUserId($this->getUser()->getId());

		parent::__construct();

		list($res, $err) = $this->payJpLogic->retrieveCustomer($this->token);

		$params = array();
		if(isset($res)){
			$card = $res->cards->data[0];
			$params["last4"] = $card->last4;
			$params["expired"] = $card->exp_month . " / " . $card->exp_year . " (月 / 年)";
			$params["name"] = $card->name;
		}

		DisplayPlugin::toggle("no_token", !count($params));
		DisplayPlugin::toggle("has_token", count($params));

		$this->addForm("form");

		self::buildRegisterForm();
		self::buildList($params);

		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_top_url(),
		));
	}

	private function buildRegisterForm(){
		$values = PayJpUtil::get("myCard");

		for ($i = 0; $i < 4; $i++) {
			$this->addInput("card_" . ($i + 1), array(
				"name" => "card[$i]",
				"value" => (isset($values["number"])) ? substr($values["number"], (4*$i), 4) : "",
				"attr:required" => true
			));
		}

		$this->addSelect("month", array(
			"name" => "month",
			"options" => range(1, 12),
			"selected" => (isset($values["exp_month"])) ? $values["exp_month"] : ""
		));
		$this->addSelect("year", array(
			"name" => "year",
			"options" => self::getYearRange(),
			"selected" => (isset($values["exp_year"])) ? substr($values["exp_year"], 2) : ""
		));

		$this->addInput("cvc", array(
			"name" => "cvc",
			"value" => (isset($values["cvc"])) ? $values["cvc"] : "",
			"attr:required" => true
		));

		$this->addInput("name", array(
			"name" => "name",
			"value" => PayJpUtil::get("name"),
			"attr:required" => true
		));
	}

	private function buildList($params){
		//各種情報
		$this->addLabel("card", array(
			"text" => (isset($params["last4"])) ? "****-****-****-" . $params["last4"] : ""
		));

		$this->addLabel("expired", array(
			"text" => (isset($params["expired"])) ? $params["expired"] : ""
		));

		$this->addLabel("name", array(
			"text" => (isset($params["name"])) ? $params["name"] : ""
		));
	}

	private function getYearRange(){
		$year = date("y");
		$array = array();
		$end = (int)$year + 10;

		for($i = $year; $i <= $end; $i++){
			$array[$i] = $i;
		}
		return $array;
	}
}
