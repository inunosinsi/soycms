<?php
SOY2::import("domain.order.SOYShop_ItemModule");
SOY2::import("module.plugins.common_item_option.util.ItemOptionUtil");
SOYShopPlugin::load("soyshop.item.option");
SOYShopPlugin::load("soyshop.item.order");
SOYShopPlugin::load("soyshop.order.customfield");
SOYShopPlugin::load("soyshop.order.edit");
class EditPage extends WebPage{

	private $id;

	function doPost(){
		if(!AUTH_OPERATE) return;	//操作権限がないアカウントの場合は以後のすべての動作を封じる

		if(soy2_check_token()){

			$logic = SOY2Logic::createInstance("logic.order.OrderLogic");
			$order = $logic->getById($this->id);
			$itemOrders = $logic->getItemsByOrderId($this->id);

			$change = array();

			if(isset($_POST["ClaimedAddress"])){
				$_change = self::updateClaimedAddress($order, $_POST["ClaimedAddress"]);
				$change = array_merge($change, $_change);
			}

			if(isset($_POST["Address"])){
				$_change = self::updateOrderAddress($order, $_POST["Address"]);
				$change = array_merge($change, $_change);
			}

			if(isset($_POST["Payment"])){
				$_change = self::updateOrderPayment($order, $_POST["Payment"]);
				$change = array_merge($change, $_change);
			}

			if(isset($_POST["Attribute"])){
				$_change = self::updateOrderAttribute($order, $_POST["Attribute"]);
				$change = array_merge($change, $_change);
			}

			if(isset($_POST["Customfield"])){
				$_change = self::updateOrderCustomfield($order, $_POST["Customfield"]);
				$change = array_merge($change, $_change);
			}

			if(isset($_POST["Module"])){
				$_change = self::updateOrderModules($order, $_POST["Module"]);
				$change = array_merge($change, $_change);
			}

			if(
				isset($_POST["AddModule"])
				 && isset($_POST["AddModule"]["name"]) && isset($_POST["AddModule"]["price"])
				 && is_array($_POST["AddModule"]["name"]) && is_array($_POST["AddModule"]["price"])
			){
				$modules = $order->getModuleList();
				foreach($_POST["AddModule"]["name"] as $key => $value){
					$name = trim($_POST["AddModule"]["name"][$key]);
					$price = trim($_POST["AddModule"]["price"][$key]);
					$isInclude = (isset($_POST["AddModule"]["isInclude"][$key]) && $_POST["AddModule"]["isInclude"][$key] == 1);

					if(strlen($name) > 0 && strlen($price) > 0){
						$module = new SOYShop_ItemModule();
						$moduleId = "added_module." . time() . $key;
						$module->setId($moduleId);
						$module->setName($name);
						$module->setPrice($price);
						$module->setIsInclude($isInclude);

						$modules[$moduleId] = $module;

						$change[] = $name."（" . $price . "円）を追加しました。";
					}
				}
				$order->setModules($modules);
			}

			$itemChange = array();

			SOY2::import("domain.config.SOYShop_ShopConfig");
			$cnf = SOYShop_ShopConfig::load();

			if(isset($_POST["Item"])){
				$newItems = $_POST["Item"];
				foreach($itemOrders as $id => $itemOrder){
					$key = $itemOrder->getId();
					if(isset($newItems[$key])){
						$newName  = (isset($newItems[$key]["itemName"])) ? $newItems[$key]["itemName"] : "";
						$newPrice = (isset($newItems[$key]["itemPrice"])) ? $newItems[$key]["itemPrice"] : 0;
						$newCount = (isset($newItems[$key]["itemCount"])) ? $newItems[$key]["itemCount"] : 0;
						$newAttributes = (isset($newItems[$key]["attributes"])) ? $newItems[$key]["attributes"] : array();
						$delete   = ( isset($newItems[$key]["itemDelete"]) && $newItems[$key]["itemDelete"] );

						//商品オプションの配列はシリアライズしておく
						if(count($newAttributes) > 0){
							$newItems[$key]["attributes"] = (isset($newItems[$key]["attributes"])) ? soy2_serialize($newItems[$key]["attributes"]) : "";
						}

						//注文数が0個の場合や商品名が空の場合も削除として扱う
						$delete = $delete || $newCount == 0 || strlen($newName) == 0;
						$updateCount = 0;	//商品個数の変更 マイナスの数字も含む

						$item = soyshop_get_item_object($itemOrder->getItemId());
						if($delete){
							$itemCode = (strlen($item->getCode()) > 0) ? $item->getCode() : $itemOrder->getItemId();

							$itemChange[] = $itemOrder->getItemName() . "（" . $itemCode." " . $itemOrder->getItemPrice() . "円×" . $itemOrder->getItemCount() . "点）を削除しました。";
							$newItems[$key]["itemCount"] = 0;
							$updateCount = 0 - (int)$itemOrder->getItemCount();
						}else{
							if($newName != $itemOrder->getItemName()) $itemChange[] = self::getHistoryText($itemOrder->getItemName(), $itemOrder->getItemName(), $newName);
							if($newPrice != $itemOrder->getItemPrice()) $itemChange[] = self::getHistoryText($itemOrder->getItemName() . "の単価", $itemOrder->getItemPrice(), $newPrice);
							if($newCount != $itemOrder->getItemCount()) {
								$itemChange[] = self::getHistoryText($itemOrder->getItemName() . "の個数", $itemOrder->getItemCount(), $newCount);
								$updateCount = (int)$newCount - (int)$itemOrder->getItemCount();
							}

							$orderAttributes = (count($itemOrder->getAttributeList()) > 0) ? $itemOrder->getAttributeList() : self::getOptionIndex();

							//商品オプションの比較
							foreach($newAttributes as $index => $attribute){
								$delegate = SOYShopPlugin::invoke("soyshop.item.option", array(
									"mode" => "edit",
									"key" => $index
								));
								if($attribute != $orderAttributes[$index]) $itemChange[] = self::getHistoryText($itemOrder->getItemName() . "の『" . $delegate->getLabel() . "』", $orderAttributes[$index] , $attribute);
							}
						}

						SOY2::cast($itemOrder, (object)$newItems[$key]);
						$itemOrder->setTotalPrice($itemOrder->getItemPrice() * $itemOrder->getItemCount());
						$itemOrders[$id] = $itemOrder;

						//在庫の変更 0でない場合　マイナスも含む
						if($updateCount !== 0) self::changeStock($itemOrder, $updateCount);
					}
				}
			}

			$newItemOrders = array();

			if(
				isset($_POST["AddItemByName"])
				 && isset($_POST["AddItemByName"]["name"]) && isset($_POST["AddItemByName"]["price"]) && isset($_POST["AddItemByName"]["count"])
				 && is_array($_POST["AddItemByName"]["name"]) && is_array($_POST["AddItemByName"]["price"]) && is_array($_POST["AddItemByName"]["count"])
			){
				$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
				foreach($_POST["AddItemByName"]["name"] as $key => $value){
					$name = (isset($_POST["AddItemByName"]["name"][$key])) ? trim($_POST["AddItemByName"]["name"][$key]) : "";
					if(!strlen($name)) continue;
					$price = (isset($_POST["AddItemByName"]["price"][$key]) && is_numeric($_POST["AddItemByName"]["price"][$key])) ? (int)trim($_POST["AddItemByName"]["price"][$key]) : 0;
					if(!$cnf->getAllowRegistrationZeroYenProducts() && $price === 0) continue;	//0円商品をカートに入れる事を許可しない

					$count = (isset($_POST["AddItemByName"]["count"][$key]) && is_numeric($_POST["AddItemByName"]["count"][$key])) ? (int)trim($_POST["AddItemByName"]["count"][$key]) : 1;
					if(!$cnf->getAllowRegistrationZeroQuantityProducts() && $count === 0) continue;	//0円商品をカートに入れる事を許可しない

					$itemId = 0;//ない商品はid=0

					$itemOrder = new SOYShop_ItemOrder();
					$itemOrder->setOrderId($this->id);
					$itemOrder->setItemId($itemId);
					$itemOrder->setItemCount($count);
					$itemOrder->setItemPrice($price);
					$itemOrder->setTotalPrice($price * $count);
					$itemOrder->setItemName($name);

					$newItemOrders[] = $itemOrder;
					$itemChange[] = $itemOrder->getItemName() . "（" . $itemOrder->getItemPrice() . "円×" . $itemOrder->getItemCount() . "点）を追加しました。";

					//在庫数の変更
					self::changeStock($itemOrder, $count);
				}

				//検索用のセッションのクリア
				SOY2ActionSession::getUserSession()->setAttribute("Order.Register.Item.Search:search_condition", null);
			}

			if(
				isset($_POST["AddItemByCode"])
				 && isset($_POST["AddItemByCode"]["code"]) && isset($_POST["AddItemByCode"]["count"])
				 && is_array($_POST["AddItemByCode"]["code"]) && is_array($_POST["AddItemByCode"]["count"])
			){
				$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
				foreach($_POST["AddItemByCode"]["count"] as $key => $value){
					$code = trim($_POST["AddItemByCode"]["code"][$key]);
					$count = trim($_POST["AddItemByCode"]["count"][$key]);
					if($count > 0){
						if(strlen($code)){
							try{
								$item = $dao->getByCode($code);
							}catch(Exception $e){
								continue;
							}
						}elseif(isset($_POST["AddItemByCode"]["name"][$key]) && strlen($_POST["AddItemByCode"]["name"][$key])){
							$name = trim($_POST["AddItemByCode"]["name"][$key]);
							try{
								$item = $dao->getByName($name);
							}catch(Exception $e){
								continue;
							}
						}else{
							continue;
						}

						$itemOrder = new SOYShop_ItemOrder();
						$itemOrder->setOrderId($this->id);
						$itemOrder->setItemId($item->getId());
						$itemOrder->setItemCount($count);
						$itemOrder->setItemPrice($item->getSellingPrice());
						$itemOrder->setTotalPrice($item->getSellingPrice() * $count);
						$itemOrder->setItemName($item->getName());

						$newItemOrders[] = $itemOrder;
						$itemChange[] = $itemOrder->getItemName() . "（" . $item->getCode() . " " . $itemOrder->getItemPrice() . "円×" . $itemOrder->getItemCount() . "点）を追加しました。";

						//在庫数の変更
						self::changeStock($itemOrder, $count);
					}
				}
			}

			//変更実行
			$isChange = (count($change) || count($itemChange));
			if($isChange){

				/*
				 *
				 * * 料金を再計算
				 */
				$price = 0;
				$reducedRatePrice = 0;	//軽減税率の方の商品合計

				SOY2::import("module.plugins.common_consumption_tax.util.ConsumptionTaxUtil");

				foreach($itemOrders as $itemOrder){
					if(ConsumptionTaxUtil::isReducedTaxRateItem($itemOrder->getItemId())){	//軽減税率対象商品
						$reducedRatePrice += $itemOrder->getTotalPrice();
					}else{
						$price += $itemOrder->getTotalPrice();
					}
				}
				foreach($newItemOrders as $itemOrder){
					if(ConsumptionTaxUtil::isReducedTaxRateItem($itemOrder->getItemId())){	//軽減税率対象商品
						$reducedRatePrice += $itemOrder->getTotalPrice();
					}else{
						$price += $itemOrder->getTotalPrice();
					}
				}

				$modules = $order->getModuleList();

				//モジュール分の加算
				$modulePrice = 0;

				foreach($modules as $moduleId => $module){

					//税金関係の場合はモジュールから削除して、後で再計算して登録
					if($moduleId === "consumption_tax") {
						unset($modules[$moduleId]);
						continue;
					}

					//モジュールの再計算のための拡張ポイントを利用する
					$moduleObj = soyshop_get_plugin_object($moduleId);
					if(!is_null($moduleObj->getId())){
						SOYShopPlugin::load("soyshop.order.module", $moduleObj);
						$delegate = SOYShopPlugin::invoke("soyshop.order.module", array(
							"mode" => "edit",
							"module" => $module,
							"orderId" => $this->id,
							"total" => $price,
							"itemOrders" => array_merge($itemOrders, $newItemOrders)
						));
						//プラグインを介した場合は配列を上書きする
						if(!is_null($delegate->getModule())){
							$module = $delegate->getModule();
							$modules[$moduleId] = $module;
						}
					}

					//モジュールを合算に含めるか調べてから足す
					if(!$module->getIsInclude()) $modulePrice += $module->getPrice();
				}


				//税金の再計算
				$module = self::calculateConsumptionTax($price, $modulePrice, $reducedRatePrice);
				if(isset($module)) $modules["consumption_tax"] = $module;

				//軽減税率対象商品分も加味して合算
				$price += $reducedRatePrice + $modulePrice;

				//外税の場合は加算
				if(isset($modules["consumption_tax"]) && !$modules["consumption_tax"]->getIsInclude()) $price += $modules["consumption_tax"]->getPrice();

				$order->setModules($modules);
				$order->setPrice($price);

				/*
				 * 保存
				 */
				$dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
				$itemOrderDAO = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");

				try{
					$dao->begin();
					$dao->update($order);

					//itemOrder 在庫数の操作はいまのところなし
					if(count($itemChange) > 0){
						foreach($itemOrders as $itemOrder){
							//注文数が空の場合は削除
							if(!$cnf->getAllowRegistrationZeroQuantityProducts() && $itemOrder->getItemCount() == 0){
								$itemOrderDAO->delete($itemOrder);
							}else{
								$itemOrderDAO->update($itemOrder);
							}
						}
						//追加商品
						SOYShopPlugin::load("soyshop.item.order");
						foreach($newItemOrders as $itemOrder){
							if(!$cnf->getAllowRegistrationZeroQuantityProducts() && $itemOrder->getItemCount() === 0) continue;
							$itemOrderId = $itemOrderDAO->insert($itemOrder);
							SOYShopPlugin::invoke("soyshop.item.order", array(
								"mode" => "order",
								"itemOrderId" => $itemOrderId
							));
						}

						SOYShopPlugin::invoke("soyshop.item.order", array(
							"mode" => "complete",
							"orderId" => $order->getId()
						));

						self::insertHistory($this->id, implode("\n", $itemChange));
					}

					//history
					if(count($change) > 0){
						self::insertHistory($this->id, implode("\n", $change));
					}

					$dao->commit();
				}catch(Exception $e){
					$dao->rollback();
					SOY2PageController::jump("Order.Edit." . $this->id . "?failed");
				}
			}

			SOYShopPlugin::load("soyshop.order.edit");
			SOYShopPlugin::invoke("soyshop.order.edit", array(
				"orderId" => $order->getId(),
				"mode" => "update",
				"isChange" => $isChange
			));

			//エラーがあった時に何らかの事をする
			SOYShopPlugin::invoke("soyshop.order.edit", array(
				"orderId" => $order->getId(),
				"mode" => "error",
				"isChange" => $isChange
			));

			//変更なし
			SOY2PageController::jump("Order.Edit." . $this->id . "?updated");
		}
	}

