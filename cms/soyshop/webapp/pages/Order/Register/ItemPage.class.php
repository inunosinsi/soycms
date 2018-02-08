<?php
SOY2::import("module.plugins.common_item_option.logic.ItemOptionLogic");

include(dirname(__FILE__) . "/common.php");

class ItemPage extends WebPage{

	private $cart;
	private $orderLogic;

	function doPost(){
		if(soy2_check_token()){

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

			if(isset($_POST["Item"])){

				$newItems = $_POST["Item"];
				$counts = array();
				foreach($items as $id => $itemOrder){
					if(isset($newItems[$id]) && is_array($newItems[$id])){
						//商品の金額
						if(isset($newItems[$id]["itemPrice"]) && is_numeric($newItems[$id]["itemPrice"])){
							$items[$id]->setItemPrice((int)$newItems[$id]["itemPrice"]);
						}

						//商品個数
						if( isset($newItems[$id]["itemDelete"]) && $newItems[$id]["itemDelete"] ){
							$counts[$id] = 0;
						}else if(isset($newItems[$id]["itemCount"]) && is_numeric($newItems[$id]["itemCount"]) && $newItems[$id]["itemCount"] > 0){
							$counts[$id] = $newItems[$id]["itemCount"];
						}

						//商品オプションの配列はシリアライズしておく
						$newAttributes = (isset($newItems[$id]["attributes"])) ? $newItems[$id]["attributes"] : array();
						if(count($newAttributes) > 0){
							$opt = (isset($newItems[$id]["attributes"]) && is_array($newItems[$id]["attributes"])) ? soy2_serialize($newItems[$id]["attributes"]) : null;
							$items[$id]->setAttributes($opt);
						}
						//$items[$id]->setItemCount($count);
					}
				}
				//商品オプションの登録
				$this->cart->setItems($items);

				//個数変更や削除
				foreach($items as $id => $itemOrder){
					$this->cart->updateItem($id, $counts[$id]);
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
					$price = isset($_POST["AddItemByName"]["price"][$key]) && strlen($_POST["AddItemByName"]["price"][$key])
					       ? trim($_POST["AddItemByName"]["price"][$key]) : "" ;
					$count = isset($_POST["AddItemByName"]["count"][$key]) && strlen($_POST["AddItemByName"]["count"][$key])
					       ? trim($_POST["AddItemByName"]["count"][$key]) : 1 ;
					if(strlen($name)>0 && strlen($price)>0 && $count > 0){
						$this->cart->addUnlistedItem($name, $count, $price);
						$this->cart->save();
					}
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
		$this->orderLogic = SOY2Logic::createInstance("logic.order.admin.OrderLogic");
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
		$this->buildForm();

		//下書き保存用のリンク
		$this->addLink("draft_link", array(
			"link" => SOY2PageController::createLink("Order.Register.Item") . "?draft"
		));

	}

	function buildForm(){
		$items = $this->cart->getItems();

		DisplayPlugin::toggle("restore", (!count($items) && $this->orderLogic->isBackupJsonFile()));
		$this->addActionLink("restore_link", array(
			"link" => SOY2PageController::createLink("Order.Register.Item.Restore")
		));

		$this->createAdd("item_list", "ItemList", array(
			"list" => $items,
			"cart" => $this->cart
		));

		$this->createAdd("total_item_price","HTMLLabel", array(
			"text" => number_format($this->cart->getItemPrice())
		));
	}

	private function getItemByCode($code){
		try{
			return SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getByCode($code);
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}

    function convertDate($date){
    	return mktime(0,0,0,$date["month"],$date["day"],$date["year"]);
    }

    function convertDateText($time){
    	return date("Y",$time)."-".date("m",$time)."-".date("d",$time);
    }

}
