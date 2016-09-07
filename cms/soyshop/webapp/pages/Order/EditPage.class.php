<?php
SOY2::import("domain.order.SOYShop_ItemModule");
SOY2::import("module.plugins.common_item_option.logic.ItemOptionLogic");
SOYShopPlugin::load("soyshop.item.option");
SOYShopPlugin::load("soyshop.order.customfield");
class EditPage extends WebPage{

	private $id;

	function doPost(){
		if(soy2_check_token()){

			$logic = SOY2Logic::createInstance("logic.order.OrderLogic");
			$order = $logic->getById($this->id);
			$itemOrders = $logic->getItemsByOrderId($this->id);

			$change = array();

			if(isset($_POST["ClaimedAddress"])){
				$_change = $this->updateClaimedAddress($order, $_POST["ClaimedAddress"]);
				$change = array_merge($change, $_change);
			}

			if(isset($_POST["Address"])){
				$_change = $this->updateOrderAddress($order, $_POST["Address"]);
				$change = array_merge($change, $_change);
			}

			if(isset($_POST["Attribute"])){
				$_change = $this->updateOrderAttribute($order, $_POST["Attribute"]);
				$change = array_merge($change, $_change);
			}
			
			if(isset($_POST["Customfield"])){
				$_change = $this->updateOrderCustomfield($order, $_POST["Customfield"]);
				$change = array_merge($change, $_change);
			}

			if(isset($_POST["Module"])){
				$_change = $this->updateOrderModules($order, $_POST["Module"]);
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

					if(strlen($name) > 0 && strlen($price) > 0){
						$module = new SOYShop_ItemModule();
						$moduleId = "added_module." . time() . $key;
						$module->setId($moduleId);
						$module->setName($name);
						$module->setPrice($price);

						$modules[$moduleId] = $module;

						$change[] = $name."（" . $price . "円）を追加しました。";
					}
				}
				$order->setModules($modules);
			}

			$itemChange = array();

			if(isset($_POST["Item"])){
				$newItems = $_POST["Item"];
				foreach($itemOrders as $id => $itemOrder){
					$key = $itemOrder->getId();
					if(isset($newItems[$key])){
						$newName  = (isset($newItems[$key]["itemName"])) ? $newItems[$key]["itemName"] : "";
						$newPrice = (isset($newItems[$key]["itemPrice"])) ? $newItems[$key]["itemPrice"] : null;
						$newCount = (isset($newItems[$key]["itemCount"])) ? $newItems[$key]["itemCount"] : null;
						$newAttributes = (isset($newItems[$key]["attributes"])) ? $newItems[$key]["attributes"] : array();
						$delete   = ( isset($newItems[$key]["itemDelete"]) && $newItems[$key]["itemDelete"] );

						//商品オプションの配列はシリアライズしておく
						if(count($newAttributes) > 0){
							$newItems[$key]["attributes"] = (isset($newItems[$key]["attributes"])) ? soy2_serialize($newItems[$key]["attributes"]) : "";
						}

						//注文数が0個の場合や商品名が空の場合も削除として扱う
						$delete = $delete || $newCount == 0 || strlen($newName) == 0;

						if($delete){
							$item = $this->getItem($itemOrder->getItemId());
							$itemCode = (strlen($item->getCode()) > 0) ? $item->getCode() : $itemOrder->getItemId();

							$itemChange[] = $itemOrder->getItemName() . "（" . $itemCode." " . $itemOrder->getItemPrice() . "円×" . $itemOrder->getItemCount() . "点）を削除しました。";
							$newItems[$key]["itemCount"] = 0;
						}else{
							if($newName != $itemOrder->getItemName()) $itemChange[] = $this->getHistoryText($itemOrder->getItemName(), $itemOrder->getItemName(), $newName);
							if($newPrice != $itemOrder->getItemPrice()) $itemChange[] = $this->getHistoryText($itemOrder->getItemName() . "の単価", $itemOrder->getItemPrice(), $newPrice);
							if($newCount != $itemOrder->getItemCount()) $itemChange[] = $this->getHistoryText($itemOrder->getItemName() . "の個数", $itemOrder->getItemCount(), $newCount);

							$orderAttributes = (count($itemOrder->getAttributeList()) > 0) ? $itemOrder->getAttributeList() : $this->getOptionIndex();

							//商品オプションの比較
							foreach($newAttributes as $index => $attribute){
								$delegate = SOYShopPlugin::invoke("soyshop.item.option", array(
									"mode" => "edit",
									"key" => $index
								));
								if($attribute != $orderAttributes[$index]) $itemChange[] = $this->getHistoryText($itemOrder->getItemName() . "の『" . $delegate->getLabel() . "』", $orderAttributes[$index] , $attribute);
							}
						}

						SOY2::cast($itemOrder, (object)$newItems[$key]);
						$itemOrder->setTotalPrice($itemOrder->getItemPrice() * $itemOrder->getItemCount());
						$itemOrders[$id] = $itemOrder;
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
					$name = trim($_POST["AddItemByName"]["name"][$key]);
					$price = trim($_POST["AddItemByName"]["price"][$key]);
					$count = trim($_POST["AddItemByName"]["count"][$key]);

					if(strlen($name) > 0 && strlen($price)>0 && $count > 0){
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
					}
				}
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
					}
				}
			}