	//税金の計算 $reducedRateTotalは軽減税率商品金額の合算
	private function calculateConsumptionTax($price, $modulePrice, $reducedRateTotal){
		$config = SOYShop_ShopConfig::load();

		$total = $price;

		//モジュール分の加算
		if($config->getConsumptionTaxInclusiveCommission()){
			$total += $modulePrice;
		}

		//$cart->calculateConsumptionTax();
		if($config->getConsumptionTax() == SOYShop_ShopConfig::CONSUMPTION_TAX_MODE_ON){
			//外税(プラグインによる処理)
			return self::setConsumptionTax($config, $total, $reducedRateTotal);
		}elseif($config->getConsumptionTaxInclusivePricing() == SOYShop_ShopConfig::CONSUMPTION_TAX_MODE_ON){
			//内税(標準実装)
			return self::setConsumptionTaxInclusivePricing($config, $total);

		}else{
			//何もしない
		}

		return null;
	}

	//外税 $reducedRateTotalは軽減税率商品金額の合算
	private function setConsumptionTax($config, $total, $reducedRateTotal){
		$pluginId = $config->getConsumptionTaxModule();
		if(!isset($pluginId)) return null;

		$plugin = soyshop_get_plugin_object($pluginId);
   		if(is_null($plugin->getId()) || $plugin->getIsActive() == SOYShop_PluginConfig::PLUGIN_INACTIVE) return null;

   		SOYShopPlugin::load("soyshop.tax.calculation", $plugin);
		return SOYShopPlugin::invoke("soyshop.tax.calculation", array(
			"mode" => "edit",
			"total" => $total,
			"reducedRateTotal" => $reducedRateTotal	//軽減税率商品金額の合計
		))->getModule();
	}

