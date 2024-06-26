<?php

class addressPage extends MainMyPagePageBase{

	const MODE_SEND = "send";		//送り先住所
	const MODE_CLAIMED = "claimed";	//請求先住所

	private $mode;
	private $orderId;
	private $userId;

	function doPost(){
		if(soy2_check_token()){
			/*
			 * 宛先の入力値
			 */
			$address = $_POST["Address"];

			//名前のデータの整形
			$address["name"] = $this->_trim($address["name"]);
			$address["reading"] = $this->convertKana($address["reading"]);

			$mypage = $this->getMyPage();

			//エラーがなければ確認へ
			if(!self::_checkError($address)){
				$order = $this->getOrderByIdAndUserId($this->orderId, $this->userId);
				$change = self::_updateAddress($order, $address);

				$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
				$orderDao->begin();

				if(count($change)) SOY2Logic::createInstance("logic.order.OrderLogic")->addHistory($this->orderId, implode("\n", $change), "", "顧客:" . $this->getUser()->getName());

				try{
					$orderDao->update($order);
					$mypage->clearAttribute("address_" . $this->mode);
					$mypage->save();

					//変更履歴のメールを送信する
					$mailLogic = SOY2Logic::createInstance("module.plugins.order_edit_on_mypage.logic.NoticeSendMailLogic", array("order" => $order, "user" => $this->getUser()));
					$mailLogic->send(implode("\n", $change));

					$orderDao->commit();

					//キャッシュの削除
					SOY2Logic::createInstance("module.plugins.order_edit_on_mypage.logic.HistoryIdCacheLogic")->removeCache();

					$this->jump("order/edit/address/" . $this->mode . "/" . $this->orderId . "?updated");
				}catch(Exception $e){
					//
				}
			}

			//エラーの場合はセッションに値を保持
			$mypage->setAttribute("address_" . $this->mode, $address);
			$mypage->save();
			$this->jump("order/edit/address/" . $this->mode . "/" . $this->orderId . "?error");
		}
	}

	function __construct($args){
		$this->checkIsLoggedIn(); //ログインチェック

        //orderIdがない場合はorderトップへ戻す
        if(!isset($args[1]) || !SOYShopPluginUtil::checkIsActive("order_edit_on_mypage")) $this->jump("order");
		$this->orderId = (int)$args[0];
		$this->mode = htmlspecialchars($args[1], ENT_QUOTES, "UTF-8");
		$this->userId = (int)$this->getUser()->getId();

		//すでに発送してしまった場合は表示しない
		if(!$this->checkUnDeliveried($this->orderId, $this->userId)) $this->jump("order");

		parent::__construct();

		DisplayPlugin::toggle("updated", isset($_GET["updated"]));

		$this->addLabel("mode_name", array(
			"text" => ($this->mode == self::MODE_SEND) ? "お届け" : "請求"
		));

		self::_buildForm();

		$mypage = $this->getMyPage();
		DisplayPlugin::toggle("has_error", $mypage->hasError());
		self::_appendErrors($mypage);

		$this->addLink("back_link", array(
			"link" => soyshop_get_mypage_url() . "/order/detail/" . $this->orderId
		));
	}

	private function _buildForm(){
		$order = $this->getOrderByIdAndUserId($this->orderId, $this->userId);
        if(!$order->isOrderDisplay()) $this->jump("order");

		$this->addLabel("order_number", array(
			"text" => $order->getTrackingNumber()
		));

		$requireText = SOYShop_ShopConfig::load()->getRequireText();
		$this->addLabel("required_label", array(
			"text" => (strlen($requireText)) ? $requireText : "必須"
		));


		$address = $this->getMyPage()->getAttribute("address_" . $this->mode);
		if(is_null($address)) $address = ($this->mode == self::MODE_SEND) ? $order->getAddressArray() : $order->getClaimedAddressArray();

		$this->addForm("form");

		foreach(array("name", "reading", "office", "zipCode", "area", "telephoneNumber") as $t){
			switch($t){
				case "area":
					$this->addSelect($t, array(
						"name" => "Address[area]",
						"options" => SOYShop_Area::getAreas(),
						"value" => (isset($address[$t])) ? $address[$t] : null,
					));
					break;
				default:
					$this->addInput(strtolower($t), array(
						"name" => "Address[" . $t . "]",
						"value" => (isset($address[$t])) ? $address[$t] : "",
					));
			}
		}

		SOY2::import("util.SOYShopAddressUtil");
		$addressItems = SOYShopAddressUtil::getAddressItems();
		for($i = 1; $i <= 4; $i++){
			$itemCnf = (isset($addressItems[$i - 1])) ? $addressItems[$i - 1] : SOYShopAddressUtil::getEmptyAddressItem();

			$this->addModel("address" . $i . "_show", array(
				"visible" => (isset($itemCnf["label"]) && strlen($itemCnf["label"]))
			));

			$this->addInput("address" . $i, array(
				"name" => "Address[address" . $i . "]",
				"value" => (isset($address["address". $i])) ? $address["address". $i] : "",
			));

			foreach(array("label", "example") as $idx){
				$this->addLabel("address". $i . "_" . $idx, array(
					"text" => (isset($itemCnf[$idx])) ? $itemCnf[$idx] : ""
				));
			}
		}
	}

