<?php
SOY2::import("module.plugins.common_item_option.util.ItemOptionUtil");

include(dirname(__FILE__) . "/common.php");
SOYShopPlugin::load("soyshop.item.option");

class ItemPage extends WebPage{

	private $cart;
	private $orderLogic;

	function doPost(){
		if(soy2_check_token()){

			//モジュールをクリアする
			$this->cart->removeModule($this->cart->getAttribute("payment_module"));
			$this->cart->removeModule($this->cart->getAttribute("delivery_module"));
			$this->cart->removeModule("consumption_tax");

			$items = $this->cart->getItems();

			//商品の差し替え
			if(isset($_POST["Change"]) && strlen($_POST["Change"]["index"]) && strlen($_POST["Change"]["code"])){
				if(isset($items[$_POST["Change"]["index"]])){
					$itemObj = self::getItemByCode($_POST["Change"]["code"]);
					if(!is_null($itemObj->getId())){
						//itemId, itemPrice, itemNameを入れ替える
						$idx = $_POST["Change"]["index"];
						$items[$idx]->setItemId($itemObj->getId());
						$items[$idx]->setItemPrice($itemObj->getPrice());
						$items[$idx]->setTotalPrice($items[$idx]->getItemPrice() * $items[$idx]->getItemCount());
						$items[$idx]->setItemName($itemObj->getName());

						/** @ToDo 商品が重複する場合は統合したい。削除できるから不要かも **/

						/** JSON形式でバックアップ **/
						$this->orderLogic->backup($items);

						$this->cart->setItems($items);
						$this->cart->save();
						SOY2PageController::jump("Order.Register.Item");
					}
				}
			}

			//並べ替え
			if(isset($_POST["Sort"]) && $_POST["Sort"] == 1){
				$newItems = array();	//並べ替え後の商品情報を入れる配列
				foreach($_POST["Item"] as $idx => $itemOrder){
					$newItems[] = $items[$idx];
				}

				/** JSON形式でバックアップ **/
				$this->orderLogic->backup($newItems);

				$this->cart->setItems($newItems);
				$this->cart->save();
				SOY2PageController::jump("Order.Register.Item");
			}

			SOY2::import("domain.config.SOYShop_ShopConfig");
			$cnf = SOYShop_ShopConfig::load();

			if(isset($_POST["Item"])){
				$newItems = $_POST["Item"];
				$counts = array();
				foreach($items as $idx => $itemOrder){
					if(isset($newItems[$idx]) && is_array($newItems[$idx])){
						//商品の金額
						if(isset($newItems[$idx]["itemPrice"]) && is_numeric($newItems[$idx]["itemPrice"])){
							$items[$idx]->setItemPrice((int)$newItems[$idx]["itemPrice"]);
						}

						//商品個数
						if( isset($newItems[$idx]["itemDelete"]) && $newItems[$idx]["itemDelete"] ){
							$counts[$idx] = 0;
						}else if(isset($newItems[$idx]["itemCount"]) && is_numeric($newItems[$idx]["itemCount"]) && $newItems[$idx]["itemCount"] > 0){
							$counts[$idx] = $newItems[$idx]["itemCount"];
						}

						//商品オプションが一致する場合は統合	未登録商品の場合は下記の処理は関係ないので行わない
						if((int)$itemOrder->getItemId() > 0){
							$resOpts = (isset($newItems[$idx]["attributes"]) && is_array($newItems[$idx]["attributes"])) ? $newItems[$idx]["attributes"] : array();
							$resOpts["itemId"] = $itemOrder->getItemId();
							$res = SOYShopPlugin::invoke("soyshop.item.option", array(
								"mode" => "compare",
								"cart" => $this->cart,
								"option" => $resOpts
							))->getCartOrderId();

							//商品オプションが一致したため統合する
							if(isset($res) && $idx != $res && isset($items[$res])){
								/** @ToDo 数がうまくいかない **/
								$items[$res]->setItemCount((int)$newItems[$res]["itemCount"] + (int)$newItems[$idx]["itemCount"]);
								unset($items[$idx]);
								continue;
							}

							//比較用で挿入しておいたitemIdを削除する
							unset($resOpts["itemId"]);

							//商品オプションの配列はシリアライズしておく
							if(count($resOpts) > 0) $items[$idx]->setAttributes(soy2_serialize($resOpts));
							//$items[$id]->setItemCount($count);
						}
					}
				}

				//商品オプションの登録
				$this->cart->setItems($items);
				$this->cart->setAttribute("add_mode_on_admin_order", 0);

				//個数変更や削除
				foreach($items as $id => $itemOrder){
					$count = (isset($counts[$id])) ? (int)$counts[$id] : 0;
					$this->cart->updateItem($id, $count);
					$this->cart->save();
				}
			}

			if(
				isset($_POST["AddItemByName"]) && is_array($_POST["AddItemByName"])
				 && isset($_POST["AddItemByName"]["name"]) && isset($_POST["AddItemByName"]["price"]) && isset($_POST["AddItemByName"]["count"])
				 && is_array($_POST["AddItemByName"]["name"]) && is_array($_POST["AddItemByName"]["price"]) && is_array($_POST["AddItemByName"]["count"])
			){
				foreach($_POST["AddItemByName"]["name"] as $key => $value){
					$name  = isset($_POST["AddItemByName"]["name"][$key]) && strlen(isset($_POST["AddItemByName"]["name"][$key]))
					       ? trim($_POST["AddItemByName"]["name"][$key]) : "" ;
					if(!strlen($name)) continue;

					$price = isset($_POST["AddItemByName"]["price"][$key]) && strlen($_POST["AddItemByName"]["price"][$key])
					       ? (int)trim($_POST["AddItemByName"]["price"][$key]) : 0 ;
					if(!$cnf->getAllowRegistrationZeroYenProducts() && $price === 0) continue;	//0円商品をカートに入れる事を許可しない

					$count = isset($_POST["AddItemByName"]["count"][$key]) && strlen($_POST["AddItemByName"]["count"][$key])
					       ? (int)trim($_POST["AddItemByName"]["count"][$key]) : 1 ;
					if(!$cnf->getAllowRegistrationZeroQuantityProducts() && $count === 0) continue;	//0円商品をカートに入れる事を許可しない

					$this->cart->addUnlistedItem($name, $count, $price);
					$this->cart->setAttribute("add_mode_on_admin_order", 0);
					$this->cart->save();
				}

				//検索用のセッションのクリア
				SOY2ActionSession::getUserSession()->setAttribute("Order.Register.Item.Search:search_condition", null);
			}

			if(
				isset($_POST["AddItemByCode"]) && is_array($_POST["AddItemByCode"])
				 && isset($_POST["AddItemByCode"]["code"]) && isset($_POST["AddItemByCode"]["count"])
				 && is_array($_POST["AddItemByCode"]["code"]) && is_array($_POST["AddItemByCode"]["count"])
			){
				$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
				foreach($_POST["AddItemByCode"]["code"] as $key => $value){
					$code = isset($_POST["AddItemByCode"]["code"][$key]) && strlen($_POST["AddItemByCode"]["code"][$key])
					       ? trim($_POST["AddItemByCode"]["code"][$key]) : "" ;
					$count = isset($_POST["AddItemByCode"]["count"][$key]) && strlen($_POST["AddItemByCode"]["count"][$key])
					       ? trim($_POST["AddItemByCode"]["count"][$key]) : 1 ;
					if(strlen($code)>0 && $count > 0){
						try{
							$item = $dao->getByCode($code);
							$this->cart->addItem($item->getId(), $count);
							$this->cart->setAttribute("add_mode_on_admin_order", 1);
							$this->cart->save();
						}catch(Exception $e){
							continue;
						}
					}
				}
			}

			/** JSON形式でバックアップ **/
			$this->orderLogic->backup($this->cart->getItems());

			//変更なし
			SOY2PageController::jump("Order.Register.Item");
		}
	}