	//内税
	private function setConsumptionTaxInclusivePricing($config, $total){
		$taxRate = (int)$config->getConsumptionTaxInclusivePricingRate();	//内税率

		if($taxRate === 0) return null;

		$module = new SOYShop_ItemModule();
		$module->setId("consumption_tax");
		$module->setName("内税");
		$module->setType(SOYShop_ItemModule::TYPE_TAX);	//typeを指定しておくといいことがある
		//内税の計算は8%の場合はtax = total / 1.08で計算する
		$module->setPrice(floor($total - ($total / (1 + $taxRate / 100))));
		$module->setIsInclude(true);	//合計に合算されない

		return $module;
	}

	private function getOptionIndex(){
		$opts = ItemOptionUtil::getOptions();
		if(!count($opts)) return array();

		$empty = array();
		foreach($opts as $key => $conf){
			$empty[$key] = "";
		}

		return $empty;
	}

	private function getHistoryText($label, $old, $new){
		return $label . "を『" . $old . "』から『" . $new . "』に変更しました";
	}

	private function insertHistory($id, $content, $more = null){
		static $historyDAO;
		if(!$historyDAO) $historyDAO = SOY2DAOFactory::create("order.SOYShop_OrderStateHistoryDAO");

		$history = new SOYShop_OrderStateHistory();
		$history->setOrderId($id);
		$history->setAuthor(SOY2Logic::createInstance("logic.order.OrderHistoryLogic")->getAuthor());
		$history->setContent($content);
		$history->setMore($more);
		$historyDAO->insert($history);
	}