			//変更実行
			if(count($change) > 0 || count($itemChange) > 0){
				
				/*
				 * 
				 * * 料金を再計算
				 */
				$price = 0;

				foreach($itemOrders as $itemOrder){
					$price += $itemOrder->getTotalPrice();
				}
				foreach($newItemOrders as $itemOrder){
					$price += $itemOrder->getTotalPrice();
				}

				$modules = $order->getModuleList();
				
				//モジュール分の加算
				$modulePrice = 0;
				
				$moduleDao = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");
				foreach($modules as $moduleId => $module){
					
					//税金関係の場合はモジュールから削除して、後で再計算して登録
					if($moduleId === "consumption_tax") {
						unset($modules[$moduleId]);
						continue;
					}
					
					//モジュールの再計算のための拡張ポイントを利用する
					try{
						$moduleObj = $moduleDao->getByPluginId($moduleId);
					}catch(Exception $e){
						$moduleObj = null;
					}
					
					if(isset($moduleObj)){
						SOYShopPlugin::load("soyshop.order.module", $moduleObj);
						$delegate = SOYShopPlugin::invoke("soyshop.order.module", array(
							"mode" => "edit",
							"module" => $module,
							"total" => $price
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
				
				/**
				 * @ToDo 税金の再計算
				 */
				//税金の再計算
				$module = self::calculateConsumptionTax($price, $modulePrice);
				if(isset($module)) $modules["consumption_tax"] = $module;

				$price += $modulePrice;

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
							if($itemOrder->getItemCount() == 0){
								$itemOrderDAO->delete($itemOrder);
							}else{
								$itemOrderDAO->update($itemOrder);
							}
						}
						//追加商品
						foreach($newItemOrders as $itemOrder){
							if($itemOrder->getItemCount() > 0){
								$itemOrderDAO->insert($itemOrder);
							}
						}

						$this->insertHistory($this->id, implode("\n", $itemChange));
					}

					//history
					if(count($change) > 0){
						$this->insertHistory($this->id, implode("\n", $change));
					}

					$dao->commit();
				}catch(Exception $e){
					$dao->rollback();
					SOY2PageController::jump("Order.Edit." . $this->id . "?failed");
				}

				SOY2PageController::jump("Order.Detail." . $this->id . "?updated");
			}

			//変更なし
			SOY2PageController::jump("Order.Detail." . $this->id);
		}
	}

	//税金の計算
	private function calculateConsumptionTax($price, $modulePrice){
		SOY2::import("domain.config.SOYShop_ShopConfig");
		$config = SOYShop_ShopConfig::load();
		
		$total = $price;
		
		//モジュール分の加算
		if($config->getConsumptionTaxInclusiveCommission()){
			$total += $modulePrice;
		}
			
		//$cart->calculateConsumptionTax();
		if($config->getConsumptionTax() == SOYShop_ShopConfig::CONSUMPTION_TAX_MODE_ON){
			//外税(プラグインによる処理)
			return self::setConsumptionTax($config, $total);
		}elseif($config->getConsumptionTaxInclusivePricing() == SOYShop_ShopConfig::CONSUMPTION_TAX_MODE_ON){
			//内税(標準実装)
			return self::setConsumptionTaxInclusivePricing($config, $total);
			
		}else{
			//何もしない
		}
			
		return null;
	}
	
	//外税
	private function setConsumptionTax($config, $total){
		$pluginId = $config->getConsumptionTaxModule();

		if(!isset($pluginId)) return null;

   		$pluginDao = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");

   		try{
   			$plugin = $pluginDao->getByPluginId($pluginId);
   		}catch(Exception $e){
   			return null;
   		}

   		if($plugin->getIsActive() == SOYShop_PluginConfig::PLUGIN_INACTIVE) return null;

   		SOYShopPlugin::load("soyshop.tax.calculation", $plugin);
		return SOYShopPlugin::invoke("soyshop.tax.calculation", array(
			"mode" => "edit",
			"total" => $total
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

	function getOptionIndex(){
		$logic = new ItemOptionLogic();
		$list = $logic->getOptions();
		$empty = array();

		foreach($list as $index => $value){
			$empty[$index] = "";
		}

		return $empty;
	}

	function getHistoryText($label, $old, $new){
		return $label . "を『" . $old . "』から『" . $new . "』に変更しました";
	}

	function insertHistory($id, $content, $more = null){
		static $historyDAO;
		if(!$historyDAO) $historyDAO = SOY2DAOFactory::create("order.SOYShop_OrderStateHistoryDAO");

		$history = new SOYShop_OrderStateHistory();
		$history->setOrderId($id);
		if(class_exists("UserInfoUtil")){
			$history->setAuthor(UserInfoUtil::getUserName());
		}else{
			$history->setAuthor("管理人");
		}
		$history->setContent($content);
		$history->setMore($more);
		$historyDAO->insert($history);
	}

	function __construct($args) {
		MessageManager::addMessagePath("admin");
		$this->id = (isset($args[0])) ? (int)$args[0] : "";
		WebPage::__construct();

		$logic = SOY2Logic::createInstance("logic.order.OrderLogic");
		try{
			$order = $logic->getById($this->id);
		}catch(Exception $e){
			SOY2PageController::jump("Order.Detail." . $this->id);
		}

		$this->addLink("order_detail_link", array(
			"link" => SOY2PageController::createLink("Order.Detail." . $order->getId())
		));

		$this->buildForm($order);

	}

	function buildForm(SOYShop_Order $order){
		$logic = SOY2Logic::createInstance("logic.order.OrderLogic");

		$this->addForm("update_form");

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
			"text" => number_format($order->getPrice()) . " 円"
		));

		$this->createAdd("attribute_list", "_common.Order.AttributeFormListComponent", array(
			"list" => $order->getAttributeList()
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

		$claimedAddress = $order->getClaimedAddressArray();

		$this->addInput("claimed_customerinfo_office", array(
			"name" => "ClaimedAddress[office]",
			"value" => $claimedAddress["office"]
		));
		$this->addInput("claimed_customerinfo_name", array(
			"name" => "ClaimedAddress[name]",
			"value" => $claimedAddress["name"]
		));
		$this->addInput("claimed_customerinfo_reading", array(
			"name" => "ClaimedAddress[reading]",
			"value" => $claimedAddress["reading"]
		));
		$this->addInput("claimed_customerinfo_zip_code", array(
			"name" => "ClaimedAddress[zipCode]",
			"value" => $claimedAddress["zipCode"],
			"size" => 10
		));
		$this->addSelect("claimed_customerinfo_area", array(
			"name" => "ClaimedAddress[area]",
			"options" => SOYShop_Area::getAreas(),
			"selected" => $claimedAddress["area"]
		));
		$this->addInput("claimed_customerinfo_address1", array(
			"name" => "ClaimedAddress[address1]",
			"value" => $claimedAddress["address1"],
		));
		$this->addInput("claimed_customerinfo_address2", array(
			"name" => "ClaimedAddress[address2]",
			"value" => $claimedAddress["address2"]
		));
		$this->addInput("claimed_customerinfo_tel_number", array(
			"name" => "ClaimedAddress[telephoneNumber]",
			"value" => $claimedAddress["telephoneNumber"]
		));

		$address = $order->getAddressArray();

		$this->addInput("order_customerinfo_office", array(
			"name" => "Address[office]",
			"value" => $address["office"]
		));
		$this->addInput("order_customerinfo_name", array(
			"name" => "Address[name]",
			"value" => $address["name"]
		));
		$this->addInput("order_customerinfo_reading", array(
			"name" => "Address[reading]",
			"value" => $address["reading"]
		));
		$this->addInput("order_customerinfo_zip_code", array(
			"name" => "Address[zipCode]",
			"value" => $address["zipCode"],
			"size" => 10
		));
		$this->addSelect("order_customerinfo_area", array(
			"name" => "Address[area]",
			"options" => SOYShop_Area::getAreas(),
			"selected" => $address["area"]
		));
		$this->addInput("order_customerinfo_address1", array(
			"name" => "Address[address1]",
			"value" => $address["address1"],
		));
		$this->addInput("order_customerinfo_address2", array(
			"name" => "Address[address2]",
			"value" => $address["address2"]
		));
		$this->addInput("order_customerinfo_tel_number", array(
			"name" => "Address[telephoneNumber]",
			"value" => $address["telephoneNumber"]
		));

		/*** 注文商品 ***/
		$this->createAdd("item_list", "_common.Order.ItemOrderFormListComponent", array(
			"list" => $logic->getItemsByOrderId($this->id),
			"htmlObj" => $this
		));

		$this->addLabel("order_total_price", array(
			"text" => number_format($order->getPrice())
		));

		$this->createAdd("module_list", "_common.Order.ModuleFormListComponent", array(
			"list" => $order->getModuleList()
		));
	}

	/**
	 * @return object#SOYShop_Item
	 * @param itemId
	 */
	function getItem($itemId){
		static $itemDAO;
		static $items = array();

		if(!$itemDAO)$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		if(!isset($items[$itemId])){
			try{
				$items[$itemId] = $itemDAO->getById($itemId);
			}catch(Exception $e){
				$items[$itemId] = new SOYShop_Item();
			}
		}

		return $items[$itemId];
	}

	/**
	 * 請求データ更新関連処理
	 */
	function updateClaimedAddress(SOYShop_Order $order, $newAddress){
		$change = array();

		$address = $order->getClaimedAddressArray();

		if($address["office"] != $newAddress["office"])		$change[]=$this->getHistoryText("請求先",$address["office"],$newAddress["office"]);
		if($address["name"] != $newAddress["name"])			$change[]=$this->getHistoryText("請求先",$address["name"],$newAddress["name"]);
		if($address["reading"] != $newAddress["reading"])	$change[]=$this->getHistoryText("請求先",$address["reading"],$newAddress["reading"]);
		if($address["zipCode"] != $newAddress["zipCode"])	$change[]=$this->getHistoryText("請求先",$address["zipCode"],$newAddress["zipCode"]);
		if($address["area"] != $newAddress["area"])			$change[]=$this->getHistoryText("請求先",SOYShop_Area::getAreaText($address["area"]) ,SOYShop_Area::getAreaText($newAddress["area"]));
		if($address["address1"] != $newAddress["address1"])$change[]=$this->getHistoryText("請求先",$address["address1"] ,$newAddress["address1"]);
		if($address["address2"] != $newAddress["address2"])$change[]=$this->getHistoryText("請求先",$address["address2"] ,$newAddress["address2"]);
		if($address["telephoneNumber"] != $newAddress["telephoneNumber"])$change[]=$this->getHistoryText("請求先",$address["telephoneNumber"] ,$newAddress["telephoneNumber"]);

		$order->setClaimedAddress($newAddress);

		return $change;
	}

	/**
	 * 注文データ更新関連処理
	 */
	function updateOrderAddress(SOYShop_Order $order, $newAddress){
		$change = array();

		$address = $order->getAddressArray();
		
		if($address["office"] != $newAddress["office"])		$change[]=$this->getHistoryText("宛先",$address["office"],$newAddress["office"]);
		if($address["name"] != $newAddress["name"])			$change[]=$this->getHistoryText("宛先",$address["name"],$newAddress["name"]);
		if($address["reading"] != $newAddress["reading"])	$change[]=$this->getHistoryText("宛先",$address["reading"],$newAddress["reading"]);
		if($address["zipCode"] != $newAddress["zipCode"])	$change[]=$this->getHistoryText("宛先",$address["zipCode"],$newAddress["zipCode"]);
		if($address["area"] != $newAddress["area"])			$change[]=$this->getHistoryText("宛先",SOYShop_Area::getAreaText($address["area"]) ,SOYShop_Area::getAreaText($newAddress["area"]));
		if($address["address1"] != $newAddress["address1"])$change[]=$this->getHistoryText("宛先",$address["address1"] ,$newAddress["address1"]);
		if($address["address2"] != $newAddress["address2"])$change[]=$this->getHistoryText("宛先",$address["address2"] ,$newAddress["address2"]);
		if($address["telephoneNumber"] != $newAddress["telephoneNumber"])$change[]=$this->getHistoryText("宛先",$address["telephoneNumber"] ,$newAddress["telephoneNumber"]);

		$order->setAddress($newAddress);

		return $change;
	}

	function updateOrderAttribute(SOYShop_Order $order, $newAttributes){
		$change = array();
		$attributes = $order->getAttributeList();

		foreach($attributes as $key => $array){
			if(isset($newAttributes[$key])){
				$newValue = $newAttributes[$key];

				if($newValue != $array["value"]){
					$change[]=$this->getHistoryText($array["name"], $array["value"], $newValue);
					$attributes[$key]["value"] = $newValue;
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
	function updateOrderCustomfield(SOYShop_Order $order, $newCustomfields){
		$change = array();
		
		$delegate = SOYShopPlugin::invoke("soyshop.order.customfield", array(
			"mode" => "config",
			"orderId" => $order->getId()
		));
		
		$list = (is_array($delegate->getList())) ? $delegate->getList() : array();
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
					$newValue1 = (isset($newCustomfields[$key]["date"])) ? $this->convertDate($newCustomfields[$key]["date"]) : null;
					$newValue2 = null;
					break;
				case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_PERIOD:
					$newValue1 = (isset($newCustomfields[$key]["start"])) ? $this->convertDate($newCustomfields[$key]["start"]) : null;
					$newValue2 = (isset($newCustomfields[$key]["end"])) ? $this->convertDate($newCustomfields[$key]["end"]) : null;
					break;
			}

			switch($obj["type"]){
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_INPUT:
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_TEXTAREA:
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_CHECKBOX:
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_RADIO:
				case SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_SELECT:
					if($newValue1 != $obj["value1"]){
						$change[]=$this->getHistoryText($obj["label"], $obj["value1"], $newValue1);
					}
					if(isset($newValue2) && $newValue2 != $obj["value2"]){
						$change[]=$this->getHistoryText($obj["label"], $obj["value2"], $newValue2);
					}
					//ここで配列を入れてしまう。
					try{
						$dao->delete($order->getId(), $key);
					}catch(Exception $e){
						//
					}

					$orderAttr = new SOYShop_OrderAttribute();
					$orderAttr->setOrderId($order->getId());
					$orderAttr->setFieldId($key);
					$orderAttr->setValue1($newValue1);
					$orderAttr->setValue2($newValue2);

					try{
						$dao->insert($orderAttr);
					}catch(Exception $e){
						//
					}
					break;
				case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_DATE:
				case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_PERIOD:
					//value2に値がない場合 dateとか
					if(is_null($newValue2)){
						if($newValue1 != $obj["value1"]){
							$change[]=$this->getHistoryText($obj["label"], $this->convertDateText($obj["value1"]), $this->convertDateText($newValue1));
						}

					//value2に値がある場合 periodとか
					}else{
						if($newValue1 != $obj["value1"] || $newValue2 != $obj["value2"]){
							$change[]=$this->getHistoryText($obj["label"], $this->convertDateText($obj["value1"]) . " ～ " . $this->convertDateText($obj["value1"]), $this->convertDateText($newValue1) . " ～ " . $this->convertDateText($newValue2));
						}
					}

					//ここで配列を入れてしまう。
					try{
						$dateDao->delete($order->getId(), $key);
					}catch(Exception $e){
						//
					}

					$orderDateAttr = new SOYShop_OrderDateAttribute();
					$orderDateAttr->setOrderId($order->getId());
					$orderDateAttr->setFieldId($key);
					$orderDateAttr->setValue1($newValue1);
					$orderDateAttr->setValue2($newValue2);

					try{
						$dateDao->insert($orderDateAttr);
					}catch(Exception $e){
						//
					}

					break;
				default:
					break;
			}
		}

		return $change;
	}

	function updateOrderModules(SOYShop_Order $order, $newModules){
		$change = array();
		$modules = $order->getModuleList();

		foreach($modules as $key => $module){
			if(isset($newModules[$key])){
				$newValue = (isset($newModules[$key]["price"])) ? $newModules[$key]["price"] : 0;
				$newName  = (isset($newModules[$key]["name"])) ? $newModules[$key]["name"] : "";
				$delete   = ( isset($newModules[$key]["delete"]) && $newModules[$key]["delete"] );

				if($delete){
					$change[] = $module->getName() . "（" . $module->getPrice() . "円）を削除しました。";
					unset($modules[$key]);
				}else{
					if($newValue != $module->getPrice()) $change[] = $this->getHistoryText($module->getName(), $module->getPrice(), $newValue);
					if($newName != $module->getName()) $change[] = $this->getHistoryText($module->getName(), $module->getName(), $newName);

					$modules[$key]->setName($newName);
					$modules[$key]->setPrice($newValue);
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

	function convertDate($date){
		return mktime(0, 0, 0, $date["month"], $date["day"], $date["year"]);
	}

	function convertDateText($time){
		return date("Y", $time) . "-" . date("m", $time) . "-" . date("d", $time);
	}
}
?>