	function __construct($args) {
		$this->cart = AdminCartLogic::getCart();
		$this->orderLogic = SOY2Logic::createInstance("logic.order.admin.AdminOrderLogic");
		parent::__construct();

		DisplayPlugin::toggle("successed", isset($_GET["successed"]));
		DisplayPlugin::toggle("failed", isset($_GET["failed"]));

		DisplayPlugin::toggle("drafted", (isset($_GET["draft"]) && $this->orderLogic->isBackupJsonFile()));
		DisplayPlugin::toggle("undrafted", (isset($_GET["draft"]) && !$this->orderLogic->isBackupJsonFile()));

		//パラメータから商品IDを取得
		$userId = (isset($args[0]))?$args[0]:null;
		if(isset($args[0]) && strlen($args[0])){
			$itemId = (int)$args[0];
			$this->cart->addItem($itemId, 1);
			$this->cart->save();
			SOY2PageController::jump("Order.Register");
		}

		$this->addForm("form");
		self::buildForm();

		//未登録商品の追加ボタンの有無
		SOY2::import("domain.config.SOYShop_ShopConfig");
		DisplayPlugin::toggle("allow_add_unregistered_item", SOYShop_ShopConfig::load()->getIsUnregisteredItem());

		//下書き保存用のリンク
		$this->addLink("draft_link", array(
			"link" => SOY2PageController::createLink("Order.Register.Item") . "?draft"
		));

	}

	private function buildForm(){
		$items = $this->cart->getItems();

		DisplayPlugin::toggle("restore", (!count($items) && $this->orderLogic->isBackupJsonFile()));
		$this->addActionLink("restore_link", array(
			"link" => SOY2PageController::createLink("Order.Register.Item.Restore")
		));

		//仕入値を出力するか？
		$this->addModel("is_purchase_price", array(
			"visible" => (SOYShop_ShopConfig::load()->getDisplayPurchasePriceOnAdmin())
		));

		include_once(dirname(__FILE__) . "/component/ItemListComponent.class.php");
		$this->createAdd("item_list", "ItemListComponent", array(
			"list" => $items,
			"cart" => $this->cart
		));

		$this->addLabel("total_item_price", array(
			"text" => number_format($this->cart->getItemPrice())
		));

		//商品詳細で自由に拡張機能を追加できる
		SOYShopPlugin::load("soyshop.order.edit");
		$this->addLabel("item_edit_add_func", array(
			"html" => SOYShopPlugin::invoke("soyshop.order.edit", array("mode" => "order"))->getHTML()
		));
	}

	private function getItemByCode($code){
		try{
			return SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getByCode($code);
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("商品を追加する", array("Order" => "注文管理", "Order.Register" => "注文を追加する"));
	}
}