	/**
	 * エラー周りを設定
	 */
	private function _appendErrors(MyPageLogic $mypage){

		$this->createAdd("name_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("name")
		));

		$this->createAdd("reading_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("reading")
		));

		$this->createAdd("zipcode_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("zipcode")
		));
		
		$this->createAdd("address_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("address")
		));

		$this->createAdd("telephonenumber_error", "ErrorMessageLabel", array(
			"text" => $mypage->getErrorMessage("telephonenumber")
		));

	}

	private function _checkError(array $address){
		$res = false;
		$mypage = $this->getMyPage();
		$mypage->clearErrorMessage();

		if(tstrlen($address["name"]) < 1){
			$mypage->addErrorMessage("name", MessageManager::get("USER_NAME_EMPTY"));
			$res = true;
		}

		$reading = str_replace(array(" ","　"), "", $address["reading"]);
		if(tstrlen($reading) < 1){
			$mypage->addErrorMessage("reading", MessageManager::get("USER_READING_EMPTY"));
			$res = true;
		}

		if(strlen(mb_ereg_replace("([-_a-zA-Z0-9ァ-ー０-９])", "", $reading)) !== 0){
			$mypage->addErrorMessage("reading", MessageManager::get("USER_READING_FALSE"));
			$res = true;
		}

		if(tstrlen($address["zipCode"]) < 1){
			$mypage->addErrorMessage("zipcode", MessageManager::get("ZIP_CODE_EMPTY"));
			$res = true;
		}

		if(tstrlen($address["area"]) < 1){
			$mypage->addErrorMessage("address", MessageManager::get("ADDRESS_EMPTY"));
			$res = true;
		}

		if(tstrlen($address["telephoneNumber"]) < 1){
			$mypage->addErrorMessage("telephonenumber", MessageManager::get("TELEPHONE_NUMBER_EMPTY"));
			$res = true;
		}

		SOY2::import("util.SOYShopAddressUtil");
		$addressItems = SOYShopAddressUtil::getAddressItems();
		for($i = 1; $i <= 4; $i++){
			$itemCnf = (isset($addressItems[$i - 1])) ? $addressItems[$i - 1] : SOYShopAddressUtil::getEmptyAddressItem();

			if($itemCnf["required"]){
				if(tstrlen($address["address" . $i]) < 1){
					$mypage->addErrorMessage("address", MessageManager::get("ADDRESS_EMPTY"));
					$res = true;
					break;
				}
			}
		}

		$mypage->save();

		return $res;
	}

	/**
	 * @param SOYShop_Order, array
	 * @return array
	 */
	private function _updateAddress(SOYShop_Order $order, array $newAddress){
		$change = array();
		$label = ($this->mode == self::MODE_SEND) ? "宛先" : "請求先";
		$address = ($this->mode == self::MODE_SEND) ? $order->getAddressArray() : $order->getClaimedAddressArray();

		foreach(array("office", "name", "reading", "zipCode", "telephoneNumber") as $idx){
			if(isset($newAddress[$idx]) && $address[$idx] != $newAddress[$idx])	$change[] = $this->getHistoryText($label, $address[$idx], $newAddress[$idx]);
		}

		for($i = 1; $i <= 4; $i++){
			if(isset($newAddress["address" . $i]) && $address["address" . $i] != $newAddress["address" . $i]) $change[] = $this->getHistoryText($label, $address["address" . $i], $newAddress["address" . $i]);
		}
		
		if(isset($newAddress["area"]) && $address["area"] != $newAddress["area"]) $change[] = $this->getHistoryText($label, SOYShop_Area::getAreaText($address["area"]), SOYShop_Area::getAreaText($newAddress["area"]));
		
		if(($this->mode == self::MODE_SEND)){
			$order->setAddress($newAddress);
		}else{
			$order->setClaimedAddress($newAddress);
		}

		return $change;
	}
}