	function __construct($args) {
		if(!AUTH_OPERATE || !isset($args[0])) SOY2PageController::jump("Order");
		$this->id = (int)$args[0];

		MessageManager::addMessagePath("admin");

		SOY2::import("domain.config.SOYShop_ShopConfig");

		parent::__construct();

		$order = soyshop_get_order_object($this->id);
		if(is_null($order->getId())) SOY2PageController::jump("Order");

		//エラーメッセージ等
		SOYShopPlugin::load("soyshop.order.edit");
		$msgs = SOYShopPlugin::invoke("soyshop.order.edit", array("orderId" => $order->getId(),"mode" => "message"))->getMessages();
		if(is_null($msgs)) $msgs = array();

		$this->createAdd("message_list", "_common.Order.EditPageMessageListComponent", array(
			"list" => $msgs,
		));

		//言語設定
		$attrs = $order->getAttributeList();
		$lng = (isset($attrs["util_multi_language"]["value"])) ? $attrs["util_multi_language"]["value"] : "jp";
		if(!defined("SOYSHOP_ADMIN_LANGUAGE")) define("SOYSHOP_ADMIN_LANGUAGE", $lng);

		$this->addLink("order_detail_link", array(
			"link" => SOY2PageController::createLink("Order.Detail." . $order->getId())
		));

		self::buildForm($order);

		//未登録商品の追加ボタンの有無
		DisplayPlugin::toggle("allow_add_unregistered_item", SOYShop_ShopConfig::load()->getIsUnregisteredItem());

		//HTMLの自由記述
		$this->addLabel("extension_html", array(
			"html" => SOYShopPlugin::invoke("soyshop.order.edit", array("mode" => "html"))->getHTML()
		));
	}

	private function buildForm(SOYShop_Order $order){
		$logic = SOY2Logic::createInstance("logic.order.OrderLogic");

		$this->addForm("update_form", array(
			"enctype" => "multipart/form-data"
		));

		$this->addLabel("order_name_text", array(
			"text" => $order->getTrackingNumber()
		));

		$this->addLabel("order_id", array(
			"text" => $order->getTrackingNumber()
		));
		$this->addLabel("order_raw_id", array(
			"text" => $order->getId()
		));

		$this->addLabel("order_date", array(
			"text" => date('Y-m-d H:i', $order->getOrderDate())
		));

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Order.Detail." . $order->getId())
		));

		$this->addLink("edit_link", array(
			"link" => SOY2PageController::createLink("Order.Edit." . $order->getId())
		));

		$this->addLabel("order_status", array(
			"text" => $order->getOrderStatusText(),
		));

		$this->addLabel("payment_status", array(
			"text" => $order->getPaymentStatusText()
		));

		$this->addLabel("order_price", array(
			"text" => soy2_number_format($order->getPrice()) . " 円"
		));

		//支払い方法の変更
		$paymentModuleList = self::getPaymentMethodList();
		DisplayPlugin::toggle("payment_method", count($paymentModuleList) > 0);

		$this->createAdd("payment_method_list", "_common.Order.PaymentMethodListComponent", array(
			"list" => $paymentModuleList,
			"selected" => self::getSelectedPaymentMethod($order->getModuleList())
		));

		$attrs = $order->getAttributeList();
		$isDeliveryTime = self::isDeliveryTimeItem($attrs);
		$deliveryTimeConfigForm = ($isDeliveryTime) ? self::getDeliveryTimeItemConfig($order) : "";
		$isDeliveryTime = (strlen($deliveryTimeConfigForm) > 0);

		DisplayPlugin::toggle("delivery_time", $isDeliveryTime);
		$this->addLabel("delivery_time_config", array(
			"html" => $deliveryTimeConfigForm
		));

		$this->createAdd("attribute_list", "_common.Order.AttributeFormListComponent", array(
			"list" => self::addExtendAttributes($attrs)
		));

		$this->createAdd("customfield_list", "_common.Order.CustomFieldFormListComponent", array(
			"list" => $this->getCustomfield($order->getId())
		));

		/*** 顧客情報 ***/
		SOY2DAOFactory::importEntity("user.SOYShop_User");
		SOY2DAOFactory::importEntity("config.SOYShop_Area");

		try{
			$customer = SOY2DAOFactory::create("user.SOYShop_UserDAO")->getById($order->getUserId());
		}catch(Exception $e){
			$customer = new SOYShop_User();
			$customer->setName("[deleted]");
		}
		$this->addLink("customer", array(
			"text" => $customer->getName(),
			"link" => SOY2PageController::createLink("User.Detail." . $customer->getId())
		));
		$this->addLabel("customer_name", array(
			"text" => $customer->getName(),
		));
		$this->addModel("show_customer_area", array(
			"visible" => strlen(SOYShop_Area::getAreaText($customer->getArea())),
		));
		$this->addLabel("customer_area", array(
			"text" => SOYShop_Area::getAreaText($customer->getArea()),
		));
		$this->addLink("customer_email", array(
			"text" => "<" . $customer->getMailAddress() . ">",
			"link" => strlen($customer->getMailAddress()) ? "mailto:" . $customer->getMailAddress() : ""
		));
		$this->addLink("customer_link", array(
			"link" => SOY2PageController::createLink("User.Detail." . $customer->getId())
		));

		//法人名の項目を表示するか？
		$this->addModel("is_offce_item", array(
			"visible" => SOYShop_ShopConfig::load()->getDisplayUserOfficeItems()
		));

		$claimedAddress = $order->getClaimedAddressArray();

		$this->addInput("claimed_customerinfo_office", array(
			"name" => "ClaimedAddress[office]",
			"value" => (isset($claimedAddress["office"])) ? $claimedAddress["office"] : ""
		));
		$this->addInput("claimed_customerinfo_name", array(
			"name" => "ClaimedAddress[name]",
			"value" => (isset($claimedAddress["name"])) ? $claimedAddress["name"] : ""
		));
		$this->addInput("claimed_customerinfo_reading", array(
			"name" => "ClaimedAddress[reading]",
			"value" => (isset($claimedAddress["reading"])) ? $claimedAddress["reading"] : ""
		));
		$this->addInput("claimed_customerinfo_zip_code", array(
			"name" => "ClaimedAddress[zipCode]",
			"value" => (isset($claimedAddress["zipCode"])) ? $claimedAddress["zipCode"] : "",
			"size" => 10
		));
		$this->addSelect("claimed_customerinfo_area", array(
			"name" => "ClaimedAddress[area]",
			"options" => SOYShop_Area::getAreas(),
			"selected" => (isset($claimedAddress["area"])) ? $claimedAddress["area"] : ""
		));
		$this->addInput("claimed_customerinfo_address1", array(
			"name" => "ClaimedAddress[address1]",
			"value" => (isset($claimedAddress["address1"])) ? $claimedAddress["address1"] : "",
		));
		$this->addInput("claimed_customerinfo_address2", array(
			"name" => "ClaimedAddress[address2]",
			"value" => (isset($claimedAddress["address2"])) ? $claimedAddress["address2"] : ""
		));
		$this->addInput("claimed_customerinfo_address3", array(
			"name" => "ClaimedAddress[address3]",
			"value" => (isset($claimedAddress["address3"])) ? $claimedAddress["address3"] : ""
		));
		$this->addInput("claimed_customerinfo_tel_number", array(
			"name" => "ClaimedAddress[telephoneNumber]",
			"value" => (isset($claimedAddress["telephoneNumber"])) ? $claimedAddress["telephoneNumber"] : ""
		));

		$address = $order->getAddressArray();

		$this->addInput("order_customerinfo_office", array(
			"name" => "Address[office]",
			"value" => (isset($address["office"])) ? $address["office"] : ""
		));
		$this->addInput("order_customerinfo_name", array(
			"name" => "Address[name]",
			"value" => (isset($address["name"])) ? $address["name"] : ""
		));
		$this->addInput("order_customerinfo_reading", array(
			"name" => "Address[reading]",
			"value" => (isset($address["reading"])) ? $address["reading"] : ""
		));
		$this->addInput("order_customerinfo_zip_code", array(
			"name" => "Address[zipCode]",
			"value" => (isset($address["zipCode"])) ? $address["zipCode"] : "",
			"size" => 10
		));
		$this->addSelect("order_customerinfo_area", array(
			"name" => "Address[area]",
			"options" => SOYShop_Area::getAreas(),
			"selected" => (isset($address["area"])) ? $address["area"] : ""
		));
		$this->addInput("order_customerinfo_address1", array(
			"name" => "Address[address1]",
			"value" => (isset($address["address1"])) ? $address["address1"] : "",
		));
		$this->addInput("order_customerinfo_address2", array(
			"name" => "Address[address2]",
			"value" => (isset($address["address2"])) ? $address["address2"] : ""
		));
		$this->addInput("order_customerinfo_address3", array(
			"name" => "Address[address3]",
			"value" => (isset($address["address3"])) ? $address["address3"] : ""
		));
		$this->addInput("order_customerinfo_tel_number", array(
			"name" => "Address[telephoneNumber]",
			"value" => (isset($address["telephoneNumber"])) ? $address["telephoneNumber"] : ""
		));

		$this->addLink("order_link", array(
			"link" => SOY2PageController::createLink("Order.Order." . $order->getId()),
			"style" => "font-weight:normal !important;"
		));

		/*** 注文商品 ***/
		//仕入値を出力するか？
		$this->addModel("is_purchase_price", array(
			"visible" => (SOYShop_ShopConfig::load()->getDisplayPurchasePriceOnAdmin())
		));

		$this->createAdd("item_list", "_common.Order.ItemOrderFormListComponent", array(
			"list" => $logic->getItemsByOrderId($this->id),
			"htmlObj" => $this
		));

		$this->addLabel("order_total_price", array(
			"text" => soy2_number_format($order->getPrice())
		));

		$this->createAdd("module_list", "_common.Order.ModuleFormListComponent", array(
			"list" => $order->getModuleList()
		));

		//商品詳細で自由に拡張機能を追加できる
		$this->addLabel("item_edit_add_func", array(
			"html" => SOYShopPlugin::invoke("soyshop.order.edit", array("mode" => "item", "orderId" => $order->getId()))->getHTML()
		));
	}

	private function changeStock(SOYShop_ItemOrder $itemOrder, $stock){
		$item = soyshop_get_item_object($itemOrder->getItemId());
		$item->setStock($item->getStock() - $stock);
		self::updateItem($item);

		SOYShopPlugin::invoke("soyshop.item.order", array(
			"mode" => "edit",
			"itemOrder" => $itemOrder
		));
	}

	private function getPaymentMethodList(){
		SOY2::import("logic.cart.CartLogic");
		SOYShopPlugin::load("soyshop.payment");
		return SOYShopPlugin::invoke("soyshop.payment", array(
			"mode" => "list",
			"cart" => new CartLogic()
		))->getList();
	}

	private function getSelectedPaymentMethod($modules){
		if(!count($modules)) return null;

		foreach($modules as $moduleId => $module){
			if(strpos($moduleId, "payment_") !== false) return $moduleId;
		}
		return null;
	}

	private function isDeliveryTimeItem($attrs){
		if(!count($attrs)) return false;
		foreach($attrs as $key => $attr){
			if(
				strpos($key, "delivery_") !== false &&
				(strpos($key, "_time_") || strpos($key, ".time"))
			) return true;
		}

		// @ToDo 配送時間帯を項目に持つプラグインがインストールされているか？ 調べる術が思いつかないのでダミー以外の配送モジュールがあればtrueにする
		$list = self::getInstalledDeliveryModuleList();
		if(count($list)) return true;

		return false;
	}

	private function getDeliveryTimeItemConfig(SOYShop_Order $order){
		//配送モジュールは何を使用しているのか？を調べる
		$modules = $order->getModuleList();
		$moduleId = null;
		foreach($modules as $key => $mod){
			if($mod->getType() == "delivery_module") $moduleId = $key;
		}

		//moduleIdが空の場合、attributesから配送関連があるか調べる
		if(is_null($moduleId)){
			$attrs = $order->getAttributeList();
			if(count($attrs)){
				$isDeliveryAttribute = false;
				foreach($attrs as $key => $attr){
					if(strpos($key, "delivery_time") !== false){
						$isDeliveryAttribute = true;
						break;
					}
				}

				if($isDeliveryAttribute){
					$list = self::getInstalledDeliveryModuleList();
					if(isset($list[0])) $moduleId = $list[0];
				}
			}
		}

		if(is_null($moduleId)) return "";

		//ダミープラグインを使っていた場合はダミーでないプラグインを使用しているか？調べる
		if(strpos($moduleId, "dummy")){
			$list = self::getInstalledDeliveryModuleList();
			$moduleId = (isset($list[0])) ? $list[0] : null;
		}

		if(is_null($moduleId)) return "";

		$plugin = SOYShopPluginUtil::getPluginById($moduleId);
		if(is_null($plugin->getId())) return "";

		SOYShopPlugin::load("soyshop.delivery", $plugin);
		return SOYShopPlugin::invoke("soyshop.delivery", array(
			"mode" => "config",
			"order" => $order
		))->getConfig();
	}

	private function addExtendAttributes($attrs){
		if(!is_array($attrs) || !count($attrs)) return array();

		//注文詳細の編集画面でattributeを増やせる拡張ポイント
		SOYShopPlugin::load("soyshop.order.edit");
		$extAttrsList = SOYShopPlugin::invoke("soyshop.order.edit", array(
			"mode" => "attribute",
		))->getAttributes();

		if(!is_array($extAttrsList) || !count($extAttrsList)) return $attrs;

		$attrIds = array_keys($attrs);

		foreach($extAttrsList as $moduldId => $extAttrs){
			foreach($extAttrs as $attrId => $extAttr){
				if(!array_search($attrId, $attrIds)){
					$attrs[$attrId] = $extAttr;
				}
			}
		}

		return $attrs;
	}

	private function getInstalledDeliveryModuleList(){
		static $list;
		if(is_array($list)) return $list;
		$list = array();
		$dao = new SOY2DAO();

		//モジュールのタイプがpackageの方にも配送関連の設定があるかもしれない
		$sql = "SELECT plugin_id FROM soyshop_plugins ".
				"WHERE plugin_type IN (\"" . SOYShop_PluginConfig::PLUGIN_TYPE_DELIVERY . "\", \"" . SOYShop_PluginConfig::PLUGIN_TYPE_PACKAGE . "\") ".
				"AND is_active = " . SOYShop_PluginConfig::PLUGIN_ACTIVE;
		try{
			$results = $dao->executeQuery($sql);
		}catch(Exception $e){
			$results = array();
		}

		if(!count($results)) return $list;

		foreach($results as $res){
			if(!isset($res["plugin_id"]) || strpos($res["plugin_id"], "dummy")) continue;
			$list[] = $res["plugin_id"];
		}
		return $list;
	}

	private function updateItem(SOYShop_Item $item){
		static $itemDAO;
		if(!$itemDAO)$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		try{
			$itemDAO->update($item);
		}catch(Exception $e){
			//var_dump($e);
		}
	}

	/**
	 * 請求データ更新関連処理
	 */
	private function updateClaimedAddress(SOYShop_Order $order, $newAddress){
		$change = array();

		$address = $order->getClaimedAddressArray();

		if(isset($address["office"]) && isset($newAddress["office"]) && $address["office"] != $newAddress["office"])		$change[]=self::getHistoryText("請求先",$address["office"],$newAddress["office"]);
		if(isset($address["name"]) && isset($newAddress["name"]) && $address["name"] != $newAddress["name"])			$change[]=self::getHistoryText("請求先",$address["name"],$newAddress["name"]);
		if(isset($address["reading"]) && isset($newAddress["reading"]) && $address["reading"] != $newAddress["reading"])	$change[]=self::getHistoryText("請求先",$address["reading"],$newAddress["reading"]);
		if(isset($address["zipCode"]) && isset($newAddress["zipCode"]) && $address["zipCode"] != $newAddress["zipCode"])	$change[]=self::getHistoryText("請求先",$address["zipCode"],$newAddress["zipCode"]);
		if(isset($address["area"]) && isset($newAddress["area"]) &&  $address["area"] != $newAddress["area"])			$change[]=self::getHistoryText("請求先",SOYShop_Area::getAreaText($address["area"]) ,SOYShop_Area::getAreaText($newAddress["area"]));
		if(isset($address["address1"]) && isset($newAddress["address1"]) && $address["address1"] != $newAddress["address1"])$change[]=self::getHistoryText("請求先",$address["address1"] ,$newAddress["address1"]);
		if(isset($address["address2"]) && isset($newAddress["address2"]) && $address["address2"] != $newAddress["address2"])$change[]=self::getHistoryText("請求先",$address["address2"] ,$newAddress["address2"]);
		if(isset($address["address3"]) && isset($newAddress["address3"]) && $address["address3"] != $newAddress["address3"])$change[]=self::getHistoryText("請求先",$address["address3"] ,$newAddress["address3"]);
		if(isset($address["telephoneNumber"]) && isset($newAddress["telephoneNumber"]) && $address["telephoneNumber"] != $newAddress["telephoneNumber"])$change[]=self::getHistoryText("請求先",$address["telephoneNumber"] ,$newAddress["telephoneNumber"]);

		$order->setClaimedAddress($newAddress);

		return $change;
	}

	/**
	 * 注文データ更新関連処理
	 */
	private function updateOrderAddress(SOYShop_Order $order, $newAddress){
		$change = array();

		$address = $order->getAddressArray();

		if(isset($address["office"]) && isset($newAddress["office"]) && $address["office"] != $newAddress["office"])		$change[]=self::getHistoryText("宛先",$address["office"],$newAddress["office"]);
		if(isset($address["name"]) && isset($newAddress["name"]) && $address["name"] != $newAddress["name"])				$change[]=self::getHistoryText("宛先",$address["name"],$newAddress["name"]);
		if(isset($address["reading"]) && isset($newAddress["reading"]) && $address["reading"] != $newAddress["reading"])	$change[]=self::getHistoryText("宛先",$address["reading"],$newAddress["reading"]);
		if(isset($address["zipCode"]) && isset($newAddress["zipCode"]) && $address["zipCode"] != $newAddress["zipCode"])	$change[]=self::getHistoryText("宛先",$address["zipCode"],$newAddress["zipCode"]);
		if(isset($address["area"]) && isset($newAddress["area"]) && $address["area"] != $newAddress["area"])				$change[]=self::getHistoryText("宛先",SOYShop_Area::getAreaText($address["area"]) ,SOYShop_Area::getAreaText($newAddress["area"]));
		if(isset($address["address1"]) && isset($newAddress["address1"]) && $address["address1"] != $newAddress["address1"])$change[]=self::getHistoryText("宛先",$address["address1"] ,$newAddress["address1"]);
		if(isset($address["address2"]) && isset($newAddress["address2"]) && $address["address2"] != $newAddress["address2"])$change[]=self::getHistoryText("宛先",$address["address2"] ,$newAddress["address2"]);
		if(isset($address["address3"]) && isset($newAddress["address3"]) && $address["address3"] != $newAddress["address3"])$change[]=self::getHistoryText("宛先",$address["address3"] ,$newAddress["address3"]);
		if(isset($address["telephoneNumber"]) && isset($newAddress["telephoneNumber"]) && $address["telephoneNumber"] != $newAddress["telephoneNumber"])$change[]=self::getHistoryText("宛先",$address["telephoneNumber"] ,$newAddress["telephoneNumber"]);

		$order->setAddress($newAddress);

		return $change;
	}

	private function updateOrderPayment(SOYShop_Order $order, $newPayment){
		$change = array();

		$moduleList = $order->getModuleList();
		$attrList = $order->getAttributeList();

		$old = self::getSelectedPaymentMethod($moduleList);

		if($newPayment !== $old){
			unset($moduleList[$old]);
			unset($attrList[$old]);

			$list = self::getPaymentMethodList();

			$module = new SOYShop_ItemModule();
			$module->setId($newPayment);
			$module->setType("payment_module");//typeを指定しておくといいことがある
			$module->setName($list[$newPayment]["name"]);
			$module->setPrice(0);	//@ToDo 金額の指定もしたいところ
			$module->setIsVisible(false);

			$moduleList[$newPayment] = $module;
			$order->setModules($moduleList);

			//attributeの方も変更
			$attrList[$newPayment] = array("name" => "支払方法", "value" => $list[$newPayment]["name"]);
			$order->setAttributes($attrList);

			$change[] = self::getHistoryText("支払方法", $list[$old]["name"], $list[$newPayment]["name"]);
		}

		return $change;
	}

	private function updateOrderAttribute(SOYShop_Order $order, $newAttributes){
		$change = array();
		$attributes = self::addExtendAttributes($order->getAttributeList());

		foreach($attributes as $key => $array){
			if(isset($newAttributes[$key])){
				$newValue = $newAttributes[$key];
				unset($newAttributes[$key]);

				if(isset($array["value"]) && $newValue != $array["value"]){
					$change[]=self::getHistoryText($array["name"], $array["value"], $newValue);
					$attributes[$key]["value"] = $newValue;
				}
			}
		}

		//delivery_module系のデータが残った場合 最初はダミープラグインを使っていたけれども、正式な値を入れたい場合
		if(count($newAttributes)){
			foreach($newAttributes as $key => $array){
				if(strpos($key, "delivery") !== false){
					$newValue = $newAttributes[$key];
					$change[]=self::getHistoryText($key, "", $newValue);
					$attributes[$key] = array("name" => $key, "value" => $newValue);
				}
			}
		}

		$order->setAttributes($attributes);

		return $change;
	}

	/**
	 * @param SOYShop_Order $order, newCustomfields($_POST["customfield"]の配列)
	 * @return array $change
	 */
	private function updateOrderCustomfield(SOYShop_Order $order, $newCustomfields){
		$change = array();

		$list = SOYShopPlugin::invoke("soyshop.order.customfield", array(
			"mode" => "config",
			"orderId" => $order->getId()
		))->getList();

		if(count($list)){
			//扱いやすい配列に変える
			$array = array();
			foreach($list as $obj){
				if(is_array($obj)){
					foreach($obj as $key => $value){
						$array[$key] = $value;
					}
				}
			}

			$dao = SOY2DAOFactory::create("order.SOYShop_OrderAttributeDAO");
			$dateDao = SOY2DAOFactory::create("order.SOYShop_OrderDateAttributeDAO");
		   	foreach($array as $key => $obj){
		   		$newValue1 = null;
				$newValue2 = null;

				$isContinue = false;
				switch($obj["type"]){
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_INPUT:
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_TEXTAREA:
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_SELECT:
						$newValue1 = (isset($newCustomfields[$key])) ? $newCustomfields[$key] : null;
						break;
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_CHECKBOX:
						$newValue1 = (isset($newCustomfields[$key])) ? implode(",", $newCustomfields[$key]) : null;
						break;
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_RADIO:
						$newValue1 = (isset($newCustomfields[$key])) ? $newCustomfields[$key] : null;

						//その他を選んだとき
						if(isset($obj["value1"]) && $newCustomfields[$key] == trim($obj["value1"])){
							$newValue2 = (isset($newCustomfields[$key . "_other_text"])) ? $newCustomfields[$key . "_other_text"] : null;
						}
						break;
					case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_DATE:
						$newValue1 = (isset($newCustomfields[$key]["date"])) ? soyshop_convert_timestamp_on_array($newCustomfields[$key]["date"]) : null;
						$newValue2 = null;
						break;
					case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_PERIOD:
						$newValue1 = (isset($newCustomfields[$key]["start"])) ? soyshop_convert_timestamp_on_array($newCustomfields[$key]["start"]) : null;
						$newValue2 = (isset($newCustomfields[$key]["end"])) ? soyshop_convert_timestamp_on_array($newCustomfields[$key]["end"]) : null;
						break;
					default:
						$isContinue = true;
				}

				// PHP7.3対策
				if($isContinue) continue;

				switch($obj["type"]){
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_INPUT:
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_TEXTAREA:
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_CHECKBOX:
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_RADIO:
					case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_SELECT:
						if($newValue1 != $obj["value1"]){
							$change[]=self::getHistoryText($obj["label"], $obj["value1"], $newValue1);
						}
						if(isset($newValue2) && $newValue2 != $obj["value2"]){
							$change[]=self::getHistoryText($obj["label"], $obj["value2"], $newValue2);
						}
						//ここで配列を入れてしまう。
						try{
							$orderAttr = $dao->get($order->getId(), $key);
						}catch(Exception $e){
							$orderAttr = new SOYShop_OrderAttribute();
							$orderAttr->setOrderId($order->getId());
							$orderAttr->setFieldId($key);
						}

						$orderAttr->setValue1($newValue1);
						$orderAttr->setValue2($newValue2);

						try{
							$dao->insert($orderAttr);
						}catch(Exception $e){
							try{
								$dao->update($orderAttr);
							}catch(Exception $e){
								//
							}
						}
						break;
					case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_DATE:
					case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_PERIOD:
						//value2に値がない場合 dateとか
						if(is_null($newValue2)){
							if($newValue1 != $obj["value1"]){
								$change[] = self::getHistoryText($obj["label"], soyshop_convert_date_string($obj["value1"]), soyshop_convert_date_string($newValue1));
							}

						//value2に値がある場合 periodとか
						}else{
							if($newValue1 != $obj["value1"] || $newValue2 != $obj["value2"]){
								$change[] = self::getHistoryText($obj["label"], soyshop_convert_date_string($obj["value1"]) . " ～ " . soyshop_convert_date_string($obj["value1"]), soyshop_convert_date_string($newValue1) . " ～ " . soyshop_convert_date_string($newValue2));
							}
						}

						try{
							$orderDateAttr = $dateDao->get($order->getId(), $key);
						}catch(Exception $e){
							$orderDateAttr = new SOYShop_OrderDateAttribute();
							$orderDateAttr->setOrderId($order->getId());
							$orderDateAttr->setFieldId($key);
						}

						$orderDateAttr->setValue1($newValue1);
						$orderDateAttr->setValue2($newValue2);

						try{
							$dateDao->insert($orderDateAttr);
						}catch(Exception $e){
							try{
								$dateDao->update($orderDateAttr);
							}catch(Exception $e){
								//
							}
						}

						break;
					default:
				}
			}
		}

		return $change;
	}

	private function updateOrderModules(SOYShop_Order $order, $newModules){
		$change = array();
		$modules = $order->getModuleList();

		foreach($modules as $key => $module){
			if(isset($newModules[$key])){
				$newValue = (isset($newModules[$key]["price"])) ? (int)str_replace(",", "", $newModules[$key]["price"]) : 0;
				$newName  = (isset($newModules[$key]["name"])) ? $newModules[$key]["name"] : "";
				$newIsInclude = (isset($newModules[$key]["isInclude"]) && $newModules[$key]["isInclude"] == 1);
				$delete   = ( isset($newModules[$key]["delete"]) && $newModules[$key]["delete"] );

				if($delete){
					$change[] = $module->getName() . "（" . $module->getPrice() . "円）を削除しました。";
					unset($modules[$key]);
				}else{
					if($newValue != $module->getPrice()) $change[] = self::getHistoryText($module->getName(), $module->getPrice(), $newValue);
					if($newName != $module->getName()) $change[] = self::getHistoryText($module->getName(), $module->getName(), $newName);
					if($newIsInclude != $module->getIsInclude()){
						if($newIsInclude){
							$change[] = self::getHistoryText($module->getName(), "合計に含めない", "合計に含める");
						}else{
							$change[] = self::getHistoryText($module->getName(), "合計に含める", "合計に含めない");
						}
					}

					$modules[$key]->setName($newName);
					$modules[$key]->setPrice($newValue);
					$modules[$key]->setIsInclude($newIsInclude);
				}
			}
		}
		$order->setModules($modules);

		return $change;
	}

	/**
	 * @param orderId int
	 * @return array => labelとformの連想配列を入れる
	 */
	function getCustomfield($orderId){
		$delegate = SOYShopPlugin::invoke("soyshop.order.customfield", array(
			"mode" => "edit",
			"orderId" => $orderId
		));

		$array = array();
		foreach($delegate->getLabel() as $obj){
			if(is_array($obj)){
				foreach($obj as $values){
					$array[] = $values;
				}
			}
		}

		return $array;
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("注文編集", array("Order" => "注文管理", "Order.Detail." . $this->id => "注文詳細"));
	}

	// function getCSS(){
	// 	$root = SOY2PageController::createRelativeLink("./js/");
	// 	return array(
	// 		$root . "tools/soy2_date_picker.css"
	// 	);
	// }

	function getScripts(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			//$root . "tools/soy2_date_picker.pack.js"
			$root . "tools/datepicker-ja.js",
			$root . "tools/datepicker.js"
		);
	}
}
